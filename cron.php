<?php define("NO_CONTENT_TYPE",1); ?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<!-- <meta http-equiv="refresh" content="60; URL=cron.php"> -->
<!-- ... andere Angaben im Dateikopf ... -->
</head>
<body>
<?php
set_time_limit(0);
//exit(0);
//$gSQL_NOT_FATAL = true; 
$lock = FALSE;
if($lock){
	if(file_exists("/tmp/zw-cron.lock")){
		$fstat = stat("/tmp/zw-cron.lock");
		if(time() - $stat[9] > 600){
			shell_exec("/bin/rm -f /tmp/zw-cron.lock");
			shell_exec("echo lock > /tmp/zw-cron.lock");
		}else{
			exit(0);
		}
	}else{
		shell_exec("echo lock > /tmp/zw-cron.lock");
	}
}


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

// ---------- queue necessary jobs ---------------
Job::queueIfNonQueuedOrRunning("Stats");
Job::queueIfNonQueuedOrRunning("Firefix");
Job::queueIfNonQueuedOrRunning("RemoveGuildRequests");
Job::queueIfNonQueuedOrRunning("PurgeOldJobs");
Job::queueIfNonQueuedOrRunning("Bier");
Job::queueIfNonQueuedOrRunning("ResCalc");
Job::queueIfNonQueuedOrRunning("RuinCorruption");
Job::queueIfNonQueuedOrRunning("Fire");
Job::queueIfNonQueuedOrRunning("UpgradesFix");
Job::queueIfNonQueuedOrRunning("SeamonsterSpawn");
Job::queueIfNonQueuedOrRunning("RemoveZeroItems");
Job::queueIfNonQueuedOrRunning("GrowWood");
Job::queueIfNonQueuedOrRunning("GrowCorn");
Job::queueIfNonQueuedOrRunning("CalcPoints");
Job::queueIfNonQueuedOrRunning("PurgeOldLogs");
Job::queueIfNonQueuedOrRunning("YoungForest");
Job::queueIfNonQueuedOrRunning("Weather");
Job::queueIfNonQueuedOrRunning("Spells");
Job::queueIfNonQueuedOrRunning("UserProdPop");
Job::queueIfNonQueuedOrRunning("SupportSlots");
Job::queueIfNonQueuedOrRunning("FinishConstructions");
Job::queueIfNonQueuedOrRunning("ThinkBuildings");
Job::queueIfNonQueuedOrRunning("GuildRes");
Job::queueIfNonQueuedOrRunning("RepairBuildings");
Job::queueIfNonQueuedOrRunning("Mana");
Job::queueIfNonQueuedOrRunning("Runes");
Job::queueIfNonQueuedOrRunning("Weltbank");
Job::queueIfNonQueuedOrRunning("Hellholes");
Job::queueIfNonQueuedOrRunning("Quest");
Job::queueIfNonQueuedOrRunning("ArmyThink");
Job::queueIfNonQueuedOrRunning("Fight");
Job::queueIfNonQueuedOrRunning("Shooting");
Job::queueIfNonQueuedOrRunning("Tech");
Job::queueIfNonQueuedOrRunning("Siege");
Job::queueIfNonQueuedOrRunning("Pillage");
Job::queueIfNonQueuedOrRunning("FinishUnits");
//Job::queueIfNonQueuedOrRunning("ItemCorruption");
//Job::queueIfNonQueuedOrRunning("Test");

// run the pending jobs
Job::runJobs(10, true);

$time = time();

if (1) {
	$lasttick = intval($gGlobal["lasttick"]);
	if ($time - $lasttick < 60) { 
		echo "skipping cron.php, only needed every 60 seconds<br>\n";
		echo "dt=".($time - $lasttick)."<br>\n";
		echo "lasttick = ".date("H:i d-m-Y",$lasttick)." $lasttick<br>\n";
		echo "curt     = ".date("H:i d-m-Y",$time)." $time<br>\n";
		exit(0);  // tick every 60 seconds
	}
}


// wichtige GLOBALS INITIALISIEREN!!! nix loeschen es sei denn ihr seid euch _WIRKLICH_ sicher
// $gTechnologyLevelsOfAllUsers = sqlgetgrouptable("SELECT `user`,`type`,`level` FROM `technology`","user","type","level");
$gVerbose = false; // if false, echo only segments

$gThiscronStartTime = $time;
$lasttick = $gGlobal["lasttick"];
$dtime = $time - $lasttick;
if($dtime < 0)$dtime = 0;
sql("UPDATE `global` SET `value`=$time WHERE `name`='lasttick' LIMIT 1");

$gGlobal["ticks"]++;
if($gGlobal["ticks"] > 30000)$gGlobal["ticks"] = 0; // todo : unhardcode
sql("UPDATE `global` SET `value`=".intval($gGlobal["ticks"])." WHERE `name`='ticks' LIMIT 1");

echo "dtime = $dtime<br><br>";


$time = time();


//MAXHP: ceil($maxhp + $maxhp/100*1.5*$level);  // NOTE: see also lib.building.php calcMaxBuildingHp

/*
$growwood = sqlgettable("SELECT * FROM `terrain` WHERE `type` = ".kTerrain_YoungForest." AND RAND() < ".kYoungToForest);
echo count($growwood)." units of YoungForest turned to Forest<br>";
foreach ($growwood as $o) {
	sql("UPDATE `terrain` SET `type` = ".kTerrain_Forest." WHERE `id` = ".$o->id." LIMIT 1");
	RegenSurroundingNWSE($o->x,$o->y);
}
unset($growwood);
*/
/*
//if map to old (1 day) or there is not map then generate
if(($gGlobal["ticks"] % 60*24) == 0 || !file_exists(GetMiniMapFile("user",GetMiniMapLastTime("user")))){
	//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	profile_page_start("cron.php - minimap",true);
	//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	
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
}
*/


//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
profile_page_end();
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

sql("UPDATE `global` SET `value`='".intval(time() - $time)."' WHERE `name`='crontime'");

?>
</body>
</html>
<?php
	if($lock)
		shell_exec("rm -f /tmp/zw-cron.lock");

SetGlobal("lastcronduration",time() - $gThiscronStartTime);
echo "<br>\n... cron finished";

exit(0);

?>
