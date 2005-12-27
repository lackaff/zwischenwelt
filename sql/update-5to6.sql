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