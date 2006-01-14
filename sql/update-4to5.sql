INSERT INTO `global` ( `id` , `name` , `value` ) 
VALUES ('', 'wb_max_gp', '60000'), ('', 'gp_pts_ratio', '1000'), ('', 'wb_paybacklimit', '80000'), ('', 'wb_payback_perc', '5');
INSERT INTO `global` ( `id` , `name` , `value` ) 
VALUES ('', 'prod_slots_runes', '100'),('', 'prod_slots_stone', '10'),
('', 'prod_slots_metal', '10'),('', 'prod_slots_lumber', '10'),('', 'prod_slots_food', '10');

CREATE TABLE `terrainpatchtype` (
`id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
`gfx` VARCHAR( 255 ) NOT NULL ,
`here` INT UNSIGNED NOT NULL ,
`up` INT UNSIGNED NOT NULL ,
`down` INT UNSIGNED NOT NULL ,
`left` INT UNSIGNED NOT NULL ,
`right` INT UNSIGNED NOT NULL ,
PRIMARY KEY ( `id` )
) TYPE = MYISAM ;
ALTER TABLE `terrainpatchtype` ADD INDEX ( `here` , `up` , `down` , `left` , `right` ) ;


ALTER TABLE `buildingtype` ADD `border` TINYINT UNSIGNED DEFAULT '1' NOT NULL ;
UPDATE `buildingtype` SET `border` = '0' WHERE `id` =3 LIMIT 1 ;
UPDATE `buildingtype` SET `border` = '0' WHERE `id` =5 LIMIT 1 ;
UPDATE `buildingtype` SET `border` = '0' WHERE `id` =17 LIMIT 1 ;
UPDATE `buildingtype` SET `border` = '0' WHERE `id` =18 LIMIT 1 ;
UPDATE `buildingtype` SET `border` = '0' WHERE `id` =24 LIMIT 1 ;
UPDATE `buildingtype` SET `border` = '0' WHERE `id` =48 LIMIT 1 ;
UPDATE `buildingtype` SET `border` = '0' WHERE `id` =49 LIMIT 1 ;
UPDATE `buildingtype` SET `border` = '0' WHERE `id` =50 LIMIT 1 ;

ALTER TABLE `terrain` CHANGE `nwse` `nwse` TINYINT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `building` CHANGE `nwse` `nwse` TINYINT UNSIGNED NOT NULL DEFAULT '0';

ALTER TABLE `user` ADD `worker_repair` FLOAT UNSIGNED DEFAULT '0' NOT NULL AFTER `worker_runes` ;
ALTER TABLE `building` ADD INDEX ( `hp` ) ;
ALTER TABLE `buildingtype` ADD INDEX ( `maxhp` ) ;


ALTER TABLE `buildingtype` ADD `movable_flag` INT UNSIGNED DEFAULT '0' NOT NULL ;
ALTER TABLE `buildingtype` ADD INDEX ( `movable_flag` ) ;
ALTER TABLE `buildingtype` ADD `movable_override_terrain` TINYINT UNSIGNED NOT NULL DEFAULT '1';


ALTER TABLE `newlog` ADD `count` INT UNSIGNED DEFAULT '1' NOT NULL ;
ALTER TABLE `newlog` ADD INDEX ( `count` ) ;

ALTER TABLE `guildlog` ADD `count` INT UNSIGNED DEFAULT '1' NOT NULL ;
ALTER TABLE `guildlog` ADD INDEX ( `count` ) ;

ALTER TABLE `user` ADD `moral` INT UNSIGNED DEFAULT '100' NOT NULL ;