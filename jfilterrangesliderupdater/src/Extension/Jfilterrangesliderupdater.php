<?php

/**
 * @copyright  (C) 2024 COOLCAT creations, Elisa Foltyn
 * @license    GNU General Public License version 3 or later
 */

namespace Coolcatcreations\Plugin\Task\Jfilterrangesliderupdater\Extension;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Scheduler\Administrator\Event\ExecuteTaskEvent;
use Joomla\Component\Scheduler\Administrator\Task\Status as TaskStatus;
use Joomla\Component\Scheduler\Administrator\Traits\TaskPluginTrait;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\SubscriberInterface;
use Joomla\Database\DatabaseAwareInterface;
use Joomla\Database\DatabaseAwareTrait;

defined('_JEXEC') or die;

/**
 * Task plugin with routines to make HTTP requests.
 * At the moment, offers a single routine for GET requests.
 *
 * @since  1.0.0
 */
class Jfilterrangesliderupdater extends CMSPlugin implements SubscriberInterface, DatabaseAwareInterface
{
	use TaskPluginTrait;
	use DatabaseAwareTrait;

	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  1.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Returns an array of events this subscriber will listen to.
	 *
	 * @var    string[]
	 * @since  1.0.0
	 */
	protected const TASKS_MAP = [
		'plg_task_rangesliderupdater' => [
			'langConstPrefix' => 'PLG_TASK_JFILTERRANGESLIDERUPDATER',
			'form' => 'rangesliderupdater_parameters',
			'method' => 'doRangesliderupdater',
		],
	];
	protected $db;

	/**
	 * Returns an array of events this subscriber will listen to.
	 *
	 * @return string[]
	 *
	 * @since  1.0.0
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'onTaskOptionsList' => 'advertiseRoutines',
			'onExecuteTask' => 'standardRoutineHandler',
			'onContentPrepareForm' => 'enhanceTaskItemForm',
		];
	}

	/**
	 * Constructor.
	 *
	 * @param DispatcherInterface $dispatcher The dispatcher
	 * @param array $config An optional associative array of configuration settings
	 *
	 * @since   4.2.0
	 */
	public function __construct(DispatcherInterface $dispatcher, array $config)
	{
		parent::__construct($dispatcher, $config);
	}


	protected function doRangesliderupdater(ExecuteTaskEvent $event): int
	{

		$db = Factory::getContainer()->get('db');

		$query = $db->getQuery(true)
			->select('*')
			->from($db->quoteName('#__jfilters_filters'))
			->where($db->quoteName('display') . ' = ' . $db->quote('range_inputs_sliders'))
			->orwhere($db->quoteName('display') . ' = ' . $db->quote('range_sliders'));

		$db->setQuery($query);
		$sliders = $db->loadObjectList();

		foreach ($sliders as $slider) {
			$this->logTask('Updating slider ' . $slider->name);

			$context = $slider->context;
			$name = $slider->name;

			$query = $db->getQuery(true)
				->select('id')
				->from($db->quoteName('#__fields'))
				->where($db->quoteName('name') . ' = ' . $db->quote($name));

			$db->setQuery($query);
			$field_id = $db->loadResult();

			$query = $db->getQuery(true)
				->select('value')
				->from($db->quoteName('#__fields_values'))
				->where($db->quoteName('field_id') . ' = ' . $db->quote($field_id));

			$db->setQuery($query);
			$values = $db->loadObjectList();

			$min = $max = null;
			foreach ($values as $value) {
				$number = (int) $value->value;
				if ($min === null || $number < $min) {
					$min = $number;
				}
				if ($max === null || $number > $max) {
					$max = $number;
				}
			}

			$attribs = json_decode($slider->attribs);
			$attribs->min_value = $min;
			$attribs->max_value = $max;

			$query = $db->getQuery(true)
				->update($db->quoteName('#__jfilters_filters'))
				->set($db->quoteName('attribs') . ' = ' . $db->quote(json_encode($attribs)))
				->where($db->quoteName('id') . ' = ' . $db->quote($slider->id));

			$db->setQuery($query);
			$db->execute();
		}

		$this->logTask('Range Slider Updater finished successfully');
		return TaskStatus::OK;
	}
}
