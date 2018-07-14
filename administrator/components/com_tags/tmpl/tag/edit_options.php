<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_tags
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

?>
<?php
	echo HTMLHelper::_('bootstrap.startAccordion', 'categoryOptions', array('active' => 'collapse0'));
	$fieldSets = $this->form->getFieldsets('params');
	$i = 0;

	foreach ($fieldSets as $name => $fieldSet) :
		$label = !empty($fieldSet->label) ? $fieldSet->label : 'COM_TAGS_' . $name . '_FIELDSET_LABEL';
		echo HTMLHelper::_('bootstrap.addSlide', 'categoryOptions', Text::_($label), 'collapse' . ($i++));
			if (isset($fieldSet->description) && trim($fieldSet->description)) :
				echo '<p class="tip">' . $this->escape(Text::_($fieldSet->description)) . '</p>';
			endif;
			?>
				<?php foreach ($this->form->getFieldset($name) as $field) : ?>
					<div class="control-group">
						<div class="control-label">
							<?php echo $field->label; ?>
						</div>
						<div class="controls">
							<?php echo $field->input; ?>
						</div>
					</div>
				<?php endforeach; ?>

				<?php if ($name == 'basic') : ?>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('note'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('note'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('tag_layout'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('tag_layout'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('tag_link_class'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('tag_link_class'); ?>
						</div>
					</div>
				<?php endif;
		echo HTMLHelper::_('bootstrap.endSlide');
	endforeach;
echo HTMLHelper::_('bootstrap.endAccordion');
