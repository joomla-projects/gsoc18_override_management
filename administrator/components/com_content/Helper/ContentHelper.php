<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Administrator\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Workflow\Workflow;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Category;
use Joomla\Registry\Registry;
use Joomla\CMS\Component\ComponentHelper;

/**
 * Content component helper.
 *
 * @since  1.6
 */
class ContentHelper extends \Joomla\CMS\Helper\ContentHelper
{
	public static $extension = 'com_content';

	/**
	 * Configure the Linkbar.
	 *
	 * @param   string  $vName  The name of the active view.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public static function addSubmenu($vName)
	{
		\JHtmlSidebar::addEntry(
			Text::_('JGLOBAL_ARTICLES'),
			'index.php?option=com_content&view=articles',
			$vName == 'articles'
		);
		\JHtmlSidebar::addEntry(
			Text::_('COM_CONTENT_SUBMENU_CATEGORIES'),
			'index.php?option=com_categories&extension=com_content',
			$vName == 'categories'
		);
		\JHtmlSidebar::addEntry(
			Text::_('COM_CONTENT_SUBMENU_FEATURED'),
			'index.php?option=com_content&view=featured',
			$vName == 'featured'
		);

		if (ComponentHelper::isEnabled('com_workflow') && ComponentHelper::getParams('com_content')->get('workflows_enable', 1))
		{
			\JHtmlSidebar::addEntry(
				Text::_('COM_CONTENT_SUBMENU_WORKFLOWS'),
				'index.php?option=com_workflow&extension=com_content',
				$vName == 'workflows'
			);

			if ($vName == 'states' || $vName == 'transitions')
			{
				$app        = Factory::getApplication();
				$workflowID = $app->getUserStateFromRequest('filter.workflow_id', 'workflow_id', 1, 'int');

				\JHtmlSidebar::addEntry(
					Text::_('COM_WORKFLOW_STATES'),
					'index.php?option=com_workflow&view=states&workflow_id=' . $workflowID . "&extension=com_content",
					$vName == 'states`'
				);

				\JHtmlSidebar::addEntry(
					Text::_('COM_WORKFLOW_TRANSITIONS'),
					'index.php?option=com_workflow&view=transitions&workflow_id=' . $workflowID . "&extension=com_content",
					$vName == 'transitions'
				);
			}
		}

		if (ComponentHelper::isEnabled('com_fields') && ComponentHelper::getParams('com_content')->get('custom_fields_enable', '1'))
		{
			\JHtmlSidebar::addEntry(
				Text::_('JGLOBAL_FIELDS'),
				'index.php?option=com_fields&context=com_content.article',
				$vName == 'fields.fields'
			);
			\JHtmlSidebar::addEntry(
				Text::_('JGLOBAL_FIELD_GROUPS'),
				'index.php?option=com_fields&view=groups&context=com_content.article',
				$vName == 'fields.groups'
			);
		}
	}

	/**
	 * Applies the content tag filters to arbitrary text as per settings for current user group
	 *
	 * @param   text  $text  The string to filter
	 *
	 * @return  string  The filtered string
	 *
	 * @deprecated  4.0  Use \JComponentHelper::filterText() instead.
	 */
	public static function filterText($text)
	{
		try
		{
			\JLog::add(
				sprintf('%s() is deprecated. Use JComponentHelper::filterText() instead', __METHOD__),
				\JLog::WARNING,
				'deprecated'
			);
		}
		catch (\RuntimeException $exception)
		{
			// Informational log only
		}

		return \JComponentHelper::filterText($text);
	}

