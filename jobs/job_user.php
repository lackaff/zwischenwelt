<?php

class Job_ResCalc extends Job {
	protected function _run(){
		global $gRes, $gGrundproduktion;
		
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
			$users = sqlgettable("SELECT * FROM `user` ORDER BY `id`","id");
			foreach($users as $u) {
				$b = sqlgettable("SELECT count( `id` ) AS `count` , `type` AS `type` , sum( `level` ) AS `level` , sum(`supportslots`) as `supportslots` FROM `building` WHERE `construction`=0 AND `user`=".$u->id." GROUP BY `type`","type");
					
				foreach ($zeroset_btypes as $key) if (!isset($b[$key])) {
					$b[$key] = new EmptyObject();
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
					
					// WARNING : all changes to user ressources within this lock that are not operating on $gPayCache_Users or $users, are overwritten here
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
					$users[$u->id]->prodfaktoren = $prodfaktoren; // for later use
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
				$food = $users[$u->id]->food;
				$food -= $foodneed;
				if($food <= 0){
					//people die
					$users[$u->id]->pop = max(0,$users[$u->id]->pop-$foodneed);
					echo "$foodneed units food in $dtime needed by ".$u->name."\n<br>";
				} else {
					echo "$foodneed people grow in $dtime by ".$u->name."\n";
				}
				$users[$u->id]->food = max(0,$food);
				sql("UPDATE `user` SET `food`=".($users[$u->id]->food).",`pop`=".($users[$u->id]->pop)." WHERE `id`=".$u->id);
				
				unset($b);
			}			
			TablesUnlock();
			
			SetGlobal("last_res_calc",T);
		}
		
		$this->requeue(in_mins(time(),1));
	}
}

class Job_Mana extends Job {
	protected function _run(){
		global $gBuildingType;
		
		if(!ExistGlobal("last_mana_calc")){
			SetGlobal("last_mana_calc",T);
		}
		
		$last = GetGlobal("last_mana_calc");
		
		if(T - $last > 0){
			$dtime = (T - $last);
			echo "DT: $dtime\n";
			
			TablesLock();
			
			$basemana = $gBuildingType[GetGlobal('building_runes')]->basemana;
			// TODO : unhardcode
			sql("UPDATE `building` SET `mana`=LEAST((`level`+1)*$basemana,`mana`+($basemana*(`level`+1)/(10+`level`/20)*".($dtime/3600).")) WHERE `type`=".GetGlobal('building_runes'));

			TablesUnlock();
			
			SetGlobal("last_mana_calc",T);
		}
		
		$this->requeue(in_mins(time(),1));
	}
}

