<?php
/**
* Mets
*
* @version $Id:
* @package Matware.Webservices
* @copyright Copyright (C) 2004 - 2015 Matware. All rights reserved.
* @author Matias Aguirre
* @email maguirre@matware.com.ar
* @link http://www.matware.com.ar/
* @license GNU General Public License version 2 or later; see LICENSE
*/

// Import the classes


class ComponentWebservicesTimeWebsocket extends ApiControllerItem
{
	/**
	 * @param   boolean  The debug flag (true/false).
	 * @since  1.0
	 */
	public $debug = false;

	/**
	 * @param   string  The buffer for debug.
	 * @since  1.0
	 */
	public $buffer = '';

	/**
	 * Constructor.
	 *
	 * @param   JInput            $input  The input object.
	 * @param   JApplicationBase  $app    The application object.
	 */
	public function __construct(JInput $input = null, JApplicationBase $app = null)
	{
		parent::__construct($input, $app);

		jimport('joomla.filesystem.file');

		// Use the default database.
		$this->_db = JFactory::getDBO();

		// Set the controller options.
		$serviceOptions = array(
			'contentType' => 'application/vnd.joomla.item.v1; schema=webservices.v1',
			'describedBy' => 'http://docs.joomla.org/Schemas/articles/v1',
			'primaryRel'  => 'webservices:time',
			'resourceMap' => __DIR__ . '/resource.json',
			'self' 		  => '/webservices:time/',
			//'tableName'   => '#__content',
		);

		// Set the service options
		$this->setOptions($serviceOptions);
	}

	/**
	 * Execute the request.
	 */
	public function execute()
	{
		// Get the user
		$this->user = $this->app->getIdentity();

		// Check if user has the privileges
		//$this->checkIdentity();

		// Get resource item data.
		$data = $this->getData();

		return json_encode($data);
	}

	/**
	 * Get database query.
	 *
	 * @return object An object with the data.
	 */
	public function getData()
	{
		$return = new stdClass;

		$return->time = date('H:i:s');

		$return->method = $this->app->input->getMethod();

		return $return;
	}
}
