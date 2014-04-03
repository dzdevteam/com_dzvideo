ALTER TABLE  `#__dzvideo_videos` 
ADD  `created_by_alias` VARCHAR( 255 ) NOT NULL AFTER  `created_by` ,
ADD  `publish_up` DATETIME NOT NULL AFTER  `created_by_alias` ,
ADD  `publish_down` DATETIME NOT NULL AFTER  `publish_up` ;

UPDATE #__categories SET extension =  "com_dzvideo" WHERE extension =  "com_dzvideo.videos.catid";