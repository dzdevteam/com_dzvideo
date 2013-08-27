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
?>
<script type="text/javascript">
    function deleteItem(item_id){
        if(confirm("<?php echo JText::_('COM_DZVIDEO_DELETE_MESSAGE'); ?>")){
            document.getElementById('form-video-delete-' + item_id).submit();
        }
    }
</script>


<div class="items">
    <ul class="items_list">
<?php $show = false; ?>
        <?php foreach ($this->items as $item) : ?>
				<?php
					if($item->state == 1 || ($item->state == 0 && JFactory::getUser()->authorise('core.edit.own',' com_dzvideo.video.'.$item->id))):
						$show = true;
						?>
							<li>
								<a href="<?php echo $item->videolink; ?>"><?php echo $item->title; ?></a>
                                <a href="<?php echo $item->catlink; ?>"><?php echo $item->catid_title; ?></a>
                                
                                <object width="425" height="344"><param value="<?php echo $item->link; ?>" name="movie">
                                <param value="true" name="allowFullScreen">
                                <embed width="425" height="344" allowfullscreen="true" type="application/x-shockwave-flash" src="http://www.youtube.com/v/<?php echo $item->vcode; ?>"></object>
                                <?php var_dump($item); ?>
							</li>
                            <li>
                            
                            </li>
						<?php endif; ?>

<?php endforeach; ?>
        <?php
        if (!$show):
            echo JText::_('COM_DZVIDEO_NO_ITEMS');
        endif;
        ?>
    </ul>
</div>
<div class="pagination">
    <p class="counter">
        <?php echo $this->pagination->getPagesCounter(); ?>
    </p>
    <?php echo $this->pagination->getPagesLinks(); ?>
</div>
<?php if(JFactory::getUser()->authorise('core.create','com_dzvideo')): ?><a href="<?php echo JRoute::_('index.php?option=com_dzvideo&task=video.edit&id=0'); ?>"><?php echo JText::_("COM_DZVIDEO_ADD_ITEM"); ?></a>
	<?php endif; ?>

