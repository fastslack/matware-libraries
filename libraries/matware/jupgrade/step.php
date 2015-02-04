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
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

/**
 * jUpgradePro step class
 *
 * @package		jUpgradePro
 */
class JUpgradeproStep
{	
	public $id = null;
	public $name = null;
	public $title = null;
	public $class = null;
	public $replace = '';
	public $xmlpath = '';
	public $element = null;
	public $conditions = null;

	public $tbl_key = '';
	public $source = '';
	public $destination = '';
	public $cid = 0;
	public $cache = 0;
	public $status = 0;
	public $total = 0;
	public $start = 0;
	public $stop = 0;
	public $laststep = '';
	public $chunk = 0;

	public $first = false;
	public $next = false;
	public $middle = false;
	public $end = false;

	public $old_ver = '';
	public $extensions = false;

	public $_table = false;

	public $debug = '';
	public $error = '';
	
	/**
	 * @var      
	 * @since  3.0
	 */
	protected $_db = null;

	function __construct($name = null, $extensions = false)
	{
		jimport('legacy.component.helper');
		JLoader::import('helpers.jupgradepro', JPATH_COMPONENT_ADMINISTRATOR);

		// Creating dabatase instance for this installation
		$this->_db = JFactory::getDBO();

		// Set step table
		if ($extensions == false) {
			$this->_table = '#__jupgradepro_steps';
		}else if($extensions === 'tables') {
			$this->_table = '#__jupgradepro_extensions_tables';
		}else if($extensions == true) {
			$this->_table = '#__jupgradepro_extensions';
		}

		// Get the old version
		$this->old_ver = JUpgradeproHelper::getVersion('old');

		// Load the last step from database
		if ($name !== false)
		{
			$this->_load($name);
		}
	}

	/**
	 *
	 * @param   stdClass   $options  Parameters to be passed to the database driver.
	 *
	 * @return  jUpgradePro  A jUpgradePro object.
	 *
	 * @since  3.0.0
	 */
	static function getInstance($name = null, $extensions = false)
	{
		// Create our new jUpgradePro connector based on the options given.
		try
		{
			$instance = new JUpgradeproStep($name, $extensions);
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException(sprintf('Unable to load JUpgradeproStep object: %s', $e->getMessage()));
		}

		return $instance;
	}

	/**
	 * Method to set the parameters. 
	 *
	 * @param   array  $parameters  The parameters to set.
	 *
	 * @return  void
	 *
	 * @since   3.0.0
	 */
	public function setParameters($data)
	{
		// Ensure that only valid OAuth parameters are set if they exist.
		if (!empty($data))
		{
			foreach ($data as $k => $v)
			{
				if (property_exists ( $this , $k ))
				{
					// Perform url decoding so that any use of '+' as the encoding of the space character is correctly handled.
					$this->$k = urldecode((string) $v);
				}
			}
		}
	}

	/**
	 * Method to get the parameters. 
	 *
	 * @return  array  $parameters  The parameters of this object.
	 *
	 * @since   3.0.0
	 */
	public function getParameters()
	{
		$return = array();

		foreach ($this as $k => $v)
		{
			if (property_exists ( $this , $k ))
			{
				if (!is_object($v)) {
					if ($v != "" || $k == 'total' || $k == 'start' || $k == 'stop') {
						// Perform url decoding so that any use of '+' as the encoding of the space character is correctly handled.
						$return[$k] = urldecode((string) $v);
					}
				}
			}
		}

		return json_encode($return);
	}

	/**
	 * Get the next step
	 *
	 * @return   step object
	 */
	public function getStep($name = false, $json = true) {

		// Check if step is loaded
		if (empty($name)) {
			return false;
		}

		JLoader::import('helpers.jupgradepro', JPATH_COMPONENT_ADMINISTRATOR);
		$params = JUpgradeproHelper::getParams();

		$limit = $this->chunk = $params->chunk_limit;

		// Getting the total
		if (isset($this->source)) {
			$this->total = JUpgradeproHelper::getTotal($this);
		}

		// We must to fragment the steps
		if ($this->total > $limit) {

			if ($this->cache == 0 && $this->status == 0) {

				if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
					$this->cache = round( ($this->total-1) / $limit, 0, PHP_ROUND_HALF_DOWN);
				}else{
					$this->cache = round( ($this->total-1) / $limit);
				}
				$this->start = 0;
				$this->stop = $limit - 1;
				$this->first = true;
				$this->debug = "{{{1}}}";

			} else if ($this->cache == 1 && $this->status == 1) {

				$this->start = $this->cid;
				$this->cache = 0;
				$this->stop = $this->total - 1;
				$this->debug = "{{{2}}}";
				$this->first = false;

			} else if ($this->cache > 0) { 

				$this->start = $this->cid;
				$this->stop = ($this->start - 1) + $limit;
				$this->cache = $this->cache - 1;
				$this->debug = "{{{3}}}";
				$this->first = false;

				if ($this->stop > $this->total) {
					$this->stop = $this->total - 1;
					$this->next = true;
				}else{
					$this->middle = true;
				}
			}

			// Status == 1
			$this->status = 1;

		}else if ($this->total == 0) {

			$this->stop = -1;
			$this->next = 1;
			$this->first = true;
			if ($this->name == $this->laststep) {
				$this->end = true;
			}
			$this->cache = 0;
			$this->status = 2;
			$this->debug = "{{{4}}}";

		}else{

			$this->start = 0;
			$this->first = 1;
			$this->cache = 0;
			$this->status = 1;
			$this->stop = $this->total - 1;
			$this->debug = "{{{5}}}";
		}

