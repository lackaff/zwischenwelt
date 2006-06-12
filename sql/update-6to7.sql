ALTER TABLE `buildingtype` ADD `maxgfxlevel` INT UNSIGNED DEFAULT '1' NOT NULL ,
ADD `maxrandcenter` INT UNSIGNED DEFAULT '0' NOT NULL ,
ADD `maxrandborder` INT UNSIGNED DEFAULT '0' NOT NULL ;



ALTER TABLE `terraintype`
ADD `maxrandcenter` INT UNSIGNED DEFAULT '0' NOT NULL ,
ADD `maxrandborder` INT UNSIGNED DEFAULT '0' NOT NULL ;

# haus und lager
UPDATE `buildingtype` SET `maxgfxlevel` = 2 WHERE `id` IN (6,7);

# gras
UPDATE `terraintype` SET `maxrandcenter` = 10, `maxrandborder` = 10, `gfx` = 'landschaft/grassrandom/grass_nwse_%RND%.png' WHERE `id` = 1;

# local styles
ALTER TABLE `user` CHANGE `usegfxpath` `localstyles` TINYINT( 3 ) UNSIGNED DEFAULT '0' NOT NULL ;
UPDATE `user` SET `localstyles` = 0;

INSERT INTO `terraintype` VALUES (26, 'Nadelwald', '', 120, 0, '#2D6220', 'nadelwald/nadelwald-%NWSE%.png', '', 1, 1, 1, 2, '26', '', 0, 0);

CREATE TABLE `userkills` (
`user` INT UNSIGNED NOT NULL DEFAULT '0',
`unittype` INT UNSIGNED NOT NULL DEFAULT '0',
`kills` FLOAT NOT NULL DEFAULT '0',
PRIMARY KEY ( `user` , `unittype` )
) TYPE = MYISAM ;

CREATE TABLE `wonder` (
`id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
`user` INT UNSIGNED NOT NULL ,
`spelltype` INT UNSIGNED NOT NULL ,
`time` INT UNSIGNED NOT NULL ,
PRIMARY KEY ( `id` )
);

CREATE TABLE `calllog` (
  `id` int(10) unsigned NOT NULL,
  `time` int(10) unsigned NOT NULL default '0',
  `user` int(10) unsigned NOT NULL default '0',
  `ip` varchar(15) NOT NULL default '',
  `script` varchar(255) NOT NULL default '',
  `query` text NOT NULL,
  `post` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `fire` (
`x` INT NOT NULL ,
`y` INT NOT NULL ,
`nextdamage` INT UNSIGNED NOT NULL DEFAULT '0',
`nextspread` INT UNSIGNED NOT NULL DEFAULT '0',
PRIMARY KEY ( `x` , `y` ) ,
INDEX ( `nextdamage` , `nextspread` )
) TYPE = MYISAM ;
ALTER TABLE `fire` ADD `created` INT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `fire` ADD `putoutprob` SMALLINT UNSIGNED NOT NULL DEFAULT '0';

ALTER TABLE `terraintype` ADD `flags` INT UNSIGNED NOT NULL DEFAULT '0';

ALTER TABLE `buildingtype` ADD `fire_prob` SMALLINT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `terraintype` ADD `fire_prob` SMALLINT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `terraintype` ADD `fire_burnout_type` INT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `user` ADD `buildings_on_fire` INT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `fire` ADD INDEX ( `created` );