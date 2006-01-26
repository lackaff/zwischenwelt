<?php
require_once("class.infobase.php");
require_once("../lib.main.php");
require_once("../lib.building.php");
require_once("../lib.technology.php");
require_once("../lib.transfer.php");

$gBuildingClassCommandCalled = false;

class cInfoBuilding extends cInfoBase {
	// todo : replace all those "$gObject->user == $gUser->id" by this function, to enable urlaubsvertretung
	function cancontroll ($user=false) { 
		global $gObject,$gUser;
		if (!$user) $user = $gUser;
		return cBuilding::CanControllBuilding($gObject,$user);
	} 
	
	function command () {
		global $f_building,$f_id,$gObject;
		if (!isset($f_building)) return;
		if (!isset($f_id)) warning("buildingcommand : buildingid not set !");
		$gObject = sqlgetobject("SELECT * FROM `building` WHERE `id` = ".intval($f_id));
		if (!$gObject) return;
		if ($gObject->construction == 0) parent::command();
	}
	
	function GetMainTabHead () { // header grafik für main tab
		global $gObject,$gUser,$gBuildingType;
		$btype = $gBuildingType[$gObject->type];
		return $this->GetBuildingPic();//.$btype->name;
	}
	
	

	function GetBuildingPic () { // uses function with the same name from lib.main.php
		global $gObject,$gUser,$gBuildingType;
		$btype = $gBuildingType[$this->type];
		$gfx = GetBuildingPic($this->type,$this->user,$this->level);
		return "<img class=\"info_buildingpic\" alt=\"".($btype->name)."\" title=\"".($btype->name)."\" src=\"".$gfx."\">";
	}
	
	// execute drawing code in display() after displaying the buffered command() output
	function classgenerate_tabs () {
		$this->generate_tab_building();
		if ($this->construction > 0) return;
		$this->generate_tab_unit_production();
		$this->generate_tab_technology();
		// TODO : eliminate infobase_cmdout ?
		// TODO : unit transfer tabbing :
		if (cTransfer::has_armytransfer($this,false)) {
			rob_ob_start();
			cTransfer::display_armytransfer($this,false);
			RegisterInfoTab("Truppen",rob_ob_end(),3);
			
			// kampfsim link in jedem gebäude in dem man KAMPF-einheiten produzieren kann
			$units = $this->producable_units();
			$has_fighters = 0;
			foreach ($units as $o) if ($o->a > 0) $has_fighters++;
			global $gInfoTabs;
			if ($has_fighters > 1) $gInfoTabs[] = array("KampfSim","",Query("kampfsim.php?sid=?"));
		}
	}
	
	function mygenerate_tabs () {
		global $gObject,$gUser,$gBuildingType,$gGlobal,$gRes;
		global $gTaxableBuildingTypes,$gOpenableBuildingTypes;
		global $gOwnerBuildingFlags,$gBuildingFlagNames;
		if ($gObject->construction > 0) return;
		// override me...
	}
	
	function display () {
		global $gObject; $gObject = $this; // backwards compatibility, better user $this
		//hide display, if this is still under construction
		//if($this->construction > 0)return;
		// set into a nice papyrus info in display
		//echo get_class($this);vardump2($this);
		if ($this->infobase_cmdout) {
			ImgBorderStart("s1","jpg","#ffffee","",32,33);
			//echo $this->infobase_cmdout;
			echo $this->infobase_cmdout;
			ImgBorderEnd("s1","jpg","ffffee",32,33);
			echo "<hr>";
		}
		if ($this->infobase_nodisplay) return;
		//echo "displaying ".get_class($this)."<hr>";
		//vardump2($this);echo "<hr>";
	}
	
	function mycommand () {} // override me for building-specific commands
	
