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
 * OAuth Controller class for converting authorised credentials to token credentials for the Matware.Libraries.
 *
 * @package     Matware.Libraries
 * @subpackage  OAuth2
 * @since       12.3
 */
class MOauth2ControllerConvert extends MOauth2ControllerBase
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
	 * @since   12.3
	 */
	public function execute()
	{
		// Verify that we have an OAuth 2.0 application.
		$this->initialise();

		// Get the credentials for the request.
		$credentials = new MOauth2Credentials($this->request);
		$credentials->load();

		// Getting the client object
		$client = $this->fetchClient($this->request->client_id);

		// Doing authentication using Joomla! users
		if ($credentials->doJoomlaAuthentication($client) == false)
		{
			$this->respondError(400, 'unauthorized_client', 'The Joomla! credentials are not valid.');
		}

		// Load the JUser class on application for this client
		$this->app->loadIdentity($client->_identity);

		// Ensure the credentials are authorised.
		if ($credentials->getType() === MOauth2Credentials::TOKEN)
		{
			$this->respondError(400, 'invalid_request', 'The token is not for a temporary credentials set.');
		}

		// Ensure the credentials are authorised.
		if ($credentials->getType() !== MOauth2Credentials::AUTHORISED)
		{
			$this->respondError(400, 'invalid_request', 'The token has not been authorised by the resource owner.');
		}

		// Convert the credentials to valid Token credentials for requesting protected resources.
		$credentials->convert();

		// Build the response for the client.
		$response = array(
			'access_token' => $credentials->getAccessToken(),
			'expires_in' => 'P60M',
			'refresh_token' => $credentials->getRefreshToken()
		);

		// Check if the request is CORS ( Cross-origin resource sharing ) and change the body if true
 		$body = $this->prepareBody($response);

		// Set the response code and body.
		$this->response->setHeader('status', '200')
			->setBody($body)
			->respond();
	}
}
