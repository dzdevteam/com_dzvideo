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

jimport('joomla.application.component.view');

/**
 * View to edit
 */
class DzvideoViewVideo extends JViewLegacy
{
    protected $state;
    protected $item;
    protected $form;

    /**
     * Display the view
     */
    public function display($tpl = null)
    {
        $this->state    = $this->get('State');
        $this->item     = $this->get('Item');
        $this->form     = $this->get('Form');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new Exception(implode("\n", $errors));
        }
        
        define('YT_API_URL', 'http://gdata.youtube.com/feeds/api/videos?q=');
        
        $url = JRequest::getVar('link'); 
        
        //$url = "http://www.youtube.com/watch?v=ADmCFmYLns4&list=PLDE424A56510098EE";
        if (preg_match("/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?&\"'>]+)/", $url, $matches)) {
        
            //Change below the video id.
            $video_id = $matches[1];
             
            //Using cURL php extension to make the request to youtube API
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, YT_API_URL . $video_id);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            //$feed holds a rss feed xml returned by youtube API
            $feed = curl_exec($ch);
            curl_close($ch);
             
                //Using SimpleXML to parse youtube's feed
            $xml = simplexml_load_string($feed);    
            $entry = $xml->entry[0];
            
            //If no entry whas found, then youtube didn't find any video with specified id
            if(!$entry) { 
                echo json_encode(array('error' => 1));
                JFactory::getApplication()->close();
            }
                        
            $media = $entry->children('media', true);
            $author= $entry->author->name;
            $group = $media->group;
            
            $title = $group->title;//$title: The video title
            $desc = $group->description;//$desc: The video description
            $thumb = $group->thumbnail[0];//There are 4 thumbnails, the first one (index 0) is the largest.
        
            //$thumb_url: the url of the thumbnail. $thumb_width: thumbnail width in pixels.
            //$thumb_height: thumbnail height in pixels. $thumb_time: thumbnail time in the video
            list($thumb_url, $thumb_width, $thumb_height, $thumb_time) = $thumb->attributes();
            $content_attributes = $group->content->attributes();
            //$vid_duration: the duration of the video in seconds. Ex.: 192.
            $vid_duration = $content_attributes['duration'];
            //$duration_formatted: the duration of the video formatted in "mm:ss". Ex.:01:54
            
            $duration_formatted = utf8_str_pad(floor($vid_duration/60), 2, "0", STR_PAD_LEFT) . ":" . utf8_str_pad($vid_duration%60, 2, "0", STR_PAD_LEFT);
          
            $result = json_decode(json_encode(array('title' => $title,'description' => $desc, 'author' => $author,'thumb_url' => $thumb_url,'vid_duration' => $duration_formatted)),true);
            foreach ($result as $key => $arr) {
                $json_encode["$key"] = $arr[0];
            }
            echo json_encode($json_encode);
            //print_r($array); 
            JFactory::getApplication()->close();
        } else {
            echo json_encode(array('error' => 1));
            JFactory::getApplication()->close();
        }
        
    }
}
