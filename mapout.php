<?php

require("lib.php");

if(isset($f_x))$px = intval($f_x); else $px = 0;
if(isset($f_y))$py = intval($f_y); else $py = 0;
if(isset($f_dx))$dx = intval($f_dx); else $dx = 10;
if(isset($f_dy))$dy = intval($f_dy); else $dy = 10;

$layer["terrain"] = Array();
$layer["army"] = Array();
$layer["building"] = Array();

$left = $px;
$right = $px + $dx;
$top = $py;
$bottom = $py + $dy;

/*
$left = sqlgetone("SELECT MIN(`x`) FROM `building`")-10;
$right = sqlgetone("SELECT MAX(`x`) FROM `building`")+10;
$top = sqlgetone("SELECT MIN(`y`) FROM `building`")-10;
$bottom = sqlgetone("SELECT MAX(`y`) FROM `building`")+10;
*/

$user = sqlgettable("SELECT * FROM `user`","id");
$tt = sqlgettable("SELECT * FROM `terraintype`","id");
$bt = sqlgettable("SELECT * FROM `buildingtype`","id");

$dx = abs($right-$left);
$dy = abs($bottom-$top);

// TODO : FIXME : terrainsegmente
$lt = sql("SELECT `type`,`x`,`y`,`nwse` FROM `terrain` WHERE $left<=`x` AND `x`<=($right) AND $top<=`y` AND `y`<=($bottom)");
while ($x = mysql_fetch_object($lt))
{
	$layer["terrain"][] = $x;
	$x = null;
}
$lt = null;

$lb = sql("SELECT `id`,`type`,`x`,`y`,`user`,`level`,`nwse` FROM `building` WHERE $left<=`x` AND `x`<=($right) AND $top<=`y` AND `y`<=($bottom)");
while ($x = mysql_fetch_object($lb))
{
	$layer["building"][] = $x;
	$x = null;
}
$lb = null;

$la = sql("SELECT `id`,`x`,`y`,`user` FROM `army` WHERE $left<=`x` AND `x`<=($right) AND $top<=`y` AND `y`<=($bottom)");
while ($x = mysql_fetch_object($la))
{
	$layer["army"][] = $x;
	$x = null;
}
$la = null;

echo "x=$px|y=$py|dx=$dx|dy=$dy";
echo "|terrain=";foreach($layer["terrain"] as $x){foreach($x as $n=>$v)echo "$v:";echo "*;";}
echo "|building=";foreach($layer["building"] as $x){foreach($x as $n=>$v)echo "$v:";echo "*;";}
echo "|army=";foreach($layer["army"] as $x){foreach($x as $n=>$v)echo "$v:";echo "*;";}

?>
