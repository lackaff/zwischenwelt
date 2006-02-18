<?php
// warning ! generates big globals
require_once("lib.fight.php");
			
// ##### ##### ##### ##### ##### ##### ##### #####
// ##### #####    army intelligence    ##### #####
// ##### ##### ##### ##### ##### ##### ##### #####

function ArmyThinkTimeShift ($armyid,$dur) {
	// cheat for debugging : skip $dur seconds in time
	sql("UPDATE `army` SET `idle`=`idle`+".intval($dur)." , `nextactiontime`=`nextactiontime`-".intval($dur).
		" WHERE `id` = ".intval($armyid));
}


function InitArmyThink () {
	global $gAllHellholes,$gAllArmys,$gAllArmyUnits,$gAllActions,$gAllPillages,$gAllSieges,$gAllFights,$gAllUsers,$gArmyShootings;
	global $gContainerType2Number;
	if (!isset($gAllUsers)) $gAllUsers = sqlgettable("SELECT * FROM `user` ORDER BY `id`","id");
	$gAllHellholes = sqlgettable("SELECT * FROM `hellhole`","id"); // used for caching army positions in GetPosSpeed() -> update moved armys in this array !!
	$gAllArmys = sqlgettable("SELECT * FROM `army`","id"); // used for caching army positions in GetPosSpeed() -> update moved armys in this array !!
	$gAllArmyUnits = sqlgetgrouptable("SELECT * FROM `unit` WHERE `army` > 0","army"); // not ordered
	$gAllActions = sqlgetgrouptable("SELECT * FROM `armyaction` ORDER BY `orderval` ASC","army");
	$gAllPillages = sqlgettable("SELECT `army` FROM `pillage` GROUP BY `army`","army");
	$gAllSieges = sqlgettable("SELECT `army` FROM `siege` GROUP BY `army`","army");
	

	$gArmyShootings = false;
	if (1) { // todo : replace by condition : only from cron (not every 30 secs, as in minicron)
		$gArmyShootings = sqlgetgrouptable("SELECT * FROM `shooting` WHERE 
			`attackertype` = ".$gContainerType2Number[kUnitContainer_Army],"attacker");
	}

	$fights_attacker = sqlgetonetable("SELECT `attacker` FROM `fight` GROUP BY `attacker`");
	$fights_defender = sqlgetonetable("SELECT `defender` FROM `fight` GROUP BY `defender`");
	$gAllFights = array_unique(array_merge($fights_attacker,$fights_defender)); // used in cron : pillage
	unset($fights_attacker);
	unset($fights_defender);
}
InitArmyThink();

// what can happen for army actions ?
// * something succeeds, sets idle to zero, doesn't move anymore this round
// * something is not possible, army waits here
// * something is not possible, action is kept for later
// * something fails, action is deleted as if it never existed

// TODO : action harvest ??
// TODO : action follow, just activates follow mode ??


function ArmyThink ($army,$debug=false) {
	if (kProfileArmyLoop) LoopProfiler("armyloop:startthink");
	global $gAllHellholes,$gAllArmys,$gAllArmyUnits,$gAllActions,$gAllPillages,$gAllSieges,$gAllFights;
	global $gTerrainType,$gRes,$gRes2ItemType,$gBuildingType,$gBodenSchatzBuildings;
	if ($debug) echo "thinking army $army->name ($army->x,$army->y)<br>";
	if (isset($gAllPillages[$army->id]))	{ if ($debug) echo "army is pillaging<br>"; return; }
	if (isset($gAllSieges[$army->id]))		{ if ($debug) echo "army is siege<br>"; return; }
	if (in_array($army->id,$gAllFights))	{ if ($debug) echo "army is fighting<br>"; return; }

	if (!isset($army->units)) $army->units = cUnit::GetUnits($army->id);
	$army->size = cUnit::GetUnitsSum($army->units);
	$army->flags = intval($army->flags);
	$wait_here = false;
	$time = time();
	
	// shooting
	if (cFight::ThinkShooting($army,kUnitContainer_Army,$debug)) return;
	
	// bodenschaetze
	if ($army->type == kArmyType_Arbeiter) {
		if (kProfileArmyLoop) LoopProfiler("armyloop:bodenschatz");
		$b = sqlgetobject("SELECT * FROM `building` WHERE `x`=".$army->x." AND `y`=".$army->y); // todo : looknear building ausnutzen !!!
		if (in_array($b->type,$gBodenSchatzBuildings)) {
			$btype = $gBuildingType[$b->type];
			$resitems = array();
			foreach ($gRes as $n=>$f) {
				$res_amount = ceil($btype->{"cost_$f"} * ($army->size / 60.0 / (float)kBodenSchatzIdealWorkers)); // baukosten = stundenprod.
				$res_type = $gRes2ItemType[$f];
				if ($res_amount > 0) cItem::SpawnItem($army->x,$army->y,$res_type,$res_amount,0,0);
			}
		}
	}
	
	// harvest
	if ($army->flags & (kArmyFlag_HarvestForest|kArmyFlag_HarvestRubble|kArmyFlag_HarvestField)) {
		if (kProfileArmyLoop) LoopProfiler("armyloop:harvest");
		$maxlast = cUnit::GetUnitsSum($army->units,"last");
		$curlast = cArmy::GetArmyTotalWeight($army); // todo : otpimize me !
		if ($curlast < $maxlast) {
			$terraintype = cMap::StaticGetTerrainAtPos($army->x,$army->y);
			$coltime = cArmy::GetArmyCollectTime($army,$terraintype);
			if ($coltime > 0 && (
				($terraintype == kTerrain_Forest && ($army->flags & kArmyFlag_HarvestForest)) || 
				($terraintype == kTerrain_Rubble && ($army->flags & kArmyFlag_HarvestRubble)) || 
				($terraintype == kTerrain_Field && ($army->flags & kArmyFlag_HarvestField)) )) {
				if ($army->idle >= $coltime) {
					if ($debug) echo "harvesting ".$gTerrainType[$terraintype]->name."<br>";
					cArmy::ArmyCollect($army,$terraintype);
					$army->idle = 0;
					sql("UPDATE `army` SET `idle`=0 WHERE `id`=".$army->id);
					return;
				} else {
					if ($debug) echo "waiting to harvest ".$gTerrainType[$terraintype]->name."<br>";
					$wait_here = true;
				}
			}
		}
	}
	
	
	if (kProfileArmyLoop) if ($army->follow) LoopProfiler("armyloop:Follow");
	
	// look for nearby armies
	$enemies = array();
	$followarmy = $army->follow ? sqlgetobject("SELECT * FROM `army` WHERE `id` = ".intval($army->follow)) : false;
	if ($followarmy && hypot($army->x-$followarmy->x,$army->y-$followarmy->y) < 11)
		$enemies[] = $followarmy;
	$armylook = 0;
	$armylook |= kArmyFlag_AutoAttack; // near
	$armylook |= kArmyFlag_RunToEnemy|kArmyFlag_AutoAttackRangeMonster; // far
	$armylook |= kArmyFlag_AutoGive_Own|kArmyFlag_AutoGive_Guild|kArmyFlag_AutoGive_Friend; //near
	if ($army->flags & $armylook) {
		if (kProfileArmyLoop) LoopProfiler("armyloop:lookNear");
		$r = ($army->flags & (kArmyFlag_RunToEnemy|kArmyFlag_AutoAttackRangeMonster)) ? 7 : 1;
		$x = $army->x;
		$y = $army->y;
		if ($army->user) {
			$enemy_userids = GetFOFUserlist($army->user,kFOF_Enemy);
			$enemy_userids[] = 0; // monsters are enemies
			//$cond = "`user` IN (".implode(",",$enemy_userids).")";
			$calc = ",((`x`-($x))*(`x`-($x)) + (`y`-($y))*(`y`-($y))) as `dist`";
			$order = "`dist`";
		} else {
			//$cond = "`user` > 0";
			$calc = "";
			$order = "RAND()";
		}
		$near_armies = sqlgettable("SELECT * $calc FROM `army` WHERE `id` <> ".$army->id." AND 
			`x` >= ".($x-$r)." AND `x` <= ".($x+$r)." AND 
			`y` >= ".($y-$r)." AND `y` <= ".($y+$r)." ORDER BY $order");
		foreach ($near_armies as $o) {
			if ($army->user && !in_array($o->user,$enemy_userids)) continue;
			if (!$army->user && $o->user == 0) continue;
			$enemies[] = $o;
		}
		foreach ($enemies as $k => $enemy) $enemies[$k]->units = $gAllArmyUnits[$enemy->id];
		if ($debug) echo "looking for enemies in radius $r, found ".count($enemies)."<br>";
		if ($army->user == 0 && !$followarmy && count($enemies) > 0) {
			// monster : remeber followed army
			$followarmy = $enemies[0];
			sql("UPDATE `army` SET ".arr2sql(array("follow"=>$followarmy->id))." WHERE `id` = ".intval($army->id));
		}
		
		// worker : autogive :
		if (count($near_armies) > 0 && ($army->flags & (kArmyFlag_AutoGive_Own|kArmyFlag_AutoGive_Guild|kArmyFlag_AutoGive_Friend))) {
			if (kProfileArmyLoop) LoopProfiler("armyloop:autogive");
			if ($debug) echo "attempting autogive<br>";
			foreach ($near_armies as $o) {
				$ok = false;
				if (!cArmy::ArmyAtDiag($army,$o->x,$o->y)) continue;
				if (!$ok && $army->flags & kArmyFlag_AutoGive_Own && $o->user == $army->user) $ok = true;
				if (!$ok && $army->flags & kArmyFlag_AutoGive_Guild && IsInSameGuild($army->user,$o->user)) $ok = true;
				if (!$ok && $army->flags & kArmyFlag_AutoGive_Friend && GetFOF($army->user,$o->user) == kFOF_Friend) $ok = true;
				if (!$ok) continue;
				if ($debug) echo "autogive to $o->name at $o->x,$o->y<br>";
				$items = sqlgettable("SELECT * FROM `item` WHERE `x`=".$army->x." AND `y`=".$army->y." AND `army` = 0 AND `building` = 0");
				foreach ($items as $item) {
					cItem::pickupItem($item,$o,-1,true);
					$o = sqlgetobject("SELECT * FROM `army` WHERE `id`=".$o->id);
				}
			}
		}
	}
	
	// AutoAttack
	if (($army->flags & kArmyFlag_AutoAttack)) foreach ($enemies as $enemy) if (cFight::FightPossible($army,$enemy,$debug)) {
		if (kProfileArmyLoop) LoopProfiler("armyloop:AutoAttack");
		if ($debug) echo "AutoAttack army: $enemy->x,$enemy->y<br>";
		if (TryExecArmyAction($army,ARMY_ACTION_ATTACK,$enemy->id,0,0,0,$debug)) return;
	}
	
	// AutoAttackRangeMonster
	/*
	TODO : check me, and convert to new shooting system
	if (($army->flags & kArmyFlag_AutoAttackRangeMonster) && 
		$army->idle >= kArmyAutoAttackRangeMonster_Timeout && 
		cArmy::hasDistantAttack($army)) {
		if (kProfileArmyLoop) LoopProfiler("armyloop:AutoAttackRangeMonster");
		$found = false;
		if (isset($gAllActions[$army->id])) foreach ($gAllActions[$army->id] as $act) 
			if ($act->cmd == ARMY_ACTION_RANGEATTACK) { $found = true; break; } 
		if (!$found) {
			echo "trying AutoAttackRangeMonster<br>";
			foreach ($enemies as $enemy) if ($enemy->user == 0 && cArmy::inDistantRange($army->x-$enemy->x,$army->y-$enemy->y,$army)) {							
				if ($debug) echo "AutoAttackRangeMonster : army $army->name [$army->id] tried a shoot at $enemy->name [$enemy->id]<br>";
				if (TryExecArmyAction($army,ARMY_ACTION_RANGEATTACK,$enemy->id,0,0,0,$debug)) return;
			}
		} else echo "no AutoAttackRangeMonster, already have ARMY_ACTION_RANGEATTACK<br>";
	}
	*/
	
	// look for nearby buildings
	$nearbuildings = false;
	$nearbuildings_rad = -1;
	if ($army->flags & (kArmyFlag_AutoSiege|kArmyFlag_AutoDeposit|kArmyFlag_AutoPillage)) {
		if (kProfileArmyLoop) LoopProfiler("armyloop:LookNearBuildings");
		$r = 1;
		$nearbuildings_rad = $r;
		$x = $army->x;
		$y = $army->y;
		$nearbuildings = sqlgettable("SELECT * FROM `building` WHERE 1
			AND `x` >= ".($x-$r)." AND `x` <= ".($x+$r)." 
			AND `y` >= ".($y-$r)." AND `y` <= ".($y+$r)."");
		if ($debug) echo "looking for buildings in radius $r, found ".count($nearbuildings)."<br>";
	}
	
	// AutoSiege
	if ($army->flags & kArmyFlag_AutoSiege) {
		if (kProfileArmyLoop) LoopProfiler("armyloop:AutoSiege");
		foreach ($nearbuildings as $building) {
			if ($army->user == 0 && $building->user == 0) continue;
			if ($army->user > 0 && GetFOF($army->user,$building->user) != kFOF_Enemy) continue;
			if ($debug) echo "AutoSiege buildingid:$building->id<br>";
			if (TryExecArmyAction($army,ARMY_ACTION_SIEGE,$building->x,$building->y,0,0,$debug)) return;
		}
	}
	
	// AutoPillage
	if ( ($army->flags & kArmyFlag_AutoPillage) && 
		!($army->flags & kArmyFlag_AutoPillageOff)) {
		if (kProfileArmyLoop) LoopProfiler("armyloop:AutoPillage");
		foreach ($nearbuildings as $building) {
			if ($building->type != kBuilding_Silo) continue;
			if ($army->user == 0 && $building->user == 0) continue;
			if ($army->user > 0 && GetFOF($army->user,$building->user) != kFOF_Enemy) continue;
			if ($debug) echo "AutoPillage buildingid:$building->id<br>";
			if (TryExecArmyAction($army,ARMY_ACTION_PILLAGE,$building->x,$building->y,-1,0,$debug)) {
				sql("UPDATE `army` SET `flags` = `flags` | ".kArmyFlag_AutoPillageOff." WHERE `id` = ".$army->id);
				return;
			}
		}
	}
		
	// AutoDeposit
	if (($army->flags & kArmyFlag_AutoDeposit) && !($army->flags & kArmyFlag_BuildingWait)) {
		if (kProfileArmyLoop) LoopProfiler("armyloop:AutoDeposit");
		if (count($nearbuildings) > 0 && ($army->flags & kArmyFlag_AlwaysCollectItems)) cItem::pickupall($army);
		foreach ($nearbuildings as $building) {
			if ($building->construction > 0 || $building->type != kBuilding_Silo) continue;
			if ($army->user != $building->user && GetFOF($army->user,$building->user) != kFOF_Friend) continue;
			if ($debug) echo "AutoDeposit buildingid:$building->id<br>";
			if (TryExecArmyAction($army,ARMY_ACTION_DEPOSIT,$building->x,$building->y,-1,0,$debug)) {
				sql("UPDATE `army` SET `flags` = `flags` | ".kArmyFlag_BuildingWait." WHERE `id` = ".$army->id);
				return;
			}
		}
	}
	
	// execute actions/commands
	if (isset($gAllActions[$army->id])) {
		if (kProfileArmyLoop) LoopProfiler("armyloop:ArmyActions");
		$myactions = $gAllActions[$army->id];
		if ($debug) echo "execute commands(".count($myactions).")<br>";
		foreach($myactions as $act) {
			if ($act->cmd == ARMY_ACTION_WAIT) {
				if ($act->starttime == 0) {
					$lastwp = sqlgetobject("SELECT * FROM `waypoint` WHERE `army` = ".$army->id." ORDER BY `priority` LIMIT 1");
					if ($lastwp->id == $act->param1) {
						// start waiting
						sql("UPDATE `armyaction` SET `starttime` = ".$time." WHERE `id` = ".$act->id);
						if ($debug) echo "ARMY_ACTION_WAIT : gestartet<br>";
						$wait_here = true;
						// do NOT set nextactiontime, army is active and CAN execute actions (such as attack nearby armies)
					}
				} else {
					if ($army->idle <= $act->param2) {
						// still waiting
						if ($debug) echo "ARMY_ACTION_WAIT : $act->army,$act->param1,$act->param2 <br>";
						$wait_here = true;
					} else {
						// done waiting
						if ($debug) echo "ARMY_ACTION_WAIT : beendet<br>";
						sql("DELETE FROM `armyaction` WHERE `id` = ".$act->id);
					}
				}
			} else {
				if (TryExecArmyAction($army,$act->cmd,$act->param1,$act->param2,$act->param3,$act->id,$debug)) return;
			}
		}
	}
	
	
	// move army
	if ($wait_here) if ($debug) echo "army is waiting here...<br>";
	if ($wait_here) return;
	
	$pos = false;
	$x = $army->x;
	$y = $army->y;
	
	// run to enemy (only those who can be attacked by melee, eg not fleets)
	if (!$pos && ($army->flags & kArmyFlag_RunToEnemy)) foreach ($enemies as $enemy) if (cFight::FightPossible($army,$enemy,$debug)) {
		if (kProfileArmyLoop) LoopProfiler("armyloop:runToEnemy");
		$dx = $enemy->x - $x;
		$dy = $enemy->y - $y;
		if ((rand(0,1) && $dx != 0) || $dy == 0) 
				$pos = array($x+($dx>0?1:-1),$y);
		else	$pos = array($x,$y+($dy>0?1:-1));
		if ($debug) echo "army is running to enemy at (".$enemy->x.",".$enemy->y.")<br>";
		break;
	}
	
	// wander or waypoint
	if (!$pos) {
		if (kProfileArmyLoop) LoopProfiler("armyloop:getwps");
		$wps = sqlgettable("SELECT * FROM `waypoint` WHERE `army` = ".$army->id." ORDER BY `priority` LIMIT 3");
		if (count($wps) < 2 && ($army->flags & kArmyFlag_Wander)) {
			if (kProfileArmyLoop) LoopProfiler("armyloop:wander");
			$d = rand(0,1)?-1:1;
			$pos = rand(0,1)?array($x,$y+$d):array($x+$d,$y);
			if ($debug) echo "army is wandering to (".$pos[0].",".$pos[1].")<br>";
			if ($army->hellhole) {
				$hellhole = isset($gAllHellholes[$army->hellhole]) ? $gAllHellholes[$army->hellhole] : false;
				if ($hellhole) {
					$olddist = hypot($x - $hellhole->x,$y - $hellhole->y);
					$newdist = hypot($pos[0] - $hellhole->x,$pos[1] - $hellhole->y);
					if ($newdist > $hellhole->radius && $olddist < $newdist) {
						if ($debug) echo "army doesn't wander further away from hellhole<br>";
						return; // out of radius -> only move towards hellhole
					}
				}
			}
		} else {
			if (count($wps) < 2) {
				if ($army->flags & kArmyFlag_BuildingWait) {
					$army->flags = $army->flags & (~kArmyFlag_BuildingWait); // don't try again and again...
					sql("UPDATE `army` SET `flags` = ".$army->flags." WHERE `id` = ".$army->id);
					//echo "deaktiviere buildingwait<br>";
				}
				//echo "keine wegpunkte, fertig<br>";
				return;
			}
			if (kProfileArmyLoop) LoopProfiler("armyloop:gotowp");
			if ($debug) echo "army walking to waypoint (".$wps[1]->x.",".$wps[1]->y.")<br>";
			$pos = GetNextStep($x,$y,$wps[0]->x,$wps[0]->y,$wps[1]->x,$wps[1]->y);
			if ($pos[0] == $x && $pos[1] == $y) {
				// arrive at waypoint
				if ($debug) echo "arrive at waypoint($x,$y)<br>";
				$army->flags = $army->flags | kArmyFlag_LastWaypointArrived;
				$army->flags = $army->flags & (~kArmyFlag_BuildingWait); // don't try again and again...
				$army->flags = $army->flags & (~kArmyFlag_AutoPillageOff); // don't pillage again and again, even if not moving
				sql("UPDATE `army` SET `flags` = ".$army->flags." WHERE `id` = ".$army->id);
				
				if (count($wps) > 2) {
					sql("DELETE FROM `waypoint` WHERE `id` = ".$wps[0]->id); // delete old startwp
					sql("UPDATE `waypoint` SET `priority` = 0 WHERE `id` = ".$wps[1]->id); // set new startwp
					
					if ($army->flags & kArmyFlag_Patrol) {
						// patroullienmodus
						cArmy::ArmySetWaypoint($army->id,$x,$y);
						if ($debug) echo "Patrol:repeat waypoint(".$x.",".$y.")<br>";
					}
					if ($debug) echo "army walking to waypoint (".$wps[2]->x.",".$wps[2]->y.")<br>";
					$pos = GetNextStep($x,$y,$wps[1]->x,$wps[1]->y,$wps[2]->x,$wps[2]->y);
					if ($pos[0] == $x && $pos[1] == $y) return;
				} else {
					// arrived at final wp => remove all wp
					sql("DELETE FROM `waypoint` WHERE `army` = ".$army->id);
					return;
				}
			} 
		}
	}
	
	// try move to $pos
	if ($pos) {
		if (kProfileArmyLoop) LoopProfiler("armyloop:getposspeed");
		$buildingbelow = -1; // GetPosSpeed speedup, if nearbuildings have been read out, use them, otherwise leave as -1
		if ($nearbuildings_rad >= 1) {
			$buildingbelow = false;
			foreach ($nearbuildings as $o) if ($o->x == $pos[0] && $o->y == $pos[1]) {
				$buildingbelow = $o; break;
			}
		}
		$speed = cArmy::GetPosSpeed($pos[0],$pos[1],$army->user,$army->units,false,$buildingbelow);
		if (kProfileArmyLoop) LoopProfiler("armyloop:getarmyspeed");
		$armyspeed = cArmy::GetArmySpeed($army);
		if (kProfileArmyLoop) LoopProfiler("armyloop:get blocking army");
		if ($armyspeed <= 0) $speed = 0; 
		if ($speed > 0 && $armyspeed > 0) $speed = max($speed,$armyspeed);
		if ($speed > 0 && sqlgetone("SELECT 1 FROM `army` WHERE `x` = ".$pos[0]." AND `y` = ".$pos[1]." LIMIT 1")) $speed = 0;
		if ($speed == 0) {
			if (kProfileArmyLoop) LoopProfiler("armyloop:moveblocked");
			if ($debug) echo "army is blocked<br>";
			// armee blockiert
			if ($army->flags & kArmyFlag_SiegeBlockingBuilding &&
				(!sqlgetone("SELECT 1 FROM `army` WHERE `x` = ".$pos[0]." AND `y` = ".$pos[1]." LIMIT 1")) &&
				($blocking=sqlgetobject("SELECT * FROM `building` WHERE `x` = ".$pos[0]." AND `y` = ".$pos[1]." LIMIT 1"))) {
				// siege blocking
				if ($debug) echo "army tries to siege blocking<br>";
				if (TryExecArmyAction($army,ARMY_ACTION_SIEGE,$blocking->x,$blocking->y,0,0,$debug)) return;
			} 
			if ($army->flags & kArmyFlag_AttackBlockingArmy &&
				($blocking=sqlgetobject("SELECT * FROM `army` WHERE `x` = ".$pos[0]." AND `y` = ".$pos[1]." LIMIT 1"))) {
				// attack-blocking
				if ($blocking->user != $army->user) { // don't attack own armies
					if ($debug) echo "army tries to attack blocking<br>";
					if (TryExecArmyAction($army,ARMY_ACTION_ATTACK,$blocking->id,0,0,0,$debug)) return;
				}
			} 
			
			if (($army->flags & kArmyFlag_RecalcBlockedRoute) && $army->idle >= kArmyRecalcBlockedRoute_Timeout &&
				($army->flags & kArmyFlag_LastWaypointArrived)) {
				// search way around
				if ($debug) echo "army tries to find a way around<br>";
				require_once("lib.path.php");
				cPath::ArmyRecalcNextWP($army,$wps);
				$army->flags = $army->flags & (~kArmyFlag_LastWaypointArrived); // don't try again and again...
				sql("UPDATE `army` SET `flags` = ".$army->flags." WHERE `id` = ".$army->id);
			}
		} else if ($army->idle >= $speed && $speed > 0) {
			if (kProfileArmyLoop) LoopProfiler("armyloop:moveok");
			if ($debug) echo "army moves<br>";
			//armee bewegt sich ,update $gAllArmys cache
			$army->x = $pos[0];
			$army->y = $pos[1];
			$gAllArmys[$army->id]->x = $pos[0];
			$gAllArmys[$army->id]->y = $pos[1];
			$army->flags = $army->flags & (~kArmyFlag_AutoPillageOff); // don't pillage again and again, even if not moving
			sql("UPDATE `army` SET `flags` = ".$army->flags." , `nextactiontime` = ".($time+$speed)." , `idle`=0,`x` = ".$pos[0]." , `y` = ".$pos[1]." WHERE `id` = ".$army->id);
			cArmy::AddSteps($pos[0],$pos[1],$army->size);
			QuestTrigger_ArmyMove($army,$pos[0],$pos[1]);
		} else if ($debug) echo "army must wait (idle=".$army->idle.",speed=".$speed.")<br>";
	}
}







// ##### ##### ##### ##### ##### ##### ##### #####
// ##### #####      army  actions      ##### #####
// ##### ##### ##### ##### ##### ##### ##### #####


	
	
function TryExecArmyAction ($army,$cmd,$param1,$param2,$param3,$actid,$debug=false) {
	global $gAllHellholes,$gAllArmys,$gAllArmyUnits,$gAllActions,$gAllPillages,$gAllSieges,$gAllFights;
	$action_complete = false;
	
	switch($cmd) {
		case ARMY_ACTION_ATTACK:
			$enemy = $gAllArmys[$param1];
			if ($enemy) $enemy->units = $gAllArmyUnits[$enemy->id];
			if (cFight::StartFight($army,$enemy,$debug)) {
				$gAllFights[] = $army->id;
				$gAllFights[] = $enemy?$enemy->id:0; // if enemy is already dead(false), StartFight returns true(done)
				$action_complete = true;
			}
		break;
		case ARMY_ACTION_SIEGE:
			if (cFight::StartSiege($army,$param1,$param2,$debug)) {
				$gAllSieges[$army->id] = $army->id;
				$action_complete = true;
			}
		break;
		case ARMY_ACTION_PILLAGE:
			if (cFight::StartPillage($army,$param1,$param2,$param3,$debug)) {
				$gAllPillages[$army->id] = $army->id;
				$action_complete = true;
			}
		break;
		case ARMY_ACTION_DEPOSIT: // ressourcen einzahlen	
			if (cArmy::inPillageRange($army->x-$param1,$army->y-$param2)) {
				global $gRes;
				$b = 1; foreach ($gRes as $n=>$f) { 
					${$f} = ($param3 == -1 || (intval($param3) & $b)) ? max(0,$army->{$f}) : 0 ; 
					$b = $b << 1; 
				}
				$building = sqlgetobject("SELECT * FROM `building` WHERE `x` = ".intval($param1)." AND `y` = ".intval($param2));
				if (!$building) { $action_complete = true; break; }
				cArmy::ArmyGetRes($army->id,$building->user,-$lumber,-$stone,-$food,-$metal,-$runes);
				$action_complete = true;
			}
		break;
	}
	
	if ($action_complete) {
		if ($actid) sql("DELETE FROM `armyaction` WHERE `id`=".$actid);
		sql("UPDATE `army` SET `idle`=0 WHERE `id`=".$army->id);
	}
	return $action_complete;
}
?>