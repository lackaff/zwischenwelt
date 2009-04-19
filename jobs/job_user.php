<?php

class Job_ResCalc extends Job {
	protected function _run(){
		if(!ExistGlobal("last_res_calc")){
			SetGlobal("last_res_calc",T);
		}
		
		$lastrescalc = GetGlobal("last_res_calc");
		
		if(T - $lastrescalc > 0){
			$dtime = (T - $lastrescalc);
			echo "DT: $dtime\n";
			
			// reset resources if <0
			$res = Array("lumber","food","stone","metal","runes");
			foreach($res as $r) sql("UPDATE `user` SET `$r`=0 WHERE `$r`<0");

			$zeroset_btypes = array(GetGlobal("building_hq"),GetGlobal("building_house"),GetGlobal("building_store"),GetGlobal("building_runes"));
		
			TablesLock();
			$gAllUsers = sqlgettable("SELECT * FROM `user` ORDER BY `id`","id");
			foreach($gAllUsers as $u) {
				$b = sqlgettable("SELECT count( `id` ) AS `count` , `type` AS `type` , sum( `level` ) AS `level` , sum(`supportslots`) as `supportslots` FROM `building` WHERE `construction`=0 AND `user`=".$u->id." GROUP BY `type`","type");
					
				foreach ($zeroset_btypes as $key) if (!isset($b[$key])) {
					$b[$key]->count = 0;
					$b[$key]->level = 0;
					$b[$key]->supportslots = 0;
				}
				
				// calc max pop
				$maxpop =	GetGlobal("pop_slots_hq")	* ($b[GetGlobal("building_hq")]->count + $b[GetGlobal("building_hq")]->level) +
							GetGlobal("pop_slots_house")	* ($b[GetGlobal("building_house")]->count + $b[GetGlobal("building_house")]->level);
				//echo "user=".$u->id." ".$u->name.": maxpop=$maxpop/".$u->maxpop."<br>";
				$usersets = "`maxpop`=$maxpop";
				
				if($b[GetGlobal("building_hq")]->count == 0) {
					// no haupthaus -> new player start ressources
					$sr = GetGlobal("store");
					switch($u->race){
						case kRace_Gnome:
							$usersets .= ",	`max_lumber`=$sr,`lumber`=$sr,
											`max_stone`=$sr,`stone`=$sr,
											`max_food`=$sr,`food`=$sr,
											`max_metal`=$sr,`metal`=$sr,
											`max_runes`=$sr, `runes`=$sr ";
						break;
						default:
							$usersets .= ",	`max_lumber`=$sr,`lumber`=$sr,
											`max_stone`=$sr,`stone`=$sr,
											`max_food`=$sr,`food`=$sr,
											`max_metal`=$sr,`metal`=$sr,
											`max_runes`=0, `runes`=0 ";
						break;
					}
				} else {
					// haupthaus -> normale berechnung // TODO : unhardcode
					$store =	GetGlobal("store") * ($b[GetGlobal("building_hq")]->count + $b[GetGlobal("building_hq")]->level) + 
								GetGlobal("store") * ($b[GetGlobal("building_store")]->count + $b[GetGlobal("building_store")]->level);
					
					$rstore = 	2500			  * ($b[GetGlobal("building_runes")]->count + $b[GetGlobal("building_runes")]->level);
					
					// WARNING : all changes to user ressources within this lock that are not operating on $gPayCache_Users or $gAllUsers, are overwritten here
					switch($u->race){
						case kRace_Gnome:
							$usersets .= ",	`max_lumber`=$store,
											`max_stone`=$store,
											`max_food`=$store,
											`max_metal`=$store,
											`max_runes`=$store";
						break;
						default:
							$usersets .= ",	`max_lumber`=$store,
											`max_stone`=$store,
											`max_food`=$store,
											`max_metal`=$store, 
											`max_runes`=$rstore";
						break;
					}
					$prodfaktoren = GetProductionFaktoren($u->id);
					$gAllUsers[$u->id]->prodfaktoren = $prodfaktoren; // for later use
					$slots = GetProductionSlots($u->id,$b);
					foreach($gRes as $resname=>$resfield) {
						$btype = GetGlobal("building_".$resfield);
						$prod_factor = $prodfaktoren[$resfield];
						$w = $u->pop * $u->{"worker_".$resfield} / 100; // anzahl zugewiesene arbeiter
						$s = $slots[$resfield]; // anzahl slots
						$p = (min($w,$s) + max(($w - $s),0) * GetGlobal("prod_faktor_slotless")) * (GetGlobal("prod_faktor")) * $prod_factor; // produktion
						
						// Grundproduktion
						if (isset($gGrundproduktion[$u->race]) && isset($gGrundproduktion[$u->race][$resfield]))
							$p += $gGrundproduktion[$u->race][$resfield];
							
						$usersets .= ",`prod_$resfield`=$p";
					}
				}
				sql("UPDATE `user` SET ".$usersets." WHERE `id`=".$u->id);
				
				//check if there is enough food for the population
				$foodneed = calcFoodNeed($u->pop,$dtime);
				$food = $gAllUsers[$u->id]->food;
				$food -= $foodneed;
				if($food <= 0){
					//people die
					$gAllUsers[$u->id]->pop = max(0,$gAllUsers[$u->id]->pop-$foodneed);
					echo "$foodneed units food in $dtime needed by ".$u->name."\n<br>";
				}
				$gAllUsers[$u->id]->food = max(0,$food);
				sql("UPDATE `user` SET `food`=".($gAllUsers[$u->id]->food).",`pop`=".($gAllUsers[$u->id]->pop)." WHERE `id`=".$u->id);
				
				unset($b);
			}			
			TablesUnlock();
			
			SetGlobal("last_res_calc",T);
		}
		
		$this->requeue(in_mins(time(),1));
	}
}

?>