<?php

require_once("../lib.main.php");
require_once("../lib.map.php");


for($i=0;$i<700000;$i+=150){
	$terrain = sqlgettable("SELECT `id`,`x`,`y`,`type` FROM `terrain` WHERE `type`=6 ORDER BY `id` ASC  LIMIT $i,150");
	
	if(count($terrain)==0)break;
	foreach($terrain as $o){
		RegenSurroundingNWSE ($o->x,$o->y);
	}
}
?>
