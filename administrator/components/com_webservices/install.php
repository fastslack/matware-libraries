<?php
/**
 * @version       $Id: 
 * @package       Matware.Libraries
 * @subpackage    Webservices
 * @copyright     Copyright (C) 1996 - 2015 Matware - All rights reserved.
 * @author        Matias Aguirre
 * @email         maguirre@matware.com.ar
 * @link          http://www.matware.com.ar/
 * @license       GNU/GPL http://www.gnu.org/licenses/gpl-2.0-standalone.html
 */

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

class com_webservicesInstallerScript
{
	/**
	 * Called before any type of action
	 *
	 * @param   string  $route  Which action is happening (install|uninstall|discover_install|update)
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function preflight($route, JAdapterInstance $adapter)
	{
	}
 
	/**
	 * Called after any type of action
	 *
	 * @param   string  $route  Which action is happening (install|uninstall|discover_install|update)
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function postflight($route, JAdapterInstance $adapter)
	{
	}
 
	/**
	 * Called on installation
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function install(JAdapterInstance $adapter)
	{
		// Declare path
		$adminpath = JPATH_ADMINISTRATOR . "/components/com_webservices/install";

		// Copy 'api' && 'etc' directories
		JFolder::copy("{$adminpath}/api", JPATH_ROOT."/api");
		JFolder::copy("{$adminpath}/etc", JPATH_ROOT."/etc");

		// Copy the CLI scripts
		JFolder::copy("{$adminpath}/cli/WebsocketServer", JPATH_ROOT."/cli/WebsocketServer", '', true);

		// Copy com_content component services
		JFolder::copy("{$adminpath}/components/com_content/services", JPATH_ROOT."/components/com_content/services", '', true);
		JFile::copy("{$adminpath}/components/com_content/services.json", JPATH_ROOT."/components/com_content/services.json");
	}
 
	/**
	 * Called on update
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function update(JAdapterInstance $adapter)
	{
	}
 
	/**
	 * Called on uninstallation
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 */
	public function uninstall(JAdapterInstance $adapter)
	{
	}
}
