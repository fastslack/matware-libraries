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

/**
 * OAuth Credentials base class for the Matware.Libraries
 *
 * @package     Matware.Libraries
 * @subpackage  OAuth2
 * @since       1.0
 */
class MOauth2Credentials
{
	/**
	 * @var    integer  Indicates temporary credentials.  These are ready to be authorised.
	 * @since  1.0
	 */
	const TEMPORARY = 0;

	/**
	 * @var    integer  Indicates authorised temporary credentials.  These are ready to be converted to token credentials.
	 * @since  1.0
	 */
	const AUTHORISED = 1;

	/**
	 * @var    integer  Indicates token credentials.  These are ready to be used for accessing protected resources.
	 * @since  1.0
	 */
	const TOKEN = 2;

	/**
	 * @var    MOauth2TableCredentials  Connector object for table class.
	 * @since  1.0
	 */
	public $_table;

	/**
	 * @var    MOauth2CredentialsState  The current credential state.
	 * @since  1.0
	 */
	public $_state;

	/**
	 * @var    MOauth2ProtocolRequest   The current HTTP request.
	 * @since  1.0
	 */
	public $_request;

	/**
	 * Object constructor.
	 *
	 * @param   MOauth2ProtocolRequest   $request  The HTTP request
	 * @param   MOauth2TableCredentials  $table    Connector object for table class.
	 *
	 * @since   1.0
	 */
	public function __construct(MOauth2ProtocolRequest $request, MOauth2TableCredentials $table = null)
	{
		// Load the HTTP request
		$this->_request = $request ? $request : new MOauth2ProtocolRequest;

		// Setup the database object.
		$this->_table = $table ? $table : JTable::getInstance('Credentials', 'MOauth2Table');

		// Assume the base state for any credentials object to be new.
		$this->_state = new MOauth2CredentialsStateNew($this->_table);

		// Setup the correct signer
		$signature = isset($this->_request->signature_method) ? $this->_request->signature_method : 'PLAINTEXT';
		$this->_signer = MOauth2CredentialsSigner::getInstance($signature);
	}

	/**
	 * Method to authorise the credentials.  This will persist a temporary credentials set to be authorised by
	 * a resource owner.
	 *
	 * @param   integer  $resourceOwnerId  The id of the resource owner authorizing the temporary credentials.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 * @throws  LogicException
	 */
	public function authorise($resourceOwnerId)
	{
		$this->_state = $this->_state->authorise($resourceOwnerId);
	}

	/**
	 * Method to convert a set of authorised credentials to token credentials.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 * @throws  LogicException
	 */
	public function convert()
	{
		$this->_state = $this->_state->convert();
	}

	/**
	 * Method to deny a set of temporary credentials.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 * @throws  LogicException
	 */
	public function deny()
	{
		$this->_state = $this->_state->deny();
	}

	/**
	 * Get the callback url associated with this token.
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function getCallbackUrl()
	{
		return $this->_state->callback_url;
	}

	/**
	 * Get the consumer key associated with this token.
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function getClientId()
	{
		return $this->_state->client_id;
	}

	/**
	 * Get the credentials key value.
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function getClientSecret()
	{
		return $this->_state->client_secret;
	}

	/**
	 * Get the temporary token secret.
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function getTemporaryToken()
	{
		return $this->_state->temporary_token;
	}

	/**
	 * Get the token secret.
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function getAccessToken()
	{
		return $this->_state->access_token;
	}

	/**
	 * Get the token secret.
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function getRefreshToken()
	{
		return $this->_state->refresh_token;
	}

	/**
	 * Get the ID of the user this token has been issued for.  Not all tokens
	 * will have known users.
	 *
	 * @return  integer
	 *
	 * @since   1.0
	 */
	public function getResourceOwnerId()
	{
		return $this->_state->resource_owner_id;
	}

	/**
	 * Get the credentials type.
	 *
	 * @return  integer
	 *
	 * @since   1.0
	 */
	public function getType()
	{
		return (int) $this->_state->type;
	}

	/**
	 * Get the expiration date.
	 *
	 * @return  integer
	 *
	 * @since   1.0
	 */
	public function getExpirationDate()
	{
		return $this->_state->expiration_date;
	}

	/**
	 * Get the temporary expiration date.
	 *
	 * @return  integer
	 *
	 * @since   1.0
	 */
	public function getTemporaryExpirationDate()
	{
		return $this->_state->temporary_expiration_date;
	}

	/**
	 * Method to initialise the credentials.  This will persist a temporary credentials set to be authorised by
	 * a resource owner.
	 *
	 * @param   string  $clientId  The key of the client requesting the temporary credentials.
	 * @param   int     $lifetime  The lifetime limit of the token.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 * @throws  LogicException
	 */
	public function initialise($clientId, $lifetime = 'PT1H')
	{
		$clientSecret = $this->_signer->secretDecode($this->_request->client_secret);

		$this->_state = $this->_state->initialise($clientId, $clientSecret, $this->_request->_fetchRequestUrl(), $lifetime);
	}

	/**
	 * Perform a password authentication challenge.
	 *
	 * @param   MOauth2Client  $client  The client.
	 *
	 * @return  boolean  True if authentication is ok, false if not
	 *
	 * @since   1.0
	 */
	public function doJoomlaAuthentication(MOauth2Client $client)
	{
		return $this->_signer->doJoomlaAuthentication($client, $this->_request);
	}

	/**
	 * Method to load a set of credentials by key.
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 * @throws  InvalidArgumentException
	 */
	public function load()
	{
		// Initialise credentials_id
		$this->_table->credentials_id = 0;

		// Load the credential
		if ( isset($this->_request->response_type) && !isset($this->_request->access_token) )
		{
			// Get the correct client secret key
			$key = $this->_signer->secretDecode($this->_request->client_secret);

			$load = $this->_table->loadBySecretKey($key, $this->_request->_fetchRequestUrl());
		}
		elseif (isset($this->_request->access_token))
		{
			$load = $this->_table->loadByAccessToken($this->_request->access_token, $this->_request->_fetchRequestUrl());
		}

		if ($load === false)
		{
			throw new InvalidArgumentException('OAuth credentials not found.');
		}

		// If nothing was found we will setup a new credential state object.
		if (!$this->_table->credentials_id)
		{
			$this->_state = new MOauth2CredentialsStateNew($this->_table);

			return false;
		}

		// Cast the type for validation.
		$this->_table->type = (int) $this->_table->type;

		// If we are loading a temporary set of credentials load that state.
		if ($this->_table->type === self::TEMPORARY)
		{
			$this->_state = new MOauth2CredentialsStateTemporary($this->_table);
		}

		// If we are loading a authorised set of credentials load that state.
		elseif ($this->_table->type === self::AUTHORISED)
		{
			$this->_state = new MOauth2CredentialsStateAuthorised($this->_table);
		}

		// If we are loading a token set of credentials load that state.
		elseif ($this->_table->type === self::TOKEN)
		{
			$this->_state = new MOauth2CredentialsStateToken($this->_table);
		}

		// Unknown OAuth credential type.
		else
		{
			throw new InvalidArgumentException('OAuth credentials not found.');
		}

		return true;
	}

	/**
	 * Delete expired credentials.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function clean()
	{
		$this->_table->clean();
	}

	/**
	 * Method to revoke a set of token credentials.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 * @throws  LogicException
	 */
	public function revoke()
	{
		$this->_state = $this->_state->revoke();
	}
}
