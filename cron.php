<?php define("NO_CONTENT_TYPE"); ?>
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
Job::queueIfNonQueued("Stats");
Job::queueIfNonQueued("Firefix");
Job::queueIfNonQueued("RemoveGuildRequests");
Job::queueIfNonQueued("PurgeOldJobs");
Job::queueIfNonQueued("Bier");
Job::queueIfNonQueued("ResCalc");
Job::queueIfNonQueued("RuinCorruption");
Job::queueIfNonQueued("Fire");
Job::queueIfNonQueued("UpgradesFix");
Job::queueIfNonQueued("SeamonsterSpawn");
Job::queueIfNonQueued("RemoveZeroItems");
Job::queueIfNonQueued("GrowWood");
Job::queueIfNonQueued("GrowCorn");
Job::queueIfNonQueued("CalcPoints");
Job::queueIfNonQueued("PurgeOldLogs");
Job::queueIfNonQueued("YoungForest");
Job::queueIfNonQueued("Weather");
Job::queueIfNonQueued("Spells");
Job::queueIfNonQueued("UserProdPop");
Job::queueIfNonQueued("SupportSlots");
Job::queueIfNonQueued("FinishConstructions");
Job::queueIfNonQueued("ThinkBuildings");
Job::queueIfNonQueued("GuildRes");
Job::queueIfNonQueued("RepairBuildings");
Job::queueIfNonQueued("Mana");
Job::queueIfNonQueued("Runes");
Job::queueIfNonQueued("Weltbank");
Job::queueIfNonQueued("Hellholes");
Job::queueIfNonQueued("Quest");
//Job::queueIfNonQueued("ItemCorruption");

// run the pending jobs
while(Job::runJobs(10, true) > 0);

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
$gTechnologyLevelsOfAllUsers = sqlgetgrouptable("SELECT `user`,`type`,`level` FROM `technology`","user","type","level");
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
profile_page_start("cron.php - army_move",true);
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

$old_time = $time;
$old_dtime = $dtime;
$gMiniCronFromCron = true;
include("minicron.php");
$time = $old_time;
$dtime = $old_dtime;


//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
profile_page_start("cron.php - oldshooting",true);
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

$oldshootings = sqlgettable("SELECT * FROM `shooting` WHERE `autocancel` = 1 AND 
	`start`		< ".($time-kShootingAlarmTimeout)." AND 
	`lastshot`	< ".($time-kShootingAlarmTimeout));
foreach ($oldshootings as $o) {
	cFight::EndShooting($o,"Es wurde lange nicht mehr geschossen");
} 
unset($oldshootings);

//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
profile_page_start("cron.php - army_fight",true);
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++


$fights = sqlgettable("SELECT * FROM `fight`");
if (count($fights) > 0) {
	TablesLock();
	foreach ($fights as $fight) cFight::FightStep($fight);
	TablesUnlock();
}
unset($fights);



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




//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
profile_page_start("cron.php - tech",true);
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++



//$gTechnologyTypes = sqlgettable("SELECT * FROM `technologytype`","id");
sql("LOCK TABLES `user` WRITE,`technology` WRITE,`building` READ,`phperror` WRITE,
						`sqlerror` WRITE, `newlog` WRITE");
$technologies = sqlgettable("SELECT * FROM `technology` WHERE `upgrades` > 0 ORDER BY `level`");
$time = time();
foreach ($technologies as $o) {
	if ($o->upgradetime > 0 && ($o->upgradetime < $time || kZWTestMode) ){
		// upgrade finished

		//only complete the tech if requirenments meet
		//echo "<br>\nHasReq(".($gTechnologyType[$o->type]->req_geb).",".($gTechnologyType[$o->type]->req_tech).",".($o->user).",".($o->level+1).")<br>\n";
		if(HasReq($gTechnologyType[$o->type]->req_geb,$gTechnologyType[$o->type]->req_tech,$o->user,$o->level+1)){
			sql("UPDATE `technology` SET
				`level` = `level` + 1 ,
				`upgrades` = `upgrades` - 1 ,
				`upgradetime` = 0 WHERE `id` = ".$o->id." LIMIT 1");
				
			$gTechnologyLevelsOfAllUsers[$o->user][$o->type] = $o->level + 1;
			
			$text = $gTechnologyType[$o->type]->name." von user ".$o->user." ist nun Level ".($o->level+1);
			echo $text."<br>\n";
			
			// TODO : neue log meldung machen !
			LogMe($o->user,NEWLOG_TOPIC_BUILD,NEWLOG_UPGRADE_FINISHED,0,0,$o->level+1,$gBuildingType[$o->type]->name,"",false);
		} else {
			sql("UPDATE `technology` SET
				`upgrades` = 0 ,
				`upgradetime` = 0 WHERE `id` = ".$o->id." LIMIT 1");
				
			$text = $gTechnologyType[$o->type]->name." von user ".$o->user." wurde abgebrochen, da die anforderungen nicht erfüllt wurden";
			echo $text."<br>\n";
		}
	} else if ($o->upgradetime == 0) {
		// test if upgrade can be started
		
		// only one upgrade per building at once
		$other = sqlgetone("SELECT 1 FROM `technology` WHERE 
			`upgradetime` > 0 AND `upgradebuilding` = ".$o->upgradebuilding." AND `id` <> ".$o->id);
		
		if (!$other) {
			$techtype = $gTechnologyType[$o->type];
			$level = GetTechnologyLevel($o->type,$o->user);
			// only upgrade if the technological requirements are met
			if (!HasReq($techtype->req_geb,$techtype->req_tech,$o->user,$level+1)) {
				sql("UPDATE `technology` SET `upgrades` = 0 WHERE `id` = ".$o->id." LIMIT 1");
				
				$text = $techtype->name." von user ".$o->user." wurde nicht gestartet, da die anforderungen nicht erfüllt wurden";
				echo $text."<br>\n";
				
				continue;
			}
		
			$upmod = cTechnology::GetUpgradeMod($o->type,$o->level);
			if (UserPay($o->user,
				$upmod * $techtype->basecost_lumber,
				$upmod * $techtype->basecost_stone,
				$upmod * $techtype->basecost_food,
				$upmod * $techtype->basecost_metal,
				$upmod * $techtype->basecost_runes)) {
				if ($gVerbose) echo $techtype->name." von user ".$o->user." upgrade gestartet<br>\n";
				$finishtime = $time + cTechnology::GetUpgradeDuration($o->type,$o->level);
				sql("UPDATE `technology` SET `upgradetime` = ".$finishtime." WHERE `id` = ".$o->id." LIMIT 1");
				
				$text = $gTechnologyType[$o->type]->name." von user ".$o->user." wurde gestartet";
				echo $text."<br>\n";
			}
		}
	}
}
sql("UNLOCK TABLES");



//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
profile_page_start("cron.php - pillage",true);
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

$pillages = sqlgettable("SELECT * FROM `pillage`");
if (count($pillages) > 0) {
	TablesLock();
	foreach ($pillages as $pillage) if (!in_array($pillage->army,$gAllFights)) cFight::PillageStep($pillage); 
	TablesUnlock();
}
unset($pillages);

//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
profile_page_start("cron.php - siege",true);
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

$sieges = sqlgettable("SELECT * FROM `siege`");
if (count($sieges) > 0) {
	TablesLock();
	foreach ($sieges as $siege) if (!in_array($siege->army,$gAllFights)) cFight::SiegeStep($siege); 
	TablesUnlock();
}
unset($sieges);


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
