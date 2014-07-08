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

//Load admin language file
$lang = JFactory::getLanguage();
$lang->load('com_dzvideo', JPATH_ADMINISTRATOR);
$canEdit = JFactory::getUser()->authorise('core.edit', 'com_dzvideo.' . $this->item->id);
if (!$canEdit && JFactory::getUser()->authorise('core.edit.own', 'com_dzvideo' . $this->item->id)) {
    $canEdit = JFactory::getUser()->id == $this->item->created_by;
}

?>

<?php if ($this->item) :
    $video_height = $this->params->get('video_height');
    $video_width  = $this->params->get('video_width');
?>
    <div class="item_fields">
        <ul class="fields_list">
            <li><?php echo JText::_('COM_DZVIDEO_FORM_LBL_VIDEO_ID'); ?>:
            <?php echo $this->item->id; ?></li>
            <li><?php echo JText::_('COM_DZVIDEO_FORM_LBL_VIDEO_ORDERING'); ?>:
            <?php echo $this->item->ordering; ?></li>
            <li><?php echo JText::_('COM_DZVIDEO_FORM_LBL_VIDEO_STATE'); ?>:
            <?php echo $this->item->state; ?></li>
            <li><?php echo JText::_('COM_DZVIDEO_FORM_LBL_VIDEO_CHECKED_OUT'); ?>:
            <?php echo $this->item->checked_out; ?></li>
            <li><?php echo JText::_('COM_DZVIDEO_FORM_LBL_VIDEO_CHECKED_OUT_TIME'); ?>:
            <?php echo $this->item->checked_out_time; ?></li>
            <li><?php echo JText::_('COM_DZVIDEO_FORM_LBL_VIDEO_CREATED_BY'); ?>:
            <?php echo $this->item->created_by; ?></li>
            <li><?php echo JText::_('COM_DZVIDEO_FORM_LBL_VIDEO_TITLE'); ?>:
            <?php echo $this->item->title; ?></li>
            <li><?php echo JText::_('COM_DZVIDEO_FORM_LBL_VIDEO_ALIAS'); ?>:
            <?php echo $this->item->alias; ?></li>
            <li><?php echo JText::_('COM_DZVIDEO_FORM_LBL_VIDEO_LINK'); ?>:
            <?php echo $this->item->link; ?></li>
            <li><?php echo JText::_('COM_DZVIDEO_FORM_LBL_VIDEO_VIDEOID'); ?>:
            <?php echo $this->item->videoid; ?></li>
            <li><?php echo JText::_('COM_DZVIDEO_FORM_LBL_VIDEO_SHORTDESC'); ?>:
            <?php echo $this->item->shortdesc; ?></li>
            <li><?php echo JText::_('COM_DZVIDEO_FORM_LBL_VIDEO_DESCRIPTION'); ?>:
            <?php echo $this->item->description; ?></li>
            <li><?php echo JText::_('COM_DZVIDEO_FORM_LBL_VIDEO_AUTHOR'); ?>:
            <?php echo $this->item->author; ?></li>
            <li><?php echo JText::_('COM_DZVIDEO_FORM_LBL_VIDEO_CATID'); ?>:
            <?php echo $this->item->catid_title; ?></li>
            <li><?php echo JText::_('COM_DZVIDEO_FORM_LBL_VIDEO_METAKEY'); ?>:
            <?php echo $this->item->metakey; ?></li>
            <li><?php echo JText::_('COM_DZVIDEO_FORM_LBL_VIDEO_METADESC'); ?>:
            <?php echo $this->item->metadesc; ?></li>
            <li><?php echo JText::_('COM_DZVIDEO_FORM_LBL_VIDEO_METADATA'); ?>:
            <?php echo $this->item->metadata; ?></li>
            <li><?php echo JText::_('COM_DZVIDEO_FORM_LBL_VIDEO_PARAMS'); ?>:
            <?php var_dump($this->item->params); ?></li>
            <li><?php echo JText::_('COM_DZVIDEO_FORM_LBL_VIDEO_LANGUAGE'); ?>:
            <?php echo $this->item->language; ?></li>
            <li><?php echo JText::_('COM_DZVIDEO_FORM_LBL_VIDEO_EMBED'); ?>:
            <?php echo $this->item->embed; ?></li>
            <li>Uploader: <?php echo $this->item->created_by->name; ?></li>
            <li><?php echo JText::_('COM_DZVIDEO_FORM_LBL_VIDEO_TAG'); ?>:
                 <?php $this->item->tagLayout = new JLayoutFile('joomla.content.tags'); ?>
                <?php echo $this->item->tagLayout->render($this->item->tags->itemTags); ?>
            </li>
            <li>Next video: <?= !empty($this->item->next_video) ? "<a href=\"{$this->item->next_video->videolink}\">{$this->item->next_video->title}</a>" : NULL; ?></li>
            <li>Previous video: <?= !empty($this->item->previous_video) ? "<a href=\"{$this->item->previous_video->videolink}\">{$this->item->previous_video->title}</a>" : NULL; ?></li>
            <object width="605" height="400">
                <param value="<?php echo $this->item->videolink; ?>" name="movie">
                <param value="true" name="allowFullScreen">
                <embed width="<?php echo $video_width; ?>" height="<?php echo $video_height; ?>" allowfullscreen="true" type="application/x-shockwave-flash"
                src="http://www.youtube.com/v/<?php echo $this->item->videoid; if ($this->params->get('video_autoplay') == 1) echo '?autoplay=1'; ?>">
            </object>
        </ul>
    </div>
<?php
else:
    echo JText::_('COM_DZVIDEO_ITEM_NOT_LOADED');
endif;
?>
