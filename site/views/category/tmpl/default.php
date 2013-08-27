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

<?php if ($this->params->get('show_category_title')) : ?>
	<span class="subheading-category"><?php echo $this->category->title;?></span>
<?php endif; ?>

<div class="<?php echo $this->params->get('pageclass_sfx');?>">
    <ul class="items_list">
        <?php foreach ($this->items as $item) : ?>
				<?php
					if($item->state == 1 ):
                        $width = $this->params->get('video_width');
                        $height = $this->params->get('video_height');
						?>
							<li>
								<a href="<?php echo $item->videolink; ?>"><?php echo $item->title; ?></a>
                                <a href="<?php echo $item->catlink; ?>"><?php echo $item->catid_title; ?></a>
                                <object width="<?php echo $width; ?>" height="<?php echo $height; ?>"><param value="<?php echo $item->link; ?>" name="movie">
                                <param value="true" name="allowFullScreen">
                                <embed width="<?php echo $width; ?>" height="<?php echo $height; ?>" allowfullscreen="true" type="application/x-shockwave-flash" src="http://www.youtube.com/v/<?php echo $item->vcode; ?>"></object>
							</li>
						<?php endif; ?>
        <?php endforeach; ?>
    </ul>
</div>

<div class="pagination">
    <p class="counter">
        <?php echo $this->pagination->getPagesCounter(); ?>
    </p>
    <?php echo $this->pagination->getPagesLinks(); ?>
</div>