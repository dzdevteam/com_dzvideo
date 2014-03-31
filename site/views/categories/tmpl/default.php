<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_Dzvideo
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/');

JHtml::_('bootstrap.tooltip');

JHtml::_('behavior.framework');

$category_column = $this->params->get('category_column');

$k = 0;

foreach ($this->items as $id => $item) {
    if ($k % $category_column == 0) {
?>
    <div class="startrow"> <!-- Start row -->
<?php        
    }
    
    $params = $item->params;
    $image = JPATH_ROOT.'/images/dzvideo/300x200.gif';
    if (JFile::exists(JPATH_ROOT.'/'.$params['image'])) 
        $image = JUri::root().'/'.$params['image'];
?>
    <a href="<?php echo $item->link;?>"><?php echo $this->escape($item->title); ?></a>
        <?php if ($item->description) : ?>
            <div class="category-desc">
        <?php echo JHtml::_('content.prepare', $item->description, '', 'com_dzvideo.categories'); ?>
            </div>
        <?php endif; ?>
        <img src="<?php echo $image; ?>" />
<?php 
    if ($k % $category_column == 0) {
?>
    </div> <!-- End row -->
<?php 
    }
}
?>

<?php if (($this->params->def('show_pagination', 1) == 1  || ($this->params->get('show_pagination') == 2)) && ($this->pagination->get('pages.total') > 1)) : ?>
<div class="pagination">
    <?php  if ($this->params->def('show_pagination_results', 1)) : ?>
    <p class="counter pull-right"> <?php echo $this->pagination->getPagesCounter(); ?> </p>
    <?php endif; ?>
    <?php echo $this->pagination->getPagesLinks(); ?> </div>
<?php  endif; ?>


