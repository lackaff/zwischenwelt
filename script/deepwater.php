<?php

require_once("../lib.main.php");
require_once("../lib.map.php");

function hastobedeep ($x,$y) {
	return sqlgetone("SELECT COUNT(*) FROM `terrain` WHERE (`type`=6 or `type`=18) 
		AND abs(`x`-$x)<=2  and abs(`y`-$y)<=2");
}

for($i=0;$i<700000;$i+=150){
	$terrain = sqlgettable("SELECT `id`,`x`,`y`,`type` FROM `terrain` WHERE `type` in (6,18) ORDER BY `id` ASC  LIMIT $i,150");
//	$terrain = sqlgettable("SELECT `id`,`x`,`y`,`type` FROM `terrain` WHERE `type`=18  LIMIT $i,150");
	
	if(count($terrain)==0)break;
	foreach($terrain as $o){
		if(hastobedeep($o->x,$o->y)==25){
			sql("UPDATE `terrain` set `type`=18 where `id`=".$o->id);
			RegenSurroundingNWSE ($o->x,$o->y);
			echo "deep water $o->x,$o->y\n";
		}
	}
//	echo "fetching new ... i = $i<br>";
}
?>
