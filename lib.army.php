<?php

// todo : reduce these includes ??
require_once("lib.main.php");
require_once("lib.guild.php");
require_once("lib.technology.php");
require_once("lib.message.php");
require_once("lib.quest.php");
require_once("lib.item.php");
require_once("lib.unit.php");

class cArmy {
	function GetJavaScriptArmyData ($army) {
		if (!is_object($army)) $army = sqlgetobject("SELECT * FROM `army` WHERE `id` = ".intval($army));
		global $gRes2ItemType,$gRes;
		$units = cUnit::GetUnits($army->id);
		$army->unitstxt = ""; 
		foreach ($units as $u) $army->unitstxt .= $u->type.":".floor($u->amount)."|";
		$army->itemstxt = "";
		$items = sqlgettable("SELECT * FROM `item` WHERE `army` = ".$army->id);
		foreach ($items as $u) $army->itemstxt .= $u->type.":".floor($u->amount)."|";
		foreach ($gRes as $n=>$f) if ($army->$f >= 1) $army->itemstxt .= $gRes2ItemType[$f].":".floor($army->$f)."|";
		$o->flags = 0;// TODO : subset for walking, fighting, shooting...
		return obj2jsparams($army,"id,x,y,name,type,user,unitstxt,itemstxt,flags");
	}
	function GetJavaScriptWPs ($armyid,$gLeft=false,$gTop=false,$gCX=false,$gCY=false) {
		if (is_object($armyid)) $armyid = $armyid->id;
		$wps = sqlgettable("SELECT * FROM `waypoint` WHERE `army` = ".intval($armyid)." ORDER BY `priority`");
		$res = "";
		// foreach connection between 2 waypoints
		$curvisible = false;
		for ($i=0,$imax=count($wps);$i<$imax-1;$i++) {
			$x1 = $wps[$i]->x;
			$y1 = $wps[$i]->y;
			$x2 = $wps[$i+1]->x;
			$y2 = $wps[$i+1]->y;
			$lastvisible = $curvisible;
			$curvisible = false;
			// filter out if connection is not visible
			if ($gLeft !== false) {
				if (max($x1,$x2) < $gLeft)			continue;
				if (min($x1,$x2) >= $gLeft+$gCX)	continue;
				if (max($y1,$y2) < $gTop)			continue;
				if (min($y1,$y2) >= $gTop+$gCY)		continue;
			}
			$curvisible = true;
			if (!$lastvisible) $res .= ";$x1,$y1;";
			$res .= "$x2,$y2;";
		}
		return $res;
	}
	
	
	function CanCreateNewArmy ($userid,$armytype) {
		global $gArmyType;
		if ($gArmyType[$armytype]->limit < 0) return true;
		$count = intval(sqlgetone("SELECT COUNT(*) FROM `army` WHERE `counttolimit` > 0 AND `user` = ".intval($userid)." AND `type` = ".intval($armytype)));
		return $count < $gArmyType[$armytype]->limit;
	}
	
	// also returns hellhole-pos if possible
	function GetArmyOwnerName ($army) {
		if (!is_object($army)) $army = sqlgetobject("SELECT * FROM `army` WHERE `id` = ".intval($army));
		if ($army->user) return sqlgetone("SELECT `name` FROM `user` WHERE `id`=".$army->user);
		if ($army->hellhole) {
			$hellhole = sqlgetobject("SELECT * FROM `hellhole` WHERE `id` = ".intval($army->hellhole));
			if ($hellhole) return "(".$hellhole->x.",".$hellhole->y.")";
		}
		return "Server";
	}

	function escapearmyname ($name) {
		return ereg_replace("[&<>]","_",$name);// html security
	}
	
	function CanControllArmy ($army,$user) {
		if(!is_object($army))$army = sqlgetobject("SELECT * FROM `army` WHERE `id`=".intval($army));
		if(!is_object($user))$user = sqlgetobject("SELECT * FROM `user` WHERE `id`=".intval($user));
		if ($army->user == 0) return $user->admin != 0;
		if ($army->user == $user->id) return true;
		if (!(intval($army->flags) & kArmyFlag_GuildCommand)) return false;
		$ownerguild = sqlgetone("SELECT `guild` FROM `user` WHERE `id`=".$army->user);
		return $user->guild == $ownerguild && (intval($user->guildstatus) % kGuildCommander) == 0;
	}
	
