<?php
/**
 * @version       $Id: 
 * @package       Matware.Component
 * @subpackage    Webservices
 * @copyright     Copyright (C) 2004 - 2015 Matware - All rights reserved.
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

		// Copy component services
		JFolder::copy("{$adminpath}/components/com_content/services", JPATH_BASE."/components/com_content/services");
		JFolder::copy("{$adminpath}/components/com_weblinks/services", JPATH_BASE."/components/com_weblinks/services");

		JFile::copy("{$adminpath}/components/com_content/services.json", JPATH_BASE."/components/com_content/services.json");
		JFile::copy("{$adminpath}/components/com_weblinks/services.json", JPATH_BASE."/components/com_weblinks/services.json");
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
