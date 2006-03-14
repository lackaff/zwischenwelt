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
			Diplomatie : <?=GetFOFtxt($gUser->id,$gObject->user)?>
			
			<?php if (GetFOF($gUser->id,$gObject->user) == kFOF_Friend) {?>
				<input type="submit" name="delfriend" value="Freundschaft kündigen">
			<?php } else { // ?>
				<input type="submit" name="addfriend" value="Freundschaft schliessen">
			<?php } // endif?>
			
			<?php if (GetFOF($gUser->id,$gObject->user) == kFOF_Enemy) {?>
				<input type="submit" name="delenemy" value="Feindschaft beenden">
			<?php } else { // ?>
				<input type="submit" name="addenemy" value="zum Feind erklären">
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
		RegisterInfoTab("Produktion",$this->PrintWorker());
		RegisterInfoTab("Übersicht",$this->PrintOverview());
		
		$diplohtml = trim($this->PrintDiplo());
		if (!empty($diplohtml)) RegisterInfoTab("Diplomatie",$diplohtml);
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
			<center><span style="font-size:14px;font-weight:bold;">Nachricht des Tages</span><br>
			<?ImgBorderStart();?>
			<span style="font-size:12px;font-style:italic;"><?=sqlgetone("SELECT `message` FROM `guild` WHERE `id`=".$gUser->guild)?></span>
			<?ImgBorderEnd();?></center>
			<br>
			<?
		}
		
		if ($gUser->food <= 0) {?>
			<center><?ImgBorderStart();?>
			<span style="color:red;font-size:12px;font-style:italic;">Ihre Bevölkerung hungert. Sie benötigen mehr Nahrung!</span>
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
				<font color="red"><b>keine Pl&auml;ne mehr !</b></font>
			<?php } else if ($concount == 1) {?>
				<a href="<?=Query("bauplan.php?sid=?")?>"><font color="#FF4400"><b>letzter Bauplan !</b></font></a>
			<?php } else if ($concount < 10) {?>
				<a href="<?=Query("bauplan.php?sid=?")?>"><font color="#FF4400"><b>nur noch <?=$concount?> Pl&auml;ne !</b></font></a>
			<?php } else {?>
				<a href="<?=Query("bauplan.php?sid=?")?>">noch <?=$concount?> Pl&auml;ne</a>
			<?php }?>
		</td>
		</tr>
		</table>
		
		<?php if (1) {
			$first = true;
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
			foreach ($quickbuildingtypes as $typeid) {
				$tquick = sqlgettable("SELECT * FROM `building` WHERE `construction`=0 AND `user` = ".$gUser->id." AND `type` = ".$typeid);	
				$i=0;foreach($tquick as $o){
					if ($first) { $first = false; ?> <table border=0><tr><th>Schnellsprung</th></tr></table> <?php }
					++$i; 
					if ($i>1) continue; // all only once
					$lpic = ($o->level>=10)?"1":"0";
					$btype = $gBuildingType[$o->type];?>
					<a href="<?=Query("?sid=?&x=".$o->x."&y=".$o->y)?>">
					<img title="<?=strip_tags($btype->name)?>" alt="<?=strip_tags($btype->name)?>" border=1 src="<?=g($btype->gfx,($o->nwse=="?")?"ns":$o->nwse,$lpic)?>"></a>
				<?php }
			}
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
		$minbtable[] = array(kBuilding_Lumberjack,4,", produziert <img src=\"".g("res_lumber.gif")."\">");
		$minbtable[] = array(kBuilding_StoneProd,4,", produziert <img src=\"".g("res_stone.gif")."\">");
		$minbtable[] = array(kBuilding_Farm,2,", produziert <img src=\"".g("res_food.gif")."\"> für deine Bevölkerung");
		$minbtable[] = array(kBuilding_Silo,2,", dort werden deine Rohstoffe gelagert");
		$minbtable[] = array(kBuilding_House,4,", damit deine Bevölkerung ".$popicon." wachsen kann");
		
		
		
		$tip = array();
		$allbuildings = sqlgetobject("SELECT 
			MIN(`level`) as `minlevel` ,
			MAX(`level`) as `maxlevel` ,
			COUNT(*) as `count` 
			FROM `building` WHERE `user` = ".$gUser->id." AND `construction` = 0");
		
		
		if (($allbuildings->minlevel < 5 && $allbuildings->count < 20) || $allbuildings->maxlevel < 5 || $debug_show_all_tips) {
			$tip[] = "Um neu anzufangen und den Startplatz selber zu wählen, einfach das ".GetBuildingTypeLink(kBuilding_HQ,$x,$y)." abreissen";
		}
		
		if ($gUser->guild == kGuild_Weltbank || $debug_show_all_tips) {
			$tip[] = "Bis du ".kplaintrenner($gGlobal['wb_max_gp'])." Punkte erreicht hast, bist du in der Weltbank (Menupünkt \"Gilde\")";
			$tip[] = "In der Weltbank kann man sich mit anderen neuen Spielern unterhalten";
			$tip[] = "<b>In der Weltbank kann man sich Rohstoffe ausleihen, bedien dich und wachse !</b>";
			$tip[] = "Erst wenn man ".kplaintrenner($gGlobal['wb_paybacklimit'])." Punkte erreicht hat, werden ".$gGlobal['wb_payback_perc']."% der Produktion abgezogen, bis die Weltbank-Schulden bezahlt sind";
			$tip[] = "Schulden sieht man als negative GildenPunkte in der Mitgliederliste";
			$tip[] = "Wenn deine Lager überlauffen, fliessen die Rohstoffe in die Weltbank, und werden als GildenPunkte angerechnet";
		}
		
		foreach ($minbtable as $o) {
			$cb = CountUserBuildingType($gUser->id,$o[0]);
			if (!$debug_show_all_tips && $cb >= $o[1]) continue;
			$tip[] = "Du solltest noch mind. ".($o[1]-$cb)." ".GetBuildingTypeLink($o[0],$x,$y)." bauen ".$o[2]."<br>";
		}
		
		if (CountUserBuildingType($gUser->id,kBuilding_Wall) < 5 || $debug_show_all_tips) {
			$tip[] = "Du solltest eine ".GetBuildingTypeLink(kBuilding_Garage,$x,$y)." und ein paar ".GetBuildingTypeLink(kBuilding_Wall,$x,$y)." bauen, um dich vor Angriffen zu schützen.";
		}
		
		if (CountUserBuildingType($gUser->id,kBuilding_Baracks) < 1 || 
			CountUserBuildingType($gUser->id,kBuilding_Smith) < 1 || $debug_show_all_tips) {
			$tip[] = "Du solltest eine ".GetBuildingTypeLink(kBuilding_Smith,$x,$y)." und ein paar ".GetBuildingTypeLink(kBuilding_Baracks,$x,$y)." bauen, damit du Soldaten ausbilden kannst";
		}
		
		if (CountUserBuildingType($gUser->id,kBuilding_Path) < 5 || 
			CountUserBuildingType($gUser->id,kBuilding_Gate) < 1 || $debug_show_all_tips) {
			$tip[] = "Du solltest ein paar ".GetBuildingTypeLink(kBuilding_Path,$x,$y)." und 
				".GetBuildingTypeLink(kBuilding_Gate,$x,$y)." bauen, damit sich deine Truppen gut bewegen können";
		}
			
		if (($allbuildings->minlevel < 5 && $allbuildings->count < 20) || $allbuildings->maxlevel < 5 || $debug_show_all_tips) {
			$tip[] = "vergiss nicht, deine Gebäude aufzustufen (upgraden)";
			$tip[] = "eine neue Stufe bringt genausoviel wie ein neues Gebäude";
			$tip[] = "je höher die Stufe, desto teurer das Upgraden";
			$tip[] = "es können beliebig viele Gebäude gleichzeitig aufgesfuft werden";
			$tip[] = "die Stufe von deinem Haupthaus bestimmt, wie hoch die Gebäude aufgestuft werden können";
			$tip[] = "in der <a href=\"".Query("summary_buildings.php?sid=?")."\">Gebäudeübersicht</a> kann man viele Upgrades auf einmal planen";
		}
		
		if (1) {
		
			if (CountUserUnitType($gUser->id,kUnitType_Kamel) < 1)
				$tip[] = "Im ".GetBuildingTypeLink(kBuilding_Market,$x,$y)." kann man ".GetUnitTypeLink(kUnitType_Kamel,$x,$y)." ausbilden";
			
			if (CountUserUnitType($gUser->id,kUnitType_Worker) < 1)
				$tip[] = "Im ".GetBuildingTypeLink(kBuilding_Silo,$x,$y)." kann man ".GetUnitTypeLink(kUnitType_Worker,$x,$y)." ausbilden";
			
			$tip[] = GetUnitTypeLink(kUnitType_Worker,$x,$y)." und Soldaten können ".GetTerrainTypeLink(kTerrain_Forest,$x,$y)."(".cost2txt(array(kHarvestAmount,0,0,0,0)).") und 
				".GetTerrainTypeLink(kTerrain_Rubble,$x,$y)."(".cost2txt(array(0,kHarvestAmount,0,0,0)).") ernten";
			$arr = array();
			$bstypes = array_unique($gFlaggedBuildingTypes[kBuildingTypeFlag_Bodenschatz]);
			foreach ($bstypes as $typeid) $arr[] = GetBuildingTypeLink($typeid,$x,$y,false,false,false,false);
			$tip[] = GetUnitTypeLink(kUnitType_Worker,$x,$y)." können Bodenschätze (".implode(" ",$arr).") abbauen (ein Arbeitertrupp produziert Rohstoffe solange er auf einem Bodenschatz-Feld steht)";
			$tip[] = "Bodenschätze kann man mit der Suche unter der Karte finden, z.b. \"Kristall\"";
			$tip[] = "rechts-oberhalb der Karte gibt es ein paar Knöpfe für unterschiedliche Weltkarten";
			$tip[] = "Zeichen auf den Weltkarten : &nbsp; ".
							"<img src=\"".kGfxServerPath."/minimap_sample_army.png\">:Armee/Monster &nbsp; ".
							"<img src=\"".kGfxServerPath."/minimap_sample_portal.png\">:Portal &nbsp; ".
							"<img src=\"".kGfxServerPath."/minimap_sample_bodenschatz.png\">:Bodenschatz &nbsp; ";
		}
		
		$cannon_count = CountUserUnitType($gUser->id,kUnitType_Kanone);
		if ($cannon_count < 1) {
			$tip[] = "Du solltest noch einen ".GetBuildingTypeLink(kBuilding_Verteidigungsturm,$x,$y).
				" bauen, und darin eine ".GetUnitTypeLink(kUnitType_Kanone,$x,$y).
				" aufstellen, um dich gegen ".GetUnitTypeLink(kUnitType_Ameise,$x,$y)." zu schützen<br>";
		}
		
		if (count($tip) == 0) return;
		
		//ImgBorderStart("s1","jpg","#ffffee","",32,33);
		?>
		<div class="hqtips">
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
		if (!$t || count($t) == 0) return false;
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
		$overviewtabs[] = array("Gebäude",			"",Query("summary_buildings.php?sid=?"));
		$overviewtabs[] = array("Forschung",		"",Query("summary_techs.php?sid=?"));
		$overviewtabs[] = array("Truppen",			"",Query("summary_units.php?sid=?"));
		$overviewtabs[] = array("Zauber",			"",Query("summary.php?sid=?"));
		$overviewtabs[] = array("Waren",			"",Query("waren.php?sid=?"));
		$overviewtabs[] = array("Kosten",			"",Query("kosten.php?sid=?"));
		$overviewtabs[] = array("Baupl&auml;ne",	"",Query("bauplan.php?sid=?"));
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
				<th align="center" colspan="7">Arbeiter</th>
				<th align="center">Auslastung</th>
				<th align="center">Slots</th>
				<th width="10">&nbsp;</th>
				<th align="center">Produktion</th>
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
					<td align=right nowrap><span id="produktion_<?=$resfield?>"><?=round($gUser->{"prod_$resfield"})?> / h</span></td>
				</tr>
			<?php }?>
			<tr>
				<th>Arbeitslos</th>
				<td></td>
				<td></td>
				<td></td>
				<td><input type="text" readonly style="width:32px" align="right" name="arbeitslos" value="<?=round($free,1)?>">%</td>
				<td></td>
				<td></td>
				<td></td>
			</tr>
		</table>
		<input type="submit" value="verteilung speichern">
		</form>
		
		<h4>Ressourcen Verbrauch durch Bewohner pro Stunde:</h4>
		
		<?=kplaintrenner(round($gUser->pop))?> Bewohner verbrauchen <?=kplaintrenner(round(calcFoodNeed($gUser->pop,60*60),1))?> Nahrung / Stunde
		
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
		<h4>Ressourcen Verbrauch durch Truppen pro Stunde:</h4>
		<?=kplaintrenner(round($unitsum))?> Einheiten verbrauchen <?=kplaintrenner(round($eatsum))?> Nahrung / Stunde
		<?php } // endif?>
		
		<?php if ($gUser->worker_runes > 0) {?>
		<h4>Ressourcen Verbrauch durch Runen Produktion pro Stunde:</h4>
		<?$pf = GetProductionFaktoren($gUser->id);
		$rpfs = $pf['runes']/(2+getTechnologyLevel($gUser->id,kTech_EffRunen)*0.2);?>
			<?=isset($gGlobal['lc_prod_runes'])?round($rpfs*$gUser->worker_runes*$gUser->pop/100*$gGlobal['lc_prod_runes']):0?> Holz / 
			<?=isset($gGlobal['fc_prod_runes'])?round($rpfs*$gUser->worker_runes*$gUser->pop/100*$gGlobal['fc_prod_runes']):0?> Nahrung /
			<?=isset($gGlobal['sc_prod_runes'])?round($rpfs*$gUser->worker_runes*$gUser->pop/100*$gGlobal['sc_prod_runes']):0?> Stein /
			<?=isset($gGlobal['mc_prod_runes'])?round($rpfs*$gUser->worker_runes*$gUser->pop/100*$gGlobal['mc_prod_runes']):0?> Metall <br>
		<?php } // endif?>
				
		<?php if ($gUser->worker_repair > 0) {?>
		<h4>Ressourcen Verbrauch durch Reparieren von Gebäuden pro Stunde:</h4>
		<?
		$x = sqlgetobject("SELECT `user`.`id` as `id`, COUNT( * ) as `broken`,`user`.`pop` as `pop`,`user`.`worker_repair` as `worker_repair`
	FROM `user`, `building`, `buildingtype`
	WHERE 
		`building`.`construction`=0 AND `buildingtype`.`id` = `building`.`type` AND `building`.`user` = `user`.`id` AND `user`.`worker_repair`>0 AND `user`.`id`=".($gUser->id)." AND 
		`building`.`hp`<CEIL(`buildingtype`.`maxhp`+`buildingtype`.`maxhp`/100*1.5*`building`.`level`)
	GROUP BY `user`.`id`");

		$worker = $x->pop * $x->worker_repair/100;
		$broken = $x->broken;
		if(empty($broken))$broken = 0;
		$all = $worker*(60*60)/(24*60*60);
		
		if($broken > 0)$plus = $all / $broken;
		else $plus = 0;
		
		$wood = $all * 100;
		$stone = $all * 100;
		?>
		<?=$broken?> beschädigte Gebäude zu reparieren verbraucht <?=round($wood,2)?> Holz und <?=round($stone,2)?> Stein /h.<br>
		Dabei werden bei allen beschädigten Gebäuden jeweils <?=round($plus,2)?> HP /h wiederhergestellt.<br>
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
				<tr><th>Freundschafts-angebote</th></tr>
				<tr><td valign="top">
					<table>
					<tr>
						<th><input type="checkbox" name="dummy" value="1" onChange="setallchecks('sel_friendoffer[]',this.checked)"></th>
						<th>Name</th>
						<th>Pos</th>
						<th>Punkte</th>
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
				<input type="submit" name="accept" value="annehmen">
				<input type="submit" name="reject_friend" value="ablehnen">
			</td>
		<?php } // endif not all empty?>
				
				
		<?php if (count($friends) > 0) {?>
			<td valign="top">
				<table>
				<tr><th>Freunde</th></tr>
				<tr><td valign="top">
					<table>
					<tr>
						<th><input type="checkbox" name="dummy" value="1" onChange="setallchecks('sel_friend[]',this.checked)"></th>
						<th>Name</th>
						<th>Pos</th>
						<th>Punkte</th>
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
				<tr><th>Feinde</th></tr>
				<tr><td valign="top">
					<table>
					<tr>
						<th><input type="checkbox" name="dummy" value="1" onChange="setallchecks('sel_enemy[]',this.checked)"></th>
						<th>Name</th>
						<th>Pos</th>
						<th>Punkte</th>
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
