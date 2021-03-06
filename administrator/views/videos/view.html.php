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
 * View class for a list of Dzvideo.
 */
class DzvideoViewVideos extends JViewLegacy
{
    protected $items;
    protected $pagination;
    protected $state;

    /**
     * Display the view
     */
    public function display($tpl = null)
    {
        $this->state        = $this->get('State');
        $this->items        = $this->get('Items');
        $this->pagination   = $this->get('Pagination');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new Exception(implode("\n", $errors));
        }
        
        DzvideoHelper::addSubmenu('videos');
        
        $this->addToolbar();
        
        $this->sidebar = JHtmlSidebar::render();
        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @since   1.6
     */
    protected function addToolbar()
    {
        require_once JPATH_COMPONENT.'/helpers/dzvideo.php';

        $state  = $this->get('State');
        $canDo  = DzvideoHelper::getActions($state->get('filter.category_id'));

        JToolBarHelper::title(JText::_('COM_DZVIDEO_TITLE_VIDEOS'), 'videos.png');

        //Check if the form exists before showing the add/edit buttons
        $formPath = JPATH_COMPONENT_ADMINISTRATOR.'/views/video';
        if (file_exists($formPath)) {

            if ($canDo->get('core.create')) {
                JToolBarHelper::addNew('video.add','JTOOLBAR_NEW');
            }

            if ($canDo->get('core.edit') && isset($this->items[0])) {
                JToolBarHelper::editList('video.edit','JTOOLBAR_EDIT');
            }

        }

        if ($canDo->get('core.edit.state')) {

            if (isset($this->items[0]->state)) {
                JToolBarHelper::divider();
                JToolBarHelper::custom('videos.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true);
                JToolBarHelper::custom('videos.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
                JToolbarHelper::custom('videos.featured', 'featured.png', 'featured_f2.png', 'JFEATURE', true);
                JToolbarHelper::custom('videos.unfeatured', 'unfeatured.png', 'featured_f2.png', 'JUNFEATURE', true);
            } else if (isset($this->items[0])) {
                //If this component does not use state then show a direct delete button as we can not trash
                JToolBarHelper::deleteList('', 'videos.delete','JTOOLBAR_DELETE');
            }

            if (isset($this->items[0]->state)) {
                JToolBarHelper::divider();
                JToolBarHelper::archiveList('videos.archive','JTOOLBAR_ARCHIVE');
            }
            if (isset($this->items[0]->checked_out)) {
                JToolBarHelper::custom('videos.checkin', 'checkin.png', 'checkin_f2.png', 'JTOOLBAR_CHECKIN', true);
            }
        }
        
        // Add a batch button
        if ($canDo->get('core.create') && $canDo->get('core.edit') && $canDo->get('core.edit.state'))
        {
            JHtml::_('bootstrap.modal', 'collapseModal');
            $title = JText::_('JTOOLBAR_BATCH');

            // Instantiate a new JLayoutFile instance and render the batch button
            $layout = new JLayoutFile('joomla.toolbar.batch');

            $dhtml = $layout->render(array('title' => $title));
            $bar = JToolBar::getInstance('toolbar');
            $bar->appendButton('Custom', $dhtml, 'batch');
        }
        
        //Show trash and delete for components that uses the state field
        if (isset($this->items[0]->state)) {
            if ($state->get('filter.state') == -2 && $canDo->get('core.delete')) {
                JToolBarHelper::deleteList('', 'videos.delete','JTOOLBAR_EMPTY_TRASH');
                JToolBarHelper::divider();
            } else if ($canDo->get('core.edit.state')) {
                JToolBarHelper::trash('videos.trash','JTOOLBAR_TRASH');
                JToolBarHelper::divider();
            }
        }
        
        

        if ($canDo->get('core.admin')) {
            JToolBarHelper::preferences('com_dzvideo');
        }
        
        //Set sidebar action - New in 3.0
        JHtmlSidebar::setAction('index.php?option=com_dzvideo&view=videos');
        
        $this->extra_sidebar = '';
        
        JHtmlSidebar::addFilter(

            JText::_('JOPTION_SELECT_PUBLISHED'),

            'filter_published',

            JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), "value", "text", $this->state->get('filter.state'), true)

        );
        
        JHtmlSidebar::addFilter(
            JText::_('JOPTION_SELECT_CATEGORY'),
            'filter_category_id',
            JHtml::_('select.options', JHtml::_('category.options', 'com_dzvideo'), 'value', 'text', $this->state->get('filter.category_id'))
        );
        
        $options   = array();
        $options[] = JHtml::_('select.option', '1', JText::_('J1'));
        $options[] = JHtml::_('select.option', '2', JText::_('J2'));
        $options[] = JHtml::_('select.option', '3', JText::_('J3'));
        $options[] = JHtml::_('select.option', '4', JText::_('J4'));
        $options[] = JHtml::_('select.option', '5', JText::_('J5'));
        $options[] = JHtml::_('select.option', '6', JText::_('J6'));
        $options[] = JHtml::_('select.option', '7', JText::_('J7'));
        $options[] = JHtml::_('select.option', '8', JText::_('J8'));
        $options[] = JHtml::_('select.option', '9', JText::_('J9'));
        $options[] = JHtml::_('select.option', '10', JText::_('J10'));
        JHtmlSidebar::addFilter(
            JText::_('JOPTION_SELECT_MAX_LEVELS'),
            'filter_level',
            JHtml::_('select.options', $options, 'value', 'text', $this->state->get('filter.level'))
        );

        
    }
    
    protected function getSortFields()
    {
        return array(
        'a.id' => JText::_('JGRID_HEADING_ID'),
        'a.ordering' => JText::_('JGRID_HEADING_ORDERING'),
        'a.created' => JText::_('JDATE'),
        'a.state' => JText::_('JSTATUS'),
        'a.checked_out' => JText::_('COM_DZVIDEO_VIDEOS_CHECKED_OUT'),
        'a.checked_out_time' => JText::_('COM_DZVIDEO_VIDEOS_CHECKED_OUT_TIME'),
        'a.created_by' => JText::_('COM_DZVIDEO_VIDEOS_CREATED_BY'),
        'a.link' => JText::_('COM_DZVIDEO_VIDEOS_LINK'),
        'a.description' => JText::_('COM_DZVIDEO_VIDEOS_DESCRIPTION'),
        'a.thumbnail' => JText::_('COM_DZVIDEO_VIDEOS_THUMBNAIL'),
        'a.embed' => JText::_('COM_DZVIDEO_VIDEOS_EMBED'),
        );
    }

    
}
