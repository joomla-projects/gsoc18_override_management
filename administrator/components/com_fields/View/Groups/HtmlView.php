<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Fields\Administrator\View\Groups;

defined('_JEXEC') or die;

use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Factory;

/**
 * Groups View
 *
 * @since  3.7.0
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * @var  \JForm
	 *
	 * @since  3.7.0
	 */
	public $filterForm;

	/**
	 * @var  array
	 *
	 * @since  3.7.0
	 */
	public $activeFilters;

	/**
	 * @var  array
	 *
	 * @since  3.7.0
	 */
	protected $items;

	/**
	 * @var  \JPagination
	 *
	 * @since  3.7.0
	 */
	protected $pagination;

	/**
	 * @var  \JObject
	 *
	 * @since  3.7.0
	 */
	protected $state;

	/**
	 * @var  string
	 *
	 * @since  3.7.0
	 */
	protected $sidebar;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @see     HtmlView::loadTemplate()
	 * @since   3.7.0
	 */
	public function display($tpl = null)
	{
		$this->state         = $this->get('State');
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new \JViewGenericdataexception(implode("\n", $errors), 500);
		}

		// Display a warning if the fields system plugin is disabled
		if (!PluginHelper::isEnabled('system', 'fields'))
		{
			$link = Route::_('index.php?option=com_plugins&task=plugin.edit&extension_id=' . \FieldsHelper::getFieldsPluginId());
			Factory::getApplication()->enqueueMessage(Text::sprintf('COM_FIELDS_SYSTEM_PLUGIN_NOT_ENABLED', $link), 'warning');
		}

		$this->addToolbar();

		// We do not need to filter by language when multilingual is disabled
		if (!Multilanguage::isEnabled())
		{
			unset($this->activeFilters['language']);
			$this->filterForm->removeField('language', 'filter');
		}

		\FieldsHelper::addSubmenu($this->state->get('filter.context'), 'groups');
		$this->sidebar = \JHtmlSidebar::render();

		return parent::display($tpl);
	}

	/**
	 * Adds the toolbar.
	 *
	 * @return  void
	 *
	 * @since   3.7.0
	 */
	protected function addToolbar()
	{
		$groupId   = $this->state->get('filter.group_id');
		$component = '';
		$parts     = \FieldsHelper::extract($this->state->get('filter.context'));

		if ($parts)
		{
			$component = $parts[0];
		}

		$canDo     = ContentHelper::getActions($component, 'fieldgroup', $groupId);

		// Get the toolbar object instance
		$bar = Toolbar::getInstance('toolbar');

		// Avoid nonsense situation.
		if ($component == 'com_fields')
		{
			return;
		}

		// Load component language file
		$lang = Factory::getLanguage();
		$lang->load($component, JPATH_ADMINISTRATOR)
		|| $lang->load($component, Path::clean(JPATH_ADMINISTRATOR . '/components/' . $component));

		$title = Text::sprintf('COM_FIELDS_VIEW_GROUPS_TITLE', Text::_(strtoupper($component)));

		// Prepare the toolbar.
		ToolbarHelper::title($title, 'puzzle fields ' . substr($component, 4) . '-groups');

		if ($canDo->get('core.create'))
		{
			ToolbarHelper::addNew('group.add');
		}

		if ($canDo->get('core.edit.state'))
		{
			ToolbarHelper::publish('groups.publish', 'JTOOLBAR_PUBLISH', true);
			ToolbarHelper::unpublish('groups.unpublish', 'JTOOLBAR_UNPUBLISH', true);
			ToolbarHelper::archiveList('groups.archive');
		}

		if (Factory::getUser()->authorise('core.admin'))
		{
			ToolbarHelper::checkin('groups.checkin');
		}

		// Add a batch button
		if ($canDo->get('core.create') && $canDo->get('core.edit') && $canDo->get('core.edit.state'))
		{
			$title = Text::_('JTOOLBAR_BATCH');

			// Instantiate a new FileLayout instance and render the batch button
			$layout = new FileLayout('joomla.toolbar.batch');

			$dhtml = $layout->render(
				array(
					'title' => $title,
				)
			);

			$bar->appendButton('Custom', $dhtml, 'batch');
		}

		if ($this->state->get('filter.state') == -2 && $canDo->get('core.delete', $component))
		{
			ToolbarHelper::deleteList('', 'groups.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif ($canDo->get('core.edit.state'))
		{
			ToolbarHelper::trash('groups.trash');
		}

		if ($canDo->get('core.admin') || $canDo->get('core.options'))
		{
			ToolbarHelper::preferences($component);
		}

		ToolbarHelper::help('JHELP_COMPONENTS_FIELDS_FIELD_GROUPS');
	}

	/**
	 * Returns the sort fields.
	 *
	 * @return  array
	 *
	 * @since   3.7.0
	 */
	protected function getSortFields()
	{
		return array(
			'a.ordering'  => Text::_('JGRID_HEADING_ORDERING'),
			'a.state'     => Text::_('JSTATUS'),
			'a.title'     => Text::_('JGLOBAL_TITLE'),
			'a.access'    => Text::_('JGRID_HEADING_ACCESS'),
			'language'    => Text::_('JGRID_HEADING_LANGUAGE'),
			'a.context'   => Text::_('JGRID_HEADING_CONTEXT'),
			'a.id'        => Text::_('JGRID_HEADING_ID'),
		);
	}
}
