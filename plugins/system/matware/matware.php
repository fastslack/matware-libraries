<?php
/**
 * @version       $Id: 
 * @package       Matware.Plugin
 * @subpackage    Matware
 * @copyright     Copyright (C) 2004 - 2014 Matware - All rights reserved.
 * @author        Matias Aguirre
 * @email         maguirre@matware.com.ar
 * @link          http://www.matware.com.ar/
 * @license       GNU/GPL http://www.gnu.org/licenses/gpl-2.0-standalone.html
 */

defined('JPATH_BASE') or die;

/**
 * System plugin for Matware
 *
 * @package     Joomla.Plugin
 * @subpackage  System
 * @since       1.0
 */
class PlgSystemMatware extends JPlugin
{
	/**
	 * Method to register custom library.
	 *
	 * @return  void
	 */
	public function onAfterInitialise()
	{
		$bootstrap = JPATH_LIBRARIES . '/matware/bootstrap.php';

		if (file_exists($bootstrap))
		{
			require_once $bootstrap;
		}
	}
}
