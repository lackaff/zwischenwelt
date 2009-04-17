<?php

require_once("lib.main.php");
require_once("lib.map.php");
require_once("lib.army.php");

define("itemspawn_debugout",false);


// todo : cItem::ArmyPayItem -> link in army..


class cItem {
	
	function GetUserTotalItemAmount ($itemtypeid,$userid) {
		global $gItemType,$gRes,$gRes2ItemType;
		$sum = 0;
		$myres = $gRes;
		foreach($myres as $n=>$f) if ($gRes2ItemType[$f] == $itemtypeid) {
			$sum += intval(sqlgetone("SELECT `$f` FROM `user` WHERE `id` = ".intval($userid)));
			$sum += intval(sqlgetone("SELECT SUM(`$f`) FROM `army` WHERE `user` = ".intval($userid)));
		}
		$sum += intval(sqlgetone("SELECT SUM(`item`.`amount`) FROM `item`,`army` WHERE `item`.`type` = ".intval($itemtypeid)." AND `army`.`id` = `item`.`army` AND `army`.`user` = ".intval($userid)));
		$sum += intval(sqlgetone("SELECT SUM(`item`.`amount`) FROM `item`,`building` WHERE `item`.`type` = ".intval($itemtypeid)." AND `building`.`id` = `item`.`building` AND `building`.`user` = ".intval($userid)));
		$sum += intval(sqlgetone("SELECT SUM(`amount`) FROM `item` WHERE `type` = ".intval($itemtypeid)." AND `user` = ".intval($userid)));
		return $sum;
	}
	
	function GetMaxTrade ($army,$tradetext) {
		if (!is_array($tradetext)) $tradetext = explode2(",",":",$tradetext);
		$max = -1;
		foreach ($tradetext as $component) if ($component[1] > 0) {
			$component_max = floor(cItem::CountArmyItem($army,$component[0]) / $component[1]);
			if ($max == -1 || $max > $component_max)
				$max = $component_max;
		}
		return ($max>0)?$max:0;
	}
	
