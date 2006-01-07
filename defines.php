<?php
//verursachte zu viel cpu last
//if ( extension_loaded('zlib') )ob_start('ob_gzhandler');

require_once("defines.mysql.php");

define("MYSQL_ERROR_LOG",BASEPATH."sqlerror.log");
define("PHP_ERROR_LOG",BASEPATH."phperror.log");

define("MSG_BELEIDIGUNG",BASEPATH."beleidigungen.txt");
define("kSessionTimeout",3600*8);
define("kTypeCacheFile",BASEPATH."tmp/tmp_types.php");
define("kTypeCacheFileDisabled",false);

#use this for unix based systems
ini_set('include_path', ".:".BASEPATH);
#and this one for windows based sysmtes
#ini_set('include_path', ".;".BASEPATH);

define("kPathSwitchTesting",$_SERVER["SCRIPT_FILENAME"]." # ".$_SERVER["PATH_TRANSLATED"]." # ".$_SERVER["HTTP_HOST"]);
//define("kZWTestMode",$_SERVER["HTTP_HOST"]=="localhost" || $_SERVER["HTTP_HOST"]=="dev.zwischenwelt.net-play.de");
define("kZWTestMode",false);
define("kZWTestMode2",kZWTestMode);
define("kZWTestMode_ArmySteps",3);
define("kZWTestMode_BuildingActionSteps",10); // units produced
define("ARMY_MOVE",600); // time a army move is performed
define("kConstructionPic","construction.png");
define("kConstructionPlanPic","constructionplan.png");
define("kTransCP","transcp.gif");

define("kStats_dtime",60*60*12);

$gResFields = array("lumber","stone","food","metal","runes");
$gResNames = array("Holz","Stein","Nahrung","Metall","Runen");
$gResTypeVars = Array(1 => "lumber",2 => "stone", 3 => "food", 4 => "metal", 5 => "runes");
$gResTypeNames = Array(1 => "Holz",2 => "Stein", 3 => "Nahrung", 4 => "Metall", 5 => "Runen");

//resource list
$gRes = my_array_combine($gResNames,$gResFields);
//list of things that can be done by people, worker adjustment
$gAdjust = array_merge($gRes,array("Reparieren"=>"repair"));

define("kMapTileSize",25);
define("kMapScript","mapjs7.php");
define("kMapNaviScript","mapnavi7.php");
define("kZWStyle_Neutral","zwstyle.css");
define("kJSMapVersion","33"); // muss mit kJSMapVersion in mapjs7_core.js uebereinstimmen
?>
