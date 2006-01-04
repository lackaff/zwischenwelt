<html>
<head>
<!-- <meta http-equiv="refresh" content="60; URL=cron.php"> -->
<!-- ... andere Angaben im Dateikopf ... -->
</head>
<body>
<?php

error_reporting(E_ALL);

require_once("cronlib.php");
require_once("lib.quest.php");
require_once("lib.map.php");
require_once("lib.technology.php");
require_once("lib.spells.php");
require_once("lib.army.php"); // sql
require_once("lib.weather.php");
require_once("lib.hellholes.php");
require_once("lib.spells.php");
require_once("lib.score.php");
require_once("lib.hook.php");

// generate new minimaps for each mode
$o = sqlgetobject("SELECT MIN(`x`) as minx,MAX(`x`) as maxx,MIN(`y`) as miny,MAX(`y`) as maxy FROM `building`");
$left = $o->minx - 10;
$right = $o->maxx + 10;
$top = $o->miny - 10;
$bottom = $o->maxy + 10;
SetGlobal("minimap_left",$left);
SetGlobal("minimap_right",$right);
SetGlobal("minimap_top",$top);
SetGlobal("minimap_bottom",$bottom);

$modes = array("user","creep","guild");
foreach($modes as $mode){
	$global = GetMiniMapGlobal($mode);
	$filename = GetMiniMapFile($mode,$time);
	echo "rendering $mode minimap to file $filename ...<br>\n";
	renderMinimap($top,$left,$bottom,$right,$filename,$mode);
	SetGlobal($global,$time);
}

echo "<br>\n... cron minimap finished<br>\n";
?>
