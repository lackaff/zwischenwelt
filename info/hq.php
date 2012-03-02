<?php
require_once("../lib.main.php");


//recalculates the user red produktion / h
function recalcResProduction($u,$lock=true,$buildings=false) { // u = userobject or id , $buildings : precalced in cron
	global $gGlobal,$gRes,$gGrundproduktion;

	if ($lock)
		sql("LOCK TABLES `phperror` WRITE, `user` WRITE,`terrain` READ, `sqlerror` WRITE,`building` READ,`technology` WRITE");
	
	if (!is_object($u))
		$u = sqlgetobject("SELECT * FROM `user` WHERE `id`=".intval($u));
	
	$prodfaktoren = GetProductionFaktoren($u->id);
	$slots = GetProductionSlots($u->id,$buildings);
	//$m = sqlgetone("SELECT SUM(`level`) AS `level` FROM `building` WHERE `type`=2 AND `construction`=0 AND `user`=".$u->id." GROUP BY `type`");
	
	$sets = array();
	foreach($gRes as $resname=>$resfield) {
		$btype = $gGlobal["building_".$resfield];
		$prod_factor = $prodfaktoren[$resfield];
		$w = $u->pop * $u->{"worker_".$resfield} / 100; // anzahl zugewiesene arbeiter
		$s = $slots[$resfield]; // anzahl slots
		$p = (min($w,$s) + max(($w - $s),0) * $gGlobal["prod_faktor_slotless"]) * ($gGlobal["prod_faktor"]) * $prod_factor; // produktion
		
		// Grundproduktion
		if (isset($gGrundproduktion[$u->race]) && isset($gGrundproduktion[$u->race][$resfield]))
				$p += $gGrundproduktion[$u->race][$resfield];
		
		$sets[] = "`prod_$resfield`=$p";
	}
	sql("UPDATE `user` SET ".implode(" , ",$sets)." WHERE `id`=".$u->id." LIMIT 1");
	
	if ($lock)
		sql("UNLOCK TABLES");
}



