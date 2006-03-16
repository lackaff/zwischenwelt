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


ALTER TABLE `buildingtype` ADD `flags` INT UNSIGNED DEFAULT '0' NOT NULL AFTER `special` ;

CREATE TABLE `shooting` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `attacker` int(10) unsigned NOT NULL default '0',
  `attackertype` int(10) unsigned NOT NULL default '0',
  `defender` int(10) unsigned NOT NULL default '0',
  `defendertype` int(10) unsigned NOT NULL default '0',
  `start` int(10) unsigned NOT NULL default '0',
  `lastshot` int(10) unsigned NOT NULL default '0',
  `fightlog` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM ;
ALTER TABLE `shooting` ADD `autocancel` TINYINT UNSIGNED DEFAULT '0' NOT NULL ;

ALTER TABLE `fightlog` DROP INDEX `fight` ;
#ALTER TABLE `fightlog` DROP `fight` ;  # unused but still in table, to keep old fights alive
ALTER TABLE `fight` ADD `fightlog` INT UNSIGNED NOT NULL ;
ALTER TABLE `buildingtype` ADD `weightlimit` INT UNSIGNED DEFAULT '0' NOT NULL AFTER `flags` ;
ALTER TABLE `session` ADD `usegfx` TINYINT UNSIGNED DEFAULT '0' NOT NULL ;

INSERT INTO `buildingtype` VALUES (75, 'Muehle', 'bis jetzt nur reine Dekoration', 100, 100, 0, 0, 0, '1:0', '', 14400, 100, 0, '', 'red', 'A', 'yellow', 0, 'gebaeude-r1/muehle.gif', 0, 0, 0, '', 5, 0, 0, 0, 1, 1, 1, '', '', '3', '', '', 1, 0, 1, 0, 1, 0, 0);

