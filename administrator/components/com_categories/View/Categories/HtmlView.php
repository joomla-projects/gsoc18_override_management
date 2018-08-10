<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_categories
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Categories\Administrator\View\Categories;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\Component\Categories\Administrator\Helper\CategoriesHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * Categories view class for the Category package.
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * An array of items
	 *
	 * @var  array
	 */
	protected $items;

	/**
	 * The pagination object
	 *
	 * @var  Pagination
	 */
	protected $pagination;

	/**
	 * The model state
	 *
	 * @var  object
	 */
	protected $state;

	/**
	 * Flag if an association exists
	 *
	 * @var  boolean
	 */
	protected $assoc;

	/**
	 * Form object for search filters
	 *
	 * @var  \JForm
	 */
	public $filterForm;

	/**
	 * The active search filters
	 *
	 * @var  array
	 */
	public $activeFilters;

	/**
	 * The sidebar markup
	 *
	 * @var  string
	 */
	protected $string;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 */
	public function display($tpl = null)
	{
		$this->state         = $this->get('State');
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->assoc         = $this->get('Assoc');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new \JViewGenericdataexception(implode("\n", $errors), 500);
		}

		// Preprocess the list of items to find ordering divisions.
		foreach ($this->items as &$item)
		{
			$this->ordering[$item->parent_id][] = $item->id;
		}

		// We don't need toolbar in the modal window.
		if ($this->getLayout() !== 'modal')
		{
			$this->addToolbar();
			$this->sidebar = \JHtmlSidebar::render();

			// We do not need to filter by language when multilingual is disabled
			if (!Multilanguage::isEnabled())
			{
				unset($this->activeFilters['language']);
				$this->filterForm->removeField('language', 'filter');
			}
		}
		else
		{
			// In article associations modal we need to remove language filter if forcing a language.
			if ($forcedLanguage = Factory::getApplication()->input->get('forcedLanguage', '', 'CMD'))
			{
				// If the language is forced we can't allow to select the language, so transform the language selector filter into a hidden field.
				$languageXml = new \SimpleXMLElement('<field name="language" type="hidden" default="' . $forcedLanguage . '" />');
				$this->filterForm->setField($languageXml, 'filter', true);

				// Also, unset the active language filter so the search tools is not open by default with this filter.
				unset($this->activeFilters['language']);
			}
		}

		return parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		$categoryId = $this->state->get('filter.category_id');
		$component  = $this->state->get('filter.component');
		$section    = $this->state->get('filter.section');
		$canDo      = ContentHelper::getActions($component, 'category', $categoryId);
		$user       = Factory::getUser();

		// Get the toolbar object instance
		$bar = Toolbar::getInstance('toolbar');

		// Avoid nonsense situation.
		if ($component == 'com_categories')
		{
			return;
		}

		// Need to load the menu language file as mod_menu hasn't been loaded yet.
		$lang = Factory::getLanguage();
		$lang->load($component, JPATH_BASE, null, false, true)
		|| $lang->load($component, JPATH_ADMINISTRATOR . '/components/' . $component, null, false, true);

		// If a component categories title string is present, let's use it.
		if ($lang->hasKey($component_title_key = strtoupper($component . ($section ? "_$section" : '')) . '_CATEGORIES_TITLE'))
		{
			$title = Text::_($component_title_key);
		}
		elseif ($lang->hasKey($component_section_key = strtoupper($component . ($section ? "_$section" : ''))))
		// Else if the component section string exits, let's use it
		{
			$title = Text::sprintf('COM_CATEGORIES_CATEGORIES_TITLE', $this->escape(Text::_($component_section_key)));
		}
		else
		// Else use the base title
		{
			$title = Text::_('COM_CATEGORIES_CATEGORIES_BASE_TITLE');
		}

		// Load specific css component
		HTMLHelper::_('stylesheet', $component . '/administrator/categories.css', array('version' => 'auto', 'relative' => true));

		// Prepare the toolbar.
		ToolbarHelper::title($title, 'folder categories ' . substr($component, 4) . ($section ? "-$section" : '') . '-categories');

		if ($canDo->get('core.create') || count($user->getAuthorisedCategories($component, 'core.create')) > 0)
		{
			ToolbarHelper::addNew('category.add');
		}

		if ($canDo->get('core.edit.state'))
		{
			ToolbarHelper::publish('categories.publish', 'JTOOLBAR_PUBLISH', true);
			ToolbarHelper::unpublish('categories.unpublish', 'JTOOLBAR_UNPUBLISH', true);
			ToolbarHelper::archiveList('categories.archive');
		}

		if (Factory::getUser()->authorise('core.admin'))
		{
			ToolbarHelper::checkin('categories.checkin');
		}

		// Add a batch button
		if ($canDo->get('core.create')
			&& $canDo->get('core.edit')
			&& $canDo->get('core.edit.state'))
		{
			$title = Text::_('JTOOLBAR_BATCH');

			// Instantiate a new \ileLayout instance and render the batch button
			$layout = new FileLayout('joomla.toolbar.batch');

			$dhtml = $layout->render(array('title' => $title));
			$bar->appendButton('Custom', $dhtml, 'batch');
		}

		if ($canDo->get('core.admin'))
		{
			ToolbarHelper::custom('categories.rebuild', 'refresh.png', 'refresh_f2.png', 'JTOOLBAR_REBUILD', false);
		}

		if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete', $component))
		{
			ToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'categories.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif ($canDo->get('core.edit.state'))
		{
			ToolbarHelper::trash('categories.trash');
		}

		if ($canDo->get('core.admin') || $canDo->get('core.options'))
		{
			ToolbarHelper::preferences($component);
		}

		// Compute the ref_key if it does exist in the component
		if (!$lang->hasKey($ref_key = strtoupper($component . ($section ? "_$section" : '')) . '_CATEGORIES_HELP_KEY'))
		{
			$ref_key = 'JHELP_COMPONENTS_' . strtoupper(substr($component, 4) . ($section ? "_$section" : '')) . '_CATEGORIES';
		}

		/*
		 * Get help for the categories view for the component by
		 * -remotely searching in a language defined dedicated URL: *component*_HELP_URL
		 * -locally  searching in a component help file if helpURL param exists in the component and is set to ''
		 * -remotely searching in a component URL if helpURL param exists in the component and is NOT set to ''
		 */
		if ($lang->hasKey($lang_help_url = strtoupper($component) . '_HELP_URL'))
		{
			$debug = $lang->setDebug(false);
			$url = Text::_($lang_help_url);
			$lang->setDebug($debug);
		}
		else
		{
			$url = null;
		}

		ToolbarHelper::help($ref_key, ComponentHelper::getParams($component)->exists('helpURL'), $url);
	}

	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 *
	 * @since   3.0
	 */
	protected function getSortFields()
	{
		return array(
			'a.lft'       => Text::_('JGRID_HEADING_ORDERING'),
			'a.published' => Text::_('JSTATUS'),
			'a.title'     => Text::_('JGLOBAL_TITLE'),
			'a.access'    => Text::_('JGRID_HEADING_ACCESS'),
			'language'    => Text::_('JGRID_HEADING_LANGUAGE'),
			'a.id'        => Text::_('JGRID_HEADING_ID'),
		);
	}
}
