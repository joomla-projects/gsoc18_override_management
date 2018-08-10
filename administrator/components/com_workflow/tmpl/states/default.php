<?php
/**
 * Items Model for a Workflow Component.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       __DEPLOY_VERSION__
 */
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;

HTMLHelper::_('behavior.tooltip');

$user      = Factory::getUser();
$userId    = $user->id;

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$saveOrderingUrl = '';

$saveOrder = ($listOrder == 's.ordering');

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_workflow&task=states.saveOrderAjax&' . Session::getFormToken() . '=1';
	HTMLHelper::_('draggablelist.draggable');
}
?>
<form action="<?php echo Route::_('index.php?option=com_workflow&view=states&workflow_id=' . (int) $this->workflowID . '&extension=' . $this->extension); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div id="j-sidebar-container" class="col-md-2">
			<?php echo $this->sidebar; ?>
		</div>
		<div class="col-md-10">
			<div id="j-main-container" class="j-main-container">
				<?php
				// Search tools bar
				echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
				?>
				<?php if (empty($this->states)) : ?>
					<div class="alert alert-warning alert-no-items">
						<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
					</div>
				<?php else: ?>
					<table class="table table-striped">
						<thead>
							<tr>
								<th scope="col" style="width:1%" class="nowrap text-center hidden-sm-down">
									<?php echo HTMLHelper::_('searchtools.sort', '', 's.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
								</th>
								<td style="width:1%" class="nowrap text-center hidden-sm-down">
									<?php echo HTMLHelper::_('grid.checkall'); ?>
								</td>
								<th scope="col" style="width:1%" class="nowrap text-center hidden-sm-down">
									<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 's.condition', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" style="width:1%" class="text-center nowrap hidden-sm-down">
									<?php echo Text::_('COM_WORKFLOW_DEFAULT'); ?>
								</th>
								<th scope="col" style="width:10%" class="nowrap hidden-sm-down">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_WORKFLOW_NAME', 's.title', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" style="width:10%" class="nowrap text-center hidden-sm-down">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_WORKFLOW_CONDITION', 's.condition', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" style="width:10%" class="nowrap text-right hidden-sm-down">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_WORKFLOW_ID', 's.id', $listDirn, $listOrder); ?>
								</th>
							</tr>
						</thead>
						<tbody class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>">
							<?php foreach ($this->states as $i => $item):
								$edit = Route::_('index.php?option=com_workflow&task=state.edit&id=' . $item->id . '&workflow_id=' . (int) $this->workflowID . '&extension=' . $this->extension);

								$canEdit    = $user->authorise('core.edit', $this->extension . '.state.' . $item->id);
								// @TODO set proper checkin fields
								$canCheckin = true || $user->authorise('core.admin', 'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
								$canChange  = $user->authorise('core.edit.state', $this->extension . '.state.' . $item->id) && $canCheckin;
								?>
								<tr class="row<?php echo $i % 2; ?>">
									<td class="order nowrap text-center hidden-sm-down">
										<?php
										$iconClass = '';
										if (!$canChange)
										{
											$iconClass = ' inactive';
										}
										elseif (!$saveOrder)
										{
											$iconClass = ' inactive tip-top hasTooltip" title="' . HTMLHelper::_('tooltipText', 'JORDERINGDISABLED');
										}
										?>
										<span class="sortable-handler<?php echo $iconClass ?>">
											<span class="icon-menu" aria-hidden="true"></span>
										</span>
										<?php if ($canChange && $saveOrder) : ?>
											<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order">
										<?php endif; ?>									</td>
									<td class="order nowrap text-center hidden-sm-down">
										<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
									</td>
									<td class="text-center">
										<div class="btn-group">
											<?php echo HTMLHelper::_('jgrid.published', $item->published, $i, 'states.', $canChange); ?>
										</div>
									</td>
									<td class="text-center hidden-sm-down">
										<?php echo HTMLHelper::_('jgrid.isdefault', $item->default, $i, 'states.', $canChange); ?>
									</td>
									<th scope="row">
										<?php if ($canEdit) : ?>
											<?php $editIcon = '<span class="fa fa-pencil-square mr-2" aria-hidden="true"></span>'; ?>
											<a href="<?php echo $edit; ?>" title="<?php echo Text::_('JACTION_EDIT'); ?> <?php echo $this->escape(addslashes($item->title)); ?>">
												<?php echo $editIcon; ?><?php echo $item->title; ?>
											</a>
										<?php else: ?>
											<?php echo $item->title; ?>
										<?php endif; ?>
									</th>
									<td class="text-center">
										<?php echo Text::_($item->condition); ?>
									</td>
									<td class="text-right">
										<?php echo $item->id; ?>
									</td>
								</tr>
							<?php endforeach ?>
						</tbody>
					</table>
					<?php // load the pagination. ?>
					<?php echo $this->pagination->getListFooter(); ?>

				<?php endif; ?>
				<input type="hidden" name="task" value="">
				<input type="hidden" name="boxchecked" value="0">
				<input type="hidden" name="workflow_id" value="<?php echo (int) $this->workflowID ?>">
				<input type="hidden" name="extension" value="<?php echo $this->extension ?>">
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</div>
