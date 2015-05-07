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

    function callyoutube() {
        var videoid = js('#jform_link').val();
        var m;
        if (m = videoid.match(/^https?:\/\/www\.youtube\.com\/.*[?&]v=([^&]+)/i) || videoid.match(/^https?:\/\/youtu\.be\/([^?]+)/i)) {
          videoid = m[1];
        }
        if (!videoid.match(/^[a-z0-9_-]{11}$/i)) {
          js('#video-error').html('<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button><?php echo JText::_("COM_DZVIDEO_VIDEO_NOT_FOUND"); ?></div>');
          return;
        } else {
          js('#video-error').html("");
        }
        js('#jform_videoid').val(videoid);
        js.get('https://www.googleapis.com/youtube/v3/videos', {
            part: 'id,player,snippet,contentDetails',
            id: videoid,
            key: 'AIzaSyAdiOHztingwOktlXHONKiZYdXo5aeRSdQ'
        }, function(data) {
            if (data.items.length == 1) {
                var link = js('#jform_link').val();
                var videoid = js('#jform_videoid').val();
                var item = data.items[0];
                js('#jform_images_mqdefault').val(item.snippet.thumbnails.medium.url);
                js('#jform_title').val(item.snippet.title);
                js('#jform_author').val(item.snippet.channelTitle);
                
                // Duration
                var iso_length = item.contentDetails.duration;
                var parts = iso_length.match(/[0-9]+.?/g);
                var length = 0;
                for (var i = 0; i < parts.length; i++) {
                    switch (parts[i].slice(-1)) {
                        case 'S':
                            length += parseInt(parts[i]);
                            break;
                        case 'M':
                            length += parseInt(parts[i]) * 60;
                            break;
                        case 'H':
                            length += parseInt(parts[i]) * 60 * 60;
                            break;
                        case 'D':
                            length += parseInt(parts[i]) * 60 * 60 * 24;
                            break;
                        default:
                            break;
                    }
                }
                js('#duration').html(iso_length); // TODO
                js('#jform_length').val(length);
                js('#jform_shortdesc').val(item.snippet.description);
                js('#mqthumb').html("<img src="+js('#jform_images_mqdefault').val()+" />");
                js('#jform_getinfo').val(1);
                js('#youtube-play').show();
                js('#jform_embed').val(item.player.embedHtml)
                
                js("#video-play iframe").attr("src","https://www.youtube.com/embed/"+videoid);
                js('#video-play').show();
            } else {
                js('#video-error').html('<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button><?php echo JText::_("COM_DZVIDEO_VIDEO_NOT_FOUND"); ?></div>');
            }
        })
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
    <?php echo JLayoutHelper::render('joomla.edit.title_alias', $this); ?>
    <div class="form-horizontal">
        <?php echo JHtml::_('bootstrap.startTabSet', 'videoTab', array('active' => 'general')); ?>
        
        <?php echo JHtml::_('bootstrap.addTab', 'videoTab', 'general', JText::_('COM_DZVIDEO_DETAILS', true)); ?>
        <div class="row-fluid">
            <div class="span9">
                 <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('link'); ?></div>
                    <div class="controls">
                        <?php echo $this->form->getInput('link'); ?>
                        <input class="btn btn-primary" type="button" name="button" id="video-button" value="<?php echo JText::_('COM_DZVIDEO_YOUTUBE_INFO', true) ?>" />
                    </div>
                </div>
                
                <div id="video-error"></div>
                
                <?php if (isset($this->item->videoid) && isset($this->item->link)) : ?>
                <div id="video-play" class="controls" >
                    <iframe
                        type="text/html"
                        src="https://www.youtube.com/embed/<?= $this->item->videoid; ?>"
                        frameborder="0" width="<?= $video_width; ?>" height="<?= $video_height; ?>">
                    </iframe>
                </div>
                <?php else : ?>
                <div id="video-play" class="controls" style="display: none;">
                    <iframe
                        type="text/html"
                        frameborder="0" width="<?= $video_width; ?>" height="<?= $video_height; ?>">
                    </iframe>
                </div>
                <?php endif; ?>
                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('description'); ?></div>
                    <div class="controls"><?php echo $this->form->getInput('description'); ?></div>
                </div>
                <input type="hidden" name="jform[getinfo]" id="jform_getinfo" value="0" />
                <hr />
                <div class="row-fluid">
                    <div class="span6">
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
                            <div class="controls"><?php echo $this->form->getInput('length'); ?></div>
                            <div class="controls" id="duration"></div>
                        </div>
                        <div class="control-group">
                            <div class="control-label"><?php echo $this->form->getLabel('author'); ?></div>
                            <div class="controls"><?php echo $this->form->getInput('author'); ?></div>
                        </div>
                        <div class="control-group">
                            <div class="control-label"><?php echo $this->form->getLabel('embed'); ?></div>
                            <div class="controls"><?php echo $this->form->getInput('embed'); ?></div>
                        </div>
                    </div>
                    <div class="span6">
                        <div class="control-group">
                            <div class="control-label"><?php echo $this->form->getLabel('custom', 'images'); ?></div>
                            <div class="controls"><?php echo $this->form->getInput('custom', 'images'); ?></div>
                        </div>
                        <div class="control-group">
                            <div class="control-label"><?php echo $this->form->getLabel('mqdefault', 'images'); ?></div>
                            <div class="controls"><?php echo $this->form->getInput('mqdefault', 'images'); ?> </div>
                        </div>
                        <div class="control-group">
                            <div id="mqthumb" class="controls">
                                <?php if (isset($this->item->images['medium'])) { ?>
                                    <img src="<?php echo JURI::root().$this->item->images['medium'] ?>" />
                                <?php } ?>
                                <?php echo $this->form->getInput('medium', 'images'); ?>
                                <?php echo $this->form->getInput('thumb', 'images'); ?>
                            </div>
                        </div>
                        <div class="control-group">
                            <div class="control-label"><?php echo $this->form->getLabel('shortdesc'); ?></div>
                            <div class="controls"><?php echo $this->form->getInput('shortdesc'); ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="span3">
                <!-- Begin Sidebar -->
                <?php echo JLayoutHelper::render('joomla.edit.global', $this); ?>
                <!-- End Sidebar -->
            </div>
        </div>
        <?php echo JHtml::_('bootstrap.endTab'); ?>
        
        <?php echo JHtml::_('bootstrap.addTab', 'videoTab', 'publishing', JText::_('COM_DZVIDEO_PUBLISHING', true)); ?>
            <div class="row-fluid">
                <div class="span6">
                     <?php echo JLayoutHelper::render('joomla.edit.publishingdata', $this); ?>
                </div>
                <div class="span6">
                    <?php echo JLayoutHelper::render('joomla.edit.metadata', $this); ?>
                </div>
            </div>
           
            
            <?php echo JHtml::_('bootstrap.endTab'); ?>
            
            <?php if (JFactory::getUser()->authorise('core.admin','dzvideo')): ?>
                <?php echo JHtml::_('bootstrap.addTab', 'videoTab', 'permissions', JText::_('COM_DZVIDEO_RULES', true)); ?>
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
        <input type="hidden" name="task" value="" />
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>
