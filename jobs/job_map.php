<?php

class Job_RuinCorruption extends Job {
	protected function _run(){
		// collapse probability
		$r = rand(0,100);
		// number of buildings to collapse
		$n = 100;
		$t = sqlgettable("SELECT b.* FROM `buildingtype` t LEFT JOIN `building` b ON b.type=t.id WHERE `collapse_prob`>0 AND `collapse_prob` < ".intval($r)." ORDER BY RAND() LIMIT ".intval($n));
		foreach($t as $b){
			$siege = sqlgetone("SELECT 1 FROM `siege` WHERE `building`=".intval($b->id)) == 1;
			if($siege){
				print "under siege so dont collapse ".$b->id."<br>\n";
			} else {
				print "collapse building ".$b->id."<br>\n";
				cBuilding::removeBuilding($b,$b->user,true,false);
			}
		}

		$this->requeue(in_mins(time(),5));
	}	
}

class Job_ItemCorruption extends Job {
	protected function _run(){
		TablesLock();
		$gammelitems = sqlgettable("SELECT `item`.`id` FROM `item`,`itemtype` WHERE `itemtype`.`id` = `item`.`type` AND
			 `itemtype`.`gammeltime` > 0 AND `item`.`param` > 0 AND `item`.`param` <= $time");
		foreach ($gammelitems as $o) {
			echo "item bei $o->x,$o->y vergammelt<br>";
			/*
			if ($gItemType[$o->type]->gammeltype == 0)
					sql("DELETE FROM `item` WHERE `id` = ".$o->id." LIMIT 1");
			else	sql("UPDATE `item` SET `type` = ".$gItemType[$o->type]->gammeltype." WHERE `id` = ".$o->id." LIMIT 1");
			*/
		}
		TablesUnlock();
		unset($gammelitems);
		
		$this->requeue(in_hours(time(),1));
	}	
}

class Job_Fire extends Job {
	protected function _run(){
		//fire spreading neighbours
		$n = array();
		$n[] = array("x"=>-1,	"y"=>0);
		$n[] = array("x"=>+1,	"y"=>0);
		$n[] = array("y"=>-1,	"x"=>0);
		$n[] = array("y"=>+1,	"x"=>0);
		
		//delete old fires, those fields count as totaly burned down
		$t = sqlgettable("SELECT * FROM `fire` WHERE `created`<".time()."-".kFireLivetime);
		foreach($t as $x)FirePutOutBurnedDown($x->x,$x->y);
		
		//reads out the fire fields that cause damage and do it, hahahaha
		$f = sqlgettable("SELECT * FROM `fire` WHERE `nextdamage`<".time());
		foreach($f as $x){
				sql("UPDATE `fire` SET `nextdamage`=".(time()+kFireDamageTimeout)." WHERE `x`=$x->x AND `y`=$x->y");
				echo "fire at ($x->x,$x->y) cause damage<br>\n";
				sql("UPDATE `building` SET `hp`=`hp`-".kFireDamage." WHERE `x`=$x->x AND `y`=$x->y");
				if(mysql_affected_rows() > 0){
						echo "building gets ".kFireDamage." damage<br>\n";
						$b = sqlgetobject("SELECT * FROM `building` WHERE `x`=$x->x AND `y`=$x->y");
						if($b->hp <= 0){
								//destroy burned down buildings
								cBuilding::removeBuilding($b,$b->user);
								echo "building destroyed<br>\n";
								FirePutOutObj($x);
						}
				}
		}
		//fire spread  and putout handling
		$f = sqlgettable("SELECT * FROM `fire` WHERE `nextspread`<".time());
		foreach($f as $x){
				//random outout check
				echo "fire at ($x->x,$x->y) put out?<br>\n";
				$r = rand(0,100);
				if($r < $x->putoutprob){
						echo "***** oki this fire was put out<br>\n";
						FirePutOutObj($x);
						continue;
				}
				
				//random spread check
				echo "fire at ($x->x,$x->y) tries to spread<br>\n";
				shuffle($n);
				foreach($n as $y){
						$r = rand(0,100);
						if($r < kFireSpreadProbability){
								$r = rand(0,100);
								//oki this fire spreads
								$px = (int)($y["x"] + $x->x);
								$py = (int)($y["y"] + $x->y);
								
								$spread = false;
								//is there a burnable building?
								$b = sqlgetone("SELECT `type` FROM `building` WHERE `x`=$px AND `y`=$py LIMIT 1");
								if(empty($b)){
										//echo "check terrain<br>\n";
										//no building, so check the terrain
										$t = cMap::StaticGetTerrainAtPos($px,$py);
										if($gTerrainType[$t]->flags & kTerrainTypeFlag_CanBurn && $r < $gTerrainType[$t]->fire_prob)$spread = true;
								} else {
										//echo "check building<br>\n";
										//check the building burnable flag
										if($gBuildingType[$b]->flags & kBuildingTypeFlag_CanBurn && $r < $gBuildingType[$b]->fire_prob)$spread = true;
								}
								if($spread){
										echo "***** fire at ($x->x,$x->y) spreads to ($px,$py)<br>\n";
										FireSetOn($px,$py);
										//set time for next spread test
										sql("UPDATE `fire` SET `nextspread`=".(time()+kFireSpreadTimeout)." WHERE `x`=$x->x AND y=$x->y");
								}
						}
				}
		}		

		$this->requeue(in_mins(time(),2));
	}	
}

class Job_GrowWood extends Job {
	protected function _run(){
		echo "grow wood ...<br>";
		$wood = sqlgetobject("SELECT `x`,`y` FROM `terrain` WHERE `type`=".kTerrain_Forest." ORDER BY RAND() LIMIT 1");
		if ($wood){
			echo "grow!!! ";
			$radius = 2; // TODO : unhardcode
			$done = false;
			$x = rand(-$radius,$radius)+$wood->x;
			$y = rand(-$radius,$radius)+$wood->y;
		
			$b = sqlgetobject("SELECT `id` FROM `building` WHERE `x`=(".($x).") AND `y`=(".($y).")");
			$t = sqlgetobject("SELECT `id`,`type` FROM `terrain` WHERE `x`=(".($x).") AND `y`=(".($y).")");
			if(empty($b) && (empty($t) || $t->type == kTerrain_Grass)) {
				echo " wood grow at ($x|$y)<br>";
				setTerrain($x,$y,kTerrain_YoungForest);
				$done = true;
			}
		}
		echo "done<br>";
		
		$this->requeue(in_hours(time(),1));
	}	
}

class Job_GrowCorn extends Job {
	protected function _run(){
		echo "grow cornfields ...<br>";
		$farm = sqlgetobject("SELECT * FROM `building` WHERE `type`=".GetGlobal("building_food")." ORDER BY RAND() LIMIT 1");
		if($farm && (rand()%10==0)){ // TODO : unhardcode
			echo "grow!!! ";
			$radius = 1;
			$done = false;
			for($x=-$radius;$x<=$radius;++$x)
				for($y=-$radius;$y<=$radius;++$y)if(!$done){
					$b = sqlgetobject("SELECT * FROM `building` WHERE `x`=(".($x+$farm->x).") AND `y`=(".($y+$farm->y).")");
					$t = sqlgetobject("SELECT * FROM `terrain` WHERE `x`=(".($x+$farm->x).") AND `y`=(".($y+$farm->y).")");
					if(empty($b) && (empty($t) || $t->type == kTerrain_Grass)){
						sql("DELETE FROM `terrain` WHERE `x`=(".($x+$farm->x).") AND `y`=(".($y+$farm->y).")");
						$o = null;
						$o->x = $x+$farm->x;
						$o->y = $y+$farm->y;
						$o->type = kTerrain_Field;
						sql("INSERT INTO `terrain` SET ".obj2sql($o));
						echo " field crow at (".$o->x."|".$o->y.")<br>";
						$done = true;
					}
				}
		}
		echo "done<br><br>";
		
		$this->requeue(in_hours(time(),1));
	}	
}

class Job_YoungForest extends Job {
	protected function _run(){
		global $gTerrainType;
		
		define("kStumpToYoung",1.0/(60*24));
		define("kYoungToForest",1.0/(60*24));
		// array(from,to,probability)
		$growarr = array(0=>array(kTerrain_TreeStumps,kTerrain_YoungForest,kStumpToYoung),
							array(kTerrain_YoungForest,kTerrain_Forest,kYoungToForest));
		foreach ($growarr as $arr) {
			$c = 0;
			$r = sql("SELECT * FROM `terrainsegment4` WHERE `type` = ".$arr[0]);
			while ($seg = mysql_fetch_object($r)) for ($y=0;$y<4;++$y) for ($x=0;$x<4;++$x) if (rand() <  $arr[2]*getrandmax()) {
				if (sqlgetone("SELECT 1 FROM `terrain` WHERE `x` = ".($seg->x*4 + $x)." AND `y` = ".($seg->y*4 + $y))) continue;
				sql("REPLACE INTO `terrain` SET `type` = ".$arr[1]." , `x` = ".($seg->x*4 + $x)." , `y` = ".($seg->y*4 + $y));
				++$c;
			}
			mysql_free_result($r);
			sql("UPDATE `terrain` SET `type` = ".$arr[1]." WHERE `type` = ".$arr[0]." AND RAND() < ".$arr[2]);
			echo (mysql_affected_rows()+$c)." units of ".$gTerrainType[$arr[0]]->name." turned to ".$gTerrainType[$arr[1]]->name."<br>";
		}
		
		$this->requeue(in_mins(time(),5));
	}	
}


?>
