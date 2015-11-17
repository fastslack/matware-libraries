<?php
/**
 * @version       $Id:
 * @package       Matware.Libraries
 * @subpackage    OAuth2
 * @copyright     Copyright (C) 2004 - 2014 Matware - All rights reserved.
 * @author        Matias Aguirre
 * @email         maguirre@matware.com.ar
 * @link          http://www.matware.com.ar/
 * @license       GNU/GPL http://www.gnu.org/licenses/gpl-2.0-standalone.html
 */
// No direct access
defined('_JEXEC') or die( 'Restricted access' );

// Register component prefix
JLoader::registerPrefix('MOauth2', __DIR__);

/**
 * MOauth2ProtocolRequest class
 *
 * @package  Matware.Libraries
 * @since    1.0
 */
class MOauth2Server
{
	/**
	 * @var    JRegistry  Options for the MOauth2Client object.
	 * @since  1.0
	 */
	protected $options;

	/**
	 * @var    JHttp  The HTTP client object to use in sending HTTP requests.
	 * @since  1.0
	 */
	protected $http;

	/**
	 * @var    MOauth2ProtocolRequest  The input object to use in retrieving GET/POST data.
	 * @since  1.0
	 */
	protected $request;

	/**
	 * @var    MOauth2ProtocolRequest  The input object to use in retrieving GET/POST data.
	 * @since  1.0
	 */
	protected $response;

	/**
	 * Constructor.
	 *
	 * @param   JRegistry               $options  The options object.
	 * @param   JHttp                   $http     The HTTP client object.
	 * @param   MOauth2ProtocolRequest  $request  The request object.
	 *
	 * @since   1.0
	 */
	public function __construct(JRegistry $options = null, JHttp $http = null, MOauth2ProtocolRequest $request = null)
	{
		// Setup the options object.
		$this->options = isset($options) ? $options : new JRegistry;

		// Setup the JHttp object.
		$this->http = isset($http) ? $http : new JHttp($this->options);

		// Setup the request object.
		$this->request = isset($request) ? $request : new MOauth2ProtocolRequest;

		// Setup the response object.
		$this->response = isset($response) ? $response : new MOauth2ProtocolResponse;

		// Getting application
		$this->_app = JFactory::getApplication();
	}

	/**
	 * Method to get the REST parameters for the current request. Parameters are retrieved from these locations
	 * in the order of precedence as follows:
	 *
	 *   - Authorization header
	 *   - POST variables
	 *   - GET query string variables
	 *
	 * @return  boolean  True if an REST message was found in the request.
	 *
	 * @since   1.0
	 */
	public function listen()
	{
		// Initialize variables.
		$found = false;

		// Get the OAuth 2.0 message from the request if there is one.
		$found = $this->request->fetchMessageFromRequest();

		if (!$found)
		{
			return false;
		}

		// If we found an REST message somewhere we need to set the URI and request method.
		if ($found && isset($this->request->response_type) && !isset($this->request->access_token) )
		{
			// Load the correct controller type
			switch ($this->request->response_type)
			{
				case 'temporary':

					$controller = new MOauth2ControllerInitialise($this->request);

					break;
				case 'authorise':

					$controller = new MOauth2ControllerAuthorise($this->request);

					break;
				case 'token':

					$controller = new MOauth2ControllerConvert($this->request);

					break;
				default:
					throw new InvalidArgumentException('No valid response type was found.');
					break;
			}

			// Execute the controller
			$controller->execute();

			// Exit
			exit;
		}

		// If we found an REST message somewhere we need to set the URI and request method.
		if ($found && isset($this->request->access_token) )
		{
			$controller = new MOauth2ControllerResource($this->request);
			$controller->execute();
		}

		return $found;
	}
}