	// save the capture calc in cUnit::CaptureShips
	function Capture ($army,$captured) {
		// tables should be locked when this is called...
		$deposit = sqlgetobject("SELECT * FROM `army` WHERE `type` = ".kArmyType_Fleet." AND `flags` & ".kArmyFlag_Captured." AND `user` = ".$army->user."
			AND `x` >= ".($army->x-1)." AND `x` <= ".($army->x+1)."
			AND `y` >= ".($army->y-1)." AND `y` <= ".($army->y+1));
		if (!$deposit) {
			$deposit = cArmy::SpawnArmy($army->x,$army->y,$captured,"gekapert",kArmyType_Fleet,$army->user,$army->quest,$army->hellhole,false,kArmyFlag_Captured);
			if (!$deposit) return false; // no room for new army
			return true; // deposit created with correct units
		}
		$units = cUnit::GetUnits($deposit->id);
		foreach ($captured as $o) $units[] = $o;
		cUnit::SetUnits($units,$deposit->id);
		return true;
	}
	
	//auf anraten von ghoulie umgeschriebe, ist nun wohl uebersichtlicher und ausserdem laut ghoulie wohl performanter
	// ext=FALSE: returns all armies a user is able to control
	// ext=TRUE: returns all armies owned by user + all guildarmies if user is gc
	
	function getMyArmies($ext=FALSE,$user=0){
		if($user==0){
			global $gUser;
			$user=$gUser;
		}else if (!is_object($user)) {
				$user=intval($user);
				$user=sqlgetobject("SELECT `guildstatus`,`id`,`guild` FROM `user` WHERE `id`=$user");
		}
		if (empty($user) || $user==0 || !is_object($user)) return array();
		if (($user->guildstatus%kGuildCommander)==0) {
			$r1 = sqlgettable("SELECT a.*,u.name as owner FROM `army` a,`user` u 
				WHERE u.`id`=a.`user` AND a.`user`=".$user->id." AND !(a.`flags`& ".kArmyFlag_GuildCommand.")"." ORDER BY `type`,`name`");
			$r2 = sqlgettable("SELECT a.*,u.`name` as owner FROM `army` a,`user` u 
				WHERE a.`flags` & ".kArmyFlag_GuildCommand." AND a.`user`=u.`id` AND u.`guild`=".$user->guild." ORDER BY `type`,`name`");
			$r = array();
			foreach ($r1 as $o) $r[$o->id] = $o;
			foreach ($r2 as $o) $r[$o->id] = $o;
			//echo "<hr>1";vardump2($r);
			//echo "user is gc : ".count($r)."<br>";
		}else{
			$r = sqlgettable("SELECT a.*,u.name as owner FROM `army` a,`user` u WHERE u.`id`=a.`user` AND a.`user`=".$user->id." 
			AND 1 ORDER BY `type`,`name`","id");
			// !(a.`flags`& ".kArmyFlag_GuildCommand.")
			//echo "user is not gc  : ".count($r)."<br>";
			//echo "<hr>2";vardump2($r);
		}
		if($ext==TRUE)
			if($user->guildstatus%kGuildCommander != 0 ) {
				$r2 = sqlgettable("SELECT a.*,u.`name` as owner FROM `user` u,`army` a WHERE u.`id`=a.`user` 
				AND a.`flags`&".kArmyFlag_GuildCommand." AND a.`user`=".$user->id." ORDER BY `type`,`name`","id");
				$r = array_merge2($r,$r2);
			}
		return $r;
	}
	
	
	
	function DrawPillageRes($mask) {
		global $gRes;
		if ($mask == -1) $mask = 255; // TODO : unhardcode
		$i = 0; 
		foreach($gRes as $n=>$f) {
			if (intval($mask) & (1<<$i)) {?><img src="<?=g("res_$f.gif")?>" valign="center"><?php }
			++$i;
		}
	}
	
	
	// returns the maximum speed="waiting time" of the units
	// uses $army->units and $army->transport (sailors) if available
	function GetArmySpeed ($army) {
		if (!is_object($army)) $army = sqlgetobject("SELECT * FROM `army` WHERE `id` = ".intval($army));
		if (!$army) return 0;
		if (!isset($army->units)) $army->units = cUnit::GetUnits($army->id);
		
		$maxweight = cUnit::GetMaxArmyWeight($army->type);
		$curweight = cUnit::GetUnitsSum($army->units,"weight");
		if ($curweight > $maxweight*1.02) return 0; // 2 percent tolerace
		
		$speed = cUnit::GetUnitsSpeed($army->units);
		$sum = cUnit::GetUnitsSum($army->units);
		
		$debug = false;
		if ($debug) echo "getArmySpeed($army) : $speed<br>\n";
		
		//size modification
		if ($sum > kArmy_BigArmyGoSlowLimit) {
			$faktor = pow(kArmy_BigArmyGoSlowFactorPer1000Units,($sum - kArmy_BigArmyGoSlowLimit) / 1000); // TODO :unhardcode
			$speed *= $faktor;
			if ($debug) echo "size mod ($sum) : $faktor -> $speed<br>\n";
		}
		
		//sea stuff mods
		if ($army->type == kArmyType_Fleet) {
			if (!isset($army->transport)) $army->transport = cUnit::GetUnits($army->id,kUnitContainer_Transport);
			$maxtransp = cUnit::GetUnitsSum($army->units,"last");
			$sailors = cUnit::GetUnitsSailors($army->transport);
			if ($sailors < ($maxtransp / 100 * 30)) {  // TODO :unhardcode
				$speed = 0;
			} else {
				$speed = cUnit::GetUnitsSpeed($army->units);
				$speed *= (0.8 + (1 - (($sailors/$maxtransp)>1 ? 1.3 :($sailors/$maxtransp))));  // TODO :unhardcode
			}
			if ($debug) echo "fleet has $sailors/$maxtransp sailors (".(100 * $sailors / $maxtransp)."%) -> $speed<br>";
		}
		
		return $speed;
	}
	
	
	
	// ##### ##### ##### ##### ##### ##### ##### #####
	// ##### ##### ## Create + Destroy ### ##### #####
	// ##### ##### ##### ##### ##### ##### ##### #####
	
	
	
	// if ($x,$y) is free, use it, else FindExit() or FindPierExit() if type=fleet
	// $treasure can be true (get from unittype), or an array(itemid=>amount,itemid=>amount)
	function SpawnArmy ($x,$y,$units,$name=false,$armytype=-1,$userid=0,$quest=0,$hellhole=0,$treasure=false,$flags=0) {
		global $gUnitType;
		
		foreach ($units as $k => $o) if (!$o->user) $units[$k]->user = $userid;
		
		// auto-determine army-name from units
		if (!$name) $name = $gUnitType[cUnit::GetUnitsMaxType($units)]->name;
		
		// auto-determine armytype from units
		if ($armytype == -1) {
			$armytype = kArmyType_Normal;
			foreach ($units as $o) if ($gUnitType[$o->type]->armytype)
				{ $armytype = $gUnitType[$o->type]->armytype; break; }
			if ($treasure == -1) $treasure = $userid == 0;
		}
		
		// determine spawn place (direct,findexit,findpierexit for fleet, return false in case of failure)
		$pos = array($x,$y);
		if (cArmy::GetPosSpeed($x,$y,$userid,$units,true) <= 0) {
			$building = sqlgetobject("SELECT * FROM `building` WHERE `x` = ".intval($x)." AND `y` = ".intval($y)." LIMIT 1");
			if ($armytype == kArmyType_Fleet && $building)
					$pos = cArmy::FindPierExit($x,$y,$userid,$units);
			else	$pos = cArmy::FindExit($x,$y,$userid,$units);
			if (!$pos) return false; // no free space
		}
		
		// construct army object
		$army = false;
		$army->name = cArmy::escapearmyname($name);
		$army->type = $armytype;
		$army->user = $userid;
		$army->quest = $quest;
		$army->hellhole = $hellhole;
		$army->x = $pos[0];
		$army->y = $pos[1];
		$army->nextactiontime = time() + 60;  // TODO :unhardcode
		$army->idle = 0;
		$army->flags = $flags;
		global $gRes;
		foreach ($gRes as $n=>$f) $army->$f = 0;
		
		// automatically determine treasure from unittype
		if ($treasure && !is_array($treasure)) {
			$treasure = cUnit::GetUnitsTreasure($units);
		}	
		
		// give treasure to army (currently res only)
		if ($treasure) {
			// todo : replace by real items, but i need to clean up the whole item code first...
			global $gItemType2Res;
			foreach ($treasure as $itemtype => $amount)
				if (isset($gItemType2Res[$itemtype]))
					$army->{$gItemType2Res[$itemtype]} = $amount;
		}
		
		// create and set units
		sql("INSERT INTO `army` SET ".obj2sql($army));
		$army->id = mysql_insert_id();
		$army->units = $units;
		cUnit::SetUnits($army->units,$army->id);
		
		// update cron army cache
		global $gAllArmys; 
		if (isset($gAllArmys)) $gAllArmys[$army->id] = $army;
		return $army;
	}
	
	function DeleteArmy ($army,$no_resdrop=false,$why=false) {
		TablesLock(); // TODO ! this unlock could destroy a lock in cron !!!! (not nestable)
		if (!is_object($army)) $army = sqlgetobject("SELECT * FROM `army` WHERE `id` = ".intval($army));
		if (!$army) { TablesUnlock(); return; }
		$armyid = intval($army->id);
		
		// delete army from cache
		global $gAllArmys;
		if (isset($gAllArmys)) unset($gAllArmys[$armyid]);
			
		cItem::dropAll($armyid);
		if (!$no_resdrop) {
			// army drops ressources
			global $gRes2ItemType;
			global $gRes;
			foreach($gRes as $n=>$f) {
				cItem::SpawnItem($army->x,$army->y,$gRes2ItemType[$f],$army->$f);
			}
		}
		
		require_once("lib.fight.php");
	
		if (!$why) $why = "Die Armee _ARMYNAME_ von _ARMYOWNERNAME_ wurde zerstört.";
		cFight::StopAllArmyFights($army,$why);
		sql("DELETE FROM `armyaction` WHERE `army`=".$army->id);
		sql("DELETE FROM `waypoint` WHERE `army` = ".$army->id);
		sql("DELETE FROM `unit` WHERE `army` = ".$army->id);
		sql("DELETE FROM `unit` WHERE `transport` = ".$army->id);
		sql("DELETE FROM `army` WHERE `id` = ".$army->id);
		TablesUnlock();
	}
	
	function ArmyAt ($army,$x,$y) { // obj or id
		if (!is_object($army)) $army = sqlgetobject("SELECT * FROM `army` WHERE `id` = ".intval($army));
		if (!$army) return false;
		return abs($army->x-$x) + abs($army->y-$y) <= 1;
	}
	
	function ArmyAtDiag ($army,$x,$y) { // obj or id
		if (!is_object($army)) $army = sqlgetobject("SELECT * FROM `army` WHERE `id` = ".intval($army));
		if (!$army) return false;
		return abs($army->x-$x) <= 1 && abs($army->y-$y) <= 1;
	}
	
	
	
	// ##### ##### ##### ##### ##### ##### ##### #####
	// ##### #####  Attack and Range ##### ##### #####
	// ##### ##### ##### ##### ##### ##### ##### #####
	
	
	function hasDistantAttack ($army) {
		if (!is_object($army)) $army = sqlgetobject("SELECT * FROM `army` WHERE `id` = ".intval($army));
		if (!isset($army->units)) $army->units = cUnit::GetUnits($army->id);
		return $army->type == kArmyType_Normal && cUnit::GetUnitsSum($army->units,"f") > 0;
	}
	
	function hasMeleeAttack($army) { // obj or id
		if (!is_object($army)) $army = sqlgetobject("SELECT `type` FROM `army` WHERE `id`=".intval($army));
		return $army->type == kArmyType_Normal || $army->type == kArmyType_Fleet;
	}
	
	function hasPillageAttack($army)  { // obj or id
		if (!is_object($army)) $army = sqlgetobject("SELECT `type` FROM `army` WHERE `id`=".intval($army));
		return $army->type == kArmyType_Normal;
	}
	
	function hasSiegeAttack($army)  { // obj or id
		if (!is_object($army)) $army = sqlgetobject("SELECT `type` FROM `army` WHERE `id`=".intval($army));
		if (!isset($army->units)) $army->units = cUnit::GetUnits($army->id);
		return cUnit::GetUnitsSiegeAttack($army->units,$army->user) > 0;
	}
	
	function inMeleeRange($dx,$dy) {
		return abs($dx)+abs($dy) <= 1;
	}
	
	function inPillageRange($dx,$dy) {
		return abs($dx)+abs($dy) <= 1;
	}
	
	function inSiegeRange($dx,$dy) {
		return abs($dx)+abs($dy) <= 1;
	}
	
	//@param: army hybrid, army=id oder army object
	//@param: dx,dy ist der abstand in reichweite?
	function inDistantRange($dx,$dy,$army) {
		if (!is_object($army)) $army = sqlgetobject("SELECT * FROM `army` WHERE `id` = ".intval($army));
		if (!isset($army->units)) $army->units = cUnit::GetUnits($army->id);
		return cUnit::GetDistantDamage($army->units,$dx,$dy) > 0;
	}
	
	
	// ##### ##### ##### ##### ##### ##### ##### #####
	// ##### #####  Res and Item Transfer  ##### #####
	// ##### ##### ##### ##### ##### ##### ##### #####
	
	
	function ArmyGetRes($armyid,$userid,$lumber,$stone,$food,$metal,$runes = 0) {
		global $gRes;
		sql("LOCK TABLES	`user` WRITE, `phperror` WRITE,
							`guild` WRITE,
							`newlog` WRITE, 
							`sqlerror` WRITE, 
							`army` WRITE,
							`unit` READ,
							`item` READ,
							`itemtype` READ,
							`unittype` READ");
		$user = sqlgetobject("SELECT * FROM `user` WHERE `id`=".intval($userid));
		$army = sqlgetobject("SELECT * FROM `army` WHERE `id`=".intval($armyid));
		$guild = $user->guild?sqlgetobject("SELECT * FROM `guild` WHERE `id` = ".$user->guild):false;
		$army_max_take = max(0,floor(cUnit::GetUnitsSum(cUnit::GetUnits($army->id),"last") - cArmy::GetArmyTotalWeight($army)));
		$debug = false;
		// overflow to guild possible, if guild exists, and ressources are put in
		/*
			if ($user->guildstatus%kSiloGive!=0) // user kann nicht einzahlen
				foreach($gRes as $n=>$f) if($$f < 0) $$f = 0;
		*/
		
		if($user && $army) {
			if ($debug) { foreach($gRes as $n=>$f) echo $$f.","; echo " begin<br>"; }
			
			// limit by silo (guild+user) capacity   or  available ressources (user only, not guild)
			foreach($gRes as $n=>$f) {
				if($$f > 0)
					$$f = max(0,min(floor($$f),floor($user->{$f})));
				else if($$f < 0) {
					$capacity = floor(max(0,$user->{"max_$f"}-$user->{$f}));
					if ($user->guildstatus%kSiloGive==0)
						$capacity += floor(max(0,$guild->{"max_$f"}-$guild->{$f}));
					$$f = -max(0,min(-$$f,$capacity,$army->$f));
				}
			}
			
			if ($debug) { foreach($gRes as $n=>$f) echo $$f.","; echo " after silo<br>"; }
			
			// limit by army capacity
			$take_out = false;
			foreach($gRes as $n=>$f) if($$f > 0) {
				$take_out = true;
				$$f = max(0,min($$f,$army_max_take));
				$army_max_take -= $$f;
			}
			
			if ($debug) { foreach($gRes as $n=>$f) echo $$f.","; echo " end <br>"; }
			
			$msg = array();
			
			foreach($gRes as $n=>$f) if ($$f != 0) {
				$$f = floor($$f);
				$user_capacity = floor($user->{"max_$f"}-$user->{$f});
				$overflow_to_guild = ($$f < 0) ? max(0,-$$f - $user_capacity) : 0;
				// wegschmeissen was nicht ins gildenlager passt
				
				$msg[] = floor(-$$f)." ".$n;
				
				sql("UPDATE `army` SET `$f`=`$f`+(".($$f).") WHERE `id`=".$army->id);
				sql("UPDATE `user` SET `$f`=`$f`-(".($$f+$overflow_to_guild).") , `guildpoints`=`guildpoints`+(".$overflow_to_guild.") WHERE `id`=".$user->id);
							
				if ($overflow_to_guild > 0)
					sql("UPDATE `guild` SET `$f`=`$f`+(".$overflow_to_guild.") WHERE `id`=".$guild->id);			
			}
			logMe($user->id,NEWLOG_TOPIC_MISC,((!$take_out)?NEWLOG_ARMY_RES_PUTDOWN:NEWLOG_ARMY_RES_GETOUT),$army->x,$army->y,0,$army->name,implode(", ",$msg));
		}
		sql("UNLOCK TABLES");
	}
	
	
	// ##### ##### ##### ##### ##### ##### ##### #####
	// ##### ##### ##### Terrain-Mod ##### ##### #####
	// ##### ##### ##### ##### ##### ##### ##### #####
	
	
	
	//calculates the modification factors for a,v,f
	//depending on the terrain and buildings at x,y
	//@param x x-position
	//@param y y-position
	//@return array filled with the modifications ie. array("a"=>attack,"v"=>defense,"f"=>range)
	function GetFieldMod($x,$y){
		global $gBuildingType,$gTerrainType;
		$mod = array("a"=>1.0,"v"=>1.0,"f"=>1.0);
		
		$ttype = cMap::StaticGetTerrainAtPos(intval($x),intval($y));
		if(isset($gTerrainType[$ttype])){
			$mod["a"] *= $gTerrainType[$ttype]->mod_a;
			$mod["v"] *= $gTerrainType[$ttype]->mod_v;
			$mod["f"] *= $gTerrainType[$ttype]->mod_f;
		}
		else {
			$mod["a"] *= $gTerrainType[kTerrain_Grass]->mod_a;
			$mod["v"] *= $gTerrainType[kTerrain_Grass]->mod_v;
			$mod["f"] *= $gTerrainType[kTerrain_Grass]->mod_f;
		}
		$ttype = sqlgetone("SELECT `type` FROM `building` WHERE `x`=(".intval($x).") AND `y`=(".intval($y).") LIMIT 1");
		if(isset($gBuildingType[$ttype])){
			$mod["a"] *= $gBuildingType[$ttype]->mod_a;	
			$mod["v"] *= $gBuildingType[$ttype]->mod_v;	
			$mod["f"] *= $gBuildingType[$ttype]->mod_f;	
		}		
		return $mod;
	}
	
	//calculates the multiplication modifier for distant damage
	//ie. less damage if you shoot through mountains
	function GetDistantMod($sx,$sy,$dx,$dy){
		global $gBuildingType,$gTerrrainType;
		$mod = 1;
		$x = $sx;
		$y = $sy;
		do {
			list($x,$y) = GetNextStep($sx,$sy,$x,$y,$dx,$dy);
			$m = cArmy::GetFieldMod($x,$y);
			$mod *= $m["f"];
		} while($x != $dy && $y != $dy);
		return $mod;
	}
		
	
	
	
	
	
	
	
	// ##### ##### ##### ##### ##### ##### ##### #####
	// ##### ##### #####  Waypoints  ##### ##### #####
	// ##### ##### ##### ##### ##### ##### ##### #####
	
	
	
	function ArmySetWaypoint ($army,$x,$y) {  // object or id
		if (!is_object($army)) $army = sqlgetobject("SELECT * FROM `army` WHERE `id`=".intval($army));
		if(!$army) return false;
		echo "ArmySetWaypoint($army->name,$x,$y)<br>";
		
		$waypointmaxprio = sqlgetone("SELECT MAX(`priority`) FROM `waypoint` WHERE `army` = ".$army->id);	
		if (!$waypointmaxprio) {
			// first waypoint set, start move delay
			sql("UPDATE `army` SET `nextactiontime` = ".(time()+60)." WHERE `id` = ".$army->id." LIMIT 1");  // TODO :unhardcode
			$wp = false;
			$wp->x = $army->x;
			$wp->y = $army->y;
			$wp->army = $army->id;
			$wp->priority = 0;
			sql("INSERT INTO `waypoint` SET ".obj2sql($wp));
			$waypointmaxprio = 0;
		}
		$wp = false;
		$wp->x = intval($x);
		$wp->y = intval($y);
		$wp->army = $army->id;
		$wp->priority = intval($waypointmaxprio)+1;
		sql("INSERT INTO `waypoint` SET ".obj2sql($wp));
		return array($wp->x,$wp->y);
	}
	
	
	function ArmyCancelWaypoint($army,$wp) {
		if (!is_object($wp))	$wp = sqlgetobject("SELECT * FROM `waypoint` WHERE `id` = ".intval($wp));
		if (!is_object($army))	$army = sqlgetobject("SELECT * FROM `army` WHERE `id` = ".intval($army));
		if (!$wp || !$army || $wp->army != $army->id) return;
		
		$wps = sqlgettable("SELECT * FROM `waypoint` WHERE `army` = ".$wp->army." ORDER BY `priority` LIMIT 3");
		if (count($wps) > 2) {
			// there are waypoints left
			sql("DELETE FROM `waypoint` WHERE `id` = ".$wp->id);
			if ($wps[1]->id == $wp->id) // deleted next wp -> adjust startwp(0)
				sql("UPDATE `waypoint` SET `x` = ".$army->x.", `y` = ".$army->y." , `priority` = 0 WHERE `id` = ".$wps[0]->id);
		} else {
			// remove all wp
			sql("DELETE FROM `waypoint` WHERE `army` = ".$wp->army);
		}
	}
	
	
	
	
	
	
	
	
	
	
	// ##### ##### ##### ##### ##### ##### ##### #####
	// ##### ##### Terrain Collection ##### ##### #####
	// ##### ##### ##### ##### ##### ##### ##### #####
	
	
	
	function GetArmyCollectTime($army,$terraintype) {
		switch ($terraintype) {
			case kTerrain_Forest :	return 60*10; break; // TODO :unhardcode
			case kTerrain_Rubble :	return 60*10; break; // TODO :unhardcode
			case kTerrain_Field :	return 60*10; break; // TODO :unhardcode
		}
		return 0;
	}
	
	function ArmyCollect($army,$terraintype) {
		switch ($terraintype) {
			case kTerrain_Forest :
				cItem::SpawnArmyItem($army,kResItemType_lumber,kHarvestAmount);
				sql("REPLACE INTO `terrain` SET `type`=".kTerrain_TreeStumps." , `x` = ".$army->x." , `y` = ".$army->y);
				break;
			case kTerrain_Rubble :
				cItem::SpawnArmyItem($army,kResItemType_stone,kHarvestAmount);
				sql("REPLACE INTO `terrain` SET `type`=".kTerrain_Grass." , `x` = ".$army->x." , `y` = ".$army->y);
				break;
			case kTerrain_Field :
				cItem::SpawnArmyItem($army,kResItemType_food,kHarvestAmount);
				sql("REPLACE INTO `terrain` SET `type`=".kTerrain_Grass." , `x` = ".$army->x." , `y` = ".$army->y);
				break;
		}
		RegenSurroundingNWSE($army->x,$army->y);
	}
	
	
	
	// ##### ##### ##### ##### ##### ##### ##### #####
	// ##### ##### #####   The Rest  ##### ##### #####
	// ##### ##### ##### ##### ##### ##### ##### #####
	
	
	
	
	
	
	
	function GetArmyTotalWeight ($army) {
		if (!is_object($army)) $army = sqlgetobject("SELECT * FROM `army` WHERE `id`=".intval($army));
		$load = $army->lumber + $army->stone + $army->food + $army->metal + $army->runes;
		$load += cItem::getArmyItemsWeight($army->id);
		$transport = cUnit::GetUnits($army->id,kUnitContainer_Transport);
		$load += cUnit::GetUnitsSum($transport,"weight");
		return $load;
	}
	
	function DropExcessCargo ($army,$receipient_army=false,$maxcargoweight=-1) {
		if (!is_object($army)) $army = sqlgetobject("SELECT * FROM `army` WHERE `id` = ".intval());
		if (!isset($army->units)) $army->units = cUnit::GetUnits($army->id);
		if (!isset($army->transport)) $army->transport = cUnit::GetUnits($army->id,kUnitContainer_Transport);
		if ($maxcargoweight == -1) $maxcargoweight = cUnit::GetUnitsSum($army->units,"last") - cUnit::GetUnitsSum($army->transport,"weight");
		if ($maxcargoweight < 0) $maxcargoweight = 0;
		
		global  $gItemType,$gRes,$gRes2ItemType,$gItemType2Res;
		$debug = false;
		
		if ($debug) echo "DropExcessCargo : maxcargoweight=$maxcargoweight<br>";
		
		$items = sqlgettable("SELECT * FROM `item` WHERE `army` = ".$army->id);
		foreach ($items as $o) {
			if ($o->amount <= 0) continue;
			$w = max(1,$gItemType[$o->type]->weight);
			$dropamount = ceil($o->amount - $maxcargoweight / $w);
			if ($dropamount > 0) {
				if ($debug) echo "dropping $dropamount ".$gItemType[$o->type]->name."<br>";
				if ($receipient_army) 
						cItem::SpawnArmyItem($receipient_army,$o->type,$dropamount,$o->quest,$o->param);
				else	cItem::SpawnItem($army->x,$army->y,$o->type,$dropamount,$o->quest,$o->param);
				$o->amount -= $dropamount;
				if ($o->amount > 0)
						sql("UPDATE `item` SET ".arr2sql(array("amount"=>$o->amount))." WHERE `id` = ".$o->id);
				else	sql("DELETE FROM `item` WHERE `id` = ".$o->id);
			}
			$maxcargoweight -= $o->amount * $w;
			if ($maxcargoweight < 0) $maxcargoweight = 0;
			if ($debug) echo "weight left after $o->amount ".$gItemType[$o->type]->name." [w=$w] : $maxcargoweight<br>";
		}
		
		// strange bug, stops after first iteration if dropping while walking directly $gRes
		$resitems = array();
		foreach ($gRes as $n=>$f) {
			$o = false;
			$o->amount = $army->$f;
			$o->type = $gRes2ItemType[$f];
			$o->quest = 0;
			$o->param = 0;
			$resitems[] = $o;
		}
			
		foreach ($resitems as $o) {
			if ($o->amount <= 0) continue;
			$w = $gItemType[$o->type]->weight;
			if ($w <= 0) continue;
			$dropamount = ceil($o->amount - $maxcargoweight / $w);
			if ($dropamount > 0) {
				if ($debug) echo "dropping $dropamount ".$gItemType[$o->type]->name."<br>";
				if ($receipient_army) 
						cItem::SpawnArmyItem($receipient_army,$o->type,$dropamount,$o->quest,$o->param);
				else	cItem::SpawnItem($army->x,$army->y,$o->type,$dropamount,$o->quest,$o->param);
				$o->amount -= $dropamount;
				sql("UPDATE `army` SET ".arr2sql(array($gItemType2Res[$o->type]=>$o->amount))." WHERE `id` = ".$army->id);
			}
			$maxcargoweight -= $o->amount * $w;
			if ($maxcargoweight < 0) $maxcargoweight = 0;
			if ($debug) echo "weight left after $o->amount ".$gItemType[$o->type]->name." [w=$w] : $maxcargoweight<br>";
		}
	}
	
	

	function AddSteps ($x,$y,$steps) {
		if ($steps == 0) return;
		sql("UPDATE `terrain` SET `steps`=`steps`+".intval($steps)." WHERE `x`=".intval($x)." AND `y`=".intval($y));
		if (mysql_affected_rows() <= 0) {
			$oldtype = cMap::StaticGetTerrainAtPos(intval($x),intval($y));
			sql("INSERT INTO `terrain` SET `type`=".$oldtype.",`x`=".intval($x).",`y`=".intval($y).",`steps`=".intval($steps));
		}
	}
	
	// $userid for BuildingOpenForUser()
	// $units for GetUnitsMovableMask()
	// uses $gAllArmys cache if available (cron/minicron)
	function GetPosSpeed ($x,$y,$userid=0,$units=false,$armyblock=true) {
		global $gTerrainType,$gBuildingType;
		$debug = false;
		
		$xycondition = "`x` = ".intval($x)." AND `y` = ".intval($y);
		$movablemask = $units ? cUnit::GetUnitsMovableMask($units) : kTerrain_Mask_Moveable_Default;
		if ($debug) echo "GetPosSpeed(),unitsmovable=$movablemask<br>\n";
		$override = true;
		
		// check buildings
		$building = sqlgetobject("SELECT * FROM `building` WHERE ".$xycondition." LIMIT 1");
		if($building){
			// is open for user?
			$b_speed = cBuilding::BuildingOpenForUser($building,$userid) ? $gBuildingType[$building->type]->speed : 0;
			$override = $gBuildingType[$building->type]->movable_override_terrain == 1;
			if ($debug) echo "GetPosSpeed(),building=$b_speed<br>\n";
			//if ($speed == 0) return 0;
			// check movable
			if (($movablemask & intval($gBuildingType[$building->type]->movable_flag)) == 0) {
				if ($debug) echo "GetPosSpeed(),b_movable=0<br>\n";
				$b_speed = 0;
				//return 0;
			}
		}
		
		{
			// check terrain
			$terraintype = cMap::StaticGetTerrainAtPos($x,$y);
			//$terraintype = sqlgetone("SELECT `type` FROM `terrain` WHERE $xycondition LIMIT 1");
			if (!$terraintype) $terraintype = kTerrain_Grass;
			$t_speed = $gTerrainType[$terraintype]->speed;
			if ($debug) echo "GetPosSpeed(),terrainspeed=$t_speed,terrainmovable=".$gTerrainType[$terraintype]->movable_flag."<br>\n";
			//if ($t_speed == 0) return 0;
			// check movable
			if (($movablemask & intval($gTerrainType[$terraintype]->movable_flag)) == 0) {
				if ($debug) echo "GetPosSpeed(),t_movable=0<br>\n";
				$t_speed = 0;
				//return 0;
			}
		}
		
		// check army
		if ($armyblock) {
			// use cron army cache if available
			global $gAllArmys;
			if (isset($gAllArmys)) foreach ($gAllArmys as $o) {
				if ($o->x == $x && $o->y == $y) {
					if ($debug) echo "GetPosSpeed(),army=0<br>\n";
					return 0;
				}
			} else if (sqlgetone("SELECT 1 FROM `army` WHERE ".$xycondition." LIMIT 1"))  {
				if ($debug) echo "GetPosSpeed(),army=0<br>\n";
				return 0;
			}
		}


		if($debug)echo "GetPosSpeed(): b_speed=$b_speed t_speed=$t_speed override=$override<br>\n";
		//check if building movable overrides terrain
		if($building && $override){
			//only building counts, terrain will be ignored
			$speed = $b_speed;
		} else if($building){
			//building and terrain, no override
			$speed = max($t_speed,$b_speed);
		} else {
			//only terrain
			$speed = $t_speed;
		}

		if ($debug) echo "GetPosSpeed()=$speed<br>\n";
		return $speed;
	}
	
	// find a valid exit for an army (kaserne ausgang)
	 // randomizes exit
	function FindExit ($x,$y,$userid=0,$units=false) {
		$arr = array(0,1,2,3);
		shuffle($arr);
		for ($i=0;$i<4;++$i) {
			if ($arr[$i] == 0 && cArmy::GetPosSpeed($x,$y+1,$userid,$units,true) > 0) return array($x,$y+1);
			if ($arr[$i] == 1 && cArmy::GetPosSpeed($x,$y-1,$userid,$units,true) > 0) return array($x,$y-1);
			if ($arr[$i] == 2 && cArmy::GetPosSpeed($x+1,$y,$userid,$units,true) > 0) return array($x+1,$y);
			if ($arr[$i] == 3 && cArmy::GetPosSpeed($x-1,$y,$userid,$units,true) > 0) return array($x-1,$y);
		}
		return false;
	}
	
	// for harbour
	function FindPierExit ($x,$y,$userid,$units) {
		$x = intval($x);
		$y = intval($y);
		$r = 7;  // TODO :unhardcode
		//fetch all near buildings of type "steg"
		$piers = sqlgettable("SELECT `x`,`y`,((`x`-$x)*(`x`-$x) + (`y`-$y)*(`x`-$y)) as `dist` FROM `building`
			WHERE `type`=".kBuilding_Steg." AND `user` = ".intval($userid)."
			AND `x`<".($x+$r)." AND `x`>".($x-$r)."  
			AND `y`<".($y+$r)." AND `y`>".($y-$r)." ORDER BY `dist`");
		foreach ($piers as $pier) {
			$pos = cArmy::FindExit($pier->x,$pier->y,$userid,$units);
			if ($pos) return $pos;
		}
		return FALSE;
	}
	
	function ArmyAtPier ($army,$x,$y,$userid) {
		$x = intval($x);
		$y = intval($y);
		$r = 7;  // TODO :unhardcode
		//fetch all near buildings of type "steg"
		$piers = sqlgettable("SELECT `x`,`y`,((`x`-$x)*(`x`-$x) + (`y`-$y)*(`x`-$y)) as `dist` FROM `building`
			WHERE `type`=".kBuilding_Steg." AND `user` = ".intval($userid)."
			AND `x`<".($x+$r)." AND `x`>".($x-$r)."  
			AND `y`<".($y+$r)." AND `y`>".($y-$r)." ORDER BY `dist`");
		foreach ($piers as $pier) if (cArmy::ArmyAt($army,$pier->x,$pier->y)) return TRUE;
		return FALSE;
	}
	
	function AddArmyFrags ($army,$frags) {
		sql("UPDATE `army` SET `frags`= `frags` + ".floatval($frags)." WHERE `id` = ".intval($army));
	}
}
?>
