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

jimport('joomla.environment.response');

/**
 * OAuth2 class for clients of OAuth 2.0 server.
 *
 * @package     Matware.Libraries
 * @subpackage  OAuth2
 * @since       1.0
 */
class MOauth2Client
{
	/**
	 * @var    MOauth2TableClient  JTable object for persisting the client object.
	 * @since  1.0
	 */
	protected $_table;

	/**
	 * @var    JUser  JUser object for persisting the Joomla! user.
	 * @since  1.0
	 */
	public $_identity;

	/**
	 * Object constructor.
	 *
	 * @param   MOauth2TableClient  $table       The JTable object to use when persisting the object.
	 * @param   array               $properties  A set of properties with which to prime the object.
	 *
	 * @codeCoverageIgnore
	 * @since   1.0
	 */
	public function __construct(MOauth2TableUsers $table = null, array $properties = null)
	{
		JTable::addIncludePath(JPATH_LIBRARIES . '/oauth2/table');

		// Setup the table object.
		$this->_table = $table ? $table : JTable::getInstance('Users', 'MOauth2Table');

		// Iterate over any input properties and bind them to the object.
		if ($properties)
		{
			foreach ($properties as $k => $v)
			{
				$this->_table->$k = $v;
			}
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
		if (isset($this->_table->$p))
		{
			return $this->_table->$p;
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
		if (isset($this->_table->$p))
		{
			$this->_table->$p = $v;
		}
	}

	/**
	 * Method to create the client in the database.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.0
	 */
	public function create()
	{
		// Can't insert something that already has an ID.
		if ($this->_table->client_id)
		{
			return false;
		}

		// Ensure we don't have an id to insert... use the auto-incrementor instead.
		$this->_table->client_id = null;

		return $this->_table->store();
	}

	/**
	 * Method to delete the client from the database.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function delete()
	{
		$this->_table->delete();
	}

	/**
	 * Method to load a client by id.
	 *
	 * @param   integer  $clientId  The id of the client to load.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function load($clientId)
	{
		$this->_table->load($clientId);
	}

	/**
	 * Method to load a client by key.
	 *
	 * @param   string  $key  The key of the client to load.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function loadByKey($key)
	{
		if ($this->_table->loadByKey($key))
		{
			$this->_identity = new JUser($this->_table->id);
		}
	}

	/**
	 * Method to update the client in the database.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.0
	 */
	public function update()
	{
		if (!$this->_table->client_id)
		{
			return false;
		}

		return $this->_table->store();
	}
}