	function CountArmyItem	($armyid,$itemtypeid) { // works with item-res translation
		if (is_object($armyid)) $armyid = $armyid->id;
		if (is_object($itemtypeid)) $itemtypeid = $itemtypeid->id;
		global $gRes2ItemType;
		$mygRes2ItemType = $gRes2ItemType;
		foreach ($mygRes2ItemType as $res => $it) if ($itemtypeid == $it) 
			return sqlgetone("SELECT `$res` FROM `army` WHERE `id` = ".intval($armyid));
		return intval(sqlgetone("SELECT (`amount`) FROM `item` WHERE 
			`army` = ".intval($armyid)." AND `type` = ".intval($itemtypeid)." LIMIT 1"));
	}
	
	function ArmyPayItem	($armyid,$itemtypeid,$payamount) { // works with item-res translation
		if (is_object($armyid)) $armyid = $armyid->id;
		if (is_object($itemtypeid)) $itemtypeid = $itemtypeid->id;
		global $gRes2ItemType;
		$mygRes2ItemType = $gRes2ItemType;
		foreach ($mygRes2ItemType as $res => $it) if ($itemtypeid == $it) {
			sql("UPDATE `army` SET `$res` = `$res` - ".intval($payamount)." WHERE `$res` >= ".intval($payamount)." AND `id` = ".intval($armyid));
			if (mysql_affected_rows() > 0) return true;
		}
		sql("UPDATE `item` SET `amount` = `amount` - ".intval($payamount)." WHERE `amount` >= ".intval($payamount)." AND
			`army` = ".intval($armyid)." AND `type` = ".intval($itemtypeid)." LIMIT 1");
		$success = mysql_affected_rows() > 0;
		sql("DELETE FROM `item` WHERE `amount` = 0 AND 
			`army` = ".intval($armyid)." AND `type` = ".intval($itemtypeid)." LIMIT 1");
		return $success;
	}
	
	// WARNING ! DON'T CALL THIS FUNCTION WITHIN A "foreach ($gRes ..)" LOOP ! use "$myres = $gRes;" or sth like that !
	// returns if the item has been picked up completely (true) or if the army has been full, and could only get a part (false)
	function SpawnArmyItem	($army,$typeid,$amount=1.0,$quest=0,$param=0) { // creates an item and gives it to the army as reward
		if ($amount < 1) return;
		if (!is_object($army)) $army = sqlgetobject("SELECT * FROM `army` WHERE `id`=".intval($army));
		// echo "SpawnArmyItem($army->id,$typeid,$amount)<br>";
		$item = cItem::SpawnItem($army->x,$army->y,$typeid,$amount,$quest,$param);
		$armynotfull = cItem::pickupItem($item,$army,$amount);
		return $armynotfull;
	}
	
	function SpawnItem		($x,$y,$typeid,$amount=1.0,$quest=0,$param=0) {
		if ($amount < 1) return;
		if (itemspawn_debugout) echo "SpawnItem($x,$y,$typeid,$amount=1.0,$quest=0,$param=0)<br>";
		global $gItemType;
		$item = false;
		$item->army = 0;
		$item->building = 0;
		$item->x = intval($x);
		$item->y = intval($y);
		$item->type = intval($typeid);
		$item->amount = floatval($amount);
		$item->param = intval($param);
		$item->quest = $quest;
		
		// gammelstart
		if (!(intval($gItemType[$item->type]->flags) & kItemFlag_GammelOnPickup) && 
			$gItemType[$item->type]->gammeltime > 0) {
			$item->param = time()+$gItemType[$item->type]->gammeltime;
		}
		
		// versuchen mit gleichem typ zu mergen
		$cond = "`type` = ".$item->type." AND `param` = ".$item->param." AND `quest` = ".$item->quest." AND
				 `army` = 0 AND `building` = 0 AND `x` = ".$item->x." AND `y` = ".$item->y;
		sql("UPDATE `item` SET `amount` = `amount` + ".$item->amount." WHERE $cond LIMIT 1");
		$merging = mysql_affected_rows() >= 1;
		if (!$merging) {
			sql("INSERT INTO `item` SET ".obj2sql($item));
			$item->id = mysql_insert_id();
		} else $item = sqlgetobject("SELECT * FROM `item` WHERE $cond LIMIT 1");
		return $item;
	}
	
	
	
	//pick up item, item/army are id/object
	// overridepos is for workers loading ressources into an adjacted army
	function pickupItem($item,$army,$limitamount=-1,$overridepos=false) {
		// todo : army item limit,
		// todo : army weight limit
		// todo : item unify in army, item unify in terrain , item unify in building
		global $gItemType;
		if (!is_object($item)) $item = sqlgetobject("SELECT * FROM `item` WHERE `id`=".intval($item));
		if (!is_object($army)) $army = sqlgetobject("SELECT * FROM `army` WHERE `id`=".intval($army));
		if (itemspawn_debugout) echo "pickupItem($item->type [$item->id],$army->name,$limitamount=-1)<br>";
		if (itemspawn_debugout) if (!$item || !$army) echo "armee oder item nicht gefunden<br>";
		if (!$item || !$army) return false;
		if (itemspawn_debugout) if ($item->amount < 1.0) echo "zuwenig vom item da<br>";
		if ($item->amount < 1.0) return false;
		if (intval($gItemType[$item->type]->flags) & kItemFlag_NoPickup) echo "nicht aufhebbar<br>";
		if (intval($gItemType[$item->type]->flags) & kItemFlag_NoPickup) return false;
		if (!($item->army == 0 && $item->building == 0)) { echo "item nicht frei,<br>"; return false; }
		if (!$overridepos && !($army->x == $item->x && $army->y == $item->y)) { echo "armee nicht auf item<br>"; return false; }
		
		
		$army->units = cUnit::GetUnits($army->id);
		$freeload = max(0,cUnit::GetUnitsSum($army->units,"last") - cArmy::GetArmyTotalWeight($army));
		
		// army is already full
		if ($freeload <= 0 && $gItemType[$item->type]->weight > 0) echo "kein platz<br>";
		if ($freeload <= 0 && $gItemType[$item->type]->weight > 0) return false;
		
		$addset = "";
		/*
		// gammel on pickup
		if ($item->param == 0 && 
			$gItemType[$item->type]->gammeltime > 0 && 
			(intval($gItemType[$item->type]->flags) & kItemFlag_GammelOnPickup)) {
			$item->param = time()+$gItemType[$item->type]->gammeltime;
			$addset .= " , `param` = ".$item->param;
		}*/
		
		if ($limitamount == -1) $limitamount = $item->amount;
		$limitamount = max(0,min($item->amount,intval($limitamount)));
		if ($gItemType[$item->type]->weight > 0)
				$takeamount = max(0,min($limitamount,floor($freeload / $gItemType[$item->type]->weight)));
		else	$takeamount = $limitamount;
		
		$item->amount -= $takeamount;
		if ($item->amount >= 1.0)
				sql("UPDATE `item` SET `amount` = `amount` - ".$takeamount." WHERE `amount` >= $takeamount AND `id` = ".$item->id); // TODO : check for rounding error !
		else	sql("DELETE FROM `item` WHERE `id` = ".$item->id);
		
		if (mysql_affected_rows() <= 0) echo "nicht mehr genug vom item da<br>";
		if (mysql_affected_rows() <= 0) return false;
		
		$newitem = false;
		$newitem->type = $item->type;
		$newitem->amount = $takeamount;
		$newitem->param = $item->param;
		$newitem->quest = $item->quest;
		$newitem->army = $army->id;
		
		// merge in army
		sql("UPDATE `item` SET `amount` = `amount` + ".$newitem->amount." 
			WHERE `type` = ".$newitem->type." AND `param` = ".$newitem->param." AND `quest` = ".$newitem->quest." AND 
			`army` = ".$newitem->army." LIMIT 1");
		$merging = mysql_affected_rows() >= 1;
		if (!$merging) {			
			// TODO : use spawnitem instead
			sql("INSERT INTO `item` SET ".obj2sql($newitem));
			$newitem->id = mysql_insert_id();
			if (intval($gItemType[$item->type]->flags) & kItemFlag_UseOnPick) cItem::useItem($newitem,$army);
		}
		
		return true;
	}
	
	//drop an item at army position, item/army are id/object
	function dropItem($item,$army,$dropamount=1) { // dropamount = -1 -> drop all
		if (!is_object($item)) $item = sqlgetobject("SELECT * FROM `item` WHERE `id`=".intval($item));
		if (!is_object($army)) $army = sqlgetobject("SELECT * FROM `army` WHERE `id`=".intval($army));
		if (itemspawn_debugout) echo "dropItem($item->type [$item->id],$army->name,$dropamount=-1)<br>";
		if (!$item || !$army) return false;
		if ($item->army != $army->id) return false;
		if ($dropamount == -1) $dropamount = $item->amount;
		$item->x = $army->x;
		$item->y = $army->y;
		$dropamount = max(0,min($item->amount,intval($dropamount)));
		if ($dropamount == 0) return;
		
		
		$item->amount -= $dropamount;
		if ($item->amount > 0)
				sql("UPDATE `item` SET `amount` = `amount` - $dropamount WHERE `amount` >= $dropamount AND `id` = ".$item->id);
		else	sql("DELETE FROM `item` WHERE `id` = ".$item->id);
		
		if (mysql_affected_rows() <= 0) return;
		
		cItem::SpawnItem($army->x,$army->y,$item->type,$dropamount,$item->quest,$item->param);
		sql("UPDATE `army` SET `useditem`=0 WHERE `useditem`=".intval($item->id));
		return true;
	}
	
	//drops all items , used by escape
	function dropAll($army){
		if (!is_object($army)) $army = sqlgetobject("SELECT * FROM `army` WHERE `id`=".intval($army));
		if (!$army) return;
		$items = sqlgettable("SELECT * FROM `item` WHERE `army`=".$army->id);
		foreach ($items as $item) 
			cItem::dropItem($item,$army,-1);
	}
	
	//pickup all items
	function pickupall($army){
		if (!is_object($army)) $army = sqlgetobject("SELECT * FROM `army` WHERE `id`=".intval($army));
		$items = sqlgettable("SELECT * FROM `item` WHERE `x`=".$army->x." AND `y`=".$army->y." AND `army` = 0 AND `building` = 0");
		foreach ($items as $item) {
			if (cItem::pickupItem($item,$army))
				$army = sqlgetobject("SELECT * FROM `army` WHERE `id`=".$army->id);
		}
		return $army;
	}
	
	function getArmyItemsWeight ($armyid) {
		// todo : check if array and using $gItemType[..]->weight is faster ??
		return sqlgetone("SELECT SUM(`itemtype`.`weight` * `item`.`amount`) FROM `item`,`itemtype` WHERE 
			`item`.`type` = `itemtype`.`id` AND `item`.`army` = ".intval($armyid));
	}
	
	
	function generateSoftTerrain ($x,$y,$newtype) {
		// used by osterei
		global $gTerrainType;
		$type = cMap::StaticGetTerrainAtPos(intval($x),intval($y));
		if (($gTerrainType[$type]->movable_flag & kTerrain_Flag_Moveable_Land) == 0) return false;
		sql("REPLACE INTO `terrain` SET ".arr2sql(array("x"=>intval($x),"y"=>intval($y),"type"=>intval($newtype))));
		return true;
	}
	
	function generateSoftTerrainWBorder ($x,$y,$newtype,$newbordertype=0) {
		// used by osterei
		require_once("lib.map.php");
		if ($newbordertype == 0) $newbordertype = $newtype;
		cItem::generateSoftTerrain($x,$y,$newtype);
		cItem::generateSoftTerrain($x-1,$y-1,$newbordertype);
		cItem::generateSoftTerrain($x+0,$y-1,$newbordertype);
		cItem::generateSoftTerrain($x+1,$y-1,$newbordertype);
		cItem::generateSoftTerrain($x-1,$y+0,$newbordertype);
		cItem::generateSoftTerrain($x+1,$y+0,$newbordertype);
		cItem::generateSoftTerrain($x-1,$y+1,$newbordertype);
		cItem::generateSoftTerrain($x+0,$y+1,$newbordertype);
		cItem::generateSoftTerrain($x+1,$y+1,$newbordertype);
		RegenAreaNWSE($x-2,$y-2,$x+2,$y+2,true);
	}
		
	function canUseItem ($item,$army) {
		if (!is_object($item)) $item = sqlgetobject("SELECT * FROM `item` WHERE `id`=".intval($item));
		if (!is_object($army)) $army = sqlgetobject("SELECT * FROM `army` WHERE `id`=".intval($army));
		if (!$item || !$army) return false;
		if ($item->army != $army->id) return false;
		global $gItemType; 
		if (intval($gItemType[$item->type]->flags) & kItemFlag_UseGivesCost) return true;
		
		$siege = 1 == sqlgetone("SELECT 1 FROM `siege` WHERE `army` = ".$army->id);
		$pillage = 1 == sqlgetone("SELECT 1 FROM `pillage` WHERE `army` = ".$army->id);
		
		switch ($item->type) {
			case kItem_Portalstein_Blau:
			case kItem_Portalstein_Gruen:
			case kItem_Portalstein_Schwarz:
			case kItem_Portalstein_Rot:
			case kItem_FaulesEi:
				if ($army->type == kArmyType_Karawane) return false;
				if ($army->type == kArmyType_Arbeiter) return false;
				$fight = sqlgetone("SELECT 1 FROM `fight` WHERE `attacker` = ".$army->id." OR `defender` = ".$army->id);
				return !$fight && !$siege && !$pillage;
			break;
			case kItem_Osterei0+0:
			case kItem_Osterei0+1:
			case kItem_Osterei0+2:
			case kItem_Osterei0+3:
			case kItem_Osterei0+4:
			case kItem_Osterei0+5:
				if ($army->type == kArmyType_Karawane) return false;
				if ($army->type == kArmyType_Arbeiter) return false;
				$fights = sqlgettable("SELECT * FROM `fight` WHERE `attacker` = ".$army->id." OR `defender` = ".$army->id);
				foreach ($fights as $o) {
					$enemy = sqlgetobject("SELECT * FROM `army` WHERE `id` = ".(($o->attacker==$army->id)?$o->defender:$o->attacker));
					if ($enemy->user > 0) return false;
				}
				return !$siege && !$pillage;
			break;
		}
		return false;
	}
	
	function useItem ($item,$army) {
		if (!is_object($item))$item = sqlgetobject("SELECT * FROM `item` WHERE `id`=".intval($item));
		if (!is_object($army))$army = sqlgetobject("SELECT * FROM `army` WHERE `id`=".intval($army));
		if (itemspawn_debugout) echo "useItem($item->type [$item->id],$army->name)<br>";
		if (!$item || !$army) return false;
		if (!cItem::canUseItem($item,$army)) return false;
		$x = $army->x;
		$y = $army->y;

		global $gItemType,$gRes; 
		if (intval($gItemType[$item->type]->flags) & kItemFlag_UseGivesCost) {
			$oldmod = array();
			$myres = $gRes;
			foreach($myres as $n=>$f) if ($gItemType[$item->type]->{"cost_".$f} > 0)
				$oldmod[] = "`$f` = `$f` + ".($gItemType[$item->type]->{"cost_".$f} * $item->amount);
			sql("UPDATE `army` SET ".implode(" , ",$oldmod)." WHERE `id` = ".$army->id." LIMIT 1");
			sql("DELETE FROM `item` WHERE `id` = ".$item->id);
			return array($x,$y);
		}
		
		
		$res = false;
		$teleportziele = false;
		switch ($item->type) {
			case kItem_Portalstein_Blau: // blau -> öff. portal
				$teleportziele = sqlgettable("SELECT *,SQRT((x-$x)*(x-$x) + (y-$y)*(y-$y)) as `dist` FROM `building` 
					WHERE `type` = ".kBuilding_Portal." AND `user` = 0 ORDER BY `dist`");
			break;
			case kItem_Portalstein_Gruen: // grün -> eigenes lager
				$teleportziele = sqlgettable("SELECT *,SQRT((x-$x)*(x-$x) + (y-$y)*(y-$y)) as `dist` FROM `building` 
					WHERE `type` = ".kBuilding_Silo." AND `user` = ".$army->user." ORDER BY `dist`");
			break;
			case kItem_Portalstein_Schwarz: // schwarz
				$teleportziele = sqlgettable("SELECT *,SQRT((x-$x)*(x-$x) + (y-$y)*(y-$y)) as `dist` FROM `building` 
					WHERE `type` = ".kBuilding_Baracks." AND `user` = ".$army->user." ORDER BY `dist`");
			break;
			case kItem_Portalstein_Rot: // rot
				$teleportziele = sqlgettable("SELECT *,SQRT((x-$x)*(x-$x) + (y-$y)*(y-$y)) as `dist` FROM `army` 
					WHERE `id` <> ".$army->id." AND `type` = ".kArmyType_Normal." AND `user` = ".$army->user." ORDER BY `dist`");
			break;
			case kItem_FaulesEi:
				$spawn = cArmy::SpawnArmy($x,$y,cUnit::Simple(kUnitType_Huhn),false,-1,0,0,0,false,kArmyFlag_Wander|kArmyFlag_AutoAttack);
				if ($spawn) cItem::SpawnArmyItem($spawn->id,kItem_FaulesEi);
				$res = array($x,$y);
			break;
			/*
			0 : erzeugt höllenhund, roter portalstein, flucht vor monsterkampf
			1 : erzeugt wald
			2 : erzeugt geröll
			3 : erzeugt felder
			4 : erzeugt hähnchen, grüner portalstein, flucht vor monsterkampf
			5 : teleportiert ramme zur armee
			*/
			case kItem_Osterei0+0:
				$teleportziele = sqlgettable("SELECT *,SQRT((x-$x)*(x-$x) + (y-$y)*(y-$y)) as `dist` FROM `army` 
					WHERE `id` <> ".$army->id." AND `type` = ".kArmyType_Normal." AND `user` = ".$army->user." ORDER BY `dist`");
				cFight::StopAllArmyFights($army,"Die Armee _ARMYNAME_ von _ARMYOWNERNAME_ hat sich wegteleportiert(Osterei).");
				$res = cItem::TeleportToFirstInList($army,$teleportziele);
				if ($res) cArmy::SpawnArmy($x,$y,cUnit::Simple(kUnitType_Hellhound,10),false,-1,0,0,0,false,kArmyFlag_Wander|kArmyFlag_AutoAttack);
				return $res;
			break;
			case kItem_Osterei0+1:
				cItem::generateSoftTerrainWBorder($x,$y,kTerrain_Forest,kTerrain_YoungForest);
				return array($x,$y);
			break;
			case kItem_Osterei0+2:
				cItem::generateSoftTerrainWBorder($x,$y,kTerrain_Rubble,kTerrain_Rubble);
				return array($x,$y);
			break;
			case kItem_Osterei0+3:
				cItem::generateSoftTerrainWBorder($x,$y,kTerrain_Field,kTerrain_Field);
				return array($x,$y);
			break;
			case kItem_Osterei0+4:
				$teleportziele = sqlgettable("SELECT *,SQRT((x-$x)*(x-$x) + (y-$y)*(y-$y)) as `dist` FROM `building` 
					WHERE `type` = ".kBuilding_Silo." AND `user` = ".$army->user." ORDER BY `dist`");
				cFight::StopAllArmyFights($army,"Die Armee _ARMYNAME_ von _ARMYOWNERNAME_ hat sich wegteleportiert(Osterei).");
				$res = cItem::TeleportToFirstInList($army,$teleportziele);
				if ($res) cArmy::SpawnArmy($x,$y,cUnit::Simple(kUnitType_Huhn,1000),false,-1,0,0,0,false,kArmyFlag_Wander|kArmyFlag_AutoAttack);
				return $res;
			break;
			case kItem_Osterei0+5:
				$ramme = sqlgetobject("SELECT * FROM `army` WHERE `type` = ".kArmyType_Siege." AND `user` = ".$army->user);
				if (!$ramme) return false;
				$ramme->units = cUnit::GetUnits($ramme->id);
				$pos = cArmy::FindExit($x,$y,$army->user,$ramme->units);
				if (!$pos) return false;
				list($nx,$ny) = $pos;
				sql("UPDATE `army` SET `x` = $nx, `y` = $ny WHERE `id` = ".$ramme->id);
				QuestTrigger_TeleportArmy($ramme,false,$nx,$ny);
				return array($x,$y);
			break;
		}
		if ($teleportziele) $res = cItem::TeleportToFirstInList($army,$teleportziele);
		
		if ($res) {
			$item->amount -= 1;
			if ($item->amount >= 1.0)
					sql("UPDATE `item` SET `amount` = `amount` - 1 WHERE `id` = ".$item->id);
			else	sql("DELETE FROM `item` WHERE `id` = ".$item->id);
		}
		return $res;
	}
	
	function TeleportToFirstInList($army,$list) {
		if(!is_object($army))$army = sqlgetobject("SELECT * FROM `army` WHERE `id`=".intval($army));
		if (!isset($army->units)) $army->units = cUnit::GetUnits($army->id);
		foreach ($list as $o) {
			$pos = cArmy::FindExit($o->x,$o->y,$army->user,$army->units);
			if (!$pos) continue;
			list($x,$y) = $pos;
			sql("UPDATE `army` SET `x` = $x, `y` = $y WHERE `id` = ".$army->id);
			QuestTrigger_TeleportArmy($army,false,$x,$y);
			return array($x,$y);
		}
		return false;
	}

} // end class
?>
