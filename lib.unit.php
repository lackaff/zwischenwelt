<?php 
require_once("lib.army.php");

/*
regel : wenn ein $units array übergeben wird, ist es der erste parameter
*/

function cmpUnit ($a,$b) {
	if ($a->type == $b->type) return 0;
	global $gUnitType;
	return ($gUnitType[$a->type]->orderval < $gUnitType[$b->type]->orderval) ? -1 : 1;
}

class cUnit {
	function Simple ($type,$amount=1,$userid=0,$spellid=0) { 
		return array(0=>arr2obj(array("type"=>$type,"amount"=>$amount,"user"=>$userid,"spell"=>$spellid))); 
	}
	
	// $container_type is one of kUnitContainer_Army,kUnitContainer_Transport,kUnitContainer_Building
	function GetUnits ($container_id,$container_type=kUnitContainer_Army) {
		return sqlgettable("SELECT * FROM `unit` WHERE `$container_type` = ".intval($container_id));
	}
	
	// $container_type is one of kUnitContainer_Army,kUnitContainer_Transport,kUnitContainer_Building, ignore amount<0
	function SetUnits ($units,$container_id,$container_type=kUnitContainer_Army) {
		// $gAllArmyUnits[$enemy->id] = $enemy->units; // TODO : update cache ??
		sql("DELETE FROM `unit` WHERE `$container_type` = ".intval($container_id));
		$units = cUnit::GroupUnits($units);
		foreach ($units as $o) if ($o->amount > 0) {
			$o->army = 0;
			$o->building = 0;
			$o->transport = 0;
			$o->$container_type = intval($container_id);
			if (isset($o->id)) unset($o->id);
			sql("INSERT INTO `unit` SET ".obj2sql($o));
		}
	}
	
	// groups together equal unittype-spell-user combos, so you can simply push new units onto the array, even works with negative amounts
	function GroupUnits ($units) {
		$res = array();
		foreach ($units as $add) if ($add->amount != 0) {
			$found = false;
			foreach ($res as $key => $o) 
				if ($o->type == $add->type && $o->spell == $add->spell && $o->user == $add->user) 
				{ $res[$key]->amount += $add->amount; $found = true; break; }
			if (!$found) $res[] = $add; // combo not in array yet, insert new
		}
		return $res;
	}
	
