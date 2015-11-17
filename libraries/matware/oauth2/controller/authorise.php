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
 * OAuth Controller class for authorising temporary credentials for the Matware.Libraries.
 *
 * According to RFC 5849, this must be handled using a GET request, so route accordingly. When implementing this in your own
 * app you should provide some means of protection against CSRF attacks.
 *
 * @package     Matware.Libraries
 * @subpackage  OAuth2
 * @since       12.3
 */
class MOauth2ControllerAuthorise extends MOauth2ControllerBase
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
		// Verify that we have an rest api application.
		$this->initialise();

		// Generate temporary credentials for the client.
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

		// Verify that we have a signed in user.
		if (isset($this->request->code) && $credentials->getTemporaryToken() !== $this->request->code)
		{
			$this->respondError(400, 'invalid_grant', 'Temporary token is not valid');
		}

		// Ensure the credentials are temporary.
		if ( (int) $credentials->getType() !== MOauth2Credentials::TEMPORARY)
		{
			$this->respondError(400, 'invalid_request', 'The token is not for a temporary credentials set.');
		}

		// Verify that we have a signed in user.
		if ($this->app->getIdentity()->get('guest'))
		{
			$this->respondError(400, 'unauthorized_client', 'You must first sign in.');
		}

		// Attempt to authorise the credentials for the current user.
		$credentials->authorise($this->app->getIdentity()->get('id'));

		/*
		if ($credentials->getCallbackUrl() && $credentials->getCallbackUrl() != 'oob')
		{
			$this->app->redirect($credentials->getCallbackUrl());

			return;
		}
		*/
		// Build the response for the client.
		$response = array(
			'oauth_code' => $credentials->getTemporaryToken(),
			'oauth_state' => true
		);

		// Check if the request is CORS ( Cross-origin resource sharing ) and change the body if true
 		$body = $this->prepareBody($response);

		// Set the response code and body.
		$this->response->setHeader('status', '200')
			->setBody($body)
			->respond();
		exit;
	}
}
