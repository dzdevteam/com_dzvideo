<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_dzvideo
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
require_once JPATH_SITE.'/components/com_dzvideo/helpers/route.php';

/**
 * Dzvideo Component Weblink Model
 *
 * @package     Joomla.Site
 * @subpackage  com_dzvideo
 * @since       3.0
 */
class DzvideoModelVideos extends JModelList
{
    /**
     * Model context string.
     *
     * @var     string
     */
    public $_context = 'com_dzvideo.categories';

    /**
     * The category context (allows other extensions to derived from this model).
     *
     * @var     string
     */
    protected $_extension = 'com_dzvideo';

    protected $_items = null;
    
    
    /**
     * Constructor.
     *
     * @param   array  An optional associative array of configuration settings.
     * @see     JController
     * @since   1.6
     */
    public function __construct($config = array()) {
        parent::__construct($config);
    }

    /**
     * Method to get a list of items.
     *
     * @return  mixed  An array of objects on success, false on failure.
     */
    public function getItems()
    {
       
        $items = parent::getItems();
        foreach ($items as &$item) {
            $item->catlink       = Jroute::_(DZVideoHelperRoute::getCategoryRoute($item->catid));
            $item->videolink     = Jroute::_(DZVideoHelperRoute::getVideoRoute(implode(array($item->id,':',$item->alias)), $item->catid));
            
            $registry = new JRegistry();
            $registry->loadString($item->images);
            $item->images = $registry->toArray();
            
            $registry = new JRegistry();
            $registry->loadString($item->params);
            $item->params = $registry->toArray();
            
            if (isset($item->catid_params)) {
                $registry = new JRegistry();
                $registry->loadString($item->catid_params);
                $item->catid_params = $registry->toArray();    
            }
            
            $item->tags = new JHelperTags;
            $item->tags->getItemTags('com_dzvideo.video', $item->id);
        }

        return $items;
    }
    
