<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('behavior.multiselect');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>
<div id="installer-update" class="clearfix">
	<form action="<?php echo Route::_('index.php?option=com_installer&view=update'); ?>" method="post" name="adminForm" id="adminForm">
		<div class="row">
			<div id="j-sidebar-container" class="col-md-2">
				<?php echo $this->sidebar; ?>
			</div>
			<div class="col-md-10">
				<div id="j-main-container" class="j-main-container">
					<?php if ($this->showMessage) : ?>
						<?php echo $this->loadTemplate('message'); ?>
					<?php endif; ?>

					<?php if ($this->ftp) : ?>
						<?php echo $this->loadTemplate('ftp'); ?>
					<?php endif; ?>
					<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
					<?php if (empty($this->items)) : ?>
						<joomla-alert type="info"><?php echo Text::_('COM_INSTALLER_MSG_UPDATE_NOUPDATES'); ?></joomla-alert>
					<?php else : ?>
						<table class="table">
							<thead>
							<tr>
								<td style="width:1%" class="nowrap">
									<?php echo HTMLHelper::_('grid.checkall'); ?>
								</td>
								<th scope="col" class="nowrap">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_INSTALLER_HEADING_NAME', 'u.name', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" class="nowrap text-center">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_INSTALLER_HEADING_LOCATION', 'client_translated', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" class="nowrap text-center">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_INSTALLER_HEADING_TYPE', 'type_translated', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" class="nowrap d-none d-md-table-cell">
									<?php echo Text::_('COM_INSTALLER_CURRENT_VERSION'); ?>
								</th>
								<th scope="col" class="nowrap center">
									<?php echo Text::_('COM_INSTALLER_NEW_VERSION'); ?>
								</th>
								<th scope="col" class="nowrap d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_INSTALLER_HEADING_FOLDER', 'folder_translated', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" class="nowrap d-none d-md-table-cell">
									<?php echo Text::_('COM_INSTALLER_HEADING_INSTALLTYPE'); ?>
								</th>
								<th scope="col" style="width:40%" class="nowrap d-none d-md-table-cell">
									<?php echo Text::_('COM_INSTALLER_HEADING_DETAILSURL'); ?>
								</th>
							</tr>
							</thead>
							<tbody>
							<?php foreach ($this->items as $i => $item) : ?>
								<?php
								$client          = $item->client_id ? Text::_('JADMINISTRATOR') : Text::_('JSITE');
								$manifest        = json_decode($item->manifest_cache);
								$current_version = $manifest->version ?? Text::_('JLIB_UNKNOWN');
								?>
								<tr class="row<?php echo $i % 2; ?>">
									<td>
										<?php echo HTMLHelper::_('grid.id', $i, $item->update_id); ?>
									</td>
									<th scope="row">
										<label for="cb<?php echo $i; ?>">
											<span class="editlinktip hasTooltip" title="<?php echo HTMLHelper::_('tooltipText', Text::_('JGLOBAL_DESCRIPTION'), $item->description ?: Text::_('COM_INSTALLER_MSG_UPDATE_NODESC'), 0); ?>">
											<?php echo $this->escape($item->name); ?>
											</span>
										</label>
									</th>
									<td class="center">
										<?php echo $item->client_translated; ?>
									</td>
									<td class="center">
										<?php echo $item->type_translated; ?>
									</td>
									<td class="d-none d-md-table-cell text-center">
										<span class="badge badge-warning"><?php echo $item->current_version; ?></span>
									</td>
									<td>
										<span class="badge badge-success"><?php echo $item->version; ?></span>
									</td>
									<td class="d-none d-md-table-cell text-center">
										<?php echo $item->folder_translated; ?>
									</td>
									<td class="d-none d-md-table-cell text-center">
										<?php echo $item->install_type; ?>
									</td>
									<td class="d-none d-md-table-cell">
										<span class="break-word">
										<?php echo $item->detailsurl; ?>
											<?php if (isset($item->infourl)) : ?>
												<br>
												<a href="<?php echo $item->infourl; ?>" target="_blank" rel="noopener noreferrer"><?php echo $this->escape($item->infourl); ?></a>
											<?php endif; ?>
										</span>
									</td>
								</tr>
							<?php endforeach; ?>
							</tbody>
						</table>

						<?php // load the pagination. ?>
						<?php echo $this->pagination->getListFooter(); ?>

					<?php endif; ?>
					<input type="hidden" name="task" value="">
					<input type="hidden" name="boxchecked" value="0">
					<?php echo HTMLHelper::_('form.token'); ?>
				</div>
			</div>
		</div>
	</form>
</div>
