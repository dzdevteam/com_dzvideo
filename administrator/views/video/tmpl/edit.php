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

JLoader::register('JFilterOutput',JPATH_PLUGINS.'/system/vinaora_vietalias/output.php');

$params = JComponentHelper::getParams('com_dzvideo');
$video_width    = $params->get('video_width');
$video_height   = $params->get('video_height'); 

?>
<script type="text/javascript">
    js = jQuery.noConflict();
   
    //add data to form fields            
    function youtubeDataCallback(data) {  
      var link = js('#jform_link').val();
      var videoid = js('#jform_videoid').val();
      js('#jform_images_mqdefault').val(data.entry.media$group.media$thumbnail[1].url);
      js('#jform_title').val(data.entry.title.$t);
      js('#jform_author').val(data.entry.author[0].name.$t);
      var second = data.entry.media$group.yt$duration.seconds;
      var sec    = second % 60;
      var min   = Math.floor(second/60);
       
      if (min.toString().length == 1) min = '0'+min.toString();else min = min.toString(); 
      if (sec.toString().length == 1) sec = '0'+sec.toString();else sec = sec.toString(); 
      
      var duration = min + ':' + sec;
    
      js('#duration').html(duration);
      js('#jform_length').val(data.entry.media$group.yt$duration.seconds);
      js('#jform_shortdesc').val(data.entry.media$group.media$description.$t.replace(/\n/g, '<br/>'));
      js('#mqthumb').html("<img src="+js('#jform_images_mqdefault').val()+" />");
      js('#jform_getinfo').val(1);
      js('#youtube-play').show();
      
      js("object param").attr("value",link);
      js("object embed").attr("src","http://www.youtube.com/v/"+videoid);
      js('#video-play').show();
    }

    function callyoutube() {
        var videoid = js('#jform_link').val();
        var m;
        if (m = videoid.match(/^http:\/\/www\.youtube\.com\/.*[?&]v=([^&]+)/i) || videoid.match(/^http:\/\/youtu\.be\/([^?]+)/i)) {
          videoid = m[1];
        }
        if (!videoid.match(/^[a-z0-9_-]{11}$/i)) {
            js('#video-content').show();
            js('#video-content').html('<?php echo JText::_("COM_DZVIDEO_VIDEO_NOT_FOUND"); ?>');
          return;
        }
        js('#jform_videoid').val(videoid);
        js.getScript('http://gdata.youtube.com/feeds/api/videos/' + encodeURIComponent(videoid) + '?v=2&alt=json-in-script&callback=youtubeDataCallback');
    }
        
    js(document).ready(function() {
      js("#jform_link").bind('paste', function() {
        // Short pause to wait for paste to complete
        setTimeout( function() {
                callyoutube();
            }, 100);
      });	
        
      //get data from youtube after button click
      js('#video-button').click(function() {
        callyoutube();  
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
        <div class="span9 form-horizontal">
            <?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>
            <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('COM_DZVIDEO_DETAILS', true)); ?>
            <div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('link'); ?></div>
				<div class="controls">
                    <?php echo $this->form->getInput('link'); ?> 
                    <input class="btn btn-primary" type="button" name="button" id="video-button" value="<?php echo JText::_('COM_DZVIDEO_YOUTUBE_INFO', true) ?>" />
                </div>
            </div>
               
            <div id="video-content" class="controls error" style="display: none;"></div>
			
            <div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('title'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('title'); ?></div>
			</div>
            <div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('alias'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('alias'); ?></div>
			</div>
            <div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('catid'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('catid'); ?></div>
			</div>
            <hr />
            <?php if (isset($this->item->videoid) && isset($this->item->link)) { ?>
            <div id="video-play" class="controls" >
                <object width="<?php echo $video_width; ?>" height="<?php echo $video_height; ?>">
                <param value="<?php echo $this->item->link; ?>" name="movie">
                <param value="true" name="allowFullScreen" >
                <embed width="<?php echo $video_width; ?>" height="<?php echo $video_height; ?>" allowfullscreen="true" type="application/x-shockwave-flash" src="http://www.youtube.com/v/<?php echo $this->item->videoid; ?>"></object>
            </div> 
            <?php } else { ?>
            <div id="video-play" class="controls" style="display: none;">
                <object width="<?php echo $video_width; ?>" height="<?php echo $video_height; ?>">
                <param value="" name="movie">
                <param value="true" name="allowFullScreen" >
                <embed width="<?php echo $video_width; ?>" height="<?php echo $video_height; ?>" allowfullscreen="true" type="application/x-shockwave-flash" src=""></object>
            </div>
            <?php } ?>
            <div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('mqdefault', 'images'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('mqdefault', 'images'); ?> </div>
            </div>
            <div class="control-group">
                <div id="mqthumb" class="controls">
                    <?php if (isset($this->item->images['medium'])) { ?>
                        <img src="<?php echo JURI::root().$this->item->images['medium'] ?>" />
                    <?php } ?>
                </div> 
			</div>
            <div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('custom', 'images'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('custom', 'images'); ?></div>
			</div>
            <input type="hidden" name="jform[getinfo]" id="jform_getinfo" value="0" />
            <hr />
            <div class="control-group">
                <div class="control-label"><?php echo $this->form->getLabel('videoid'); ?></div>
                <div class="controls"><?php echo $this->form->getInput('videoid'); ?></div>
            </div>
            <div class="control-group">
                <div class="control-label"><?php echo JTEXT::_('COM_DZVIDEO_FORM_LBL_VIDEO_DIMENSION'); ?></div>
                <div class="controls"><?php echo $this->form->getInput('width'); ?> x <?php echo $this->form->getInput('height'); ?></div>
            </div>
            <div class="control-group">
                <div class="control-label"><?php echo $this->form->getLabel('length'); ?></div>
                <div class="controls"><?php echo $this->form->getInput('length'); ?><div class="controls" id="duration"></div></div>
            </div>
            <div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('author'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('author'); ?></div>
			</div>
            <div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('embed'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('embed'); ?></div>
			</div>
            <hr />
            <div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('shortdesc'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('shortdesc'); ?></div>
			</div>
            <div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('description'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('description'); ?></div>
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
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('hits'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('hits'); ?></div>
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