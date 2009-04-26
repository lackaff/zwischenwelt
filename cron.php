<?php define("NO_CONTENT_TYPE",1); ?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<!-- <meta http-equiv="refresh" content="60; URL=cron.php"> -->
<!-- ... andere Angaben im Dateikopf ... -->
</head>
<body>
<?php
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
Job::queueIfNonQueuedOrRunning("Shooting");
Job::queueIfNonQueuedOrRunning("Tech");
Job::queueIfNonQueuedOrRunning("Siege");
Job::queueIfNonQueuedOrRunning("Pillage");
Job::queueIfNonQueuedOrRunning("Test");
//Job::queueIfNonQueuedOrRunning("ItemCorruption");

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


//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
profile_page_start("cron.php - actions part1",true);
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++



$i_bsteps = kZWTestMode ? kZWTestMode_BuildingActionSteps : 1;
//for ($kbstep=0;$kbstep<$i_bsteps;$kbstep++) {

	// process running actions
	$running_actions = sqlgettable("SELECT * FROM `action` WHERE `starttime` > 0 GROUP BY `building`");
	foreach ($running_actions as $action) {
		$unittype = $gUnitType[$action->param1];
		
		// has one action cycle completed ?
		if ($time >= ($action->starttime + $unittype->buildtime) || kZWTestMode) {						
			if ($action->param2 > 0) {
				// unit complete
				switch ($action->cmd) {
					case kActionCmd_Build:
						$curtargetid = GetBParam($action->building,"target",0);
						// TODO : der check hier ob das gebaeude existiert geht vermutlich auf die performance, bessere bei gebaeude tot alle stationierungen auf das gebaeude canceln
						if (!sqlgetone("SELECT 1 FROM `building` WHERE `id` = ".intval($curtargetid))) $curtargetid = 0;
						if ($curtargetid == 0) $curtargetid = $action->building;
						
						// pay pop
						$actionuserid = intval(sqlgetone("SELECT `user` FROM `building` WHERE `id` = ".intval($action->building)));
						sql("UPDATE `user` SET `pop`=`pop`-1 WHERE `id`=$actionuserid");
						
						cUnit::AddUnits($curtargetid,$action->param1,1,kUnitContainer_Building,$actionuserid);
					break;
				}
				
				//echo "action ".$action->id." : in building ".$action->building." produced one ".$unittype->name." (".($action->param2-1)." left)<br>";

				if ($action->param2-1 > 0)
						sql("UPDATE `action` SET `starttime` = 0 , `param2` = `param2` - 1 WHERE `id` = ".$action->id);
				else	sql("DELETE FROM `action` WHERE `id` = ".$action->id);
			} else sql("DELETE FROM `action` WHERE `id` = ".$action->id);
		}
	}
	unset($running_actions);

profile_page_start("cron.php - actions part2",true);
$gAvailableUnitTypesByUser = array();

	// start action where building has nothing to do
	$waiting_actions = sqlgettable("SELECT *,MAX(`starttime`) as `maxstarttime` FROM `action` GROUP BY `building`");
	foreach ($waiting_actions as $action) if ($action->maxstarttime == 0) {
		$unittype = $gUnitType[$action->param1];
		$actionuserid = intval(sqlgetone("SELECT `user` FROM `building` WHERE `id` = ".intval($action->building)));
		
		$availableUnitTypes = false;
		if (isset($gAvailableUnitTypesByUser[$actionuserid])) {
			$availableUnitTypes = $gAvailableUnitTypesByUser[$actionuserid];
		} else {
			$availableUnitTypes = array();
			$gAvailableUnitTypesByUser[$actionuserid] = $availableUnitTypes;
		}
		
		$available = false;
		if (isset($availableUnitTypes[$unittype->id])) {
			$available = $availableUnitTypes[$unittype->id];
		} else {
			$available = HasReq($unittype->req_geb,$unittype->req_tech_a.",".$unittype->req_tech_v,$actionuserid);
			$gAvailableUnitTypesByUser[$actionuserid][$unittype->id] = $available;
		}
		
		// only build if the technological requirements are met
		if (!$available) {
			sql("DELETE FROM `action` WHERE `id` = ".$action->id);
			continue;
		}
		
		// building weight-limit, used to block ramme
		$max_weight_left_source = cUnit::GetMaxBuildingWeight($gUnitType[$action->param1]->buildingtype);
		if ($max_weight_left_source >= 0) {
			$curtargetid = GetBParam($action->building,"target",0);
			if ($curtargetid == 0) $curtargetid = $action->building;
			$max_weight_left_source -= cUnit::GetUnitsSum(cUnit::GetUnits($curtargetid,kUnitContainer_Building),"weight");
			if ($max_weight_left_source < $gUnitType[$action->param1]->weight) continue;
		}
		
		if (sqlgetone("SELECT `pop` FROM `user` WHERE `id` = ".intval($actionuserid)) <= 0 || 
			!UserPay($actionuserid,$unittype->cost_lumber,$unittype->cost_stone,
									$unittype->cost_food,$unittype->cost_metal,$unittype->cost_runes))
		{
			//echo "action ".$action->id." : in building ".$action->building." (".$action->param2." ".$unittype->name.") is waiting for ressources<br>";
			continue;
		}
								
		sql("UPDATE `action` SET `starttime` = ".$time." WHERE `id` = ".$action->id);
		//echo "action ".$action->id." in building ".$action->building." started<br>";
	}
	unset($waiting_actions);
//}



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
