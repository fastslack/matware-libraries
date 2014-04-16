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
defined('JPATH_PLATFORM') or die;

JLoader::register('MOauth2Credentials', JPATH_LIBRARIES.'/matware/oauth2/credentials.php');

/**
 * OAuth Controller class for initiating temporary credentials for the Matware.Libraries.
 *
 * @package     Matware.Libraries
 * @subpackage  OAuth2
 * @since       1.0
 */
class MOauth2ControllerResource extends MOauth2ControllerBase
{
	/**
	 * Constructor.
	 *
	 * @param   MOauth2ProtocolRequest   $request   The request object
	 * @param   MOauth2ProtocolResponse  $response  The response object
	 *
	 * @since   1.0
	 */
	public function __construct(MOauth2ProtocolRequest $request = null, MOauth2ProtocolResponse $response = null)
	{
		// Call parent first
		parent::__construct();

		// Setup the request object.
		$this->request = isset($request) ? $request : new MOauth2ProtocolRequest;

		// Setup the response object.
		$this->response = isset($response) ? $response : new MOauth2ProtocolResponse;
	}

	/**
	 * Handle the request.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function execute()
	{
		// Verify that we have an OAuth 2.0 application.
		$this->initialise();

		// Generate temporary credentials for the client.
		$credentials = new MOauth2Credentials($this->request);
		$credentials->load();

		// Getting the client object
		$client = $this->fetchClient($this->request->client_id);

		// Ensure the credentials are authorised.
		if ($credentials->getType() !== MOauth2Credentials::TOKEN)
		{
			$this->respondError(400, 'invalid_request', 'The token is not for a valid credentials yet.');
		}

		// Load the JUser class on application for this client
		$this->app->loadIdentity($client->_identity);
	}
}