		// Mark if is the end of the step
		if ($this->name == $this->laststep && $this->cache == 1) {
			$this->end = true;
		}

		// updating the status flag
		$this->_updateStep();

		return $this->getParameters();
	}

	/**
	 * Getting the current step from database and put it into object properties
	 *
	 * @return   step object
	 */
	public function _load($name = null) {

		// Getting the data
		$query = $this->_db->getQuery(true);
		$query->select('e.*');
		$query->from($this->_table.' AS e');

		if ($this->_table == '#__jupgradepro_extensions_tables') {
			$query->leftJoin('`#__jupgradepro_extensions` AS ext ON ext.name = e.element');
			$query->select('ext.xmlpath');
		}

		if (!empty($name)) {
			$query->where("e.name = '{$name}'");
		}else{
			$query->where("e.status != 2");
		}

		$query->where("e.version = {$this->_db->quote($this->old_ver)}");

		$query->order('e.id ASC');
		$query->limit(1);

		$this->_db->setQuery($query);
		$step = $this->_db->loadAssoc();

		// Check for query error.
		$error = $this->_db->getErrorMsg();
		if ($error) {
			return false;
		}

		// Check if step is an array
		if (!is_array($step)) {
			return false;
		}

		// Reset the $query object
		$query->clear();

		// Select last step
		$query->select('name');
		$query->from($this->_table);
		$query->where("status = 0");
		if ($this->_table == '#__jupgradepro_extensions_tables') {
			$query->where("element = '{$step['element']}'");
		}
		$query->where("version = {$this->_db->quote($this->old_ver)}");
		$query->order('id DESC');
		$query->limit(1);

		$this->_db->setQuery($query);
		$step['laststep'] = $this->_db->loadResult();

		// Set the parameters
		$this->setParameters($step);

		return true;
	}

	/**
	 * updateStep
	 *
	 * @return	none
	 * @since	2.5.2
	 */
	public function _updateStep() {

		$query = $this->_db->getQuery(true);
		$query->update($this->_table);

		$columns = array('status', 'cache', 'total', 'start', 'stop', 'first', 'debug');

		foreach ($columns as $column) {
			if (!empty($this->$column)) {
				$query->set("{$column} = '{$this->$column}'");
			}
		}

		$query->where("name = {$this->_db->quote($this->name)}");
		$query->where("version = {$this->_db->quote($this->old_ver)}");

		// Execute the query
		$this->_db->setQuery($query)->execute();

		// Check for query error.
		$error = $this->_db->getErrorMsg();

		if ($error) {
			throw new Exception($error);
		}

		return true;
	}

	/**
	 * 
	 *
	 * @return  boolean  True if the user and pass are authorized
	 *
	 * @since   1.0
	 * @throws  InvalidArgumentException
	 */
	public function _updateID($id)
	{
		$name = $this->_getStepName();

		$query = $this->_db->getQuery(true);
		$query->update($this->_table);
		$query->set("`cid` = '{$id}'");
		$query->where("name = {$this->_db->quote($name)}");
		$query->where("version = {$this->_db->quote($this->old_ver)}");

		// Execute the query
		return $this->_db->setQuery($query)->execute();
	}

	/**
	 * Updating the steps table
	 *
	 * @return  boolean  True if the user and pass are authorized
	 *
	 * @since   1.0
	 * @throws  InvalidArgumentException
	 */
	public function _nextID($total = false)
	{
		$update_cid = (int) $this->_getStepID() + 1;
		$this->_updateID($update_cid);
		echo JUpgradeproHelper::isCli() ? "•" : "";
	}

	/**
	 * Update the step id
	 *
	 * @return  int  The next id
	 *
	 * @since   3.0.0
	 */
	public function _getStepID()
	{
		$this->_load($this->name);
		return $this->cid;
	}

	/**
	 * @return  string	The step name  
	 *
	 * @since   3.0
	 */
	public function _getStepName()
	{
		return $this->name;
	}
}
