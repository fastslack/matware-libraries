<?php
/**
 * @package     Webservices.Admin
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Token Model
 *
 * @package     Webservices.Admin
 * @subpackage  Models
 * @since       1.0
 */
class WebservicesModelToken extends JModelAdmin
{
	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $type    The table name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JTable  A JTable object
	 *
	 * @since   1.0
	 */
	public function getTable($type = 'Token', $prefix = 'WebservicesTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Abstract method for getting the form from the model.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 *
	 * @since   1.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_webservices.token', 'token', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}


	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success.
	 */
	public function save($post)
	{
		$data = new stdClass();

		// Get the tokens table
		$table = $this->getTable();

		if (trim($post['username']) != 'anonymous')
		{
			// Prepare the configuration for Oauth2 client
			$config = array(
				'url' => $post['url'],
				'username' => $post['username'],
				'password' => $post['password'],
				'signature_method' => $post['signature_method']
			);

			// Set the custom options if exists
			$options = isset($options) ? $options : new JRegistry($config);

			// Get the OAuth2 client
			$client = new MClientOauth2($options);

			// Fetch the access token
			try
			{
				$data = $client->fetchAccessToken();
			}

			catch (Exception $e)
			{
				$this->setError($e->getMessage());

				return false;
			}
		}
		else
		{
			$data->access_token = 'anonymous';
			$data->refresh_token = 'anonymous';
		}

		// Add others needed fields
		$data->resource_uri = $post['url'];
		$data->signature_method = $post['signature_method'];

		// Encode the client id
		$data->client_id = base64_encode($post['username']);

		// Use the client date
		$date = JFactory::getDate();
		$data->created = $date->toSql();

		// Set the expiration date
		if (isset($data->expires_in))
		{
			$date->add(new DateInterval($data->expires_in));
			$data->expiration_date = $date->toSql();
		}

		// Bind the data.
		if (!$table->bind((array) $data))
		{
			$this->setError($table->getError());

			return false;
		}

		// Prepare the row for saving
		$this->prepareTable($table);

		// Check the data.
		if (!$table->check())
		{
			$this->setError($table->getError());

			return false;
		}

		// Store the data.
		if (!$table->store())
		{
			$this->setError($table->getError());

			return false;
		}

		// Clear the cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission for the component.
	 *
	 * @since   12.2
	 */
	protected function canDelete($record)
	{
		$user = JFactory::getUser();
		return $user->authorise('core.delete', $this->option);
	}

	/**
	 * Method to delete one or more records.
	 *
	 * @param   array  &$pks  An array of record primary keys.
	 *
	 * @return  boolean  True if successful, false if an error occurs.
	 *
	 * @since   12.2
	 */
	public function delete(&$pks)
	{
		$dispatcher = JEventDispatcher::getInstance();
		$pks = (array) $pks;
		$table = $this->getTable();

		// Iterate the items to delete each one.
		foreach ($pks as $i => $pk)
		{

			if ($table->load($pk))
			{

				if ($this->canDelete($table))
				{

					$context = $this->option . '.' . $this->name;

					// Trigger the onContentBeforeDelete event.
					$result = $dispatcher->trigger($this->event_before_delete, array($context, $table));
					if (in_array(false, $result, true))
					{
						$this->setError($table->getError());
						return false;
					}

					if (!$table->delete($pk))
					{
						$this->setError($table->getError());
						return false;
					}

					// Trigger the onContentAfterDelete event.
					$dispatcher->trigger($this->event_after_delete, array($context, $table));

				}
				else
				{

					// Prune items that you can't change.
					unset($pks[$i]);
					$error = $this->getError();
					if ($error)
					{
						JLog::add($error, JLog::WARNING, 'jerror');
						return false;
					}
					else
					{
						JLog::add(JText::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'), JLog::WARNING, 'jerror');
						return false;
					}
				}

			}
			else
			{
				$this->setError($table->getError());
				return false;
			}
		}

		// Clear the component's cache
		$this->cleanCache();

		JFactory::getApplication()->redirect('index.php?option=com_webservices&view=tokens');
	}
}