	/**
	 * Adds Count Items for Category Manager.
	 *
	 * @param   \stdClass[]  &$items  The banner category objects
	 *
	 * @return  \stdClass[]
	 *
	 * @since   3.5
	 */
	public static function countItems(&$items)
	{
		$db = \JFactory::getDbo();

		foreach ($items as $item)
		{
			$item->count_trashed     = 0;
			$item->count_unpublished = 0;
			$item->count_published   = 0;

			$query  = $db->getQuery(true);

			$query	->select($db->quoteName('condition'))
					->select('COUNT(*) AS ' . $db->quoteName('count'))
					->from($db->quoteName('#__content', 'c'))
					->from($db->quoteName('#__workflow_states', 's'))
					->from($db->quoteName('#__workflow_associations', 'a'))
					->where($db->quoteName('a.item_id') . ' = ' . $db->quoteName('c.id'))
					->where($db->quoteName('s.id') . ' = ' . $db->quoteName('a.state_id'))
					->where('catid = ' . (int) $item->id)
					->where('a.extension = ' . $db->quote('com_content'))
					->group($db->quoteName('condition'));

			$articles = $db->setQuery($query)->loadObjectList();

			foreach ($articles as $article)
			{
				if ($article->condition == Workflow::PUBLISHED)
				{
					$item->count_published = $article->count;
				}

				if ($article->condition == Workflow::UNPUBLISHED)
				{
					$item->count_unpublished = $article->count;
				}

				if ($article->condition == Workflow::TRASHED)
				{
					$item->count_trashed = $article->count;
				}
			}
		}

		return $items;
	}

	/**
	 * Adds Count Items for Tag Manager.
	 *
	 * @param   \stdClass[]  &$items     The content objects
	 * @param   string       $extension  The name of the active view.
	 *
	 * @return  \stdClass[]
	 *
	 * @since   3.6
	 */
	public static function countTagItems(&$items, $extension)
	{
		$db      = \JFactory::getDbo();
		$parts   = explode('.', $extension);
		$section = null;

		if (count($parts) > 1)
		{
			$section = $parts[1];
		}

		$join  = $db->quoteName('#__content') . ' AS c ON ct.content_item_id=c.id';
		$state = 'state';

		if ($section === 'category')
		{
			$join  = $db->quoteName('#__categories') . ' AS c ON ct.content_item_id=c.id';
			$state = 'published as state';
		}

		foreach ($items as $item)
		{
			$item->count_trashed     = 0;
			$item->count_archived    = 0;
			$item->count_unpublished = 0;
			$item->count_published   = 0;
			$query                   = $db->getQuery(true);
			$query->select($state . ', count(*) AS count')
				->from($db->quoteName('#__contentitem_tag_map') . 'AS ct ')
				->where('ct.tag_id = ' . (int) $item->id)
				->where('ct.type_alias =' . $db->q($extension))
				->join('LEFT', $join)
				->group('state');
			$db->setQuery($query);
			$contents = $db->loadObjectList();

			foreach ($contents as $content)
			{
				if ($content->state == 1)
				{
					$item->count_published = $content->count;
				}

				if ($content->state == 0)
				{
					$item->count_unpublished = $content->count;
				}

				if ($content->state == 2)
				{
					$item->count_archived = $content->count;
				}

				if ($content->state == -2)
				{
					$item->count_trashed = $content->count;
				}
			}
		}

		return $items;
	}

	/**
	 * Returns a valid section for articles. If it is not valid then null
	 * is returned.
	 *
	 * @param   string  $section  The section to get the mapping for
	 *
	 * @return  string|null  The new section
	 *
	 * @since   3.7.0
	 */
	public static function validateSection($section)
	{
		if (\JFactory::getApplication()->isClient('site'))
		{
			// On the front end we need to map some sections
			switch ($section)
			{
				// Editing an article
				case 'form':

					// Category list view
				case 'featured':
				case 'category':
					$section = 'article';
			}
		}

		if ($section != 'article')
		{
			// We don't know other sections
			return null;
		}

		return $section;
	}

	/**
	 * Returns valid contexts
	 *
	 * @return  array
	 *
	 * @since   3.7.0
	 */
	public static function getContexts()
	{
		\JFactory::getLanguage()->load('com_content', JPATH_ADMINISTRATOR);

		$contexts = array(
			'com_content.article'    => \JText::_('COM_CONTENT'),
			'com_content.categories' => \JText::_('JCATEGORY')
		);

		return $contexts;
	}

