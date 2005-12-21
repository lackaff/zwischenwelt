<?php

require_once("lib.army.php");
require_once("lib.spells.php");

// attempts to use $gAllUsers cache

class cFight {

	// $army must be object, replaces _ARMYNAME_ and _ARMYOWNERNAME_ in $why
	function StopAllArmyFights ($army,$why) {
		$armyownername = cArmy::GetArmyOwnerName($army);
		$why = strtr($why,array("_ARMYNAME_"=>$army->name,"_ARMYOWNERNAME_"=>$armyownername));
		$fights = sqlgettable("SELECT * FROM `fight` WHERE `attacker` = ".$army->id." OR `defender` = ".$army->id);
		$pillages = sqlgettable("SELECT * FROM `pillage` WHERE `army`=".$army->id);
		$sieges = sqlgettable("SELECT * FROM `siege` WHERE `army`=".$army->id);
		foreach ($fights as $o) cFight::EndFight($o,$why);
		foreach ($pillages as $o) cFight::EndPillage($o,$why);
		foreach ($sieges as $o) cFight::EndSiege($o,$why);
	}
	
	// $building must be object, replaces _BUILDINGTYPE_ , _x_ , _y_ , _BUILDINGOWNERNAME_ in $why
	function StopAllBuildingFights ($building,$why) {
		global $gBuildingType;
		$buildingownername = $building->user ? sqlgetone("SELECT `name` FROM `user` WHERE `id` = ".$building->user) : "Server";
		$why = strtr($why,array("_BUILDINGTYPE_"=>$gBuildingType[$building->type]->name,
			"_x_"=>$building->x,"_y_"=>$building->y,"_BUILDINGOWNERNAME_"=>$buildingownername));
		$pillages = sqlgettable("SELECT * FROM `pillage` WHERE `building`=".$building->id);
		$sieges = sqlgettable("SELECT * FROM `siege` WHERE `building`=".$building->id);
		foreach ($pillages as $o) cFight::EndPillage($o,$why);
		foreach ($sieges as $o) cFight::EndSiege($o,$why);
	}
		
		
	// ##### ##### ##### ##### ##### ##### ##### #####
	// ##### #####    PILLAGE  ##### ##### ##### #####
	// ##### ##### ##### ##### ##### ##### ##### #####
	
	
	// $army,$building must be objects
	function PillagePossible ($army,$debug=false) {
		if (!cArmy::hasPillageAttack($army))
			{ if ($debug) echo "PillagePossible : army has no pillage attack<br>"; return false; }
		return true;
	}
	

