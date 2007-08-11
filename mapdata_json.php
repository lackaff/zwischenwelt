<?php
define("kFastSession",true);
require_once("lib.main.php");
require_once("lib.map.php");
require_once("lib.army.php");
require_once("lib.unit.php");
require_once("lib.weather.php");
if (isset($f_sid)) Lock();
// outputs map data in json format for javascript parsin by custom maps

$minx = isset($f_minx) ? intval($f_minx) : 0;
$miny = isset($f_miny) ? intval($f_miny) : 0;
$maxx = isset($f_maxx) ? intval($f_maxx) : 0;
$maxy = isset($f_maxy) ? intval($f_maxy) : 0;
$idlist = isset($f_idlist) ? explode(",",$f_idlist) : array();

// http://zwischenwelt.org/mapdata_json.php?minx=0&miny=0&maxx=999&maxy=999&what=armypos
// http://zwischenwelt.org/mapdata_json.php?idlist=264881&what=armyunit
// see also http://zwischenwelt.org/mapjs7_globals.js.php
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



$whatlist = explode(",",$f_what);
$res = array();
$res['meta']['now']=time();
foreach ($whatlist as $what) switch ($what) {
	case "building":	$res[$what] = MapData_Building(	$minx,$miny,$maxx,$maxy); break;	// x={y={id,type,user,level,hp,mana}}
	case "armypos":		$res[$what] = MapData_ArmyPos(	$minx,$miny,$maxx,$maxy); break;	// x={y=armyid}
	case "terrain":		$res[$what] = MapData_Terrain(	$minx,$miny,$maxx,$maxy); break;	// terrain1={x,y,type},terrain4={x,y,type},terrain64={x,y,type}
	case "items":		$res[$what] = MapData_Items(	$minx,$miny,$maxx,$maxy); break;	// id={x,y,type,amount}
	case "armywp":		$res[$what] = MapData_ArmyWP(	$idlist); break;	// armyid={id1={x1,y1},id2=..}
	case "armyunit":	$res[$what] = MapData_ArmyUnit(	$idlist); break;	// armyid={id1={type1,amount1},id2={type2,amount2}}
	case "armyitem":	$res[$what] = MapData_ArmyItem(	$idlist); break;	// armyid={id1={type1,amount1},id2={type2,amount2}}
	case "armyinfo":	$res[$what] = MapData_ArmyInfo(	$idlist); break;	// armyid={armyname="bla",user=ownerid,...}
	case "userinfo":	$res[$what] = MapData_UserInfo(	$idlist); break;	// userid={name="bla",guildid=123,fof="enemy"}
	case "guildinfo":	$res[$what] = MapData_GuildInfo($idlist); break;	// guildid={name="bla"}
	//default : $res[$what] = "ERROR:no query"; break;
}
echo php_json_encode($res);

// todo : armywp

function MakeXYCond ($minx,$miny,$maxx,$maxy) {
	return 	     "`x` >= ".intval($minx).
			" AND `y` >= ".intval($miny).
			" AND `x` <= ".intval($maxx).
			" AND `y` <= ".intval($maxy);
}

function MakeIDListCond ($idlist,$fieldname="id",$bSkipZero=true) {
	$mylist = array();
	foreach ($idlist as $id) {
		if (intval($id) == 0 && $bSkipZero) continue;
		$mylist[] = intval($id);
	}
	if (empty($mylist)) return "0";
	return "`".$fieldname."` IN (".implode(",",$mylist).")";
}

function MapData_Building	($minx,$miny,$maxx,$maxy) {
	$mytable = sqlgettable("SELECT * FROM `building` WHERE ".MakeXYCond($minx,$miny,$maxx,$maxy));
	// x={y={id,type,user,level,hp,mana}}
	$res = array();
	foreach ($mytable as $o) {
		if (!isset($res[$o->x])) $res[$o->x] = array();
		$res[$o->x][$o->y] = array($o->id,$o->type,$o->user,$o->level,$o->hp,$o->mana);
	}
	return $res;
}

function MapData_ArmyPos	($minx,$miny,$maxx,$maxy) {
	$mytable = sqlgettable("SELECT `x`,`y`,`id` FROM `army` WHERE ".MakeXYCond($minx,$miny,$maxx,$maxy));
	$res = array();
	foreach ($mytable as $o) {
		if (!isset($res[$o->x])) $res[$o->x] = array();
		$res[$o->x][$o->y] = $o->id;
	}
	return $res;
}

