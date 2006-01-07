<?php

require_once("lib.technology.php");



// distance from hq,silo,harbor...
// priority=-1 means new building/plan
// priority=0 means for a construction
function GetBuildDistance ($x,$y,$userid=0,$priority=-1) { 
	global $gUser,$gBuildDistanceSources; 
	if ($userid == 0) $userid = $gUser->id;
	$x = intval($x);
	$y = intval($y);
	
	$distformula = "((`x`-$x)*(`x`-$x) + (`y`-$y)*(`y`-$y))";
	$cond = "`user` = ".intval($userid)." AND `type` IN (".implode(",",$gBuildDistanceSources).")";
	$existing_dist = floatval(sqlgetone("SELECT MIN($distformula) FROM `building` WHERE `construction` = 0 AND $cond"));
	if ($priority == 0) return sqrt($existing_dist);
	
	$priocond = ($priority == -1)?"1":("`priority` < ".intval($priority));
	$plan_dist = sqlgetone("SELECT MIN($distformula) FROM `construction` WHERE $priocond AND $cond");
	if (!$plan_dist) return sqrt($existing_dist);
	return sqrt(min(floatval($plan_dist),$existing_dist));
}


// check if other building in 2x2 cross
function InBuildCross ($x,$y,$user,$priority=false) {
	$x = intval($x);
	$y = intval($y);

	// allow building of first building anywhere
	if (!UserHasBuilding($user,kBuilding_HQ))
		return true;

	// must be within 2 fields of own building, or building plan
	if (sqlgetone("SELECT 1 FROM `building` WHERE 
		`x` >= ".($x-2)." AND `x` <= ".($x+2)." AND 
		`y` >= ".($y-2)." AND `y` <= ".($y+2)." AND `user` = ".$user." LIMIT 1"))
		return true;
	if ($priority == -1) return false;

	// if $priority == false, set to max priority
	if (!$priority)
		$priority = intval(sqlgetone("SELECT MAX(`priority`) FROM `construction` WHERE `user` = ".$user)) + 1;

	// check constructions with smaller(build first) priority !
	if (sqlgetone("SELECT 1 FROM `construction` WHERE 
		`x` >= ".($x-2)." AND `x` <= ".($x+2)." AND 
		`y` >= ".($y-2)." AND `y` <= ".($y+2)." AND `user` = ".$user." AND `priority` < ".intval($priority)." LIMIT 1"))
		return true;
		
	return false;
}

//checks the build requirenments
//excludes, need nears and required
// todo : add param : priority, and conside buildingplans with lower priority,
// todo : this is neccessary in order to check build-plans
function CanBuildHere($x,$y,$buildingtypeid){
	$x = intval($x);$y = intval($y);$buildingtypeid = intval($buildingtypeid);
	global $gBuildingType;
	$debug = false;
	$b = $gBuildingType[$buildingtypeid];
	assert(!empty($b));
	
	if($debug)echo "CanBuildHere($x,$y,$buildingtypeid)<br>\n";
	//print_r($b);
	
	//check excludes
	if(sizeof($b->exclude_building)>0){
		if($debug)echo "check for excludes<br>\n";
		$l = "(".implode($b->exclude_building,",").")";
		if($debug)echo "SELECT COUNT(*) FROM `building` WHERE `type` IN $l AND ((`x`=($x) AND ABS(`y`-($y))=1) OR (`y`=($y) AND ABS(`x`-($x))=1))<br>\n";
		$c = sqlgetone("SELECT COUNT(*) FROM `building` WHERE `type` IN $l AND ((`x`=($x) AND ABS(`y`-($y))=1) OR (`y`=($y) AND ABS(`x`-($x))=1))");
		if($debug)echo "$c excludes found<br>\n";
		if($c > 0)return false;
	} else if($debug)echo "no excludes<br>\n";
	
	//check needs
	if(sizeof($b->neednear_building)>0){
		if($debug)echo "check for need nears<br>\n";
		$l = "(".implode($b->neednear_building,",").")";
		if($debug)echo "SELECT COUNT(*) FROM `building` WHERE `type` IN $l AND ABS(`x`-($x))<=".kBuildingRequirenment_NearRadius." AND ABS(`y`-($y))<=".kBuildingRequirenment_NearRadius." AND (`x`<>$x OR `y`<>$y)<br>\n";
		$c = sqlgetone("SELECT COUNT(*) FROM `building` WHERE `type` IN $l AND ABS(`x`-($x))<=".kBuildingRequirenment_NearRadius." AND ABS(`y`-($y))<=".kBuildingRequirenment_NearRadius." AND (`x`<>$x OR `y`<>$y)");
		if($debug)echo "$c need nears found<br>\n";
		if($c == 0)return false;
	} else if($debug)echo "no need nears<br>\n";
	
	//check requirements
	if(sizeof($b->require_building)>0){
		if($debug)echo "check for requirements<br>\n";
		$l = "(".implode($b->require_building,",").")";
		if($debug)echo "SELECT COUNT(*) FROM `building` WHERE `type` IN $l AND ((`x`=($x) AND ABS(`y`-($y))=1) OR (`y`=($y) AND ABS(`x`-($x))=1))<br>\n";
		$c = sqlgetone("SELECT COUNT(*) FROM `building` WHERE `type` IN $l AND ((`x`=($x) AND ABS(`y`-($y))=1) OR (`y`=($y) AND ABS(`x`-($x))=1))");
		if($debug)echo "$c requirements found<br>\n";
		if($c == 0)return false;
	} else if($debug)echo "no requirements<br>\n";
	
	return true;
}

function OwnConstructionInProcess ($x,$y) {
	global $gUser;
	// check ob schon ein bau geplant ist wird
	// verhindern das 2 baupläne auf dem selben feld entstehen
	if (sqlgetone("SELECT 1 FROM `construction` WHERE 
		`x` = ".intval($x)." AND `y` = ".intval($y)." AND `user` = ".$gUser->id." LIMIT 1")) 
		return true;
	return false;
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
// priority=-1 means new building/plan
// priority=0 means for a construction
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
			
// priority=-1 means for a new building
// priority=0 means for a construction
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
// priority=-1 means for a new building/plan
// priority=0 means for a construction
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

function GetBuildlist ($x,$y,$unsafe=FALSE,$withhq=TRUE,$ignorereq=TRUE,$ignoreterrain=FALSE) {
	global $gUser,$gBuildingType,$gTerrainType;
	if(!$unsafe && !inBuildCross($x,$y,$gUser->id))
		return array();
	$x=intval($x);
	$y=intval($y);
	$tid = sqlgetone("SELECT `type` FROM `terrain` WHERE `x`=(".intval($x).") AND `y`=(".intval($y).")");
	if(empty($tid))$tid = kTerrain_Grass;
	$r = array();
	foreach($gBuildingType as $o){
		if(!$ignoreterrain && $o->terrain_needed > 0 && $o->terrain_needed != $tid)continue;
		if(!$ignoreterrain && $gTerrainType[$tid]->buildable == 0 && $o->terrain_needed != $tid)continue;
		//echo "[$o->id $o->race $gUser->race]";
		if($o->race > 0 && $gUser->race != $o->race)continue;
		
		//skip the hq?
		if(!$withhq && $o->id==kBuilding_HQ) continue;
//		else if($o->id==kBuilding_HQ && !isPositionInBuildableRange($x,$y))continue;
		
		if($o->special>0)continue;
		if(!$ignorereq && !HasReq($o->req_geb,$o->req_tech,$gUser->id))continue;
		
		if(!$ignoreterrain)
			switch($o->id){
				case kBuilding_Steg:
					if(!sqlgetobject("SELECT 1 FROM `building` WHERE (`type`=".kBuilding_Harbor." OR `type`=".kBuilding_Steg.") 
					AND ((`x`=$x AND `y`=$y+1) OR (`x`=$x AND `y`=$y-1) OR (`x`=$x+1 AND `y`=$y) OR (`x`=$x-1 AND `y`=$y)) LIMIT 1"))		
						continue;
				break;
				case kBuilding_SeaWall:
					if(!sqlgetobject("SELECT 1 FROM `building` WHERE (`type`=".kBuilding_SeaWall." OR `type`=".kBuilding_Wall.") 
					AND ((`x`=$x AND `y`=$y+1) OR (`x`=$x AND `y`=$y-1) OR (`x`=$x+1 AND `y`=$y) OR (`x`=$x-1 AND `y`=$y)) LIMIT 1"))
						continue;
				break;
				case kBuilding_SeaGate:
				if(!sqlgetobject("SELECT 1 FROM `building` WHERE (`type`=".kBuilding_SeaWall." OR `type`=".kBuilding_Wall.") 
					AND ((`x`=$x AND `y`=$y+1) OR (`x`=$x AND `y`=$y-1) OR (`x`=$x+1 AND `y`=$y) OR (`x`=$x-1 AND `y`=$y)) LIMIT 1"))
						continue;
				break;
				case kBuilding_Harbor:
					if(!sqlgetobject("SELECT 1 FROM `terrain` WHERE (`type`=".kTerrain_Sea.") 
					AND ((`x`=$x AND `y`=$y+1) OR (`x`=$x AND `y`=$y-1) OR (`x`=$x+1 AND `y`=$y) OR (`x`=$x-1 AND `y`=$y)) LIMIT 1"))
						continue;
				break;
			}
		
		$r[$o->id]=$o->id;
		//if(HasReq($o->req_geb,$o->req_tech,$gUser->id) && $o->special==0)
		//	$r[$o->id]=$o->id;
	}
	return $r;
}



function CancelConstruction ($id,$user=false) {
	global $gUser;
	// param : construction id
	// checks if user owns this construction, if user is specified
	// cancel a construction, and correct the other construction priorities
	// returns canceled construction, for x,y read

	$con = sqlgetobject("SELECT * FROM `construction` WHERE `id` = ".$id);
	if (!$con) return false;
	if ($user && $con->user != $user) return false;
	sql("DELETE FROM `construction` WHERE `id` = ".intval($id));
	sql("UPDATE `construction` SET `priority` = `priority` - 1 WHERE 
		`priority` > ".$con->priority." AND `user` = ".$con->user);
	return $con;
}


function BuildNext ($id,$userid=false) {
	global $gUser;
	// param : construction id
	// checks if user owns this construction, if user is specified
	// cancel a construction, and correct the other construction priorities
	// returns canceled construction, for x,y read

	$con = sqlgetobject("SELECT * FROM `construction` WHERE `id` = ".$id." LIMIT 1");
	if (!$con) return false;
	if ($userid && $con->user != $userid) return false;

	sql("UPDATE `construction` SET `priority` = `priority`+1 WHERE `user`=".$con->user." AND `priority`<".$con->priority);
	sql("UPDATE `construction` SET `priority` = 1 WHERE `id`=".$con->id);
}


function &TableToXYIndex($table) {
	$t = array();
	foreach($table as $x)
		$t[$x->x][$x->y] =& $x;
	return $t;
}


function getBridgeParam($x,$y,$t=null) {
	$x = intval($x);
	$y = intval($y);
	if($t == null)$t = TableToXYIndex(sqlgettable("SELECT * FROM `terrain` WHERE ABS(`x`-($x)) <= 2 AND ABS(`y`-($y)) <= 2"));
	for ($mx=$x-1;$mx<=$x+1;$mx++)
	for ($my=$y-1;$my<=$y+1;$my++)
		if (!isset($t[$mx][$my])) $t[$mx][$my]->type = kTerrain_Grass;

	//ist das ein gerade flußstück?
	if (	$t[$x+1][$y]->type == kTerrain_River && 
			$t[$x-1][$y]->type == kTerrain_River && 
			$t[$x][$y+1]->type != kTerrain_River && 
			$t[$x][$y-1]->type != kTerrain_River)
		return "ns";
	else if($t[$x+1][$y]->type != kTerrain_River && 
			$t[$x-1][$y]->type != kTerrain_River && 
			$t[$x][$y+1]->type == kTerrain_River && 
			$t[$x][$y-1]->type == kTerrain_River)
		return "we";
	else return "";
}




//returns percent 0.0 - 1.0 of construction progress
function GetConstructionProgress($building) {
	if(!is_object($building)) $building = sqlgetobject("SELECT * FROM `building` WHERE `id`=".intval($building));
	if (!$building) return 0;
	$timeleft = max(0,$building->construction - time());
	$buildtime = max(1,GetBuildTime($building->x,$building->y,$building->type,0,$building->user)); // prevent div by zero
	return 1.0 - min(1.0,$timeleft / $buildtime);
}


?>
