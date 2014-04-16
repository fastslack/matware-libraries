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
 * OAuth Credentials state class for the Matware.Libraries
 *
 * @package     Matware.Libraries
 * @subpackage  OAuth2
 * @since       1.0
 */
abstract class MOauth2CredentialsState
{
	/**
	 * @var    MOauth2TableCredentials  Table object for credentials.
	 * @since  1.0
	 */
	protected $table;

	/**
	 * Object constructor.
	 *
	 * @param   MOauth2TableCredentials  $table       The database driver to use when persisting the object.
	 * @param   array                    $properties  A set of properties with which to prime the object.
	 *
	 * @codeCoverageIgnore
	 * @since   1.0
	 */
	public function __construct(MOauth2TableCredentials $table = null, array $properties = null)
	{
		// Setup the table object.
		$this->table = $table ? $table : JTable::getInstance('Credentials', 'MOauth2Table');

		// Iterate over any input properties and bind them to the object.
		if ($properties)
		{
			$this->table->bind($properties);
		}
	}

	/**
	 * Method to get a property value.
	 *
	 * @param   string  $p  The name of the property for which to return the value.
	 *
	 * @return  mixed  The property value for the given property name.
	 *
	 * @since   1.0
	 */
	public function __get($p)
	{
		if (isset($this->table->$p))
		{
			return $this->table->$p;
		}
	}

	/**
	 * Method to set a value for a property.
	 *
	 * @param   string  $p  The name of the property for which to set the value.
	 * @param   mixed   $v  The property value to set.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function __set($p, $v)
	{
		if (isset($this->table->$p))
		{
			$this->table->$p = $v;
		}
	}

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
	abstract public function authorise($resourceOwnerId, $lifetime = 0);

	/**
	 * Method to convert a set of authorised credentials to token credentials.
	 *
	 * @return  MOauth2CredentialsState
	 *
	 * @since   1.0
	 * @throws  LogicException
	 */
	abstract public function convert();

	/**
	 * Method to deny a set of temporary credentials.
	 *
	 * @return  MOauth2CredentialsState
	 *
	 * @since   1.0
	 * @throws  LogicException
	 */
	abstract public function deny();

	/**
	 * Method to initialise the credentials.  This will persist a temporary credentials set to be authorised by
	 * a resource owner.
	 *
	 * @param   string  $clientId      The key of the client requesting the temporary credentials.
	 * @param   string  $clientSecret  The secret key of the client requesting the temporary credentials.
	 * @param   string  $callbackUrl   The callback URL to set for the temporary credentials.
	 * @param   string  $lifetime      How long (DateInterval format) the temporary credentials should be valid (defaults to 60 minutes).
	 *
	 * @url http://php.net/manual/en/class.dateinterval.php
	 *
	 * @return  MOauth2CredentialsState
	 *
	 * @since   1.0
	 * @throws  LogicException
	 */
	abstract public function initialise($clientId, $clientSecret, $callbackUrl, $lifetime = 'PT1H');

	/**
	 * Method to revoke a set of token credentials.
	 *
	 * @return  MOauth2CredentialsState
	 *
	 * @since   1.0
	 * @throws  LogicException
	 */
	abstract public function revoke();

	/**
	 * Method to create the credentials in the database.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.0
	 */
	protected function create()
	{
		// Can't insert something that already has an ID.
		if ($this->table->credentials_id)
		{
			return false;
		}

		// Ensure we don't have an id to insert... use the auto-incrementor instead.
		// U unset($this->table->credentials_id);

		// Insert the object into the database.
		return $this->table->store();
	}

	/**
	 * Method to delete the credentials from the database.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function delete()
	{
		$this->table->delete();
	}

	/**
	 * Generate a random (and optionally unique) key.
	 *
	 * @param   boolean  $unique  True to enforce uniqueness for the key.
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	protected function randomKey($unique = false)
	{
		$str = md5(uniqid(rand(), true));

		if ($unique)
		{
			list ($u, $s) = explode(' ', microtime());
			$str .= dechex($u) . dechex($s);
		}

		return $str;
	}

	/**
	 * Method to update the credentials in the database.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.0
	 */
	protected function update()
	{
		if (!$this->table->credentials_id)
		{
			return false;
		}

		// Update the object into the database.
		return $this->table->store();
	}
}