function MapData_Items		($minx,$miny,$maxx,$maxy) {
	$mytable = sqlgettable("SELECT * FROM `item` WHERE `army` = 0 AND `building` = 0 AND ".MakeXYCond($minx,$miny,$maxx,$maxy));
	$res = array();
	foreach ($mytable as $o) $res[$o->id] = array($o->x,$o->y,$o->type,$o->amount);
	return $res;
}

function MapData_TerrainPart	($mytable) {
	$res = array();
	foreach ($mytable as $o) {
		if (!isset($res[$o->x])) $res[$o->x] = array();
		$res[$o->x][$o->y] = $o->type;
	}
	return $res;
}

function MapData_Terrain	($minx,$miny,$maxx,$maxy) {
	$t1 = sqlgettable("SELECT `x`,`y`,`type` FROM `terrain` WHERE ".MakeXYCond($minx,$miny,$maxx,$maxy));
	$t4 = sqlgettable("SELECT `x`,`y`,`type` FROM `terrainsegment4` WHERE ".MakeXYCond(
		floor($minx/4),floor($miny/4),ceil($maxx/4)+1,ceil($maxy/4)+1));
	$t64 = sqlgettable("SELECT `x`,`y`,`type` FROM `terrainsegment64` WHERE ".MakeXYCond(
		floor($minx/64),floor($miny/64),ceil($maxx/64)+1,ceil($maxy/64)+1));
	$res = array("terrain1"=>MapData_TerrainPart($t1),"terrain4"=>MapData_TerrainPart($t4),"terrain64"=>MapData_TerrainPart($t64));
	return $res;
}

function MapData_ArmyUnit	($idlist) {
	$res = array();
	foreach ($idlist as $id) {
		$mytable = sqlgettable("SELECT * FROM `unit` WHERE `army` = ".intval($id));
		$mylist = array();
		foreach ($mytable as $o) $mylist[$o->id] = array($o->type,$o->amount);
		$res[intval($id)] = $mylist;
	}
	return $res;
}

function MapData_ArmyItem	($idlist) {
	$res = array();
	foreach ($idlist as $id) {
		$mytable = sqlgettable("SELECT * FROM `item` WHERE `army` = ".intval($id));
		$mylist = array();
		foreach ($mytable as $o) $mylist[$o->id] = array($o->type,$o->amount);
		$res[intval($id)] = $mylist;
	}
	return $res;
}

function MapData_ArmyInfo	($idlist) { 
	$mytable = sqlgettable("SELECT * FROM `army` WHERE ".MakeIDListCond($idlist));
	$res = array();
	foreach ($mytable as $o) $res[$o->id] = array(	
		"name"=>$o->name,
		"user"=>$o->user,
		"lumber"=>$o->lumber,
		"stone"=>$o->stone,
		"food"=>$o->food,
		"metal"=>$o->metal,
		"runes"=>$o->runes,
		"type"=>$o->type);
	return $res;
}

function MapData_ArmyWP	($idlist) { 
	global $gUser;
	$res = array();
	foreach ($idlist as $id) {
		if (!isset($gUser) || !$gUser) continue;
		$army = sqlgetone("SELECT * FROM `army` WHERE `id` = ".intval($id));
		if (!cArmy::CanControllArmy($army,$gUser)) continue;
		$mytable = sqlgettable("SELECT * FROM `waypoint` WHERE `army` = ".intval($id)." ORDER BY `priority`");
		$mylist = array();
		foreach ($mytable as $o) $mylist[$o->id] = array($o->x,$o->y);
		$res[intval($id)] = $mylist;
	}
	return $res;
}

function MapData_UserInfo	($idlist) { 
	global $gUser;
	$mytable = sqlgettable("SELECT * FROM `user` WHERE ".MakeIDListCond($idlist));
	$res = array();
	foreach ($mytable as $o)  {
		$fof = (isset($gUser) && $gUser) ? GetFOF($gUser->id,$o->id) : 0;
		$res[$o->id] = array("name"=>$o->name,"guild"=>$o->guild , "fof"=>$fof );
	}
	return $res;
}

function MapData_GuildInfo	($idlist) { 
	$mytable = sqlgettable("SELECT * FROM `guild` WHERE ".MakeIDListCond($idlist));
	$res = array();
	foreach ($mytable as $o) $res[$o->id] = array( "name"=>$o->name );
	return $res;
}

?>