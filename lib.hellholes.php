<?php
require_once("lib.main.php");
require_once("lib.army.php");
require_once("lib.path.php");


// constructs a Hellhole instance
function GetHellholeInstance ($o) {
	$class = "Hellhole_".$o->ai_type;
	$hellhole = new $class();
	if ($o) $hellhole->SetObject($o);
	return $hellhole;
}


// $findbuilding=true : returns building if one is found -> check for   === true
function Reachable ($x1,$y1,$x2,$y2,$movablemask,$findbuilding=false,$report_nonuser_buildings=false) {
	$debugtxt = "Reachable($x1,$y1,$x2,$y2,$movablemask,$findbuilding=false)";
	global $gTerrainType;
	for (list($x,$y)=GetNextStep($x1,$y1,$x1,$y1,$x2,$y2);$x!=$x2||$y!=$y2;list($x,$y)=GetNextStep($x,$y,$x1,$y1,$x2,$y2)) {
		if ($findbuilding) {
			$building = sqlgetobject("SELECT * FROM `building` WHERE `x` = ".$x." AND `y` = ".$y." LIMIT 1");
			global $gBuildingType;
			if ($building && $gBuildingType[$building->type]->speed == 0) {
				if ($building->user != 0) return $building;
				if ($building->user == 0 && $report_nonuser_buildings) return $building;
			}
		}
		$ter = cMap::StaticGetTerrainAtPos($x,$y);
		if (!(intval($gTerrainType[$ter]->movable_flag) & intval($movablemask))) 
			{ echo "$debugtxt = blocked($x,$y)<br>"; return false; }
	}
	echo "$debugtxt = free<br>";
	return true;
}

