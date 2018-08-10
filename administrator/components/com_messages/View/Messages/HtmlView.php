<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_messages
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Messages\Administrator\View\Messages;

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Toolbar\Toolbar;

/**
 * View class for a list of messages.
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
	 * @var  \Joomla\CMS\Pagination\Pagination
	 */
	protected $pagination;

	/**
	 * The model state
	 *
	 * @var  \JObject
	 */
	protected $state;

	/**
	 * Form object for search filters
	 *
	 * @var    \JForm
	 * @since  4.0.0
	 */
	public $filterForm;

	/**
	 * The active search filters
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	public $activeFilters;

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

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new \JViewGenericdataexception(implode("\n", $errors), 500);
		}

		$this->addToolbar();

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
		$canDo = ContentHelper::getActions('com_messages');
		ToolbarHelper::title(Text::_('COM_MESSAGES_MANAGER_MESSAGES'), 'envelope inbox');

		if ($canDo->get('core.create'))
		{
			ToolbarHelper::addNew('message.add');
		}

		if ($canDo->get('core.edit.state'))
		{
			ToolbarHelper::divider();
			ToolbarHelper::publish('messages.publish', 'COM_MESSAGES_TOOLBAR_MARK_AS_READ', true);
			ToolbarHelper::unpublish('messages.unpublish', 'COM_MESSAGES_TOOLBAR_MARK_AS_UNREAD', true);
		}

		ToolbarHelper::divider();
		$bar = Toolbar::getInstance('toolbar');
		$bar->appendButton(
			'Popup',
			'cog',
			'COM_MESSAGES_TOOLBAR_MY_SETTINGS',
			'index.php?option=com_messages&amp;view=config&amp;tmpl=component',
			500,
			250,
			0,
			0,
			'',
			'',
			'<button class="btn btn-secondary" type="button" data-dismiss="modal" aria-hidden="true">'
			. Text::_('JCANCEL')
			. '</button>'
			. '<button class="btn btn-success" type="button" data-dismiss="modal" aria-hidden="true"'
			. ' onclick="jQuery(\'#modal-cog iframe\').contents().find(\'#saveBtn\').click();">'
			. Text::_('JSAVE')
			. '</button>'
		);

		if ($state->get('filter.state') == -2 && $canDo->get('core.delete'))
		{
			ToolbarHelper::divider();
			ToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'messages.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif ($canDo->get('core.edit.state'))
		{
			ToolbarHelper::divider();
			ToolbarHelper::trash('messages.trash');
		}

		if ($canDo->get('core.admin'))
		{
			ToolbarHelper::preferences('com_messages');
		}

		ToolbarHelper::divider();
		ToolbarHelper::help('JHELP_COMPONENTS_MESSAGING_INBOX');
	}
}