$gClassName = "cInfoHQ";
class cInfoHQ extends cInfoBuilding {
	function mycommand () {
		foreach ($_REQUEST as $name=>$val) ${"f_".$name} = $val;
		global $gObject;
		global $gUser;
		global $gRes;
		global $gAdjust;
		global $gRes2ItemType;
		
		if ($gObject->type != kBuilding_HQ) return;
		switch ($f_do) {
			case "change worker":
				if($gObject->user == $gUser->id) {
					$free = 100;
					foreach($gAdjust as $resname=>$resfield) {
						$arbeiter = min($free,max(0,floatval(${"f_arbeiter_".$resfield})));
						$free -= $arbeiter;
						sql("UPDATE `user` SET `worker_$resfield`=($arbeiter) WHERE `id`=".$gUser->id);
					}
					recalcResProduction($gUser->id);
					$gUser = sqlgetobject("SELECT * FROM `user` WHERE `id` = ".$gUser->id);
				}
			break;
			case "hqdiplo":
				if (isset($f_remove_friend)) {
					$f_sel_friend = intarray(isset($f_sel_friend)?$f_sel_friend:false);
					foreach ($f_sel_friend as $uid)	SetFOF($gUser->id,intval($uid),kFOF_Neutral);
				}
				if (isset($f_remove_enemy)) {
					$f_sel_enemy = intarray(isset($f_sel_enemy)?$f_sel_enemy:false);
					foreach ($f_sel_enemy as $uid)	SetFOF($gUser->id,intval($uid),kFOF_Neutral);
				}
				if (isset($f_accept)) {
					$f_sel_friendoffer = intarray(isset($f_sel_friendoffer)?$f_sel_friendoffer:false);
					foreach ($f_sel_friendoffer as $uid) SetFOF($gUser->id,intval($uid),kFOF_Friend);
				}
				if (isset($f_reject_friend)) {
					$f_sel_friendoffer = intarray(isset($f_sel_friendoffer)?$f_sel_friendoffer:false);
					foreach ($f_sel_friendoffer as $uid) 
						if (GetFOF(intval($uid),$gUser->id) == kFOF_Friend) 
							SetFOF(intval($uid),$gUser->id,kFOF_Neutral);
				}
			break;
		}
	}
	
	
	function mydisplay() {
		global $gObject,$gUser;
		global $gRes,$gAdjust,$gGlobal,$gResTypeNames,$gResTypeVars;
		global $gItemType,$gSpellType,$gTechnologyType,$gTechnologyGroup,$gBuildingType;
		?>
		
		<?php if ($gObject->user != $gUser->id) {?>
		<form method="post" action="<?=Query("?sid=?&x=?&y=?")?>">
		<input type="hidden" name="do" value="setfof">
		<input type="hidden" name="other" value="<?=$gObject->user?>">
			Diplomacy : <?=GetFOFtxt($gUser->id,$gObject->user)?>
			
			<?php if (GetFOF($gUser->id,$gObject->user) == kFOF_Friend) {?>
				<input type="submit" name="delfriend" value="Cancel Friendship">
			<?php } else { // ?>
				<input type="submit" name="addfriend" value="Establish Friendship">
			<?php } // endif?>
			
			<?php if (GetFOF($gUser->id,$gObject->user) == kFOF_Enemy) {?>
				<input type="submit" name="delenemy" value="Cancel Hostilities">
			<?php } else { // ?>
				<input type="submit" name="addenemy" value="Declare Enemy">
			<?php } // endif?>
		</form>
		<?php } // endif?>
		
		<?php if ($gObject->user != $gUser->id) {?>
			<?php 
			$profil = sqlgetone("SELECT `profil` FROM `userprofil` WHERE `id`=".$gObject->user);
			$owneruser = sqlgetobject("SELECT * FROM `user` WHERE `id`=".intval($gObject->user));
			$ownername = $owneruser->name;
			?>
			<?php ImgBorderStart("s2","jpg","#ffffee","bg-s2",32,33); ?>
			<p align="center"><a href="<?=query("msg.php?sid=?&show=compose&to=".urlencode($ownername))?>"><span style="font-family:serif;font-size:18px;font-style:italic;"><?=$ownername?></span></a></p>
			<p align="center"><span style="font-family:serif;font-size:11px;">von<br>
			<a href="<?=Query("viewguild.php?sid=?&id=".$owneruser->guild)?>"><?=sqlgetone("SELECT `name` FROM `guild` WHERE `id` = ".intval($owneruser->guild))?></a>
			</span></p>
			<?php 
			$registered = sqlgetone("SELECT `registered` FROM `user` u WHERE u.id=".$gObject->user);
			if($registered > 0){ ?>
			<p align="center"><span style="font-family:serif;font-size:11px;">dabei seit <?=date("j.n.y",$registered)?></span></p>
			<?php } ?>
			<?=nl2br(htmlspecialchars($profil))?>
			<?php ImgBorderEnd("s2","jpg","#ffffee",32,33); ?>
		<?php } else { // ?>
			<?=$this->PrintCenter()?>
		<?php } // endif?>
		
		<?=$this->PrintTitles();?>
		<?php
	}
	
	
	function mygenerate_tabs() {
		global $gObject,$gUser;
		global $gRes,$gAdjust,$gGlobal,$gResTypeNames,$gResTypeVars;
		global $gItemType,$gSpellType,$gTechnologyType,$gTechnologyGroup,$gBuildingType;
		foreach ($_REQUEST as $name=>$val) ${"f_".$name} = $val;
		
		if ($this->construction > 0) return;
		if ($gObject->user != $gUser->id) return;
		
		/*
		profile_page_start("hq.php");
		rob_ob_start();
		if($gObject->user == $gUser->id) {
			$hqtabs = array();
			//$hqtabs[] = array("Zentrum",$this->PrintCenter());
			$hqtabs[] = array("Diplomatie",		"",Query("diplo.php?sid=?"));
			$hqtabs[] = array("Einstellungen",	"",Query("profile.php?sid=?"));
			echo GenerateTabs("hqtabs",$hqtabs);
		} 
		profile_page_end(); 
		*/
		RegisterInfoTab("Production",$this->PrintWorker());
		RegisterInfoTab("Overview",$this->PrintOverview());
		
		$diplohtml = trim($this->PrintDiplo());
		if (!empty($diplohtml)) RegisterInfoTab("Diplomacy",$diplohtml);
		// RegisterInfoTab("Einstellungen",Query("profile.php?sid=?"));
	}
	