class Job_Tech extends Job {
	protected function _run(){
		global $gTechnologyType, $gBuildingType;

		$gTechnologyLevelsOfAllUsers = sqlgetgrouptable("SELECT `user`,`type`,`level` FROM `technology`","user","type","level");
		
		//$gTechnologyTypes = sqlgettable("SELECT * FROM `technologytype`","id");
		sql("LOCK TABLES `user` WRITE,`technology` WRITE,`building` READ,`phperror` WRITE,
								`sqlerror` WRITE, `newlog` WRITE");
		$technologies = sqlgettable("SELECT * FROM `technology` WHERE `upgrades` > 0 ORDER BY `level`");
		$time = time();
		foreach ($technologies as $o) {
			if ($o->upgradetime > 0 && ($o->upgradetime < $time || kZWTestMode) ){
				// upgrade finished
		
				//only complete the tech if requirenments meet
				//echo "<br>\nHasReq(".($gTechnologyType[$o->type]->req_geb).",".($gTechnologyType[$o->type]->req_tech).",".($o->user).",".($o->level+1).")<br>\n";
				if(HasReq($gTechnologyType[$o->type]->req_geb,$gTechnologyType[$o->type]->req_tech,$o->user,$o->level+1)){
					sql("UPDATE `technology` SET
						`level` = `level` + 1 ,
						`upgrades` = `upgrades` - 1 ,
						`upgradetime` = 0 WHERE `id` = ".$o->id." LIMIT 1");
						
					$gTechnologyLevelsOfAllUsers[$o->user][$o->type] = $o->level + 1;
					
					$text = $gTechnologyType[$o->type]->name." von user ".$o->user." ist nun Level ".($o->level+1);
					echo $text."<br>\n";
					
					// TODO : neue log meldung machen !
					LogMe($o->user,NEWLOG_TOPIC_BUILD,NEWLOG_UPGRADE_FINISHED,0,0,$o->level+1,$gTechnologyType[$o->type]->name,"",false);
				} else {
					sql("UPDATE `technology` SET
						`upgrades` = 0 ,
						`upgradetime` = 0 WHERE `id` = ".$o->id." LIMIT 1");
						
					$text = $gTechnologyType[$o->type]->name." von user ".$o->user." wurde abgebrochen, da die anforderungen nicht erfüllt wurden";
					echo $text."<br>\n";
				}
			} else if ($o->upgradetime == 0) {
				// test if upgrade can be started
				
				// only one upgrade per building at once
				$other = sqlgetone("SELECT 1 FROM `technology` WHERE 
					`upgradetime` > 0 AND `upgradebuilding` = ".$o->upgradebuilding." AND `id` <> ".$o->id);
				
				if (!$other) {
					$techtype = $gTechnologyType[$o->type];
					$level = GetTechnologyLevel($o->type,$o->user);
					// only upgrade if the technological requirements are met
					if (!HasReq($techtype->req_geb,$techtype->req_tech,$o->user,$level+1)) {
						sql("UPDATE `technology` SET `upgrades` = 0 WHERE `id` = ".$o->id." LIMIT 1");
						
						$text = $techtype->name." von user ".$o->user." wurde nicht gestartet, da die anforderungen nicht erfüllt wurden";
						echo $text."<br>\n";
						
						continue;
					}
				
					$upmod = cTechnology::GetUpgradeMod($o->type,$o->level);
					if (UserPay($o->user,
						$upmod * $techtype->basecost_lumber,
						$upmod * $techtype->basecost_stone,
						$upmod * $techtype->basecost_food,
						$upmod * $techtype->basecost_metal,
						$upmod * $techtype->basecost_runes)) {
						echo $techtype->name." von user ".$o->user." upgrade gestartet<br>\n";
						$finishtime = $time + cTechnology::GetUpgradeDuration($o->type,$o->level);
						sql("UPDATE `technology` SET `upgradetime` = ".$finishtime." WHERE `id` = ".$o->id." LIMIT 1");
						
						$text = $gTechnologyType[$o->type]->name." von user ".$o->user." wurde gestartet";
						echo $text."<br>\n";
					}
				}
			}
		}
		sql("UNLOCK TABLES");
		
		$this->requeue(in_mins(time(),1));
	}
}

class Job_UserProdPop extends Job {
	protected function _run(){
		global $gResFields;
		
		if(!ExistGlobal("last_prod_calc")){
			SetGlobal("last_prod_calc",T);
		}
		
		$last = GetGlobal("last_prod_calc");
		
		if(T - $last > 0){
			$dtime = (T - $last);
			echo "calc res,pop mana... ".($dtime/3600)."<br>";
			echo "DT: $dtime\n";
			
			//sql("UPDATE `user` SET `pop`=`maxpop` WHERE `pop`>`maxpop`");
			
			TablesLock();
			sql("UPDATE `user` SET	`pop`=LEAST(`maxpop`,`pop`+".($dtime/300).") ,
									`lumber`=(`lumber`+`prod_lumber`*".($dtime/3600).") , 
									`stone`=(`stone`+`prod_stone`*".($dtime/3600).") ,
									`food`=(`food`+`prod_food`*".($dtime/3600).") ,
									`metal`=(`metal`+`prod_metal`*".($dtime/3600).")");
			
			//gnome:
			sql("UPDATE `user` SET `runes`=`runes`+`prod_runes`*".($dtime/3600)." WHERE `race`=".kRace_Gnome); // TODO : unhardcode					
			TablesUnlock();
			
			echo "flush user res to guild... <br>\n";
			TablesLock();
			foreach($gResFields as $r){
				$t = sqlgettable("SELECT `id`,`$r`,`max_$r`,`guild` FROM `user` WHERE `guild`>0 AND `$r`>`max_$r`");
				foreach($t as $x) {
					$radd = ($x->{$r}) - ($x->{"max_$r"});
					sql("UPDATE `guild` SET `$r`=`$r`+($radd) WHERE `id`=".$x->guild);
					sql("UPDATE `user` SET `guildpoints`=`guildpoints`+($radd) WHERE `id`=".$x->id);
					echo "add user ".$x->id." res to guild ".$x->guild." [$r] $radd<br>\n";
				}
				unset($t);
				sql("UPDATE `user` SET `$r`=`max_$r` WHERE `$r`>`max_$r`");
			}
			TablesUnlock();

			SetGlobal("last_prod_calc",T);
		}

		$this->requeue(in_secs(time(),10));
	}	
}

class Job_SupportSlots extends Job {
	protected function _run(){
		$q = sql("SELECT `id` FROM `building` WHERE (
			`type`=".GetGlobal("building_lumber")." OR 
			`type`=".GetGlobal("building_stone")." OR 
			`type`=".GetGlobal("building_food")." OR 
			`type`=".GetGlobal("building_runes")." OR 
			`type`=".GetGlobal("building_metal").")");
		
		if($q){
			while($row = mysql_fetch_row($q)){
				getSlotAddonFromSupportFields($row[0]);
			}
			mysql_free_result($q);
		}

		$this->requeue(in_hours(time(),1));
	}	
}

class Job_GuildRes extends Job {
	protected function _run(){
		global $gResFields, $gRes;
		
		// todo : optimize by select max with group by guild ??
		//calc guild max resources
		echo "calc guild max res...<br>\n";
		$gGuilds = sqlgettable("SELECT * FROM `guild`");
		foreach($gGuilds as $x){
			$s = "";
			foreach($gResFields as $r)$s .= ", sum(`max_$r`) as `max_$r`";
			$s{0} = ' ';
			$s = "SELECT".$s;
			$s .= " FROM `user` WHERE `guild`=".$x->id;
			$o = sqlgetobject($s);
			sql("UPDATE `guild` SET ".obj2sql($o)." WHERE `id`=".$x->id);
			echo "Guild ".$x->id." res max set to ".implode("|",obj2array($o))."<br>\n";
		}
		echo "enforcing guild max res ....<br>\n";
		$set="";
		// todo : single query
		foreach($gRes as $f=>$r){
			sql("UPDATE `guild` SET `$r`=`max_$r` WHERE `$r`>`max_$r");
		}
		
		$this->requeue(in_mins(time(),1));
	}	
}

class Job_Runes extends Job {
	protected function _run(){
		if(!ExistGlobal("last_runes_calc")){
			SetGlobal("last_runes_calc",T);
		}
		
		$last = GetGlobal("last_runes_calc");
		
		if(T - $last > 0){
			$dtime = (T - $last);
			echo "DT: $dtime\n";
			
			$gTechnologyLevelsOfAllUsers = sqlgetgrouptable("SELECT `user`,`type`,`level` FROM `technology`","user","type","level");
			$users = sqlgettable("SELECT * FROM `user` ORDER BY `id`","id");
			foreach($users as $u){
				switch($u->race){
					default:
						$rpfs = (0.8 + 
							(isset($gTechnologyLevelsOfAllUsers[$u->id][kTech_MagieMeisterschaft])?$gTechnologyLevelsOfAllUsers[$u->id][kTech_MagieMeisterschaft]:0)*0.6)/2;
					   if($u->worker_runes>0){
							if(($u->lumber+$u->prod_lumber*($dtime/3600)) >= ($rpfs*($u->worker_runes*$u->pop/100*GetGlobal('lc_prod_runes'))*($dtime/3600))){
								$l=$rpfs*1;
							}else{
								$l=$rpfs*($u->lumber+$u->prod_lumber*($dtime/3600))/($u->worker_runes*$u->pop/100*GetGlobal('lc_prod_runes')*($dtime/3600));
							}
							if(($u->metal+$u->prod_metal*($dtime/3600)) >= ($rpfs*($u->worker_runes*$u->pop/100*GetGlobal('mc_prod_runes'))*($dtime/3600))){
								$m=$rpfs*1;
							}else{
								$m=$rpfs*($u->metal+$u->prod_metal*($dtime/3600))/($u->worker_runes*$u->pop/100*GetGlobal('mc_prod_runes')*($dtime/3600));
							}
							if(($u->stone+$u->prod_stone*($dtime/3600)) >= ($rpfs*($u->worker_runes*$u->pop/100*GetGlobal('sc_prod_runes')*($dtime/3600)))){
								$s=$rpfs*1;
							}else{
								$s=$rpfs*($u->stone+$u->prod_stone*($dtime/3600))/($u->worker_runes*$u->pop/100*GetGlobal('sc_prod_runes')*($dtime/3600));
							}
							if(($u->food+$u->prod_food*($dtime/3600)) >= ($rpfs*($u->worker_runes*$u->pop/100*GetGlobal('fc_prod_runes')*($dtime/3600)))){
								$f=$rpfs*1;
							}else{
								$f=$rpfs*($u->food+$u->prod_food*($dtime/3600))/($u->worker_runes*$u->pop/100*GetGlobal('fc_prod_runes')*($dtime/3600));
							}
							$factor=round(min($l,$m,$s,$f),3);
							sql("UPDATE `user` SET `runes`=`runes`+`prod_runes`*".($dtime/3600)."*".$factor." WHERE `id`=".$u->id);
							UserPay($u->id,	$u->worker_runes*$u->pop/100*GetGlobal('lc_prod_runes')*$factor*($dtime/3600),
											$u->worker_runes*$u->pop/100*GetGlobal('sc_prod_runes')*$factor*($dtime/3600),
											$u->worker_runes*$u->pop/100*GetGlobal('fc_prod_runes')*$factor*($dtime/3600),
											$u->worker_runes*$u->pop/100*GetGlobal('mc_prod_runes')*$factor*($dtime/3600));
						}
					break;
					case kRace_Gnome:
					break;
				}
			}
				
			SetGlobal("last_runes_calc",T);
		}
		
		$this->requeue(in_mins(time(),1));
	}	
}

?>
