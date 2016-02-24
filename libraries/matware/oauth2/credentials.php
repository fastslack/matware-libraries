<?php
/**
 * @version       $Id:
 * @package       Matware.Libraries
 * @subpackage    OAuth2
 * @copyright     Copyright (C) 2004 - 2016 Matware - All rights reserved.
 * @author        Matias Aguirre
 * @email         maguirre@matware.com.ar
 * @link          http://www.matware.com.ar/
 * @license       GNU/GPL http://www.gnu.org/licenses/gpl-2.0-standalone.html
 */
defined('_JEXEC') or die( 'Restricted access' );

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
	public $table;

	/**
	 * @var    MOauth2CredentialsState  The current credential state.
	 * @since  1.0
	 */
	public $state;

	/**
	 * @var    MOauth2ProtocolRequest   The current HTTP request.
	 * @since  1.0
	 */
	public $request;

	/**
	 * @var    MOauth2CredentialsSigner   The current credential signer.
	 * @since  1.0
	 */
	public $signal;

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
		$this->request = $request ? $request : new MOauth2ProtocolRequest;

		// Setup the database object.
		$this->table = $table ? $table : JTable::getInstance('Credentials', 'MOauth2Table');

		// Assume the base state for any credentials object to be new.
		$this->state = new MOauth2CredentialsStateNew($this->table);

		// Setup the correct signer
		$signature = isset($this->request->signature_method) ? $this->request->signature_method : 'PLAINTEXT';
		$this->signer = MOauth2CredentialsSigner::getInstance($signature);
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
		$this->state = $this->state->authorise($resourceOwnerId);
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
		$this->state = $this->state->convert();
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
		$this->state = $this->state->deny();
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
		return $this->state->callback_url;
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
		return $this->state->client_id;
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
		return $this->state->client_secret;
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
		return $this->state->temporary_token;
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
		return $this->state->access_token;
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
		return $this->state->refresh_token;
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
		return $this->state->resource_owner_id;
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
		return (int) $this->state->type;
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
		return $this->state->expiration_date;
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
		return $this->state->temporary_expiration_date;
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
		$clientSecret = $this->signer->secretDecode($this->request->client_secret);

		$this->state = $this->state->initialise($clientId, $clientSecret, $this->request->_fetchRequestUrl(), $lifetime);
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
		return $this->signer->doJoomlaAuthentication($client, $this->request);
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
		// Clean all expired tokens
		$this->table->clean();

		// Initialise credentials_id
		$this->table->credentials_id = 0;

		// Load the credential
		if ( isset($this->request->response_type) && !isset($this->request->access_token) )
		{
			// Get the correct client secret key
			$key = $this->signer->secretDecode($this->request->client_secret);

			$load = $this->table->loadBySecretKey($key, $this->request->_fetchRequestUrl());
		}
		elseif (isset($this->request->access_token))
		{
			$load = $this->table->loadByAccessToken($this->request->access_token, $this->request->_fetchRequestUrl());
		}

		if ($load === false)
		{
			throw new InvalidArgumentException('OAuth credentials not found.');
		}

		// If nothing was found we will setup a new credential state object.
		if (!$this->table->credentials_id)
		{
			$this->state = new MOauth2CredentialsStateNew($this->table);

			return false;
		}

		// Cast the type for validation.
		$this->table->type = (int) $this->table->type;

		// If we are loading a temporary set of credentials load that state.
		if ($this->table->type === self::TEMPORARY)
		{
			$this->state = new MOauth2CredentialsStateTemporary($this->table);
		}

		// If we are loading a authorised set of credentials load that state.
		elseif ($this->table->type === self::AUTHORISED)
		{
			$this->state = new MOauth2CredentialsStateAuthorised($this->table);
		}

		// If we are loading a token set of credentials load that state.
		elseif ($this->table->type === self::TOKEN)
		{
			$this->state = new MOauth2CredentialsStateToken($this->table);
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
		$this->table->clean();
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
		$this->state = $this->state->revoke();
	}
}
