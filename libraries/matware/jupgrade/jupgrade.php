<?php
/**
* jUpgradePro
*
* @version $Id:
* @package jUpgradePro
* @copyright Copyright (C) 2004 - 2017 Matware. All rights reserved.
* @author Matias Aguirre
* @email maguirre@matware.com.ar
* @link http://www.matware.com.ar/
* @license GNU General Public License version 2 or later; see LICENSE
*/
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

/**
 * jUpgradePro utility class for migrations
 *
 * @package		Matware
 * @subpackage	com_jupgradepro
 */
class JUpgradepro
{
	/**
	 * @var
	 * @since  3.0
	 */
	public $params = null;

	/**
	 * @var
	 * @since  3.0
	 */
	public $ready = true;

	/**
	 * @var
	 * @since  3.0
	 */
	public $_db = null;

	/**
	 * @var
	 * @since  3.0
	 */
	public $_driver = null;

	/**
	 * @var
	 * @since  3.0
	 */
	public $_version = null;

	/**
	 * @var
	 * @since  3.0
	 */
	public $_total = null;

	/**
	 * @var	array
	 * @since  3.0
	 */
	protected $_step = null;

	/**
	 * @var    array  List of extensions steps
	 * @since  12.1
	 */
	private $extensions_steps = array('extensions', 'ext_components', 'ext_modules', 'ext_plugins');

	/**
	 * @var bool Can drop
	 * @since	0.4.
	 */
	public $canDrop = false;

	function __construct(JUpgradeproStep $step = null)
	{
		// Set the current step
		$this->_step = $step;

		jimport('legacy.component.helper');
		jimport('cms.version.version');
		JLoader::import('helpers.jupgradepro', JPATH_COMPONENT_ADMINISTRATOR);

		// Get the component parameters
		$this->params = JUpgradeproHelper::getParams();

		// Create the dabatase instance for this installation
		$this->_db = JFactory::getDBO();

		// Get the driver
		JLoader::register('JUpgradeproDriver', JPATH_LIBRARIES.'/matware/jupgrade/driver.php');

		if ($this->_step instanceof JUpgradeproStep) {
			$this->_step->table = $this->getSourceTable();
		}

		// Initialize the driver
		$this->_driver = JUpgradeproDriver::getInstance($step);

		// Get the total
		if (!empty($step->source)) {
			$this->_total = JUpgradeproHelper::getTotal($step);
		}

		// Set timelimit to 0
		if(!@ini_get('safe_mode')) {
			if (!empty($this->params->timelimit)) {
				set_time_limit(0);
			}
		}

		// Make sure we can see all errors.
		if (!empty($this->params->error_reporting)) {
			error_reporting(E_ALL);
			@ini_set('display_errors', 1);
		}

		// MySQL grants check
		$query = "SHOW GRANTS FOR CURRENT_USER";
		$this->_db->setQuery( $query );
		$list = $this->_db->loadRowList();
		$grant = empty($list[1][0]) ? $list[0][0] : $list[1][0];

		if (strpos($grant, 'DROP') == true || strpos($grant, 'ALL') == true) {
			$this->canDrop = true;
		}

		// Prevent Joomla! uri errors
		if (!isset($_SERVER['HTTP_HOST']))
		{
			$_SERVER['HTTP_HOST'] = '';
		}
	}

	/**
	 *
	 * @param   stdClass   $options  Parameters to be passed to the database driver.
	 *
	 * @return  jUpgradePro  A jUpgradePro object.
	 *
	 * @since  3.0.0
	 */
	static function getInstance(JUpgradeproStep $step = null)
	{
		if ($step == null) {
			return false;
		}

		// Correct the 3rd party extensions class name
		if (isset($step->element)) {
			$step->class = empty($step->class) ? 'JUpgradeproExtensions' : $step->class;
		}

		// Getting the class name
		$class = '';
		if (isset($step->class)) {
			$class = $step->class;
		}

		// Require the correct file
		if (is_object($step))
		{
			JUpgradeproHelper::requireClass($step->name, $step->xmlpath, $class);
		}

		// If the class still doesn't exist we have nothing left to do but throw an exception.  We did our best.
		if (!class_exists($class))
		{
			$class = 'JUpgradepro';
		}

		// Create our new jUpgradePro connector based on the options given.
		try
		{
			$instance = new $class($step);
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException(sprintf('Unable to load JUpgradepro object: %s', $e->getMessage()));
		}

		return $instance;
	}

