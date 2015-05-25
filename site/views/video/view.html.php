<?php

/**
 * @version     1.0.0
 * @package     com_dzvideo
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      DZ Team <dev@dezign.vn> - dezign.vn
 */
// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View to edit
 */
class DzvideoViewVideo extends JViewLegacy {

    protected $state;
    protected $item;
    protected $params;

    /**
     * Display the view
     */
    public function display($tpl = null) {

        $app    = JFactory::getApplication();
        $user       = JFactory::getUser();
        $dispatcher = JEventDispatcher::getInstance();

        $this->state = $this->get('State');
        $this->item = $this->get('Data');
        $this->params = $app->getParams('com_dzvideo');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new Exception(implode("\n", $errors));
        }
        
        if (!$this->item) {
            JError::raiseError(404, 'Not Found');
        }

        $this->item->catid_title = ($this->item->catid) ? $this->getModel()->getCategoryName($this->item->catid)->title : '';

        if($this->_layout == 'edit') {

            $authorised = $user->authorise('core.create', 'com_dzvideo');

            if ($authorised !== true) {
                throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
            }
        }
        
        JPluginHelper::importPlugin('dz');
        $dispatcher->trigger('onTextPrepare', array ('com_dzvideo.video.description', &$this->item->description));

        // Increase hit counter
        $this->getModel()->hit();

        $this->_prepareDocument();

        parent::display($tpl);
    }


    /**
     * Prepares the document
     */
    protected function _prepareDocument()
    {
        $app    = JFactory::getApplication();
        $menus  = $app->getMenu();
        $title  = null;

        $pathway = $app->getPathway();
        $pathway->addItem($this->item->title);

        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        $menu = $menus->getActive();
        if($menu)
        {
            $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
        } else {
            $this->params->def('page_heading', JText::_('com_dzvideo_DEFAULT_PAGE_TITLE'));
        }
        $title = $this->params->get('page_title', '');
        if ($menu && ($menu->query['view'] != 'video' || $this->item->id != (int) @$menu->query['id'])) {
            $title = $this->item->title;
        }
        if (empty($title)) {
            $title = $app->getCfg('sitename');
        }
        elseif ($app->getCfg('sitename_pagetitles', 0) == 1) {
            $title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
        }
        elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
            $title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
        }
        $this->document->setTitle($title);

        if ($this->params->get('menu-meta_description'))
        {
            $this->document->setDescription($this->params->get('menu-meta_description'));
        }

        if ($this->params->get('menu-meta_keywords'))
        {
            $this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
        }

        if ($this->params->get('robots'))
        {
            $this->document->setMetadata('robots', $this->params->get('robots'));
        }

        // Facebook meta tag
        $this->document->addCustomTag("<meta property='og:title' content='{$this->item->title}' />");
        $this->document->addCustomTag("<meta property='og:url' content='" . JUri::current() . "' />");
        $this->document->addCustomTag("<meta property=\"og:image\" content=\"" . JUri::root() . $this->item->images['medium'] . "\" />");
        $this->document->addCustomTag("<meta property=\"og:description\" content=\"" . str_replace(array('<br />', '"'), array("\n", '&quot;'), (empty($this->item->description) ? $this->item->shortdesc : $this->item->description)) . "\" />");
        $this->document->addCustomTag("<meta property=\"og:type\" content=\"video.other\" />");
    }

}
