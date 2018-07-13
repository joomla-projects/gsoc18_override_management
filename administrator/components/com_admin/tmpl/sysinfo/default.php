<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;

// Add specific helper files for html generation
HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');

HTMLHelper::_('behavior.tabstate');
?>

<form action="<?php echo Route::_('index.php?option=com_admin&view=sysinfo'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<?php // Begin Content ?>
		<div class="col-md-12">
			<?php echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', array('active' => 'site')); ?>

			<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'site', Text::_('COM_ADMIN_SYSTEM_INFORMATION')); ?>
			<?php echo $this->loadTemplate('system'); ?>
			<?php echo HTMLHelper::_('bootstrap.endTab'); ?>

			<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'phpsettings', Text::_('COM_ADMIN_PHP_SETTINGS')); ?>
			<?php echo $this->loadTemplate('phpsettings'); ?>
			<?php echo HTMLHelper::_('bootstrap.endTab'); ?>

			<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'config', Text::_('COM_ADMIN_CONFIGURATION_FILE')); ?>
			<?php echo $this->loadTemplate('config'); ?>
			<?php echo HTMLHelper::_('bootstrap.endTab'); ?>

			<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'directory', Text::_('COM_ADMIN_DIRECTORY_PERMISSIONS')); ?>
			<?php echo $this->loadTemplate('directory'); ?>
			<?php echo HTMLHelper::_('bootstrap.endTab'); ?>

			<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'phpinfo', Text::_('COM_ADMIN_PHP_INFORMATION')); ?>
			<?php echo $this->loadTemplate('phpinfo'); ?>
			<?php echo HTMLHelper::_('bootstrap.endTab'); ?>

			<?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>
		</div>
		<input type="hidden" name="task" value="">
		<?php echo HTMLHelper::_('form.token'); ?>
		<?php // End Content ?>
	</div>
</form>
