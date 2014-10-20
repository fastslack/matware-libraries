<?php
/**
 * @package     Joomla.Services
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

abstract class ApiControllerItem extends ApiControllerBase
{
	/*
	 * Unique key value.
	 */
	protected $id = 0;

	/**
	 * Execute the request.
	 */
	public function execute()
	{
		// Get resource item id from input.
		$this->id = (int) $this->input->get('id');

		// Get resource item data.
		$data = $this->getData();

		// Get service object.
		$service = $this->getService();

		// Load the data into the HAL object.
		$service->load($data);

		parent::execute();
	}

	/**
	 * Get data for a single resource item.
	 *
	 * @return object Single resource item object.
	 */
	public function getData()
	{
		// Get the database query object.
		$query = $this->getQuery($this->tableName);

		// Get a database query helper object.
		$apiQuery = $this->getApiQuery();

		// Get single record from database.
		$data = $apiQuery->getItem($query, (int) $this->id);

		return $data;
	}

	/**
	 * Post data from JSON resource item.
	 *
	 * @param   string	$data  The JSON+HAL resource.
	 *
	 * @return bool True if resource is created, false if some error occured
	 */
	public function postData($data, $tableClass = false, $tablePrefix = 'JTable', $tablePath = array())
	{
		// Declare return
		$return = false;

		// Get the database query object.
		$query = $this->db->getQuery(true);

		// Get a database query helper object.
		$apiQuery = $this->getApiQuery();

		// Get the correct table class
		$tableClass = ($tableClass != false) ? $this->tableClass : $tableClass;

		// Get the correct table prefix
		$tablePrefix = ($tablePrefix != 'JTable') ? $tablePrefix : 'JTable';

		// Include the legacy table classes
		JTable::addIncludePath(JPATH_LIBRARIES . '/legacy/table/');

		// Include the custom table path if exists
		if (count($tablePath))
		{
			foreach ($tablePath as $path)
			{
				JTable::addIncludePath($path);
			}
		}

		// Declare the JTable class
		$table = JTable::getInstance($tableClass, $tablePrefix, array('dbo' => $this->db));

		try
		{
			$return = $apiQuery->postItem($query, $table, $data);
		}
		catch (Exception $e)
		{
			$this->app->setHeader('status', '400', true);

			// An exception has been caught, echo the message and exit.
			echo json_encode(array('message' => $e->getMessage(), 'code' => $e->getCode(), 'type' => get_class($e)));
			exit;
		}

		return $return;
	}
}
