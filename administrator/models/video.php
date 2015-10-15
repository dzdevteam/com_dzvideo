<?php
/**
 * @version     1.0.0
 * @package     com_dzvideo
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      DZ Team <dev@dezign.vn> - dezign.vn
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');
require_once JPATH_COMPONENT_ADMINISTRATOR.'/helpers/dzvideo.php';

/**
 * Dzvideo model.
 */
class DzvideoModelvideo extends JModelAdmin
{
    /**
     * @var     string  The prefix to use with controller messages.
     * @since   1.6
     */
    protected $text_prefix = 'COM_DZVIDEO';


    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param   type    The table type to instantiate
     * @param   string  A prefix for the table class name. Optional.
     * @param   array   Configuration array for model. Optional.
     * @return  JTable  A database object
     * @since   1.6
     */
    public function getTable($type = 'Video', $prefix = 'DzvideoTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Method to get the record form.
     *
     * @param   array   $data       An optional array of data for the form to interogate.
     * @param   boolean $loadData   True if the form is to load its own data (default case), false if not.
     * @return  JForm   A JForm object on success, false on failure
     * @since   1.6
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Initialise variables.
        $app    = JFactory::getApplication();

        // Get the form.
        $form = $this->loadForm('com_dzvideo.video', 'video', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) {
            return false;
        }
        
        $user = JFactory::getUser();

        // Check for existing article.
        // Modify the form based on Edit State access controls.
        $id = JFactory::getApplication()->input->get('id', 0);
        if ($id != 0 && (!$user->authorise('core.edit.state', 'com_dzvideo.video.' . (int) $id))
            || ($id == 0 && !$user->authorise('core.edit.state', 'com_dzvideo')))
        {
            // Disable fields for display.
            $form->setFieldAttribute('featured', 'disabled', 'true');
            $form->setFieldAttribute('publish_up', 'disabled', 'true');
            $form->setFieldAttribute('publish_down', 'disabled', 'true');
            $form->setFieldAttribute('state', 'disabled', 'true');

            // Disable fields while saving.
            // The controller has already verified this is an article you can edit.
            $form->setFieldAttribute('featured', 'filter', 'unset');
            $form->setFieldAttribute('publish_up', 'filter', 'unset');
            $form->setFieldAttribute('publish_down', 'filter', 'unset');
            $form->setFieldAttribute('state', 'filter', 'unset');
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  mixed   The data for the form.
     * @since   1.6
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = JFactory::getApplication()->getUserState('com_dzvideo.edit.video.data', array());

        if (empty($data)) {
            $data = $this->getItem();
            
        }

        return $data;
    }

    /**
     * Method to get a single record.
     *
     * @param   integer The id of the primary key.
     *
     * @return  mixed   Object on success, false on failure.
     * @since   1.6
     */
    public function getItem($pk = null)
    {
        if ($item = parent::getItem($pk)) {
          
            $registry = new JRegistry();
            $registry->loadString($item->images);
            $item->images = $registry->toArray();

            //Do any procesing on fields here if needed
            $registry = new JRegistry();
            $registry->loadString($item->metadata);
            $item->metadata = $registry->toArray();
            
            if (!empty($item->id))
            {
                $item->tags = new JHelperTags;
                $item->tags->getTagIds($item->id, 'com_dzvideo.video');
            }

        }

        return $item;
    }

    /**
     * Prepare and sanitise the table prior to saving.
     *
     * @since   1.6
     */
    protected function prepareTable($table)
    {
        jimport('joomla.filter.output');
        // Set the publish date to now
        $db = $this->getDbo();
        
        if ($table->state == 1 && (int) $table->publish_up == 0)
        {
            $table->publish_up = JFactory::getDate()->toSql();
        }

        if ($table->state == 1 && intval($table->publish_down) == 0)
        {
            $table->publish_down = $db->getNullDate();
        }

        if (empty($table->id)) {

            // Set ordering to the last item if not set
            if (@$table->ordering === '') {
                $db = JFactory::getDbo();
                $db->setQuery('SELECT MAX(ordering) FROM #__dzvideo_videos');
                $max = $db->loadResult();
                $table->ordering = $max+1;
            }
        }
    }
    
    public function save($data)
    {
        $dispatcher = JEventDispatcher::getInstance();
        $table = $this->getTable();
        
        $input = JFactory::getApplication()->input;

        if ((!empty($data['tags']) && $data['tags'][0] != ''))
        {
            $table->newTags = $data['tags'];
        }

        $key = $table->getKeyName();
        $pk = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');
        $isNew = true;

        // Include the content plugins for the on save events.
        JPluginHelper::importPlugin('content');

        // Allow an exception to be thrown.
        try
        {
            // Load the row if saving an existing record.
            if ($pk > 0)
            {
                $table->load($pk);
                $isNew = false;
            }
            $form = $input->getVar('jform');
  
            if ($form['getinfo'] == 1) {

                // new item or retrieve new youtube info
                if (isset($form['images']) && is_array($form['images'])) {
                    $images     = $form['images'];
                    if (isset($images['mqdefault'])) {
                        //clone new thumb & medium to hosting
                        $links = DzvideoHelper::generateThumbs($images['mqdefault'],$form['videoid']);
                        $data['images']['thumb'] = $links['thumb'];
                        $data['images']['medium'] = $links['medium'];
                    }
                }
            }
            // Bind the data.
            if (!$table->bind($data))
            {
                $this->setError($table->getError());
                return false;
            }

            // Prepare the row for saving
            $this->prepareTable($table);

            // Check the data.
            if (!$table->check())
            {
                $this->setError($table->getError());
                return false;
            }

            // Trigger the onContentBeforeSave event.
            $result = $dispatcher->trigger($this->event_before_save, array($this->option . '.' . $this->name, $table, $isNew));
            if (in_array(false, $result, true))
            {
                $this->setError($table->getError());
                return false;
            }

            // Store the data.
            if (!$table->store())
            {
                $this->setError($table->getError());
                return false;
            }

            // Clean the cache.
            $this->cleanCache();

            // Trigger the onContentAfterSave event.
            $dispatcher->trigger($this->event_after_save, array($this->option . '.' . $this->name, $table, $isNew));
        }
        catch (Exception $e)
        {
            $this->setError($e->getMessage());

            return false;
        }

        $pkName = $table->getKeyName();

        if (isset($table->$pkName))
        {
            $this->setState($this->getName() . '.id', $table->$pkName);
        }
        $this->setState($this->getName() . '.new', $isNew);

        return true;
    }
    
    public function delete(&$pks)
    {
        $dispatcher = JEventDispatcher::getInstance();
        $pks = (array) $pks;
        $table = $this->getTable();

        // Include the content plugins for the on delete events.
        JPluginHelper::importPlugin('content');

        // Iterate the items to delete each one.
        foreach ($pks as $i => $pk)
        {

            if ($table->load($pk))
            {

                if ($this->canDelete($table))
                {

                    $context = $this->option . '.' . $this->name;

                    // Trigger the onContentBeforeDelete event.
                    $result = $dispatcher->trigger($this->event_before_delete, array($context, $table));
                    if (in_array(false, $result, true))
                    {
                        $this->setError($table->getError());
                        return false;
                    }
                    
                    // Get the images' paths
                    $images = new JRegistry();
                    $images->loadString($table->images);
                    $images = $images->toArray();

                    if (!$table->delete($pk))
                    {
                        $this->setError($table->getError());
                        return false;
                    }
                    
                    // We need to delete images file after finishing deleting a row in #_dzvideo_videos
                    if (JFile::exists(JPATH_ROOT.'/'.$images['thumb'])) {
                        JFile::delete(JPATH_ROOT.'/'.$images['thumb']);
                    }
                    
                    if (JFile::exists(JPATH_ROOT.'/'.$images['medium'])) {
                        JFile::delete(JPATH_ROOT.'/'.$images['medium']);
                    }

                    // Trigger the onContentAfterDelete event.
                    $dispatcher->trigger($this->event_after_delete, array($context, $table));

                }
                else
                {

                    // Prune items that you can't change.
                    unset($pks[$i]);
                    $error = $this->getError();
                    if ($error)
                    {
                        JLog::add($error, JLog::WARNING, 'jerror');
                        return false;
                    }
                    else
                    {
                        JLog::add(JText::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'), JLog::WARNING, 'jerror');
                        return false;
                    }
                }

            }
            else
            {
                $this->setError($table->getError());
                return false;
            }
        }

        // Clear the component's cache
        $this->cleanCache();

        return true;
    }

    /**
     * Batch copy items to a new category or current.
     * Actually we don't allow copy because of no video reupload
     * Leave this function here to get meaningful error, not duplicating alias error
     *
     * @param   integer  $value     The new category.
     * @param   array    $pks       An array of row IDs.
     * @param   array    $contexts  An array of item contexts.
     *
     * @return  mixed  An array of new IDs on success, boolean false on failure.
     *
     * @since   11.1
     */
    protected function batchCopy($value, $pks, $contexts)
    {
        $categoryId = (int) $value;
        

        $newIds = array();

        if (!parent::checkCategoryId($categoryId))
        {
            return false;
        }

        // Parent exists so we let's proceed
        while (!empty($pks))
        {
            // Pop the first ID off the stack
            $pk = array_shift($pks);

            $this->table->reset();

            // Check that the row actually exists
            if (!$this->table->load($pk))
            {
                if ($error = $this->table->getError())
                {
                    // Fatal error
                    $this->setError($error);

                    return false;
                }
                else
                {
                    // Not fatal error
                    $this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_BATCH_MOVE_ROW_NOT_FOUND', $pk));
                    continue;
                }
            }

            // Alter the title & alias
            $table = $this->getTable();
            while ($table->load(array('alias' => $this->table->alias))) {
                $this->table->title = JString::increment($this->table->title);
                $this->table->alias = JString::increment($this->table->alias, 'dash');
            }

            // Reset the ID because we are making a copy
            $this->table->id = 0;

            // Reset hits because we are making a copy
            $this->table->hits = 0;

            // Unpublish because we are making a copy
            $this->table->state = 0;

            // New category ID
            $this->table->catid = $categoryId;

            // TODO: Deal with ordering?
            // $table->ordering    = 1;

            // Get the featured state
            $featured = $this->table->featured;

            // Check the row.
            if (!$this->table->check())
            {
                $this->setError($this->table->getError());
                return false;
            }

            parent::createTagsHelper($this->tagsObserver, $this->type, $pk, $this->typeAlias, $this->table);

            // Store the row.
            if (!$this->table->store())
            {
                $this->setError($this->table->getError());
                return false;
            }

            // Get the new item ID
            $newId = $this->table->get('id');

            // Add the new ID to the array
            $newIds[$pk] = $newId;
        }

        // Clean the cache
        $this->cleanCache();

        return $newIds;
    }
    
    /**
	 * Method to toggle the featured setting of articles.
	 *
	 * @param   array    $pks    The ids of the items to toggle.
	 * @param   integer  $value  The value to toggle to.
	 *
	 * @return  boolean  True on success.
	 */
	public function featured($pks, $value = 0)
	{
		// Sanitize the ids.
		$pks = (array) $pks;
		JArrayHelper::toInteger($pks);

		if (empty($pks))
		{
			$this->setError(JText::_('COM_DZVIDEO_NO_ITEM_SELECTED'));

			return false;
		}

		try
		{
			$db = $this->getDbo();
			$query = $db->getQuery(true)
						->update($db->quoteName('#__dzvideo_videos'))
						->set('featured = ' . (int) $value)
						->where('id IN (' . implode(',', $pks) . ')');
			$db->setQuery($query);
			$db->execute();
            
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());
			return false;
		}

		$this->cleanCache();

		return true;
	}
}
