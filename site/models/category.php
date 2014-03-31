<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_dzvideo
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
require_once JPATH_COMPONENT.'/helpers/route.php';

/**
 * Dzvideo Component Model
 *
 * @package     Joomla.Site
 * @subpackage  com_dzvideo
 * @since       3.0
 */
class DzvideoModelCategory extends JModelList
{
    /**
     * Model context string.
     *
     * @var     string
     */
    public $_context = 'com_dzvideo.category';

    /**
     * The category context (allows other extensions to derived from this model).
     *
     * @var     string
     */
    protected $_extension = 'com_dzvideo.videos.catid';

    protected $_items = null;
    
    protected $_item = null;
    
    protected $_category = null;
    
    protected $_siblings = null;

    protected $_children = null;

    protected $_parent = null;
    
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
        $query->select($this->getState('list.select', 'a.*'))
            ->from($db->quoteName('#__dzvideo_videos') . ' AS a');
         
        // Filter by category.
        $categoryId = $this->getState('category.id','root');
        if (is_numeric($categoryId))
        {
            $categoryEquals = 'a.catid = '. (int) $categoryId;
            
            $showsubcategories = $this->getState('filter.subcategories'); 
            if ($showsubcategories) {
                $levels = (int) $this->getState('filter.max_category_levels', 1);
                
                if ($categoryId == 0)
                    $categoryId = 1;
                $subQuery = $db->getQuery(true)
                    ->select('sub.id')
                    ->from('#__categories as sub')
                    ->join('INNER', '#__categories as this ON sub.lft > this.lft AND sub.rgt < this.rgt')
                    ->where('this.id = ' . (int) $categoryId);
                if ($levels >= 0)
                {
                    $subQuery->where('sub.level <= this.level + ' . $levels);
                }
                
                // Add the subquery to the main query
                $query->where('(' . $categoryEquals . ' OR a.catid IN (' . $subQuery->__toString() . '))');
            } else {
                $query->where($categoryEquals);
            }
            

                

            //Filter by published category
            $cpublished = $this->getState('filter.c.published');
            if (is_numeric($cpublished))
            {
                $query->where('c.published = ' . (int) $cpublished);
            }
        }
        
        // Join over user
        $query->select('ua.name as created_by_name');
        $query->join('LEFT', '#__users as ua on ua.id = a.created_by');
        
        // Filter by search in title
        $search = $this->getState('list.filter');
        if (!empty($search))
        {
            $search = $db->quote('%' . $db->escape($search, true) . '%');
            $query->where('(a.title LIKE ' . $search . ')');
        }
        
        $published = $this->getState('filter.published', 1);
        $query->where('a.state = '.(int)$published);
        
        // Filter by language
        if ($this->getState('filter.language'))
        {
            $query->where('a.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
        }
              
        // Add the list ordering clause.
        $query->order($db->escape($this->getState('list.ordering', 'a.ordering')) . ' ' . $db->escape($this->getState('list.direction', 'ASC')));
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
        $app = JFactory::getApplication('site');
        $this->setState('filter.extension', $this->_extension);
                        
        // Get the parent id if defined.
        
        $id = $app->input->get('id', 0, 'int');
        $this->setState('category.id', $id);        

        // Load the parameters. Merge Global and Menu Item params into new object
        $params = $app->getParams();
        $menuParams = new JRegistry;

        if ($menu = $app->getMenu()->getActive())
        {
            $menuParams->loadString($menu->params);
        }

        $mergedParams = clone $menuParams;
        $mergedParams->merge($params);

        $this->setState('params', $mergedParams);        

        $this->setState('filter.published', 1);
        
        // Optional filter text
        $this->setState('list.filter', $app->input->getString('filter-search'));
        
        $this->setState('list.ordering',$mergedParams->get('display_ordering'));
        $this->setState('list.direction',$mergedParams->get('display_direction'));
        
        $limit = $app->getUserStateFromRequest($this->context . '.list.limit', 'limit',$mergedParams->get('display_number'), $app->getCfg('list_limit'));
        $this->setState('list.limit', $limit);
        
        $limitstart = $app->input->getInt('limitstart', 0);
        $this->setState('list.start', $limitstart);        
        
        // set the depth of the category query based on parameter
        $showSubcategories = $params->get('show_category_sub', '0');

        if ($showSubcategories)
        {
            $this->setState('filter.max_category_levels', $params->get('show_category_maxlevel', '1'));
            $this->setState('filter.subcategories', true);
        }
        
        $this->setState('filter.language', JLanguageMultilang::isEnabled());
    }
    
    /**
     * Method to get category data for the current category
     *
     * @param   integer  An optional ID
     *
     * @return  object
     * @since   1.5
     */
    public function getCategory()
    {
        if (!is_object($this->_item))
        {
            $categories = JCategories::getInstance('dzvideo');
             
            $this->_item = $categories->get($this->getState('category.id', 'root'));           
            
            if (is_object($this->_item))
            {
                
                $this->_children = $this->_item->getChildren();
                $this->_parent = false;
                if ($this->_item->getParent())
                {
                    $this->_parent = $this->_item->getParent();
                }
                $this->_rightsibling = $this->_item->getSibling();
                $this->_leftsibling = $this->_item->getSibling(false);
                
                $this->_item->link = DZVideoHelperRoute::getCategoryRoute($this->_item, $this->_item->language);
                $this->_item->catlink = Jroute::_(DZVideoHelperRoute::getCategoryRoute($this->_item->id)); 
                if (is_object($this->_parent)) {
                    $this->_parent->link = DZVideoHelperRoute::getCategoryRoute($this->_parent, $this->_parent->language);
                    $this->_parent->catlink = Jroute::_(DZVideoHelperRoute::getCategoryRoute($this->_parent->id)); 
                }
                if (count($this->_children)) {
                    foreach($this->_children as &$child) {
                        $child->link = DZVideoHelperRoute::getCategoryRoute($child, $child->language);
                        $child->catlink = Jroute::_(DZVideoHelperRoute::getCategoryRoute($child->id)); 
                    }
                }
            }
            else
            {
                $this->_children = false;
                $this->_parent = false;
            }
        }

        return $this->_item;
    }

    /**
     * Get the parent category
     *
     * @param   integer  An optional category id. If not supplied, the model state 'category.id' will be used.
     *
     * @return  mixed  An array of categories or false if an error occurs.
     */
    public function getParent()
    {
        if (!is_object($this->_item))
        {
            $this->getCategory();
        }
        return $this->_parent;
    }
    

    /**
     * Get the sibling (adjacent) categories.
     *
     * @return  mixed  An array of categories or false if an error occurs.
     */
    function &getLeftSibling()
    {
        if (!is_object($this->_item))
        {
            $this->getCategory();
        }
        return $this->_leftsibling;
    }

    function &getRightSibling()
    {
        if (!is_object($this->_item))
        {
            $this->getCategory();
        }
        return $this->_rightsibling;
    }

    /**
     * Get the child categories.
     *
     * @param   integer  An optional category id. If not supplied, the model state 'category.id' will be used.
     *
     * @return  mixed  An array of categories or false if an error occurs.
     */
     
    function &getChildren()
    {
        if (!is_object($this->_item))
        {
            $this->getCategory();
        }

        return $this->_children;
    }
}
