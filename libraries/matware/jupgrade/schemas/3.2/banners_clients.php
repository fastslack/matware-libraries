<?php
/**
* jUpgradePro
*
* @version $Id:
* @package jUpgradePro
* @copyright Copyright (C) 2004 - 2014 Matware. All rights reserved.
* @author Matias Aguirre
* @email maguirre@matware.com.ar
* @link http://www.matware.com.ar/
* @license GNU General Public License version 2 or later; see LICENSE
*/
/**
 * Upgrade class for banners clients 
 *
 * @package		MatWare
 * @subpackage	com_jupgrade
 * @since		3.2.0
 */
class JUpgradeproBannersClients extends JUpgradepro
{
	/**
	 * Setting the conditions hook
	 *
	 * @return	void
	 * @since	3.0.0
	 * @throws	Exception
	 */
	public static function getConditionsHook()
	{
		$conditions = array();
		
		$conditions['select'] = 'id, `name`, `state`, `contact`, `email`, `extrainfo`, `checked_out`, `checked_out_time`';
		
		$conditions['where'] = array();
		
		return $conditions;
	}	
}
