<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Language\Text;

$list = $displayData['list'];

?>
<nav role="navigation" aria-label="<?php echo Text::_('JLIB_HTML_PAGINATION'); ?>">
	<ul class="pagination ml-0 mb-4">
		<?php echo $list['start']['data']; ?>
		<?php echo $list['previous']['data']; ?>

		<?php foreach ($list['pages'] as $page) : ?>
			<?php echo $page['data']; ?>
		<?php endforeach; ?>

		<?php echo $list['next']['data']; ?>
		<?php echo $list['end']['data']; ?>
	</ul>
</nav>
