<?php

include_once("../cronlib.php");

class Job_FinishConstructions extends Job {
	protected function _run(){
		$cons = sqlgettable("SELECT * FROM `building` WHERE `construction` > 0","user");
		
		$gAllUsers = sqlgettable("SELECT * FROM `user` ORDER BY `id`","id");
		foreach($gAllUsers as $x) {
			$o = isset($cons[$x->id])?$cons[$x->id]:false;
			if($o) { 
				if($time > $o->construction || kZWTestMode) {
					if ($gVerbose) echo "fertiggestellt : ".$gBuildingType[$o->type]->name."(".$o->x.",".$o->y.")<br>";
					
					$now = microtime_float();
					CompleteBuild($o,($x->flags & kUserFlags_AutomaticUpgradeBuildingTo)>0);
					echo "Profile CompleteBuild : ".sprintf("%0.3f",microtime_float()-$now)."<br>\n";
				}
			} else {
				$mycon = sqlgetobject("SELECT * FROM `construction` WHERE `user`=".$x->id." ORDER BY `priority` LIMIT 1");
				if ($mycon) {
					$now = microtime_float();
					if (startBuild($mycon))
						if ($gVerbose) echo "gestartet : ".$gBuildingType[$mycon->type]->name."(".$mycon->x.",".$mycon->y.")<br>";
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

		$this->requeue(in_mins(time(),1));
	}	
}


?>