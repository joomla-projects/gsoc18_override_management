<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_newsfeeds
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Newsfeeds\Administrator\View\Newsfeeds;

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\Component\Newsfeeds\Administrator\Helper\NewsfeedsHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Factory;

/**
 * View class for a list of newsfeeds.
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * The list of newsfeeds
	 *
	 * @var    \JObject
	 * @since  1.6
	 */
	protected $items;

	/**
	 * The pagination object
	 *
	 * @var    \Joomla\CMS\Pagination\Pagination
	 * @since  1.6
	 */
	protected $pagination;

	/**
	 * The model state
	 *
	 * @var    \JObject
	 * @since  1.6
	 */
	protected $state;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @since   1.6
	 */
	public function display($tpl = null)
	{
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		// Modal layout doesn't need the submenu.
		if ($this->getLayout() !== 'modal')
		{
			NewsfeedsHelper::addSubmenu('newsfeeds');
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new \JViewGenericdataexception(implode("\n", $errors), 500);
		}

		// We don't need toolbar in the modal layout.
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
			// We also need to change the category filter to show show categories with All or the forced language.
			if ($forcedLanguage = Factory::getApplication()->input->get('forcedLanguage', '', 'CMD'))
			{
				// If the language is forced we can't allow to select the language, so transform the language selector filter into a hidden field.
				$languageXml = new \SimpleXMLElement('<field name="language" type="hidden" default="' . $forcedLanguage . '" />');
				$this->filterForm->setField($languageXml, 'filter', true);

				// Also, unset the active language filter so the search tools is not open by default with this filter.
				unset($this->activeFilters['language']);

				// One last changes needed is to change the category filter to just show categories with All language or with the forced language.
				$this->filterForm->setFieldAttribute('category_id', 'language', '*,' . $forcedLanguage, 'filter');
			}
		}

		parent::display($tpl);
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
		$state = $this->get('State');
		$canDo = ContentHelper::getActions('com_newsfeeds', 'category', $state->get('filter.category_id'));
		$user  = Factory::getUser();

		// Get the toolbar object instance
		$bar = Toolbar::getInstance('toolbar');
		ToolbarHelper::title(Text::_('COM_NEWSFEEDS_MANAGER_NEWSFEEDS'), 'feed newsfeeds');

		if (count($user->getAuthorisedCategories('com_newsfeeds', 'core.create')) > 0)
		{
			ToolbarHelper::addNew('newsfeed.add');
		}

		if ($canDo->get('core.edit.state'))
		{
			ToolbarHelper::publish('newsfeeds.publish', 'JTOOLBAR_PUBLISH', true);
			ToolbarHelper::unpublish('newsfeeds.unpublish', 'JTOOLBAR_UNPUBLISH', true);
			ToolbarHelper::archiveList('newsfeeds.archive');
		}

		if ($canDo->get('core.admin'))
		{
			ToolbarHelper::checkin('newsfeeds.checkin');
		}

		// Add a batch button
		if ($user->authorise('core.create', 'com_newsfeeds')
			&& $user->authorise('core.edit', 'com_newsfeeds')
			&& $user->authorise('core.edit.state', 'com_newsfeeds'))
		{
			$title = Text::_('JTOOLBAR_BATCH');

			// Instantiate a new FileLayout instance and render the batch button
			$layout = new FileLayout('joomla.toolbar.batch');

			$dhtml = $layout->render(array('title' => $title));
			$bar->appendButton('Custom', $dhtml, 'batch');
		}

		if ($state->get('filter.published') == -2 && $canDo->get('core.delete'))
		{
			ToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'newsfeeds.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif ($canDo->get('core.edit.state'))
		{
			ToolbarHelper::trash('newsfeeds.trash');
		}

		if ($user->authorise('core.admin', 'com_newsfeeds') || $user->authorise('core.options', 'com_newsfeeds'))
		{
			ToolbarHelper::preferences('com_newsfeeds');
		}

		ToolbarHelper::help('JHELP_COMPONENTS_NEWSFEEDS_FEEDS');
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
			'a.ordering'     => Text::_('JGRID_HEADING_ORDERING'),
			'a.published'    => Text::_('JSTATUS'),
			'a.name'         => Text::_('JGLOBAL_TITLE'),
			'category_title' => Text::_('JCATEGORY'),
			'a.access'       => Text::_('JGRID_HEADING_ACCESS'),
			'numarticles'    => Text::_('COM_NEWSFEEDS_NUM_ARTICLES_HEADING'),
			'a.cache_time'   => Text::_('COM_NEWSFEEDS_CACHE_TIME_HEADING'),
			'a.language'     => Text::_('JGRID_HEADING_LANGUAGE'),
			'a.id'           => Text::_('JGRID_HEADING_ID')
		);
	}
}
