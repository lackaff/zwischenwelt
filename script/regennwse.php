<?php

require_once("../lib.main.php");
require_once("../lib.map.php");

$left = $gGlobal["minimap_left"];
$right = $gGlobal["minimap_right"];
$top = $gGlobal["minimap_top"];
$bottom = $gGlobal["minimap_bottom"];

$tables = array("terrain","building");
foreach($tables as $t){
	$sum = sqlgetone("SELECT COUNT(*) FROM `$t`");	
	echo "start with table '$t'\n";
	$i = 0;
	do {
		$l = sqlgettable("SELECT * FROM `$t` LIMIT $i,512");
		foreach($l as $x)RegenSurroundingNWSE($x->x,$x->y);
		$i += 512;
		echo " -> index=$i ".round($i/$sum,1)."%\n";
	} while(sizeof($l)>0);
	echo "table finished\n";
}

/*
for($x=$left;$x<=$right;++$x)
	for($y=$top;$y<=$bottom;++$y){
		echo "$x,$y\n";
		RegenSurroundingNWSE($x,$y);
	}
*/

?>
