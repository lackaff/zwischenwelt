<?php
/*
lib.main.php has these two important functions :
GetTechnologyLevel ($typeid,$userid=0)  
UserHasBuilding ($user,$type,$level=0)

techtype : maxlevel 
techcost(type,level) -> geldbeutelsystem
cText::printcost($cost,$user=0) : darstellung mit items und gruen/rot fuer user kann sichs leisten oder nicht.

tables using the tech system :
array(	"buildingtype"=>"req_geb,req_tech",
		"spelltype"=>"req_tech,req_building",
		"technologytype"=>"req_tech,req_geb",
		"unittype"=>"req_tech_a,req_tech_v,req_geb" );

WARNING ! the group field in technology group is now useless.
*/

// * <b>GetMaxBuildingLevel($typeid,$userid=0); // returns the highest level of one buildingtype the user has, -1 if not build, 0 is the initial level
function GetMaxBuildingLevel ($typeid,$userid=0) {
	global $gUser;
	if ($userid == 0) $userid = $gUser->id;
	$r = sqlgetone("SELECT MAX(`level`) FROM `building` WHERE `user` = ".intval($userid)." AND `type` = ".intval($typeid));
	if ($r === false) return -1;
	return intval($r);	
}

class cTechnology {
	function GetUpgradeDuration ($typeid,$level) {
		global $gTechnologyType;
		return $gTechnologyType[$typeid]->basetime * cTechnology::GetUpgradeMod($typeid,$level);
	}
	function GetUpgradeMod ($typeid,$level) {
		global $gTechnologyType;
		return intval($level)*$gTechnologyType[$typeid]->increment + 1.0;
	}
}

function ParseReq ($req) {
	$level = 0;
	if (empty($req)) return array();
	if (!$req || $req == "") return array();
	// OLD SYNTAX, STILL SUPPORTED : $req is the requirement text from technologytype like "4:5,33:5" for id 4 at least level 5
	// NEW SYNTAX : type>minlevel+inc  OR  type<maxlevel+inc   inc can be float : "4>5+0.5"  for id 4 at least level 5
	// SYNTAX EXTENSION : a number in [] means, that everything on right of this number is only relevant for the requirements 
	//                    if the level of the building/technology is greater or equal to this value
	//                    this extends the requirenments, the reqs for the lower level are still needed
	//										this extension is only used for techs, dont use this for units bonus dmg for overteching
	//										low levels need to be left of the higher ones
	//										use the tech extension ONLY for tech dependencies (no unit/building deps)
	$arr = explode(",",$req);
	$res = array();
	//echo $req."<br>";
	foreach ($arr as $element) {
		$element = trim($element);
		if ($element == "") continue;
		if (eregi('\[([0-9]+)\]',$element,$r)){
			$level = $r[1];
			//print_r($r);
			//echo "[element=$element level=$level]";
		} else if (eregi("([0-9]+)([<>:])(-?[0-9]+)(\\+([0-9.]+))?",$element,$r)) {
			$newo = false;
			$newo->type = intval($r[1]);
			$newo->level = abs(intval($r[3])); // WARNING ! SIGN LOST FOR MAX = 0 !!!
			$newo->ismax = ($r[2]=="<" || intval($r[3]) < 0)?1:0;
			//echo "[ismax=$newo->ismax at level=$level by $element]<br>";
			$newo->inc = isset($r[5])?floatval($r[5]):0;
			//vardump2($newo);
			$res[$level][$newo->type] = $newo;
		} else warning("SKIPPING UNKNOWN TECH SYNTAX '".addslashes($element)."'");
	}
	return $res;
}

//returns a list of all levels that have different requirenments
//default: return array(0), ie. return array(0,10,50)
function ParseReqLevels($req){
	$arr = explode(",",$req);
	$level = array(0);
	foreach ($arr as $element) {
		$element = trim($element);
		if (empty($element)) continue;
		if (eregi('\[([0-9]+)\]',$element,$r))$level[] = $r[1];
	}
	
	return $level;
}

//returns all requirenments for a specific level of a technology
//returns same format as old parsereq (no level layer in the array)
function ParseReqForATechLevel($req,$level=0){
	$arr = explode(",",$req);
	$res = array();
	foreach ($arr as $element) {
		$element = trim($element);
		if (empty($element)) continue;
		if (eregi('\[([0-9]+)\]',$element,$r)){if($r[1]>$level)break;}
		else $res[] = $element;
	}
	
	$r = ParseReq(implode(",",$res));
	if(sizeof($r)>0)return $r[0];
	else return array();
}

