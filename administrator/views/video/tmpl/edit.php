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

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.keepalive');

// Import CSS
$document = JFactory::getDocument();
$document->addStyleSheet('components/com_dzvideo/assets/css/dzvideo.css');
?>
<script type="text/javascript">
    js = jQuery.noConflict();
    js(document).ready(function(){
        js('#video-button').click(function() { 
         var url = "index.php?option=com_dzvideo&view=video&format=raw";
         js.ajax({
            url: url,
            type: "POST",
            data: 'link='+js('#jform_link').val(),
            dataType: 'html',
            success: function(response) {
                data = js.parseJSON(response);
                if (parseInt(data.error) == 1) {
                    js('#video-content').html('<?php echo JText::_("COM_DZVIDEO_VIDEO_NOT_FOUND"); ?>');    
                } else {
                    js('#jform_title').val(data.title);       
                    js('#jform_description').val(data.description); 
                    js('#jform_thumbnail').val(data.thumb_url); 
                    js('#jform_author').val(data.author);  
                    js('#jform_alias').val(''); 
                    
                    var RegExp = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;
                    if(RegExp.test(data.thumb_url)){
                        js('#thumb').html("<img src="+data.thumb_url+" height='90' width ='120'/>");   
                    }
                }
            }
        });
      });    
    });
    
    Joomla.submitbutton = function(task)
    {
        if(task == 'video.cancel'){
            Joomla.submitform(task, document.getElementById('video-form'));
        }
        else{
            
            if (task != 'video.cancel' && document.formvalidator.isValid(document.id('video-form'))) {
                
                Joomla.submitform(task, document.getElementById('video-form'));
            }
            else {
                alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
            }
        }
    }
</script>

<form action="<?php echo JRoute::_('index.php?option=com_dzvideo&layout=edit&id=' . (int) $this->item->id); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="video-form" class="form-validate">
    <div class="row-fluid">
        <div class="span10 form-horizontal">
            <?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>

            <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('COM_DZVIDEO_DETAILS', true)); ?>

            <div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('link'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('link'); ?></div>
                <div id="video-button" class="control-label"><input type="button" name="button" id="button" value="<?php echo JText::_('COM_DZVIDEO_YOUTUBE_INFO', true) ?>" /></div>
                <div id="video-content" class="controls"></div>
			</div>
            <div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('catid'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('catid'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('title'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('title'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('alias'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('alias'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('description'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('description'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('thumbnail'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('thumbnail'); ?></div>
			</div>
            <div class="control-group">
				<div id="thumb" class="controls"></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('image'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('image'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('author'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('author'); ?></div>
			</div>
            <div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('embed'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('embed'); ?></div>
			</div>

            <?php echo JHtml::_('bootstrap.endTab'); ?>
            <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'publishing', JText::_('COM_DZVIDEO_PUBLISHING', true)); ?>
            <div class="control-group">
                <div class="control-label"><?php echo $this->form->getLabel('id'); ?></div>
                <div class="controls"><?php echo $this->form->getInput('id'); ?></div>
            </div>
            <div class="control-group">
                <div class="control-label"><?php echo $this->form->getLabel('created'); ?></div>
                <div class="controls"><?php echo $this->form->getInput('created'); ?></div>
            </div>
            <div class="control-group">
                <div class="control-label"><?php echo $this->form->getLabel('created_by'); ?></div>
                <div class="controls"><?php echo $this->form->getInput('created_by'); ?></div>
            </div>
            <?php echo JHtml::_('bootstrap.endTab'); ?>
            <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'metadata', JText::_('JGLOBAL_FIELDSET_METADATA_OPTIONS', true)); ?>
                <?php echo JLayoutHelper::render('joomla.edit.metadata', $this); ?>
            <?php echo JHtml::_('bootstrap.endTab'); ?>
        <?php if (JFactory::getUser()->authorise('core.admin','dzvideo')): ?>
            <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'permissions', JText::_('COM_DZVIDEO_RULES', true)); ?>    
        	<div class="fltlft" style="width:86%;">
  				<fieldset class="panelform">
                    <?php echo JHtml::_('sliders.start', 'permissions-sliders-'.$this->item->id, array('useCookie'=>1)); ?>
                    <?php echo JHtml::_('sliders.panel', JText::_('ACL Configuration'), 'access-rules'); ?>
                    <?php echo $this->form->getInput('rules'); ?>
                    <?php echo JHtml::_('sliders.end'); ?>
                </fieldset>
        	</div>
        	<?php echo JHtml::_('bootstrap.endTab'); ?>
        <?php endif; ?>
        <?php echo JHtml::_('bootstrap.endTabSet'); ?>
      </div>
        <div class="clr"></div>
        <!-- Begin Sidebar -->
        <?php echo JLayoutHelper::render('joomla.edit.details', $this); ?>
        <!-- End Sidebar -->

        <input type="hidden" name="task" value="" />
        <?php echo JHtml::_('form.token'); ?>

    </div>
</form>