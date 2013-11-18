<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_dzvideo
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * DZvideo Component Category Tree
 *
 * @static
 * @package     Joomla.Site
 * @subpackage  com_dzvideo
 * @since       3.0
 */
class DZVideoCategories extends JCategories
{
	public function __construct($options = array())
	{
		$options['table'] = '#__dzvideo_videos';
		$options['extension'] = 'com_dzvideo.videos.catid';
		parent::__construct($options);
	}
}
