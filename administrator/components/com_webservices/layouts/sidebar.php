<?php
/**
 * @package     Webservices.Admin
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
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
