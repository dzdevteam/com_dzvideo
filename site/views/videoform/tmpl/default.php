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

JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');

JHtml::_('jquery.framework');
JFactory::getDocument()->addScript(JUri::root() . 'components/com_dzvideo/assets/js/videoform.js');

//Load admin language file
$lang = JFactory::getLanguage();
$lang->load('com_dzvideo', JPATH_ADMINISTRATOR);
?>

<div class="video-edit front-end-edit">
    <?php if (!empty($this->item->id)): ?>
        <h1>Edit <?php echo $this->item->id; ?></h1>
    <?php else: ?>
        <h1>Add</h1>
    <?php endif; ?>

    <form id="form-video" action="<?php echo JRoute::_('index.php?option=com_dzvideo&task=video.save'); ?>" method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
        <div class="control-group">
            <div class="control-label"><?php echo $this->form->getLabel('link'); ?></div>
            <div class="controls"><?php echo $this->form->getInput('link'); ?></div>
        </div>
        <div class="control-group">
            <div class="controls"><div id="alert"></div><br /><div id="embed"></div></div>
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
            <div class="control-label"><?php echo $this->form->getLabel('shortdesc'); ?></div>
            <div class="controls"><?php echo $this->form->getInput('shortdesc'); ?></div>
        </div>
        <div class="control-group">
            <div class="control-label"><?php echo $this->form->getLabel('description'); ?></div>
            <div class="controls"><?php echo $this->form->getInput('description'); ?></div>
        </div>
        <div class="control-group">
            <div class="control-label"><?php echo $this->form->getLabel('author'); ?></div>
            <div class="controls"><?php echo $this->form->getInput('author'); ?></div>
        </div>
        <div class="control-group">
        <?php $canState = false; ?>
        <?php if ($this->item->id): ?>
            <?php $canState = JFactory::getUser()->authorise('core.edit.state','com_dzvideo.video'.$this->item->id); ?>
        <?php else: ?>
            <?php $canState = JFactory::getUser()->authorise('core.edit.state','com_dzvideo'); ?>
        <?php endif; ?>             
        <?php if(!$canState): ?>
            <?php
                $state_value = 0;
                if($this->item->state == 1):
                    $state_value = 1;
                endif;
            ?>
            <input type="hidden" name="jform[state]" value="<?php echo $state_value; ?>" />
        <?php else: ?>
            <div class="control-label"><?php echo $this->form->getLabel('state'); ?></div>
            <div class="controls"><?php echo $this->form->getInput('state'); ?></div>                   
        <?php endif; ?>
        </div>
        <div class="control-group">
            <div class="control-label"><?php echo $this->form->getLabel('created_by'); ?></div>
            <div class="controls"><?php echo $this->form->getInput('created_by'); ?></div>
        </div>
        <div>
            <?php echo $this->form->getInput('videoid'); ?>
            <?php echo $this->form->getInput('mqdefault', 'images'); ?>
            <?php echo $this->form->getInput('width'); ?>
            <?php echo $this->form->getInput('height'); ?>
            <?php echo $this->form->getInput('length'); ?>
            <?php echo $this->form->getInput('language'); ?>
        </div>

        <div class="control-group">
            <button type="submit" class="validate"><span><?php echo JText::_('JSUBMIT'); ?></span></button>
            <?php echo JText::_('or'); ?>
            <a href="<?php echo JRoute::_('index.php?option=com_dzvideo&task=video.cancel'); ?>" title="<?php echo JText::_('JCANCEL'); ?>"><?php echo JText::_('JCANCEL'); ?></a>

            <input type="hidden" name="option" value="com_dzvideo" />
            <input type="hidden" name="task" value="videoform.save" />
            <?php echo JHtml::_('form.token'); ?>
        </div>
    </form>
</div>
