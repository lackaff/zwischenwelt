<?php
define("kFastSession",true);
require_once("lib.main.php");
require_once("lib.map.php");
require_once("lib.army.php");
require_once("lib.unit.php");
require_once("lib.weather.php");
//Lock();
// outputs map data in json format for javascript parsin by custom maps

$minx = isset($f_minx) ? intval($f_minx) : 0;
$miny = isset($f_miny) ? intval($f_miny) : 0;
$maxx = isset($f_maxx) ? intval($f_maxx) : 0;
$maxy = isset($f_maxy) ? intval($f_maxy) : 0;

// http://zwischenwelt.org/mapdata_json.php?minx=0&miny=0&maxx=999&maxy=999&what=armypos
// sid=....

function json_encode_string($in_str) {
	mb_internal_encoding("UTF-8");
	$convmap = array(0x80, 0xFFFF, 0, 0xFFFF);
	$str = "";
	for($i=mb_strlen($in_str)-1; $i>=0; $i--)
	{
	  $mb_char = mb_substr($in_str, $i, 1);
	  if(mb_ereg("&#(\\d+);", mb_encode_numericentity($mb_char, $convmap, "UTF-8"), $match))
	  {
		$str = sprintf("\\u%04x", $match[1]) . $str;
	  }
	  else
	  {
		$str = $mb_char . $str;
	  }
	}
	return $str;
}

function php_json_encode($arr) {
	$json_str = "";
	if(is_array($arr))
	{
	  $pure_array = true;
	  $array_length = count($arr);
	  for($i=0;$i<$array_length;$i++)
	  {
		if(! isset($arr[$i]))
		{
		  $pure_array = false;
		  break;
		}
	  }
	  if($pure_array)
	  {
		$json_str ="[";
		$temp = array();
		for($i=0;$i<$array_length;$i++)       
		{
		  $temp[] = sprintf("%s", php_json_encode($arr[$i]));
		}
		$json_str .= implode(",",$temp);
		$json_str .="]";
	  }
	  else
	  {
		$json_str ="{";
		$temp = array();
		foreach($arr as $key => $value)
		{
		  $temp[] = sprintf("\"%s\":%s", $key, php_json_encode($value));
		}
		$json_str .= implode(",",$temp);
		$json_str .="}";
	  }
	}
	else
	{
	  if(is_string($arr))
	  {
		$json_str = "\"". json_encode_string($arr) . "\"";
	  }
	  else if(is_numeric($arr))
	  {
		$json_str = $arr;
	  }
	  else
	  {
		$json_str = "\"". json_encode_string($arr) . "\"";
	  }
	}
	return $json_str;
}


switch ($f_what) {
	case "armypos":		JSON_ArmyPos(	$minx,$miny,$maxx,$maxy); break;	// x={y=armyid}
	case "terrain":		JSON_Terrain(	$minx,$miny,$maxx,$maxy); break;	// terrain1={x,y,type},terrain4={x,y,type},terrain64={x,y,type}
	case "items":		JSON_Items(		$minx,$miny,$maxx,$maxy); break;	// id={x,y,type,amount}
	case "armyunit":	JSON_ArmyUnit(	$f_idlist); break;	// armyid={typ1=amount,typ2=amount}
	case "armyitem":	JSON_ArmyItem(	$f_idlist); break;	// armyid={typ1=amount,typ2=amount}
	case "armyinfo":	JSON_ArmyInfo(	$f_idlist); break;	// armyid={armyname="bla",owner=ownerid}
	case "userinfo":	JSON_UserInfo(	$f_idlist); break;	// userid={name="bla",guildid=123,fof="enemy"}
	case "guildinfo":	JSON_GuildInfo(	$f_idlist); break;	// guildid={name="bla"}
	default : echo "ERROR:no query"; break;
}

function MakeXYCond ($minx,$miny,$maxx,$maxy) {
	return 	     "`x` >= ".intval($minx).
			" AND `y` >= ".intval($miny).
			" AND `x` <= ".intval($maxx).
			" AND `y` <= ".intval($maxy);
}

function JSON_ArmyPos	($minx,$miny,$maxx,$maxy) {
	$mytable = sqlgettable("SELECT `x`,`y`,`id` FROM `army` WHERE ".MakeXYCond($minx,$miny,$maxx,$maxy));
	$res = array();
	foreach ($mytable as $o) {
		if (!isset($res[$o->x])) $res[$o->x] = array();
		$res[$o->x][$o->y] = $o->id;
	}
	echo php_json_encode($res);
}

function JSON_Items		($minx,$miny,$maxx,$maxy) {
	$mytable = sqlgettable("SELECT * FROM `item` WHERE `army` = 0 AND `building` = 0 AND ".MakeXYCond($minx,$miny,$maxx,$maxy));
	$res = array();
	foreach ($mytable as $o) $res[$o->id] = array($o->x,$o->y,$o->type,$o->amount);
	echo php_json_encode($res);
}


function JSON_TerrainPart	($mytable) {
	$res = array();
	foreach ($mytable as $o) {
		if (!isset($res[$o->x])) $res[$o->x] = array();
		$res[$o->x][$o->y] = $o->type;
	}
	return $res;
}

function JSON_Terrain	($minx,$miny,$maxx,$maxy) {
	$t1 = sqlgettable("SELECT `x`,`y`,`type` FROM `terrain` WHERE ".MakeXYCond($minx,$miny,$maxx,$maxy));
	$t4 = sqlgettable("SELECT `x`,`y`,`type` FROM `terrainsegment4` WHERE ".MakeXYCond(
		floor($minx/4),floor($miny/4),ceil($maxx/4)+1,ceil($maxy/4)+1));
	$t64 = sqlgettable("SELECT `x`,`y`,`type` FROM `terrainsegment64` WHERE ".MakeXYCond(
		floor($minx/64),floor($miny/64),ceil($maxx/64)+1,ceil($maxy/64)+1));
	$outarr = array("terrain1"=>JSON_TerrainPart($t1),"terrain4"=>JSON_TerrainPart($t4),"terrain64"=>JSON_TerrainPart($t64));
	echo php_json_encode($outarr);
}

function JSON_ArmyUnit	($idlist) { }
function JSON_ArmyItem	($idlist) { }
function JSON_ArmyInfo	($idlist) { }
function JSON_UserInfo	($idlist) { }
function JSON_GuildInfo	($idlist) { }


?>