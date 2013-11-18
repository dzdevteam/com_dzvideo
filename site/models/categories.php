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
 * This models supports retrieving lists of article categories.
 *
 * @package     Joomla.Site
 * @subpackage  com_Dzvideo
 */
class DzvideoModelCategories extends JModelList
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

	private $_items = null;
    
    public function __construct($config = array()) {
        parent::__construct($config);
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
		$parentId =  $app->input->get('id', 0, 'int');
        
        if ($parentId == 0) $parentId = 1;
		$this->setState('filter.parentId', $parentId);

		$params = $app->getParams();
		$this->setState('params', $params);

		$this->setState('filter.published',	1);
        
        // Menu parameters
        $input = JFactory::getApplication()->input;
        $menuitemid = $input->getInt( 'Itemid' );  // this returns the menu id number so you can reference parameters
        $menu = JSite::getMenu();
        if ($menuitemid) {
           $menuparams = $menu->getParams( $menuitemid );
           $this->setState('menuparams',$menuparams);
        }
        
        $this->setState('list.catordering',$menuparams->get('catordering'));
        $this->setState('list.direction',$menuparams->get('direction'));
        
        $limit = $app->getUserStateFromRequest('global.list.limit', 'limit',$menuparams->get('display_num'), $app->getCfg('list_limit'));
        $this->setState('list.limit', $limit);
        
        $limitstart = $app->input->getInt('limitstart', 0);
        $this->setState('list.start', $limitstart);        
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id	A prefix for the store id.
	 *
	 * @return  string  A store id.
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= ':'.$this->getState('filter.extension');
		$id	.= ':'.$this->getState('filter.published');
		$id	.= ':'.$this->getState('filter.parentId');

		return parent::getStoreId($id);
	}
    
    protected function getListQuery() {
        // Create a new query object.
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        
        $menuparams = $this->getState('menuparams');
        // Select the required fields from the table.
        $query->select(
                $this->getState(
                        'list.select', 'a.*'
                )
        );
        
        $query->from('#__categories AS a');
        
        $query->where('a.extension = ' . $db->quote($this->_extension))->where('a.parent_id = '.$this->getState('filter.parentId')); 
        
        // Filter by search in title
        $search = $this->getState('filter.search');
        if (!empty($search))
		{
			$search = $db->quote('%' . $db->escape($search, true) . '%');
			$query->where('(a.title LIKE ' . $search . ')');
		}
        
        $published = $this->getState('filter.published', 1);
        $query->where('a.published = '.(int)$published);
              
        $query->order($db->escape($this->getState('list.catordering', 'a.lft')) . ' ' . $db->escape($this->getState('list.direction', 'ASC')));
        
        return $query;
    }

	/**
	 * redefine the function an add some properties to make the styling more easy
	 *
	 * @return mixed An array of data items on success, false on failure.
	 */
	public function getItems()
	{
        $items = parent::getItems();
        foreach ($items as &$item) {
            $item->link     = Jroute::_(DZVideoHelperRoute::getCategoryRoute($item->id));
            $registry = new JRegistry();
            $registry->loadString($item->params);
            $item->params = $registry->toArray();
        }

        return $items;
	}  
}
