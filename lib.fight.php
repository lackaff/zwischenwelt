<?php
require_once("lib.army.php");
require_once("lib.spells.php");

// attempts to use $gAllUsers cache

class cFight {

	//simply increase the amount of registered kills of one unittype of the user with userid
	static function AddUserkills($userid,$unittypeid,$kills){
		if($unittypeid == 0 || $kills == 0)return;
		//echo "static function AddUserkills($userid,$unittypeid,$kills)<br>\n";
		$userid = intval($userid);
		$unittypeid = intval($unittypeid);
		$kills = floatval($kills);
		
		sql("UPDATE `userkills` SET `kills`=`kills`+$kills WHERE `user`=$userid AND `unittype`=$unittypeid");
		if(mysql_affected_rows()<=0)
			sql("REPLACE INTO `userkills` SET `kills`=$kills , `user`=$userid , `unittype`=$unittypeid");
		//echo "user $userid gets $kills\n";
	}

	//increase the kills for a user (userid), units is a list of killed unittypes
	static function AddUserkillsFromKilledUnits($userid,$units){
		foreach($units as $x)
			cFight::AddUserkills($userid,$x->type,$x->amount);
	}

	// $army must be object, replaces _ARMYNAME_ and _ARMYOWNERNAME_ in $why
	static function StopAllArmyFights ($army,$why) {
		global $gNumber2ContainerType,$gContainerType2Number;
		$armyownername = cArmy::GetArmyOwnerName($army);
		$why = strtr($why,array("_ARMYNAME_"=>$army->name,"_ARMYOWNERNAME_"=>$armyownername));
		$fights = sqlgettable("SELECT * FROM `fight` WHERE `attacker` = ".$army->id." OR `defender` = ".$army->id);
		$pillages = sqlgettable("SELECT * FROM `pillage` WHERE `army`=".$army->id);
		$sieges = sqlgettable("SELECT * FROM `siege` WHERE `army`=".$army->id);
		$shootings = sqlgettable("SELECT * FROM `shooting` WHERE 
			(`attacker`=".$army->id." AND `attackertype` = ".$gContainerType2Number[kUnitContainer_Army].") OR 
			(`defender`=".$army->id." AND `defendertype` = ".$gContainerType2Number[kUnitContainer_Army].")");
		foreach ($fights as $o) cFight::EndFight($o,$why);
		foreach ($pillages as $o) cFight::EndPillage($o,$why);
		foreach ($sieges as $o) cFight::EndSiege($o,$why);
		foreach ($shootings as $o) cFight::EndShooting($o,$why);
	}
	
	// $building must be object, replaces _BUILDINGTYPE_ , _x_ , _y_ , _BUILDINGOWNERNAME_ in $why
	static function StopAllBuildingFights ($building,$why) {
		global $gContainerType2Number,$gNumber2ContainerType,$gUnitType,$gBuildingType,$gArmyType;
		$buildingownername = $building->user ? sqlgetone("SELECT `name` FROM `user` WHERE `id` = ".$building->user) : "Server";
		$why = strtr($why,array("_BUILDINGTYPE_"=>$gBuildingType[$building->type]->name,
			"_x_"=>$building->x,"_y_"=>$building->y,"_BUILDINGOWNERNAME_"=>$buildingownername));
		$pillages = sqlgettable("SELECT * FROM `pillage` WHERE `building`=".$building->id);
		$sieges = sqlgettable("SELECT * FROM `siege` WHERE `building`=".$building->id);
		$shootings = sqlgettable("SELECT * FROM `shooting` WHERE 
			(`attacker`=".$building->id." AND `attackertype` = ".$gContainerType2Number[kUnitContainer_Building].") OR 
			(`defender`=".$building->id." AND `defendertype` = ".$gContainerType2Number[kUnitContainer_Building].")");
		foreach ($pillages as $o) cFight::EndPillage($o,$why);
		foreach ($sieges as $o) cFight::EndSiege($o,$why);
		foreach ($shootings as $o) cFight::EndShooting($o,$why);
	}
	
	// get a textual representation of army/building, used for ShootingStep,SendFightReport
	// todo : replace GetArmyOwnerName with me
	static function GetContainerText ($containerobj,$containertype=kUnitContainer_Army) {
		global $gContainerType2Number,$gNumber2ContainerType,$gUnitType,$gBuildingType,$gArmyType;
		if (is_numeric($containertype)) $containertype = $gNumber2ContainerType[$containertype];
		if ($containerobj && !is_object($containerobj)) $containerobj = sqlgetobject("SELECT * FROM `".addslashes($containertype)."` WHERE `id` = ".intval($containerobj));
		if (empty($containerobj)) return "unknown_unit_container";
		if ($containertype == kUnitContainer_Army)		$text = $gArmyType[$containerobj->type]->name." ".$containerobj->name;
		if ($containertype == kUnitContainer_Building)	$text = $gBuildingType[$containerobj->type]->name." Stufe ".$containerobj->level;
		$text .= " bei ($containerobj->x,$containerobj->y)";
		if ($containerobj->user) $text .= " von ".sqlgetone("SELECT `name` FROM `user` WHERE `id` = ".$containerobj->user);
		if ($containertype == kUnitContainer_Army && $containerobj->hellhole) {
			$hellhole = sqlgetobject("SELECT * FROM `hellhole` WHERE `id` = ".intval($containerobj->hellhole));
			if ($hellhole) $text .= "  von (".$hellhole->x.",".$hellhole->y.")";
		}
		return $text;
	}
		
		
	// ##### ##### ##### ##### ##### ##### ##### #####
	// ##### #####    Shooting  ##### ##### ##### #####
	// ##### ##### ##### ##### ##### ##### ##### #####
	
	// NOTE : attackertype,defendertype and ctype in this section refers to the "containertype", eg army or building, see kUnitContainer_Army and related constants
	
	// only performs type-check, not range check (damage-based)
	// leave defenderobj = false and set to test if attackerobj can shoot at anything of ctype $defendertype (building or army)
	static function CanShoot ($attackerobj,$attackertype=kUnitContainer_Army,$defenderobj=false,$defendertype=kUnitContainer_Army) {
		global $gContainerType2Number,$gNumber2ContainerType;
		$attackertype = is_numeric($attackertype)?$gNumber2ContainerType[$attackertype]:$attackertype;
		$defendertype = is_numeric($defendertype)?$gNumber2ContainerType[$defendertype]:$defendertype;
		
		if (empty($attackerobj)) return false;
		if ($defendertype == kUnitContainer_Army && $defenderobj && $defenderobj->type != kArmyType_Normal) return false;
		return true;
	}
	
	
	// general shooter intelligence : look for potential targets, start shootings, choose one active shooting, and shoot
	static function ThinkShooting ($attackerobj,$attackertype,$debug=false) {
		if (empty($attackerobj)) return false;
		global $gContainerType2Number,$gNumber2ContainerType;
		global $gBuildingType,$gArmyType;
		global $gArmyShootings; // TODO : use this caching if available
		$attackertype = is_numeric($attackertype)?$gNumber2ContainerType[$attackertype]:$attackertype;
		
		if (!isset($attackerobj->units)) $attackerobj->units = cUnit::GetUnits($attackerobj->id,$attackertype);
		$r = cUnit::GetUnitsMaxRange($attackerobj->units);
		if ($r <= 0) return false;
		
		$time = time();
		$x = $attackerobj->x;
		$y = $attackerobj->y;
		$cooldown = cUnit::GetDistantCooldown($attackerobj->units);
		$myshootings = false;
		if ($attackertype == kUnitContainer_Army && isset($gArmyShootings)) { // only use shootingcache ($gArmyShootings) if it is set
			$myshootings2 = ($gArmyShootings && isset($gArmyShootings[$attackerobj->id])) ? $gArmyShootings[$attackerobj->id] : array();
			$myshootings = array(); // grouped by defendertype , firstindex=defendertype secondindex=defender
			foreach ($myshootings2 as $o) {
				if (!isset($myshootings[$o->defendertype])) $myshootings[$o->defendertype] = array();
				$myshootings[$o->defendertype][$o->defender] = $o;
			}
		}
		if (empty($myshootings)) {
			$myshootings = sqlgetgrouptable("SELECT * FROM `shooting` WHERE 
				`attacker` = ".$attackerobj->id." AND 
				`attackertype` = ".$gContainerType2Number[$attackertype],"defendertype","defender");
		}
		
		$lastshot = 0;
		foreach ($myshootings as $ctype => $arr) foreach ($arr as $o) $lastshot = max($lastshot,$o->lastshot);
		
		if ($debug) echo "ThinkShooting : attacker=".$attackertype."[".$attackerobj->id."]($x,$y),cooldown=$cooldown,r=$r,lastshot=".date("d.m.Y H:i:s",$lastshot)."<br>";
		
		if ($lastshot > 0 && $time - $lastshot < $cooldown) return false; // not ready yet, still cooling down
		if ($attackertype == kUnitContainer_Army && $attackerobj->idle < $cooldown) return false; // not ready yet, still cooling down
		if ($attackertype == kUnitContainer_Army && kProfileArmyLoop) LoopProfiler("armyloop:shooting");
		
		// TODO : use hasDistantAttack ??? combine with CanShoot ??
		
		if ($attackertype == kUnitContainer_Building) {
			$autosiege			= intval($attackerobj->flags) & kBuildingFlag_AutoShoot_Enemy;
			$autoshoot_enemy	= intval($attackerobj->flags) & kBuildingFlag_AutoShoot_Enemy;
			$autoshoot_stranger	= intval($attackerobj->flags) & kBuildingFlag_AutoShoot_Strangers;
			$canshoot_armies	= intval($gBuildingType[$attackerobj->type]->flags) & kBuildingTypeFlag_CanShootArmy;
			$canshoot_buildings	= intval($gBuildingType[$attackerobj->type]->flags) & kBuildingTypeFlag_CanShootBuilding;
		} else if ($attackertype == kUnitContainer_Army) {
			$autosiege 			= intval($attackerobj->flags) & kArmyFlag_AutoSiege;
			$autoshoot_enemy	= intval($attackerobj->flags) & kArmyFlag_AutoShoot_Enemy;
			$autoshoot_stranger	= intval($attackerobj->flags) & kArmyFlag_AutoShoot_Strangers;
			$rangedsiegedmg = cUnit::GetUnitsRangedSiegeDamage($attackerobj->units);  // use only for armies
			$canshoot_armies	= $attackerobj->type != kArmyType_Siege; // TODO : unhardcode
			$canshoot_buildings	= $rangedsiegedmg > 0;
		} else return false; // SHOULD NOT HAPPEN
		
		if (!$canshoot_armies && !$canshoot_buildings) return false;
		
		if ($debug) echo "ThinkShooting : rangedsiegedmg=$rangedsiegedmg,
			autosiege=".($autosiege?1:0).",
			autoshoot_enemy=".($autoshoot_enemy?1:0).",
			autoshoot_stranger=".($autoshoot_stranger?1:0).",
			canshootarmies=".($canshoot_armies?1:0).",
			canshootbuildings=".($canshoot_buildings?1:0)."<br>";
		
		if ($autosiege || $autoshoot_enemy || $autoshoot_stranger) {
			// search for new targets
			$xylimit = "`x` >= ".($x-$r)." AND `x` <= ".($x+$r)." AND 
						`y` >= ".($y-$r)." AND `y` <= ".($y+$r);
			$nearstuff = array();
			if ($canshoot_armies)		$nearstuff[kUnitContainer_Army]		= sqlgettable("SELECT * FROM `army` WHERE ".$xylimit);
			if ($canshoot_buildings)	$nearstuff[kUnitContainer_Building]	= sqlgettable("SELECT * FROM `building` WHERE ".$xylimit);
			foreach ($nearstuff as $ctype => $arr) foreach ($arr as $o) {
				$ctypenum = $gContainerType2Number[$ctype];
				if ($debug) echo "checking near : $ctype,".oposinfolink($o)."<br>";
				if (isset($myshootings[$ctypenum]) && isset($myshootings[$ctypenum][$o->id])) {
					if ($debug) echo "already added<br>";
					continue; // already added
				}
				if (cUnit::GetDistantDamage($attackerobj->units,$o->x-$x,$o->y-$y) <= 0) {
					if ($debug) echo "out of range<br>";
					continue; // out of range
				}
				// now check fof
				$fof = GetFOF($attackerobj->user,$o->user);
				if ($ctype == kUnitContainer_Building && IsFriendlyServerBuilding($o)) 
					$fof = kFOF_Friend;
				
				
				if ($debug) echo "checking near : $ctype,".oposinfolink($o)." in range, fof=$fof <br>";
				if ($fof == kFOF_Friend) continue;
				if (IsInSameGuild($attackerobj->user,$o->user)) continue;
				
				// TODO : insameguild, is hellhole building...
				
				$attack = false;
				if ($fof == kFOF_Enemy && $autosiege && $canshoot_buildings && $ctype == kUnitContainer_Building) $attack = true;
				if ($fof == kFOF_Enemy && $autoshoot_enemy)		$attack = true;
				if ($fof != kFOF_Enemy && $autoshoot_stranger)	$attack = true;
				// start the attack (add to myshootings to consider it right away for next shot
				if ($attack) {
					if ($debug) echo "start shooting at $ctype,".oposinfolink($o)."<br>";
					if (!isset($myshootings[$ctypenum])) $myshootings[$ctypenum] = array();
					$newshooting = cFight::StartShooting($attackerobj->id,$attackertype,$o->id,$ctype,true,$attackerobj,$o);
					if ($newshooting) $myshootings[$ctypenum][] = $newshooting;
				}
			}
		}
		
		if ($attackertype == kUnitContainer_Army && (intval($attackerobj->flags) & kArmyFlag_HoldFire)) return false;
		
		// now choose where to shoot next (go for most damage, to use different unit types effectively)
		$found_maxdmg = 0;
		$found_shooting = false;
		$found_target = false;
		foreach ($myshootings as $ctypenum => $arr) foreach ($arr as $o) {
			$ctype = $gNumber2ContainerType[$ctypenum];
			$target = sqlgetobject("SELECT * FROM `". $ctype."` WHERE `id` = ".$o->defender);
			if (empty($target)) {
				// dead
				cFight::EndShooting($o,"Ziel verschwunden");
				continue;
			}
			if ($attackertype == kUnitContainer_Building) {
				$dmg = ($ctype == kUnitContainer_Building) ? cUnit::GetUnitsRangedSiegeDamage($attackerobj->units,$target->x-$x,$target->y-$y) : cUnit::GetDistantDamage($attackerobj->units,$target->x-$x,$target->y-$y);
			} else if ($attackertype == kUnitContainer_Army) {
				$dmg = ($ctype == kUnitContainer_Building) ? cUnit::GetUnitsRangedSiegeDamage($attackerobj->units,$target->x-$x,$target->y-$y) : cUnit::GetDistantDamage($attackerobj->units,$target->x-$x,$target->y-$y);
				
			} else return false; // SHOULD NOT HAPPEN
			
			// siege-modifier
			if ($ctype == kUnitContainer_Building) $dmg = $dmg / 3600.0 * 60.0;
			
			//change damage depending on the path the bullet pased
			//$dmg *= GetDistantMod($attackerobj->x,$attackerobj->y,$defenderobj->x,$defenderobj->y);
			//$dmg *= rand(80,100)/100.0;
			
			if ($debug) echo "considering at shooting  $ctype,".oposinfolink($target)." : dmg=$dmg<br>";
			if ($found_maxdmg < $dmg) {
				$found_maxdmg = $dmg;
				$found_shooting = $o;
				$found_target = $target;
			}
		}
		if ($found_shooting) {
			if ($debug) echo "DECIDED to shoot at ".oposinfolink($found_target)." : dmg=$found_maxdmg<br>";
			cFight::ShootingStep($found_shooting,$attackerobj,$found_target,$found_maxdmg);
			
			if ($attackertype == kUnitContainer_Army) {
				// todo : getexp,frags,trainelites  ... ShootingStep returns array of killed units ?
				sql("UPDATE `army` SET ".arr2sql(array("nextactiontime"=>($time+$cooldown),"idle"=>0))." WHERE `id` = ".intval($attackerobj->id));		
			}
			
			return true;
		}
		return false;
	}
	
	
	
	
	
	static function StartShooting ($attacker,$attackertype,$defender,$defendertype,$autocancel=false,$attackerobj=false,$defenderobj=false) {
		global $gContainerType2Number,$gNumber2ContainerType;
		$shooting = new EmptyObject();
		$shooting->attacker = $attacker;
		$shooting->defender = $defender;
		$shooting->attackertype = is_numeric($attackertype)?$attackertype:$gContainerType2Number[$attackertype];
		$shooting->defendertype = is_numeric($defendertype)?$defendertype:$gContainerType2Number[$defendertype];
		$attackertype = $gNumber2ContainerType[$shooting->attackertype];
		$defendertype = $gNumber2ContainerType[$shooting->defendertype];
		$shooting->start = time();
		$shooting->lastshot = 0;
		$shooting->autocancel = intval($autocancel);
		
		// hack : unshootable types
		if (empty($attackerobj)) $attackerobj = sqlgetobject("SELECT * FROM `".addslashes($attackertype)."` WHERE `id` = ".intval($shooting->attacker));
		if (empty($defenderobj)) $defenderobj = sqlgetobject("SELECT * FROM `".addslashes($defendertype)."` WHERE `id` = ".intval($shooting->defender));
		if (!cFight::CanShoot($attackerobj,$attackertype,$defenderobj,$defendertype)) return false;
		
		// start fightlog
		$fightlog = cFight::StartFightLog($attackerobj,$defenderobj,$attackertype,$defendertype);
		
		$shooting->fightlog = $fightlog->id;
		sql("INSERT INTO `shooting` SET ".obj2sql($shooting));
		$shooting->id = mysql_insert_id();
		return $shooting;
	}
	
	// fire one shot
	static function ShootingStep ($shooting,$attackerobj=false,$defenderobj=false,$dmg=0,$debug=true) {
		global $gContainerType2Number,$gNumber2ContainerType,$gUnitType,$gBuildingType,$gArmyType;
		global $gAllUsers;
		if ($dmg <= 0) return; // todo : autocalc from units in attacker
		if (empty($shooting)) return;
		if (is_numeric($shooting->attackertype)) $shooting->attackertype = $gNumber2ContainerType[$shooting->attackertype];
		if (is_numeric($shooting->defendertype)) $shooting->defendertype = $gNumber2ContainerType[$shooting->defendertype];
		if (empty($attackerobj)) $attackerobj = sqlgetobject("SELECT * FROM `".addslashes($shooting->attackertype)."` WHERE `id` = ".intval($shooting->attacker));
		if (empty($defenderobj)) $defenderobj = sqlgetobject("SELECT * FROM `".addslashes($shooting->defendertype)."` WHERE `id` = ".intval($shooting->defender));
		if (empty($attackerobj) || empty($defenderobj)) {
			cFight::EndShooting($o,"Ziel verschwunden");
			return; // error
		}
		
		// who/what/where is attacker/defender ?
		// todo : more comfortable message system (buildingID) (armyID) (userID) (x,y) -> map+infolink
		
		$attackernametext = cFight::GetContainerText($attackerobj,$shooting->attackertype);
		$defendernametext = cFight::GetContainerText($defenderobj,$shooting->defendertype);
		
		// peng... determine time since last shot
		$target_killed = false;
		$now = time();
		$age = $now - $shooting->lastshot; 
		if ($debug) echo "ShootingStep $attackernametext at $defendernametext : dmg=$dmg,age=$age<br>\n";
		if ($age > kShootingAlarmTimeout || $shooting->lastshot == 0) {
			if ($debug) echo "kShootingAlarmTimeout<br>\n";
			cFight::ActOfWar($attackerobj->user,$defenderobj->user,"Beschuss",$attackerobj->x,$attackerobj->y);
			
			$topic = "Firing upon $defendernametext";
			$msg = $defendernametext." has been fired upon by ".$attackernametext.".<br>\n";
			if ($defenderobj->user) sendMessage($defenderobj->user,0,$topic,$msg,kMsgTypeReport,FALSE);
			
			$userflags = isset($gAllUsers) ? $gAllUsers[$attackerobj->user]->flags : sqlgetone("SELECT `flags` FROM `user` WHERE `id` = ".intval($attackerobj->user));
			$monsterberichte = !(intval($userflags) & kUserFlags_NoMonsterFightReport);
			if ($attackerobj->user && ($defenderobj->user || $monsterberichte)) 
				sendMessage($attackerobj->user,0,$topic,$msg,kMsgTypeReport,FALSE);
		}
		
		// apply damage
		// todo : capsule me as DamageArmy and DamageBuilding (in something like lib.damage.php, used from spells,fight,hunger...)
		if ($shooting->defendertype == kUnitContainer_Army) {
			$defenderobj = $defenderobj;
			// TablesLock(); // no lock needed ???, we are within minicron
			$defenderobj->units = cUnit::GetUnits($defenderobj->id);
			$defenderobj->vorher_units = $defenderobj->units;
			$defenderobj->units = cUnit::GetUnitsAfterDamage($defenderobj->units,$dmg,$defenderobj->user);
			$defenderobj->lost_units = cUnit::GetUnitsDiff($defenderobj->vorher_units,$defenderobj->units);
			
			cFight::AddUserkillsFromKilledUnits($attackerobj->user,$defenderobj->lost_units);
			
			if ($shooting->attackertype == kUnitContainer_Army)
				cArmy::AddArmyFrags($attackerobj->id,cUnit::GetUnitsExp($defenderobj->lost_units));
					
			if ($debug) foreach ($defenderobj->lost_units as $o)
				echo "<img src='".g($gUnitType[$o->type]->gfx)."'>".floor($o->amount)."<br>\n";
			$defenderobj->size = cUnit::GetUnitsSum($defenderobj->units);
			cUnit::SetUnits($defenderobj->units,$defenderobj->id);
			// $gAllArmyUnits[$enemy->id] = $enemy->units; // TODO : update cache ??
			if ($defenderobj->size < 1.0) $target_killed = true;
			// TablesUnlock();
		}
		if ($shooting->defendertype == kUnitContainer_Building) {
			$defenderobj->hp = max(0,$defenderobj->hp-$dmg);
			sql("UPDATE `building` SET `hp`=`hp`-".$dmg." WHERE `id`=".$defenderobj->id);
			if ($defenderobj->hp <= 0) {
				$target_killed = true;
				if ($shooting->attackertype == kUnitContainer_Army)
					cArmy::AddArmyFrags($attackerobj->id,1);
			}
		}
		
		// army cannot move while shooting
		if ($shooting->attackertype == kUnitContainer_Army)		
			sql("UPDATE `army` SET `idle`=0 WHERE `id`=".$shooting->attacker);
		
		// register last shot
		$shooting->lastshot = $now;
		sql("UPDATE `shooting` SET ".arr2sql(array("lastshot"=>$shooting->lastshot))." WHERE `id` = ".intval($shooting->id));
		
		// target killed
		if ($target_killed) {
			cFight::EndShooting($shooting,"Target was killed.",$attackerobj,$defenderobj);
			if ($shooting->defendertype == kUnitContainer_Army) cArmy::DeleteArmy($defenderobj,false,"Vernichtet");
			if ($shooting->defendertype == kUnitContainer_Building) cBuilding::removeBuilding($defenderobj,$defenderobj->user);
		}
	}
	
	static function EndShooting ($shooting,$why=0,$attackerobj=false,$defenderobj=false) {
		global $gContainerType2Number,$gNumber2ContainerType;
		//echo "EndShooting : $why<br>\n";
		if ($shooting->lastshot > 0) {
			//echo "fightlog = $shooting->fightlog<br>\n";
			$fightlog = sqlgetobject("SELECT * FROM `fightlog` WHERE `id` = ".intval($shooting->fightlog));
			if ($fightlog) {
				// send fight report
				if (is_numeric($shooting->attackertype)) $shooting->attackertype = $gNumber2ContainerType[$shooting->attackertype];
				if (is_numeric($shooting->defendertype)) $shooting->defendertype = $gNumber2ContainerType[$shooting->defendertype];
				if (empty($attackerobj)) $attackerobj = sqlgetobject("SELECT * FROM `".addslashes($shooting->attackertype)."` WHERE `id` = ".intval($shooting->attacker));
				if (empty($defenderobj)) $defenderobj = sqlgetobject("SELECT * FROM `".addslashes($shooting->defendertype)."` WHERE `id` = ".intval($shooting->defender));
				//echo "sending fight report...<br>\n";
				cFight::SendFightReport($fightlog,$attackerobj,$defenderobj,$why,true,$shooting->attackertype,$shooting->defendertype);
			}
		}
		sql("DELETE FROM `shooting` WHERE `id` = ".$shooting->id);
	}
	
	// ##### ##### ##### ##### ##### ##### ##### #####
	// ##### #####    PILLAGE  ##### ##### ##### #####
	// ##### ##### ##### ##### ##### ##### ##### #####
	
	
	// $army,$building must be objects
	static function PillagePossible ($army,$debug=false) {
		if (!cArmy::hasPillageAttack($army))
			{ if ($debug) echo "PillagePossible : army has no pillage attack<br>"; return false; }
		return true;
	}
	

	// $army,$building must be objects
	static function StartPillage ($army,$x,$y,$restypes,$debug=false) {
		if (!cFight::PillagePossible($army,$debug)) 
			{ if ($debug) echo "StartPillage : not possible<br>"; return true; }
		if (!cArmy::inPillageRange($army->x-$x,$army->y-$y)) 
			{ if ($debug) echo "StartPillage : out of range<br>"; return false; }
		$building = sqlgetobject("SELECT * FROM `building` WHERE `x` = ".intval($x)." AND `y` = ".intval($y));
		if (empty($building)) 
			{ if ($debug) echo "StartPillage : no building<br>"; return false; }
			
		// starting pillage
		if ($debug) echo "StartPillage : starting pillage<br>";
		$t = false;
		$t->start = time();
		$t->army = $army->id;
		$t->building = $building->id;
		$t->type = $restypes;
		sql("INSERT INTO `pillage` SET ".obj2sql($t));
		$t->id = mysql_insert_id();
		
		cFight::ActOfWar($army->user,$building->user,"Plünderung",$army->x,$army->y);
		
		// userlog
		global $gAllUsers,$gBuildingType;
		$armyownername = cArmy::GetArmyOwnerName($army);
		$busername = $building->user?(isset($gAllUsers)?$gAllUsers[$building->user]->name:sqlgetone("SELECT `name` FROM `user` WHERE `id` = ".intval($building->user))):"server";
		if ($army->user)		LogMe($army->user,		NEWLOG_TOPIC_FIGHT,NEWLOG_PILLAGE_ATTACKER_START,$building->x,$building->y,0,$army->name,$busername);
		if ($building->user)	LogMe($building->user,	NEWLOG_TOPIC_FIGHT,NEWLOG_PILLAGE_DEFENDER_START,$building->x,$building->y,0,$army->name,$armyownername);
		
		// TODO : pillagestep mit parameter fuer den ersten durchlauf, wenn dann was geklaut und es der erste durchlauf ist, erst dann wird nachricht geschickt
		// TODO : pillagestep aus pillagestart heraus aufrufen.
		// TODO : pillagestart bei fehlgeschlagenem pillagestep fehlerhaft beenden ( kein pillage eintrag, armee wird nicht aufgehalten)
		
		// report
		if ($building->user) {
			$topic = "Plünderung bei ($building->x,$building->y)";
			$report = "Unser ".$gBuildingType[$building->type]->name." bei ($building->x,$building->y) wird von ".$army->name." von ".$armyownername." geplündert !<br>";
			sendMessage($building->user,0,$topic,$report,kMsgTypeReport,FALSE);
			
			if ($army->user && $building->user)
				GuildLogMe($army->x,$army->y,$army->user,$building->user,
					sqlgetone("SELECT `guild` FROM `user` WHERE `id` = ".$army->user),
					sqlgetone("SELECT `guild` FROM `user` WHERE `id` = ".$building->user),
					"Plünderung","");
		}
		return true;
	}
	
	
	static function PillageStep ($pillage,$debug=false) {
		global $gResFields;
		//if ($debug) echo "pillage<br>";
		//if ($debug) vardump2($pillage);
		$army = sqlgetobject("SELECT * FROM `army` WHERE `id` = ".$pillage->army);
		$building = sqlgetobject("SELECT * FROM `building` WHERE `id` = ".$pillage->building);
		if (empty($building) || empty($army)) {
			warning("BUG : $building , $army<br>");
			sql("DELETE FROM `pillage` WHERE `id`=".$pillage->id);
			return;
		}
		
		$pluendergrenze = 200 + $building->level*25; // TODO : unhardcode
		$randomorder = Array();
		for($i=0;$i<sizeof($gResFields);++$i)$randomorder[] = $i;
		shuffle($randomorder);
		
		$army->units = cUnit::GetUnits($army->id);
		$army->lastmax = cUnit::GetUnitsSum($army->units,"last");
		$army->last = $army->lumber + $army->stone + $army->food + $army->metal + $army->runes;
		$army->last_vorher = $army->last;
		$army->pillage = min(0.01 * cUnit::GetUnitsSum($army->units,"pillage"),$army->lastmax-$army->last);
		$army->pillageleft = $army->pillage;
	
		foreach($gResFields as $res) $army->{"pillage_".$res} = 0;
					
		$user = sqlgetobject("SELECT * FROM `user` WHERE `id` = ".$building->user);
		foreach ($randomorder as $resid) {
			if ($pillage->type != -1 && !($pillage->type & (1<<$resid))) continue;
			$stolen = max(0,min($user->{$gResFields[$resid]} - $pluendergrenze,$army->pillageleft));
			$army->{"pillage_".$gResFields[$resid]} = $stolen;
			$army->pillageleft -= $stolen;
			if ($army->pillageleft <= 1) break;
		}
		
		$pillagetotal = $army->pillage - $army->pillageleft;
		// foreach($gResFields as $res)$pillagetotal += $army->{"pillage_".$res};
	
		global $gAllUsers;
		$armyownername = cArmy::GetArmyOwnerName($army);
	
		echo "Army ".$army->name." (".$armyownername.") has pillaged".
			intval($pillagetotal)."/".intval($army->pillage).": ".
			intval($army->pillage_lumber).",".
			intval($army->pillage_stone).",".
			intval($army->pillage_food).",".
			intval($army->pillage_metal).",".
			intval($army->pillage_runes)." of ".
			intval($user->lumber).",".
			intval($user->stone).",".
			intval($user->food).",".
			intval($user->metal).",".
			intval($user->runes)." from ".
			"storehouse (".$building->x."|".$building->y.").<br>";
			
		foreach($gResFields as $res){
			sql("UPDATE `army` SET `$res` = `$res` + ".$army->{"pillage_".$res}." WHERE `id` = ".$army->id);
			sql("UPDATE `user` SET `$res` = `$res` - ".$army->{"pillage_".$res}." WHERE `id` = ".$building->user);
		}
		
		$army->last += $pillagetotal;
		
		if ($debug) echo "Load: $army->last / $army->lastmax<br>";
		if ($army->last > $army->lastmax - 2) { // TODO : unhardcode
			cFight::EndPillage($pillage,"The pillagers are fully laden.");
		} else if ($pillagetotal < $army->pillage/2) { // TODO : BUG !!! stops as soon as one of the target res is empty
			cFight::EndPillage($pillage,"The storehouse is empty.");
		}
	}
	
	static function EndPillage ($pillage,$why,$aborted=false) {
		echo "EndPillage : $why<br>";
		$army = sqlgetobject("SELECT * FROM `army` WHERE `id` = ".$pillage->army);
		$building = sqlgetobject("SELECT * FROM `building` WHERE `id` = ".$pillage->building);
		$buildingownername = $building->user ? sqlgetone("SELECT `name` FROM `user` WHERE `id`=".$building->user) : "Server";
		$armyownername = cArmy::GetArmyOwnerName($army);
		if ($aborted) {
			if ($army->user)		LogMe($army->user,		NEWLOG_TOPIC_FIGHT,NEWLOG_PILLAGE_ATTACKER_CANCEL,$building->x,$building->y,0,$army->name,$buildingownername);
			if ($building->user)	LogMe($building->user,	NEWLOG_TOPIC_FIGHT,NEWLOG_PILLAGE_DEFENDER_CANCEL,$building->x,$building->y,0,$army->name,$armyownername);
		} else {
			if ($army->user)		LogMe($army->user,		NEWLOG_TOPIC_FIGHT,NEWLOG_PILLAGE_ATTACKER_STOP,$building->x,$building->y,0,$army->name,$buildingownername);
			if ($building->user)	LogMe($building->user,	NEWLOG_TOPIC_FIGHT,NEWLOG_PILLAGE_DEFENDER_STOP,$building->x,$building->y,0,$army->name,$armyownername);
		}
		sql("DELETE FROM `pillage` WHERE `id` = ".$pillage->id);
	}
	
	
	// ##### ##### ##### ##### ##### ##### ##### #####
	// ##### #####    SIEGE    ##### ##### ##### #####
	// ##### ##### ##### ##### ##### ##### ##### #####
	
	
	
	// $army,$building must be objects
	static function SiegePossible ($army,$debug=false) {
		if (!cArmy::hasSiegeAttack($army))
			{ if ($debug) echo "SiegePossible : army has no siege attack<br>"; return false; }
		return true;
	}
	
	
	// $army,$building must be objects
	static function StartSiege ($army,$x,$y,$debug=false) {
		if (!cFight::SiegePossible($army,$debug)) 
			{ if ($debug) echo "StartSiege : not possible<br>"; return true; }
		if (!cArmy::inSiegeRange($army->x-$x,$army->y-$y)) 
			{ if ($debug) echo "StartSiege : out of range<br>"; return false; }
		$building = sqlgetobject("SELECT * FROM `building` WHERE `x` = ".intval($x)." AND `y` = ".intval($y));
		if (empty($building)) 
			{ if ($debug) echo "StartSiege : no building<br>"; return false; }
			
			
		if (intval($army->flags) & kArmyFlag_SiegePillage) {
			if (!isset($army->units)) $army->units = cUnit::GetUnits($army->id);
			$freeload = max(0,cUnit::GetUnitsSum($army->units,"last") - cArmy::GetArmyTotalWeight($army));
			$armyfull = $freeload < 1;
			
			if ($debug) echo "armyfull = ".($armyfull?1:0).", freeload = $freeload<br>";
			
			// army is full, stop pillage-siege
			if ($armyfull && (intval($army->flags) & kArmyFlag_StopSiegeWhenFull)) {
				if ($debug) echo "StartSiege : full : abort<br>";
				return false;
			}
		}
			
		// starting siege
		if ($debug) echo "StartSiege : starting siege<br>";
		$t = new EmptyObject();
		$t->start = time();
		$t->army = $army->id;
		$t->building = $building->id;
		sql("INSERT INTO `siege` SET ".obj2sql($t));
		$t->id = mysql_insert_id();
			
		cFight::ActOfWar($army->user,$building->user,"Belagerung",$army->x,$army->y);
		
		// userlog
		global $gAllUsers,$gBuildingType;
		$armyownername = cArmy::GetArmyOwnerName($army);
		$busername = $building->user?(isset($gAllUsers)?$gAllUsers[$building->user]->name:sqlgetone("SELECT `name` FROM `user` WHERE `id` = ".intval($building->user))):"server";
		//if ($debug) echo "siege : $armyownername $busername $army->user $building->user <br>";
		if ($army->user)		LogMe($army->user,		NEWLOG_TOPIC_FIGHT,NEWLOG_RAMPAGE_ATTACKER_START,$building->x,$building->y,0,$army->name,$busername);
		if ($building->user)	LogMe($building->user,	NEWLOG_TOPIC_FIGHT,NEWLOG_RAMPAGE_DEFENDER_START,$building->x,$building->y,0,$army->name,$armyownername);
		
		// report
		if ($building->user) {
			$topic = "Belagerung bei ($building->x,$building->y)";
			$report = "Unser ".$gBuildingType[$building->type]->name." bei ($building->x,$building->y) wird von ".$army->name." von ".$armyownername." belagert !<br>";
			sendMessage($building->user,0,$topic,$report,kMsgTypeReport,FALSE);
			
			if ($army->user && $building->user)
				GuildLogMe($army->x,$army->y,$army->user,$building->user,
					sqlgetone("SELECT `guild` FROM `user` WHERE `id` = ".$army->user),
					sqlgetone("SELECT `guild` FROM `user` WHERE `id` = ".$building->user),
					"Belagerung","");
		}
				
		return true;
	}
	
	static function SiegeStep ($siege,$debug=false) {
		$army = sqlgetobject("SELECT * FROM `army` WHERE `id` = ".$siege->army);
		$building = sqlgetobject("SELECT * FROM `building` WHERE `id` = ".$siege->building);
		if (empty($army) || empty($building)) {
			warning("BUG ! army_empty=".empty($army)." building_empty=".empty($building)."<br>");
			sql("DELETE FROM `siege` WHERE `id`=".$siege->id);
			return;
		}
		
		$units = cUnit::GetUnits($siege->army);
		$dmg = cUnit::GetUnitsSiegeAttack($units,$army->user) / 3600.0 * 60.0;
		$building->hp -= $dmg;
		
		if (intval($army->flags) & kArmyFlag_SiegePillage) {
			global $gBuildingType,$gRes2ItemType,$gRes;
			$percent_of_level_0 = $dmg / $gBuildingType[$building->type]->maxhp;
			if ($debug) echo "SiegePillage : ".$dmg." / ".$gBuildingType[$building->type]->maxhp." = ".$percent_of_level_0."<br>";
			$myres = $gRes;
			$armyfull = false;
			foreach ($myres as $n=>$f) {
				$cost = $gBuildingType[$building->type]->{"cost_".$f} * $building->level;
				// todo : multiply by building->level  ???
				$get = ceil($percent_of_level_0 * $cost * kSiegePillageEfficiency);
				if ($debug) echo "SiegePillage: $n : ".$cost." -> ".$get."<br>";
				if ($get <= 0) continue;
				if (!cItem::SpawnArmyItem($army->id,$gRes2ItemType[$f],$get))  {
					$armyfull = true;
					echo "army full<br>";
				} else {
					echo "army NOT full<br>";
				}
			}
			
			echo "armyfull = ".($armyfull?1:0)."<br>";
			
			// army is full, stop pillage-siege
			if ($armyfull && (intval($army->flags) & kArmyFlag_StopSiegeWhenFull)) {
				echo "siegepillage : full : abort<br>";
				cFight::EndSiege($siege,"Der Belagerer hat sich zurückgezogen.");
				return;
			}
		}
		
		
		global $gBuildingType;
		echo $army->name."(".$army->x.",".$army->y.") belagert ".$gBuildingType[$building->type]->name."(".$building->x."|".$building->y.") mit $dmg Schaden<br>";
			
		sql("UPDATE `building` SET `hp`='".$building->hp."' WHERE `id`=".$building->id);
		
		// gebäude tot, oder noch im bau unter 10 prozent..
		if ($building->hp < 1 || ($building->construction>0 && GetConstructionProgress($building)<0.1)) { // TODO : unhardcode
			// belagerung beendet
			cFight::EndSiege($siege,"The building was destroyed.");
			cBuilding::removeBuilding($building,$building->user);
			cArmy::AddArmyFrags($siege->army,1);
		}
	}
	
	static function EndSiege ($siege,$why,$aborted=false) {
		echo "EndSiege : $why<br>";
		// todo : messages
		$army = sqlgetobject("SELECT * FROM `army` WHERE `id` = ".$siege->army);
		$building = sqlgetobject("SELECT * FROM `building` WHERE `id` = ".$siege->building);
		$buildingownername = $building->user ? sqlgetone("SELECT `name` FROM `user` WHERE `id`=".$building->user) : "Server";
		$armyownername = cArmy::GetArmyOwnerName($army);
		if ($aborted) {
			if ($army->user)		LogMe($army->user,		NEWLOG_TOPIC_FIGHT,NEWLOG_RAMPAGE_ATTACKER_CANCEL,$building->x,$building->y,0,$army->name,$buildingownername);
			if ($building->user)	LogMe($building->user,	NEWLOG_TOPIC_FIGHT,NEWLOG_RAMPAGE_DEFENDER_CANCEL,$building->x,$building->y,0,$army->name,$armyownername);
		} else {
			if ($army->user)		LogMe($army->user,		NEWLOG_TOPIC_FIGHT,NEWLOG_RAMPAGE_ATTACKER_DESTROY,$building->x,$building->y,0,$army->name,$buildingownername);
			if ($building->user)	LogMe($building->user,	NEWLOG_TOPIC_FIGHT,NEWLOG_RAMPAGE_DEFENDER_DESTROY,$building->x,$building->y,0,$army->name,$armyownername);
		}
		sql("DELETE FROM `siege` WHERE `id` = ".$siege->id);
	}
	
	// ##### ##### ##### ##### ##### ##### ##### #####
	// ##### #####    FIGHT  ##### ##### ##### #####
	// ##### ##### ##### ##### ##### ##### ##### #####
	
	
	// $attacker,$defender must be army-objects
	// $attacker->units and $defender->units must be set
	static function FightPossible ($attacker,$defender,$debug=false) {
		if (empty($defender))		{ if ($debug) echo "FightPossible : no enemy<br>"; return false; }
		$ma = cUnit::GetUnitsMovableMask($attacker->units);
		$md = cUnit::GetUnitsMovableMask($defender->units);
		if (($ma & $md) == 0)	{ if ($debug) echo "FightPossible : movable-mismatch $ma & $md<br>"; return false; }
		if (!cArmy::hasMeleeAttack($attacker))
			{ if ($debug) echo "FightPossible : attacker has no melee attack<br>"; return false; }
		return true;
	}
	
	

	// $army,$enemy must be army-objects
	// $army->units and $enemy->units must be set
	static function StartFight ($army,$enemy,$debug=false) {
		if (!cFight::FightPossible($army,$enemy,$debug)) 
			{ if ($debug) echo "StartFight : fight not possible<br>"; return true; }
		if (!cArmy::inMeleeRange($army->x-$enemy->x,$army->y-$enemy->y)) 
			{ if ($debug) echo "StartFight : out of range<br>"; return false; }
			
		if (sqlgetone("SELECT 1 FROM `fight` WHERE	(`attacker` = ".$army->id." AND `defender` = ".$enemy->id.") OR 
													(`attacker` = ".$enemy->id." AND `defender` = ".$army->id.") LIMIT 1")) 
			{ if ($debug) echo "StartFight : already fighting<br>"; return false; }
		
		// start fightlog
		$fightlog = cFight::StartFightLog($army,$enemy);
		
		// starting fight
		if ($debug) echo "StartFight : starting fight<br>";
		$fight = new EmptyObject();
		$fight->fightlog = $fightlog->id;
		$fight->start = time();
		$fight->attacker = $army->id;
		$fight->defender = $enemy->id;
		sql("INSERT INTO `fight` SET ".obj2sql($fight));
		$fight->id = mysql_insert_id();
			
		cFight::ActOfWar($army->user,$enemy->user,"Kampf",$army->x,$army->y);
			
		// player-versus-player
		if ($army->user > 0 && $enemy->user > 0)
			GuildLogMe($army->x,$army->y,$army->user,$enemy->user,
				sqlgetone("SELECT `guild` FROM `user` WHERE `id` = ".$army->user),
				sqlgetone("SELECT `guild` FROM `user` WHERE `id` = ".$enemy->user),
				"Kampf","");
		
		
		if ($army->user > 0) LogMe($army->user,NEWLOG_TOPIC_FIGHT,NEWLOG_FIGHT_START,$army->x,$army->y,0,$army->name,$enemy->name);
		if ($enemy->user > 0) LogMe($enemy->user,NEWLOG_TOPIC_FIGHT,NEWLOG_FIGHT_START,$enemy->x,$enemy->y,0,$enemy->name,$army->name);
		
		if((intval($army->flags) & kArmyFlag_GuildCommand)){
			$gc=getGuildCommander(sqlgetone("SELECT `guild` FROM `user` WHERE `id` = ".intval($army->user)));
			foreach($gc as $c)
				if($c!=$army->user)LogMe($c,NEWLOG_TOPIC_FIGHT,NEWLOG_FIGHT_START,$army->x,$army->y,0,$army->name,$enemy->name);
		}
		if((intval($enemy->flags) & kArmyFlag_GuildCommand)){
			$gc=getGuildCommander(sqlgetone("SELECT `guild` FROM `user` WHERE `id` = ".intval($enemy->user)));
			foreach($gc as $c)
				if($c!=$enemy->user)LogMe($c,NEWLOG_TOPIC_FIGHT,NEWLOG_FIGHT_START,$enemy->x,$enemy->y,0,$enemy->name,$army->name);
		}
		return true;
	}
	
	
	static function FightStep ($fight) {
		//sets fighting army idle=0 so no one can move an do stuff like this
		sql("UPDATE `army` SET `idle`=0 WHERE `id`=".intval($fight->attacker)." OR `id`=".intval($fight->defender));
		
		$army1 = sqlgetobject("SELECT * FROM `army` WHERE `id` = ".$fight->attacker);
		$army2 = sqlgetobject("SELECT * FROM `army` WHERE `id` = ".$fight->defender);
		
		if(empty($army1) || empty($army2)) {
			sql("DELETE FROM `fight` WHERE `id`=".$fight->id);
			warning("BUG : $army1 , $army2<br>");
			return;
		}
		
		if ((intval($army1->flags) & kArmyFlag_WillingToAbortFight) &&
			(intval($army2->flags) & kArmyFlag_WillingToAbortFight)) {
			cFight::EndFight($fight,"Waffenstillstand.");
			return;
		}
		
		// ghoul: i suggest we only use the coordinates of army1 to get the spells, to improve performance, the spell search takes a while
		$spells = GetSpellsInArea($army1->x,$army1->y,array(0,kSpellType_ArmeeDerToten));
		foreach ($spells as $o) echo "spell:".$o->spelltype->name.",";
		echo "<br>";
		
		// todo : optimize this ? maybe as param, since where this static function is called, we hava a complete fight-list
		$army1->fightcount = intval(sqlgetone("SELECT COUNT(`id`) FROM `fight` WHERE `attacker` = ".intval($army1->id)." OR `defender` = ".intval($army1->id)));
		$army2->fightcount = intval(sqlgetone("SELECT COUNT(`id`) FROM `fight` WHERE `attacker` = ".intval($army2->id)." OR `defender` = ".intval($army2->id)));
		
		$army1->units = cUnit::GetUnits($army1->id);
		$army2->units = cUnit::GetUnits($army2->id);
		
		$m1 = cArmy::GetFieldMod($army1->x,$army1->y);
		$m2 = cArmy::GetFieldMod($army2->x,$army2->y);
		
		/* ***** FIGHT_ROUND_START ***** */
		// no database changes until FIGHT_ROUND_END, for sync with simulator
		cFight::FightCalcStep($army1,$army2,$m1,$m2,$spells,true);
		/* ***** FIGHT_ROUND_END ***** */
		
		// todo : fleet : throw excess transports overboard as ships are lost
		
		if ($army1->type == kArmyType_Fleet) cUnit::SetUnits($army1->transport,$army1->id,kUnitContainer_Transport);
		if ($army2->type == kArmyType_Fleet) cUnit::SetUnits($army2->transport,$army2->id,kUnitContainer_Transport);
		
		if ($army1->type == kArmyType_Fleet && $army1->captureattack > 0) cArmy::Capture($army1,$army1->captured);
		if ($army2->type == kArmyType_Fleet && $army2->captureattack > 0) cArmy::Capture($army2,$army2->captured);
		
		cUnit::SetUnits($army1->units,$army1->id);
		cUnit::SetUnits($army2->units,$army2->id);
		
		cArmy::AddArmyFrags($army1->id,$army1->newfrags);
		cArmy::AddArmyFrags($army2->id,$army2->newfrags);
		
		// todo : dump corpses instead...
		sql("UPDATE `terrain` SET `kills`=`kills`+".round(abs(cUnit::GetUnitsSum($army1->vorher_units) - cUnit::GetUnitsSum($army1->units)))." WHERE `x`=".$army1->x." AND `y`=".$army1->y);
		sql("UPDATE `terrain` SET `kills`=`kills`+".round(abs(cUnit::GetUnitsSum($army2->vorher_units) - cUnit::GetUnitsSum($army2->units)))." WHERE `x`=".$army2->x." AND `y`=".$army2->y);
		
		$army1->overweight = cArmy::GetArmyTotalWeight($army1) - cUnit::GetUnitsSum($army1->units,"last");
		$army2->overweight = cArmy::GetArmyTotalWeight($army2) - cUnit::GetUnitsSum($army2->units,"last");
		// todo : dump $army1->overweight ressources..
		
		$army1->size = cUnit::GetUnitsSum($army1->units);
		$army2->size = cUnit::GetUnitsSum($army2->units);
		
		// kampf beenden
		global $gArmyType,$gRes2ItemType,$gRes;
		if ($army1->size <= 0 && $army2->size <= 0) {
			cFight::EndFight($fight,"Both armies were destroyed.");
			cArmy::DeleteArmy($army1);
			cArmy::DeleteArmy($army2);
		} else if ($army1->size <= 0) {
			cFight::EndFight($fight,$gArmyType[$army1->type]->name." $army1->name was destroyed.");
			cArmy::DropExcessCargo($army1,$army2,0);
			cArmy::DeleteArmy($army1,true);
		} else if ($army2->size <= 0) {
			cFight::EndFight($fight,$gArmyType[$army2->type]->name." $army2->name was destroyed.");
			cArmy::DropExcessCargo($army2,$army1,0);
			cArmy::DeleteArmy($army2,true);
		}
	}
	
	static function FightCalcStep (&$army1,&$army2,$m1,$m2,$spells,$showverlauf) {
		if (!isset($army1->flags)) $army1->flags = 0; // kampfsim
		if (!isset($army2->flags)) $army2->flags = 0; // kampfsim
		
		$army1->totalattack = cUnit::GetUnitsAttack($army1->units,$army1->user);
		$army2->totalattack = cUnit::GetUnitsAttack($army2->units,$army1->user);
				
		if ($army1->type == kArmyType_Fleet) $army1->transport = cUnit::GetUnits($army1->id,kUnitContainer_Transport);
		if ($army2->type == kArmyType_Fleet) $army2->transport = cUnit::GetUnits($army2->id,kUnitContainer_Transport);
		
		if ($army1->type == kArmyType_Fleet) {
			if (intval($army1->flags) & kArmyFlag_CaptureShips)
					$army1->captureattack = cUnit::GetUnitsCaptureAttack($army1->transport,$army1->user);
			else	$army1->captureattack = 0;
			if ($army1->captureattack > 0) // start normal fight if no more capture
					$army1->totalattack = $army1->captureattack;
			else	$army1->totalattack += cUnit::GetUnitsFightOnDeckAttack($army1->transport,$army1->user);
		}
		
		if ($army2->type == kArmyType_Fleet) {
			if (intval($army2->flags) & kArmyFlag_CaptureShips)
					$army2->captureattack = cUnit::GetUnitsCaptureAttack($army2->transport,$army2->user);
			else	$army2->captureattack = 0;
			if ($army2->captureattack > 0) // start normal fight if no more capture
					$army2->totalattack = $army2->captureattack;
			else	$army2->totalattack += cUnit::GetUnitsFightOnDeckAttack($army2->transport,$army2->user);
		}
		
		if (intval($army1->flags) & kArmyFlag_Captured){
			$army1->captureattack=0;
			$army1->totalattack=0;
			$army2->totalattack*=2;
		}
		if (intval($army1->flags) & kArmyFlag_Captured){
			$army2->captureattack=0;
			$army2->totalattack=0;
			$army1->totalattack*=2;
		}
		
		$army1->totalattack *= $m1["a"] * 0.01 / $army1->fightcount;
		$army2->totalattack *= $m2["a"] * 0.01 / $army2->fightcount;
		if ($showverlauf)
			echo "at1 = $army1->totalattack , at2 = $army2->totalattack<br>";
		
		$army1->vorher_units = $army1->units;
		$army2->vorher_units = $army2->units;
		
		$army1->units = cUnit::GetUnitsAfterDamage($army1->units,$army2->totalattack,$army1->user,$m1["v"]);
		$army2->units = cUnit::GetUnitsAfterDamage($army2->units,$army1->totalattack,$army2->user,$m2["v"]);
		
		$army1->lost_units = cUnit::GetUnitsDiff($army1->vorher_units,$army1->units);
		$army2->lost_units = cUnit::GetUnitsDiff($army2->vorher_units,$army2->units);
		
		cFight::AddUserkillsFromKilledUnits($army1->user,$army2->lost_units);
		cFight::AddUserkillsFromKilledUnits($army2->user,$army1->lost_units);

		$army1->newfrags = cUnit::GetUnitsExp($army2->lost_units);
		$army2->newfrags = cUnit::GetUnitsExp($army1->lost_units);
		
		if ($army1->type == kArmyType_Fleet && $army1->captureattack > 0)
			list($army1->transport,$army1->captured) = cUnit::CaptureShips($army1->transport,$army2->lost_units);
		if ($army2->type == kArmyType_Fleet && $army2->captureattack > 0)
			list($army2->transport,$army2->captured) = cUnit::CaptureShips($army2->transport,$army1->lost_units);
		
		$army1->units = cUnit::TrainElites($army1->units,$army1->newfrags);
		$army2->units = cUnit::TrainElites($army2->units,$army2->newfrags);
		
		foreach ($spells as $spell) if (method_exists($spell,"ModUnits")) {
			list($army1->units,$army1->lost_units) = $spell->ModUnits($army1,$army1->units,$army1->lost_units);
			list($army2->units,$army2->lost_units) = $spell->ModUnits($army2,$army2->units,$army2->lost_units);
		}
		
		$army1->size = cUnit::GetUnitsSum($army1->units);
		$army2->size = cUnit::GetUnitsSum($army2->units);
		
		if (!isset($army1->id)) $army1->id = 1; // kampfsim
		if (!isset($army2->id)) $army2->id = 2; // kampfsim
		
		if ($showverlauf)
			echo "[size_$army1->id=".sprintf("%0.2f",$army1->size)." size_$army2->id=".sprintf("%0.2f",$army2->size)."]<br>";
		
		$army1->frags += $army1->newfrags;
		$army2->frags += $army2->newfrags;
	}
	
	
	static function EndFight ($fight,$why) {
		echo "EndFight : $why<br>";
		if ($fight->fightlog == 0) { // temporary hack while changing database, the `fightlog`.`fight` field is not used anymore, can be dropped
			$fight->fightlog = sqlgetone("SELECT `id` FROM `fightlog` WHERE `fight` = ".intval($fight->id)); // this line can savely be removed
		}
		cFight::SendFightReport($fight->fightlog,$fight->attacker,$fight->defender,$why);
		sql("DELETE FROM `fight` WHERE `id` = ".$fight->id);
	}
	
	// used for StartFight and StartShooting
	static function StartFightLog ($attacker,$defender,$attackertype=kUnitContainer_Army,$defendertype=kUnitContainer_Army) {
		if (!is_object($attacker)) $attacker = sqlgetobject("SELECT * FROM `$attackertype` WHERE `id` = ".intval($attacker));
		if (!is_object($defender)) $defender = sqlgetobject("SELECT * FROM `$defendertype` WHERE `id` = ".intval($defender));
		if (!isset($attacker->units)) $attacker->units = cUnit::GetUnits($attacker->id,$attackertype);
		if (!isset($defender->units)) $defender->units = cUnit::GetUnits($defender->id,$defendertype);
		$attacker->transport = array();
		$defender->transport = array();
		if ($attackertype == kUnitContainer_Army && !isset($attacker->transport)) $attacker->transport = cUnit::GetUnits($attacker->id,kUnitContainer_Transport);
		if ($defendertype == kUnitContainer_Army && !isset($defender->transport)) $defender->transport = cUnit::GetUnits($defender->id,kUnitContainer_Transport);
		
		$fightlog = new EmptyObject();
		$fightlog->startunits1 = cUnit::Units2Text($attacker->units);
		$fightlog->startunits2 = cUnit::Units2Text($defender->units);
		$fightlog->starttransport1 = cUnit::Units2Text($attacker->transport);
		$fightlog->starttransport2 = cUnit::Units2Text($defender->transport);
		sql("INSERT INTO `fightlog` SET ".obj2sql($fightlog));
		$fightlog->id = mysql_insert_id();
		return $fightlog;
	}
		
	// used for EndFight and EndShooting
	static function SendFightReport ($fightlog,$attacker,$defender,$why,$is_shooting=false,$attackertype=kUnitContainer_Army,$defendertype=kUnitContainer_Army) {
		global $gUnitType,$gAllUsers,$gRes,$gItemType,$gRes2ItemType;
		
		// army1 == attacker, army2 == defender
		if ($attacker && !is_object($attacker)) $attacker = sqlgetobject("SELECT * FROM `$attackertype` WHERE `id` = ".intval($attacker));
		if ($defender && !is_object($defender)) $defender = sqlgetobject("SELECT * FROM `$defendertype` WHERE `id` = ".intval($defender));
		
		// send log messages and collect users to be informed
		$to_uid_list = array();
		if ($attacker && $attacker->user) $to_uid_list[] = $attacker->user;
		if ($defender && $defender->user) $to_uid_list[] = $defender->user;
		
		// TODO : if ($is_shooting) not NEWLOG_FIGHT_STOP but NEWLOG_SHOOTING_STOP or something like that
		if ($attacker && $attacker->user) LogMe($attacker->user,NEWLOG_TOPIC_FIGHT,NEWLOG_FIGHT_STOP,$attacker->x,$attacker->y,0,$why,"");
		if ($defender && $defender->user) LogMe($defender->user,NEWLOG_TOPIC_FIGHT,NEWLOG_FIGHT_STOP,$defender->x,$defender->y,0,$why,"");
		
		if ($attacker && $attacker->user && (intval($attacker->flags) & kArmyFlag_GuildCommand)){
			$gc = getGuildCommander(sqlgetone("SELECT `guild` FROM `user` WHERE `id` = ".$attacker->user));
			foreach($gc as $c) {
				if($c!=$attacker->user) LogMe($c,NEWLOG_TOPIC_FIGHT,NEWLOG_FIGHT_STOP,$attacker->x,$attacker->y,0,$why,"");
				$to_uid_list[] = $c;
			}
		}
		if ($defender && $defender->user && (intval($defender->flags) & kArmyFlag_GuildCommand)){
			$gc = getGuildCommander(sqlgetone("SELECT `guild` FROM `user` WHERE `id` = ".$defender->user));
			foreach($gc as $c) {
				if($c!=$defender->user)LogMe($c,NEWLOG_TOPIC_FIGHT,NEWLOG_FIGHT_STOP,$defender->x,$defender->y,0,$why,"");
				$to_uid_list[] = $c;
			}
		}
		$to_uid_list = array_unique($to_uid_list);
		
		// get infos
		if ($fightlog && !is_object($fightlog)) $fightlog = sqlgetobject("SELECT * FROM `fightlog` WHERE `id` = ".intval($fightlog));
		$attacker->starttransport = array();
		$defender->starttransport = array();
		$attacker->transport = array();
		$defender->transport = array();
		if ($fightlog) {
			sql("DELETE FROM `fightlog` WHERE `id` = ".$fightlog->id);
			// todo : admin logging !
			if ($attacker) $attacker->startunits = cUnit::Text2Units($fightlog->startunits1);
			if ($defender) $defender->startunits = cUnit::Text2Units($fightlog->startunits2);
			if ($attacker && $attackertype == kUnitContainer_Army) $attacker->starttransport = cUnit::Text2Units($fightlog->starttransport1);
			if ($defender && $defendertype == kUnitContainer_Army) $defender->starttransport = cUnit::Text2Units($fightlog->starttransport2);
		} else {
			if ($attacker) $attacker->startunits = cUnit::GetUnits($attacker->id,$attackertype);
			if ($defender) $defender->startunits = cUnit::GetUnits($defender->id,$defendertype);
			if ($attacker && $attackertype == kUnitContainer_Army) $attacker->starttransport = cUnit::GetUnits($attacker->id,kUnitContainer_Transport);
			if ($defender && $defendertype == kUnitContainer_Army) $defender->starttransport = cUnit::GetUnits($defender->id,kUnitContainer_Transport);
		}
		if ($attacker) $attacker->units = cUnit::GetUnits($attacker->id,$attackertype);
		if ($defender) $defender->units = cUnit::GetUnits($defender->id,$defendertype);
		if ($attacker && $attackertype == kUnitContainer_Army) $attacker->transport = cUnit::GetUnits($attacker->id,kUnitContainer_Transport);
		if ($defender && $defendertype == kUnitContainer_Army) $defender->transport = cUnit::GetUnits($defender->id,kUnitContainer_Transport);
		if ($attacker) $attacker->startsize = cUnit::GetUnitsSum($attacker->startunits);
		if ($defender) $defender->startsize = cUnit::GetUnitsSum($defender->startunits);
		if ($attacker) $attacker->size = cUnit::GetUnitsSum($attacker->units);
		if ($defender) $defender->size = cUnit::GetUnitsSum($defender->units);
		$monster = (($attacker && $attackertype == kUnitContainer_Army && $attacker->user == 0) ||
					($defender && $defendertype == kUnitContainer_Army && $defender->user == 0));
	
		$attacker->destroyed = ($attackertype == kUnitContainer_Army) ? ($attacker->size < 1.0) : ($attacker->hp < 1.0);
		$defender->destroyed = ($defendertype == kUnitContainer_Army) ? ($defender->size < 1.0) : ($defender->hp < 1.0);
			
		// start report
		if ($is_shooting)
				$topic = $monster?"Monster Bombardment":"Bombardment";
		else	$topic = $monster?"Monster":"Battle Report";
		if ($is_shooting)
				$report = "The bombardment at (".$defender->x.",".$defender->y.") has ended.<br>";
		else	$report = "The battle at (".$attacker->x.",".$attacker->y.") has ended.<br>";
		$report .= $why."<br>";
		if ($monster) $report .= "Monster battle reports can be cancelled on the Settings page.<br>";
		$report .= "<br>";
		
		// report army state
		$arr = array(array($attacker,$attackertype),array($defender,$defendertype));
		foreach ($arr as $pair) {
			list($container,$containertype) = $pair;
			if (empty($container)) continue;
			$containernametext = cFight::GetContainerText($container,$containertype);
			
			$report .= "<b>".$containernametext."</b> ".($container->destroyed?"<font color='red'><b>(ausgelöscht)</b></font>":"")."<br>";
			$losses = cUnit::GetUnitsDiff($container->startunits,$container->units,true);
			$losses = array_merge($losses,cUnit::GetUnitsDiff($container->starttransport,$container->transport,true));
			$losses = cUnit::GroupUnits($losses);
		
			// anfangszustand
			if ($container->startsize > 0) {
				rob_ob_start();
				cText::UnitsList($container->startunits,$container->user,"",false);
				if (cUnit::GetUnitsSum($container->starttransport) > 0) cText::UnitsList($container->starttransport,$container->user,"",false);
				$report .= rob_ob_end();
			}
			
			// verluste/neuzugänge
			$report .= "<table border=1 cellspacing=0>";
			$arr_loss = array();
			$arr_gain = array();
			if (!$container->destroyed) {
				foreach ($losses as $o) if (floor(abs($o->amount)) > 0) {
					$txt = "<td align='right'>".floor(abs($o->amount))."</td><td><img src='".g($gUnitType[$o->type]->gfx)."'></td>";
					if ($o->amount > 0) 
							$arr_loss[] = $txt;
					else	$arr_gain[] = $txt;
				}
				if (count($arr_loss) > 0) $report .= "<tr><th>Casualties</th>".implode(" ",$arr_loss)."</tr>";
				if (count($arr_gain) > 0) $report .= "<tr><th>New Additions</th>".implode(" ",$arr_gain)."</tr>";
			} else {
				$report .= "<font color='red'><b>razed</b></font>";
			}
			
			// verlorene res, items
			if ($container->destroyed && $containertype == kUnitContainer_Army) {
				$arr_items = array();
				foreach ($gRes as $n=>$f) if ($container->{$f} > 0)
					$arr_items[] = "<td align='right'>".$container->{$f}."</td><td><img src='".g($gItemType[$gRes2ItemType[$f]]->gfx)."'></td>";
				$containeritems = sqlgettable("SELECT * FROM `item` WHERE `army` = ".$container->id);
				foreach ($containeritems as $o) 
					$arr_items[] = "<td align='right'>".$o->amount."</td><td><img src='".g($gItemType[$o->type]->gfx)."'></td>";
				if (count($arr_items) > 0) $report .= "<tr><th>Beute</th>".implode(" ",$arr_items)."</tr>";
			}
			$report .= "</table>";
			
			// endzustand
			if (!$container->destroyed) {
				if (count($arr_loss) > 0 || count($arr_gain) > 0) {
					rob_ob_start();
					cText::UnitsList($container->units,$container->user,"",false);
					if (cUnit::GetUnitsSum($container->transport) > 0) cText::UnitsList($container->transport,$container->user,"",false);
					if ($is_shooting)
							$report .= "Units after the bombardment :";
					else	$report .= "Units after the battle :";
					$report .= rob_ob_end();
				} else {
					$report .= "No casualties";
				}
			}
			
			$report .= "<br>";
			$report .= "<br>";
		}
		
		// echo $report; // debug
		
		// send report
		foreach($to_uid_list as $uid) if ($uid) {
			$niederlage = false;
			if ($uid == $attacker->user && $attacker->destroyed) { $niederlage = true; echo "army1 tot<br>";}
			if ($uid == $defender->user && $defender->destroyed) { $niederlage = true; echo "army2 tot<br>";}
			if ($monster && $attacker->user && $attacker->destroyed) { $niederlage = true; echo "army1 tot gc<br>";} // for gcs
			if ($monster && $defender->user && $defender->destroyed) { $niederlage = true; echo "army2 tot gc<br>";} // for gcs
			if (!$niederlage && $monster) {
				$userflags = isset($gAllUsers) ? $gAllUsers[$uid]->flags : sqlgetone("SELECT `flags` FROM `user` WHERE `id` = ".intval($uid));
				if (intval($userflags) & kUserFlags_NoMonsterFightReport) continue;
			}
			sendMessage($uid,0,$niederlage?"Defeat!":$topic,$report,kMsgTypeReport,FALSE);
		}
	}
	
	
	// $army must be object, $x,$y are the coordinates to flee to...
	static function Flee ($army,$x,$y) {
		if (abs($x-$army->x) + abs($y-$army->y) > 1) return false;
		$army->units = cUnit::GetUnits($army->id);
		if (cArmy::GetPosSpeed($x,$y,$army->user,$army->units) <= 0) return false;
		$fights = sqlgettable("SELECT * FROM `fight` WHERE `attacker`=".$army->id." OR `defender`=".$army->id);
		if (count($fights) == 0) return false;
		$army->flucht_units = cUnit::GetUnitsAfterEscape($army->units,$army->user);
		sql("UPDATE `army` SET `idle` = 450, `frags` = `frags` / 2 , `nextactiontime` = ".time()." , `x` = $x, `y` = $y WHERE `id` = ".$army->id);
		cUnit::SetUnits($army->flucht_units,$army->id);
		foreach ($fights as $fight) {
			$enemy = (($fight->attacker!=$army->id)?$fight->attacker:$fight->defender);
			// den gegner ein bisschen beschäftigen, damit er nicht gleich nächste runder hinterherrennt und weiterkämpft
			sql("UPDATE `army` SET `idle` = 0, `nextactiontime` = ".(time()+180)." WHERE `id` = ".$enemy);
		}
		cFight::StopAllArmyFights($army,"Army _ARMYNAME_ (_ARMYOWNERNAME_) has fled.");
		cArmy::DropExcessCargo($army,$enemy,0);
		QuestTrigger_EscapeArmy($army,$x,$y);
	}
	
	
	// TODO : include ranged fight here, send warning messages for fof-state-change
	// notify parties of new fof state
	static function ActOfWar ($attacker_uid,$defender_uid,$what,$x,$y) {
		if (!$attacker_uid || !$defender_uid) return;
		if ($attacker_uid == $defender_uid) return;
		$topic = "Hostile Activity";
		if (GetFOF($attacker_uid,$defender_uid) != kFOF_Enemy) {
			$defender_name = sqlgetone("SELECT `name` FROM `user` WHERE `id` = ".intval($defender_uid));
			$report = "Because of your hostile activitiy,<br>";
			$report .= "$what at ($x,$y), $defender_name has declared you an enemy.<br>";
			SetFOF($attacker_uid,$defender_uid,kFOF_Enemy);
			sendMessage($attacker_uid,0,"Enemy:$defender_name",$report,kMsgTypeReport,FALSE);
		}
		if (GetFOF($defender_uid,$attacker_uid) != kFOF_Enemy) {
			$attacker_name = sqlgetone("SELECT `name` FROM `user` WHERE `id` = ".intval($attacker_uid));
			$report = "$attacker_name has perpetrated hostilities,<br>";
			$report .= "$what at ($x,$y), and has been declared an enemy.<br>";
			SetFOF($defender_uid,$attacker_uid,kFOF_Enemy);
			sendMessage($defender_uid,0,"Enemy:$attacker_name",$report,kMsgTypeReport,FALSE);
		}
	}
}
?>
