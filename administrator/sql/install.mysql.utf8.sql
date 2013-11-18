CREATE TABLE IF NOT EXISTS `#__dzvideo_videos` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`asset_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
`ordering` INT(11)  NOT NULL ,
`state` TINYINT(1)  NOT NULL DEFAULT '1',
`featured` TINYINT(1)  NOT NULL DEFAULT '0',
`checked_out` INT(11)  NOT NULL DEFAULT '0',
`checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
`created` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
`created_by` INT(11)  NOT NULL DEFAULT '0',
`modified` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
`title` VARCHAR(255)  NOT NULL DEFAULT '',
`alias` VARCHAR(255)  NOT NULL DEFAULT '',
`link` VARCHAR(255)  NOT NULL DEFAULT '',
`videoid` VARCHAR(255)  NOT NULL DEFAULT '',
`shortdesc` TEXT NOT NULL DEFAULT '',
`description` TEXT NOT NULL DEFAULT '',
`images` TEXT  NOT NULL DEFAULT '',
`author` VARCHAR(255)  NOT NULL DEFAULT '',
`catid` INT(11)  NOT NULL DEFAULT '0',
`length` INT(11)  NOT NULL DEFAULT '0',
`width` INT(11)  NOT NULL DEFAULT '0',
`height` INT(11)  NOT NULL DEFAULT '0',
`embed` TEXT NOT NULL DEFAULT '',
`metakey` TEXT NOT NULL ,
`metadesc` TEXT NOT NULL ,
`metadata` TEXT NOT NULL ,
`params` TEXT NOT NULL ,
`language` VARCHAR(255) NOT NULL DEFAULT '',
`hits` int(11) NOT NULL DEFAULT '0',
PRIMARY KEY (`id`),
KEY `idx_catid` (`catid`),
KEY `idx_state` (`state`),
KEY `idx_checkout` (`checked_out`),
KEY `idx_featured_catid` (`featured`),
KEY `idz_language` (`language`)
) DEFAULT COLLATE=utf8_general_ci;

INSERT INTO `#__content_types` (`type_id`, `type_title`, `type_alias`, `table`, `rules`, `field_mappings`, `router`) VALUES (NULL, 'DZ Video - Video', 'com_dzvideo.video', '{"special":{"dbtable":"#__dzvideo_videos","key":"id","type":"Video","prefix":"DZVideoTable","config":"array()"}}', '', '{"common":[{"core_content_item_id":"id","core_title":"title","core_state":"state","core_alias":"alias","core_body":"description", "core_params":"params", "core_metadata":"metadata", "core_ordering":"ordering", "core_metakey":"metakey", "core_metadesc":"metadesc", "asset_id":"asset_id"}]}', 'DZVideoHelperRoute::getVideoRoute');