CREATE TABLE `terrainsegment4` (
`x` INT NOT NULL ,
`y` INT NOT NULL ,
`type` INT UNSIGNED NOT NULL DEFAULT '1',
PRIMARY KEY ( `x` , `y` )
) TYPE = MYISAM ;

CREATE TABLE `terrainsegment64` (
`x` INT NOT NULL ,
`y` INT NOT NULL ,
`type` INT UNSIGNED NOT NULL DEFAULT '1',
PRIMARY KEY ( `x` , `y` )
) TYPE = MYISAM ;

ALTER TABLE `buildingtype` ADD `convert_into_terrain` INT UNSIGNED NOT NULL DEFAULT '0';

ALTER TABLE `newlog` ADD INDEX ( `time` ) ;


CREATE TABLE `poll` (
`id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
`name` VARCHAR( 255 ) NOT NULL ,
`created` INT UNSIGNED DEFAULT '0' NOT NULL ,
PRIMARY KEY ( `id` ) ,
INDEX ( `created` )
) TYPE = MYISAM ;

CREATE TABLE `poll_answer` (
`poll` INT UNSIGNED NOT NULL ,
`number` INT UNSIGNED NOT NULL ,
`user` INT UNSIGNED NOT NULL ,
PRIMARY KEY ( `poll` , `number` , `user` )
) TYPE = MYISAM ;

 CREATE TABLE `poll_choice` (
`number` INT UNSIGNED DEFAULT '1' NOT NULL ,
`poll` INT UNSIGNED NOT NULL ,
`text` VARCHAR( 255 ) NOT NULL ,
PRIMARY KEY ( `number` , `poll` )
) TYPE = MYISAM CHARACTER SET latin1 COLLATE latin1_general_ci ;

ALTER TABLE `poll_answer` ADD `time` INT UNSIGNED DEFAULT '0' NOT NULL ;