	/**
	 * The public entry point for the class.
	 *
	 * @return	boolean
	 * @since	0.4.
	 */
	public function upgrade()
	{
		try
		{
			$this->setDestinationData();
		}
		catch (Exception $e)
		{
			throw new Exception($e->getMessage());
		}

		return true;
	}

	/**
	 * Sets the data in the destination database.
	 *
	 * @return	void
	 * @since	0.4.
	 * @throws	Exception
	 */
	protected function setDestinationData($rows = false)
	{
		// Initialise Joomla! application
		$app = JFactory::getApplication('administrator');

		// Get step name
		$name = $this->_step->_getStepName();

		// Get method
		$method = $this->params->method;

		// Before migrate hook
		if ($this->_step->first == true && $this->_step->cid == 0) {
			try
			{
				if (method_exists($this, 'beforeHook')) {
					$this->beforeHook();
				}
			}
			catch (Exception $e)
			{
				throw new Exception($e->getMessage());
			}
		}

		// Get the source data.
		if ($rows === false) {
			$rows = $this->dataSwitch();
		}

		// Call to database method hook
		if ( $method == 'database' OR $method == 'database_all') {
			if (method_exists($this, 'databaseHook')) {
				$rows = $this->databaseHook($rows);
			}
		}

		// Call structure hook to create the db table
		if ($this->_step->first == true && $this->_step->cid == 0) {

			$structureHook = 'structureHook_'.$name;

			if (method_exists($this, $structureHook)) {
				try
				{
					$this->$structureHook();
				}
				catch (Exception $e)
				{
					throw new Exception($e->getMessage());
				}
			}
		}

		// Calling the data modificator hook
		$dataHookFunc = 'dataHook_'.$name;

		// If method exists call the custom dataHook
		if (method_exists($this, $dataHookFunc)) {
			try
			{
				$rows = $this->$dataHookFunc($rows);
			}
			catch (Exception $e)
			{
				throw new Exception($e->getMessage());
			}
		// If method not exists call the default dataHook
		}else{
			try
			{
				$rows = $this->dataHook($rows);
			}
			catch (Exception $e)
			{
				throw new Exception($e->getMessage());
			}
		}

		// Insert the data to the target
		if ($rows !== false) {

			try
			{
				$this->ready = $this->insertData($rows);
			}
			catch (Exception $e)
			{
				throw new Exception($e->getMessage());
			}
		}

		// Load the step object
		$this->_step->_load($this->_step->name);

		// Call after migration hook
		if ($this->getTotal() == $this->_step->cid) {
			$this->ready = $this->afterHook($rows);
		}

		// Call after all steps hook
		if (empty($this->_step->laststep) && $this->_step->cache == 0 && $this->getTotal() == $this->_step->cid) {
			$this->ready = $this->afterAllStepsHook();
		}

		return $this->ready;
	}

	/**
	 * dataSwitch
	 *
	 * @return	array	The requested data
	 * @since	3.0.0
	 * @throws	Exception
	 */
	protected function dataSwitch($name = null)
	{
		// Init rows variable
		$rows = array();

		// Get the method and chunk
		$method = $this->params->method;
		$chunk = $this->params->chunk_limit;

		switch ($method) {
			case 'rest':
				$name = ($name == null) ? $this->_step->_getStepName() : $name;

				$rows = $this->_driver->getSourceDataRestList($name, $chunk);
		    break;
			case 'database':
		    $rows = $this->_driver->getSourceDatabase();
		    break;
		}

		return $rows;
	}

	/**
	 * insertData
	 *
	 * @return	void
	 * @since	3.0.0
	 * @throws	Exception
	 */
	protected function insertData($rows)
	{
		$table = $this->getDestinationTable();

		// Replacing the table name if xml exists
		$table = $this->replaceTable($table);

		if (is_array($rows)) {

			$total = count($rows);

			foreach ($rows as $row)
			{
				if ($row != false) {
					// Convert the array into an object.
					$row = (object) $row;

					try	{
						$this->_db->insertObject($table, $row);

						$this->_step->_nextID($total);

					}	catch (Exception $e) {

						$this->_step->_nextID($total);
						$this->saveError($e->getMessage());

						continue;
					}
				}
			}
		}else if (is_object($rows)) {

			if ($rows != false) {
				try
				{
					$this->_db->insertObject($table, $rows);
				}
				catch (Exception $e)
				{
					throw new Exception($e->getMessage());
				}
			}

		}

		return !empty($this->_step->error) ? false : true;
	}

