CREATE TABLE IF NOT EXISTS `#__dzvideo_videos` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,

`asset_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',

`ordering` INT(11)  NOT NULL ,
`state` TINYINT(1)  NOT NULL DEFAULT '1',
`checked_out` INT(11)  NOT NULL ,
`checked_out_time` DATETIME NOT NULL,
`created` DATETIME NOT NULL ,
`created_by` INT(11)  NOT NULL ,
`modified` DATETIME NOT NULL ,
`title` VARCHAR(255)  NOT NULL ,
`alias` VARCHAR(255)  NOT NULL ,
`link` VARCHAR(255)  NOT NULL ,
`description` TEXT NOT NULL ,
`thumbnail` VARCHAR(255)  NOT NULL ,
`image` VARCHAR(255)  NOT NULL ,
`author` VARCHAR(255)  NOT NULL ,
`catid` INT(11)  NOT NULL ,
`metakey` TEXT NOT NULL ,
`metadesc` TEXT NOT NULL ,
`metadata` TEXT NOT NULL ,
`params` TEXT NOT NULL ,
`language` VARCHAR(255)  NOT NULL ,
`embed` TEXT NOT NULL ,
`tag` TEXT NOT NULL ,
PRIMARY KEY (`id`)
) DEFAULT COLLATE=utf8_general_ci;

