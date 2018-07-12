<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$titlecore = JText::_('COM_TEMPLATES_LAYOUTS_DIFFVIEW_SHOW_CORE');
$titlediff = JText::_('COM_TEMPLATES_LAYOUTS_DIFFVIEW_SHOW_DIFF');
$description = JText::_('COM_TEMPLATES_LAYOUTS_DIFFVIEW_DESC');
?>
<button type="button" class="btn btn-secondary disabled" onclick-task="template.do.nothing" title="<?php echo $description; ?>"><?php echo $titlecore; ?></button>
<button type="button" class="btn btn-secondary disabled" onclick-task="template.do.nothing" title="<?php echo $description; ?>"><?php echo $titlediff; ?></button>