// $r is max-radius
function SearchNearestBuilding ($x,$y,$r,$buildingtype,$userid) {
	$x = intval($x); $y = intval($y); $r = intval($r);
	return sqlgetobject("SELECT *,((`x`-($x))*(`x`-($x)) + (`y`-($y))*(`y`-($y))) as `dist` FROM `building` 
				WHERE `type` = ".intval($buildingtype)." AND `user` = ".intval($userid)." 
				AND `x` >= ".($x-$r)." AND `x` <= ".($x+$r)." 
				AND `y` >= ".($y-$r)." AND `y` <= ".($y+$r)." ORDER BY `dist` LIMIT 1");
}





//********************[ Hellhole_0 ]*************************************************************
// hellhole base class
// stupidly spit out wandering monsters
//*********************************************************************************************

class Hellhole_0 {
	// transfer vars from sql-object $o to $this
	function SetObject ($o) {
		assert($o);
		$o = get_object_vars($o);
		foreach($o as $name=>$value) $this->$name = $value;
	}
	
	// called from removeBuilding()
	// $userid of the destroyer, for quest / igms
	function Destroy ($userid) {
		assert($this->id);
		sql("DELETE FROM `hellhole` WHERE `id` = ".$this->id." LIMIT 1");
	}
	
	// try to spawn a new monster
	function SpawnMonster ($flags=false,$spawntype=-1) {
		$monstercount = intval(sqlgetone("SELECT COUNT(*) FROM `army` WHERE `hellhole` = ".$this->id));
		if ($this->type < 0) return false;
		if ($monstercount >= $this->num) return false;
		
		global $gUnitType,$gRandomSpawnTypes;
		if ($spawntype == -1) $spawntype = $this->type ? $this->type : $gRandomSpawnTypes[array_rand($gRandomSpawnTypes)];
		$spawncount = $this->armysize + $this->level * $this->armysize/10;
		if ($flags === false) $flags = kArmyFlag_Wander|kArmyFlag_RunToEnemy|kArmyFlag_AutoAttack;
		$newmonster = cArmy::SpawnArmy($this->x,$this->y,cUnit::Simple($spawntype,$spawncount),
			false,kArmyType_Normal,0,0,$this->id,true,$flags);
		if ($newmonster) echo "Spawned $spawncount ".$gUnitType[$spawntype]->name." at $newmonster->x,$newmonster->y <br>";
		else echo "spawn of $spawncount ".$gUnitType[$spawntype]->name." failed<br>";
		return $newmonster;
	}
	
	function Think () {
		$time = time();
		$this->spawntime = $time + $this->spawndelay;
		
		if ($this->SpawnMonster()) {
			$this->totalspawns++;
			sql("UPDATE `hellhole` SET `spawntime` = ".$this->spawntime." , `totalspawns` = ".$this->totalspawns." WHERE `id` = ".$this->id." LIMIT 1");
			
			// check for levelup
			$curlevel = round($this->totalspawns/($this->num*5));
			if ($curlevel > $this->level && $time - $this->lastupgrade > 12*60*60) {
				echo "hellhole ".$this->id." goes upgrading *g* .... newlevel: ".$this->level." <br>";
				$this->level++;
				$this->lastupgrade = $time;
				sql("UPDATE `building` SET `level` = ".$this->level." WHERE `x`=".$this->x." AND `y`=".$this->y." LIMIT 1");
				sql("UPDATE `hellhole` SET `level` = ".$this->level." , `lastupgrade` = ".$this->lastupgrade."  WHERE `id` = ".$this->id." LIMIT 1");
			}
		} else sql("UPDATE `hellhole` SET `spawntime` = ".$this->spawntime." WHERE `id` = ".$this->id." LIMIT 1");
	}
	
	
	function SaveData ($data=array()) {
		sql("UPDATE `hellhole` SET ".arr2sql(array("ai_data"=>implode(",",$data)))." WHERE `id` = ".intval($this->id));
	}
	
	// time to think
	function Cron ($dtime) { 
		// parent::Cron($dtime);
		assert($dtime>0); 
		if ($this->spawntime > time()) return;
		$this->Think();
	}
}



//********************[ Hellhole_1 ]*************************************************************
// siege until path to ai_data is clear, then pillage raids...
//*********************************************************************************************

define("kHellHole1_Mode_Plan",0);
define("kHellHole1_Mode_Siege",1);
define("kHellHole1_Mode_Raid",2);
define("kHellHole1_Data_ThinkCount",2);
define("kHellHole1_Data_SiegeCount",3);
define("kHellHole1_Data_RaidCount",4);
define("kHellHole1_Data_Mode",5);
define("kHellHole1_Data_Count",6);

class Hellhole_1 extends Hellhole_0 {
	function Hellhole_1 () {
		// $this->spawndelay = 3600; // should be about one hour
		$this->search_building_rad = 200; // todo : unhardcode , choosing player buildings only within this range
		$this->search_silo_rad = 25; // todo : unhardcode , max distance from found player building to silo
		$this->out_of_base_rad = 5; // todo : unhardcode , ramme exits the base this many fields
		$this->maxcount_think = 100; // todo : unhardcode? // stop attacking player after about 4 days
		$this->maxcount_siege = 3; // todo : unhardcode? // stop if 3 sieges failed
		$this->maxcount_raid = 5; // todo : unhardcode? // stop after 5 raid-attempts
		$this->victim_minpts = 5000; // todo : unhardcode? // don't attack players below a certain limit
	}
	function SearchNewTarget () {
		echo "searching for new target<br>";
		global $gUnitType;
		$r = $this->search_building_rad;
		$movablemask = intval($gUnitType[$this->type]->movable_flag) & intval($gUnitType[$this->type2]->movable_flag);
		$building = Reachable($this->x,$this->y,$this->x+rand(-$r,$r),$this->y+rand(-$r,$r),$movablemask,true);
		//if ($building && $building !== true) vardump2($building);
		if (!$building || $building === true || $building->user == 0) return false; // no building found
		echo "found building ".opos2txt($building)." of ".nick($building->user)."<br>";
		if (sqlgetone("SELECT `general_pts` FROM `user` WHERE `id` = ".$building->user) < $this->victim_minpts) return false; // too weak
		$silo = SearchNearestBuilding($building->x,$building->y,$this->search_silo_rad,kBuilding_Silo,$building->user);
		if (!$silo) return false;
		echo "found new target : ".opos2txt($silo)." of ".nick($silo->user)."<br>"; 
		return $silo;
	}
	
	function Think () {
		global $gUnitType;
		
		$time = time();
		$this->spawntime = $time + $this->spawndelay;
		sql("UPDATE `hellhole` SET `spawntime` = ".$this->spawntime." WHERE `id` = ".$this->id." LIMIT 1");
		
		$this->SpawnMonster();
		
		$data = ($this->ai_data && $this->ai_data != "") ? explode(",",$this->ai_data) : false;
		if ($data && count($data) < kHellHole1_Data_Count) $data = false;
		$building = $data ? sqlgetobject("SELECT * FROM `building` WHERE `x` = ".intval($data[0])." AND `y` = ".intval($data[1])) : false;
		if (!$building || $building->type != kBuilding_Silo) {
			$building = $this->SearchNewTarget();
			if (!$building) return;
			$data = array($building->x,$building->y,0,0,0,kHellHole1_Mode_Plan); // initialize data
			$this->SaveData($data);
		}
		
		$dx = $data[0] - $this->x;
		$dy = $data[1] - $this->y;
		// first exit own base using pathfinding
		$exitbase_pos = array($this->x,$this->y);
		if ($dx != 0) $exitbase_pos[0] += (($dx>0)?1:-1)*$this->out_of_base_rad;
		if ($dy != 0) $exitbase_pos[1] += (($dy>0)?1:-1)*$this->out_of_base_rad;
		// one step bevore the target, ramme clears path to this point and raiders go here to pillage
		if (abs($dx) > abs($dy))
				$epos = array($data[0]+(($dx>0)?-1:1),$data[1]);
		else	$epos = array($data[0],$data[1]+(($dy>0)?-1:1));
		// returnpoint for the raiders, one step in direction of target
		if (abs($dx) > abs($dy))
				$returnpos = array($this->x+(($dx>0)?1:-1),$this->y);
		else	$returnpos = array($this->x,$this->y+(($dy>0)?1:-1));
		
		// check reachability, reset target if not reachable
		if ($data[kHellHole1_Data_Mode] == kHellHole1_Mode_Plan) {
			// check way to target, and way back
			$movablemask = intval($gUnitType[$this->type]->movable_flag) & intval($gUnitType[$this->type2]->movable_flag);
			if (!Reachable($exitbase_pos[0],$exitbase_pos[1],$epos[0],$epos[1],$movablemask,false) ||
				!Reachable($epos[0],$epos[1],$exitbase_pos[0],$exitbase_pos[1],$movablemask,false)) 
				{ $this->SaveData(); return; } // target unreachable
			$data[kHellHole1_Data_Mode] = kHellHole1_Mode_Siege;
			echo "target reachability verifyed<br>";
		}
		
		// increment think counter
		if (++$data[kHellHole1_Data_ThinkCount] > $this->maxcount_think)
			 { $this->SaveData(); return; }
		else   $this->SaveData($data);
		
		// list troups
		$ramme = false;
		$monsters = sqlgettable("SELECT * FROM `army` WHERE `hellhole` = ".$this->id." ORDER BY `id`");
		foreach ($monsters as $o) if (cUnit::GetUnitsMaxType(cUnit::GetUnits($o->id)) == $this->type2) {$ramme = $o;break;}
				
		if ($data[kHellHole1_Data_Mode] == kHellHole1_Mode_Siege) {
			echo "mode:siege<br>";
			if (!$ramme) {
				// increment siege counter
				if (++$data[kHellHole1_Data_SiegeCount] > $this->maxcount_siege)
					 { $this->SaveData(); return; }
				else   $this->SaveData($data);
				
				// siege just started
				echo "start new siege<br>";
				$ramme = cArmy::SpawnArmy($this->x,$this->y,cUnit::Simple($this->type2,$this->armysize2),
					false,-1,0,0,$this->id,false,kArmyFlag_SiegeBlockingBuilding);
				if (!$ramme) return;
				
				// exit the base, and then siege to one step bevore the target and then return, so the path back is also cleared
				echo "sending ramme to ".$epos[0].",".$epos[1]."<br>";
				cPath::ArmySetRouteTo($ramme->id,$exitbase_pos[0],$exitbase_pos[1]); // pathfinding out
				cArmy::ArmySetWaypoint($ramme,$epos[0],$epos[1]); // straight path (siege anything)
				cArmy::ArmySetWaypoint($ramme,$exitbase_pos[0],$exitbase_pos[1]); // straight path (siege anything)
			}
			
			// ramme arrived at destination
			if ($ramme && !sqlgetone("SELECT 1 FROM `waypoint` WHERE `army` = ".intval($ramme->id))) {
				echo "Path has been cleared ! switching to raid mode<br>";
				$data[kHellHole1_Data_Mode] = kHellHole1_Mode_Raid;
				$this->SaveData($data);
				// kill ramme, not needed anymore
				if ($ramme) cArmy::DeleteArmy($ramme);
			}
		}
		
		if ($data[kHellHole1_Data_Mode] == kHellHole1_Mode_Raid) {
			echo "mode:raid<br>";
			// only the first(=oldest,order_by_id) non-ramme monster is going on raids, to ensure the way back is free =)
			$raider = false;
			foreach ($monsters as $o) if ($o->id != $ramme->id) { $raider = $o; break; }
			if (!$raider) break;
			
			if (!sqlgetone("SELECT 1 FROM `waypoint` WHERE `army` = ".intval($raider->id))) {
				// increment raid counter
				if (++$data[kHellHole1_Data_RaidCount] > $this->maxcount_raid)
					 { $this->SaveData(); return; }
				else   $this->SaveData($data);
				
				// start new raid, dump res and items before going on raid
				echo "start new raid<br>";
				cItem::dropAll($raider);
				global $gRes2ItemType;
				global $gRes;
				foreach($gRes as $n=>$f) {
					cItem::SpawnItem($raider->x,$raider->y,$gRes2ItemType[$f],$raider->$f);
					sql("UPDATE `army` SET `$f` = 0 WHERE `id` = ".$raider->id);
				}
				
				// set raid path and flags
				$raiderflags = kArmyFlag_AutoPillage|kArmyFlag_HarvestField|kArmyFlag_AutoAttack;
				sql("UPDATE `army` SET `flags` = ".$raiderflags." WHERE `id` = ".$raider->id);
				cPath::ArmySetRouteTo($raider->id,$exitbase_pos[0],$exitbase_pos[1]); // pathfinding out
				cArmy::ArmySetWaypoint($raider,$epos[0],$epos[1]); // straight path
				cArmy::ArmySetWaypoint($raider,$exitbase_pos[0],$exitbase_pos[1]); // straight path
				cPath::ArmySetRouteTo($raider->id,$returnpos[0],$returnpos[1]); // pathfinding back in
			}
		}
	}
}


//********************[ Hellhole_2 ]*************************************************************
// spawn horde around boss...  mainly used for megablob, boss and minions should be able to walk on all terrain...
//*********************************************************************************************

class Hellhole_2 extends Hellhole_0 {
	function Hellhole_2 () {
		// $this->spawndelay = 3600; // should be about one hour
		$this->search_building_rad = 30; // todo : unhardcode , choosing player buildings only within this range
		$this->victim_minpts = 100000; // todo : unhardcode? // don't attack players below a certain limit
		$this->spawncount = 6; // todo : unhardcode? // so many monsters are spawned at once
	}
	function SearchNewTarget () {
		echo "searching for new target<br>";
		global $gUnitType;
		$r = $this->search_building_rad;
		$x = $this->x+rand(-$r,$r);
		$y = $this->y+rand(-$r,$r);
		$movablemask = intval($gUnitType[$this->type2]->movable_flag);
		$building = Reachable($this->x,$this->y,$x,$y,$movablemask,true);
		if ($building === true) {
			echo "found free space at ".pos2txt($x,$y).";<br>";
			return array($x,$y);
		}
		if (!$building || $building->user == 0) return false; // no building found
		if (sqlgetone("SELECT `general_pts` FROM `user` WHERE `id` = ".$building->user) < $this->victim_minpts) return false; // too weak
		echo "found new target : ".opos2txt($building)." of ".nick($building->user)."<br>"; 
		return array($building->x,$building->y);
	}
	
	function Think () {
		global $gUnitType;
		$time = time();
		$this->spawntime = $time + $this->spawndelay;
		sql("UPDATE `hellhole` SET `spawntime` = ".$this->spawntime." WHERE `id` = ".$this->id." LIMIT 1");
		
		// check for boss
		$boss = ($this->ai_data!="") ? sqlgetobject("SELECT * FROM `army` WHERE `id` = ".intval($this->ai_data)) : false;
		if (!$boss) { $this->BossDied(); return; }
		
		// boss dies randomly about once every month, and is checked about every day for being still on the map
		if (rand(0,60*24) == 0) {
			$bounds = sqlgetobject("SELECT MIN(`x`) as minx,MAX(`x`) as maxx,MIN(`y`) as miny,MAX(`y`) as maxy FROM `building`");
			if ($boss->x < $bounds->minx || $boss->y < $bounds->miny ||
				$boss->x > $bounds->maxx || $boss->y > $bounds->maxy || rand(0,31) == 0)
				cArmy::DeleteArmy($boss);
		}
		
		// if boss has nowhere to go, search for one of the big players =)
		if (!sqlgetone("SELECT 1 FROM `waypoint` WHERE `army` = ".intval($boss->id))) {
			echo "boss needs new target<br>";
			$pos = $this->SearchNewTarget();
			if ($pos) cArmy::ArmySetWaypoint($boss,$pos[0],$pos[1]); // straight path (siege anything)
		}
		
		// move hellhole to boss
		$this->x = $boss->x;
		$this->y = $boss->y;
		sql("UPDATE `hellhole` SET ".arr2sql(array("x"=>$this->x,"y"=>$this->y))." WHERE `id` = ".intval($this->id));
		
		// spawn 4 monsters, a little bit away from boss
		$monsterflags = kArmyFlag_SiegeBlockingBuilding|kArmyFlag_AutoSiege|kArmyFlag_AutoAttack|kArmyFlag_Wander;
		$monstercount = sqlgetone("SELECT COUNT(`id`) FROM `army` WHERE `hellhole` = ".$this->id);
		$spawnplaces = array(array(-2,2),array(2,-2),array(-2,-2),array(2,2));
		$spawnplaces = array_merge($spawnplaces,$spawnplaces,$spawnplaces); // 3 times per spawnplace
		shuffle($spawnplaces);
		$s = 0;
		foreach ($spawnplaces as $add) {
			if ($monstercount > $this->num) break;
			if ($s >= $this->spawncount) break;
			if (cArmy::SpawnArmy($this->x + $add[0],$this->y + $add[1],cUnit::Simple($this->type,$this->armysize),
						false,-1,0,0,$this->id,true,$monsterflags)) {++$monstercount; ++$s;}
		}
		
		// delete monsters directly next to boss, so he can move freely, and monsters out of radius
		$this->monsters = sqlgettable("SELECT * FROM `army` WHERE `hellhole` = ".$this->id);
		foreach ($this->monsters as $o) {
			if ($o->id == $boss->id) continue; // don't kill boss =)
			$dist = hypot($o->x-$this->x,$o->y-$this->y); // hypotenuse : the long side of a orthogonal triangle
			if ($dist <= 1) cArmy::DeleteArmy($o,true); // delete monsters directly next to boss, so he can move freely
			if ($dist > $this->radius) cArmy::DeleteArmy($o,true); // delete monsters that went too far away
			// todo : leave schlimetrace for the latter ones
			// todo : burned earth, regenerates like young forest
		}
		
	}
	
	function BossDied () {
		// all minions die
		echo "boss is dead, all minions die<br>";
		$monsters = sqlgettable("SELECT * FROM `army` WHERE `hellhole` = ".$this->id);
		foreach ($monsters as $o) cArmy::DeleteArmy($o);
			
		// search random startplace and spawn new boss...
		$bounds = sqlgetobject("SELECT MIN(`x`) as minx,MAX(`x`) as maxx,MIN(`y`) as miny,MAX(`y`) as maxy FROM `building`");
		for($i=0;$i<10;++$i){
			$d = 10;
			$x = rand($bounds->minx,$bounds->maxx);
			$y = rand($bounds->miny,$bounds->maxy);
			$count = sqlgetone("SELECT COUNT(`id`) FROM `building` WHERE 
				`x` >= (".($x-$d).") AND `x` <= (".($x+$d).") AND 
				`y` >= (".($y-$d).") AND `y` <= (".($y+$d).")");
			if ($count == 0) {
				// free space found =)
				$this->bosstype = $this->type2;
				$this->bosscount = $this->armysize2;
				$bossunits = cUnit::Simple($this->bosstype,$this->bosscount);
				$bossflags = kArmyFlag_AttackBlockingArmy|kArmyFlag_AutoAttack|kArmyFlag_Wander;
				if (cUnit::GetUnitsSiegeAttack($bossunits,0) > 0) $bossflags |= kArmyFlag_SiegeBlockingBuilding;
				$boss = cArmy::SpawnArmy($x,$y,$bossunits,false,-1,0,0,$this->id,true,$bossflags);
				if ($boss) $this->SaveData(array(0=>$boss->id));
				if ($boss) echo "spawned new boss at $boss->x,$boss->y<br>";
				$this->x = $boss->x;
				$this->y = $boss->y;
				sql("UPDATE `hellhole` SET ".arr2sql(array("x"=>$this->x,"y"=>$this->y))." WHERE `id` = ".intval($this->id));
				return;
			}
		}
	}
}



//********************[ Hellhole_3 ]*************************************************************
// ant-hole
// uses first unittype (ant) for siege & pillage 
// uses second unittype (ant-king) for spreading
// ressources that are brought back are piled onto the ant-hole
// when a certain ressource amount is reached, and ant-king is sent out to create a new ant-hole with the same type of building as this one
//*********************************************************************************************

class Hellhole_3 extends Hellhole_0 {
	function Hellhole_3 () {
		// $this->spawndelay = 3600; // should be about one hour, so the hellhole can abort siege-pillaging ants
		$this->raid_rad = 40; // maximal-travel-radius for soldiers
		$this->spread_rad = 40; // maximal travel-radius for king
		$this->spread_mindist = 11; // minimum distance of new base to existing bases
		$this->victim_minpts = 30000; // don't SPREAD near players below a certain limit
	}
	
	// check if the location of a new base is ok
	function CheckSpreadPoint ($x,$y) {
		global $gTerrainType;
		$terrain = cMap::StaticGetTerrainAtPos($x,$y);
		$terrain_is_ok = intval($gTerrainType[$terrain]->movable_flag) & (kTerrain_Flag_Moveable_Land|kTerrain_Flag_Moveable_Wood);
		if (!$terrain_is_ok) { echo "terrain nicht ok<br>"; return false; }
		
		$r = $this->spread_mindist;
		$xylimit = "`x` >= ".($x-$r)." AND `x` <= ".($x+$r)." AND `y` >= ".($y-$r)." AND `y` <= ".($y+$r);
		if (sqlgetone("SELECT 1 FROM `hellhole` WHERE `ai_type` = ".intval($this->ai_type)." AND ".$xylimit." LIMIT 1")) {
			echo "schon ein hellhole in der naehe<br>"; 
			return false;
		}
		$users = sqlgetonetable("SELECT `user` FROM `building` WHERE ".$xylimit." GROUP BY `user`");
		foreach ($users as $userid) if ($userid > 0)
			if (sqlgetone("SELECT `general_pts` FROM `user` WHERE `id` = ".intval($userid)) < $this->victim_minpts) {
				echo "schwacher spieler ($userid) in der naehe<br>"; 
				return false;
			}
		return true;
	}
	
	function BuildNewHellhole ($king) {
		global $gBuildingType;
		if (!$king) return;
		cArmy::DeleteArmy($king,true,"Ein neuer Ameisenbau wurde gegründet....");
		
		// DESIGN-PATTERN : PROTOTYPE =)
		$oldbuilding = sqlgetobject("SELECT * FROM `building` WHERE `x` = ".intval($this->x)." AND `y` = ".intval($this->y));
		if (!$oldbuilding) return;
		
		$newbuilding = $oldbuilding;
		unset($newbuilding->id);
		$newbuilding->x = $king->x;
		$newbuilding->y = $king->y;
		$newbuilding->hp = $gBuildingType[$newbuilding->type]->maxhp;
		sql("INSERT INTO `building` SET ".obj2sql($newbuilding));
		
		$oldhellhole = sqlgetobject("SELECT * FROM `hellhole` WHERE `id` = ".intval($this->id));
		if (!$oldhellhole) return;
		
		$newhellhole = $oldhellhole;
		unset($newhellhole->id);
		$newhellhole->x = $king->x;
		$newhellhole->y = $king->y;
		sql("INSERT INTO `hellhole` SET ".obj2sql($newhellhole));
		
		echo "Its a small step for an AntKing, but a great leap for Antinity !<br>";
	}
	
	function Think () {
		global $gUnitType,$gRes2ItemType,$gRes,$gUser;
		
		// need to collect at least this many ressources before spreading
		$this->spread_min_respoints = $gUnitType[$this->type]->last * $this->armysize * 10; // 10 runs
		
		$x = $this->x;
		$y = $this->y;
		
		$time = time();
		$this->spawntime = $time + $this->spawndelay;
		sql("UPDATE `hellhole` SET `spawntime` = ".$this->spawntime." WHERE `id` = ".$this->id." LIMIT 1");
		
		$this->SpawnMonster(
			kArmyFlag_Wander		|
			kArmyFlag_RunToEnemy	|
			kArmyFlag_AutoAttack	|
			kArmyFlag_HarvestForest	| // they sure are hungry ...
			kArmyFlag_HarvestRubble	|
			kArmyFlag_HarvestField	|
			kArmyFlag_AlwaysCollectItems| // finders keepers ;)
			kArmyFlag_AutoSiege			| // deactivated for abort and return
			kArmyFlag_StopSiegeWhenFull	| // stop and return
			kArmyFlag_SiegePillage
			);
			
		// list troups
		$king = false;
		$raider = false;
		$potential_raiders = array();
		$monsters = sqlgettable("SELECT * FROM `army` WHERE `hellhole` = ".$this->id);
		foreach ($monsters as $k => $o) {	
			$o->wpcount = intval(sqlgetone("SELECT COUNT(*) FROM `waypoint` WHERE `army` = ".$o->id));
			$monsters[$k]->wpcount = $o->wpcount;
			
			$o->units = cUnit::GetUnits($o->id);
			$monsters[$k]->units = $o->units;
			
			if (cUnit::GetUnitsMaxType($o->units) == $this->type2) {
				$king = $o;
			} else {
				$potential_raiders[] = $o;
				if ($o->wpcount > 0) $raider = $o;
			}
		}
		
		// raider-code
		if ($raider) {
			if (isset($gUser)) echo "raider aktiv ".opos2txt($raider)."<br>";
			/*
			if (intval($raider->flags) & kArmyFlag_AutoSiege) {
				// moving out
				echo "raider is moving out<br>";
			} else {
				// coming back
				echo "raider is coming back<br>";
			}*/
		} else if (count($potential_raiders) > 0) {
			// take ressources of potential raiders
			foreach ($potential_raiders as $army) {
				// put res onto bughole
				$myres = $gRes;
				foreach($myres as $n=>$f) {
					$drop = $army->{$f};
					if ($drop > 0) {
						sql("UPDATE `army` SET `$f` = GREATEST(0,`$f` - $drop) WHERE `$f` >= $drop AND `id` = ".$army->id);
						if (mysql_affected_rows() > 0) {
							if (isset($gUser)) echo "ressource collected : $drop $n from ".opos2txt($army)."<br>";
							cItem::SpawnItem($x,$y,$gRes2ItemType[$f],$drop);
						}
					}
				}
			}
		
			// send out new raider
			$raider = $potential_raiders[array_rand($potential_raiders)];
			if (isset($gUser)) echo "send out new raider ? ".opos2txt($raider)."<br>";
			
			// pick random location
			$dist = rand ( $this->rad , $this->raid_rad );
			$ang = 2.0 * M_PI * ((float)rand() / (float)getrandmax());
			$tx = round($x + $dist * sin($ang));
			$ty = round($y + $dist * cos($ang));
			
			// check if the location is ok and reachable
			$position_ok = false;
			//if ($this->CheckRaidPoint($tx,$ty)) {
			if (1) {
				$reachable = Reachable($raider->x,$raider->y,$tx,$ty,$gUnitType[$this->type]->movable_flag,true,true);
				if ($reachable === true) {
					$position_ok = true;
				} else if ($reachable) {
					// we hit a building on the way, consider to siege it instead...
					$building = $reachable;
					$points = sqlgetone("SELECT `general_pts` FROM `user` WHERE `id` = ".intval($building->user));
					if ($points >= $this->victim_minpts) {
						// target a position right next to the building
						$dx = $building->x - $raider->x;
						$dy = $building->y - $raider->y;
						if (abs($dx) > abs($dy)) {
							$tx = $building->x + (($dx>0)?-1:1);
							$ty = $building->y;
						} else {
							$tx = $building->x;
							$ty = $building->y + (($dy>0)?-1:1);
						}
						// check if new target pos can be reached
						$reachable = Reachable($raider->x,$raider->y,$tx,$ty,$gUnitType[$this->type]->movable_flag,true,true);
						if ($reachable === true) {
							$position_ok = true;
						}
					}
				}
			}
			
			if ($position_ok) {
				echo "FOR KING AND COUNTRY ! CHAAAAAAAARGE !!!!<br>";
				// and send him on his way
				cArmy::ArmySetWaypoint($raider,$tx,$ty);
				cArmy::ArmySetWaypoint($raider,$raider->x,$raider->y);
			}
		}
		
		// king-code
		$kingtick = true; // todo : only every x hours...
		if ($kingtick) {
			if (!$king) {
				if (count($gRes2ItemType) > 0)
						$myresitems = sqlgettable("SELECT * FROM `item` WHERE `x` = ".intval($x)." AND `y` = ".intval($y)." AND `type` IN (".implode(",",$gRes2ItemType).")");
				else	$myresitems = array();
				shuffle($myresitems);
				$myrespoints = 0;
				foreach ($myresitems as $o) $myrespoints += $o->amount;
				
				echo "respoints : ".kplaintrenner($myrespoints)." / ".kplaintrenner($this->spread_min_respoints)."<br>";
		
				if ($myrespoints > $this->spread_min_respoints) {
					echo "create a king<br>";
						
					// create a king
					$kingflags = kArmyFlag_AutoAttack | kArmyFlag_Wander;
					$king = cArmy::SpawnArmy($x,$y,cUnit::Simple($this->type2,$this->armysize2),
										false,kArmyType_Normal,0,0,$this->id,false,$kingflags);
					
					if ($king) {
						echo "Long live the AntKing !<br>";
						// load him full of ressources
						foreach ($myresitems as $o) 			
							cItem::pickupItem($o,$king->id,-1,true);
						// let him run around for a while, and try to send him every following kingtick
					}
				} else {
					echo "Still hungry for more ressources...<br>";
				}
			} else if ($king) {
				if (intval($king->flags) & kArmyFlag_Wander) {
					// the king is still around the base, try to send him away
					
					// pick random location
					$dist = rand ( $this->spread_mindist , $this->spread_rad );
					$ang = 2.0 * M_PI * ((float)rand() / (float)getrandmax());
					$tx = round($x + $dist * sin($ang));
					$ty = round($y + $dist * cos($ang));
					
					echo "time to boldly go, where no ant has gone before...($tx,$ty) <br>";
					
					// check if the location is ok and reachable
					if ($this->CheckSpreadPoint($tx,$ty)) {
						$reachable = Reachable($king->x,$king->y,$tx,$ty,$gUnitType[$this->type2]->movable_flag,true,true);
						if ($reachable === true) {
							echo "and onward he went, on a journey of kings...<br>";
							
							// and send him on his way
							cArmy::ArmySetWaypoint($king,$tx,$ty);
							
							// dont wander anymore, also used for checking if he is on his way
							$king->flags = $king->flags & (~kArmyFlag_Wander);
							sql("UPDATE `army` SET `flags` = ".intval($king->flags)." WHERE `id` = ".intval($king->id));
						} else {
							echo "Reachable($king->x,$king->y,$tx,$ty,".$gUnitType[$this->type2]->movable_flag.") nicht ok<br>";
						}
					} else {
						echo "CheckSpreadPoint($tx,$ty) nicht ok<br>";
					}
				} else if (!sqlgetone("SELECT 1 FROM `waypoint` WHERE `army` = ".$king->id)) {
					// the king has arrived ;)
					echo "HOMERUN !!!<br>";
					$this->BuildNewHellhole($king);
				}
			}
		}
	}
}



?>