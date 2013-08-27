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
require_once JPATH_COMPONENT.'/helpers/route.php';

/**
 * Methods supporting a list of Dzvideo records.
 */
class DzvideoModelCategory extends JModelList {
    
    protected $_item = null;
            
    protected $_category = null;
    
   	protected $_siblings = null;

	protected $_children = null;

	protected $_parent = null;

    /**
     * Constructor.
     *
     * @param    array    An optional associative array of configuration settings.
     * @see        JController
     * @since    1.6
     */
    public function __construct($config = array()) {
        parent::__construct($config);
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @since	1.6
     */
    protected function populateState($ordering = null, $direction = null) {

        // Initialise variables.
        $app = JFactory::getApplication();
                
        $params = JComponentHelper::getParams('com_dzvideo');
        $menuParams = new JRegistry;
        $maxlevel = -1;
        if ($menu = $app->getMenu()->getActive())
		{
            $menuParams->loadString($menu->params); 
            $maxlevel = $menu->query['maxLevel'];
		}

		$mergedParams = clone $menuParams;
		$mergedParams->merge($params);
        $params       = $mergedParams;
        
        // List state information
        $limit = $app->getUserStateFromRequest('global.list.limit', 'limit',$params->get('display_num'), $app->getCfg('list_limit'));
        $this->setState('list.limit', $limit);

        $limitstart = $app->input->getInt('limitstart', 0);
        $this->setState('list.start', $limitstart);
        
        // Optional filter text
		$this->setState('list.filter', $app->input->getString('filter-search'));
        
        $orderCol = $app->input->get('filter_order', 'ordering');
		if (!in_array($orderCol, $this->filter_fields))
		{
			$orderCol = 'ordering';
		}
		$this->setState('list.ordering', $orderCol);
        
        $listOrder = $app->input->get('filter_order_Dir', 'ASC');
		if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', '')))
		{
			$listOrder = 'ASC';
		}
		$this->setState('list.direction', $listOrder);

		$id = $app->input->get('id', 0, 'int');
		$this->setState('category.id', $id);

		if(empty($ordering)) {
			$ordering = 'a.ordering';
		}

        $this->setState('maxlevel',$maxlevel);        
        $this->setState('params', $mergedParams);
        
        // List state information.
        parent::populateState($ordering, $direction);
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return	JDatabaseQuery
     * @since	1.6
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
    
		// Join over the created by field 'created_by'
		$query->select('created_by.name AS created_by');
		$query->join('LEFT', '#__users AS created_by ON created_by.id = a.created_by');
		      
        // Join over the category 'catid'
		$query->select('catid.title AS catid_title, catid.alias AS catid_alias');
		$query->join('LEFT', '#__categories AS catid ON catid.id = a.catid');
        
        $subquery = 'SELECT cat.id as id FROM #__categories AS cat JOIN #__categories AS parent ';
		$subquery .= 'ON cat.lft BETWEEN parent.lft AND parent.rgt ';
		$subquery .= 'WHERE parent.extension = ' . $db->quote('com_dzvideo.videos.catid').' AND parent.id ='.$this->getState('category.id', 'root');
         
        $maxlevel = (int)$this->getState('maxlevel');
        if ($maxlevel != -1) {
            $subquery .= " AND cat.level <= parent.level + $maxlevel";
        }
        
        $query->where('a.catid IN ('.$subquery.')'); 
        
        // Filter by search in title
        $search = $this->getState('filter.search');
        if (!empty($search))
		{
			$search = $db->quote('%' . $db->escape($search, true) . '%');
			$query->where('(a.title LIKE ' . $search . ')');
		}
        
              
        $query->order($db->escape($this->getState('list.ordering', 'a.ordering')) . ' ' . $db->escape($this->getState('list.direction', 'ASC')));

        return $query;
    }
    
    protected function getdisplayyoutube($link){
        if (preg_match("/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?&\"'>]+)/", $link, $matches)) {
            $video_id = $matches[1];           
        } 
        return $video_id;
    }
        
    public function getItems() {
        $items = parent::getItems();
        
        foreach ($items as &$item) {
            $item->videolink    = JRoute::_(DZVideoHelperRoute::getVideoRoute(implode(array($item->id,':',$item->alias)), $item->catid));
            $item->catlink      = Jroute::_(DZVideoHelperRoute::getCategoryRoute($item->catid));
            $item->vcode        = $this->getdisplayyoutube($item->link);
        }
        return $items;
    }
}
