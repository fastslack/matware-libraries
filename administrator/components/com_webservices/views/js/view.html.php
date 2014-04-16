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
 * Js View
 *
 * @package     Webservices.Admin
 * @subpackage  Views
 * @since       1.0
 */
class WebservicesViewJs extends JViewLegacy
{
	/**
	 * @var  JForm
	 */
	protected $form;

	/**
	 * @var  object
	 */
	protected $item;

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
		WebservicesHelper::addSubmenu('webservices');

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
		return JText::_('COM_WEBSERVICES_TOKEN_FORM_TITLE');
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		// Get the toolbar object instance
		$bar = JToolBar::getInstance('toolbar');

		JToolbarHelper::title(JText::_('COM_WEBSERVICES_MANAGER_JS'), 'webservices.png');

		JToolbarHelper::preferences('com_webservices');

		JToolbarHelper::help('JHELP_COMPONENTS_WEBSERVICES_LINKS');
	}

}
