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

