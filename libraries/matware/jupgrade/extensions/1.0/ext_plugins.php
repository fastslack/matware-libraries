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
 * Upgrade class for 3rd party extensions plugins
 *
 * This class takes the weblinks from the existing site and inserts them into the new site.
 *
 * @since	3.0.0
 */
class JUpgradeproExtensionsPlugins extends JUpgradepro
{
	/**
	 * @var		string	The name of the source database table.
	 * @since	3.0.0
	 */
	protected $source = '#__mambots';

	/**
	 * @var		string	The name of the destination database table.
	 * @since	3.0.0
	 */
	protected $destination = '#__extensions';

	/**
	 * @var		string	The name of the source database table.
	 * @since	3.0.0
	 */
	protected $_tbl_key = 'id';

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
		
		$conditions['as'] = "p";
		
		$conditions['select'] = 'name, \'plugin\' AS type, element, folder, client_id, ordering, params';

		$where = array();
		$where[] = "element   NOT   IN   ('joomla',   'ldap',   'gmail',   'openid',   'content',   'categories',   'contacts',   'sections',   'newsfeeds',   'weblinks',   'pagebreak',   'vote',   'emailcloak',   'geshi',   'loadmodule',   'pagenavigation', 'none',   'tinymce',   'xstandard',   'image',   'readmore',   'sef',   'debug',   'legacy',   'cache',   'remember', 'backlink', 'log', 'blogger', 'mtupdate' )";
		
		$conditions['where'] = $where;

		$conditions['group_by'] = 'element';
		
		return $conditions;
	}

	/**
	 * Get the raw data for this part of the upgrade.
	 *
	 * @return	array	Returns a reference to the source data array.
	 * @since	3.0.0
	 * @throws	Exception
	 */
	public function databaseHook($rows = null)
	{
		// Do some custom post processing on the list.
		foreach ($rows as &$row)
		{
			$row = (array) $row;
			// Converting params to JSON
			$row['params'] = $this->convertParams($row['params']);
			// Defaults
			$row['type'] = 'plugin';
		}

		return $rows;
	}
}
