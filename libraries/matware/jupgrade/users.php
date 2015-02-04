<?php
/**
* jUpgradePro
*
* @version $Id:
* @package jUpgradePro
* @copyright Copyright (C) 2004 - 2013 Matware. All rights reserved.
* @author Matias Aguirre
* @email maguirre@matware.com.ar
* @link http://www.matware.com.ar/
* @license GNU General Public License version 2 or later; see LICENSE
*/
/**
 * Database methods
 *
 * This class search for extensions to be migrated
 *
 * @since	3.0.0
 */
class JUpgradeproUser extends JUpgradepro
{
	/**
	 * @var      
	 * @since  3.0
	 */
	protected	$usergroup_map = array(
			// Old	=> // New
			0		=> 0,	// ROOT
			28		=> 1,	// USERS (=Public)
			29		=> 1,	// Public Frontend
			17		=> 2,	// Registered
			18		=> 2,	// Registered
			19		=> 3,	// Author
			20		=> 4,	// Editor
			21		=> 5,	// Publisher
			30		=> 6,	// Public Backend (=Manager)
			23		=> 6,	// Manager
			24		=> 7,	// Administrator
			25		=> 8,	// Super Administrator
	);

	/**
	 * Method to do pre-processes modifications before migrate
	 *
	 * @return      boolean Returns true if all is fine, false if not.
	 * @since       3.2.0
	 * @throws      Exception
	 */
	public function beforeHook()
	{
		// Get the data
		$query = $this->_db->getQuery(true);
		$query->select("u.id, u.username");
		$query->from("#__users AS u");
		$query->join("LEFT", "#__user_usergroup_map AS um ON um.user_id = u.id");
		$query->join("LEFT", "#__usergroups AS ug ON ug.id = um.group_id");
		$query->order('u.id ASC');
		$query->limit(1);

		$this->_db->setQuery($query);

		try {
			$superuser = $this->_db->loadObject();
		} catch (RuntimeException $e) {
			throw new RuntimeException($e->getMessage());
		}

		// Update the super user id to 2147483647
		$query->clear();
		$query->update("#__users");
		$query->set("`id` = 2147483647");
		$query->where("username = '{$superuser->username}'");

		// Execute the query
		try {
			$this->_db->setQuery($query)->execute();
		} catch (RuntimeException $e) {
			throw new RuntimeException($e->getMessage());
		}

		// Update the user_usergroup_map
		$query->clear();
		$query->update("#__user_usergroup_map");
		$query->set("`user_id` = 2147483647");
		$query->where("`group_id` = 8");
		$query->where("`user_id` = {$superuser->id}");

		// Execute the query
		try {
			$this->_db->setQuery($query)->execute();
		} catch (RuntimeException $e) {
			throw new RuntimeException($e->getMessage());
		}
	}

	/**
	 * Get the mapping of the old usergroups to the new usergroup id's.
	 *
	 * @return	array	An array with keys of the old id's and values being the new id's.
	 * @since	1.1.0
	 */
	protected function getUsergroupIdMap()
	{
		return $this->usergroup_map;
	}

	/**
	 * Map old user group from Joomla 1.5 to new installation.
	 *
	 * @return	int	New user group
	 * @since	1.2.2
	 */
	protected function mapUserGroup($id) {
		return isset($this->usergroup_map[$id]) ? $this->usergroup_map[$id] : $id;
	}

	/**
	 * Method to get a map of the User id to ARO id.
	 *
	 * @returns	array	An array of the user id's keyed by ARO id.
	 * @since	0.4.4
	 * @throws	Exception on database error.
	 */
	protected function getUserIdAroMap($aro_id)
	{
		// Get the version
		$old_version = JUpgradeproHelper::getVersion('old');
		// Get thge correct table key
		$key = ($old_version == '1.0') ? 'aro_id' : 'id';

		$this->_driver->_db_old->setQuery(
			'SELECT value' .
			' FROM #__core_acl_aro' .
			' WHERE '.$key.' = '.$aro_id
		);

		$return	= $this->_driver->_db_old->loadResult();
		$error	= $this->_driver->_db_old->getErrorMsg();

		// Check for query error.
		if ($error) {
			throw new Exception($error);
		}

		return $return;
	}
}
