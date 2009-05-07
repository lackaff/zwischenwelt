<?php

require_once("lib.technology.php");
require_once("lib.map.php");


// check if other building in 2x2 cross
// TODO : userID ! ensure
// priority=-1 means all plans, new building-plan
// priority=0 means ignore plans, for a constructionstart (cron)
function InBuildCross ($x,$y,$user,$priority=-1) {
	$x = intval($x);
	$y = intval($y);
	$d = kBuildingRequirenment_CrossRadius;

	// allow building of first building anywhere
	if (!UserHasBuilding($user,kBuilding_HQ)) return true;

	// can only build near own buildings
	$cond = "`x` >= ".($x-$d)." AND `x` <= ".($x+$d)." AND 
			 `y` >= ".($y-$d)." AND `y` <= ".($y+$d)." AND `user` = ".$user;
	if (sqlgetone("SELECT 1 FROM `building` WHERE $cond LIMIT 1")) return true;
	if ($priority == 0) return false;

	// also check plans
	$priocond = ($priority == -1)?"1":("`priority` < ".intval($priority));
	if (sqlgetone("SELECT 1 FROM `construction` WHERE $cond AND $priocond LIMIT 1")) return true;
	return false;
}

// distance from hq,silo,harbor...
// priority=-1 means all plans, new building-plan
// priority=0 means ignore plans, for a constructionstart (cron)
function GetBuildDistance ($x,$y,$userid=0,$priority=-1) { 
	global $gUser,$gBuildDistanceSources; 
	if ($userid == 0) $userid = $gUser->id;
	$x = intval($x);
	$y = intval($y);
	
	$distformula = "((`x`-$x)*(`x`-$x) + (`y`-$y)*(`y`-$y))";
	$cond = "`user` = ".intval($userid)." AND `type` IN (".implode(",",array_filter($gBuildDistanceSources)).")";
	$existing_dist = floatval(sqlgetone("SELECT MIN($distformula) FROM `building` WHERE `construction` = 0 AND $cond"));
	if ($priority == 0) return sqrt($existing_dist);
	
	$priocond = ($priority == -1)?"1":("`priority` < ".intval($priority));
	$plan_dist = sqlgetone("SELECT MIN($distformula) FROM `construction` WHERE $priocond AND $cond");
	if (!$plan_dist) return sqrt($existing_dist);
	return sqrt(min(floatval($plan_dist),$existing_dist));
}


