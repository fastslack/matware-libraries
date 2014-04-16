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

defined('JPATH_REDRAD') or die;

$data = $displayData;

$active = null;

if (isset($data['active']))
{
	$active = $data['active'];
}
?>
<ul class="nav nav-tabs nav-stacked">
	<li>
		<?php if ($active === 'credentials') : ?>
			<a class="active" href="<?php echo JRoute::_('index.php?option=com_webservices&view=credentials') ?>">
				<i class="icon-key"></i>
				<?php echo JText::_('COM_WEBSERVICES_CREDENTIAL_LIST_TITLE') ?>
			</a>
		<?php else : ?>
			<a href="<?php echo JRoute::_('index.php?option=com_webservices&view=credentials') ?>">
				<i class="icon-key"></i>
				<?php echo JText::_('COM_WEBSERVICES_CREDENTIAL_LIST_TITLE') ?>
			</a>
		<?php endif; ?>
	</li>
	<li>
		<?php if ($active === 'tokens') : ?>
			<a class="active" href="<?php echo JRoute::_('index.php?option=com_webservices&view=tokens') ?>">
				<i class="icon-ticket"></i>
				<?php echo JText::_('COM_WEBSERVICES_TOKENS_LIST_TITLE') ?>
			</a>
		<?php else : ?>
			<a href="<?php echo JRoute::_('index.php?option=com_webservices&view=tokens') ?>">
				<i class="icon-ticket"></i>
				<?php echo JText::_('COM_WEBSERVICES_TOKENS_LIST_TITLE') ?>
			</a>
		<?php endif; ?>
	</li>
</ul>
