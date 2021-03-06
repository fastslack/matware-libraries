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

JLoader::register("JUpgradeproUser", JPATH_LIBRARIES."/matware/jupgrade/users.php");

/**
 * Upgrade class for Users
 *
 * This class takes the users from the existing site and inserts them into the new site.
 *
 * @since	0.4.4
 */
class JUpgradeproUsers extends JUpgradeproUser
{
	/*
	 * Fake method after hooks
	 *
	 * @return	void
	 * @since	3.0.0
	 * @throws	Exception
	 */
	public function afterHook()
	{
		// Updating the super user id to 10
		$query = $this->_db->getQuery(true);
		$query->update("#__users");
		$query->set("`id` = 2");
		$query->where("id = 2147483647");
		// Execute the query
		try {
			$this->_db->setQuery($query)->execute();
		} catch (RuntimeException $e) {
			throw new RuntimeException($e->getMessage());
		}

		// Updating the user_usergroup_map
		$query->clear();
		$query->update("#__user_usergroup_map");
		$query->set("`user_id` = 2");
		$query->where("`user_id` = 2147483647");
		// Execute the query
		try {
			$this->_db->setQuery($query)->execute();
		} catch (RuntimeException $e) {
			throw new RuntimeException($e->getMessage());
		}
	}

	/**
	 * Get the raw data for this part of the upgrade.
	 *
	 * @return	array	Returns a reference to the source data array.
	 * @since	0.4.4
	 * @throws	Exception
	 */
	public function &databaseHook($rows)
	{
		// Do some custom post processing on the list.
		foreach ($rows as &$row)
		{
			$row = (array) $row;

			$row['params'] = $this->convertParams($row['params']);
		}
		
		return $rows;
	}

	/**
	 * Sets the data in the destination database.
	 *
	 * @return	void
	 * @since	0.4.
	 * @throws	Exception
	 */
	public function dataHook($rows)
	{
		// Do some custom post processing on the list.
		foreach ($rows as &$row)
		{
			$row = (array) $row;

			if (version_compare(JUpgradeproHelper::getVersion('new'), '3.0', '>=')) {
				unset($row['usertype']);
				if (isset($row['uid'])) {
					unset($row['uid']);
				}
			}

			// Chaging admin username and email
			if ($row['username'] == 'admin') {
				$row['username'] = $row['username'].'-old';
				$row['email'] = $row['email'].'-old';
			}

			// Remove unused fields. 
			unset($row['gid']);
		}

		return $rows;
	}

	/**
	 * A hook to be able to modify params prior as they are converted to JSON.
	 *
	 * @param	object	$object	A reference to the parameters as an object.
	 *
	 * @return	void
	 * @since	0.4.
	 * @throws	Exception
	 */
	protected function convertParamsHook(&$object)
	{
		$object->timezone = 'UTC';
	}
}
