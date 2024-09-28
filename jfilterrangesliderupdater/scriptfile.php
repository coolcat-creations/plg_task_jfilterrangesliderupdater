<?php

/**
 * @copyright  (C) 2024 COOLCAT creations, Elisa Foltyn
 * @license    GNU General Public License version 3 or later
 */

defined('_JEXEC') || die();

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerScriptInterface;
use Joomla\CMS\Language\Text;

return new class() implements InstallerScriptInterface
{

  private string $minimumJoomla = '4.4.0';
  private string $minimumPhp    = '8.1.0';

  public function install(InstallerAdapter $adapter): bool
  {
    return true;
  }

  public function update(InstallerAdapter $adapter): bool
  {
    return true;
  }

  public function uninstall(InstallerAdapter $adapter): bool
  {
    return true;
  }

  public function preflight(string $type, InstallerAdapter $adapter): bool
  {
    if (version_compare(PHP_VERSION, $this->minimumPhp, '<')) {
      Factory::getApplication()->enqueueMessage(sprintf(Text::_('JLIB_INSTALLER_MINIMUM_PHP'), $this->minimumPhp), 'error');
      return false;
    }

    if (version_compare(JVERSION, $this->minimumJoomla, '<')) {
      Factory::getApplication()->enqueueMessage(sprintf(Text::_('JLIB_INSTALLER_MINIMUM_JOOMLA'), $this->minimumJoomla), 'error');
      return false;
    }

    return true;
  }

  public function postflight(string $type, InstallerAdapter $adapter): bool
  {
    if ($type !== 'install' || $type !== 'discover_install') {
      return true;
    }

    $db    = Factory::getDbo();
    $query = $db->getQuery(true)
        ->update('#__extensions')
        ->set($db->qn('enabled') . ' = 1')
        ->where($db->qn('type') . ' = ' . $db->q('plugin'))
        ->where($db->qn('element') . ' = ' . $db->q('jfilterrangesliderupdater'))
        ->where($db->qn('folder') . ' = ' . $db->q('task'));

    $db->setQuery($query);
    $db->execute();

    return true;
  }
};
