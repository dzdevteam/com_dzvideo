<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_dzvideo
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/');

JHtml::_('bootstrap.tooltip');

JHtml::_('behavior.framework');


/** SHOW CATEGORY INFO */
$registry = new JRegistry();
$registry->loadString($this->category->params);
$category_params = $registry->toArray();

?>

<div class="category">
<?php if ($this->params->def('show_category_title', 1) == 1) : ?>
    <h3> <?php echo $this->category->title; ?></h3>
<?php endif; ?>
<?php if ($this->params->def('show_description', 1) == 1) : ?>
    <div class="category_description"><?php echo $this->category->description; ?></div>
<?php endif; ?>
<?php if ($this->params->def('show_description_image', 1) == 1) : ?>
    <?php if (isset($category_params['image']) && (JFile::exists(JPATH_ROOT.'/'.$category_params['image']))) : 
            $category_image = Juri::root().'/'.$category_params['image'];
    ?>
            <img src="<?php echo $category_image; ?>" />
    <?php endif; ?>
<?php endif; ?>
</div>

<form action="<?php echo htmlspecialchars(JUri::getInstance()->toString()); ?>" method="post" name="adminForm" id="adminForm">
    <fieldset class="filters btn-toolbar">
        <div class="btn-group">
            <label class="filter-search-lbl element-invisible" for="filter-search"><span class="label label-warning"><?php echo JText::_('JUNPUBLISHED'); ?></span><?php echo JText::_('COM_DZVIDEO_FILTER_LABEL').'&#160;'; ?></label>
            <input type="text" name="filter-search" id="filter-search" value="<?php echo $this->escape($this->state->get('list.filter')); ?>" class="inputbox" onchange="document.adminForm.submit();" title="<?php echo JText::_('COM_DZVIDEO_FILTER_SEARCH_DESC'); ?>" placeholder="<?php echo JText::_('COM_DZVIDEO_FILTER_SEARCH_DESC'); ?>" />
        </div>
    </fieldset>
<?php 

/** SHOW VIDEOS LIST */
$n = count($this->items);
$listOrder  = $this->escape($this->params->get('list.ordering'));
$listDirn   = $this->escape($this->params->get('list.direction'));

$k = 0;

$video_column = $this->params->get('display_column');

foreach ($this->items as $id => $item) {
    if ($k % $video_column == 0) {
?>
    <ul class="startrow"> <!-- Start row -->
<?php        
    } 
    
    $images = $item->images;
    $image = JPATH_ROOT.'/images/dzvideo/300x200.gif';
    if (JFile::exists(JPATH_ROOT.'/'.$images['custom'])) {
        $image = JUri::root().'/'.$images['custom'];
    } elseif (JFile::exists(JPATH_ROOT.'/'.$images['medium'])) {
        $image =  JUri::root().'/'.$images['medium'];
    }
?>
    <li>
        <a href="<?php echo $item->videolink;?>"><?php echo $this->escape($item->title); ?></a>
        Vcode: <?php echo $item->videoid; ?>
        <?php if ($item->description) : ?>
            <div class="category-desc">
        <?php echo JHtml::_('content.prepare', $item->description, '', 'com_dzvideo.categories'); ?>
            </div>
        <?php endif; ?>
        <br/>
        Image: <img src="<?php echo $image; ?>" />
        <?php 
            $tags = new JHelperTags;
            $tags->getItemTags('com_dzvideo.video', $item->id);
            $tagLayout = new JLayoutFile('joomla.content.tags');
            $item->tags = $tagLayout->render($tags->itemTags);
        ?>
        
    </li>
<?php 
    if ($k % $video_column == 0) {
?>
    </ul> <!-- End row -->
<?php 
    }
}
?>
    <?php if ($this->params->get('show_pagination')) : ?>
         <div class="pagination">
            <?php if ($this->params->def('show_pagination_results', 1)) : ?>
                <p class="counter">
                    <?php echo $this->pagination->getPagesCounter(); ?>
                </p>
            <?php endif; ?>
                <?php echo $this->pagination->getPagesLinks(); ?>
            </div>
        <?php endif; ?>
</form>

<!-- PRINT CHILDREN -->

<?php if (!empty($this->children)&& $this->maxLevel != 0) : ?>
    <div class="cat-children">
    <?php if ($this->params->get('show_category_heading_title_text', 1) == 1) : ?>
        <h3> <?php echo JTEXT::_('JGLOBAL_SUBCATEGORIES'); ?> </h3>
    <?php endif; ?>
        <?php echo $this->loadTemplate('children'); ?> </div>
    <?php endif; ?>