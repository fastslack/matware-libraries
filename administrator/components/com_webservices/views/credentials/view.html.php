<?php
/**
 * @package     Webservices.Admin
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Credentials View
 *
 * @package     Webservices.Admin
 * @subpackage  Views
 * @since       1.0
 */
class WebservicesViewCredentials extends JViewLegacy
{
	/**
	 * @var  array
	 */
	protected $items;

	/**
	 * @var  object
	 */
	protected $state;

	/**
	 * @var  JPagination
	 */
	protected $pagination;

	/**
	 * @var  array
	 */
	protected $status_options = array();

	/**
	 * @var  array
	 */
	protected $status = array();

	/**
	 * Display method
	 *
	 * @param   string  $tpl  template name
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		require_once JPATH_COMPONENT.'/helpers/webservices.php';

		// Get items
		$this->items = $this->get('Items');
		$this->state = $this->get('State');
		$this->pagination = $this->get('Pagination');

		WebservicesHelper::addSubmenu('webservices');

		$options = array();
		$options[] = JHtml::_('select.option', '0', 'JTEMPORARY');
		$options[] = JHtml::_('select.option', '1', 'JAUTHORISED');
		$options[] = JHtml::_('select.option', '2', 'JTOKEN');
		$this->status_options = $options;

		$options = array();
		$options[] = 'JTEMPORARY';
		$options[] = 'JAUTHORISED';
		$options[] = 'JTOKEN';
		$this->status = $options;

		// Add the toolbar
		$this->addToolbar();

		$this->sidebar = JHtmlSidebar::render();

		// Display the template
		parent::display($tpl);
	}

	/**
	 * Get the view title.
	 *
	 * @return  string  The view title.
	 */
	public function getTitle()
	{
		return JText::_('COM_WEBSERVICES_CREDENTIAL_LIST_TITLE');
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		$state	= $this->get('State');
		$canDo	= WebservicesHelper::getActions($state->get('filter.category_id'));
		$user	= JFactory::getUser();
		// Get the toolbar object instance
		$bar = JToolBar::getInstance('toolbar');

		JToolbarHelper::title(JText::_('COM_WEBSERVICES_MANAGER_WEBSERVICES'), 'webservices.png');

		if ($canDo->get('core.delete'))
		{
			JToolbarHelper::deleteList('', 'credentials.delete', 'COM_WEBSERVICES_TOOLBAR_REVOKE');
		}

		JToolbarHelper::divider();

		if ($canDo->get('core.admin'))
		{
			JToolbarHelper::preferences('com_webservices');
		}

		JToolbarHelper::help('JHELP_COMPONENTS_WEBSERVICES_LINKS');

		JHtmlSidebar::setAction('index.php?option=com_webservices&view=credentials');
	}

	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 *
	 * @since   3.0
	 */
	protected function getSortFields()
	{
		return array(
			'a.ordering' => JText::_('JGRID_HEADING_ORDERING'),
			'a.state' => JText::_('JSTATUS'),
			'a.title' => JText::_('JGLOBAL_TITLE'),
			'a.access' => JText::_('JGRID_HEADING_ACCESS'),
			'a.hits' => JText::_('JGLOBAL_HITS'),
			'a.language' => JText::_('JGRID_HEADING_LANGUAGE'),
			'a.id' => JText::_('JGRID_HEADING_ID')
		);
	}
}
