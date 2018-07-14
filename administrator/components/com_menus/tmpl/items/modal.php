<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Factory;

$app = Factory::getApplication();

if ($app->isClient('site'))
{
	Session::checkToken('get') or die(Text::_('JINVALID_TOKEN'));
	HTMLHelper::_('stylesheet', 'system/adminlist.css', array(), true);
}

HTMLHelper::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/helpers/html');

HTMLHelper::_('behavior.core');
HTMLHelper::_('script', 'com_menus/admin-items-modal.min.js', array('version' => 'auto', 'relative' => true));
HTMLHelper::_('bootstrap.popover', '.hasPopover', array('placement' => 'bottom'));

$function     = $app->input->get('function', 'jSelectMenuItem', 'cmd');
$editor    = $app->input->getCmd('editor', '');
$listOrder    = $this->escape($this->state->get('list.ordering'));
$listDirn     = $this->escape($this->state->get('list.direction'));
$link         = 'index.php?option=com_menus&view=items&layout=modal&tmpl=component&' . Session::getFormToken() . '=1';

if (!empty($editor))
{
	// This view is used also in com_menus. Load the xtd script only if the editor is set!
	Factory::getDocument()->addScriptOptions('xtd-menus', array('editor' => $editor));
	$onclick = "jSelectMenuItem";
	$link    = 'index.php?option=com_menus&view=items&layout=modal&tmpl=component&editor=' . $editor . '&' . Session::getFormToken() . '=1';
}
?>
<div class="container-popup">
	<form action="<?php echo Route::_($link); ?>" method="post" name="adminForm" id="adminForm" class="form-inline">

		<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

		<?php if (empty($this->items)) : ?>
			<joomla-alert type="warning"><?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></joomla-alert>
		<?php else : ?>
			<table class="table table-sm">
				<thead>
					<tr>
						<th style="width:1%" class="nowrap text-center">
							<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
						</th>
						<th class="title">
							<?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap d-none d-md-table-cell">
							<?php echo HTMLHelper::_('searchtools.sort', 'COM_MENUS_HEADING_MENU', 'menutype_title', $listDirn, $listOrder); ?>
						</th>
						<th style="width:5%" class="text-center nowrap d-none d-md-table-cell">
							<?php echo HTMLHelper::_('searchtools.sort', 'COM_MENUS_HEADING_HOME', 'a.home', $listDirn, $listOrder); ?>
						</th>
						<th style="width:10%" class="nowrap d-none d-md-table-cell">
							<?php echo HTMLHelper::_('searchtools.sort',  'JGRID_HEADING_ACCESS', 'a.access', $listDirn, $listOrder); ?>
						</th>
						<th style="width:15%" class="nowrap d-none d-md-table-cell">
							<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'language', $listDirn, $listOrder); ?>
						</th>
						<th style="width:1%" class="nowrap d-none d-md-table-cell">
							<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
						</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<td colspan="7">
							<?php echo $this->pagination->getListFooter(); ?>
						</td>
					</tr>
				</tfoot>
				<tbody>
				<?php foreach ($this->items as $i => $item) : ?>
					<?php $uselessMenuItem = in_array($item->type, array('separator', 'heading', 'alias', 'url', 'container')); ?>
					<?php if ($item->language && Multilanguage::isEnabled())
					{
						if ($item->language !== '*')
						{
							$language = $item->language;
						}
						else
						{
							$language = '';
						}
					}
					elseif (!Multilanguage::isEnabled())
					{
						$language = '';
					}
					?>
					<tr class="row<?php echo $i % 2; ?>">
						<td class="text-center">
							<?php echo HTMLHelper::_('menus.state', $item->published, $i, 0); ?>
						</td>
						<td>
							<?php $prefix = LayoutHelper::render('joomla.html.treeprefix', array('level' => $item->level)); ?>
							<?php echo $prefix; ?>
							<?php if (!$uselessMenuItem) : ?>
								<a class="select-link" href="javascript:void(0)" data-function="<?php echo $this->escape($function); ?>" data-id="<?php echo $item->id; ?>"  data-title="<?php echo $this->escape($item->title); ?>" data-uri="<?php echo 'index.php?Itemid=' . $item->id; ?>" data-language="<?php echo $this->escape($language); ?>">
									<?php echo $this->escape($item->title); ?>
								</a>
							<?php else : ?>
								<?php echo $this->escape($item->title); ?>
							<?php endif; ?>
							<span class="small">
								<?php if (empty($item->note)) : ?>
									<?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
								<?php else : ?>
									<?php echo Text::sprintf('JGLOBAL_LIST_ALIAS_NOTE', $this->escape($item->alias), $this->escape($item->note)); ?>
								<?php endif; ?>
							</span>
							<div title="<?php echo $this->escape($item->path); ?>">
								<?php echo $prefix; ?>
								<span class="small" title="<?php echo isset($item->item_type_desc) ? htmlspecialchars($this->escape($item->item_type_desc), ENT_COMPAT, 'UTF-8') : ''; ?>">
									<?php echo $this->escape($item->item_type); ?></span>
							</div>
						</td>
						<td class="small d-none d-md-table-cell">
							<?php echo $this->escape($item->menutype_title); ?>
						</td>
						<td class="text-center d-none d-md-table-cell">
							<?php if ($item->type == 'component') : ?>
								<?php if ($item->language == '*' || $item->home == '0') : ?>
									<?php echo HTMLHelper::_('jgrid.isdefault', $item->home, $i, 'items.', ($item->language != '*' || !$item->home) && 0); ?>
								<?php else : ?>
									<?php if ($item->language_image) : ?>
										<?php echo HTMLHelper::_('image', 'mod_languages/' . $item->language_image . '.gif', $item->language_title, array('title' => $item->language_title), true); ?>
									<?php else : ?>
										<span class="badge badge-secondary" title="<?php echo $item->language_title; ?>"><?php echo $item->language_sef; ?></span>
									<?php endif; ?>
								<?php endif; ?>
							<?php endif; ?>
						</td>
						<td class="small d-none d-md-table-cell">
							<?php echo $this->escape($item->access_level); ?>
						</td>
						<td class="small d-none d-md-table-cell">
							<?php if ($item->language == '') : ?>
								<?php echo Text::_('JDEFAULT'); ?>
							<?php elseif ($item->language == '*') : ?>
								<?php echo Text::alt('JALL', 'language'); ?>
							<?php else : ?>
								<?php echo LayoutHelper::render('joomla.content.language', $item); ?>
							<?php endif; ?>
						</td>
						<td class="d-none d-md-table-cell">
							<span title="<?php echo sprintf('%d-%d', $item->lft, $item->rgt); ?>">
								<?php echo (int) $item->id; ?>
							</span>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>

		<input type="hidden" name="task" value="">
		<input type="hidden" name="boxchecked" value="0">
		<input type="hidden" name="function" value="<?php echo $function; ?>">
		<input type="hidden" name="forcedLanguage" value="<?php echo $app->input->get('forcedLanguage', '', 'cmd'); ?>">
		<?php echo HTMLHelper::_('form.token'); ?>

	</form>
</div>
