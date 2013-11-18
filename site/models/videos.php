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
	 * @var		string
	 */
	public $_context = 'com_dzvideo.categories';

	/**
	 * The category context (allows other extensions to derived from this model).
	 *
	 * @var		string
	 */
	protected $_extension = 'com_dzvideo.videos.catid';

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
		$query->select($this->getState('list.select', 'a.*','c.title as catid_title','c.params as catid_params','c.description as catid_desciption'))
			->from($db->quoteName('#__dzvideo_videos') . ' AS a');

		// Filter by category.
		if ($categoryId = $this->getState('category.id'))
		{
		  if ($categoryId > 1) {
    			$query->where('a.catid = ' . (int) $categoryId);
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
        
        // Filter by language
		if ($this->getState('filter.language'))
		{
			$query->where('a.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
		}
              
		// Add the list ordering clause.
		$query->order($db->escape($this->getState('list.ordering','a.ordering')) . ' ' . $db->escape($this->getState('list.direction', 'ASC')));
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

		$this->setState('filter.published',	1);
        
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
