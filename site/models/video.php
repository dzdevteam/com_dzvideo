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

jimport('joomla.application.component.modelform');
jimport('joomla.event.dispatcher');
require_once JPATH_SITE.'/components/com_dzvideo/helpers/route.php';

/**
 * Dzvideo model.
 */
class DzvideoModelVideo extends JModelForm
{

    var $_item = null;

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @since   1.6
     */
    protected function populateState()
    {
        $app = JFactory::getApplication('com_dzvideo');

        // Load state from the request userState on edit or from the passed variable on default
        if (JFactory::getApplication()->input->get('layout') == 'edit') {
            $id = JFactory::getApplication()->getUserState('com_dzvideo.edit.video.id');
        } else {
            $id = JFactory::getApplication()->input->get('id');
            JFactory::getApplication()->setUserState('com_dzvideo.edit.video.id', $id);
        }
        $this->setState('video.id', $id);

        // Load the parameters.
        $params = $app->getParams();
        $params_array = $params->toArray();
        if(isset($params_array['item_id'])){
            $this->setState('video.id', $params_array['item_id']);
        }
        $this->setState('params', $params);
    }

    /**
     * Method to get an ojbect.
     *
     * @param   integer The id of the object to get.
     *
     * @return  mixed   Object on success, false on failure.
     */
    public function &getData($id = null)
    {
        if ($this->_item === null)
        {
            $this->_item = false;

            if (empty($id)) {
                $id = $this->getState('video.id');
            }

            // Get a level row instance.
            $table = $this->getTable();

            // Attempt to load the row.
            if ($table->load($id))
            {
                // Check published state.
                if ($published = $this->getState('filter.published'))
                {
                    if ($table->state != $published) {
                        return $this->_item;
                    }
                }

                // Convert the JTable to a clean JObject.
                $properties = $table->getProperties(1);
                $this->_item = JArrayHelper::toObject($properties, 'JObject');

                $this->_item->videolink    = JRoute::_(DZVideoHelperRoute::getVideoRoute(implode(array($this->_item->id,':',$this->_item->alias)), $this->_item->catid));

                $this->_item->catlink      = Jroute::_(DZVideoHelperRoute::getCategoryRoute($this->_item->catid));

                $registry = new JRegistry();
                $registry->loadString($this->_item->images);
                $this->_item->images = $registry->toArray();

                $registry = new JRegistry();
                $registry->loadString($this->_item->params);
                $this->_item->params = $registry->toArray();

                $this->_item->tags = new JHelperTags;
                $this->_item->tags->getItemTags('com_dzvideo.video', $this->_item->id);

                $this->_item->created_by = new JUser($this->_item->created_by);

                $this->_item->shortdesc = str_replace("\n", "<br />", $this->_item->shortdesc);

                // Get next video
                $videosModel = JModelLegacy::getInstance('Videos', 'DZVideoModel', array('ignore_request' => true));
                $videosModel->setState('filter.date_filtering', 'range');
                $videosModel->setState('filter.exclude_id', $this->_item->id);
                $videosModel->setState('list.limit', 1);
                $videosModel->setState('list.ordering', "a.created");
                $videosModel->setState('filter.start_date_range', $this->_item->created);
                $videosModel->setState('filter.end_date_range', date_format(new DateTime(),"Y-m-d H:i:s"));
                $videosModel->setState('list.direction', 'ASC');

                $videos = $videosModel->getItems();

                if (!empty($videos)) {
                  $this->_item->next_video = $videos[0];
                }

                // Get previous video
                $videosModel->setState('filter.start_date_range', $this->getDbo()->getNullDate());
                $videosModel->setState('filter.end_date_range', $this->_item->created);
                $videosModel->setState('list.direction', 'DESC');
                $videos = $videosModel->getItems();

                if (!empty($videos)) {
                  $this->_item->previous_video = $videos[0];
                }
            } elseif ($error = $table->getError()) {
                $this->setError($error);
            }
        }


        return $this->_item;
    }

    public function getTable($type = 'Video', $prefix = 'DzvideoTable', $config = array())
    {
        $this->addTablePath(JPATH_COMPONENT_ADMINISTRATOR.'/tables');
        return JTable::getInstance($type, $prefix, $config);
    }


