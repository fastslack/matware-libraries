<?php
/**
 * @package     Joomla.Services
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

class ComponentContentArticlesGet extends ApiControllerItem
{
	/**
	 * Constructor.
	 *
	 * @param   JInput            $input  The input object.
	 * @param   JApplicationBase  $app    The application object.
	 */
	public function __construct(JInput $input = null, JApplicationBase $app = null)
	{
		parent::__construct($input, $app);

		// Use the default database.
		$this->setDatabase();

		// Set the controller options.
		$serviceOptions = array(
			'contentType' => 'application/vnd.joomla.item.v1; schema=articles.v1',
			'describedBy' => 'http://docs.joomla.org/Schemas/articles/v1',
			'primaryRel'  => 'joomla:articles',
			'resourceMap' => __DIR__ . '/resource.json',
			'self' 		  => '/joomla:articles/' . (int) $this->input->get('id'),
			'tableName'   => '#__content',
		);

		$this->setOptions($serviceOptions);
	}

	/**
	 * Get database query.
	 *
	 * May be overridden in child classes.
	 *
	 * @param  string  $table  Primary table name.
	 *
	 * @return JDatabaseDriver object.
	 */
	public function getQuery($table)
	{
		// Get the user
		$user = $this->app->getIdentity();

		// Get the base query.
		$query = parent::getQuery($table);

		// Filter by access level.
		if ($user->guest != 1)
		{
			$groups = implode(',', $user->getAuthorisedViewLevels());
			$query->where('access IN (' . $groups . ')');
		}
		else if ($user->guest == 1)
		{
			$query->where('access = 1');
		}

		return $query;
	}
}