// $dist=0 means directly adjacted, NOT DIAGONALLY
// $neartypes is array(btypeid,btypeid,...); ONE match is enough
// priority=-1 means all plans, new building-plan
// priority=0 means ignore plans, for a constructionstart (cron)
function GetNearBuilding ($x,$y,$userid,$dist,$neartypes,$priority=-1) {
	global $gBuildingType;
	$x = intval($x); $y = intval($y);
	$debug = false;
	if ($dist == 0)
			$condxy = "((`x`=$x AND (`y`=$y+1 OR `y`=$y-1)) OR (`y`=$y AND (`x`=$x+1 OR `x`=$x-1)))";
	else	$condxy = "`x` >= ".($x-$dist)." AND `x` <= ".($x+$dist)." AND `y` >= ".($y-$dist)." AND `y` <= ".($y+$dist);
	
	if ($debug) echo "GetNearBuilding($x,$y,$userid,$dist,(".implode(",",$neartypes)."),$priority)<br>\n";
	$res = sqlgetobject("SELECT * FROM `building` WHERE 
		`type` IN (".implode(",",$neartypes).") AND 
		`user` = ".intval($userid)." AND ".$condxy." LIMIT 1");
	if ($priority == 0 || $res) return $res;
	// consider plans
	$priocond = ($priority == -1)?"1":("`priority` < ".intval($priority));
	$res = sqlgetobject("SELECT * FROM `construction` WHERE 
		`type` IN (".implode(",",$neartypes).") AND 
		`user` = ".intval($userid)." AND ".$condxy." LIMIT 1");
	return $res;
}

// checks the build requirenments
// terrain_needed, terraintype->buildable
// excludes, need nears and required
// harbour hack (must be next to water)
// DOES NOT CHECK Build-cross, use InBuildCross
// DOES NOT CHECK TECH-reqs, use HasReq
// priority=-1 means all plans, new building-plan
// priority=0 means ignore plans, for a constructionstart (cron)
function CanBuildHere($x,$y,$buildingtypeid,$user=false,$priority=-1,$ignoreterrain=false) {
	global $gBuildingType,$gTerrainType,$gUser;
	if ($user === false) $user = $gUser;
	if (!is_object($user)) $user = sqlgetobject("SELECT * FROM `user` WHERE `id` = ".intval($user));
	$x = intval($x);
	$y = intval($y);
	$buildingtypeid = intval($buildingtypeid);
	$b = $gBuildingType[$buildingtypeid];
	assert(!empty($b));
	
	$debug = false;
	if ($debug) echo "CanBuildHere($x,$y,$buildingtypeid,$user->id,$priority)<br>\n";
	
	// players may not build special buildings
	if ($b->special > 0) if ($debug) echo "players may not build special buildings<br>\n";
	if ($b->special > 0) return false;
	
	// check terrain
	if (!$ignoreterrain) {
		$tid = cMap::StaticGetTerrainAtPos($x,$y);
		if (!$tid) $tid = kTerrain_Grass;
		if ($debug) echo "tid=$tid<br>\n";
		if ($b->terrain_needed > 0 && $b->terrain_needed != $tid) if ($debug) echo "terrain_needed $b->terrain_needed<br>\n";
		if ($b->terrain_needed > 0 && $b->terrain_needed != $tid) return false;
		if ($gTerrainType[$tid]->buildable == 0 && $b->terrain_needed != $tid) if ($debug) echo "terrain unbuildable<br>\n";
		if ($gTerrainType[$tid]->buildable == 0 && $b->terrain_needed != $tid) return false;
	}
	
	// check race and req
	if ($b->race > 0 && $user->race != $b->race) if ($debug) echo "race mismatch<br>\n";
	if ($b->race > 0 && $user->race != $b->race) return false;
	// if (!HasReq($b->req_geb,$b->req_tech,$user->id)) return false;
	
	// HACK : place all special conditions here =)
	switch ($b->id) {
		case kBuilding_Steg:
			if (sizeof($b->require_building) == 0) $b->require_building = array(0=>kBuilding_Harbor,kBuilding_Steg);
		break;
		case kBuilding_SeaWall:
		case kBuilding_SeaGate:
			if (sizeof($b->require_building) == 0) $b->require_building = array(0=>kBuilding_SeaWall,kBuilding_Wall);
		break;
		case kBuilding_Harbor:
			if (cMap::StaticGetTerrainAtPos($x,$y+1) != kTerrain_Sea &&
				cMap::StaticGetTerrainAtPos($x,$y-1) != kTerrain_Sea &&
				cMap::StaticGetTerrainAtPos($x+1,$y) != kTerrain_Sea &&
				cMap::StaticGetTerrainAtPos($x-1,$y) != kTerrain_Sea)
				return false;
		break;
	}
	
	if ($debug) echo "check needs..<br>\n";
	
	//check needs
	if (sizeof($b->exclude_building)>0 && 
		GetNearBuilding($x,$y,$user->id,kBuildingRequirenment_ExcludeRadius,$b->exclude_building,$priority)) return false;
	if (sizeof($b->neednear_building)>0 && 
		!GetNearBuilding($x,$y,$user->id,kBuildingRequirenment_NearRadius,$b->neednear_building,$priority)) return false;
	if (sizeof($b->require_building)>0 && 
		!GetNearBuilding($x,$y,$user->id,kBuildingRequirenment_NextToRadius,$b->require_building,$priority)) return false;
	
	return true;
}


function OwnConstructionInProcess ($x,$y) {
	global $gUser;
	// check ob schon ein eigener bau geplant ist wird
	return sqlgetone("SELECT 1 FROM `construction` WHERE 
		`x` = ".intval($x)." AND `y` = ".intval($y)." AND `user` = ".$gUser->id." LIMIT 1") == 1;
}



function GetBuildDistFactor ($dist) {
	if ($dist <= 4.0)
			return 1.0;
	else	return 1.0  + ($dist-4.0) * 0.1;
}

function GetBuildTechFactor ($userid) {
	if (is_object($userid)) $userid = $userid->id;
	$techlevel = ($userid != 0)?GetTechnologyLevel(kTech_Architecture,$userid):0;
	$tf = 1.0;
	for($i=0;$i<$techlevel;++$i) $tf *= 0.95; // todo : document in wiki
	return $tf;
}

// btypeid=-1 means any speedy building
// priority=-1 means all plans, new building-plan
// priority=0 means ignore plans, for a constructionstart (cron)
function GetBuildNewbeeFactor ($btypeid=-1,$priority=-1,$userid=false) {
	global $gSpeedyBuildingTypes,$gUser;
	if ($btypeid != -1 && !in_array($btypeid,$gSpeedyBuildingTypes)) return 1.0;
	if ($userid === false) $userid = $gUser->id;
	if (is_object($userid)) $userid = $userid->id;
	if (is_object($btypeid)) $btypeid = $btypeid->id;
	$cond = "`user`=".intval($userid)." AND `type` IN (".implode(",",$gSpeedyBuildingTypes).")";
	//echo "GetBuildNewbeeFactor($btypeid,$priority,$userid)<br>$cond<br>";
	$sbcount = intval(sqlgetone("SELECT count(*) FROM `building` WHERE `construction` = 0 AND $cond"));
	if ($priority == -1) // -1 means for a new plan -> take all existing plans into account 
		$sbcount += intval(sqlgetone("SELECT count(*) FROM `construction` WHERE $cond"));
	else if ($priority > 0)
		$sbcount += intval(sqlgetone("SELECT count(*) FROM `construction` WHERE `priority` < ".intval($priority)." AND $cond"));
	if ($sbcount <= kSpeedyBuildingsLimit)
			return $sbcount / (float)kSpeedyBuildingsLimit;
	else	return 1.0;
}
			
// priority=-1 means all plans, new building-plan
// priority=0 means ignore plans, for a constructionstart (cron)
// typeid=-1 means for a speedy building
function GetBuildTime ($x,$y,$typeid,$priority=-1,$userid=false) { // object(building or construction) or id
	global $gUser,$gBuildingType;
	if ($userid === false) $userid = $gUser->id;
	if (is_object($userid)) $userid = $userid->id;
	$dist = GetBuildDistance($x,$y,$userid,$priority);
	$faktor_dist = GetBuildDistFactor($dist);
	$faktor_tech = GetBuildTechFactor($userid);
	$faktor_newbee = GetBuildNewbeeFactor($typeid,$priority,$userid);
	return ((float)$gBuildingType[$typeid]->buildtime) * (float)$faktor_dist * (float)$faktor_tech * (float)$faktor_newbee;
}

// print an explanation for how GetBuildTime works
// priority=-1 means all plans, new building-plan
// priority=0 means ignore plans, for a constructionstart (cron)
// type=-1 means for a speedy building
function PrintBuildTimeHelp ($x,$y,$type=-1,$priority=-1,$userid=false) {
	global $gUser;
	if ($userid === false) $userid = $gUser->id;
	if (is_object($userid)) $userid = $userid->id;
	$dist = GetBuildDistance($x,$y,$userid,$priority);
	$faktor_dist = GetBuildDistFactor($dist);
	$faktor_tech = GetBuildTechFactor($userid);
	$faktor_newbee = GetBuildNewbeeFactor($type,$priority,$userid);
	?>
	<table>
	<tr>
		<td>Entfernung zum Haupthaus oder Lager <?=round($dist,2)?></td>
		<td>Bauzeit * <?=round($faktor_dist,2)?></td>
	</tr><tr>
		<td>Architekturlevel: <?=GetTechnologyLevel(kTech_Architecture,$userid)?></td>
		<td>Bauzeit * <?=round($faktor_tech,2)?></td>
	</tr><tr>
	<?php if ($faktor_newbee < 1.0) {?>	
		<td>frisch gegründete Siedlung (Newbee)</td>
		<td>Bauzeit * <?=round($faktor_newbee,2)?></td>
	</tr><tr>
	<?php } // endif?>
		<td>Insgesamt</td>
		<td>Bauzeit * <?=round($faktor_dist * $faktor_tech * $faktor_newbee,2)?></td>
	</tr><tr>
	</table>
	<?php
}


function CancelConstruction ($id,$user=false) {
	global $gUser;
	// param : construction id
	// checks if user owns this construction, if user is specified
	// cancel a construction, and correct the other construction priorities
	// returns canceled construction, for x,y read

	$con = sqlgetobject("SELECT * FROM `construction` WHERE `id` = ".$id);
	if (empty($con)) return false;
	if ($user && $con->user != $user) return false;
	sql("DELETE FROM `construction` WHERE `id` = ".intval($id));
	sql("UPDATE `construction` SET `priority` = `priority` - 1 WHERE 
		`priority` > ".$con->priority." AND `user` = ".$con->user);
	return $con;
}



//returns percent 0.0 - 1.0 of construction progress
function GetConstructionProgress($building) {
	if(!is_object($building)) $building = sqlgetobject("SELECT * FROM `building` WHERE `id`=".intval($building));
	if (empty($building)) return 0;
	$timeleft = max(0,$building->construction - time());
	$buildtime = max(1,GetBuildTime($building->x,$building->y,$building->type,0,$building->user)); // prevent div by zero
	return 1.0 - min(1.0,$timeleft / $buildtime);
}

// move construction to the front of the building-queue and adjust all priorities
//if userid > 0 then only userid's constructions can be moved otherwise all
function MoveContructionQueueFront($id,$userid=0){
	$con = sqlgetobject("SELECT * FROM `construction` WHERE `id` = ".intval($id)." LIMIT 1");
	if($con && ($userid == 0 || $userid == $con->user)) {
		sql("UPDATE `construction` SET `priority` = `priority`+1 WHERE `user`=".$con->user." AND `priority`<".$con->priority);
		sql("UPDATE `construction` SET `priority` = 1 WHERE `id`=".$con->id);
	}
}

?>