    /**
     * Method to check in an item.
     *
     * @param   integer     The id of the row to check out.
     * @return  boolean     True on success, false on failure.
     * @since   1.6
     */
    public function checkin($id = null)
    {
        // Get the id.
        $id = (!empty($id)) ? $id : (int)$this->getState('video.id');

        if ($id) {

            // Initialise the table
            $table = $this->getTable();

            // Attempt to check the row in.
            if (method_exists($table, 'checkin')) {
                if (!$table->checkin($id)) {
                    $this->setError($table->getError());
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Method to check out an item for editing.
     *
     * @param   integer     The id of the row to check out.
     * @return  boolean     True on success, false on failure.
     * @since   1.6
     */
    public function checkout($id = null)
    {
        // Get the user id.
        $id = (!empty($id)) ? $id : (int)$this->getState('video.id');

        if ($id) {

            // Initialise the table
            $table = $this->getTable();

            // Get the current user object.
            $user = JFactory::getUser();

            // Attempt to check the row out.
            if (method_exists($table, 'checkout')) {
                if (!$table->checkout($user->get('id'), $id)) {
                    $this->setError($table->getError());
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Method to get the profile form.
     *
     * The base form is loaded from XML
     *
     * @param   array   $data       An optional array of data for the form to interogate.
     * @param   boolean $loadData   True if the form is to load its own data (default case), false if not.
     * @return  JForm   A JForm object on success, false on failure
     * @since   1.6
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_dzvideo.video', 'video', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) {
            return false;
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
        $data = $this->getData();


                if ( isset($data->tag) ) {
                    // Catch the item tags (string with ',' coma glue)
                    $tags = explode(",",$data->tag);

                    $db = JFactory::getDbo();
                    $namedTags = array(); // Cleaning and initalization of named tags array

                    // Get the tag names of each tag id
                    foreach ($tags as $tag) {

                        $query = $db->getQuery(true);
                        $query->select("title");
                        $query->from('`#__tags`');
                        $query->where( "id=" . intval($tag) );

                        $db->setQuery($query);
                        $row = $db->loadObjectList();

                        // Read the row and get the tag name (title)
                        if (!is_null($row)) {
                            foreach ($row as $value) {
                                if ( $value && isset($value->title) ) {
                                    $namedTags[] = trim($value->title);
                                }
                            }
                        }

                    }

                    // Finally replace the data object with proper information
                    $data->tag = !empty($namedTags) ? implode(', ',$namedTags) : $data->my_tags;
        }
        return $data;
    }

    /**
     * Method to save the form data.
     *
     * @param   array       The form data.
     * @return  mixed       The user id on success, false on failure.
     * @since   1.6
     */
    public function save($data)
    {
        $id = (!empty($data['id'])) ? $data['id'] : (int)$this->getState('video.id');
        $state = (!empty($data['state'])) ? 1 : 0;
        $user = JFactory::getUser();

        if($id) {
            //Check the user can edit this item
            $authorised = $user->authorise('core.edit', 'com_dzvideo.video.'.$id) || $authorised = $user->authorise('core.edit.own', 'com_dzvideo.video.'.$id);
            if($user->authorise('core.edit.state', 'com_dzvideo.video.'.$id) !== true && $state == 1){ //The user cannot edit the state of the item.
                $data['state'] = 0;
            }
        } else {
            //Check the user can create new items in this section
            $authorised = $user->authorise('core.create', 'com_dzvideo');
            if($user->authorise('core.edit.state', 'com_dzvideo.video.'.$id) !== true && $state == 1){ //The user cannot edit the state of the item.
                $data['state'] = 0;
            }
        }

        if ($authorised !== true) {
            JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
            return false;
        }

        $table = $this->getTable();
        if ($table->save($data) === true) {
            return $id;
        } else {
            return false;
        }

    }

     function delete($data)
    {
        $id = (!empty($data['id'])) ? $data['id'] : (int)$this->getState('video.id');
        if(JFactory::getUser()->authorise('core.delete', 'com_dzvideo.video.'.$id) !== true){
            JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
            return false;
        }
        $table = $this->getTable();
        if ($table->delete($data['id']) === true) {
            return $id;
        } else {
            return false;
        }

        return true;
    }

    function getCategoryName($id){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select('title')
            ->from('#__categories')
            ->where('id = ' . $id);
        $db->setQuery($query);
        return $db->loadObject();
    }

    /**
     * Increment the hit counter for the article.
     *
     * @param   integer  $pk  Optional primary key of the article to increment.
     *
     * @return  boolean  True if successful; false otherwise and internal error set.
     */
    public function hit($pk = 0)
    {
        $input = JFactory::getApplication()->input;
        $hitcount = $input->getInt('hitcount', 1);

        if ($hitcount)
        {
            $pk = (!empty($pk)) ? $pk : (int) $this->getState('video.id');

            $table = JTable::getInstance('Video', 'DZVideoTable');
            $table->load($pk);
            $table->hit($pk);
        }

        return true;
    }
}