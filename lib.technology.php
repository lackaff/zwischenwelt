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
	if (empty($req)) return array();
	if (!$req || $req == "") return array();
	// OLD SYNTAX, STILL SUPPORTED : $req is the requirement text from technologytype like "4:5,33:5" for id 4 at least level 5
	// NEW SYNTAX : type>minlevel+inc  OR  type<maxlevel+inc   inc can be float : "4>5+0.5"  for id 4 at least level 5
	$arr = explode(",",$req);
	$res = array();
	//echo $req."<br>";
	foreach ($arr as $element) {
		if ($element == "") continue;
		if (eregi("([0-9]+)([<>:])(-?[0-9]+)(\\+([0-9.]+))?",$element,$r)) {
			$newo = false;
			$newo->type = intval($r[1]);
			$newo->level = abs(intval($r[3])); // WARNING ! SIGN LOST FOR MAX = 0 !!!
			$newo->ismax = ($r[2]=="<" || intval($r[3]) < 0)?1:0;
			$newo->inc = isset($r[5])?floatval($r[5]):0;
			//vardump2($newo);
			$res[$newo->type] = $newo;
		} else warning("SKIPPING UNKNOWN TECH SYNTAX '".addslashes($element)."'");
	}
	return $res;
}
//ParseReq("4:5,33:-5,"."4>5+0.5,"."4<5+0.5,"."4<5+5");

function SetTechnologyUpgrades($typeid,$buildingid,$num) {
	global $gTechnologyType;
	$techtype =  $gTechnologyType[$typeid];
	if (!$techtype) return;

	$building = sqlgetobject("SELECT * FROM `building` WHERE `id` = ".intval($buildingid));
	if (!$building) return;

	if (intval($building->level) < intval($techtype->buildinglevel)) return;
	if (!HasReq($techtype->req_geb,$techtype->req_tech)) return;

	$tech = GetTechnologyObject($techtype->id);
	if ($tech->upgradebuilding && $tech->upgrades > 0 && $tech->upgradebuilding != $building->id) return;

	$num = max(($tech->upgradetime == 0)?0:1,min($techtype->maxlevel-$tech->level,intval($num)));
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
function HasReq ($req_geb,$req_tech,$userid=0,$inclevel=0) {
	global $gUser;
	if ($userid == 0) $userid = $gUser->id;
	$req_tech = (is_string($req_tech) && !empty($req_tech)) ? ParseReq($req_tech) : array();
	$req_geb = (is_string($req_geb) && !empty($req_geb)) ? ParseReq($req_geb) : array();

	// check technologies
	foreach ($req_tech as $type => $o)
		if (($o->ismax == 0 && GetTechnologyLevel($type,$userid) < floor(abs($o->level) + $o->inc*$inclevel)) ||
			($o->ismax != 0 && GetTechnologyLevel($type,$userid) > floor(abs($o->level) + $o->inc*$inclevel)) )
			return false;
	
	// check buildings
	foreach ($req_geb as $type => $o)
		if (($o->ismax == 0 && GetMaxBuildingLevel($type,$userid) < floor(abs($o->level) + $o->inc*$inclevel)) ||
			($o->ismax != 0 && GetMaxBuildingLevel($type,$userid) > floor(abs($o->level) + $o->inc*$inclevel)) )
			return false;
	return true;
}
?>
