<?php
/**
 * @version     1.0.0
 * @package     com_dzvideo
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      DZ Team <dev@dezign.vn> - dezign.vn
 */

// no direct access
defined('_JEXEC') or die;

$app = JFactory::getApplication();

if ($app->isSite())
{
    JSession::checkToken('get') or die(JText::_('JINVALID_TOKEN'));
}

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');
JHTML::_('behavior.modal()'); 

// Import CSS
$document = JFactory::getDocument();
$document->addStyleSheet('components/com_dzvideo/assets/css/dzvideo.css');

$user   = JFactory::getUser();
$userId = $user->get('id');
$listOrder  = $this->state->get('list.ordering');
$listDirn   = $this->state->get('list.direction');
$canOrder   = $user->authorise('core.edit.state', 'com_dzvideo');
$saveOrder  = $listOrder == 'a.ordering';
if ($saveOrder)
{
    $saveOrderingUrl = 'index.php?option=com_dzvideo&task=videos.saveOrderAjax&tmpl=component';
    JHtml::_('sortablelist.sortable', 'videoList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}
$function  = $app->input->getCmd('function', 'dzvideoSelectVideo');
$form      = $app->input->getCmd('form', '');
$sortFields = $this->getSortFields();
?>
<script type="text/javascript">
    Joomla.orderTable = function() {
        table = document.getElementById("sortTable");
        direction = document.getElementById("directionTable");
        order = table.options[table.selectedIndex].value;
        if (order != '<?php echo $listOrder; ?>') {
            dirn = 'asc';
        } else {
            dirn = direction.options[direction.selectedIndex].value;
        }
        Joomla.tableOrdering(order, dirn, '');
    }
 
</script>

<?php
//Joomla Component Creator code to allow adding non select list filters
if (!empty($this->extra_sidebar)) {
    $this->sidebar .= $this->extra_sidebar;
}
?>

<?php 

$videoparams    = JComponentHelper::getParams('com_dzvideo');
$video_height   = $videoparams->get('video_height');  
$video_width    = $videoparams->get('video_width');
$thumb_height   = $videoparams->get('thumb_height');  
$thumb_width    = $videoparams->get('thumb_width');

?>

<form action="<?php echo JRoute::_('index.php?option=com_dzvideo&view=videos&layout=modal&tmpl=component&function='.$function.'&'.JSession::getFormToken().'=1&form='.$form); ?>'); ?>" method="post" name="adminForm" id="adminForm">  
    <div id="filter-bar" class="btn-toolbar">
        <div class="filter-search btn-group pull-left">
            <label for="filter_search" class="element-invisible"><?php echo JText::_('JSEARCH_FILTER');?></label>
            <input type="text" name="filter_search" id="filter_search" placeholder="<?php echo JText::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('JSEARCH_FILTER'); ?>" />
        </div>
        <div class="btn-group pull-left">
            <button class="btn hasTooltip" type="submit" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
            <button class="btn hasTooltip" type="button" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.id('filter_search').value='';this.form.submit();"><i class="icon-remove"></i></button>
        </div>
        <div class="btn-group pull-right hidden-phone">
            <label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC');?></label>
            <?php echo $this->pagination->getLimitBox(); ?>
        </div>
        <div class="btn-group pull-right hidden-phone">
            <label for="directionTable" class="element-invisible"><?php echo JText::_('JFIELD_ORDERING_DESC');?></label>
            <select name="directionTable" id="directionTable" class="input-medium" onchange="Joomla.orderTable()">
                <option value=""><?php echo JText::_('JFIELD_ORDERING_DESC');?></option>
                <option value="asc" <?php if ($listDirn == 'asc') echo 'selected="selected"'; ?>><?php echo JText::_('JGLOBAL_ORDER_ASCENDING');?></option>
                <option value="desc" <?php if ($listDirn == 'desc') echo 'selected="selected"'; ?>><?php echo JText::_('JGLOBAL_ORDER_DESCENDING');?></option>
            </select>
        </div>
        <div class="btn-group pull-right">
            <label for="sortTable" class="element-invisible"><?php echo JText::_('JGLOBAL_SORT_BY');?></label>
            <select name="sortTable" id="sortTable" class="input-medium" onchange="Joomla.orderTable()">
                <option value=""><?php echo JText::_('JGLOBAL_SORT_BY');?></option>
                <?php echo JHtml::_('select.options', $sortFields, 'value', 'text', $listOrder);?>
            </select>
        </div>
    </div>
    <div class="clearfix"> </div>
    <table class="table table-striped" id="videoList">
        <thead>
            <tr>
            <?php if (isset($this->items[0]->ordering)): ?>
                <th width="1%" class="nowrap center hidden-phone">
                    <?php echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>
                </th>
            <?php endif; ?>
            <?php if (isset($this->items[0]->state)): ?>
                <th width="1%" class="nowrap center">
                    <?php echo JHtml::_('grid.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
                </th>
            <?php endif; ?>
            
            <th class='left'>
            <?php echo JText::_('COM_DZVIDEO_VIDEOS_IMAGE'); ?>
            </th>
            <th class='left'>
            <?php echo JHtml::_('grid.sort',  'COM_DZVIDEO_VIDEOS_TITLE', 'a.title', $listDirn, $listOrder); ?>
            </th>    
            <th class='left'>
            <?php echo JHtml::_('grid.sort',  'COM_DZVIDEO_VIDEOS_CATID', 'a.catid', $listDirn, $listOrder); ?>
            </th>
            <th class='left'>
            <?php echo JText::_('COM_DZVIDEO_VIDEOS_INFORMATION'); ?>
            </th>
            <?php if (isset($this->items[0]->id)): ?>
                <th width="1%" class="nowrap center hidden-phone">
                    <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
                </th>
            <?php endif; ?>
            </tr>
        </thead>
        <tfoot>
            <?php 
            if(isset($this->items[0])){
                $colspan = count(get_object_vars($this->items[0]));
            }
            else{
                $colspan = 10;
            }
        ?>
        <tr>
            <td colspan="<?php echo $colspan ?>">
                <?php echo $this->pagination->getListFooter(); ?>
            </td>
        </tr>
        </tfoot>
        <tbody>
        <?php foreach ($this->items as $i => $item) :
            $ordering   = ($listOrder == 'a.ordering');
            $canCreate  = $user->authorise('core.create',       'com_dzvideo');
            $canEdit    = $user->authorise('core.edit',         'com_dzvideo');
            $canCheckin = $user->authorise('core.manage',       'com_dzvideo');
            $canChange  = $user->authorise('core.edit.state',   'com_dzvideo');
            
            $image          = array();
            $image        = $item->images;
            
            $display_image  = JUri::root().'images/dzvideo/120x80.gif';
            if (isset($image['custom']) && !empty($image['custom']) && JFile::exists(JPATH_ROOT.'/'.$image['custom'])) {
                $display_image = JUri::root().$image['custom'];
            } elseif (isset($image['thumb']) && !empty($image['thumb']) && JFile::exists(JPATH_ROOT.'/'.$image['thumb'])) {
                $display_image = JUri::root().$image['thumb']; 
            }
            
            
            ?>
            <tr class="row<?php echo $i % 2; ?>">
                
            <?php if (isset($this->items[0]->ordering)): ?>
                <td class="order nowrap center hidden-phone">
                <?php if ($canChange) :
                    $disableClassName = '';
                    $disabledLabel    = '';
                    if (!$saveOrder) :
                        $disabledLabel    = JText::_('JORDERINGDISABLED');
                        $disableClassName = 'inactive tip-top';
                    endif; ?>
                    <span class="sortable-handler hasTooltip <?php echo $disableClassName?>" title="<?php echo $disabledLabel?>">
                        <i class="icon-menu"></i>
                    </span>
                    <input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering;?>" class="width-20 text-area-order " />
                <?php else : ?>
                    <span class="sortable-handler inactive" >
                        <i class="icon-menu"></i>
                    </span>
                <?php endif; ?>
                </td>
            <?php endif; ?>
            <?php if (isset($this->items[0]->state)): ?>
                <td class="center">
                    <?php echo JHtml::_('jgrid.published', $item->state, $i, 'videos.', $canChange, 'cb'); ?>
                </td>
            <?php endif; ?>
            <td>
                <?php 
                if (isset($item->videoid)) { 
                ?>
                    <a class="modal " href="http://www.youtube-nocookie.com/embed/<?php echo $item->videoid;?>" rel="{handler: 'iframe', size: {x: <?php echo $video_width; ?> , y: <?php echo $video_height; ?>}} " &tmpl=component />
                    <img height="<?php echo $thumb_height; ?>" width="<?php echo $thumb_width; ?>" src="<?php echo $display_image; ?>" alt="<?php echo $item->title; ?>" title="<?php echo $item->title; ?>" />
                    </a>
                <?php } else { ?>
                    <img height="<?php echo $thumb_height; ?>" width="<?php echo $thumb_width; ?>" src="<?php echo $display_image; ?>" alt="<?php echo $item->title; ?>" title="<?php echo $item->title; ?>" />
                <?php } ?>
            </td>
            <td>
                <?php if (isset($item->checked_out) && $item->checked_out) : ?>
                    <?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'videos.', $canCheckin); ?>
                <?php endif; ?>
                <?php if ($canEdit) : ?>
                    <a href="#" onclick="window.parent.<?php echo $this->escape($function); ?>(<?php echo $item->id; ?>, '<?php echo $form; ?>'); return false;">
                    <?php echo $this->escape($item->title); ?></a>
                <?php else : ?>
                    <?php echo $this->escape($item->title); ?>
                <?php endif; ?>
            </td>
            <td>
                <?php echo $item->catid; ?>
            </td>
            <td>
                <?php echo JTEXT::_('COM_DZVIDEO_VIDEOS_DURATION').': '.str_pad(floor($item->length/60),2,'0',STR_PAD_LEFT).':'.str_pad(floor($item->length%60),2,'0',STR_PAD_LEFT); ?>
                <br/>
                <?php echo JTEXT::_('COM_DZVIDEO_VIDEOS_DIMENSION').': '.$item->width.'x'.$item->height; ?>
                <br/>
                <?php echo JTEXT::_('COM_DZVIDEO_VIDEOS_AUTHOR').': '.$item->author; ?>
            </td>

            <?php if (isset($this->items[0]->id)): ?>
                <td class="center hidden-phone">
                    <?php echo (int) $item->id; ?>
                </td>
            <?php endif; ?>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
    <?php echo JHtml::_('form.token'); ?>
</form>        

        
