<?php

require_once(BASEPATH."/cronlib.php");
require_once(BASEPATH."/lib.quest.php");
require_once(BASEPATH."/lib.map.php");
require_once(BASEPATH."/lib.technology.php");
require_once(BASEPATH."/lib.spells.php");
require_once(BASEPATH."/lib.army.php"); // sql
require_once(BASEPATH."/lib.weather.php");
require_once(BASEPATH."/lib.fight.php");
require_once(BASEPATH."/lib.armythink.php"); // warning ! generates big globals, called here, so idletime add is in $gAllArmys

class Job_Shooting extends Job {
	protected function _run(){
		$time = time();
		
		$oldshootings = sqlgettable("SELECT * FROM `shooting` WHERE `autocancel` = 1 AND 
			`start`		< ".($time-kShootingAlarmTimeout)." AND 
			`lastshot`	< ".($time-kShootingAlarmTimeout));
		foreach ($oldshootings as $o) {
			cFight::EndShooting($o,"Es wurde lange nicht mehr geschossen");
		} 
		unset($oldshootings);
		
		$this->requeue(in_mins(time(),1));
	}
}

class Job_Fight extends Job {
	protected function _run(){
		
		$fights = sqlgettable("SELECT * FROM `fight`");
		if (count($fights) > 0) {
			foreach ($fights as $fight) {
				TablesLock();
				cFight::FightStep($fight);
				TablesUnlock();
			}
		}
		unset($fights);
		
		$this->requeue(in_mins(time(),1));
	}
}

class Job_ArmyThink extends Job {
	protected function _run(){
		global $gAllArmyUnits;
		
		if(!ExistGlobal("last_army_think")){
			SetGlobal("last_army_think",T);
		}
		
		$lastrescalc = GetGlobal("last_army_think");
		
		if(T - $lastrescalc > 0){
			$dtime = (T - $lastrescalc);
			echo "DT: $dtime\n";

			InitArmyThink();
			
			sql("UPDATE `army` SET `idle`=`idle`+$dtime");
			
			$c = 0;
			$r = sql("SELECT * FROM `army`");
			if ($r !== true && $r !== false) { 
				while ($army = mysql_fetch_object($r)) { //echo "army ".(++$c)."<br>";
					TablesLock();
					//if ($c++ > 100) break;
					if (!isset($gAllArmyUnits[$army->id])) warning("Army $army->id ($army->x,$army->y) has no units ??<br>");
					$army->units = isset($gAllArmyUnits[$army->id])?$gAllArmyUnits[$army->id]:array(); // constructed in lib.armythink.php
					$army->size = cUnit::GetUnitsSum($army->units);
					$army->useditemobj = $army->useditem ? sqlgetobject("SELECT * FROM `item` WHERE `id` = ".$army->useditem) : false;
					//if ($army->size < 1.0) { cArmy::DeleteArmy($army->id); continue; }
					if ($army->type == kArmyType_Fleet) // todo : $army->transport = $gAllArmyTransport[$army->id];
						$army->transport = cUnit::GetUnits($army->id,kUnitContainer_Transport);
					
					// eating : monsters and siege-armies do not eat
					if ($army->user != 0 && $army->type != kArmyType_Siege) {
						if ($army->type == kArmyType_Fleet)
								$verbrauch = $dtime * cUnit::GetUnitsEatSum($army->transport) / 3600.0;
						else	$verbrauch = $dtime * cUnit::GetUnitsEatSum($army->units) / 3600.0;
						
						if ($army->useditemobj && $army->useditemobj->type == kItem_Spam) $verbrauch *= 0.5;
						
						if ($verbrauch > 0) {
							$food = sqlgetone("SELECT `food` FROM `user` WHERE `id`=".intval($army->user));
							$hungerschaden = max(0,$verbrauch - $food);
							sql("UPDATE `user` SET `food`=GREATEST(0,`food`-$verbrauch) WHERE `id`=".$army->user);
							if ($hungerschaden > 0) {
								if($army->type == kArmyType_Fleet) {
									$army->transport = cUnit::GetUnitsAfterDamage($army->transport,$hungerschaden,$army->user);
									cUnit::SetUnits($army->transport,$army->id,kUnitContainer_Transport);
								} else {
									$army->units = cUnit::GetUnitsAfterDamage($army->units,$hungerschaden,$army->user);
									cUnit::SetUnits($army->units,$army->id);
									$army->size = cUnit::GetUnitsSum($army->units);
									if ($army->size <= 0.0) { 
										// armee ist verhungert
										cArmy::DeleteArmy($army->id); 
										TablesUnlock();
										continue; 
									}
								} 
							}
						}
					}
					
					if ($army->nextactiontime > T) {
						TablesUnlock();
						continue;
					}
					
					ArmyThink($army);
					TablesUnlock();
				}
			}
			
			mysql_free_result($r);
			
			SetGlobal("last_army_think",T);

			$this->requeue(in_mins(time(),1));
		}
	}
}

class Job_Pillage extends Job {
	protected function _run(){
		
		$pillages = sqlgettable("SELECT * FROM `pillage`");
		if (count($pillages) > 0) {
			foreach ($pillages as $pillage) {
				TablesLock();
				if (sqlgetone("SELECT 1 FROM `fight` WHERE 
					`attacker`=".intval($pillage->army)." OR 
					`defender`=".intval($pillage->army)
				) != 1){
					cFight::PillageStep($pillage);
				}
				TablesUnlock();
			}
		}
		unset($pillages);		
		
		$this->requeue(in_mins(time(),1));
	}
}

class Job_Siege extends Job {
	protected function _run(){
		
		$sieges = sqlgettable("SELECT * FROM `siege`");
		if (count($sieges) > 0) {
			foreach ($sieges as $siege) {
				TablesLock();
				if (sqlgetone("SELECT 1 FROM `fight` WHERE 
					`attacker`=".intval($siege->army)." OR 
					`defender`=".intval($siege->army)
				) != 1){
					cFight::SiegeStep($siege); 
				}
				TablesUnlock();
			}
		}
		unset($sieges);
		
		$this->requeue(in_mins(time(),1));
	}
}

?>