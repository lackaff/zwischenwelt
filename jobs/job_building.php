<?php

require_once(BASEPATH."/cronlib.php");

class Job_FinishUnits extends Job {
	protected function _run(){
		global $gBuildingType, $gUnitType;
		
		// process running actions
		$running_actions = sqlgettable("SELECT * FROM `action` WHERE `starttime` > 0 GROUP BY `building`");
		foreach ($running_actions as $action) {
			$unittype = $gUnitType[$action->param1];
			
			// has one action cycle completed ?
			if ($time >= ($action->starttime + $unittype->buildtime) || kZWTestMode) {						
				if ($action->param2 > 0) {
					// unit complete
					switch ($action->cmd) {
						case kActionCmd_Build:
							$curtargetid = GetBParam($action->building,"target",0);
							// TODO : der check hier ob das gebaeude existiert geht vermutlich auf die performance, bessere bei gebaeude tot alle stationierungen auf das gebaeude canceln
							if (!sqlgetone("SELECT 1 FROM `building` WHERE `id` = ".intval($curtargetid))) $curtargetid = 0;
							if ($curtargetid == 0) $curtargetid = $action->building;
							
							// pay pop
							$actionuserid = intval(sqlgetone("SELECT `user` FROM `building` WHERE `id` = ".intval($action->building)));
							sql("UPDATE `user` SET `pop`=`pop`-1 WHERE `id`=$actionuserid");
							
							cUnit::AddUnits($curtargetid,$action->param1,1,kUnitContainer_Building,$actionuserid);
						break;
					}
					
					//echo "action ".$action->id." : in building ".$action->building." produced one ".$unittype->name." (".($action->param2-1)." left)<br>";
	
					if ($action->param2-1 > 0)
							sql("UPDATE `action` SET `starttime` = 0 , `param2` = `param2` - 1 WHERE `id` = ".$action->id);
					else	sql("DELETE FROM `action` WHERE `id` = ".$action->id);
				} else sql("DELETE FROM `action` WHERE `id` = ".$action->id);
			}
		}
		unset($running_actions);
				
		$availableUnitTypesByUser = array();
	
		// start action where building has nothing to do
		$waiting_actions = sqlgettable("SELECT *,MAX(`starttime`) as `maxstarttime` FROM `action` GROUP BY `building`");
		foreach ($waiting_actions as $action) if ($action->maxstarttime == 0) {
			$unittype = $gUnitType[$action->param1];
			$actionuserid = intval(sqlgetone("SELECT `user` FROM `building` WHERE `id` = ".intval($action->building)));
			
			$availableUnitTypes = false;
			if (isset($availableUnitTypesByUser[$actionuserid])) {
				$availableUnitTypes = $availableUnitTypesByUser[$actionuserid];
			} else {
				$availableUnitTypes = array();
				$availableUnitTypesByUser[$actionuserid] = $availableUnitTypes;
			}
			
			$available = false;
			if (isset($availableUnitTypes[$unittype->id])) {
				$available = $availableUnitTypes[$unittype->id];
			} else {
				$available = HasReq($unittype->req_geb,$unittype->req_tech_a.",".$unittype->req_tech_v,$actionuserid);
				$availableUnitTypesByUser[$actionuserid][$unittype->id] = $available;
			}
			
			// only build if the technological requirements are met
			if (!$available) {
				sql("DELETE FROM `action` WHERE `id` = ".$action->id);
				continue;
			}
			
			// building weight-limit, used to block ramme
			$max_weight_left_source = cUnit::GetMaxBuildingWeight($gUnitType[$action->param1]->buildingtype);
			if ($max_weight_left_source >= 0) {
				$curtargetid = GetBParam($action->building,"target",0);
				if ($curtargetid == 0) $curtargetid = $action->building;
				$max_weight_left_source -= cUnit::GetUnitsSum(cUnit::GetUnits($curtargetid,kUnitContainer_Building),"weight");
				if ($max_weight_left_source < $gUnitType[$action->param1]->weight) continue;
			}
			
			if (sqlgetone("SELECT `pop` FROM `user` WHERE `id` = ".intval($actionuserid)) <= 0 || 
				!UserPay($actionuserid,$unittype->cost_lumber,$unittype->cost_stone,
										$unittype->cost_food,$unittype->cost_metal,$unittype->cost_runes))
			{
				//echo "action ".$action->id." : in building ".$action->building." (".$action->param2." ".$unittype->name.") is waiting for ressources<br>";
				continue;
			}
									
			sql("UPDATE `action` SET `starttime` = ".$time." WHERE `id` = ".$action->id);
			//echo "action ".$action->id." in building ".$action->building." started<br>";
		}
		unset($waiting_actions);
			
		$this->requeue(in_mins(time(),1));
	}	
}