	/**
	 * Check if state can be deleted
	 *
	 * @param   int  $stateID  Id of state to delete
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function canDeleteState($stateID)
	{
		$db    = \JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('id')
			->from($db->quoteName('#__content'))
			->where('state = ' . (int) $stateID);
		$db->setQuery($query);
		$states = $db->loadResult();

		return empty($states);
	}

	/**
	 * Method to filter transitions by given id of state
	 *
	 * @param   array  $transitions  Array of transitions
	 * @param   int    $pk           Id of state
	 * @param   int    $workflow_id  Id of the workflow
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function filterTransitions($transitions, $pk, $workflow_id = 0): array
	{
		return array_values(
			array_filter(
				$transitions,
				function ($var) use ($pk, $workflow_id)
				{
					return in_array($var['from_state_id'], [-1, $pk]) && $var['to_state_id'] != $pk && $workflow_id == $var['workflow_id'];
				}
			)
		);
	}

	/**
	 * Method to change state of multiple ids
	 *
	 * @param   array  $pks        Array of IDs
	 * @param   int    $condition  Condition of the workflow state
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function updateContentState($pks, $condition): bool
	{
		if (empty($pks))
		{
			return false;
		}

		try
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);

			$query->update($db->quoteName('#__content'))
				->set($db->quoteName('state') . '=' . (int) $condition)
				->where($db->quoteName('id') . ' IN (' . implode(', ', $pks) . ')');

			$db->setQuery($query)->execute();
		}
		catch (\Exception $e)
		{
			return false;
		}

		return true;
	}

	/**
	 * Prepares a form
	 *
	 * @param   \Joomla\CMS\Categories\Form  $form  The form to change
	 * @param   array|object                 $data  The form data
	 *
	 * @return void
	 */
	public static function onPrepareForm(Form $form, $data)
	{
		if ($form->getName() != 'com_categories.categorycom_content')
		{
			return;
		}

		$db = Factory::getDbo();

		$data = (array) $data;

		$form->setFieldAttribute('workflow_id', 'default', 'inherit');

		$query = $db->getQuery(true);

		$query	->select($db->quoteName('title'))
				->from($db->quoteName('#__workflows'))
				->where($db->quoteName('default') . ' = 1')
				->where($db->quoteName('published') . ' = 1');

		$defaulttitle = $db->setQuery($query)->loadResult();

		$option = Text::_('COM_CONTENT_WORKFLOW_INHERIT_WORKFLOW_NEW');

		if (!empty($data['id']))
		{
			$category = new Category($db);

			$categories = $category->getPath((int) $data['id']);

			// Remove the current category, because we search vor inherit from parent
			array_shift($categories);

			$option = Text::sprintf('COM_CONTENT_WORKFLOW_INHERIT_WORKFLOW', $defaulttitle);

			if (!empty($categories))
			{
				$categories = array_reverse($categories);

				foreach ($categories as $cat)
				{
					$cat->params = new Registry($cat->params);

					$workflow_id = $cat->params->get('workflow_id');

					if ($workflow_id == 'inherit')
					{
						continue;
					}
					elseif ($workflow_id == 'use_default')
					{
						break;
					}
					elseif ((int) $workflow_id > 0)
					{
					$query	->clear('where')
								->where($db->quoteName('id') . ' = ' . (int) $workflow_id)
								->where($db->quoteName('published') . ' = 1');

						$title = $db->setQuery($query)->loadResult();

						if (!is_null($title))
						{
							$option = Text::sprintf('COM_CONTENT_WORKFLOW_INHERIT_WORKFLOW', $title);

							break;
						}
					}
				}
			}
		}

		$field = $form->getField('workflow_id', 'params');

		$field->addOption($option, ['value' => 'inherit']);

		$field->addOption(Text::sprintf('COM_CONTENT_WORKFLOW_DEFAULT_WORKFLOW', $defaulttitle), ['value' => 'use_default']);

		$field->addOption('- ' . Text::_('COM_CONTENT_WORKFLOWS') . ' -', ['disabled' => 'true']);
	}
}
