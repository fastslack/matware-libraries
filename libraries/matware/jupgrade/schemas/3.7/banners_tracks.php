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
 * Upgrade class for Banners
 *
 * This class takes the banners from the existing site and inserts them into the new site.
 *
 * @since       3.2.0
 */
class JUpgradeproBannersTracks extends JUpgradepro
{
	/**
	 * Setting the conditions hook
	 *
	 * @return	array
	 * @since	3.2.0
	 * @throws	Exception
	 */
	public static function getConditionsHook()
	{
		$conditions = array();
		
		$conditions['where'] = array();

		$conditions['group_by'] = "banner_id";
		
		return $conditions;
	}
} // end class
