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

JLoader::register('JUpgradeproMenus', JPATH_LIBRARIES."/matware/jupgrade/menus.php");

/**
 * Upgrade class for Menus
 *
 * This class takes the menus from the existing site and inserts them into the new site.
 *
 * @since	3.2.0
 */

class JUpgradeproMenu extends JUpgradeproMenus
{
	/**
	 * Setting the conditions hook
	 *
	 * @return	array
	 * @since	3.0.0
	 * @throws	Exception
	 */
	public static function getConditionsHook()
	{
		$conditions = array();

		$conditions['as'] = "m";

		$conditions['select'] = 'm.*';

		$conditions['where'] = array();
		$conditions['where'][] = "m.alias != 'root'";
		$conditions['where'][] = "m.id > 101";

		$conditions['order'] = "m.id ASC";

		return $conditions;
	}

	/**
	 * A hook to be able to modify params prior as they are converted to JSON.
	 *
	 * @param	object	$object	A reference to the parameters as an object.
	 *
	 * @return	void
	 * @since	3.6.2
	 * @throws	Exception
	 */
	protected function convertParamsHook(&$object)
	{
		// Add 3.6 parameters
		if (version_compare(JUpgradeproHelper::getVersion('new'), '3.6', '>='))
		{
			$object->featured_categories = isset($object->featured_categories)  ? $object->featured_categories : "[]";
			$object->layout_type = isset($object->layout_type)  ? $object->layout_type : "blog";
			$object->num_leading_articles = isset($object->num_leading_articles)  ? $object->num_leading_articles : 1;
			$object->num_intro_articles = isset($object->num_intro_articles)  ? $object->num_intro_articles : 3;
			$object->num_columns = isset($object->num_columns)  ? $object->num_columns : 3;
			$object->num_links = isset($object->num_links)  ? $object->num_links : 0;
			$object->multi_column_order = isset($object->multi_column_order)  ? $object->multi_column_order : 1;
			$object->orderby_pri = isset($object->orderby_pri)  ? $object->orderby_pri : "";
			$object->orderby_sec = isset($object->orderby_sec)  ? $object->orderby_sec : "front";
			$object->order_date = isset($object->order_date)  ? $object->order_date : "";
			$object->show_pagination = isset($object->show_pagination)  ? $object->show_pagination : "";
			$object->show_pagination_results = isset($object->show_pagination_results)  ? $object->show_pagination_results : 1;
			$object->info_block_position = isset($object->info_block_position)  ? $object->info_block_position : "";
			$object->info_block_show_title = isset($object->info_block_show_title)  ? $object->info_block_show_title : "";
			$object->show_parent_category = isset($object->show_parent_category)  ? $object->show_parent_category : "";
			$object->link_parent_category = isset($object->link_parent_category)  ? $object->link_parent_category : "";
			$object->link_author = isset($object->link_author)  ? $object->link_author : "";
			$object->show_publish_date = isset($object->show_publish_date)  ? $object->show_publish_date : "";
			$object->show_readmore_title = isset($object->show_readmore_title)  ? $object->show_readmore_title : "";
			$object->show_tags = isset($object->show_tags)  ? $object->show_tags : "";
			$object->show_feed_link = isset($object->show_feed_link)  ? $object->show_feed_link : 1;
			//$object->menu-anchor_title = isset($object->menu-anchor_title)  ? $object->menu-anchor_title : "";
			//$object->menu-anchor_css = isset($object->menu-anchor_css)  ? $object->menu-anchor_css : "";
			$object->menu_text = isset($object->menu_text)  ? $object->menu_text : 1;
			$object->menu_show = isset($object->menu_show)  ? $object->menu_show : 1;
			$object->robots = isset($object->robots)  ? $object->robots : "";
		}
	}

	/**
	 * Sets the data in the destination database.
	 *
	 * @return	void
	 * @since	0.4.
	 * @throws	Exception
	 */
	public function dataHook($rows = null)
	{
		$params = $this->getParams();
		$table	= $this->getDestinationTable();

		// Get extensions id's of the new Joomla installation
		$query = "SELECT extension_id, element"
		." FROM #__extensions";
		$this->_db->setQuery($query);
		$extensions_ids = $this->_db->loadObjectList('element');

		$total = count($rows);

		foreach ($rows as $row)
		{
			// Convert the array into an object.
			$row = (object) $row;

			// Convert params
			$this->convertParams($row->params);

			// Fix duplicated alias
			$row->alias = $this->fixAlias('#__menu', $row);

			// Get new/old id's values
			$menuMap = new stdClass();

			// Save the old id
			$menuMap->old = $row->id;

			// Not needed
			unset($row->id);
			unset($row->name);
			unset($row->option);
			unset($row->componentid);
			unset($row->ordering);

			// Inserting the menu
			try	{
				$this->_db->insertObject($table, $row);
			}	catch (Exception $e) {
				throw new Exception($e->getMessage());
			}

			// Save the new id
			$menuMap->new = $this->_db->insertid();

			// Save old and new id
			try	{
				$this->_db->insertObject('#__jupgradepro_menus', $menuMap);
			}	catch (Exception $e) {
				throw new Exception($e->getMessage());
			}

			// Updating the steps table
			$this->_step->_nextID($total);
		}

		return false;
	}
}
