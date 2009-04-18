<?php
require_once("lib.quest.php");
require_once("lib.main.php");
require_once("lib.army.php"); 
require_once("lib.map.php");
require_once("lib.hook.php");
require_once("lib.fight.php");

// todo : lib.main.php  GetBuildingCSS 



class cBuilding {

	function Think ($building,$debug=false) {
		if (cFight::ThinkShooting($building,kUnitContainer_Building,$debug)) return;
	}
	
	function GetJavaScriptBuildingData ($building,$userid=false,$quote='"') {
		global $gUser,$gBuildingType,$gContainerType2Number;
		if ($userid === false) $userid = $gUser->id;
		$building->jsflags = 0;
		$building->hp = floor($building->hp);
		
		$building->unitstxt = ""; 
		if ($building->user == $userid || intval($gBuildingType[$building->type]->flags) & kBuildingTypeFlag_OthersCanSeeUnits) {
			$units = cUnit::GetUnits($building->id,kUnitContainer_Building);
			foreach ($units as $u) $building->unitstxt .= $u->type.":".floor($u->amount)."|";
		}
		
		if ($building->type == kBuilding_Portal) {
			if (intval(GetBParam($building->id,"target"))>0) $building->jsflags |= kJSMapBuildingFlag_Open;
		} else {
			if (cBuilding::BuildingOpenForUser($building,$userid)) $building->jsflags |= kJSMapBuildingFlag_Open;
		}
		
		if (sqlgetone("SELECT 1 FROM `siege` WHERE `building` = ".$building->id." LIMIT 1"))
			$building->jsflags |= kJSMapBuildingFlag_BeingSieged;
			
		if (sqlgetone("SELECT 1 FROM `pillage` WHERE `building` = ".$building->id." LIMIT 1"))
			$building->jsflags |= kJSMapBuildingFlag_BeingPillaged;
			
		if (sqlgetone("SELECT 1 FROM `shooting` WHERE `lastshot` > ".(time()-kShootingAlarmTimeout)." AND
			`attacker` = ".$building->id." AND `attackertype` = ".$gContainerType2Number[kUnitContainer_Building]." LIMIT 1"))
			$building->jsflags |= kJSMapBuildingFlag_Shooting;
			
		if (sqlgetone("SELECT 1 FROM `shooting` WHERE `lastshot` > ".(time()-kShootingAlarmTimeout)." AND
			`defender` = ".$building->id." AND `defendertype` = ".$gContainerType2Number[kUnitContainer_Building]." LIMIT 1"))
			$building->jsflags |= kJSMapBuildingFlag_BeingShot;
			
		return obj2jsparams($building,"x,y,type,user,level,hp,construction,jsflags,unitstxt,id,burning_since",$quote); // end
	}
	
	function CanControllBuilding ($building,$user) {
		return $user->id == $building->user || ($building->user == 0 && $user->admin);
	}
	
	function getPortalConCost ($building,$target=false) {
		// currently target is not important, passed as false
		if ($building->user == 0)
				return array(1000,1000,1000,1000,0);  	// TODO :unhardcode
		else	return array(1000,1000,1000,1000,1500); // TODO :unhardcode
	}
	
	function getPortalFetchArmyCost ($building) {
		// currently target is not important, passed as false
		if ($building->user == 0)
				return array(1000,1000,1000,1000,0);  	// TODO :unhardcode
		else	return array(1000,1000,1000,1000,1500); // TODO :unhardcode
	}
	
	function getPortalConTax ($building,$target,$userid) {
		if (is_object($userid)) $userid = $userid->id;
		$outtax = cBuilding::BuildingTaxForUser($building,$userid)?GetBParam($building->id,"tax"):false;
		$intax = cBuilding::BuildingTaxForUser($target,$userid)?GetBParam($target->id,"tax"):false;
		if ($outtax) $outtax = explode(",",$outtax);
		if ($intax) $intax = explode(",",$intax);
		if ($outtax && $intax) return array_add($outtax,$intax);
		if ($outtax) return $outtax;
		return $intax;
	}
	
	function BuildingOpenForUser ($building,$userid) {
		if (is_object($userid)) $userid = $userid->id;
		if (!is_object($building)) $building = sqlgetobject("SELECT * FROM `building` WHERE `id`=".intval($building));
		global $gOpenableBuildingTypes;
		if (!in_array($building->type,$gOpenableBuildingTypes)) return true;
		if ($building->type == kBuilding_Portal && $building->user == 0) return true; // HACK
		if ($building->construction > 0) return false;
		if (sqlgetone("SELECT 1 FROM `siege` WHERE `building` = ".$building->id." LIMIT 1")) return false;
		if ($building->user == $userid) return true;
		$fof = $userid ? GetFOF($building->user,$userid) : ($building->user?kFOF_Enemy:kFOF_Friend); // if both user and building belong to server, allow passage
		if (!(intval($building->flags) ^ kBuildingFlag_OpenMask)) return true; // open for all ?
		if ($fof == kFOF_Enemy)							return (intval($building->flags) & kBuildingFlag_Open_Enemy) != 0;
		if ($fof == kFOF_Friend) 						return (intval($building->flags) & kBuildingFlag_Open_Friend) != 0;
		if (IsInSameGuild($building->user,$userid))	return (intval($building->flags) & kBuildingFlag_Open_Guild) != 0;
		return (intval($building->flags) & kBuildingFlag_Open_Stranger) != 0;
	}
	
	function BuildingTaxForUser ($building,$userid) {
		if (is_object($userid)) $userid = $userid->id;
		if (!is_object($building)) $building = sqlgetobject("SELECT * FROM `building` WHERE `id`=".intval($building));
		if ($building->user == $userid) return false;
		if ($building->type == kBuilding_Portal && $building->user == 0) return true;
		global $gTaxableBuildingTypes;
		if (!in_array($building->type,$gTaxableBuildingTypes)) return false;
		
		$fof = $userid ? GetFOF($building->user,$userid) : kFOF_Enemy;
		if (!(intval($building->flags) ^ kBuildingFlag_TaxMask)) return true;
		if ($fof == kFOF_Enemy)							return (intval($building->flags) & kBuildingFlag_Tax_Enemy) != 0;
		if ($fof == kFOF_Friend) 						return (intval($building->flags) & kBuildingFlag_Tax_Friend) != 0;
		if (IsInSameGuild($building->user,$userid))	return (intval($building->flags) & kBuildingFlag_Tax_Guild) != 0;
		return (intval($building->flags) & kBuildingFlag_Tax_Stranger) != 0;
	}
	
	function calcMaxBuildingHp($type,$level){
		global $gBuildingType;
		$maxhp = $gBuildingType[$type]->maxhp;
		$newmaxhp = ceil($maxhp + $maxhp/100*1.5*$level);  // TODO :unhardcode
		//echo "[type=$type maxhp=$maxhp level=$level maxhp=$newmaxhp]";
		return $newmaxhp;
	}
	
	function SetBuildingUpgrades($buildingid,$num) {
		$building = sqlgetobject("SELECT * FROM `building` WHERE `id` = ".intval($buildingid));
		if (empty($building)) return;
		$num = max(($building->upgradetime == 0)?0:1,intval($num));
		sql("UPDATE `building` SET `upgrades` = ".$num." WHERE `id` = ".intval($buildingid));
	}
	
	function calcUpgradeCostsMod($level){ // todo : document in wiki...
		$n=$level;
		if($n>3){
			if($n>7)$t=2.9;
			else $t=7;
			$cor=$n/$t;
			if($n>6)
				$suff=$n/5;
			else 
				$suff=0;
			$mod=((6+$cor)*((($n-1)*($n-1))/($n*$n)))+$suff;
		}else{
		 $mod=$n-0.1;
		}
		return $mod;
	}
	
	 
	function calcUpgradeTime($btypeid,$level=0) {
		global $gBuildingType;
		if ($btypeid != kBuilding_HQ)
				$base = $gBuildingType[$btypeid]->buildtime;
		else	$base = kHQ_Upgrade_BaseTime;
		
		if($base<20)$base=43200;  // TODO :unhardcode
		$time=round(($base*($level))/30,0);  // TODO :unhardcode
		return $time;
	}
	
	//removes a bulding, but only the one of the user that is logged in
	function removeBuilding($building,$userid=0,$noruin=false,$report_css_change=false) { // obj or id
		global $gBuildingType,$gUser;
		if (!is_object($building))
			$building = sqlgetobject("SELECT * FROM `building` WHERE `id`=".intval($building));
		if ($userid == 0 && (empty($building) || $building->user != 0)) $userid = $gUser->id;
		if (empty($building) || $building->user != $userid) return false;
		
		require_once("lib.fight.php");
		cFight::StopAllBuildingFights($building,"Das Gebäude _BUILDINGTYPE_ bei (_x_,_y_) von _BUILDINGOWNERNAME_ wurde zerstört.");
		
		sql("DELETE FROM `building` WHERE `id`=".$building->id);
		sql("DELETE FROM `action` WHERE `building`=".$building->id);
		$hellholes = sqlgettable("SELECT * FROM `hellhole` WHERE `x`=".$building->x." AND `y`=".$building->y);
		foreach ($hellholes as $o) {
			require_once("lib.hellholes.php");
			$hellhole = GetHellholeInstance($o);
			$hellhole->Destroy($userid);
		}
		// forschung stoppen
		sql("UPDATE `technology` SET `upgradebuilding` = 0 , `upgradetime` = 0, `upgrades` = 0
			WHERE `upgradetime` > 0 AND `upgradebuilding` = ".$building->id);
				
		$terraintype = cMap::StaticGetTerrainAtPos($building->x,$building->y);
				
		// in ruine oder schutt verwandeln, wenn es keine brücke oder ähnliches ist
		if ($building->type != kBuilding_Bridge && $building->type != kBuilding_GB && rand(0,1) == 0) {
			if (
				($terraintype == kTerrain_Grass) && 
				($noruin || $gBuildingType[$building->type]->ruinbtype == 0 || rand(0,1) == 0)
			) {
				// schutt
				$schutt = false;
				$schutt->type = (rand(0,1) == 0)?kTerrain_Rubble:kTerrain_Flowers;
				$schutt->x = $building->x;
				$schutt->y = $building->y;
				sql("DELETE FROM `terrain` WHERE `x` = ".$building->x." AND `y` = ".$building->y);
				sql("INSERT INTO `terrain` SET ".obj2sql($schutt));
			} else if(isset($gBuildingType[$gBuildingType[$building->type]->ruinbtype])){
				// ruine
				$ruin = false;
				$ruin->type = $gBuildingType[$building->type]->ruinbtype;
				$ruin->level = $building->level;
				$ruin->x = $building->x;
				$ruin->y = $building->y;
				$ruin->hp = kRuinStartHp;
				sql("INSERT INTO `building` SET ".obj2sql($ruin));
			}
		}
		
		//TODO: wenn kaserne vernichtet wird armee (aus 80% der einheiten) erstellen wenn moeglich sonst units loeschen
		//Wenn bruecke vernichtet wird und armee drauf steht sollten 50% ueberleben und in 2 armeen aufgesplittet werden
		//beides evtl mit zufall auf basisprozente
		// ghouly : aber wir müssen aufpassen das dadurch das armee limit nicht überschritten werden kann, 
		//   sonst werden das manche leute absichtlich machen mit milizenarmeen, und dann mit richtigen einheiten aufstocken.
		//   ich finde nicht das man eine brücke auf der eine armee steht absichtlich einreissen können soll, das braucht arbeiter,
		//   die die armee abschalchten würde, brücken werden ja schliesslich nicht mit sprengstoff drinnen gebaut.
		//   evtl bei spielerlöschung sogar die brücken als spieler 0 gebäude da lassen.
		
		switch($building->type){
			case kBuilding_HQ: {
				// haupthaus vernichtet, alles andere auch vernichten
				$allbuildings = sqlgettable("SELECT * FROM `building` WHERE `user` = ".$userid);
				foreach ($allbuildings as $o)
					cBuilding::removeBuilding($o,$userid);
		
				sql("DELETE FROM `construction` WHERE `user` = ".$userid);
				sql("DELETE FROM `technology` WHERE `user` = ".$userid); // neuanfang ist wichtig ;) ausserdem mit blick in die zukunft ...
				sql("UPDATE `user` SET `guildpoints` = 0 WHERE `id`=".$userid); // waere sonst unfair
				// WARNING : require_once in function is only save, when :
				// a) you know the function is only called once, and the inlcude not neeeded otherwise (tear down building)
				// b) the include file is included bevore this function anyway (cron)
				// otherwise all the "globals" from this include file won't be accesssible outside this function
		
				$allarmies = sqlgettable("SELECT * FROM `army` WHERE `user` = ".$userid);
				foreach ($allarmies as $o) cArmy::DeleteArmy($o); // cleans up all things belonging to army (units, waypoints...)
			} break;
			case kBuilding_Hellhole: {
				sql("DELETE FROM `hellhole` WHERE `x`=($building->x) AND `y`=($building->y) LIMIT 1");
			} break;
		}
		
		$result = true;
		if ($report_css_change)
				$result = RegenSurroundingNWSE($building->x,$building->y,true);
		else	RegenSurroundingNWSE($building->x,$building->y);
		
		Hook_DestroyBuilding($building);
		return $result;
	}
	
	
	
			
	function listAllKaserneTargets($building) {
		$x = $building->x;
		$y = $building->y;
		return sqlgettable("SELECT *,SQRT((`x`-($x))*(`x`-($x)) + (`y`-($y))*(`y`-($y))) as `dist` FROM `building` WHERE 
			`type` = ".$building->type." AND `user` = ".$building->user." AND `id` <> ".$building->id." HAVING `dist` <= ".kBaracksDestMaxDist." ORDER BY `dist`");
	}
			
	//list all possible portal targets
	function listAllPortalTargets($building,$user) {
		if (!is_object($building)) $building = sqlgetobject("SELECT * FROM `building` WHERE `id`=".intval($building));
		if (!is_object($user)) $user = sqlgetobject("SELECT * FROM `user` WHERE `id`=".intval($user));
		
		$allportals = sqlgettable("SELECT *,
			SQRT((`x` - ".$building->x.")*(`x` - ".$building->x.") + 
				 (`y` - ".$building->y.")*(`y` - ".$building->y.")) as `dist`
			FROM `building` WHERE `type` = ".kBuilding_Portal." AND `construction` = 0 ORDER BY `dist`");
		$maxdist = 100*(1+$building->level);  // TODO :unhardcode
		$list = array();
		foreach ($allportals as $o) 
			if ($o->id != $building->id && $o->dist < $maxdist && cBuilding::BuildingOpenForUser($o,$user->id)) 
				$list[] = $o;
		return $list;
	}

} // end class
?>