	/*
	 * Get query condition's
	 *
	 * @return	void
	 * @since	3.0.0
	 * @throws	Exception
	 */
	public static function getConditionsHook()
	{
		$conditions = array();

		$conditions['select'] = '*';

		$conditions['where'] = array();

		return $conditions;
	}

	/*
	 * Fake method of dataHook if it not exists
	 *
	 * @return	void
	 * @since	3.0.0
	 * @throws	Exception
	 */
	public function dataHook($rows)
	{
		// Do customisation of the params field here for specific data.
		return $rows;
	}

	/*
	 * Fake method after hooks
	 *
	 * @return	void
	 * @since	3.0.0
	 * @throws	Exception
	 */
	public function afterHook()
	{
		return true;
	}

	/**
	 * Hook to do custom migration after all steps
	 *
	 * @return	boolean Ready
	 * @since	1.1.0
	 */
	protected function afterAllStepsHook()
	{
		return true;
	}

	/**
 	* Get the table structure
	*/
	public function getTableStructure() {

		// Getting the source table
		$table = $this->getSourceTable();

		// Getting the structure
		if ($this->params->method == 'database') {
			$result = $this->_driver->_db_old->getTableCreate($table);
			$structure = str_replace($this->_driver->_db_old->getPrefix(), "#__", "{$result[$table]} ;\n\n");
		}else if ($this->params->method == 'rest') {
			$structure = $this->_driver->requestRest("tablestructure", str_replace('#__', '', $table));
		}

		// Create only if not exists
		$structure = str_replace('CREATE TABLE', 'CREATE TABLE IF NOT EXISTS', $structure);

		// Replacing the table name from xml
		$replaced_table = $this->replaceTable($table);

		if ($replaced_table != $table) {
			$structure = str_replace($table, $replaced_table, $structure);
		}

		// Inserting the structure to new site
		$this->_db->setQuery($structure);
		$this->_db->query();

		return true;
	}

	/**
	 * Replace table name
	 *
	 * @return	string The replaced table
	 * @since 3.0.3
	 * @throws	Exception
	 */
	protected function replaceTable($table, $structure = null) {

		$replaced_table = $table;

		// Replace table name from xml
		$replace = explode("|", $this->_step->replace);

		if (count($replace) > 1) {
			$replaced_table = str_replace($replace[0], $replace[1], $table);
		}

		return $replaced_table;
	}

	/**
	 * @return  string	The destination table key name
	 *
	 * @since   3.0
	 */
	public function getDestKeyName()
	{
		$table = $this->getDestinationTable();

		$query = "SHOW KEYS FROM {$table} WHERE Key_name = 'PRIMARY'";
		$this->_db->setQuery( $query );
		$keys = $this->_db->loadObjectList();

		return !empty($keys) ? $keys[0]->Column_name : '';
	}

	/**
	 * @return  bool	Check if the value exists in the table
	 *
	 * @since   3.0
	 */
	public function valueExists($row, $fields)
	{
		$table = $this->getSourceTable();
		$key = $this->getDestKeyName();
		$value = $row->$key;

		$conditions = array();
		foreach ($fields as $field) {
			$conditions[] = "{$field} = {$row->$field}";
		}

		$where = count( $conditions ) ? 'WHERE ' . implode( ' AND ', $conditions ) : '';

		$query = "SELECT `{$key}` FROM {$table} {$where} LIMIT 1";
		$this->_db->setQuery( $query );
		$exists = $this->_db->loadResult();

		return empty($exists) ? false : true;
	}

	/**
	 * TODO: Replace this function: get the new id directly
	 * Internal function to get original database prefix
	 *
	 * @return	an original database prefix
	 * @since	0.5.3
	 * @throws	Exception
	 */
	public function getMapList($table = 'categories', $section = false, $custom = false)
	{
		// Getting the categories id's
		$query = "SELECT *"
		." FROM #__jupgradepro_{$table}";

		if ($section !== false) {
			$query .= " WHERE section = '{$section}'";
		}

		if ($custom !== false) {
			$query .= " WHERE {$custom}";
		}

		$this->_db->setQuery($query);
		$data = $this->_db->loadObjectList('old');

		// Check for query error.
		$error = $this->_db->getErrorMsg();

		if ($error) {
			throw new Exception($error);
			return false;
		}

		return $data;
	}

