<?php
/**
 * @package     Joomla.Services
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

class ComponentContentArticlesCreate extends ApiControllerItem
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

		// Use the default database.
		$this->setDatabase();

		// Set the controller options.
		$serviceOptions = array(
			'contentType' => 'application/vnd.joomla.item.v1; schema=articles.v1',
			'describedBy' => 'http://docs.joomla.org/Schemas/articles/v1',
			'primaryRel'  => 'joomla:articles',
			'resourceMap' => __DIR__ . '/resource.json',
			'tableName'   => '#__content',
			'tableClass'  => 'Content',
		);

		$this->setOptions($serviceOptions);
	}

	/**
	 * Execute the request.
	 */
	public function execute()
	{
		// Get the user
		$user = $this->app->getIdentity();

		// Check if client has permission to post data
		if ($user->guest == 1)
		{
			header('Status: 400', true, 400);

			$response = array(
				'error' => 'unauthorized_client',
				'error_description' => 'The Joomla! credentials are not valid.'
			);

			echo json_encode($response);
			exit;
		}

		// Get resource item id from input.
		$data = $this->input->post->getArray();

		// Get service object.
		$service = $this->getService();

		// Get the resource map
		$resourceMap = $service->getResourceMap();

		// Transform the data to its internal representation to save
		$targetData = $resourceMap->toInternal(json_decode($data['_data']));

		// Store the target data
		$this->postData($targetData);

		// Set the correct header if resource is created
		header('Status: 201 Created', true, 201);

		exit;
	}

}
