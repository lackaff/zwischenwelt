<?php

class Job_RuinCorruption extends Job {
	protected function _run(){
		// collapse probability
		$r = rand(0,100);
		// number of buildings to collapse
		$n = 100;
		$t = sqlgettable("SELECT b.* FROM `buildingtype` t LEFT JOIN `building` b ON b.type=t.id WHERE `collapse_prob`>0 AND `collapse_prob` < ".intval($r)." ORDER BY RAND() LIMIT ".intval($n));
		foreach($t as $b){
			print "collapse building $b->id<br>\n";
			cBuilding::removeBuilding($b,$b->user,true,false);
		}

		$this->requeue(in_mins(time(),1));
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

		$this->requeue(in_mins(time(),1));
	}	
}


?>