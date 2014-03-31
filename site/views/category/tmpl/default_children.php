<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');

 foreach ($this->children as $id => $child) :
  ?>
    <div>
            <h3 class="page-header item-title">         
                <a href="<?php echo Jroute::_(DZVideoHelperRoute::getCategoryRoute($child->id)); ?>">
                <?php echo $this->escape($child->title); ?></a>
    
                <?php if (count($child->getChildren()) > 0) : ?>
                    <a href="#category-<?php echo $child->id;?>" ></a>
                <?php endif;?>
            </h3>
    </div>
<?php endforeach; ?>

