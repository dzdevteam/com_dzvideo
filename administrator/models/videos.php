<?php

/**
 * @version     1.0.0
 * @package     com_dzvideo
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      DZ Team <dev@dezign.vn> - dezign.vn
 */
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Methods supporting a list of Dzvideo records.
 */
class DzvideoModelvideos extends JModelList {

    /**
     * Constructor.
     *
     * @param    array    An optional associative array of configuration settings.
     * @see        JController
     * @since    1.6
     */
    public function __construct($config = array()) {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                                'id', 'a.id',
                'ordering', 'a.ordering',
                'state', 'a.state',
                'created', 'a.created',
                'created_by', 'a.created_by',
                'modified', 'a.modified',
                'title', 'a.title',
                'alias', 'a.alias',
                'link', 'a.link',
                'description', 'a.description',
                'thumbnail', 'a.thumbnail',
                'image', 'a.image',
                'author', 'a.author',
                'catid', 'a.catid',
                'metakey', 'a.metakey',
                'metadesc', 'a.metadesc',
                'metadata', 'a.metadata',
                'params', 'a.params',
                'language', 'a.language',
                'embed', 'a.embed',
                'tag', 'a.tag',

            );
        }

        parent::__construct($config);
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     */
    protected function populateState($ordering = null, $direction = null) {
        // Initialise variables.
        $app = JFactory::getApplication('administrator');

        // Load the filter state.
        $search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $published = $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_published', '', 'string');
        $this->setState('filter.state', $published);
        
        $categoryId = $this->getUserStateFromRequest($this->context . '.filter.category_id', 'filter_category_id');
        $this->setState('filter.category_id', $categoryId);

        // Load the parameters.
        $params = JComponentHelper::getParams('com_dzvideo');
        $this->setState('params', $params);

        // List state information.
        parent::populateState('a.created', 'desc');
    }

    /**
     * Method to get a store id based on model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param   string      $id A prefix for the store id.
     * @return  string      A store id.
     * @since   1.6
     */
     
 
    protected function getStoreId($id = '') {
        // Compile the store id.
        $id.= ':' . $this->getState('filter.search');
        $id.= ':' . $this->getState('filter.state');
        $id .= ':' . $this->getState('filter.category_id');

        return parent::getStoreId($id);
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return  JDatabaseQuery
     * @since   1.6
     */
    protected function getListQuery() {
        // Create a new query object.
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select(
                $this->getState(
                        'list.select', 'a.*'
                )
        );
        $query->from('`#__dzvideo_videos` AS a');
        
        // Join over the users for the checked out user.
        $query->select('uc.name AS editor');
        $query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');
    
        // Join over the user field 'created_by'
        $query->select('created_by.name AS created_by');
        $query->join('LEFT', '#__users AS created_by ON created_by.id = a.created_by');
        // Join over the category 'catid'
        $query->select('catid.title AS catid');
        $query->join('LEFT', '#__categories AS catid ON catid.id = a.catid');

        
        // Filter by published state
        $published = $this->getState('filter.state');
        if (is_numeric($published)) {
            $query->where('a.state = '.(int) $published);
        } else if ($published === '') {
            $query->where('(a.state IN (0, 1))');
        }
        
        $categoryId = $this->getState('filter.category_id');
        if (is_numeric($categoryId))
        {
            $cat_tbl = JTable::getInstance('Category', 'JTable');
            $cat_tbl->load($categoryId);
            $rgt = $cat_tbl->rgt;
            $lft = $cat_tbl->lft;
            $baselevel = (int) $cat_tbl->level;
            $query->where('catid.lft >= ' . (int) $lft)
                ->where('catid.rgt <= ' . (int) $rgt);
        }
        elseif (is_array($categoryId))
        {
            JArrayHelper::toInteger($categoryId);
            $categoryId = implode(',', $categoryId);
            $query->where('a.catid IN (' . $categoryId . ')');
        }
    
        // Filter by search in title
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = ' . (int) substr($search, 3));
            } else {
                $search = $db->Quote('%' . $db->escape($search, true) . '%');
                $query->where("a.title LIKE $search OR a.shortdesc LIKE $search OR a.description LIKE $search OR a.author LIKE $search");
            }
        }

        // Add the list ordering clause.
        $orderCol = $this->state->get('list.ordering');
        $orderDirn = $this->state->get('list.direction');
        if ($orderCol && $orderDirn) {
            $query->order($db->escape($orderCol . ' ' . $orderDirn));
        }

        return $query;
    }

    public function getItems() {
        $items = parent::getItems();
        
        foreach ($items as $oneItem) {

            if ( isset($oneItem->tag) ) {
                // Catch the item tags (string with ',' coma glue)
                $tags = explode(",",$oneItem->tag);

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
                    $oneItem->tag = !empty($namedTags) ? implode(', ',$namedTags) : $oneItem->tag;
                }
        }
        
        foreach ($items as &$item) {
            $registry = new JRegistry();
            $registry->loadString($item->images);
            $item->images = $registry->toArray();
        }
        
        return $items;
    }

}
