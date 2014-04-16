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
defined('_JEXEC') or die( 'Restricted access' );

/**
 * OAuth2 Client Table
 *
 * @package     Matware.Libraries
 * @subpackage  OAuth2
 * @since       1.0
 */
class MOauth2TableCredentials extends JTable
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  $db  Database driver object.
	 *
	 * @since   1.0
	 */
	public function __construct($db)
	{
		parent::__construct('#__webservices_credentials', 'credentials_id', $db);
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
		// Build the query to delete the rows from the database.
		$query = $this->_db->getQuery(true);
		$query->delete('#__services_credentials')
			->where(array('expiration_date < ' . time(), 'expiration_date > 0'), 'AND')
			->where(array('temporary_expiration_date < ' . time(), 'temporary_expiration_date > 0'), 'AND');

		// Set and execute the query.
		$this->_db->setQuery($query);
		$this->_db->execute();
	}

	/**
	 * Load the credentials by key.
	 *
	 * @param   string  $key  The key for which to load the credentials.
	 * @param   string  $uri  The uri from the request.
	 *
	 * @return  void
	 *
	 * @since 1.0
	 */
	public function loadBySecretKey($key, $uri)
	{
		// Build the query to load the row from the database.
		$query = $this->_db->getQuery(true);
		$query->select('*')
		->from('#__webservices_credentials')
		->where($this->_db->quoteName('client_secret') . ' = ' . $this->_db->quote($key))
		->where($this->_db->quoteName('resource_uri') . ' = ' . $this->_db->quote($uri));

		// Set and execute the query.
		$this->_db->setQuery($query);
		$properties = $this->_db->loadAssoc();

		if (!is_array($properties))
			return false; 

		// Bind the result to the object
		if ($this->bind($properties))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Load the credentials by key.
	 *
	 * @param   string  $key  The key for which to load the credentials.
	 * @param   string  $uri  The uri from the request.
	 *
	 * @return  void
	 *
	 * @since 1.0
	 */
	public function loadByAccessToken($key, $uri)
	{
		// Build the query to load the row from the database.
		$query = $this->_db->getQuery(true);
		$query->select('*')
		->from('#__webservices_credentials')
		->where($this->_db->quoteName('access_token') . ' = ' . $this->_db->quote($key));
		//->where($this->_db->quoteName('resource_uri') . ' = ' . $this->_db->quote($uri));

		// Set and execute the query.
		$this->_db->setQuery($query);
		$properties = $this->_db->loadAssoc();

		if (!is_array($properties))
			return false; 

		// Bind the result to the object
		if ($this->bind($properties))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}