class Job_FinishConstructions extends Job {
	protected function _run(){
		global $gBuildingType;
		
		// finish building construction
		$time = time();
		$cons = sqlgettable("SELECT * FROM `building` WHERE `construction` <= ".intval($time),"user");
		
		foreach($cons as $o) {
			$x = sqlgetobject("SELECT * FROM `user` WHERE `id`=".intval($o->user));
			if($time >= $o->construction) {
				echo "fertiggestellt : ".$gBuildingType[$o->type]->name."(".$o->x.",".$o->y.")<br>";
				
				$now = microtime_float();
				CompleteBuild($o,($x->flags & kUserFlags_AutomaticUpgradeBuildingTo)>0);
				echo "Profile CompleteBuild : ".sprintf("%0.3f",microtime_float()-$now)."<br>\n";
			}
		}
		unset($cons);
		
		// start new constructions
		$users = sqlgettable("SELECT * FROM `user` ORDER BY `id`","id");
		foreach($users as $x) {
			$cons = sqlgetone("SELECT COUNT(*) FROM `building` WHERE `construction`>0 AND `user`=".intval($x->id));
			if($cons == 0){
				$mycon = sqlgetobject("SELECT * FROM `construction` WHERE `user`=".$x->id." ORDER BY `priority` LIMIT 1");
				if ($mycon) {
					$now = microtime_float();
					if (startBuild($mycon))
						echo "gestartet : ".$gBuildingType[$mycon->type]->name."(".$mycon->x.",".$mycon->y.")<br>";
					echo "Profile startBuild : ".sprintf("%0.3f",microtime_float()-$now)."<br>\n";
				}
			}
		}
		unset($cons);
		
		$this->requeue(in_mins(time(),1));
	}	
}