	// $container_type is one of kUnitContainer_Army,kUnitContainer_Transport,kUnitContainer_Building
	// almost atomary, $add can be negative, $field in (army,transport,building) returns true for success
	// racecondition minimized by "`amount` = `amount` + $add WHERE `amount` + $add > 0"
	function AddUnits ($container_id,$typeid,$add,$container_type=kUnitContainer_Army,$uid=0,$spellid=0,$force=false) {
		if ($add == 0) return true;
		if (is_object($container_id)) $container_id = $container_id->id;
		if (is_object($typeid)) $typeid = $typeid->id;
		sql("UPDATE `unit` SET `amount` = GREATEST(0,`amount` + (".floatval($add).")) WHERE
			".($force?"1 ":"`amount` + (".floatval($add).") >= 0.0 ")." 
			AND `$container_type` = ".intval($container_id)." 
			AND `type` = ".intval($typeid)." 
			AND `user` = ".intval($uid)." 
			AND `spell` = ".intval($spellid)." LIMIT 1");
		if (mysql_affected_rows() > 0) return true;
		if ($add < 0) return false;
		sql("INSERT INTO `unit` SET `$container_type` = ".intval($container_id)." , `type` = ".intval($typeid)." , 
			`user` = ".intval($uid)." , `spell` = ".intval($spellid)." , `amount` = ".floatval($add));
		return true;
	}
	
	
	
	// ##### ##### ##### ##### ##### ##### ##### #####
	// ##### ##### #####  attributes  ##### ##### ####
	// ##### ##### ##### ##### ##### ##### ##### #####
	
	
	
	// $type_mult can be a,v,last,pillage,weight or false for 1
	function GetUnitsSum ($units,$type_mult=false) {
		$sum = 0;
		global $gUnitType;
		foreach ($units as $o) $sum += $o->amount * ($type_mult ? $gUnitType[$o->type]->$type_mult : 1.0);
		return $sum;
	}
	
	// (baseattack + techbonus) * chaosfaktor
	function GetUnitsAttack ($units,$uid=0) {
		return (cUnit::GetUnitsSum($units,"a") + cUnit::GetUnitsBonusSum($units,$uid,"a")) * cUnit::GetUnitsChaosFaktor($units);
	}
	
	// CHAOSFACTOR for decreasing attack in large armies
	// todo : improve system, this is one maxed out at 1500 units...
	function GetUnitsChaosFaktor ($units) {
		return max(0.7,1.0 - 0.1*max(0,cUnit::GetUnitsSum($units)-100)/500.0);
	}
	
	// TECHBONUS for units
	// $cat = a,v  TODO : maybe f,r
	function GetUnitsBonusSum ($units,$uid,$cat) {
		if (!$uid) return 0;
		$sum = 0.0;
		foreach ($units as $o) $sum += $o->amount * cUnit::GetUnitBonus($o->type,$uid,$cat);
		return $sum;
	}
	
	// TECHBONUS for specific type
	// $cat = a,v  TODO : maybe f,r
	function GetUnitBonus ($unittypeid,$uid,$cat) {
		if (!$uid) return 0;
		$sum = 0;
		global $gUnitType;
		$req = ParseReqForATechLevel(isset($gUnitType[$unittypeid]->{"req_tech_$cat"})?$gUnitType[$unittypeid]->{"req_tech_$cat"}:0);
		if(sizeof($req)>0)foreach ($req as $k=>$v) if (!$v->ismax) $sum += max(0.0,GetTechnologyLevel($k,$uid)-$v->level) * 3.0;
		return $sum;
	}
	
	// CAPTURE in SEAFIGHT
	function GetUnitsCaptureAttack ($units,$uid) {
		if (!$uid) return 0;
		$sum = 0.0;
		global $gUnitType;
		foreach ($units as $o) 
			$sum += $o->amount * $gUnitType[$o->type]->eff_capture * 
				($gUnitType[$o->type]->a + cUnit::GetUnitBonus($o->type,$uid,"a"));
		return $sum;
	}
	
	// FIGHTONDECK in SEAFIGHT
	function GetUnitsFightOnDeckAttack ($units,$uid) {
		if (!$uid) return 0;
		$sum = 0.0;
		global $gUnitType;
		foreach ($units as $o) 
			$sum += $o->amount * $gUnitType[$o->type]->eff_fightondeck * 
				($gUnitType[$o->type]->a + cUnit::GetUnitBonus($o->type,$uid,"a"));
		return $sum;
	}
	
	// Siege-attack
	function GetUnitsSiegeAttack ($units,$uid) {
		$sum = 0.0;
		global $gUnitType;
		foreach ($units as $o) 
			$sum += $o->amount * $gUnitType[$o->type]->eff_siege * 
				($gUnitType[$o->type]->a + cUnit::GetUnitBonus($o->type,$uid,"a"));
		return $sum;
	}
	
	// SAILORS for SHIPSPEED
	function GetUnitsSailors ($units) {
		$sum = 0;
		global $gUnitType;
		foreach ($units as $o) $sum += $o->amount * $gUnitType[$o->type]->eff_sail * $gUnitType[$o->type]->weight;
		return $sum;
	}
	
	//generates a minimum movable_flag mask that all units of army have in common
	function GetUnitsMovableMask ($units) {
		$mask = 0;
		$maskset = false;
		global $gUnitType;
		foreach ($units as $o) if ($o->amount >= 1) {
			$flag = intval($gUnitType[$o->type]->movable_flag);
			$mask = ($maskset)?($mask & $flag):($flag);
			$maskset = true;
		}
		return $mask;
	}
	
	
	// return units that can walk on $mask
	function FilterUnitsMovable ($units,$mask) {
		$res = array();
		global $gUnitType;
		foreach ($units as $o) if (intval($gUnitType[$o->type]->movable_flag) & $mask) $res[] = $o;
		return $res;
	}
	// return units that can walk on $mask
	function FilterUnitsType ($units,$type) {
		$res = array();
		foreach ($units as $o) if ($o->type == $type) $res[] = $o;
		return $res;
	}
	
	// TREASURE for units, uses random, for cArmy::SpawnArmy(), used by hellhole
	// $unittype->treasure = "itemtype:amount,itemtype:amount,..."  where amount -1 = random,  amount=2 is relative (see below)  amount=t4 is total (see below)
	// 22:-1,33:1,44:1,55:2,66:t3  means that the army getting the treasure (which usually has only one type of unit)
	// gets a total of 3 item66 , and the rest of the load available is completely filled with item22,item33,item44 and item55
	// if there are n item33, there will be n item44 , (2*n) item55 and a random amount of item22
	// probably much more than n, because -1 is simply replaced by a random number between 0 and 100
	function GetUnitsTreasure ($units) {
		global $gUnitType,$gItemType;
		$res = array();
		foreach ($units as $o) {
			$freelast = $o->amount * $gUnitType[$o->type]->last;
			$mygive = array();
			$totalmygive = 0;
			$treasure = explode(",",$gUnitType[$o->type]->treasure);
			foreach ($treasure as $t) if ($t != "") {
				list($itemtype,$amount) = explode(":",$t);
				$w = max(1,$gItemType[$itemtype]->weight);
				if ($amount{0} == 't') { // total (for unittype) amount
					$amount = intval(substr($amount,1));
					$res[$itemtype] = (isset($res[$itemtype])?$res[$itemtype]:0) + $amount;
					$freelast -= $w * $amount;
				} else { // relative(to other items) amount 
					if ($amount == -1) $amount = rand(0,100);
					$mygive[$itemtype] = $amount;
					$totalmygive += $w * $amount;
				}
			}
			if ($totalmygive == 0 || $freelast <= 0) continue;
			foreach ($mygive as $itemtype => $amount)
				$res[$itemtype] = (isset($res[$itemtype])?$res[$itemtype]:0) + $amount * $freelast / $totalmygive;
		}
		return $res;
	}
	
	// EXP for units
	function GetUnitsExp ($units) {
		return (cUnit::GetUnitsSum($units,"a") + cUnit::GetUnitsSum($units,"v")) / kArmy_AW_for_one_exp;
	}
	
	// FOOD usage per hour
	function GetUnitsEatSum ($units) {
		return round(cUnit::GetUnitsSum($units) / 24,1);
	}
	
	// RANGED damage
	function GetDistantDamage ($units,$dx,$dy) {
		global $gUnitType;
		$r = sqrt($dx*$dx+$dy*$dy);
		$dmg = 0;
		foreach ($units as $o) {
			if ($gUnitType[$o->type]->r >= $r && $gUnitType[$o->type]->f > 0)
				$dmg += $o->amount * $gUnitType[$o->type]->f;
		}
		return $dmg;
	}
	
	// RANGED siege damage
	function GetUnitsRangedSiegeDamage ($units) {
		global $gUnitType;
		$dmg = 0;
		foreach ($units as $o) 
			if ($o->amount > 0) 
				$dmg += max(0,$o->amount * $gUnitType[$o->type]->f * $gUnitType[$o->type]->eff_siege);
		return $dmg;
	}
	
	// RANGED attack range
	function GetUnitsMaxRange ($units) {
		global $gUnitType;
		$r = 0;
		foreach ($units as $o) 
			if ($o->amount > 0 && $gUnitType[$o->type]->f > 0) 
				$r = max($r,$gUnitType[$o->type]->r);
		return $r;
	}
	
	// RANGED Cooldown
	function GetDistantCooldown($units) {
		global $gUnitType;
		$maxcd = 0;
		foreach ($units as $o)
			if ($gUnitType[$o->type]->cooldown > $maxcd)
				$maxcd = $gUnitType[$o->type]->cooldown;
		return $maxcd;
	}
	
	// MAP-PICTURE
	function GetUnitsMaxType($units) {
		$maxtypeid = 0;
		$maxamount = 0;
		foreach ($units as $o) if ($o->amount > $maxamount) {
			$maxamount = $o->amount;
			$maxtypeid = $o->type;
		}
		return $maxtypeid;
	}
	
	// ARMYLIMIT
	function GetMaxArmyWeight ($armytype) {
		global $gArmyType;
		return $gArmyType[$armytype]->weightlimit;
		// TODO : techbonus/malus from ->addtechs ->subtechs
	}
	
	// BUILDINGLIMIT
	function GetMaxBuildingWeight ($buildingtypeid) {
		global $gBuildingType;
		$limit = $gBuildingType[$buildingtypeid]->weightlimit;
		if ($limit <= 0) return -1;
		return $limit;
		//if ($buildingtypeid == kBuilding_Garage) return 1000;
		//if ($buildingtypeid == kBuilding_MagicTower) return 2400;
		// -1 means no limit
	}
	
	// used in cArmy::getArmySpeed()
	function GetUnitsSpeed ($units) {
		global $gUnitType;
		if (count($units) == 0) return 0;
		$max = 0;
		foreach ($units as $o) if ($o->amount >= 1.0) $max = max($max,$gUnitType[$o->type]->speed);
		return $max;
	}
	
	// serializes units to string, for storage in fightlog
	function Units2Text ($units) {
		$res = array();
		foreach ($units as $o) $res[] = implode(",",array($o->type,$o->amount,$o->spell,$o->user));
		return implode("#",$res);
	}
	
	// unserializes units to string, for storage in fightlog
	function Text2Units ($text) {
		$units = array();
		if ($text == "") return $units;
		$arr = explode("#",$text);
		foreach ($arr as $block) {
			$o = false;
			list($o->type,$o->amount,$o->spell,$o->user) = explode(",",$block);
			$units[] = $o;
		}
		return $units;
	}
	
	
	// ##### ##### ##### ##### ##### ##### ##### #####
	// ##### #####   damage,training,etc   ##### #####
	// ##### ##### ##### ##### ##### ##### ##### #####
	
	
	
	// sort units for damaging by orderval of unittype
	function GetUnitsAfterDamage ($units,$damage,$uid,$mod_v=1.0) {
		global $gUnitType;
		usort($units,"cmpUnit");
		$res = array();
		foreach ($units as $o) {
			if ($damage > 0) {
				$v = $mod_v * ($gUnitType[$o->type]->v + cUnit::GetUnitBonus($o->type,$uid,"v"));
				if ($damage > $v * $o->amount) {
					// unit type is completely annihalated, go to next
					$damage -= $v * $o->amount;
				} else {
					// unit type is only damaged, n
					$o->amount -= $damage / $v;
					$damage = 0; // no more damage
					$res[] = $o;
				}
			} else $res[] = $o; // no more damage
		}
		return $res;
	}
	
	// escape damage
	function GetUnitsAfterEscape ($units,$uid) {
		$totaldef = cUnit::GetUnitsSum($units,"v") + cUnit::GetUnitsBonusSum($units,$uid,"v");
		return cUnit::GetUnitsAfterDamage($units,$totaldef/2,$uid); 
	}
	
	// if neg, negative amounts are returned, otherwise they are clamped to zero
	function GetUnitsDiff ($before,$after,$neg=false) {
		$diff = array();
		$debug = false;
		foreach ($before as $lost) if ($lost->amount > 0) {
			global $gUnitType;
			if ($debug) echo "diffing $lost->amount ".$gUnitType[$lost->type]->name." : <br>";
			foreach ($after as $o) 
				if ($lost->type == $o->type && 
					(!isset($lost->spell) || !isset($o->spell) || $lost->spell == $o->spell) && 
					(!isset($lost->user) || !isset($o->user) || $lost->user == $o->user)) {
					if ($debug) echo "left : ".$o->amount." of $o->user / $lost->user , $o->spell / $lost->spell<br>";
					$lost->amount -= $o->amount;
					if (!$neg && $lost->amount < 0) $lost->amount = 0; // clamp
				}
			if ($debug) echo "diff = ".$lost->amount."<br>";
			$diff[] = $lost;
		}
		return $diff;
	}
	
	// train units into elites based on the achieved experience
	function TrainElites ($units,$exp) {
		global $gUnitType;
		
		// count untrained units total
		$untrained_total = 0;
		foreach ($units as $o) if ($gUnitType[$o->type]->elite) $untrained_total += $o->amount;
		if ($untrained_total <= 0) return $units;
		
		// train
		$res = array();
		foreach ($units as $o) {
			if ($gUnitType[$o->type]->elite) {
				$train = $exp * 0.4 * $o->amount / $untrained_total;
				$o->amount -= $train;
				$res[] = $o; // reduce untrained
				$o->amount = $train;
				$o->type = $gUnitType[$o->type]->elite;
				$res[] = $o; // add trained, keep spell and user (it is possible to train spell-bound units, they stay spellbound)
			} else $res[] = $o;
		}
		return $res;
	}
	
	// returns array($transport,$captured) after capturing
	// $transport is the crew containing the pirates
	// $lost are the units that the enemy lost in this battle, some of them will be captured
	function CaptureShips ($transport,$lostunits) {
		// capture
		$captured = array();
		foreach ($lostunits as $lost) {
			$lost->amount *= 0.25;
			$captured[] = $lost->amount;
		}
		// dying pirates
		global $gUnitType;
		$newtransport = array();
		$dyingpirates = 10;
		foreach ($transport as $o) {
			if ($dyingpirates > 0 && $gUnitType[$o->type]->eff_capture > 0) {
				if ($o->amount <= $dyingpirates) {
					// completely annihalated
					$dyingpirates -= $o->amount;
				} else {
					// a few of them die, no more dying afterwards
					$o->amount -= $dyingpirates;
					$dyingpirates = 0;
					$newtransport[] = $o;
				}
			} else $newtransport[] = $o;
		}
		return array($newtransport,$captured);
	}
}
?>
