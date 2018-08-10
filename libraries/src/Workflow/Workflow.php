<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Workflow;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * Workflow Class.
 *
 * @since  __DEPLOY_VERSION__
 */
class Workflow
{
	/**
	 * Name of the extension the workflow belong to
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $extension = null;

	protected $options = [];

	protected $names = [
		self::PUBLISHED => 'COM_WORKFLOW_PUBLISHED',
		self::UNPUBLISHED => 'COM_WORKFLOW_UNPUBLISHED',
		self::TRASHED => 'COM_WORKFLOW_TRASHED'
	];

	protected $db;

	/**
	 * Every item with a state which has the condition PUBLISHED is visible/active on the page
	 */
	const PUBLISHED = 1;

	/**
	 * Every item with a state which has the condition UNPUBLISHED is not visible/inactive on the page
	 */
	const UNPUBLISHED = 0;

	/**
	 * Every sitem with a state which has the condition TRASHED is trashed
	 */
	const TRASHED = -2;

	/**
	 * Class constructor
	 *
	 * @param   array  $options  Array of options
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct($options)
	{
		// Required options
		$this->extension  = $options['extension'];

		// Default some optional options
		$this->options['access']      = 'true';
		$this->options['published']   = 1;
		$this->options['countItems']  = 0;

		$this->setOptions($options);
	}

	/**
	 * Returns the translated condition name, based on the given number
	 *
	 * @param   int  $value  The condition ID
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getConditionName($value)
	{
		return ArrayHelper::getValue($this->names, $value, '', 'string');
	}

	/**
	 * Executes a transition to change the current state in the association table
	 *
	 * @param   array|int  $pks            The item IDs, which should use the transition
	 * @param   int        $transition_id  The transition which should be executed
	 *
	 * @return  boolean
	 */
	public function executeTransition($pks, $transition_id)
	{
		if (!is_array($pks))
		{
			$pks = [(int) $pks];
		}

		$pks = ArrayHelper::toInteger($pks);
		$pks = array_filter($pks);

		if (!count($pks))
		{
			return true;
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$select = $db->quoteName(
			[
				't.id',
				't.to_state_id',
				't.from_state_id',
				's.condition',
			]
		);

		$query->select($select)
				->from($db->quoteName('#__workflow_transitions', 't'))
				->leftJoin($db->quoteName('#__workflow_states', 's') . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('t.to_state_id'))
				->where($db->quoteName('t.id') . ' = ' . (int) $transition_id);

		if (!empty($this->options['published']))
		{
			$query->where($db->quoteName('t.published') . ' = 1');
		}

		$transition = $db->setQuery($query)->loadObject();

		// Check if the items can execute this transition
		foreach ($pks as $pk)
		{
			$assoc = $this->getAssociation($pk);

			if (!in_array($transition->from_state_id, [-1, $assoc->state_id]))
			{
				return false;
			}
		}

		$parts = explode('.', $this->extension);

		$component = reset($parts);

		$componentInterface = Factory::getApplication()->bootComponent($component);

		if ($componentInterface instanceof WorkflowServiceInterface)
		{
			$componentInterface->updateContentState($pks, $transition->condition);
		}

		return $this->updateAssociations($pks, $transition->to_state_id);
	}

	/**
	 * Creates an association for the workflow_associations table
	 *
	 * @param   int  $pk     ID of the item
	 * @param   int  $state  ID of state
	 *
	 * @return  boolean
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function createAssociation($pk, $state)
	{
		try
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);

			$query->insert($db->quoteName('#__workflow_associations'))
				->columns($db->quoteName(array('item_id', 'state_id', 'extension')))
				->values((int) $pk . ', ' . (int) $state . ', ' . $db->quote($this->extension));

			$db->setQuery($query)->execute();
		}
		catch (\Exception $e)
		{
			return false;
		}

		return true;
	}

	/**
	 * Update an existing association with a new state
	 *
	 * @param   array  $pks    An Array of item IDs which should be changed
	 * @param   int    $state  The new state ID
	 *
	 * @return  boolean
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function updateAssociations($pks, $state)
	{
		if (!is_array($pks))
		{
			$pks = [$pks];
		}

		$pks = ArrayHelper::toInteger($pks);

		try
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);

			$query->update($db->quoteName('#__workflow_associations'))
				->set($db->quoteName('state_id') . '=' . (int) $state)
				->whereIn($db->quoteName('item_id'), $pks)
				->where($db->quoteName('extension') . '=' . $db->quote($this->extension));

			$db->setQuery($query)->execute();
		}
		catch (\Exception $e)
		{
			return false;
		}

		return true;
	}

	/**
	 * Removes associations form the workflow_associations table
	 *
	 * @param   int  $pks  ID of content
	 *
	 * @return  boolean
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function deleteAssociation($pks)
	{
		$pks = ArrayHelper::toInteger($pks);

		try
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);

			$query
				->delete($db->quoteName('#__workflow_associations'))
				->whereIn($db->quoteName('item_id'), $pks)
				->andWhere($db->quoteName('extension') . '=' . $db->quote($this->extension));

			$db->setQuery($query)->execute();
		}
		catch (\Exception $e)
		{
			return false;
		}

		return true;
	}

	/**
	 * Loads an existing association item with state and item ID
	 *
	 * @param   int  $item_id  The item ID to load
	 *
	 * @return  object
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getAssociation($item_id)
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true);

		$select = $db->quoteName(
			[
				'item_id',
				'state_id'
			]
		);

		$query->select($select)
				->from($db->quoteName('#__workflow_associations'))
				->where($db->quoteName('item_id') . ' = ' . (int) $item_id)
				->where($db->quoteName('extension') . ' = ' . $db->quote($this->extension));

		return $db->setQuery($query)->loadObject();
	}

	/**
	 * Allows to set some optional options, eg. if the access level should be considered.
	 *
	 * @param   array  $options  The new options
	 *
	 * @return  void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function setOptions(array $options)
	{
		if (isset($options['access']))
		{
			$this->options['access'] = $options['access'];
		}

		if (isset($options['published']))
		{
			$this->options['published'] = $options['published'];
		}

		if (isset($options['countItems']))
		{
			$this->options['countItems'] = $options['countItems'];
		}
	}
}
