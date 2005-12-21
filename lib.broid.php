<?php
//this kills all armys and buildings at pos x/y
function zap($x,$y,$noruin=false)
{
	$x = intval($x);
	$y = intval($y);
	
	$armies = sqlgettable("SELECT * FROM `army` WHERE `x`=$x AND `y`=$y");
	foreach ($armies as $army) cArmy::DeleteArmy($army,true,"B.R.O.I.D. : Blue Ray of Instant Death");
	
	sql("DELETE FROM `hellhole` WHERE `x`=$x AND `y`=$y");
	sql("DELETE FROM `item` WHERE `army` = 0 AND `x`=$x AND `y`=$y");
		
	$building = sqlgetobject("SELECT * FROM `building` WHERE `x`=$x AND `y`=$y");
	if ($building) {
		$cssclassarr = cBuilding::removeBuilding($building,$building->user,$noruin,true);
	} else {
		sql("DELETE FROM `terrain` WHERE `x`=$x AND `y`=$y");
		sql("INSERT INTO `terrain` SET `x`=$x,`y`=$y,`type`=5");
		$cssclassarr = RegenSurroundingNWSE($x,$y,true);
	}
	return $cssclassarr;
}
?>