//$r = ParseReq("[15],22<15,23<15,24<15");
//$r = ParseReq("4:5,33:-5,4>5+0.5,[10],4<5+0.5,4<5+5");
//print_r($r);
/*
Array
(
    [0] => Array
        (
            [4] => stdClass Object
                (
                    [type] => 4
                    [level] => 5
                    [ismax] => 0
                    [inc] => 0.5
                )

            [33] => stdClass Object
                (
                    [type] => 33
                    [level] => 5
                    [ismax] => 1
                    [inc] => 0
                )

        )

    [10] => Array
        (
            [4] => stdClass Object
                (
                    [type] => 4
                    [level] => 5
                    [ismax] => 1
                    [inc] => 5
                )

        )

)HasReq
*/

function SetTechnologyUpgrades($typeid,$buildingid,$num) {
	global $gTechnologyType;
	echo " debuggin SetTechnologyUpgrades(typeid=$typeid,buildingid=$buildingid,num=$num)<br>";
	$techtype =  $gTechnologyType[$typeid];
	if (!$techtype) { echo "no techtype id $typeid<br>"; return; }
	$tech = GetTechnologyObject($techtype->id);
	if ($tech->upgradetime > 0) $buildingid = $tech->upgradebuilding;

	$building = sqlgetobject("SELECT * FROM `building` WHERE `id` = ".intval($buildingid));
	if (!$building) { echo "no building id $buildingid<br>"; return; }

	if (intval($building->level) < intval($techtype->buildinglevel))  
		{ echo "$building->level < $techtype->buildinglevel <br>"; return; }
	if (!HasReq($techtype->req_geb,$techtype->req_tech,$building->user,$tech->level+1)) 
		{ echo "not HasReq($techtype->req_geb,$techtype->req_tech,$building->user,$tech->level+1)<br>"; return;}

	// if ($tech->upgradetime && $tech->upgrades > 0 && $tech->upgradebuilding != $building->id) return;
	
	$num = max(($tech->upgradetime == 0)?0:1,min($techtype->maxlevel-$tech->level,intval($num)));
	echo " set num=$num = max(($tech->upgradetime == 0)?0:1,min($techtype->maxlevel-$tech->level,intval($num)))<br>";
	//$num = max(($tech->upgradetime == 0)?0:1,intval($num)); // maxlevel limit aus ?
	if ($num == 0) $buildingid = 0;
	sql("UPDATE `technology` SET `upgrades` = ".$num." , `upgradebuilding` = ".intval($buildingid)." WHERE `id` = ".$tech->id);
}


function GetTechnologyObject ($typeid,$userid=0) {
	global $gUser;
	if ($userid == 0) $userid = $gUser->id;
	$o = sqlgetobject("SELECT * FROM `technology` WHERE `user` = ".intval($userid)." AND `type` = ".intval($typeid)." LIMIT 1");
	if ($o) return $o;

	$o->user = $userid;
	$o->type = $typeid;
	$o->level = 0;
	$o->upgrades = 0;
	$o->upgradetime = 0;
	$o->upgradebuilding = 0;
	sql("INSERT INTO `technology` SET ".obj2sql($o));
	$o->id = mysql_insert_id();

	return $o;
}




// does a user fullfill some requirements ?
// $req_tech,$req_geb are arrays, output from ParseReq, or the strings from technologytype
// $techlevel is the level of the technology/building to check reqs for (ie. 15 if you have level 14)
function HasReq ($req_geb,$req_tech,$userid=0,$techlevel=255) {
	global $gUser;
	if ($userid == 0) $userid = $gUser->id;
	$req_tech = (is_string($req_tech) && !empty($req_tech)) ? ParseReqForATechLevel($req_tech, $techlevel) : array();
	$req_geb = (is_string($req_geb) && !empty($req_geb)) ? ParseReqForATechLevel($req_geb, $techlevel) : array();

	// check technologies
	if(sizeof($req_tech)>0)foreach ($req_tech as $type => $o)
	if (($o->ismax == 0 && GetTechnologyLevel($type,$userid) < abs($o->level)) ||
		($o->ismax != 0 && GetTechnologyLevel($type,$userid) > abs($o->level)) ){
		return false;
	}
	
	// check buildings
	if(sizeof($req_geb)>0)foreach ($req_geb as $type => $o){
		if (($o->ismax == 0 && GetMaxBuildingLevel($type,$userid) < abs($o->level)) ||
			($o->ismax != 0 && GetMaxBuildingLevel($type,$userid) > abs($o->level)) ){
			return false;
		}
	}
	
	return true;
}
?>
