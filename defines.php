<?php
//verursachte zu viel cpu last
//if ( extension_loaded('zlib') )ob_start('ob_gzhandler');

require_once("defines.mysql.php");
if (!defined("ZW_LOGDB_PREFIX")) define("ZW_LOGDB_PREFIX",""); // can be something like "`zwlog`."  used as in  "SELECT * FROM ".ZW_LOGDB_PREFIX."`calllog` WHERE ..."
if (!defined("ZW_MAIL_SENDER")) define("ZW_MAIL_SENDER","zwischenwelt@net-play.de");
if (!defined("ZW_NEWREGISTRATION_NOTIFY")) define("ZW_NEWREGISTRATION_NOTIFY",false);
if (!defined("ZW_ENABLE_CALLLOG")) define("ZW_ENABLE_CALLLOG",true); // logs every page-call with parameters
if (!defined("ZW_ENABLE_PROFILING")) define("ZW_ENABLE_PROFILING",false); // time and memory usage profiling, disable for better performance

//define("MYSQL_ERROR_LOG",BASEPATH."sqlerror.log");
define("PHP_ERROR_LOG",BASEPATH."phperror.log");

define("MSG_BELEIDIGUNG",BASEPATH."beleidigungen.txt");
define("kSessionTimeout",3600*8);
define("kTypeCacheFile",BASEPATH."tmp/tmp_types.php");
define("kTypeCacheFileDisabled",false);

#use this for unix based systems
ini_set('include_path', ".:".BASEPATH);
#and this one for windows based sysmtes
#ini_set('include_path', ".;".BASEPATH);

define("kPathSwitchTesting",false); // $_SERVER["SCRIPT_FILENAME"]." # ".$_SERVER["PATH_TRANSLATED"]." # ".$_SERVER["HTTP_HOST"]);
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


define("kProfileArmyLoop",ZW_ENABLE_PROFILING); // see also ZW_ENABLE_PROFILING
define("kMapTileSize",25);
define("kMapScript","mapjs7.php");
define("kMapNaviScript","mapnavi7.php");
define("kZWStyle_Neutral","zwstyle.css");
define("kJSMapVersion","43"); // mapversion, $gGlobal["typecache_version_adder"] wird immer addiert
define("kStyleSheetVersion","1"); // css version, $gGlobal["typecache_version_adder"] wird immer addiert
define("kDummyFrames",10); // soviele dummy-befehls-empfaenger frames gibt es, viele -> schnell aufeinander folgende mapclicks können besser bearbeitet werden
?>