	// general commands, gatelike, unit-production etc
	// override me for things like gate and unit-production
	function classcommand () {
		// execute this only once, and not for every buildingtype
		global $gBuildingClasscommand_called; // singleton ?
		if ($gBuildingClasscommand_called) return;
		$gBuildingClasscommand_called = true;
		
		foreach ($_REQUEST as $name=>$val) ${"f_".$name} = $val;
		global $gObject;
		global $gUser;
		global $gRes;
		global $gRes2ItemType;
		global $gUnitType;
		global $gOwnerBuildingFlags;
		global $gTechnologyType;
		
		// #### tech_admin ####
		if ($f_building == "tech_admin" && $gUser->admin) switch ($f_do) {
			case "lvlup":
				// ein level hochcheaten
				sql("UPDATE `technology` SET `level` = `level` + 1 WHERE `user` = ".$gObject->user." AND `type` = ".intval($f_tech));
			break;
			case "lvlmax":
				// auf max level hochcheaten
				sql("UPDATE `technology` SET `level` = ".$gTechnologyType[$f_tech]->maxlevel." WHERE `user` = ".$gObject->user." AND `type` = ".intval($f_tech));
			break;
			case "lvlmin":
				// auf max level hochcheaten
				sql("UPDATE `technology` SET `level` = 0 WHERE `user` = ".$gObject->user." AND `type` = ".intval($f_tech));
			break;
			case "lvlhalfup": 
				// ein halbes level hochcheaten, um den forschungsvorgang selbst zu debuggen...
				sql("UPDATE `technology` SET `upgrades` = 1 , `upgradetime` = ".(time()+3600)." , `upgradebuilding` = ".$gObject->id." 
					WHERE `user` = ".$gObject->user." AND `type` = ".intval($f_tech));
			break;
			case "reset":
				// alle forschungen für diese tech für alle spieler auf 0 resetten
				sql("DELETE FROM `technology` WHERE `type` = ".intval($f_tech));
			break;
			case "newtech":
				if (isset($f_newgroup)) {
					$newtechgroup = false;
					$newtechgroup->buildingtype = $gObject->type;
					$newtechgroup->name = "neue techgroup";
					$newtechgroup->gfx = "upgrades/upgrade_base.png";
					sql("INSERT INTO `technologygroup` SET ".obj2sql($newtechgroup));
					$f_group = mysql_insert_id();
				}
				
				$newtech = false;
				$newtech->buildingtype = $gObject->type;
				$newtech->group = intval($f_group);
				$newtech->name = "neue tech";
				$newtech->gfx = "upgrades/upgrade_base.png";
				$newtech->maxlevel = 0;
				sql("INSERT INTO `technologytype` SET ".obj2sql($newtech));
				
				// regenerate typecache
				global $gTechnologyType; 
				global $gTechnologyGroup; 
				RegenTypeCache();
				require(kTypeCacheFile);
				
			break;
		}

		// #### tech_command : planung, vergessen etc von technologie und forschung ####
		if ($f_building == "tech_command") switch ($f_do) {
			case "plantechupgrades":
				if (!$this->cancontroll()) break;
				if (isset($f_plan)) SetTechnologyUpgrades($f_techtype,$gObject->id,$f_upcount);
				if (isset($f_forget)) {
					// downgrade if idle
					sql("UPDATE `technology` SET `level` = GREATEST(0,`level`-1)
						WHERE `upgradetime` = 0 AND `type` = ".$f_techtype." AND `user` = ".$gObject->user);
					// cancel running uprade
					sql("UPDATE `technology` SET `upgradetime` = 0 , `upgrades` = 0
						WHERE `upgradetime` > 0 AND `type` = ".$f_techtype." AND `user` = ".$gObject->user);
				}
			break;
		}

		// #### gatelike : zoll & offen/geschlossen ####
		if ($f_building == "gatelike") switch ($f_do) {
			case "set_tax_n_flags":
				if (!$this->cancontroll()) break;
				if (!isset($f_i_flags)) $f_i_flags = array();
				if (!is_array($f_i_flags)) $f_i_flags = array(0=>$f_i_flags);
				$wannabe_sum = array_sum($f_i_flags);
				$mask = 0;
				$realsum = 0;
				foreach ($gOwnerBuildingFlags as $flag => $btypes) if (in_array($gObject->type,$btypes)) {
					$mask |= $flag;
					if ($wannabe_sum & $flag)
						$realsum |= $flag;
				}
				$gObject->flags = intval($gObject->flags);
				$gObject->flags &= (kBuildingFlag_AllSet ^ $mask);
				$gObject->flags |= $realsum;
				if (isset($f_foralloftype)) 
						sql("UPDATE `building` SET `flags` = ".$gObject->flags." WHERE `type` = ".$gObject->type." AND `user` = ".$gObject->user);
				else	sql("UPDATE `building` SET `flags` = ".$gObject->flags." WHERE `id` = ".$gObject->id." LIMIT 1");
				
				if (!isset($f_tax)) $f_tax = array();
				if (!is_array($f_tax)) $f_tax = array(0=>$f_tax);
				array_walk($f_tax,"walkint");
				$tax = isset($f_tax)?implode(",",$f_tax):"";
				if (isset($f_foralloftype)) {
					$buildings = sqlgettable("SELECT * FROM `building` WHERE `type` = ".$gObject->type." AND `user` = ".$gObject->user."");
					foreach ($buildings as $o) 
						SetBParam($o->id,"tax",$tax);
				} else	SetBParam($gObject->id,"tax",$tax);
			break;
		}
		
		// #### einheitenproduktion ####
		if ($f_building == "unit_producer") switch ($f_do) {
			case "action":
				if (!$this->cancontroll()) break;
				$gActions = sqlgettable("SELECT * FROM `action` WHERE `building` = ".$gObject->id." ORDER BY `id`","id");
				foreach ($gActions as $o) if (isset(${"f_abort_".$o->id})) {
					sql("DELETE FROM `action` WHERE `starttime` = 0 AND `id` = ".$o->id);
					sql("UPDATE `action` SET `param2` = 1 WHERE `param2` > 1 AND `id` = ".$o->id);
					break;
				}
			break;
			case "build":
				if (!$this->cancontroll()) break;
				$producable_units = $this->producable_units();
				foreach ($producable_units as $o) if (isset(${"f_build_".$o->id}) && $f_amount[$o->id] > 0) {
					if(!HasReq($o->req_geb,$o->req_tech_v.",".$o->req_tech_a,$gObject->user)) break;
					$action = false;
					$action->building = $gObject->id;
					$action->cmd = kActionCmd_Build;
					$action->param1 = $o->id;
					$action->param2 = $f_amount[$o->id];
					$action->starttime = 0;
					sql("INSERT INTO `action` SET ".obj2sql($action));
					break;
				}
			break;
			case "set_all_tasks_to_this":
				if (!$this->cancontroll()) break;
				// todo --- 
				$my_actions = sqlgettable("SELECT * FROM `action` WHERE `building` = ".$gObject->id." ORDER BY `id`","id");
				$my_factories = sqlgettable("SELECT * FROM `building` WHERE `type` = ".$gObject->type." AND `user` = ".$gObject->user." AND `id` <> ".$gObject->id);
				foreach ($my_factories as $fac) {
					sql("DELETE FROM `action` WHERE `starttime` = 0 AND `building` = ".$fac->id);
					sql("UPDATE `action` SET `param2` = 1 WHERE `param2` > 1 AND `building` = ".$fac->id);
					foreach ($my_actions as $act) {
						$newac = $act;
						$newac->building = $fac->id;
						$newac->starttime = 0;
						unset($newac->id);
						sql("INSERT INTO `action` SET ".obj2sql($newac));
					}
				}
			break;
		}
		
		// #### einheiten-stationierung ####
		if ($f_building == "unit_station" && $this->cancontroll()) {
			$possibleTargets = cBuilding::listAllKaserneTargets($gObject); 
			switch ($f_do) {
				case "set_target":
					if ($f_target == 0) SetBParam($gObject->id,"target",0);
					foreach ($possibleTargets as $o) if ($f_target == $o->id)
						SetBParam($gObject->id,"target",$o->id);
				break;
				case "set_target_here":
					foreach ($possibleTargets as $o)
						SetBParam($o->id,"target",$gObject->id);
					SetBParam($gObject->id,"target",$gObject->id);
				break;
			}
		}
	}
	
	function producable_units () {
		// einheitentypen auflisten, die in diesem gebäude produziert werden können
		global $gObject;
		global $gUnitType;
		$res = array();
		foreach ($gUnitType as $o) 
			if ($o->buildtime > 0 && !($o->flags & kUnitFlag_Elite) && $o->buildingtype == $gObject->type)
				$res[] = $o;
		return $res;
	}
	
	//function display_unit_production() {
	function generate_tab_unit_production() {
		global $gObject,$gUser,$gBuildingType,$gUnitType;
		
		// einheitenproduktions-dialog
		if (!$this->cancontroll($gUser)) return;
		if ($gObject->construction > 0) return;
		
		
		$gActions = sqlgettable("SELECT * FROM `action` WHERE `building` = ".$gObject->id." ORDER BY `id`","id");
		$producable_units = $this->producable_units();
		
		// TODO : UNHARDCODE !!!
		$can_station_units = $gObject->type == kBuilding_Harbor || $gObject->type == kBuilding_Baracks || 
			$gObject->type == kBuilding_Silo || 
			$gObject->type == kBuilding_Market;
		
		if (count($producable_units) == 0 && count($gActions) == 0 && !$can_station_units) return;
		
		rob_ob_start();
		ImgBorderStart("s1","jpg","#ffffee","",32,33);
		
		if ($can_station_units) {
			$curtargetid = GetBParam($gObject->id,"target",0);
			$possibleTargets = cBuilding::listAllKaserneTargets($gObject); 
			?>
			<form method="post" action="<?=Query("?sid=?&x=?&y=?")?>">
				<INPUT TYPE="hidden" NAME="building" VALUE="unit_station">
				<INPUT TYPE="hidden" NAME="id" VALUE="<?=$gObject->id?>">
				<INPUT TYPE="hidden" NAME="do" VALUE="set_target">
				Frisch ausgebildete Truppen
				<select name="target">
					<option value="0">- hier -</option>
					<?php foreach ($possibleTargets as $o) {?>
					<option value="<?=$o->id?>" <?=$curtargetid==$o->id?"selected":""?>>bei <?="(".$o->x.",".$o->y.")"?></option>
					<?php } // endforeach?>
				</select>
				<input type="submit" name="save" value="stationieren">
			</form>
			
			<form method="post" action="<?=Query("?sid=?&x=?&y=?")?>">
			<INPUT TYPE="hidden" NAME="building" VALUE="unit_station">
			<INPUT TYPE="hidden" NAME="id" VALUE="<?=$gObject->id?>">
			<INPUT TYPE="hidden" NAME="do" VALUE="set_target_here">
			Alle umliegenden Truppen hier 
			<input type="submit" name="save" value="stationieren">
			</form>
			<br>
			
			<?php 
		} // endif
		
		
		/* ausbildungsauftrag uebertragen */
		?>
		<form method="post" action="<?=Query("?sid=?&x=?&y=?")?>">
		<INPUT TYPE="hidden" NAME="building" VALUE="unit_producer">
		<INPUT TYPE="hidden" NAME="id" VALUE="<?=$gObject->id?>">
		<INPUT TYPE="hidden" NAME="do" VALUE="set_all_tasks_to_this">
		Den Ausbildungsauftrag von hier für alle gleichen Geb&auml;ude 
		<input type="submit" name="save" value="übernehmen">
		</form>
		<?php 
		
		// current actions
		if (count($gActions) > 0) {?>
			<FORM METHOD=POST ACTION="<?=Query("?sid=?&x=?&y=?&building=unit_producer&id=".$gObject->id."&do=action")?>">
			<table border=1 cellspacing=0>
			<tr><th colspan=4>In Ausbildung</th></tr>
			<?php foreach($gActions as $o) switch ($o->cmd) {
				case kActionCmd_Build:
					$unittype = $gUnitType[$o->param1];
					$bgcolor = sqlgetone("SELECT `color` FROM `user` WHERE `id` = ".intval($gObject->user));
					?>
					<tr>
					<td nowrap><img style="background-color:<?=$bgcolor?>" class="picframe" src="<?=g($gUnitType[$o->param1]->gfx)?>"></td>
					<td nowrap><?=$o->param2?></td>
					<td><INPUT TYPE="submit" NAME="abort_<?=$o->id?>" VALUE="abbrechen"></td>
					<td><?=($o->starttime>0)?Duration2Text($unittype->buildtime - (time() - $o->starttime)):""?></td>
					</tr>
					<?php
				break;
			} ?>
			</table>
			</FORM>
			<br>
			<?php 
		}
		
		// unittypelist
		$typemap = array();
		$units_here = cUnit::GetUnits($gObject->id,kUnitContainer_Building);
		foreach ($producable_units as $o) {
			$here = 0;
			foreach ($units_here as $h) 
				if ($h->type == $o->id && $h->spell == 0 && $h->user == $gObject->user) 
					$here += $h->amount;
			$one = false;
			$one->type = $o->id;
			$one->amount = $here;
			$one->spell = 0;
			$one->user = $gObject->user;
			
			if (HasReq($o->req_geb,$o->req_tech_a.",".$o->req_tech_v,$gObject->user)) {
				$one->cell = '<INPUT TYPE="text" NAME="amount['.$o->id.']" VALUE="0" style="width:30px">';
				$one->cell .= '<INPUT TYPE="submit" NAME="build_'.$o->id.'" VALUE="ausbilden">';
			} else {
				$infourl = Query("?sid=?&x=?&y=?&infounittype=".$o->id);
				$one->cell = '<a style="color:red" href="'.$infourl.'">Anforderungen</a>';
			}
			$typemap[] = $one;
		}
		// append not yet listed units stationed here
		foreach ($units_here as $h) {
			$found = false;
			foreach ($producable_units as $o)
				if ($h->type == $o->id && $h->spell == 0 && $h->user == $gObject->user)
					{ $found = true; break; }
			if (!$found) $typemap[] = $h;
		}
		
		if (count($typemap) > 0) { 
			?>
			<FORM METHOD=POST ACTION="<?=Query("?sid=?&x=?&y=?&building=unit_producer&id=".$gObject->id."&do=build")?>">
			<?php cText::UnitsList($typemap,$gObject->user);?>
			</FORM>
			<?php
		}
		
		ImgBorderEnd("s1","jpg","#ffffee",32,33);
		RegisterInfoTab("Ausbildung",rob_ob_end());
	}
	
	
	function generate_tab_building() {
		global $gObject,$gUser,$gBuildingType,$gGlobal,$gRes;
		global $gTaxableBuildingTypes,$gOpenableBuildingTypes,$gFlaggedBuildingTypes;
		global $gOwnerBuildingFlags,$gBuildingFlagNames;
		
		rob_ob_start();
		
		if ($gUser->admin) if (in_array($this->type,$gFlaggedBuildingTypes[kBuildingTypeFlag_CanShoot])) {
			?>
			<a href="<?=query("?sid=?&x=?&y=?&buildingthink=1")?>">(think)</a><br>
			<?php
			global $f_buildingthink;
			if (isset($f_buildingthink)) {
				echo "Thinking...(please reload page afterwards)<br>";
				cBuilding::Think($gObject,true);
				echo "<br>";
			}
		}
		
		$btype = $gBuildingType[$gObject->type];
		if($gObject->type==kBuilding_Portal && intval(sqlgetone("SELECT `value` FROM `buildingparam` WHERE `name`='target' AND `building`=".$gObject->id))>0)
			$btype->gfx=str_replace("zu","offen",$btype->gfx);
		if(($gObject->type==kBuilding_GB || $gObject->type==kBuilding_Gate || $gObject->type==kBuilding_SeaGate) && 
			cBuilding::BuildingOpenForUser($gObject,$gUser->id))
			$btype->gfx=str_replace("zu","offen",$btype->gfx);
		$lpic = ($gObject->level<10)?"0":"1";
		?>
			<table border=0 cellspacing=0 cellpadding=0 width="100%">
			<tr>
				<?php /* #### BAUSTELLEN-BILD #### */ ?>
				<?php if ($gObject->construction) {?>
				<td rowspan=2 valign="top"><img src="<?=g(kConstructionPic)?>" border=1></td>
				<td rowspan=2 valign="center">&gt;</td>
				<?php }?>
				
				<?php /* #### GEBÄUDE BILD & ENERGIE #### */ ?>
				<td rowspan=2 valign="top" width=30>
					<table border=0 cellspacing=0 cellpadding=0>
						<tr><td>
							<?php $infourl = Query("?sid=?&x=?&y=?&infobuildingtype=".$btype->id);?>
							<?php $race = sqlgetone("SELECT `race` FROM `user` WHERE `id` = ".$gObject->user);?>
							<a href="<?=$infourl?>"><?=$this->GetBuildingPic()?></a>
						</td></tr>
						<tr><td height=5 style="border:1px solid black"><?=DrawBar($gObject->hp,cBuilding::calcMaxBuildingHp($btype->id,$gObject->level),GradientRYG(GetFraction($gObject->hp,cBuilding::calcMaxBuildingHp($btype->id,$gObject->level))),"black")?></td></tr>
						<?if($gObject->type==$gGlobal['building_runes']){?>
						<tr><td height=5 style="border:1px solid black"><?=DrawBar($gObject->mana,$btype->basemana*(1+$gObject->level),GradientCB(GetFraction($gObject->mana,$btype->basemana*(1+$gObject->level))),"black")?></td></tr>
						<?}?>
					</table>
				</td>
				
				<?php /* #### NAME & KURZZBESCHREIBUNG #### */ ?>
				<td><?=cText::Wiki("building",$btype->id)?><a href="<?=$infourl?>"><?=$btype->name?></a> 
					<?php 
					$info = array();
					$info[] = "Stufe:".$gObject->level;
					$info[] = "HP:".ceil($gObject->hp)."/".cBuilding::calcMaxBuildingHp($btype->id,$gObject->level);
					if ($gObject->type == $gGlobal['building_runes'])
						$info[] = "Mana:".ceil($gObject->mana)."/".($btype->basemana*(1+$gObject->level));
					if (($slots=getSlotAddonFromSupportFields($gObject->id)) > 0)
						$info[] = "$slots extra Slots";
					?>
					(<?=implode(", ",$info)?>)<br>
					<?php if ($gObject->user > 0) {?>
						<?php $owner = sqlgetobject("SELECT `name`,`moral` FROM `user` WHERE `id` = ".$gObject->user);?>
						<?php $ownerhq = sqlgetobject("SELECT * FROM `building` WHERE `type` = ".kBuilding_HQ." AND `user` = ".$gObject->user);?>
						von <a href="<?=query("?sid=?&x=".$ownerhq->x."&y=".$ownerhq->y)?>"><?=GetFOFtxt($gUser->id,$gObject->user,$owner->name)?></a>
						<a href="<?=query("msg.php?show=compose&to=".($owner->name)."&sid=?")?>"><img border=0 src="<?=g("icon/guild-send.png")?>"></a> 
						<?=Moral2HtmlIcon($owner->moral)?>
						<?php if($gUser->admin){ ?>
							<a href="<?=query("adminuser.php?id=$gObject->user&sid=?")?>"><img alt="user" title="user" src="<?=g("icon/admin.png")?>" border=0></a>
						<?php } ?>
					<?php }?>
				<img alt="Geschwindigkeit: Wartezeit in s bis der nächste Schritt möglich ist" title="Geschwindigkeit: Wartezeit in s bis der nächste Schritt möglich ist" src="<?=g("sanduhrklein.gif")?>">:<?=$btype->speed?>s 
				<?php if (0) { /* TODO : REACTIVATE ONCE THE TERRAIN MOD SYSTEM IS COMPLETE */?>
				Mod:(a*<?=round($btype->mod_a,2)?>|v*<?=round($btype->mod_v,2)?>|f*<?=round($btype->mod_f,2)?>) <?=cText::Wiki("kampf_mod")?>
				<?php } // endif?>
				<?php if($gUser->admin){ ?>
					<a href="<?=query("adminbuilding.php?id=$gObject->id&sid=?")?>"><img alt="Building" title="Building" src="<?=g("icon/admin.png")?>" border=0></a>
					<a href="<?=query("adminunit.php?containerid=$gObject->id&containertype=".kUnitContainer_Building."&sid=?")?>"><img alt=Units title=Units src="<?=g("icon/admin.png")?>" border=0></a>
					<a href="<?=query("adminbuildingtype.php?id=$btype->id&sid=?")?>"><img alt="BuildingType" title="BuildingType" src="<?=g("icon/admin.png")?>" border=0></a>
				<?php } ?>
				</td>
				<?php /* #### BELAGERN #### */ ?>
				<?php if ($gObject->user != $gUser->id) {?>
					<td align="right" nowrap>
						<?php $gArmies = cArmy::getMyArmies(TRUE);?>
						<?php $count = 0; foreach($gArmies as $o) if (cArmy::CanControllArmy($o,$gUser) && cArmy::hasSiegeAttack($o)) $count++; ?>
						<?php if ($count > 0) {?>
							<form method="post" action="<?=Query("?sid=?&x=?&y=?&do=siege&target=".$gObject->id)?>">
							mit <SELECT NAME="army">
							<?php foreach($gArmies as $o) if (cArmy::CanControllArmy($o,$gUser) && cArmy::hasSiegeAttack($o)) {?>
								<OPTION VALUE="<?=$o->id?>" <?=($o->id == $gUser->lastusedarmy)?"selected":""?>><?=$o->name?> (<?=$o->owner?>)</OPTION>
							<?php }?>
							</SELECT>
							<input type="submit" value="belagern">
							</form>
						<?php }?>
					</td>
				<?php }?>
				
			</tr>
			<tr></tr>
			</table>
			
			<?php 
			if (!$gObject->construction) {
				if ($this->cancontroll()) {
					require_once("../lib.building.php");
					$upgrades = $gObject->upgrades;
					$upgrading = $gObject->upgradetime;
					if($upgrading > 0) {
						$time = time();
						$rest = Duration2Text($upgrading-$time);
						echo "Upgrade wird gerade ausgef&uuml;hrt, Restzeit: $rest<br>";
					}
					
					?>
						<?php /* #### UPGRADES #### */ ?>
						<table width="100%"><tr><td align="left" valign="top">
						
							<?php /* #### EINSTELLUNG DES UPGRADES #### */ ?>
							<FORM METHOD=POST ACTION="<?=Query("?sid=?&x=?&y=?")?>">
							<INPUT TYPE="hidden" NAME="do" VALUE="planupgrades">
							<INPUT TYPE="hidden" NAME="id" VALUE="<?=$gObject->id?>">
							<table><tr>
							<td><INPUT TYPE="text" NAME="upcount" VALUE="<?=$upgrades?>" style="width:20px"></td>
							<td><INPUT TYPE="submit" VALUE="Upgrades"></td>
							<td></td>
							</tr></table>
							</FORM>
							
						</td><td align="left" nowrap>
						
							<?php /* #### KOSTEN AUFLISTUNG #### */ ?>
							<table border=1 cellspacing=0 rules="all">
							<tr>
								<td colspan=2 align="center"><a href="<?=Query("summary_buildings.php?sid=?&selbtype=".$gObject->type)?>">Upgrade auf Stufe</a></td>
								<?php foreach($gRes as $n=>$f)echo '<td align="center"><img src="'.g('res_'.$f.'.gif').'"></td>'; ?>
								<td align="center"><img src="<?=g("sanduhrklein.gif")?>"></td>
							</tr>
							<?php foreach($gRes as $n=>$f) ${"totalcost_".$f} = 0; $timesum = 0;?>
							<?php $show = 4;for ($L=$gObject->level;$L<$gObject->level+$upgrades;$L++) {?>
								<?php
									--$show;
									$upmod = cBuilding::calcUpgradeCostsMod($L+1); $time = cBuilding::calcUpgradeTime($btype->id,$L+1);
									if (!$upgrading || $L > $gObject->level) {
										$timesum += $time;
										foreach($gRes as $n=>$f) ${"totalcost_".$f} += round($upmod*$btype->{"cost_".$f},0);
									}
									if($show>0){
								?>
								<tr>
									<td align="right"><?=(!$upgrading || $L > $gObject->level)?"geplant":"wird ausgeführt"?></td>
									<td align="right">&nbsp;<?=$L+1?>&nbsp;</td>
									<?php foreach($gRes as $n=>$f) echo '<td align="right">'.ktrenner(round($upmod*$btype->{"cost_".$f},0)).'</td>'; ?>
									<td align="right"><?=Duration2Text($time)?></td>
								</tr>
							<?php } else if($show == 0){?>
								<tr>
									<td colspan=7>...</td>
								</tr>
							<?php } } ?>
							<?php if ($upgrades > (($upgrading)?1:0)) {?>
								<tr>
									<td align="right"><a href="<?=Query("kosten.php?sid=?")?>"><b>summe</b></a></td>
									<td align="right"></td>
									<?php foreach($gRes as $n=>$f) echo '<td align="right"><b>'.ktrenner(round(${"totalcost_".$f},0)).'</b></td>'; ?>
									<td align="right"><b><?=Duration2Text($timesum)?></b></td>
								</tr>
							<?php }?>
							<?php $upmod = cBuilding::calcUpgradeCostsMod($gObject->level+1+$upgrades);?>
							<tr>
								<td></td>
								<td align="right">&nbsp;<?=$gObject->level+$upgrades+1?>&nbsp;</td>
								<?php foreach($gRes as $n=>$f)echo '<td align="right">'.ktrenner(round($upmod*$btype->{"cost_".$f},0)).'</td>'; ?>
								<td align="right"><?=Duration2Text(cBuilding::calcUpgradeTime($gObject->type,$gObject->level+1+$upgrades))?></td>
							</tr>
							</table>
							
						</td><td align="right" valign="top" nowrap>
						
							<?php /* #### ABREISSEN #### */ ?>
							<FORM METHOD=POST ACTION="<?=Query("?sid=?&x=?&y=?")?>">
							<INPUT TYPE="hidden" NAME="do" VALUE="removebuilding">
							<INPUT TYPE="hidden" NAME="id" VALUE="<?=$gObject->id?>">
							<INPUT TYPE="submit" VALUE="<?=$gObject->construction?"abbrechen":"abreissen"?>"><br>
							<INPUT TYPE="checkbox" NAME="sure" VALUE="1">sicher !
							</FORM>
							
						</td></tr></table>
					<?php
				}
				
				/* #### GATELIKE (zoll und offen/geschlossen) #### */
				if (in_array($gObject->type,$gTaxableBuildingTypes)) {
					$gTax = GetBParam($gObject->id,"tax");
					if ($gTax) $gTax = explode(",",$gTax); 
					?>
					<?php if ($this->cancontroll()) {?>
					<?php } else if (cBuilding::BuildingTaxForUser($gObject,$gUser->id) && cBuilding::BuildingOpenForUser($gObject,$gUser->id)) {?>
						Zoll pro <?=kPortalTaxUnitNum?> Mann : <?=cost2txt($gTax)?><br>
					<?php } // endif Zoll?>
				<?php } // endif tax
				
				if ($this->cancontroll()) { $edited = false;?>
					<FORM METHOD="POST" ACTION="<?=Query("?sid=?&x=?&y=?")?>">
					<INPUT TYPE="HIDDEN" NAME="building" VALUE='gatelike'>
					<INPUT TYPE="HIDDEN" NAME="id" VALUE='<?=$gObject->id?>'>
					<INPUT TYPE="HIDDEN" NAME="do" VALUE="set_tax_n_flags">
					<SCRIPT LANGUAGE="JavaScript" type="text/javascript">
					<!--
						function setchecks (name,check,start,count) {
							for (var i=start;i<start+count;++i)
								document.getElementsByName(name)[i].checked = check;
						}
					//-->
					</SCRIPT>
					<?php if (in_array($gObject->type,$gOpenableBuildingTypes) || in_array($gObject->type,$gTaxableBuildingTypes)) {?>
						<table>
						<?php if (in_array($gObject->type,$gOpenableBuildingTypes)) { $edited = true;?>
							<tr>
								<th>Offen für </th>
								<td align="center" nowrap><?=IFlagCheck($gObject,"flags",kBuildingFlag_Open_Guild)?>Gilde</td>
								<td align="center" nowrap><?=IFlagCheck($gObject,"flags",kBuildingFlag_Open_Friend)?>Freunde</td>
								<td align="center" nowrap><?=IFlagCheck($gObject,"flags",kBuildingFlag_Open_Stranger)?>Fremde</td>
								<td align="center" nowrap><?=IFlagCheck($gObject,"flags",kBuildingFlag_Open_Enemy)?>Feinde</td>
								<td align="center" nowrap>
									&nbsp;&nbsp;
									<input type="checkbox" name="dummy" value="1" <?=(intval($gObject->flags) ^ kBuildingFlag_OpenMask)?"":"checked"?> onChange="setchecks('i_flags[]',this.checked,0,4)">
									alle
								</td>
							</tr>
						<?php }?>
						<?php if (in_array($gObject->type,$gTaxableBuildingTypes)) { $edited = true;?>
							<tr>
								<th>Zoll für </th>
								<td align="center" nowrap><?=IFlagCheck($gObject,"flags",kBuildingFlag_Tax_Guild)?>Gilde</td>
								<td align="center" nowrap><?=IFlagCheck($gObject,"flags",kBuildingFlag_Tax_Friend)?>Freunde</td>
								<td align="center" nowrap><?=IFlagCheck($gObject,"flags",kBuildingFlag_Tax_Stranger)?>Fremde</td>
								<td align="center" nowrap><?=IFlagCheck($gObject,"flags",kBuildingFlag_Tax_Enemy)?>Feinde</td>
								<td align="center" nowrap>
									&nbsp;&nbsp;
									<input type="checkbox" name="dummy" value="1" <?=(intval($gObject->flags) ^ kBuildingFlag_TaxMask)?"":"checked"?> onChange="setchecks('i_flags[]',this.checked,4,4)">
									alle
								</td>
							</tr>
						<?php }?>
						</table>
					<?php } // endif gateflags?>
					
					<?php if (in_array($gObject->type,$gTaxableBuildingTypes)) { $edited = true;?>
						Zoll pro <?=kPortalTaxUnitNum?> Mann : 
						<?php $i=0; foreach($gRes as $n=>$f) {?>
						<img src="<?=g("res_$f.gif")?>">
						<input type="text" name="tax[]" size=5 value="<?=$gTax?$gTax[$i++]:0?>">
						<?php }?><br>
					<?php } // endif settax?>
					
					<table>
					<?php foreach ($gOwnerBuildingFlags as $flag => $btypes) if (!(intval($flag) & kBuildingFlag_OpenTaxMask) && in_array($gObject->type,$btypes)) {?>
						<tr>
							<td><?=IFlagCheck($gObject,"flags",$flag)?></td>
							<td><?=$gBuildingFlagNames[$flag]?></td>
						</tr>
					<?php $edited = true; }?>
					</table>
					
					<?php if ($edited) {?>
						<INPUT TYPE="submit" VALUE="speichern">
						<input type="checkbox" name="foralloftype" value="1">
							Für alle 
							<img src="<?=g($btype->gfx,($gObject->nwse=="?" || empty($gObject->nwse))?"ns":$gObject->nwse,$lpic)?>" border=1>
							übernehmen
					<?php } // endif?>
					</FORM>	
				<?php } // endif verhalten edit
				
				
				// if ($btype->script && strlen($btype->script) > 0 && file_exists($btype->script.".php")) include($btype->script.".php");
				
			} else if ($this->cancontroll()) {?>
				<?php /* #### BAUSTELLE #### */ ?>
				<?php 
				
				global $gSpeedyBuildingTypes;
				$normalbuildtime = $gBuildingType[$gObject->type]->buildtime;
				$buildtime = GetBuildTime($gObject->x,$gObject->y,$gObject->type,0,$gObject->user);
				$remaining_time = max(0,$gObject->construction - time());
				//echo "$gObject->x,$gObject->y,$gObject->type,0,$gObject->user<br>";
				?>
				<?php PrintBuildTimeHelp($gObject->x,$gObject->y,$gObject->type,0); ?>
				<?php if (!in_array($gObject->type,$gSpeedyBuildingTypes)) {?>
				(Gebäude-Typ ist vom NewbieFaktor ausgeschlossen)<br>
				<?php } // endif?>
				<table>
				<tr><td>normale Bauzeit</td><td><?=($normalbuildtime>0)?Duration2Text($normalbuildtime):"sofort fertig"?></td></tr>
				<tr><td>effektive Bauzeit</td><td><?=($buildtime>0)?Duration2Text($buildtime):"sofort fertig"?></td></tr>
				<tr><td>BauBeginn</td><td><?=date("H:i d.m.Y",$gObject->construction - $buildtime)?></td></tr>
				<tr><td>BauEnde</td><td><?=date("H:i d.m.Y",$gObject->construction)?>
				<?php if ($gUser->admin == 1){ ?>
					<FORM METHOD=POST ACTION="<?=Query("?sid=?&x=?&y=?")?>">
					<INPUT TYPE="hidden" NAME="do" VALUE="completeconstruction">
					<INPUT TYPE="hidden" NAME="id" VALUE="<?=$gObject->id?>">
					<INPUT TYPE="submit" VALUE="fertig">
					</FORM>
				<?php } ?>
				</td></tr>
				<tr><td>Restzeit</td><td><?=($remaining_time>0)?Duration2Text($remaining_time):"fertig, nurnoch aufräumen..."?> (<?=round(100*GetConstructionProgress($gObject))?>%)</td></tr>
				</table>
				<FORM METHOD=POST ACTION="<?=Query("?sid=?&x=?&y=?")?>">
				<INPUT TYPE="hidden" NAME="do" VALUE="removebuilding">
				<INPUT TYPE="hidden" NAME="id" VALUE="<?=$gObject->id?>">
				<INPUT TYPE="submit" VALUE="<?=$gObject->construction?"abbrechen":"abreissen"?>">
				<INPUT TYPE="checkbox" NAME="sure" VALUE="1">sicher !
				</FORM>
				
			<?php }?>
			
			<?php if ($gUser->admin && $gObject->user != $gUser->id && $gObject->user > 0) {?>
				<?php /* #### SYMBIOSE KNOPF #### */ ?>
				<?php $owner = sqlgetobject("SELECT * FROM `user` WHERE `id` = ".$gObject->user);?>
				<FORM METHOD=POST ACTION="<?=Query("../symbiose.php?sid=?")?>" target="_parent">
				<INPUT TYPE="hidden" NAME="uid" VALUE="<?=$gObject->user?>">
				<INPUT TYPE="submit" VALUE="symbiose mit <?=$owner->name?>">
				</FORM>
			<?php }?>
			<?php 
		
		global $gBodenSchatzBuildings;
		if (in_array($gObject->type,$gBodenSchatzBuildings)) {
			// todo : cost-system
			$costarr = array($btype->cost_lumber,$btype->cost_stone,$btype->cost_food,$btype->cost_metal,$btype->cost_runes);
			echo "<b>BODENSCHATZ ! : ".cost2txt($costarr)." pro Stunde bei ".ktrenner(kBodenSchatzIdealWorkers)." Arbeitern</b><br>";
		}
		
		if ($this->construction == 0) $this->mydisplay();
		
		RegisterInfoTab($this->GetMainTabHead(),rob_ob_end(),2);
	}
	
	function generate_tab_technology () {
		foreach ($_REQUEST as $name=>$val) ${"f_".$name} = $val; // TODO : noch notwendig ??
		global $gObject,$gUser,$gBuildingType,$gRes,$gTechnologyType,$gTechnologyGroup;
		if (!$this->cancontroll()) return;
		if ($gObject->construction > 0) return;
		
		$localtechtypes = array(); // the techs available in this building
		foreach ($gTechnologyType as $o) 
			if ($o->buildingtype == $gObject->type) {
				if (!isset(	$localtechtypes[$o->group]))
							$localtechtypes[$o->group] = array();
				$localtechtypes[$o->group][] = $o;
			}
		if (count($localtechtypes) == 0 && !$gUser->admin) return; // no techs for this building
		ksort($localtechtypes);
		
		rob_ob_start();
		
		if($gUser->admin){ ?>
			Techadmin:
			<a href="<?=query("?sid=?&x=?&y=?&building=tech_admin&id=".$gObject->id."&do=newtech&group=0")?>">(+)</a>
			<a href="<?=query("?sid=?&x=?&y=?&building=tech_admin&id=".$gObject->id."&do=newtech&newgroup=1")?>">(+g)</a>
		<?php }
		
		ImgBorderStart("s1","jpg","#ffffee","",32,33);
		?>
		<?php if($gUser->admin){ ?>
			<?php if (0) {?>ACHTUNG, (reset) setzt das forschungslevel ALLER spieler auf 0 zurück !!!<br><?php } ?>
			cheaten : lvl: ein level , max: auf maximum, min: auf 0 , 1h : eine Stunde vor fertigstellung<br>
		<?php } ?>
		<table>
		<tr>
			<th colspan=3>Technologie</th>
			<?php foreach($gRes as $n=>$f)echo '<th><img src="'.g('res_'.$f.'.gif').'"></th>'; ?>
			<th align="center"><img src="<?=g("sanduhrklein.gif")?>"></th>
			<th colspan=3>Jetzt/Max/Upgrades</th>
		</tr>
		<?php foreach ($localtechtypes as $groupid => $arr) {?>
		
			<?php /* Header fuer technologygroup */ ?>
			<?php if ($groupid != 0) { $group = $gTechnologyGroup[$groupid]; ?>
				<tr><td colspan="12" bgcolor="black" height="1"></td></tr>
				<tr>
					<td><?="<img src='".g($group->gfx)."'>"?></td>
					<td></td>
					<td nowrap><?=$group->name?>  
					<?php if($gUser->admin){ ?>
						<a href="<?=query("admintechgroup.php?id=$group->id&sid=?")?>"><img alt="group" title="group" src="<?=g("icon/admin.png")?>" border=0></a>
						<a href="<?=query("?sid=?&x=?&y=?&building=tech_admin&id=".$gObject->id."&do=newtech&group=$group->id")?>">(+)</a>
					<?php } ?></td>
					<?php foreach($gRes as $n=>$f)echo '<th><img src="'.g('res_'.$f.'.gif').'"></th>'; ?>
					<th align="center"><img src="<?=g("sanduhrklein.gif")?>"></th>
					<th colspan=3>Jetzt/Max/Upgrades</th>
				</tr>
			<?php } // endif?>
			
			<?php /* Row fuer technologytype */ ?>
			<?php foreach ($arr as $o) {
				$tech = GetTechnologyObject($o->id);
				$upmod = cTechnology::GetUpgradeMod($tech->type,$tech->level+$tech->upgrades);
				$upbuilding = false;
				if ($tech->upgradebuilding && $tech->upgrades > 0 && $tech->upgradebuilding != $gObject->id) 
					$upbuilding = sqlgetobject("SELECT * FROM `building` WHERE `id` = ".intval($tech->upgradebuilding));
					$detaillink = Query("?sid=?&x=?&y=?&infotechtype=".$o->id);
					$wikilink = cText::Wiki("tech",$o->id);
					global $gSpellType;
					foreach ($gSpellType as $spell) 
						if ($spell->primetech == $o->id) 
							$wikilink = cText::Wiki("spell",$spell->id);
				?>
				<tr>
					<td><?=isset($gTechnologyGroup[$o->group])?("<img src='".g($gTechnologyGroup[$o->group]->gfx)."'>"):""?></td>
					<td><a href="<?=$detaillink?>"><?="<img border=0 src='".g($o->gfx)."'>"?></a></td>
					<td nowrap><?=$wikilink?> 
					<?php if($gUser->admin){ ?>
						<a href="<?=query("admintech.php?id=$o->id&sid=?")?>"><img alt="tech" title="tech" src="<?=g("icon/admin.png")?>" border=0></a>
						<a href="<?=query("?sid=?&x=?&y=?&building=tech_admin&id=".$gObject->id."&do=lvlup&tech=".$o->id)?>">lvl</a>
						<a href="<?=query("?sid=?&x=?&y=?&building=tech_admin&id=".$gObject->id."&do=lvlmax&tech=".$o->id)?>">max</a>
						<a href="<?=query("?sid=?&x=?&y=?&building=tech_admin&id=".$gObject->id."&do=lvlmin&tech=".$o->id)?>">min</a>
						<a href="<?=query("?sid=?&x=?&y=?&building=tech_admin&id=".$gObject->id."&do=lvlhalfup&tech=".$o->id)?>">1h</a>
						<?php if(0) { ?><a href="<?=query("?sid=?&x=?&y=?&building=tech_admin&id=".$gObject->id."&do=reset&tech=".$o->id)?>">(reset)</a><?php } ?>
					<?php } ?>
					<a href="<?=$detaillink?>"> <?=$o->name?></a></td>
					<?php 
						foreach($gRes as $n=>$f) 
							echo "<td align='right'>".(($tech->level == $o->maxlevel)?"":round($upmod*$o->{"basecost_".$f},0))."</td>";
					?>
					<td align='right' nowrap>&nbsp;<?=($tech->level == $o->maxlevel)?"":Duration2Text(cTechnology::GetUpgradeDuration($tech->type,$tech->level+$tech->upgrades))?></td>
					<td align='right'>&nbsp;&nbsp;<?=$tech->level?>/</td>
					<td align='right'><?=$o->maxlevel?>/</td>
					<td align='left' nowrap>
						<?php /* #### PLANBAR #### */?>
						<FORM METHOD=POST ACTION="<?=Query("?sid=?&x=?&y=?")?>">
						<INPUT TYPE="hidden" NAME="building" VALUE="tech_command">
						<INPUT TYPE="hidden" NAME="do" VALUE="plantechupgrades">
						<INPUT TYPE="hidden" NAME="id" VALUE="<?=$gObject->id?>">
						<INPUT TYPE="hidden" NAME="techtype" VALUE="<?=$o->id?>">
						<table>
						<tr>
							<?php if ($tech->level == $o->maxlevel) {?>
								<td colspan=2><font color="green"><b>Maximum</b></font></td>
							<?php } else if ( intval($gObject->level) < intval($o->buildinglevel) ||
										!HasReq($o->req_geb,$o->req_tech,$gObject->user,GetTechnologyLevel($o->id,$gObject->user)+1)) {?>
								<?php /* anforderungen nicht erfuellt */ ?>
								<td colspan=2><a href="<?=$detaillink?>"><font color="red"><b>Anforderungen</b></font></a></td>
							<?php } else if ($upbuilding) {?>
								<?php /* in anderem gebauede geplant */ ?>
								<td colspan=2><a href="<?=Query("info.php?sid=?&x=".$upbuilding->x."&y=".$upbuilding->y)?>">
								<font color="green"><b>geplant</b></font>
								</a></td>
							<?php } else {?>
								<td><INPUT TYPE="text" NAME="upcount" VALUE="<?=$tech->upgrades?>" style="width:20px"></td>
								<td><INPUT TYPE="submit" NAME="plan" VALUE="geplant"></td>
							<?php }?>
							
							<?php if ($tech->level > 0 || $tech->upgradetime > 0) {?>
							<td><INPUT TYPE="submit" NAME="forget" VALUE="<?=($tech->upgradetime > 0)?"abbrechen":"vergessen"?>"></td>
							<?php } // endif?>
						</tr>
						
						<?php /* #### FORSCHUNG LÄUFT #### */?>
						<?php if ($tech->upgradetime > 0 && $tech->upgradebuilding == $gObject->id) {?>
							<?php 
								$max = max(1,cTechnology::GetUpgradeDuration($tech->type,$tech->level));
								$timeleft = max(0,$tech->upgradetime - time());
								$cur = $max - $timeleft;
								$percent = min(100,max(0,floor((100.0*$cur)/$max)));
							?>
							<tr><td height=5 colspan=2 style="border:1px solid black"><?=DrawBar($cur,$max,GradientRYG(GetFraction($cur,$max)),"black")?></td></tr>
							<tr><td colspan=2><?="$percent% ".Duration2Text($timeleft)?></td></tr>
						<?php } // endif ?>
						
						</table>
						</FORM>
					</td>
				</tr>
			<?php } // endforeach?>
		<?php } // endforeach?>
		</table>
		<?php
		ImgBorderEnd("s1","jpg","#ffffee",32,33);
		RegisterInfoTab("Forschung",rob_ob_end());
	}
}
?>
