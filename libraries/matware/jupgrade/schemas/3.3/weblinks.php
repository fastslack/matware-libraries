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
 * Upgrade class for Weblinks
 *
 * This class takes the weblinks from the existing site and inserts them into the new site.
 *
 * @since	3.2.0
 */
class JUpgradeproWeblinks extends JUpgradepro
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

		$conditions['select'] = '`id`, `catid`, `title`, `alias`, `url`, `description`, `hits`, '
     .' `state`, `checked_out`, `checked_out_time`, `ordering`, `params`, `language`';
				
		return $conditions;
	}

	/**
	 * Get the raw data for this part of the upgrade.
	 *
	 * @return	array	Returns a reference to the source data array.
	 * @since		3.2.0
	 * @throws	Exception
	 */
	public function &dataHook($rows)
	{
		// Do some custom post processing on the list.
		foreach ($rows as &$row)
		{
			$row = (array) $row;

			// Remove unused fields.
			if (version_compare(JUpgradeproHelper::getVersion('new'), '2.5', '=')) {
				unset($row['version']);
				unset($row['images']);
			}
		}
		
		return $rows;
	}
}