	/**
	 * Internal function to get original database prefix
	 *
	 * @return	an original database prefix
	 * @since	0.5.3
	 * @throws	Exception
	 */
	public function getMapListValue($table = 'categories', $section = false, $custom = false)
	{
		// Getting the categories id's
		$query = "SELECT new"
		." FROM #__jupgradepro_{$table}";

		if ($section !== false)
		{
			if ($section == 'categories')
			{
				$query .= " WHERE (section REGEXP '^[\-\+]?[[:digit:]]*\.?[[:digit:]]*$' OR section = 'com_section')";
			}
			else
			{
				$query .= " WHERE section = '{$section}'";
			}
		}

		if ($custom !== false) {
			if ($section !== false) {
				$query .= " AND {$custom}";
			}else{
				$query .= " WHERE {$custom}";
			}
		}

		$this->_db->setQuery($query);
		$data = $this->_db->loadResult();

		// Check for query error.
		$error = $this->_db->getErrorMsg();

		if ($error) {
			throw new Exception($error);
			return false;
		}

		return $data;
	}

	/**
	 * Fix the aliases
	 *
	 * @param	string	$table	The table to search.
	 * @param	array   $row		The row values
	 *
	 * @return	array	The row values
	 * @since	3.6.2
	 * @throws	Exception
	 */
	public function fixAlias($table, $row)
	{
		$row = (array) $row;

		// Get alias from title if its empty
		if ($row['alias'] == "")
		{
			$row['alias'] = JFilterOutput::stringURLSafe($row['title']);
		}

		// Transliterate title
		if (isset($row['title']) && $row['title'] != "")
		{
			$title_ascii = JFilterOutput::stringURLSafe($this->url_slug($row['title']));
		}

		// Check if extension exists
		$row['extension'] = isset($row['extension']) ? $row['extension'] : false;

		// Check if has duplicated aliases
		$alias = $this->getAlias($table, $row['alias'], $row['extension']);

		// Prevent MySQL duplicate error
		// @@ Duplicate entry for key 'idx_client_id_parent_id_alias_language'
		if (!empty($alias))
		{
			$row['alias'] = $alias."-".rand(0, 999999);
		} else if (isset($title_ascii)) {
			$alias = $this->getAlias($table, $title_ascii, $row['extension']);
			$row['alias'] = (!empty($alias)) ? $alias."-".rand(0, 999999) : $title_ascii;
		}

		return $row['alias'];
	}

	/**
	 * Get the alias if its duplicated
	 *
	 * @param	string	$table	The table to search.
	 * @param	string	$alias	The alias to search.
	 * @param	string	$extension	The extension to filter.
	 *
	 * @return	string	The alias
	 * @since	3.2.1
	 * @throws	Exception
	 */
	public function getAlias($table, $alias, $extension = false)
	{
		$alias = strtolower($alias);

		if (is_numeric ( $extension ))
		{
			$extension = 'com_content';
		}

		$query = $this->_db->getQuery(true);
		$query->select('alias');
		$query->from($table);
		if ($extension !== false) {
			$query->where("extension = '{$extension}'");
		}
		$query->where("alias RLIKE '^{$alias}$' OR alias RLIKE '^{$alias}[~]$'");
		$query->order('alias DESC');
		$query->limit(1);
		$this->_db->setQuery($query);

		return (string) $this->_db->loadResult();
	}

	/**
	 * Converts the params fields into a JSON string.
	 *
	 * @param	string	$params	The source text definition for the parameter field.
	 *
	 * @return	string	A JSON encoded string representation of the parameters.
	 * @since	0.4.
	 * @throws	Exception from the convertParamsHook.
	 */
	protected function convertParams($params, $hook = true)
	{
		$temp	= new JRegistry($params);
		$object	= $temp->toObject();

		// Fire the hook in case this parameter field needs modification.
		if ($hook === true) {
			$this->convertParamsHook($object);
		}

		$return = json_encode($object);

		return $return;
	}

	/**
	 * A hook to be able to modify params prior as they are converted to JSON.
	 *
	 * @param	object	$object	A reference to the parameters as an object.
	 *
	 * @return	void
	 * @since	0.4.
	 * @throws	Exception
	 */
	protected function convertParamsHook(&$object)
	{
		// Do customisation of the params field here for specific data.
	}