    /**
     * Method to get a store id based on model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param   string  $id  A prefix for the store id.
     *
     * @return  string  A store id.
     *
     * @since   1.6
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . serialize($this->getState('filter.published'));
        $id .= ':' . $this->getState('filter.access');
        $id .= ':' . $this->getState('featured');
        $id .= ':' . serialize($this->getState('category.id'));
        $id .= ':' . $this->getState('filter.date_filtering');
        $id .= ':' . $this->getState('filter.date_field');
        $id .= ':' . $this->getState('filter.start_date_range');
        $id .= ':' . $this->getState('filter.end_date_range');
        $id .= ':' . $this->getState('filter.relative_date');
        $id .= ':' . $this->getState('filter.language');

        return parent::getStoreId($id);
    }

    /**
     * Method to build an SQL query to load the list data.
     *
     * @return  string    An SQL query
     * @since   1.6
     */
    protected function getListQuery()
    {
        // Create a new query object.
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        
        $menuparams = $this->getState('menuparams');
        
        // Select required fields from the categories.
        $query->select(
            $this->getState(
                'list.select', 
                'a.*, ' .
                'c.title as catid_title, c.params as catid_params, c.description as catid_desciption, ' .
                'CASE WHEN a.publish_up = 0 THEN a.created ELSE a.publish_up END as publish_up'
            )
        )->from($db->quoteName('#__dzvideo_videos') . ' AS a');

        // Filter by category.
        if ($categoryId = $this->getState('category.id'))
        {
            if (is_array($categoryId)) {
                $query->where('a.catid IN (' . implode(',', $categoryId) . ')');
            } else {
                if ($categoryId > 1) {
                    $query->where('a.catid = ' . (int) $categoryId);
                }
            }
        }
        
        $query->join('LEFT', '#__categories AS c ON c.id = a.catid');

        //Filter by published category
        $cpublished = $this->getState('filter.c.published');
        if (is_numeric($cpublished))
        {
            $query->where('c.published = ' . (int) $cpublished);
        }
        
        // Filter by search in title
        $search = $this->getState('list.filter');
        if (!empty($search))
        {
            $search = $db->quote('%' . $db->escape($search, true) . '%');
            $query->where('(a.title LIKE ' . $search . ')');
        }
        $featured = (int)$this->getState('featured') == 1;
        if ($featured) {
            $query->where('a.featured = 1');    
        }
        
        $arrtime = array('all' => 0,'year' => 1,'3month' => 2,'month' => 3,'week' => 4);
        $filter_date = $this->getState('filter.published', 'all');
        
        $published = $this->getState('filter.published', 1);
        $query->where('a.state = '.(int)$published);
        
        // Filter by start and end dates.
        $nullDate   = $db->Quote($db->getNullDate());
        $nowDate    = $db->Quote(JFactory::getDate()->toSql());

        $query->where('(a.publish_up = '.$nullDate.' OR a.publish_up <= '.$nowDate.')');
        $query->where('(a.publish_down = '.$nullDate.' OR a.publish_down >= '.$nowDate.')');
        
        // Filter by language
        if ($this->getState('filter.language'))
        {
            $query->where('a.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
        }
        
        // Filter by tags
        $tags = $this->getState('filter.tag_ids');
        if ($tags) {
            $query->join('INNER', '#__contentitem_tag_map as tm ON a.id = tm.content_item_id AND tm.tag_id IN (' . $tags . ')');
            $query->group('a.id');
        }
        
        // Filter exclude id
        $exclude_id = $this->getState('filter.exclude_id');
        if ($exclude_id > 1) {
            $query->where('a.id != ' . (int) $exclude_id);
        }
        
        // Filter by Date Range or Relative Date
        $dateFiltering = $this->getState('filter.date_filtering', 'off');
        $dateField = $this->getState('filter.date_field', 'a.created');

        switch ($dateFiltering)
        {
            case 'range':
                $startDateRange = $db->quote($this->getState('filter.start_date_range', $nullDate));
                $endDateRange = $db->quote($this->getState('filter.end_date_range', $nullDate));
                $query->where(
                    '(' . $dateField . ' >= ' . $startDateRange . ' AND ' . $dateField .
                        ' <= ' . $endDateRange . ')'
                );
                break;

            case 'relative':
                $relativeDate = (int) $this->getState('filter.relative_date', 0);
                $query->where(
                    $dateField . ' >= DATE_SUB(' . $nowDate . ', INTERVAL ' .
                        $relativeDate . ' DAY)'
                );
                break;

            case 'off':
            default:
                break;
        }
              
        // Add the list ordering clause.
        $query->order($db->escape($this->getState('list.ordering','a.created')) . ' ' . $db->escape($this->getState('list.direction', 'DESC')));
        return $query;
    }
    
    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @since   1.6
     */
    protected function populateState($ordering = null, $direction = null)
    {
        $app = JFactory::getApplication();
        $this->setState('filter.extension', $this->_extension);
                        
        // Get the parent id if defined.
        
        $id = $app->input->get('id', 0, 'int'); 
        $this->setState('category.id', $id);        

        $params = $app->getParams();
        $this->setState('params', $params);

        $this->setState('filter.published', 1);
        
        // Optional filter text
        $this->setState('list.filter', $app->input->getString('filter-search'));
        
        // Menu parameters
        $input = JFactory::getApplication()->input;
        $menuitemid = $input->getInt( 'Itemid' );  // this returns the menu id number so you can reference parameters
        $menu = JSite::getMenu();
        if ($menuitemid) {
           $menuparams = $menu->getParams( $menuitemid );
           $this->setState('menuparams',$menuparams);
        }
        
        $this->setState('list.featured',$menuparams->get('featured'));
        
        $this->setState('list.filter_date',$menuparams->get('filter_date'));
        
        $this->setState('list.ordering',$menuparams->get('ordering'));
        $this->setState('list.direction',$menuparams->get('direction'));
        
        $limit = $app->getUserStateFromRequest('global.list.limit', 'limit',$menuparams->get('display_num'), $app->getCfg('list_limit'));
        $this->setState('list.limit', $limit);
        
        $limitstart = $app->input->getInt('limitstart', 0);
        $this->setState('list.start', $limitstart);        
        
        $this->setState('filter.language', JLanguageMultilang::isEnabled());
    }
}
