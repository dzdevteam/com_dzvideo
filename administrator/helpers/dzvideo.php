<?php
/**
 * @version     1.0.0
 * @package     com_dzvideo
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      DZ Team <dev@dezign.vn> - dezign.vn
 */

// No direct access
defined('_JEXEC') or die;
jimport( 'joomla.filesystem.file' );
jimport( 'joomla.filesystem.folder' );

/**
 * Dzvideo helper.
 */
class DzvideoHelper
{
    /**
     * Configure the Linkbar.
     */
    public static function addSubmenu($vName = '')
    {
        JHtmlSidebar::addEntry(
            JText::_('COM_DZVIDEO_TITLE_VIDEOS'),
            'index.php?option=com_dzvideo&view=videos',
            $vName == 'videos'
        );
        JHtmlSidebar::addEntry(
            'Categories',
            "index.php?option=com_categories&extension=com_dzvideo",
            $vName == 'categories'
        );
        
        if ($vName=='categories') {            
            JToolBarHelper::title('DZ Video: Categories');      
        }
    }

    /**
     * Gets a list of the actions that can be performed.
     *
     * @return  JObject
     * @since   1.6
     */
    public static function getActions()
    {
        $user   = JFactory::getUser();
        $result = new JObject;

        $assetName = 'com_dzvideo';

        $actions = array(
            'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.own', 'core.edit.state', 'core.delete'
        );

        foreach ($actions as $action) {
            $result->set($action, $user->authorise($action, $assetName));
        }

        return $result;
    }
    
    public static function generateThumbs($imgfile, $videoid)
    {
        $params = JComponentHelper::getParams('com_dzvideo');
        $basedir = $params->get('image_directory', 'images/dzvideo');
        
        
        if (!JFolder::exists(JPATH_ROOT.'/'.$basedir)) {
            JFolder::copy(JPATH_COMPONENT_ADMINISTRATOR."/dzvideo",JPATH_ROOT.'/'.$basedir);
        }
    
        // Get different pre-configured sizes
        $thumbwidth     = $params->get('small_intro_width', '120');
        $thumbheight    = $params->get('small_intro_height', '80');
        $mediumwidth    = $params->get('medium_intro_width', '300');
        $mediumheight   = $params->get('medium_intro_height', '200');
        
        $desc = JPATH_ROOT.'/'.$basedir.'/'.$videoid.'.jpg';
        
        $ch = curl_init($imgfile);
        $fp = fopen($desc, 'wb');
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);
        
        // Generate thumbs
        $image = new JImage($desc);
        $imagedir = pathinfo($image->getPath(), PATHINFO_DIRNAME);
        $other_thumbs = $image->createThumbs(array($mediumwidth.'x'.$mediumheight), JImage::SCALE_INSIDE, $imagedir);
        $thumb = $image->createThumbs($thumbwidth.'x'.$thumbheight, JImage::SCALE_INSIDE, $imagedir); 
        
        $links = array();
        $basepath = substr($imagedir, strpos($imagedir, $basedir));
        $links['thumb'] = $basepath. '/' . pathinfo($thumb[0]->getPath(), PATHINFO_BASENAME);
        $links['medium'] = $basepath. '/' . pathinfo($other_thumbs[0]->getPath(), PATHINFO_BASENAME);
        
        JFile::delete($desc);
        
        return $links;
    }
}
