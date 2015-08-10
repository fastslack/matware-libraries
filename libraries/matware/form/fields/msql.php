<?php
/**
 * @version       $Id:
 * @package       Matware.Libraries
 * @subpackage    MFormFieldSQL
 * @copyright     Copyright (C) 2004 - 2014 Matware - All rights reserved.
 * @author        Matias Aguirre
 * @email         maguirre@matware.com.ar
 * @link          http://www.matware.com.ar/
 * @license       GNU/GPL http://www.gnu.org/licenses/gpl-2.0-standalone.html
 */
defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('list');

/**
 * Supports an custom SQL select list
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
class JFormFieldMSQL extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $type = 'MSQL';

	/**
	 * The keyField.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $keyField;

	/**
	 * The valueField.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $valueField;

	/**
	 * The translate.
	 *
	 * @var    boolean
	 * @since  3.2
	 */
	protected $translate = false;

	/**
	 * The query.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $query;

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string  $name  The property name for which to the the value.
	 *
	 * @return  mixed  The property value or null.
	 *
	 * @since   3.2
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'header':
			case 'keyField':
			case 'valueField':
			case 'translate':
			case 'query':
				return $this->$name;
		}

		return parent::__get($name);
	}

	/**
	 * Method to set certain otherwise inaccessible properties of the form field object.
	 *
	 * @param   string  $name   The property name for which to the the value.
	 * @param   mixed   $value  The value of the property.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'header':
			case 'keyField':
			case 'valueField':
			case 'translate':
			case 'query':
				$this->$name = (string) $value;
				break;

			default:
				parent::__set($name, $value);
		}
	}

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the <field /> tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     JFormField::setup()
	 * @since   3.2
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return)
		{
			// Check if its using the old way
			$this->query = (string) $this->element['query'];

			if (empty($this->query))
			{
				// Get the query from the form
				$query = array();
				$defaults = array();

				$query['select'] = (string) $this->element['sql_select'];

				$query['from'] = (string) $this->element['sql_from'];

				$query['join'] = isset($this->element['sql_join']) ? (string) $this->element['sql_join'] : '';

				$query['where'] = isset($this->element['sql_where']) ? (string) $this->element['sql_where'] : '';

				$query['group'] = isset($this->element['sql_group']) ? (string) $this->element['sql_group'] : '';

				$query['order'] = (string) $this->element['sql_order'];

				// Get the filters
				$filters = !empty($this->element['sql_filter']) ? explode(",", $this->element['sql_filter']) : '';

				// Get the default value for query if empty
				if (is_array($filters))
				{
					foreach ($filters as $key => $val)
					{
						$name = "sql_default_{$val}";
						$attrib = (string) $this->element[$name];

						if (!empty($attrib))
						{
							$defaults[$val] = $attrib;
						}
					}
				}

				// Process the query
				$this->query = $this->processQuery($query, $filters, $defaults);
			}

			$this->keyField   = $this->element['key_field'] ? (string) $this->element['key_field'] : 'value';
			$this->valueField = $this->element['value_field'] ? (string) $this->element['value_field'] : (string) $this->element['name'];
			$this->translate  = $this->element['translate'] ? (string) $this->element['translate'] : false;
			$this->header  = $this->element['header'] ? (string) $this->element['header'] : false;
		}

		return $return;
	}

	/**
	 * Method to process the query from form.
	 *
	 * @param   array   $conditions   The conditions from the form.
	 * @param   string  $filter       The columns to filter.
	 *
	 * @return  $query  The query object.
	 *
	 * @since   12.1
	 */
	protected function processQuery($conditions, $filter)
	{
		// Get the database object.
		$db = JFactory::getDbo();

		// Get the query object
		$query = $db->getQuery(true);

		// Select fields
		$query->select($conditions['select']);

		// From selected table
		$query->from($conditions['from']);

		// Join over the groups
		if (!empty($conditions['join']))
		{
			$query->join('LEFT', $conditions['join']);
		}

		// Group by
		if (!empty($conditions['group']))
		{
			$query->group($conditions['group']);
		}

		// Where condition
		if (!empty($conditions['where']))
		{
			$query->where($conditions['where']);
		}

		// Process the filters
		if (!empty($filter))
		{
			$html_filters = JFactory::getApplication()->getUserStateFromRequest($this->context . '.filter', 'filter', array(), 'array');

			if (!is_array($filter))
			{
				$filter = explode(",", $filter);
			}

			foreach($filter as $k => $value)
			{
				if (isset($html_filters[$value]))
				{
					$query->where("{$value} = {$html_filters[$value]}");
				}
			}
		}

		return $query;
	}

	/**
	 * Method to get the custom field options.
	 * Use the query attribute to supply a query to generate the list.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getOptions()
	{
		$options = array();

		// Initialize some field attributes.
		$key   = $this->keyField;
		$value = $this->valueField;
		$header = $this->header;

		// Get the database object.
		$db = JFactory::getDbo();

		// Set the query and get the result list.
		$db->setQuery($this->query);
		$items = $db->loadObjectlist();

		// Add header.
		if (!empty($header))
		{
			$header_title = JText::_($header);
			$options[] = JHtml::_('select.option', '', $header_title);
		}

		// Build the field options.
		if (!empty($items))
		{
			foreach ($items as $item)
			{
				if ($this->translate == true)
				{
					$options[] = JHtml::_('select.option', $item->$key, JText::_($item->$value));
				}
				else
				{
					$options[] = JHtml::_('select.option', $item->$key, $item->$value);
				}
			}
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
