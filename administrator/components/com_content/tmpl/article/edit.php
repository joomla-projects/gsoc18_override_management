<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('formbehavior.chosen', '#jform_catid', null, array('disable_search_threshold' => 0 ));

$this->configFieldsets  = array('editorConfig');
$this->hiddenFieldsets  = array('basic-limited');
$this->ignore_fieldsets = array('jmetadata', 'item_associations');
$this->useCoreUI = true;

// Create shortcut to parameters.
$params = clone $this->state->get('params');
$params->merge(new Registry($this->item->attribs));

$app = Factory::getApplication();
$input = $app->input;

$assoc = Associations::isEnabled();

// In case of modal
$isModal = $input->get('layout') == 'modal' ? true : false;
$layout  = $isModal ? 'modal' : 'edit';
$tmpl    = $isModal || $input->get('tmpl', '', 'cmd') === 'component' ? '&tmpl=component' : '';
?>

<form action="<?php echo Route::_('index.php?option=com_content&layout=' . $layout . $tmpl . '&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">

	<?php echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>

	<div>
		<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'general')); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('COM_CONTENT_ARTICLE_CONTENT')); ?>
		<div class="row">
			<div class="col-md-9">
				<fieldset class="adminform">
					<?php echo $this->form->getInput('articletext'); ?>
				</fieldset>
			</div>
			<div class="col-md-3">
				<div class="card card-light">
					<div class="card-body">
						<?php echo LayoutHelper::render('joomla.edit.global', $this); ?>
					</div>
				</div>
			</div>
		</div>

		<?php echo JHtml::_('uitab.endTab'); ?>

		<?php // Do not show the images and links options if the edit form is configured not to. ?>
		<?php if ($params->get('show_urls_images_backend') == 1) : ?>
			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'images', Text::_('COM_CONTENT_FIELDSET_URLS_AND_IMAGES')); ?>
			<div class="row">
				<div class="col-md-6">
					<?php echo $this->form->renderField('images'); ?>
					<?php foreach ($this->form->getGroup('images') as $field) : ?>
						<?php echo $field->renderField(); ?>
					<?php endforeach; ?>
				</div>
				<div class="col-md-6">
					<?php foreach ($this->form->getGroup('urls') as $field) : ?>
						<?php echo $field->renderField(); ?>
					<?php endforeach; ?>
				</div>
			</div>

			<?php echo JHtml::_('uitab.endTab'); ?>
		<?php endif; ?>

		<?php $this->show_options = $params->get('show_article_options', 1); ?>
		<?php echo LayoutHelper::render('joomla.edit.params', $this); ?>

		<?php // Do not show the publishing options if the edit form is configured not to. ?>
		<?php if ($params->get('show_publishing_options', 1) == 1) : ?>
			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'publishing', Text::_('COM_CONTENT_FIELDSET_PUBLISHING')); ?>
			<div class="row">
				<div class="col-md-6">
					<?php echo LayoutHelper::render('joomla.edit.publishingdata', $this); ?>
				</div>
				<div class="col-md-6">
					<?php echo LayoutHelper::render('joomla.edit.metadata', $this); ?>
				</div>
			</div>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>
		<?php endif; ?>

		<?php if ( ! $isModal && $assoc) : ?>
			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'associations', Text::_('JGLOBAL_FIELDSET_ASSOCIATIONS')); ?>
			<?php echo $this->loadTemplate('associations'); ?>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>
		<?php elseif ($isModal && $assoc) : ?>
			<div class="hidden"><?php echo $this->loadTemplate('associations'); ?></div>
		<?php endif; ?>

		<?php if ($this->canDo->get('core.admin')) : ?>
			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'editor', Text::_('COM_CONTENT_SLIDER_EDITOR_CONFIG')); ?>
			<?php echo $this->form->renderFieldset('editorConfig'); ?>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>
		<?php endif; ?>

		<?php if ($this->canDo->get('core.admin')) : ?>
			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'permissions', Text::_('COM_CONTENT_FIELDSET_RULES')); ?>
				<?php echo $this->form->getInput('rules'); ?>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>
		<?php endif; ?>

		<?php echo JHtml::_('uitab.endTabSet'); ?>

		<input type="hidden" name="task" value="">
		<input type="hidden" name="return" value="<?php echo $input->getCmd('return'); ?>">
		<input type="hidden" name="forcedLanguage" value="<?php echo $input->get('forcedLanguage', '', 'cmd'); ?>">
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>
