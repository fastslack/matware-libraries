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
 * Token View
 *
 * @package     Webservices.Admin
 * @subpackage  Views
 * @since       1.0
 */
class WebservicesViewToken extends JViewLegacy
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
		$this->form	= $this->get('Form');
		$this->item	= $this->get('Item');

		$this->addToolbar();

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
		require_once JPATH_COMPONENT.'/helpers/webservices.php';

		$state	= $this->get('State');
		$canDo	= WebservicesHelper::getActions($state->get('filter.category_id'));
		$user	= JFactory::getUser();
		// Get the toolbar object instance
		$bar = JToolBar::getInstance('toolbar');

		JToolbarHelper::title(JText::_('COM_WEBSERVICES_MANAGER_TOKEN'), 'webservices.png');

		// If not checked out, can save the item.
		if (($canDo->get('core.edit')||(count($user->getAuthorisedCategories('com_webservices', 'core.create')))))
		{
			JToolbarHelper::apply('token.apply');
			JToolbarHelper::save('token.save');
		}

		if (empty($this->item->id))
		{
			JToolbarHelper::cancel('token.cancel');
		}
		else
		{
			JToolbarHelper::cancel('token.cancel', 'JTOOLBAR_CLOSE');
		}
	}

}