	function PrintCenter () {
		foreach ($_REQUEST as $name=>$val) ${"f_".$name} = $val;
		global $gObject,$gUser;
		global $gRes,$gAdjust,$gGlobal,$gResTypeNames,$gResTypeVars;
		global $gItemType,$gSpellType,$gTechnologyType,$gTechnologyGroup,$gBuildingType;
		rob_ob_start(); 
		
		if($gUser->guild>0 && isset($f_fc) && $f_fc==1) {
			?>
			<center><span style="font-size:14px;font-weight:bold;">Message of the Day</span><br>
			<?ImgBorderStart();?>
			<span style="font-size:12px;font-style:italic;"><?=sqlgetone("SELECT `message` FROM `guild` WHERE `id`=".$gUser->guild)?></span>
			<?ImgBorderEnd();?></center>
			<br>
			<?
		}
		
		if ($gUser->food <= 0) {?>
			<center><?ImgBorderStart();?>
			<span style="color:red;font-size:12px;font-style:italic;">Your people are starving. They need more food!</span>
			<?ImgBorderEnd();?></center>
			<br>
		<?php } // endif?>
		
		<table><tr>
		<?php
		$con = sqlgetobject("SELECT * FROM `building` WHERE `construction` > 0 AND `user` = ".$gObject->user." LIMIT 1");
		$cps = sqlgettable("SELECT * FROM `construction` WHERE `user` = ".$gObject->user." ORDER BY `priority` LIMIT 10");
		$concount = sqlgetone("SELECT COUNT(`id`) FROM `construction` WHERE `user` = ".$gObject->user);
		if ($con) {
			$btype = $gBuildingType[$con->type];
			$proz=100*GetConstructionProgress($con);
			?>
			<td>
			<a href="<?=Query("?sid=?&x=".$con->x."&y=".$con->y)?>">
				<table><tr>
					<td valign="center"><a href="<?=Query("?sid=?&x=".$con->x."&y=".$con->y)?>"><img border=1 src="<?=g("construction.png")?>"></a></td>
					<td valign="center">&gt;</td>
					<td valign="center"><img border=1 src="<?=g($btype->gfx,($con->nwse=="?")?"ns":$con->nwse,0,$gUser->race)?>"></td>
				</tr>
				<tr><td align=center><?=round($proz)?>%</td></tr>
				</table>		
			</a>
			</td>
		<?php }
		if (count($cps) > 0) {?>
			<td valign="top">
				<table><tr>
					<?php foreach ($cps as $cp) {$btype = $gBuildingType[$cp->type];?>
					<td valign="center"><a href="<?=Query("?sid=?&x=".$cp->x."&y=".$cp->y)?>"><img border=1 src="<?=g($btype->gfx,"ns",0,$gUser->race)?>"></a></td>
					<?php }?>
				</tr></table>
			</td>
		<?php }?>
		<td>
			<?php if ($concount == 0) {?>
				<font color="red"><b>No more building plans!</b></font>
			<?php } else if ($concount == 1) {?>
				<a href="<?=Query("bauplan.php?sid=?")?>"><font color="#FF4400"><b>Last building planned!</b></font></a>
			<?php } else if ($concount < 10) {?>
				<a href="<?=Query("bauplan.php?sid=?")?>"><font color="#FF4400"><b>Only <?=$concount?> plans remain!</b></font></a>
			<?php } else {?>
				<a href="<?=Query("bauplan.php?sid=?")?>">noch <?=$concount?> Plans</a>
			<?php }?>
		</td>
		</tr>
		</table>
		
		<?php
		if($gUser->buildings_on_fire > 0){
				//infobox if the user has burning buildings
				$o = sqlgetobject("SELECT f.x,f.y FROM `fire` f,`building` b WHERE b.user=".($gUser->id)." AND f.x=b.x AND f.y=b.y LIMIT 1");
				?>
				<div class="info_warning"><?=$gUser->buildings_on_fire?> Geb&auml;ude stehen in Flammen 
				<a href="<?=Query("?sid=?&x=".$o->x."&y=".$o->y)?>" target=info>(<?=$o->x?>,<?=$o->y?>)</a>.</div>
				<?php
		}
		?>
		
		
		<h1>Jump to Building</h1>
		<?php if (1) {
			$first = true;
			/*
			$quickbuildingtypes = array(kBuilding_Market,
										kBuilding_Smith,
										kBuilding_Garage,
										kBuilding_Chessboard,
										kBuilding_MagicTower,
										kBuilding_Temple,
										kBuilding_BROID,
										kBuilding_Hospital,
										kBuilding_Portal,
										kBuilding_Baracks,
										kBuilding_Tavern,
										kBuilding_Harbor,
										kBuilding_Werft);
										*/
			foreach($gBuildingType as $id=>$obj)if($obj->flags & kBuildingTypeFlag_IsInQuickJump){
				$x = sqlgetobject("SELECT `x`,`y`,`level` FROM `building` WHERE `user`=".$gUser->id." AND `type`=".$obj->id." ORDER BY `level` DESC LIMIT 1");
				if(!empty($x)){?>
					<a target=info href="<?=Query("?sid=?&x=".$x->x."&y=".$x->y)?>" title="<?=$obj->name?> (<?=$x->x?>,<?=$x->y?>)">
						<img src="<?=GetBuildingPic($obj,false,$x->level)?>" border="0" title="<?=$obj->name?> (<?=$x->x?>,<?=$x->y?>)">
					</a>
				<?php }
			}
			/*
			foreach ($gBuildingType as $typeid=>$type) {
				if($type->flags & kBuildingTypeFlag_IsInQuickJump == 0)continue;
				//$tquick = sqlgettable("SELECT * FROM `building` WHERE `construction`=0 AND `user` = ".$gUser->id." AND `type` = ".$typeid);	
				$i=0;
				if ($first) { $first = false; ?> <table border=0><tr><th>Schnellsprung</th></tr></table> <?php }
				++$i; 
				if ($i>1) continue; // all only once
				$lpic = ($o->level>=10)?"1":"0";
				$btype = $gBuildingType[$o->type];?>
				<a href="<?=Query("?sid=?&x=".$o->x."&y=".$o->y)?>">
				<img title="<?=strip_tags($btype->name)?>" alt="<?=strip_tags($btype->name)?>" border=1 src="<?=g($btype->gfx,($o->nwse=="?")?"ns":$o->nwse,$lpic)?>"></a>
				<?php 
			}
			*/
		} // endif
		?>
		
		
		<?php if((intval($gUser->flags) & kUserFlags_DontShowNoobTip)==0)$this->PrintTips(); ?>
		
		<?php
		return rob_ob_end();
	}
	
