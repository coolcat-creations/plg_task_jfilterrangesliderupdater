<?php

/**
 * @copyright  (C) 2024 COOLCAT creations, Elisa Foltyn
 * @license    GNU General Public License version 3 or later
 */

defined('_JEXEC') || die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Coolcatcreations\Plugin\Task\Jfilterrangesliderupdater\Extension\Jfilterrangesliderupdater;

return new class implements ServiceProviderInterface
{
    /**
     * Registers the service provider with a DI container.
     *
     * @param   Container  $container  The DI container.
     *
     * @return  void
     *
     * @since   0.0.1
     */
    public function register(Container $container)
    {
        $container->set(
            PluginInterface::class,
            function (Container $container) {
                $dispatcher = $container->get(DispatcherInterface::class);
                $plugin     = new Jfilterrangesliderupdater(
                    $dispatcher,
                    (array) PluginHelper::getPlugin('task', 'jfilterrangesliderupdater')
                );
                $plugin->setApplication(Factory::getApplication());

                return $plugin;
            }
        );
    }
};