	// $army,$building must be objects
	function StartPillage ($army,$x,$y,$restypes,$debug=false) {
		if (!cFight::PillagePossible($army,$debug)) 
			{ if ($debug) echo "StartPillage : not possible<br>"; return true; }
		if (!cArmy::inPillageRange($army->x-$x,$army->y-$y)) 
			{ if ($debug) echo "StartPillage : out of range<br>"; return false; }
		$building = sqlgetobject("SELECT * FROM `building` WHERE `x` = ".intval($x)." AND `y` = ".intval($y));
		if (!$building) 
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
		$busername = $building->user?(isset($gAllUsers)?$gAllUsers[$building->user]:sqlgetone("SELECT `name` FROM `user` WHERE `id` = ".intval($building->user))):"server";
		if ($army->user)		LogMe($army->user,		NEWLOG_TOPIC_FIGHT,NEWLOG_PILLAGE_ATTACKER_START,$building->x,$building->y,0,$army->name,$busername);
		if ($building->user)	LogMe($building->user,	NEWLOG_TOPIC_FIGHT,NEWLOG_PILLAGE_DEFENDER_START,$building->x,$building->y,0,$army->name,$armyownername);
		
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
	}
	
	
	function PillageStep ($pillage,$debug=false) {
		global $gResFields;
		$army = sqlgetobject("SELECT * FROM `army` WHERE `id` = ".$pillage->army);
		$building = sqlgetobject("SELECT * FROM `building` WHERE `id` = ".$pillage->building);
		if (!$building || !$army) {
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
		
		$pillagetotal = 0;
		foreach($gResFields as $res)$pillagetotal += $army->{"pillage_".$res};
	
		global $gAllUsers;
		$armyownername = cArmy::GetArmyOwnerName($army);
	
		echo "armee ".$army->name." von spieler ".$armyownername." hat ".
			intval($pillagetotal)."/".intval($army->pillage)." : ".
			intval($army->pillage_lumber).",".
			intval($army->pillage_stone).",".
			intval($army->pillage_food).",".
			intval($army->pillage_metal).",".
			intval($army->pillage_runes)." von ".
			intval($user->lumber).",".
			intval($user->stone).",".
			intval($user->food).",".
			intval($user->metal).",".
			intval($user->runes)." aus ".
			"Lager (".$building->x."|".$building->y.") geplündert.<br>";
			
		foreach($gResFields as $res){
			sql("UPDATE `army` SET `$res` = `$res` + ".$army->{"pillage_".$res}." WHERE `id` = ".$army->id);
			sql("UPDATE `user` SET `$res` = `$res` - ".$army->{"pillage_".$res}." WHERE `id` = ".$building->user);
		}
		
		$army->last += $pillagetotal;
		
		if ($debug) echo "auslastung : $army->last / $army->lastmax<br>";
		if ($army->last > $army->lastmax - 2) { // TODO : unhardcode
			cFight::EndPillage($pillage,"Die Plünderer sind vollgeladen.");
		} else if ($pillagetotal < $army->pillage/2) { // TODO : unhardcode
			cFight::EndPillage($pillage,"Das Lager ist leer.");
		}
	}
	
	function EndPillage ($pillage,$why,$aborted=false) {
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
	function SiegePossible ($army,$debug=false) {
		if (!cArmy::hasSiegeAttack($army))
			{ if ($debug) echo "SiegePossible : army has no siege attack<br>"; return false; }
		return true;
	}
	
	
	// $army,$building must be objects
	function StartSiege ($army,$x,$y,$debug=false) {
		if (!cFight::SiegePossible($army,$debug)) 
			{ if ($debug) echo "StartSiege : not possible<br>"; return true; }
		if (!cArmy::inSiegeRange($army->x-$x,$army->y-$y)) 
			{ if ($debug) echo "StartSiege : out of range<br>"; return false; }
		$building = sqlgetobject("SELECT * FROM `building` WHERE `x` = ".intval($x)." AND `y` = ".intval($y));
		if (!$building) 
			{ if ($debug) echo "StartSiege : no building<br>"; return false; }
			
		// starting siege
		if ($debug) echo "StartSiege : starting siege<br>";
		$t = false;
		$t->start = time();
		$t->army = $army->id;
		$t->building = $building->id;
		sql("INSERT INTO `siege` SET ".obj2sql($t));
		$t->id = mysql_insert_id();
			
		cFight::ActOfWar($army->user,$building->user,"Belagerung",$army->x,$army->y);
		
		// userlog
		global $gAllUsers,$gBuildingType;
		$armyownername = cArmy::GetArmyOwnerName($army);
		$busername = $building->user?(isset($gAllUsers)?$gAllUsers[$building->user]:sqlgetone("SELECT `name` FROM `user` WHERE `id` = ".intval($building->user))):"server";
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
	
	function SiegeStep ($siege,$debug=false) {
		$army = sqlgetobject("SELECT * FROM `army` WHERE `id` = ".$siege->army);
		$building = sqlgetobject("SELECT * FROM `building` WHERE `id` = ".$siege->building);
		if (!$army || !$building) {
			warning("BUG ! $army $building<br>");
			sql("DELETE FROM `siege` WHERE `id`=".$siege->id);
			return;
		}
		
		$units = cUnit::GetUnits($siege->army);
		$dmg = cUnit::GetUnitsSiegeAttack($units,$army->user) / 3600.0 * 60.0;
		$building->hp -= $dmg;
		
		global $gBuildingType;
		echo $army->name."(".$army->x.",".$army->y.") belagert ".$gBuildingType[$building->type]->name."(".$building->x."|".$building->y.") mit $dmg Schaden<br>";
			
		sql("UPDATE `building` SET `hp`='".$building->hp."' WHERE `id`=".$building->id);
		
		// gebäude tot, oder noch im bau unter 10 prozent..
		if ($building->hp < 1 || ($building->construction>0 && GetConstructionProgress($building)<0.1)) { // TODO : unhardcode
			// belagerung beendet
			cFight::EndSiege($siege,"Das Gebäude wurde vernichtet.");
			cBuilding::removeBuilding($building,$building->user);
			cArmy::AddArmyFrags($siege->army,1);
		}
	}
	
	function EndSiege ($siege,$why,$aborted=false) {
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
	function FightPossible ($attacker,$defender,$debug=false) {
		if (!$defender)		{ if ($debug) echo "FightPossible : no enemy<br>"; return false; }
		$ma = cUnit::GetUnitsMovableMask($attacker->units);
		$md = cUnit::GetUnitsMovableMask($defender->units);
		if (($ma & $md) == 0)	{ if ($debug) echo "FightPossible : movable-mismatch $ma & $md<br>"; return false; }
		if (!cArmy::hasMeleeAttack($attacker))
			{ if ($debug) echo "FightPossible : attacker has no melee attack<br>"; return false; }
		return true;
	}
	
	

	// $army,$enemy must be army-objects
	// $army->units and $enemy->units must be set
	function StartFight ($army,$enemy,$debug=false) {
		if (!cFight::FightPossible($army,$enemy,$debug)) 
			{ if ($debug) echo "StartFight : fight not possible<br>"; return true; }
		if (!cArmy::inMeleeRange($army->x-$enemy->x,$army->y-$enemy->y)) 
			{ if ($debug) echo "StartFight : out of range<br>"; return false; }
			
		if (sqlgetone("SELECT 1 FROM `fight` WHERE	(`attacker` = ".$army->id." AND `defender` = ".$enemy->id.") OR 
													(`attacker` = ".$enemy->id." AND `defender` = ".$army->id.") LIMIT 1")) 
			{ if ($debug) echo "StartFight : already fighting<br>"; return false; }
		
		// starting fight
		if ($debug) echo "StartFight : starting fight<br>";
		$fight = false;
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
		
		// start fightlog
		if (!isset($army->units)) $army->units = cUnit::GetUnits($army->id);
		if (!isset($enemy->units)) $enemy->units = cUnit::GetUnits($enemy->id);
		if (!isset($army->transport)) $army->transport = cUnit::GetUnits($army->id,kUnitContainer_Transport);
		if (!isset($enemy->transport)) $enemy->transport = cUnit::GetUnits($enemy->id,kUnitContainer_Transport);
		$fightlog = false;
		$fightlog->fight = $fight->id;
		$fightlog->startunits1 = cUnit::Units2Text($army->units);
		$fightlog->startunits2 = cUnit::Units2Text($enemy->units);
		$fightlog->starttransport1 = cUnit::Units2Text($army->transport);
		$fightlog->starttransport2 = cUnit::Units2Text($enemy->transport);
		sql("INSERT INTO `fightlog` SET ".obj2sql($fightlog));
		
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
	
	
	function FightStep ($fight) {
		//sets fighting army idle=0 so no one can move an do stuff like this
		sql("UPDATE `army` SET `idle`=0 WHERE `id`=".intval($fight->attacker)." OR `id`=".intval($fight->defender));
		
		$army1 = sqlgetobject("SELECT * FROM `army` WHERE `id` = ".$fight->attacker);
		$army2 = sqlgetobject("SELECT * FROM `army` WHERE `id` = ".$fight->defender);
		
		if(!$army1 || !$army2) {
			warning("BUG : $army1 , $army2<br>");
			sql("DELETE FROM `fight` WHERE `id`=".$fight->id);
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
		
		// todo : optimize this ? maybe as param, since where this function is called, we hava a complete fight-list
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
			cFight::EndFight($fight,"Beide Armeen wurden vernichtet.");
			cArmy::DeleteArmy($army1);
			cArmy::DeleteArmy($army2);
		} else if ($army1->size <= 0) {
			cFight::EndFight($fight,"Die ".$gArmyType[$army1->type]->name." $army1->name wurde vernichtet.");
			cArmy::DropExcessCargo($army1,$army2,0);
			cArmy::DeleteArmy($army1,true);
		} else if ($army2->size <= 0) {
			cFight::EndFight($fight,"Die ".$gArmyType[$army2->type]->name." $army2->name wurde vernichtet.");
			cArmy::DropExcessCargo($army2,$army1,0);
			cArmy::DeleteArmy($army2,true);
		}
	}
	
	function FightCalcStep (&$army1,&$army2,$m1,$m2,$spells,$showverlauf) {
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
	
	
	function EndFight ($fight,$why) {
		echo "EndFight : $why<br>";
		$army1 = sqlgetobject("SELECT * FROM `army` WHERE `id` = ".$fight->attacker);
		$army2 = sqlgetobject("SELECT * FROM `army` WHERE `id` = ".$fight->defender);
		if ($army1->user) LogMe($army1->user,NEWLOG_TOPIC_FIGHT,NEWLOG_FIGHT_STOP,$army1->x,$army1->y,0,$why,"");
		if ($army2->user) LogMe($army2->user,NEWLOG_TOPIC_FIGHT,NEWLOG_FIGHT_STOP,$army2->x,$army2->y,0,$why,"");
		$to_uid_list = array($army1->user,$army2->user);
		
		if ($army1->user && (intval($army1->flags) & kArmyFlag_GuildCommand)){
			$gc = getGuildCommander(sqlgetone("SELECT `guild` FROM `user` WHERE `id` = ".$army1->user));
			foreach($gc as $c) {
				if($c!=$army1->user) LogMe($c,NEWLOG_TOPIC_FIGHT,NEWLOG_FIGHT_STOP,$army1->x,$army1->y,0,$why,"");
				$to_uid_list[] = $c;
			}
		}
		if((intval($army2->flags) & kArmyFlag_GuildCommand)){
			$gc = getGuildCommander(sqlgetone("SELECT `guild` FROM `user` WHERE `id` = ".$army2->user));
			foreach($gc as $c) {
				if($c!=$army1->user)LogMe($c,NEWLOG_TOPIC_FIGHT,NEWLOG_FIGHT_STOP,$army2->x,$army2->y,0,$why,"");
				$to_uid_list[] = $c;
			}
		}
		// todo : send fight-reports
		cFight::SendFightReport($fight,$army1,$army2,$to_uid_list,$why);
		sql("DELETE FROM `fight` WHERE `id` = ".$fight->id);
	}
	
	// only called from EndFight
	function SendFightReport ($fight,$army1,$army2,$to_uid_list,$why) {
		global $gUnitType,$gAllUsers,$gRes,$gItemType,$gRes2ItemType;
		$to_uid_list = array_unique($to_uid_list);
		
		// get infos
		$fightlog = sqlgetobject("SELECT * FROM `fightlog` WHERE `fight` = ".$fight->id);
		if ($fightlog) {
			sql("DELETE FROM `fightlog` WHERE `id` = ".$fightlog->id);
			// todo : admin logging !
			$army1->startunits = cUnit::Text2Units($fightlog->startunits1);
			$army2->startunits = cUnit::Text2Units($fightlog->startunits2);
			$army1->starttransport = cUnit::Text2Units($fightlog->starttransport1);
			$army2->starttransport = cUnit::Text2Units($fightlog->starttransport2);
		} else {
			$army1->startunits = cUnit::GetUnits($army1->id);
			$army2->startunits = cUnit::GetUnits($army2->id);
			$army1->starttransport = cUnit::GetUnits($army1->id,kUnitContainer_Transport);
			$army2->starttransport = cUnit::GetUnits($army2->id,kUnitContainer_Transport);
		}
		$army1->units = cUnit::GetUnits($army1->id);
		$army2->units = cUnit::GetUnits($army2->id);
		$army1->transport = cUnit::GetUnits($army1->id,kUnitContainer_Transport);
		$army2->transport = cUnit::GetUnits($army2->id,kUnitContainer_Transport);
		$army1->size = cUnit::GetUnitsSum($army1->units);
		$army2->size = cUnit::GetUnitsSum($army2->units);
		$monster = ($army1->user == 0 || $army2->user == 0);
	
		// start report
		$topic = $monster?"Monster":"Kampfbericht";
		$report = "Die Schlacht bei (".$army1->x.",".$army1->y.") ist beendet.<br>";
		$report .= $why."<br>";
		if ($monster) $report .= "Monsterkampfberichte können unter Einstellungen abgeschaltet werden.<br>";
		$report .= "<br>";
		
		// report army state
		$armies = array($army1,$army2);
		foreach ($armies as $army) {
			$ownername = cArmy::GetArmyOwnerName($army);
			$report .= "<b>".$army->name." von ".$ownername."</b>".($army->size?"":" <font color='red'><b>(ausgelöscht)</b></font>")."<br>";
			$losses = cUnit::GetUnitsDiff($army->startunits,$army->units,true);
			$losses = array_merge($losses,cUnit::GetUnitsDiff($army->starttransport,$army->transport,true));
			$losses = cUnit::GroupUnits($losses);
		
			// anfangszustand
			rob_ob_start();
			cText::UnitsList($army->startunits,$army->user,"",false);
			if (cUnit::GetUnitsSum($army->starttransport) > 0) cText::UnitsList($army->starttransport,$army->user,"",false);
			$report .= rob_ob_end();
			
			// verluste/neuzugänge
			$report .= "<table border=1 cellspacing=0>";
			$arr_loss = array();
			$arr_gain = array();
			foreach ($losses as $o) if (floor(abs($o->amount)) > 0) {
				$txt = "<td align='right'>".floor(abs($o->amount))."</td><td><img src='".g($gUnitType[$o->type]->gfx)."'></td>";
				if ($o->amount > 0) 
						$arr_loss[] = $txt;
				else	$arr_gain[] = $txt;
			}
			if (count($arr_loss) > 0) $report .= "<tr><th>Verluste</th>".implode(" ",$arr_loss)."</tr>";
			if (count($arr_gain) > 0) $report .= "<tr><th>Neuzugänge</th>".implode(" ",$arr_gain)."</tr>";
			
			// verlorene res, items
			if ($army->size <= 0) {
				$arr_items = array();
				foreach ($gRes as $n=>$f) if ($army->{$f} > 0)
					$arr_items[] = "<td align='right'>".$army->{$f}."</td><td><img src='".g($gItemType[$gRes2ItemType[$f]]->gfx)."'></td>";
				$armyitems = sqlgettable("SELECT * FROM `item` WHERE `army` = ".$army->id);
				foreach ($armyitems as $o) 
					$arr_items[] = "<td align='right'>".$o->amount."</td><td><img src='".g($gItemType[$o->type]->gfx)."'></td>";
				if (count($arr_items) > 0) $report .= "<tr><th>Beute</th>".implode(" ",$arr_items)."</tr>";
			}
			$report .= "</table>";
			
			// endzustand
			if ($army->size > 0) {
				rob_ob_start();
				cText::UnitsList($army->units,$army->user,"",false);
				if (cUnit::GetUnitsSum($army->transport) > 0) cText::UnitsList($army->transport,$army->user,"",false);
				$report .= "Einheiten nach dem Kampf :".rob_ob_end();
			}
			
			$report .= "<br>";
		}
		
		// echo $report; // debug
		
		// send report
		foreach($to_uid_list as $uid) if ($uid) {
			$niederlage = false;
			if ($uid == $army1->user && $army1->size <= 0) { $niederlage = true; echo "army1 tot<br>";}
			if ($uid == $army2->user && $army2->size <= 0) { $niederlage = true; echo "army2 tot<br>";}
			if ($monster && $army1->user && $army1->size <= 0) { $niederlage = true; echo "army1 tot gc<br>";} // for gcs
			if ($monster && $army2->user && $army2->size <= 0) { $niederlage = true; echo "army2 tot gc<br>";} // for gcs
			if (!$niederlage && $monster) {
				$userflags = isset($gAllUsers) ? $gAllUsers[$uid]->flags : sqlgetone("SELECT `flags` FROM `user` WHERE `id` = ".intval($uid));
				if (intval($userflags) & kUserFlags_NoMonsterFightReport) continue;
			}
			sendMessage($uid,0,$niederlage?"Niederlage!":$topic,$report,kMsgTypeReport,FALSE);
		}
	}
	
	
	// $army must be object, $x,$y are the coordinates to flee to...
	function Flee ($army,$x,$y) {
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
		cFight::StopAllArmyFights($army,"Die Armee _ARMYNAME_ von _ARMYOWNERNAME_ ist geflohen.");
		cArmy::DropExcessCargo($army,$enemy,0);
		QuestTrigger_EscapeArmy($army,$x,$y);
	}
	
	
	// TODO : include ranged fight here, send warning messages for fof-state-change
	// notify parties of new fof state
	function ActOfWar ($attacker_uid,$defender_uid,$what,$x,$y) {
		if (!$attacker_uid || !$defender_uid) return;
		$topic = "Kriegerischer Akt";
		if (GetFOF($attacker_uid,$defender_uid) != kFOF_Enemy) {
			$defender_name = sqlgetone("SELECT `name` FROM `user` WHERE `id` = ".intval($defender_uid));
			$report = "Durch einen kriegerischen Akt von dir,<br>";
			$report .= "$what bei ($x,$y), wurde $defender_name zu deinem Feind.<br>";
			SetFOF($attacker_uid,$defender_uid,kFOF_Enemy);
			sendMessage($attacker_uid,0,"Feind:$defender_name",$report,kMsgTypeReport,FALSE);
		}
		if (GetFOF($defender_uid,$attacker_uid) != kFOF_Enemy) {
			$attacker_name = sqlgetone("SELECT `name` FROM `user` WHERE `id` = ".intval($attacker_uid));
			$report = "$attacker_name wurde durch einen kriegerischen Akt,<br>";
			$report .= "$what bei ($x,$y), zu deinem Feind.<br>";
			SetFOF($defender_uid,$attacker_uid,kFOF_Enemy);
			sendMessage($defender_uid,0,"Feind:$attacker_name",$report,kMsgTypeReport,FALSE);
		}
	}
}
?>