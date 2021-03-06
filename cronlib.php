<?php
require_once("lib.main.php");
require_once("lib.army.php");
require_once("lib.construction.php");
require_once("lib.building.php");
require_once("lib.hook.php");


function StartBuild ($con) {
	if (empty($con)) return false;
	global $gBuildingType,$gVerbose;
	
	// param : construction object
	// returns true if the building started successfully
	// WARNING ! USES TABLE LOCK FOR CONSTRUCTIONS !

	$success = false;
	$debugname = $gBuildingType[$con->type]->name."(".$con->x.",".$con->y.")";

	// check if buildable and affordable
	$blockingbuilding = sqlgetone("SELECT 1 FROM `building` WHERE `x` = ".$con->x." AND `y` = ".$con->y." LIMIT 1");
	if ($blockingbuilding) {
		if ($gVerbose) echo "can't start $debugname, a building is in the way<br>";
		sql("DELETE FROM `construction` WHERE `id` = ".$con->id);
		sql("UPDATE `construction` SET `priority` = `priority` - 1 WHERE `user` = ".$con->user);
		// TODO : SEND MESSAGE TO PLAYER, but not from this function
	} else if (sqlgetone("SELECT 1 FROM `army` WHERE `x` = ".$con->x." AND `y` = ".$con->y." LIMIT 1")) {
		if ($gVerbose) echo "can't start $debugname, army is in the way, waiting for army to move<br>";
		// TODO message to user ?
	} else if (InBuildCross($con->x,$con->y,$con->user,0)) {
		$buildingtype = $gBuildingType[$con->type];
		if (CanBuildHere($con->x,$con->y,$con->type,$con->user,0,true) && 
			HasReq($buildingtype->req_geb,$buildingtype->req_tech,$con->user)) {

			// headquater is free
			if ($con->type == kBuilding_HQ || UserPay($con->user,
											$buildingtype->cost_lumber,$buildingtype->cost_stone,
											$buildingtype->cost_food,$buildingtype->cost_metal,$buildingtype->cost_runes))
			{
				sql("DELETE FROM `construction` WHERE `id` = ".$con->id." LIMIT 1");
				sql("UPDATE `construction` SET `priority` = `priority` - 1 WHERE `user` = ".$con->user);
				$building = new EmptyObject();
				$building->x = $con->x;
				$building->y = $con->y;
				$building->flags = kBuildingFlag_AutoShoot_Enemy;
				$building->param = $con->param;
				$building->user = $con->user;
				$building->type = $con->type;
				$building->level = 0;
				$building->hp = $buildingtype->maxhp;
				$building->construction = time() + GetBuildTime($building->x,$building->y,$building->type,0,$building->user); // fertigstellungszeit
				
				
				sql("INSERT INTO `building` SET ".obj2sql($building));
				$success = true;
				// delete all construction on x,y
				$canceledcons = sqlgetonetable("SELECT `id` FROM `construction` WHERE
					`x` = ".$building->x." AND `y` = ".$building->y);
				foreach($canceledcons as $o)
					CancelConstruction($o);
				// TODO : SEND MESSAGE TO PLAYER
			} else {
				if ($gVerbose) echo "can't start $debugname, not enough money<br>";
			}
		} else {
			// building here is impossible, abort plan
			sql("DELETE FROM `construction` WHERE `id` = ".$con->id." LIMIT 1");
			sql("UPDATE `construction` SET `priority` = `priority` - 1 WHERE `user` = ".$con->user);
		}
	} else {
		$max=sqlgetone("SELECT MAX(`priority`)+1 FROM `construction` WHERE `user`=".$con->user." GROUP BY `user`");
		if ($con->priority != $max)
			sql("UPDATE `construction` SET `priority`=".$max." WHERE `id`=".$con->id." LIMIT 1");
		if ($gVerbose) echo "can't start $debugname , isn't in buildingcross ... moved to the end of building queue<br>";
		// TODO message to user !
	}

	return $success;
}

function CompleteBuild ($building,$bAutomaticUpgradeBuildingTo=true) { // object
	if (empty($building)) return;
	global $gBuildingType;
	
	if($gBuildingType[$building->type]->convert_into_terrain>0){
		$terrain = $gBuildingType[$building->type]->convert_into_terrain;
		echo "building complete, create terrain $terrain<br>\n";
		setTerrain($building->x,$building->y,$terrain);
		sql("DELETE FROM `building` WHERE `id`=".$building->id." LIMIT 1");
	} else {	
		//automaticaly plan upgrades?
		if($bAutomaticUpgradeBuildingTo){
			echo "auto upgrade enabled<br>\n";
			$upgradeto = sqlgetone("SELECT MIN(`level`+`upgrades`) FROM `building` 
				WHERE `user`=".intval($building->user)." AND `type`=".intval($building->type)." AND `construction`=0");
			if (!$upgradeto) $upgradeto = 0; // no previous buildings found
		} else $upgradeto = 0;
		echo "building ($building->x,$building->y) complete, $upgradeto upgrades planned<br>\n";
		sql("UPDATE `building` SET `construction`=0,`upgrades`=".intval($upgradeto)." WHERE `id`=".$building->id." LIMIT 1");
	}
	
	//RegenSurroundingNWSE($building->x,$building->y);
	
	LogMe($building->user,NEWLOG_TOPIC_BUILD,NEWLOG_BUILD_FINISHED,$building->x,$building->y,0,$gBuildingType[$building->type]->name,"",false);
	Hook_CreateBuilding($building);
}




?>