	/**
	 * Converts the metadata fields into a JSON string.
	 *
	 * @param	string	$params	The source text definition for the parameter field.
	 *
	 * @return	string	A JSON encoded string representation of the parameters.
	 * @since	  3.6.2
	 * @throws	Exception from the convertMetadataHook.
	 */
	protected function convertMetadata($params, $hook = true)
	{
		$temp	= new JRegistry($params);
		$object	= $temp->toObject();

		// Fire the hook in case this parameter field needs modification.
		if ($hook === true) {
			$this->convertMetadataHook($object);
		}

		$return = json_encode($object);

		return $return;
	}

	/**
	 * A hook to be able to modify params prior as they are converted to JSON.
	 *
	 * @param	object	$object	A reference to the parameters as an object.
	 *
	 * @return	void
	 * @since   3.6.2
	 * @throws	Exception
	 */
	protected function convertMetadataHook(&$object)
	{
		// Do customisation of the params field here for specific data.
	}

	/**
	 * Internal function to get the component settings
	 *
	 * @return	an object with global settings
	 * @since	0.5.7
	 */
	public function getParams()
	{
		return $this->params;
	}

	/**
	 * Get total of the rows of the table
	 *
	 * @access	public
	 * @return	int	The total of rows
	 */
	public function getTotal()
	{
		return $this->_total;
	}

	/**
	 * @return  string	The table name
	 *
	 * @since   3.0
	 */
	public function getSourceTable()
	{
		return '#__'.$this->_step->source;
	}

	/**
	 * @return  string	The table name
	 *
	 * @since   3.0
	 */
	public function getDestinationTable()
	{
		return '#__'.$this->_step->destination;
	}

	/**
	 * Save drivers errors
	 *
	 * @return  true
	 *
	 * @since   3.0.0
	 */
	public function saveError($error)
	{
		$query = $this->_db->getQuery(true);
		$query->insert('#__jupgradepro_errors')
			->columns('`message`')
			->values($this->_db->quote($error));
		$this->_db->setQuery($query);
		$this->_db->execute();

		return true;
	}

