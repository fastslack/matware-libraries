<?php
/**
 * @package     Joomla.Services
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

class ApiServicesRootCreate extends ApiControllerBase
{
	/**
	 * Constructor.
	 *
	 * @param   JInput            $input  The input object.
	 * @param   JApplicationBase  $app    The application object.
	 */
	public function __construct(JInput $input = null, JApplicationBase $app = null)
	{
		parent::__construct($input, $app);

		// Set the controller options.
		$serviceOptions = array(
			'contentType' => 'application/vnd.joomla.service.v1',
			'describedBy' => 'http://docs.joomla.org/Schemas/service/v1',
			'self' 		  => '/',
		);

		$this->setOptions($serviceOptions);
	}

	/**
	 * Execute the request.
	 */
	public function execute()
	{
		// Get service object.
		$service = $this->getService();

		// Store the data from $this->input

		parent::execute();
	}
}
