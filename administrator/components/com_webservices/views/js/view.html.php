<?php
/**
 * @version       $Id: 
 * @package       Matware.Component
 * @subpackage    Webservices
 * @copyright     Copyright (C) 2004 - 2014 Matware - All rights reserved.
 * @author        Matias Aguirre
 * @email         maguirre@matware.com.ar
 * @link          http://www.matware.com.ar/
 * @license       GNU/GPL http://www.gnu.org/licenses/gpl-2.0-standalone.html
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