	// for noobs, you should build xx..
	function PrintTips () {
		global $gUser,$gBuildingType,$gGlobal;
		global $gFlaggedBuildingTypes;
		$x = $this->x;
		$y = $this->y;
		
		$debug_show_all_tips = false;
		$minbtable = array();
		$popicon = "<img src=\"".g("pop-r%R%.png","","",$gUser->race)."\">";
		$minbtable[] = array(kBuilding_Lumberjack,4,", (produces <img src=\"".g("res_lumber.gif")."\">)");
		$minbtable[] = array(kBuilding_StoneProd,4,", (produces <img src=\"".g("res_stone.gif")."\">)");
		$minbtable[] = array(kBuilding_Farm,2,", (produces <img src=\"".g("res_food.gif")."\">)");
		$minbtable[] = array(kBuilding_Silo,2,", (provides storage of resources)");
		$minbtable[] = array(kBuilding_House,4,", (allows your population ".$popicon." to grow)");
		
		
		
		$tip = array();
		$allbuildings = sqlgetobject("SELECT 
			MIN(`level`) as `minlevel` ,
			MAX(`level`) as `maxlevel` ,
			COUNT(*) as `count` 
			FROM `building` WHERE `user` = ".$gUser->id." AND `construction` = 0");
		
		
		if (($allbuildings->minlevel < 5 && $allbuildings->count < 20) || $allbuildings->maxlevel < 5 || $debug_show_all_tips) {
			$tip[] = "To start anew and choose a new location, simply demolish your ".GetBuildingTypeLink(kBuilding_HQ,$x,$y);
		}
		
		if ($gUser->guild == kGuild_Weltbank || $debug_show_all_tips) {
			$tip[] = "Until you reach ".kplaintrenner($gGlobal['wb_max_gp'])." points, you are a member of the Weltbank Guild";
			$tip[] = "You can chat with other players in the Weltbank";
			$tip[] = "<b>You can borrow resources from the Weltbank to aid your growth.</b>";
			$tip[] = "When you reach ".kplaintrenner($gGlobal['wb_paybacklimit'])." points, ".$gGlobal['wb_payback_perc']."% of your production will be deducted until you have paid off your debts to the Weltbank.";
			$tip[] = "Your debts will appear as negative points in the guild member list.";
			$tip[] = "When your storehouses overflow, the excess resources will go to the Weltbank and reduce your debts.";
		}
		
		$tip[] = "A new buildings must be built within two fields of your existing buildings.<br>";
		
		foreach ($minbtable as $o) {
			$cb = CountUserBuildingType($gUser->id,$o[0]);
			if (!$debug_show_all_tips && $cb >= $o[1]) continue;
			$tip[] = "You should build at least ".($o[1]-$cb)." more ".GetBuildingTypeLink($o[0],$x,$y)." ".$o[2]."<br>";
		}
		
		if (CountUserBuildingType($gUser->id,kBuilding_Wall) < 5 || $debug_show_all_tips) {
			$tip[] = "You should build ".GetBuildingTypeLink(kBuilding_Garage,$x,$y)." and a few ".GetBuildingTypeLink(kBuilding_Wall,$x,$y).", to defend yourself from attack.";
		}
		
		if (CountUserBuildingType($gUser->id,kBuilding_Baracks) < 1 || 
			CountUserBuildingType($gUser->id,kBuilding_Smith) < 1 || $debug_show_all_tips) {
			$tip[] = "You should build ".GetBuildingTypeLink(kBuilding_Smith,$x,$y)." and a few ".GetBuildingTypeLink(kBuilding_Baracks,$x,$y)." so you can train an army.";
		}
		
		if (CountUserBuildingType($gUser->id,kBuilding_Path) < 5 || 
			CountUserBuildingType($gUser->id,kBuilding_Gate) < 1 || $debug_show_all_tips) {
			$tip[] = "You should build ".GetBuildingTypeLink(kBuilding_Path,$x,$y)." and 
				".GetBuildingTypeLink(kBuilding_Gate,$x,$y)." so your troops can move more easily.";
		}
			
		if (($allbuildings->minlevel < 5 && $allbuildings->count < 20) || $allbuildings->maxlevel < 5 || $debug_show_all_tips) {
			$tip[] = "don't forget to upgrade your buuildings";
			$tip[] = "a new level is just as beneficial as a new building";
			$tip[] = "the higher the leval, the more expensive upgrades become";
			$tip[] = "many buildings can be upgraded simultaneously";
			$tip[] = "the level of your Town Center determines the maximum level of other buildings";
			$tip[] = "several upgrades at once can be planned in the <a href=\"".Query("summary_buildings.php?sid=?");
		}
		
		if (1) {
		
			if (CountUserUnitType($gUser->id,kUnitType_Kamel) < 1)
				$tip[] = "In ".GetBuildingTypeLink(kBuilding_Market,$x,$y)." can one ".GetUnitTypeLink(kUnitType_Kamel,$x,$y)." train";
			
			if (CountUserUnitType($gUser->id,kUnitType_Worker) < 1)
				$tip[] = "In ".GetBuildingTypeLink(kBuilding_Silo,$x,$y)." can one ".GetUnitTypeLink(kUnitType_Worker,$x,$y)." train";
			
			$tip[] = GetUnitTypeLink(kUnitType_Worker,$x,$y)." and soldiers can harvest ".GetTerrainTypeLink(kTerrain_Forest,$x,$y)."(".cost2txt(array(kHarvestAmount,0,0,0,0)).") and 
				".GetTerrainTypeLink(kTerrain_Rubble,$x,$y)."(".cost2txt(array(0,kHarvestAmount,0,0,0)).")";
			$arr = array();
			$bstypes = array_unique($gFlaggedBuildingTypes[kBuildingTypeFlag_Bodenschatz]);
			foreach ($bstypes as $typeid) $arr[] = GetBuildingTypeLink($typeid,$x,$y,false,false,false,false);
			$tip[] = GetUnitTypeLink(kUnitType_Worker,$x,$y)." can gather resources (".implode(" ",$arr).") from the map";
			$tip[] = "Resources can be found by searching the map, e.g. \"Kristall\"";
			$tip[] = "The buttons above the map provide different views of the world";
			$tip[] = "Symbols on the world map : &nbsp; ".
							"<img src=\"".kGfxServerPath."/minimap_sample_army.png\">:Armies/Monsters &nbsp; ".
							"<img src=\"".kGfxServerPath."/minimap_sample_portal.png\">:Portals &nbsp; ".
							"<img src=\"".kGfxServerPath."/minimap_sample_bodenschatz.png\">:Resources &nbsp; ";
		}
		
		$cannon_count = CountUserUnitType($gUser->id,kUnitType_Kanone);
		if ($cannon_count < 1) {
			$tip[] = "You should build a  ".GetBuildingTypeLink(kBuilding_Verteidigungsturm,$x,$y).
				", which can be garrisoned with ".GetUnitTypeLink(kUnitType_Kanone,$x,$y).
				" to defend against ".GetUnitTypeLink(kUnitType_Ameise,$x,$y)."<br>";
		}
		
		if (count($tip) == 0) return;
		
		//ImgBorderStart("s1","jpg","#ffffee","",32,33);
		?>
		<div class="hqtips">
		<h1>Tips</h1>
		<ul>
		<?php foreach ($tip as $o) {?>
		<li><?=$o?></li>
		<?php } // endforeach?>
		</ul></div>
		<?php
		//ImgBorderEnd("s1","jpg","ffffee",32,33);
	}
	
	// auszeichnungen, orden, ...
	function PrintTitles () {
		global $gObject;
		$t = sqlgettable("SELECT * FROM `title` WHERE `user`=".$gObject->user);
		if (empty($t) || count($t) == 0) return false;
		rob_ob_start(); 
		?>
		<!-- titel -->
		<table cellpadding=2 cellspacing=2 border=0>
		<?php foreach($t as $x) { ?>
			<tr><td><img src="<?=g($x->image)?>" border=0></td><th><?=$x->title?></th><td><?=$x->text?></td></tr>
		<?php }?>
		</table>
		<?php
		return rob_ob_end();
	}
	
	function PrintOverview () {
		global $gUser;
		rob_ob_start(); 
		$overviewtabs = array();
		$overviewtabs[] = array("Buildings",			"",Query("summary_buildings.php?sid=?"));
		$overviewtabs[] = array("Research",		"",Query("summary_techs.php?sid=?"));
		$overviewtabs[] = array("Armies",			"",Query("summary_units.php?sid=?"));
		$overviewtabs[] = array("Magic",			"",Query("summary.php?sid=?"));
		$overviewtabs[] = array("Trade Goods",			"",Query("waren.php?sid=?"));
		$overviewtabs[] = array("Expenses",			"",Query("kosten.php?sid=?"));
		$overviewtabs[] = array("Building Plans",	"",Query("bauplan.php?sid=?"));
		$overviewtabs[] = array("Quests",			"",Query("quest.php?sid=?"));
		// echo GenerateTabs("overviewtabs",$overviewtabs,"",false);
		?>
		<div class="hqoverview"><ul>
		<?php foreach ($overviewtabs as $o) {?>
		<li><a href="<?=$o[2]?>"><?=$o[0]?></a></li>
		<?php } // endforeach?>
		</ul></div>
		<?php
		return rob_ob_end();
	}
	
	function PrintWorker () {
		global $gObject,$gUser,$gGlobal,$gAdjust,$gRes,$gGrundproduktion;
		rob_ob_start(); 
		?>
		
		<!--arbeiterverteilung-->
		<form action="<?=Query("?sid=?&x=?&y=?")?>" method="post">
		<input type="hidden" name="building" value="hq">
		<input type="hidden" name="do" value="change worker">
		<input type="hidden" name="id" value="<?=$gObject->id?>">
		<table border=0>
			<tr>
				<th align="left">Job</th>
				<th align="center" colspan="7">Workers</th>
				<th align="center">Utilization</th>
				<th align="center">Slots</th>
				<th width="10">&nbsp;</th>
				<th align="center">Production</th>
			</tr>
					
			<?php 
			$prodfaktoren = GetProductionFaktoren($gUser->id);
			$slots = GetProductionSlots($gUser->id);
			//print_r($slots);
			?>
					
			<SCRIPT LANGUAGE="JavaScript">
			<!--
				totalworker = <?=$gUser->pop?>;
				slotlessfaktor = <?=$gGlobal["prod_faktor_slotless"]?>;
				generalprodfakt = <?=$gGlobal["prod_faktor"]?>;
				prodfaktoren = new Array();
				slots = new Array();
				<?php foreach($gAdjust as $resname=>$resfield) {
					if(!isset($prodfaktoren[$resfield]))$prodfaktoren[$resfield] = 0;
					if(!isset($slots[$resfield]))$slots[$resfield] = 0;
				?>
					prodfaktoren['<?=$resfield?>'] = <?=$prodfaktoren[$resfield]?>;
					slots['<?=$resfield?>'] = <?=$slots[$resfield]?>;
				<?php }?>
				
				function verteil (f,add) {
					if (add > 0) 
						add = Math.min(parseFloat(document.getElementsByName("arbeitslos")[0].value,10),add);
					
					var curval = parseFloat(document.getElementsByName("arbeiter_"+f)[0].value,10);
					curval = Math.min(100,Math.max(0,curval+add));
					document.getElementsByName("arbeiter_"+f)[0].value = Math.round(curval*10)/10;
					var auslastung = (slots[f]>0)?Math.round(curval * totalworker / slots[f]):0;
					var w = (totalworker * curval / 100.0);
					var prod = (Math.min(w,slots[f]) + Math.max((w - slots[f]),0) * slotlessfaktor) * generalprodfakt * prodfaktoren[f];
					<?php 
					if (isset($gGrundproduktion[$gUser->race])) {
						foreach ($gRes as $n=>$f) if (isset($gGrundproduktion[$gUser->race][$f])) {
							echo "if (f == \"".$f."\") prod += ".$gGrundproduktion[$gUser->race][$f].";\n";
						}
					}
					?>
					document.getElementById("auslastung_"+f).innerHTML = auslastung+"%";
					document.getElementById("auslastung_"+f).style.color = (auslastung > 100)?"red":"green";
					document.getElementById("produktion_"+f).innerHTML = Math.round(prod) + " / h";
					recalcfrei();
				}
				function recalcfrei () {
					var total = 0;
					<?php foreach($gAdjust as $n=>$f) {?>
					total += Math.min(100,Math.max(0,parseFloat(document.getElementsByName("arbeiter_<?=$f?>")[0].value,10)));
					<?php }?>
					document.getElementsByName("arbeitslos")[0].value = Math.round((100-total)*10)/10;
					
				}
				
			//-->
			</SCRIPT>
	
			
			<?php $free = 100; foreach($gAdjust as $n=>$resfield) { 
				$free -= $gUser->{"worker_$resfield"};  // arbeitslose
				$w = $gUser->pop * $gUser->{"worker_".$resfield} / 100; // anzahl arbeiter
				if($slots[$resfield]>0)$auslastung = round(100.0 * $w / $slots[$resfield]);
				//else $auslastung=round(100.0*$w);
				else $auslastung=0;
				?>
				<tr> 
					<th align="left"><?=$n?></th>
					<td nowrap>-<input type="button" value="5" onClick="verteil('<?=$resfield?>',-5);"></td>
					<td><input type="button" value="1" onClick="verteil('<?=$resfield?>',-1);"></td>
					<td><input type="button" value=".1" onClick="verteil('<?=$resfield?>',-0.1);"></td>
					<td nowrap><input type="text" readonly style="width:32px" align="right" name="arbeiter_<?=$resfield?>" value="<?=$gUser->{"worker_$resfield"}?>">%</td>
					<td nowrap>+<input type="button" value=".1" onClick="verteil('<?=$resfield?>',0.1);"></td>
					<td><input type="button" value="1" onClick="verteil('<?=$resfield?>',1);"></td>
					<td><input type="button" value="5" onClick="verteil('<?=$resfield?>',5);"></td>
					<td align=right><span id="auslastung_<?=$resfield?>" style="color:<?=(($auslastung>100)?"red":"green")?>"><?=$auslastung?>%</span></td>
					<td align=right><span id="slots_<?=$resfield?>"><?=round($slots[$resfield])?></span></td>
					<td></td>
					<td align=right nowrap><span id="produktion_<?=$resfield?>">
						<?=(isset($gUser->{"prod_$resfield"}))?round($gUser->{"prod_$resfield"})." / h":""?>
					</span></td>
				</tr>
			<?php }?>
			<tr>
				<th>Unemployed</th>
				<td></td>
				<td></td>
				<td></td>
				<td><input type="text" readonly style="width:32px" align="right" name="arbeitslos" value="<?=round($free,1)?>">%</td>
				<td></td>
				<td></td>
				<td></td>
			</tr>
		</table>
		<input type="submit" value="Save Changes">
		</form>
		
		<h4>Resource use by peasants, per hour:</h4>
		
		<?=kplaintrenner(round($gUser->pop))?> peasants use <?=kplaintrenner(round(calcFoodNeed($gUser->pop,60*60),1))?> food per hour.
		
		<?php
		$allarmies = sqlgettable("SELECT * FROM `army` WHERE `user` = ".$gUser->id);
		$eatsum = 0;
		$unitsum = 0;
		foreach ($allarmies as $army) {
			$units = cUnit::GetUnits($army->id);
			$unitsum += cUnit::GetUnitsSum($units);
			$eatsum += cUnit::GetUnitsEatSum($units);
		}
		?>
		<?php if ($eatsum > 0) {?>
		<h4>Resource use by military units, per hour:</h4>
		<?=kplaintrenner(round($unitsum))?> units use <?=kplaintrenner(round($eatsum))?> food per hour.
		<?php } // endif?>
		
		<?php if ($gUser->worker_runes > 0) {?>
		<h4>Resource use through rune production, per hour:</h4>
		<?$pf = GetProductionFaktoren($gUser->id);
		$rpfs = $pf['runes']/(2+getTechnologyLevel($gUser->id,kTech_EffRunen)*0.2);?>
			<?=isset($gGlobal['lc_prod_runes'])?round($rpfs*$gUser->worker_runes*$gUser->pop/100*$gGlobal['lc_prod_runes']):0?> lumber / 
			<?=isset($gGlobal['fc_prod_runes'])?round($rpfs*$gUser->worker_runes*$gUser->pop/100*$gGlobal['fc_prod_runes']):0?> food /
			<?=isset($gGlobal['sc_prod_runes'])?round($rpfs*$gUser->worker_runes*$gUser->pop/100*$gGlobal['sc_prod_runes']):0?> stone /
			<?=isset($gGlobal['mc_prod_runes'])?round($rpfs*$gUser->worker_runes*$gUser->pop/100*$gGlobal['mc_prod_runes']):0?> metal <br>
		<?php } // endif?>
				
		<?php if ($gUser->worker_repair > 0) {?>
		<h4>Resource use for building repair, per hour:</h4>
		<?
		$x = sqlgetobject("SELECT `user`.`id` as `id`, COUNT( * ) as `broken`,`user`.`pop` as `pop`,`user`.`worker_repair` as `worker_repair`
	FROM `user`, `building`, `buildingtype`
	WHERE 
		`building`.`construction`=0 AND `buildingtype`.`id` = `building`.`type` AND `building`.`user` = `user`.`id` AND `user`.`worker_repair`>0 AND `user`.`id`=".($gUser->id)." AND 
		`building`.`hp`<CEIL(`buildingtype`.`maxhp`+`buildingtype`.`maxhp`/100*1.5*`building`.`level`)
	GROUP BY `user`.`id`");

		if($x){
			$worker = $x->pop * $x->worker_repair/100;
			$broken = $x->broken;
			if(empty($broken))$broken = 0;
			if($broken > 0)$plus = $all / $broken;
		} else {
			$worker = 0;
			$broken = 0;
			$plus = 0;
		}
		$all = $worker*(60*60)/(24*60*60);
		$wood = $all * 100;
		$stone = $all * 100;
		?>
		<?=$broken?> damaged buildings need <?=round($wood,2)?> lumber and <?=round($stone,2)?> stone for repair /h.<br>
		Damaged buildings will regain <?=round($plus,2)?> HP per hour.<br>
		<?php } // endif?>
		
		<?php
		return rob_ob_end();
	}
	
	function PrintDiplo () {
		global $gObject,$gUser,$gGlobal;
		
		$friends = sqlgettable("SELECT `user`.*,`user`.`general_pts`+`user`.`army_pts` as `pts` FROM `fof_user`,`user` 
			WHERE `class` = ".kFOF_Friend." AND `master` = ".$gUser->id." AND `other` = `user`.id ORDER BY `pts` DESC");
		$enemies = sqlgettable("SELECT `user`.*,`user`.`general_pts`+`user`.`army_pts` as `pts` FROM `fof_user`,`user` 
			WHERE `class` = ".kFOF_Enemy." AND `master` = ".$gUser->id." AND `other` = `user`.id ORDER BY `pts` DESC");
		$friend_offers = sqlgettable("SELECT `user`.*,`user`.`general_pts`+`user`.`army_pts` as `pts` FROM `fof_user`,`user` 
			WHERE `class` = ".kFOF_Friend." AND `other` = ".$gUser->id." AND `master` = `user`.id ORDER BY `pts` DESC");
		$friendids = intarray(AF($friends,"id"));
		
		if (count($friends) == 0 && count($enemies) == 0 && count($friend_offers) == 0) return "";
		

		rob_ob_start();
		?>
		
		<form method="post" action="<?=Query("?sid=?&x=?&y=?")?>">
		<input type="hidden" name="building" value="hq">
		<input type="hidden" name="do" value="hqdiplo">
		<input type="hidden" name="id" value="<?=$gObject->id?>">
			
		<table><tr>
		
		<?php $count = 0; foreach ($friend_offers as $o) if (!in_array($o->id,$friendids)) $count++; ?>
		<?php if ($count > 0) {?>
			<td valign="top">
				<table>
				<tr><th>Friendship offers</th></tr>
				<tr><td valign="top">
					<table>
					<tr>
						<th><input type="checkbox" name="dummy" value="1" onChange="setallchecks('sel_friendoffer[]',this.checked)"></th>
						<th>Name</th>
						<th>Pos</th>
						<th>Points</th>
					</tr>
					<?php foreach ($friend_offers as $o) if (!in_array($o->id,$friendids)) {?>
						<tr>
							<td><input type="checkbox" name="sel_friendoffer[]" value="<?=$o->id?>"></td>
							<td><?=usermsglink($o)?></td>
							<td><?=opos2txt(sqlgetobject("SELECT * FROM `building` WHERE `type` = ".kBuilding_HQ." AND `user` = ".$o->id))?></td>
							<td align="right"><?=kplaintrenner($o->general_pts+$o->army_pts)?></td>
						</tr>
					<?php } // endforeach?>
					</table>
				</td></tr>
				</table>
				<input type="submit" name="accept" value="Accept">
				<input type="submit" name="reject_friend" value="Decline">
			</td>
		<?php } // endif not all empty?>
				
				
		<?php if (count($friends) > 0) {?>
			<td valign="top">
				<table>
				<tr><th>Friends</th></tr>
				<tr><td valign="top">
					<table>
					<tr>
						<th><input type="checkbox" name="dummy" value="1" onChange="setallchecks('sel_friend[]',this.checked)"></th>
						<th>Name</th>
						<th>Pos</th>
						<th>Points</th>
					</tr>
					<?php foreach ($friends as $o) {?>
						<tr>
							<td><input type="checkbox" name="sel_friend[]" value="<?=$o->id?>"></td>
							<td><?=usermsglink($o)?><?=GetFOF($o->id,$gUser->id)==kFOF_Friend?"":"<font color='#0088FF'>(einseitig)</font>"?></td>
							<td><?=opos2txt(sqlgetobject("SELECT * FROM `building` WHERE `type` = ".kBuilding_HQ." AND `user` = ".$o->id))?></td>
							<td align="right"><?=kplaintrenner($o->general_pts+$o->army_pts)?></td>
						</tr>
					<?php } // endforeach?>
					</table>
				</td></tr>
				</table>
				<input type="submit" name="remove_friend" value="entfernen">
			</td>
		<?php }?>
		
		<?php if (count($enemies) > 0) {?>
			<td valign="top">
				<table>
				<tr><th>Enemies</th></tr>
				<tr><td valign="top">
					<table>
					<tr>
						<th><input type="checkbox" name="dummy" value="1" onChange="setallchecks('sel_enemy[]',this.checked)"></th>
						<th>Name</th>
						<th>Pos</th>
						<th>Points</th>
					</tr>
					<?php foreach ($enemies as $o) {?>
						<tr>
							<td><input type="checkbox" name="sel_enemy[]" value="<?=$o->id?>"></td>
							<td><?=usermsglink($o)?></td>
							<td><?=opos2txt(sqlgetobject("SELECT * FROM `building` WHERE `type` = ".kBuilding_HQ." AND `user` = ".$o->id))?></td>
							<td align="right"><?=kplaintrenner($o->general_pts+$o->army_pts)?></td>
						</tr>
					<?php } // endforeach?>
					</table>
				</td></tr>
				</table>
				<input type="submit" name="remove_enemy" value="entfernen">
			</td>
		<?php }?>
		
		</tr></table>
		</form>
		
		<?php
		return rob_ob_end();
	}
}?>
