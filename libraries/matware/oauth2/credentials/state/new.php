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
 * OAuth New Credentials class for the Matware.Libraries
 *
 * @package     Matware.Libraries
 * @subpackage  OAuth2
 * @since       1.0
 */
class MOauth2CredentialsStateNew extends MOauth2CredentialsState
{
	/**
	 * Method to authorise the credentials.  This will persist a temporary credentials set to be authorised by
	 * a resource owner.
	 *
	 * @param   integer  $resourceOwnerId  The id of the resource owner authorizing the temporary credentials.
	 * @param   integer  $lifetime         How long the permanent credentials should be valid (defaults to forever).
	 *
	 * @return  MOauth2CredentialsState
	 *
	 * @since   1.0
	 * @throws  LogicException
	 */
	public function authorise($resourceOwnerId, $lifetime = 0)
	{
		throw new LogicException('Only temporary credentials can be authorised.');
	}

	/**
	 * Method to convert a set of authorised credentials to token credentials.
	 *
	 * @return  MOauth2CredentialsState
	 *
	 * @since   1.0
	 * @throws  LogicException
	 */
	public function convert()
	{
		throw new LogicException('Only authorised credentials can be converted.');
	}

	/**
	 * Method to deny a set of temporary credentials.
	 *
	 * @return  MOauth2CredentialsState
	 *
	 * @since   1.0
	 * @throws  LogicException
	 */
	public function deny()
	{
		throw new LogicException('Only temporary credentials can be denied.');
	}

	/**
	 * Method to initialise the credentials.  This will persist a temporary credentials set to be authorised by
	 * a resource owner.
	 *
	 * @param   string   $clientId      The key of the client requesting the temporary credentials.
	 * @param   string   $clientSecret  The secret key of the client requesting the temporary credentials.
	 * @param   string   $callbackUrl   The callback URL to set for the temporary credentials.
	 * @param   string   $lifetime      How long (DateInterval format) the temporary credentials should be valid (defaults to 60 minutes).
	 *
	 * @url http://php.net/manual/en/class.dateinterval.php
	 *
	 * @return  MOauth2CredentialsState
	 *
	 * @since   1.0
	 * @throws  LogicException
	 */
	public function initialise($clientId, $clientSecret, $callbackUrl, $lifetime = 'PT4H')
	{
		// Setup the properties for the credentials.
		$this->table->credentials_id = null;
		$this->table->callback_url = $callbackUrl;
		$this->table->client_id = $clientId;
		$this->table->client_secret = $clientSecret;
		$this->table->client_ip = $_SERVER['REMOTE_ADDR'];
		$this->table->temporary_token = $this->randomKey();
		$this->table->resource_uri = $callbackUrl;
		$this->table->type = MOauth2Credentials::TEMPORARY;

		// Set the correct date adding the lifetime
		// @@ TODO: Fix static timezone
		$date = JFactory::getDate('now', 'America/Buenos_Aires');
		$date->add(new DateInterval($lifetime));
		$this->table->expiration_date = $date->toSql(true);

		// Persist the object in the database.
		$this->create();

		return new MOauth2CredentialsStateTemporary($this->table);
	}

	/**
	 * Method to revoke a set of token credentials.
	 *
	 * @return  MOauth2CredentialsState
	 *
	 * @since   1.0
	 * @throws  LogicException
	 */
	public function revoke()
	{
		throw new LogicException('Only token credentials can be revoked.');
	}
}