	/**
	 * Transliterate string
	 *
	 * @access  public
	 * @param   string  An string to be transliterated
	 * @param   array   An array with options
	 * @since	3.6.2
	 */
	public function url_slug($str, $options = array())
	{
		// Make sure string is in UTF-8 and strip invalid UTF-8 characters
		$str = mb_convert_encoding((string)$str, 'UTF-8', mb_list_encodings());

		$defaults = array(
			'delimiter' => '-',
			'limit' => null,
			'lowercase' => true,
			'replacements' => array(),
			'transliterate' => true,
		);

		// Merge options
		$options = array_merge($defaults, $options);

		$char_map = array(
			// Latin
			'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'AE', 'Ç' => 'C',
			'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
			'Ð' => 'D', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ő' => 'O',
			'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ű' => 'U', 'Ý' => 'Y', 'Þ' => 'TH',
			'ß' => 'ss',
			'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'ae', 'ç' => 'c',
			'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
			'ð' => 'd', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ő' => 'o',
			'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ű' => 'u', 'ý' => 'y', 'þ' => 'th',
			'ÿ' => 'y',
			// Latin symbols
			'©' => '(c)',
			// Greek
			'Α' => 'A', 'Β' => 'B', 'Γ' => 'G', 'Δ' => 'D', 'Ε' => 'E', 'Ζ' => 'Z', 'Η' => 'H', 'Θ' => '8',
			'Ι' => 'I', 'Κ' => 'K', 'Λ' => 'L', 'Μ' => 'M', 'Ν' => 'N', 'Ξ' => '3', 'Ο' => 'O', 'Π' => 'P',
			'Ρ' => 'R', 'Σ' => 'S', 'Τ' => 'T', 'Υ' => 'Y', 'Φ' => 'F', 'Χ' => 'X', 'Ψ' => 'PS', 'Ω' => 'W',
			'Ά' => 'A', 'Έ' => 'E', 'Ί' => 'I', 'Ό' => 'O', 'Ύ' => 'Y', 'Ή' => 'H', 'Ώ' => 'W', 'Ϊ' => 'I',
			'Ϋ' => 'Y',
			'α' => 'a', 'β' => 'b', 'γ' => 'g', 'δ' => 'd', 'ε' => 'e', 'ζ' => 'z', 'η' => 'h', 'θ' => '8',
			'ι' => 'i', 'κ' => 'k', 'λ' => 'l', 'μ' => 'm', 'ν' => 'n', 'ξ' => '3', 'ο' => 'o', 'π' => 'p',
			'ρ' => 'r', 'σ' => 's', 'τ' => 't', 'υ' => 'y', 'φ' => 'f', 'χ' => 'x', 'ψ' => 'ps', 'ω' => 'w',
			'ά' => 'a', 'έ' => 'e', 'ί' => 'i', 'ό' => 'o', 'ύ' => 'y', 'ή' => 'h', 'ώ' => 'w', 'ς' => 's',
			'ϊ' => 'i', 'ΰ' => 'y', 'ϋ' => 'y', 'ΐ' => 'i',
			// Turkish
			'Ş' => 'S', 'İ' => 'I', 'Ç' => 'C', 'Ü' => 'U', 'Ö' => 'O', 'Ğ' => 'G',
			'ş' => 's', 'ı' => 'i', 'ç' => 'c', 'ü' => 'u', 'ö' => 'o', 'ğ' => 'g',
			// Russian
			'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'Yo', 'Ж' => 'Zh',
			'З' => 'Z', 'И' => 'I', 'Й' => 'J', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O',
			'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',
			'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sh', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu',
			'Я' => 'Ya',
			'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'zh',
			'з' => 'z', 'и' => 'i', 'й' => 'j', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o',
			'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c',
			'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sh', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu',
			'я' => 'ya',
			// Ukrainian
			'Є' => 'Ye', 'І' => 'I', 'Ї' => 'Yi', 'Ґ' => 'G',
			'є' => 'ye', 'і' => 'i', 'ї' => 'yi', 'ґ' => 'g',
			// Czech
			'Č' => 'C', 'Ď' => 'D', 'Ě' => 'E', 'Ň' => 'N', 'Ř' => 'R', 'Š' => 'S', 'Ť' => 'T', 'Ů' => 'U',
			'Ž' => 'Z',
			'č' => 'c', 'ď' => 'd', 'ě' => 'e', 'ň' => 'n', 'ř' => 'r', 'š' => 's', 'ť' => 't', 'ů' => 'u',
			'ž' => 'z',
			// Polish
			'Ą' => 'A', 'Ć' => 'C', 'Ę' => 'e', 'Ł' => 'L', 'Ń' => 'N', 'Ó' => 'o', 'Ś' => 'S', 'Ź' => 'Z',
			'Ż' => 'Z',
			'ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n', 'ó' => 'o', 'ś' => 's', 'ź' => 'z',
			'ż' => 'z',
			// Latvian
			'Ā' => 'A', 'Č' => 'C', 'Ē' => 'E', 'Ģ' => 'G', 'Ī' => 'i', 'Ķ' => 'k', 'Ļ' => 'L', 'Ņ' => 'N',
			'Š' => 'S', 'Ū' => 'u', 'Ž' => 'Z',
			'ā' => 'a', 'č' => 'c', 'ē' => 'e', 'ģ' => 'g', 'ī' => 'i', 'ķ' => 'k', 'ļ' => 'l', 'ņ' => 'n',
			'š' => 's', 'ū' => 'u', 'ž' => 'z'
		);

		// Make custom replacements
		$str = preg_replace(array_keys($options['replacements']), $options['replacements'], $str);

		// Transliterate characters to ASCII
		if ($options['transliterate']) {
			$str = str_replace(array_keys($char_map), $char_map, $str);
		}

		// Replace non-alphanumeric characters with our delimiter
		$str = preg_replace('/[^\p{L}\p{Nd}]+/u', $options['delimiter'], $str);

		// Remove duplicate delimiters
		$str = preg_replace('/(' . preg_quote($options['delimiter'], '/') . '){2,}/', '$1', $str);

		// Truncate slug to max. characters
		$str = mb_substr($str, 0, ($options['limit'] ? $options['limit'] : mb_strlen($str, 'UTF-8')), 'UTF-8');

		// Remove delimiter from ends
		$str = trim($str, $options['delimiter']);

		return $options['lowercase'] ? mb_strtolower($str, 'UTF-8') : $str;
	}

} // end class
