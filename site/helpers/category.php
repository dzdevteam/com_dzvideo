<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_weblinks
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Weblinks Component Category Tree
 *
 * @static
 * @package     Joomla.Site
 * @subpackage  com_weblinks
 * @since       1.6
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