class Job_UpgradeBuildings extends Job {
	protected function _run(){
		$time = time();
		$hqlevels = sqlgettable("SELECT `user`,`level` FROM `building` WHERE `type`=".kBuilding_HQ,"user");
		$sieges = sqlgettable("SELECT `building` FROM `siege`","building");
		
		$count_start = 0;
		$count_end = 0;
		//$buildings = sqlgettable("SELECT * FROM `building` WHERE `upgrades` > 0 ORDER BY `level`");  OLD
		$mysqlresult = sql("SELECT * FROM `building` WHERE `upgrades` > 0 AND upgradetime < $time");
		echo "testing ".mysql_num_rows($mysqlresult)." buildings for upgrades<br>\n";
		while ($o = mysql_fetch_object($mysqlresult)) {
			if ($o->upgradetime > 0 && ($o->upgradetime < $time || kZWTestMode)) {
				$count_end++;
				// upgrade finished
				if (isset($sieges[$o->id])) continue;
				$maxhp = cBuilding::calcMaxBuildingHp($o->type,$o->level+1);
				$up = $maxhp - cBuilding::calcMaxBuildingHp($o->type,$o->level);
				$heal = $maxhp/100*2.0;
				
				sql("UPDATE `building` SET
					`level` = `level` + 1 ,
					`upgrades` = GREATEST(0,`upgrades` - 1) ,
					`hp` = LEAST(`hp`+".($up+$heal)." , $maxhp),
					`upgradetime` = 0 WHERE `id` = ".$o->id." LIMIT 1");
				// echo "upgrade auf ".($o->level+1)." fertig : ".$gBuildingType[$o->type]->name."(".$o->x."|".$o->y."), hpup=".$up.", hpheal=".$heal."<br>\n";
				LogMe($o->user,NEWLOG_TOPIC_BUILD,NEWLOG_UPGRADE_FINISHED,$o->x,$o->y,$o->level+1,$gBuildingType[$o->type]->name,"",false);
				//$o = sqlgetobject("SELECT * FROM `building` WHERE `id`=".$o->id." LIMIT 1");
				$o->level++;
				$o->upgrades = max(0,$o->upgrades-1);
				$o->hp = min($o->hp+$up+$heal,$maxhp);
				$o->upgradetime = 0;
				Hook_UpgradeBuilding($o);
			} else if ($o->upgradetime == 0) {
				$count_start++;
				// test if upgrade can be started
				if (!isset($hqlevels[$o->user])) continue;
				if (isset($sieges[$o->id])) continue;
				$hqlevel = $hqlevels[$o->user]->level;
				$level = $o->level + 1;
				if($level <= (3*($hqlevel+1))) { // TODO : unhardcode
					$mod = cBuilding::calcUpgradeCostsMod($level);
					if (UserPay($o->user,
						$mod * $gBuildingType[$o->type]->cost_lumber,
						$mod * $gBuildingType[$o->type]->cost_stone,
						$mod * $gBuildingType[$o->type]->cost_food,
						$mod * $gBuildingType[$o->type]->cost_metal,
						$mod * $gBuildingType[$o->type]->cost_runes)) {
						// echo "upgrade auf $level gestartet : ".$gBuildingType[$o->type]->name."(".$o->x."|".$o->y.")<br>\n";
						$finishtime = $time + cBuilding::calcUpgradeTime($o->type,$level);
						sql("UPDATE `building` SET `upgradetime` = ".$finishtime." WHERE `id` = ".intval($o->id)." LIMIT 1");
					}
				}
			}
		}
		echo "<br>\ns=".($count_start++);
		echo "<br>\ne=".($count_end++)."<br>\n";
		mysql_free_result($mysqlresult);
		unset($sieges);
		unset($hqlevels);

		$this->requeue(in_mins(time(),1));
	}	
}

class Job_ThinkBuildings extends Job {
	protected function _run(){
		global $gFlaggedBuildingTypes;
		
		$typelist = array_merge($gFlaggedBuildingTypes[kBuildingTypeFlag_CanShootArmy],$gFlaggedBuildingTypes[kBuildingTypeFlag_CanShootBuilding]);
		if (count($typelist) > 0) {
			$buildings = sqlgettable("SELECT * FROM `building` WHERE `type` IN (".implode(",",$typelist).")");
			foreach ($buildings as $o) {
				cBuilding::Think($o);
			}
		}

		$this->requeue(in_mins(time(),5));
	}	
}

class Job_RepairBuildings extends Job {
	protected function _run(){
			if(!ExistGlobal("last_repair")){
			SetGlobal("last_repair",T);
		}
		
		$last = GetGlobal("last_repair");
		
		if(T - $last > 0){
			$dtime = (T - $last);
			echo "DT: $dtime\n";
				
			TablesLock();
			$t = sqlgettable("SELECT `user`.`id` as `id`, COUNT( * ) as `broken`,`user`.`pop` as `pop`,`user`.`worker_repair` as `worker_repair`
			FROM `user`, `building`, `buildingtype`
			WHERE 
				`building`.`construction`=0 AND `buildingtype`.`id` = `building`.`type` AND `building`.`user` = `user`.`id` AND `user`.`worker_repair`>0 AND 
				`building`.`hp`<CEIL(`buildingtype`.`maxhp`+`buildingtype`.`maxhp`/100*1.5*`building`.`level`)
			GROUP BY `user`.`id`");
			foreach($t as $x){
				//one worker should be able to repair one hp in one day and consume 100 wood and 100 stone for this
				if($x->broken == 0)continue;
				$worker = $x->pop * $x->worker_repair/100;
				$broken = $x->broken;
				$all = $worker*$dtime/(24*60*60);
				$plus = $all / $broken;
				$wood = $all * 100;
				$stone = $all * 100;
				
				echo "$worker worker repair $all, $plus hp in $broken buildings of user $x->id consuming $wood wood and $stone stone\n<br>";
				if(!UserPay($x->id,$wood,$stone,0,0,0))continue;
				sql("UPDATE `building`, `buildingtype` SET `building`.`hp` = LEAST(
					`building`.`hp`+($plus),
					CEIL(`buildingtype`.`maxhp`+`buildingtype`.`maxhp`/100*1.5*`building`.`level`)
					) WHERE 
					`building`.`construction`=0 AND `building`.`user`=".intval($x->id)." AND `building`.`type`=`buildingtype`.`id` AND
					`building`.`hp`<CEIL(`buildingtype`.`maxhp`+`buildingtype`.`maxhp`/100*1.5*`building`.`level`)");
				echo mysql_affected_rows()." buildings updated\n<br>";
			}
			sql("UPDATE `building`, `buildingtype` SET `building`.`hp` = CEIL(`buildingtype`.`maxhp`+`buildingtype`.`maxhp`/100*1.5*`building`.`level`) WHERE 
				`building`.`construction`=0 AND `building`.`type`=`buildingtype`.`id` AND
				`building`.`hp`>CEIL(`buildingtype`.`maxhp`+`buildingtype`.`maxhp`/100*1.5*`building`.`level`)");
			echo mysql_affected_rows()." buildings had to much hp and were reduced to maxhp\n<br>";
			TablesUnlock();
				
			SetGlobal("last_repair",T);
		}

		$this->requeue(in_mins(time(),1));
	}	
}


?>