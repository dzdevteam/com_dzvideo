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
<?php if ($this->item) : ?>

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
			<li><?php echo JText::_('COM_DZVIDEO_FORM_LBL_VIDEO_DESCRIPTION'); ?>:
			<?php echo $this->item->description; ?></li>
			<li><?php echo JText::_('COM_DZVIDEO_FORM_LBL_VIDEO_THUMBNAIL'); ?>:
			<?php echo $this->item->thumbnail; ?></li>
			<li><?php echo JText::_('COM_DZVIDEO_FORM_LBL_VIDEO_IMAGE'); ?>:
			<?php echo $this->item->image; ?></li>
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
			<?php echo $this->item->params; ?></li>
			<li><?php echo JText::_('COM_DZVIDEO_FORM_LBL_VIDEO_LANGUAGE'); ?>:
			<?php echo $this->item->language; ?></li>
			<li><?php echo JText::_('COM_DZVIDEO_FORM_LBL_VIDEO_EMBED'); ?>:
			<?php echo $this->item->embed; ?></li>
			<li><?php echo JText::_('COM_DZVIDEO_FORM_LBL_VIDEO_TAG'); ?>:
			<?php echo $this->item->tag; ?></li>


        </ul>

    </div>
    <?php if($canEdit): ?>
		<a href="<?php echo JRoute::_('index.php?option=com_dzvideo&task=video.edit&id='.$this->item->id); ?>"><?php echo JText::_("COM_DZVIDEO_EDIT_ITEM"); ?></a>
	<?php endif; ?>
								<?php if(JFactory::getUser()->authorise('core.delete','com_dzvideo.video.'.$this->item->id)):
								?>
									<a href="javascript:document.getElementById('form-video-delete-<?php echo $this->item->id ?>').submit()"><?php echo JText::_("COM_DZVIDEO_DELETE_ITEM"); ?></a>
									<form id="form-video-delete-<?php echo $this->item->id; ?>" style="display:inline" action="<?php echo JRoute::_('index.php?option=com_dzvideo&task=video.remove'); ?>" method="post" class="form-validate" enctype="multipart/form-data">
										<input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>" />
										<input type="hidden" name="jform[ordering]" value="<?php echo $this->item->ordering; ?>" />
										<input type="hidden" name="jform[state]" value="<?php echo $this->item->state; ?>" />
										<input type="hidden" name="jform[checked_out]" value="<?php echo $this->item->checked_out; ?>" />
										<input type="hidden" name="jform[checked_out_time]" value="<?php echo $this->item->checked_out_time; ?>" />
										<input type="hidden" name="jform[created_by]" value="<?php echo $this->item->created_by; ?>" />
										<input type="hidden" name="jform[title]" value="<?php echo $this->item->title; ?>" />
										<input type="hidden" name="jform[alias]" value="<?php echo $this->item->alias; ?>" />
										<input type="hidden" name="jform[link]" value="<?php echo $this->item->link; ?>" />
										<input type="hidden" name="jform[description]" value="<?php echo $this->item->description; ?>" />
										<input type="hidden" name="jform[thumbnail]" value="<?php echo $this->item->thumbnail; ?>" />
										<input type="hidden" name="jform[image]" value="<?php echo $this->item->image; ?>" />
										<input type="hidden" name="jform[author]" value="<?php echo $this->item->author; ?>" />
										<input type="hidden" name="jform[catid]" value="<?php echo $this->item->catid; ?>" />
										<input type="hidden" name="jform[metakey]" value="<?php echo $this->item->metakey; ?>" />
										<input type="hidden" name="jform[metadesc]" value="<?php echo $this->item->metadesc; ?>" />
										<input type="hidden" name="jform[metadata]" value="<?php echo $this->item->metadata; ?>" />
										<input type="hidden" name="jform[params]" value="<?php echo $this->item->params; ?>" />
										<input type="hidden" name="jform[language]" value="<?php echo $this->item->language; ?>" />
										<input type="hidden" name="jform[embed]" value="<?php echo $this->item->embed; ?>" />
										<input type="hidden" name="jform[tag]" value="<?php echo $this->item->tag; ?>" />
										<input type="hidden" name="option" value="com_dzvideo" />
										<input type="hidden" name="task" value="video.remove" />
										<?php echo JHtml::_('form.token'); ?>
									</form>
								<?php
								endif;
							?>
<?php
else:
    echo JText::_('COM_DZVIDEO_ITEM_NOT_LOADED');
endif;
?>
