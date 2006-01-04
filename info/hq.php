<?php
require_once("../lib.main.php");


//recalculates the user red produktion / h
function recalcResProduction($u,$lock=true,$buildings=false) { // u = userobject or id , $buildings : precalced in cron
	global $gGlobal,$gRes;

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
		if ($resfield == "lumber" || $resfield == "stone") $p += 10; // grundprod holz+stein : 10/h
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
		}
	}
	
	
	function mygenerate_tabs() {
		if ($this->construction > 0) return;
		foreach ($_REQUEST as $name=>$val) ${"f_".$name} = $val;
		global $gUser;
		global $gGlobal;
		global $gObject;
		global $gItemType;
		global $gSpellType;
		global $gRes;
		global $gAdjust;
		global $gResTypeNames;
		global $gResTypeVars;
		global $gTechnologyType;
		global $gTechnologyGroup;
		global $gBuildingType;
		
		profile_page_start("hq.php");
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
		?>
		
		<?php if($gObject->user == $gUser->id) {?>
			<?php if ($gUser->food <= 0) {?>
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
				<?php } else if ($concount < 10) {?>
					<font color="#FF4400"><b>nur noch <?=$concount?> Pl&auml;ne !</b></font>
				<?php } else {?>
					noch <?=$concount?> Pl&auml;ne
				<?php }?>
			</td>
			</tr>
			</table>
		
			<?php if (1) {?>
				<!--building quick jump-->
				<table border=0><tr><th>Schnellsprung</th></tr></table>
				<?php 
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
						++$i; 
						if ($i>1) continue; // all only once
						$lpic = ($o->level>=10)?"1":"0";
						$btype = $gBuildingType[$o->type];?>
						<a href="<?=Query("?sid=?&x=".$o->x."&y=".$o->y)?>">
						<img title="<?=strip_tags($btype->name)?>" alt="<?=strip_tags($btype->name)?>" border=1 src="<?=g($btype->gfx,($o->nwse=="?")?"ns":$o->nwse,$lpic)?>"></a>
					<?php }
				}
			} // endif?>
		
			
			<?php
			$hqtabs = array();
			rob_ob_start(); 
			$this->PrintWorker();
			$content = rob_ob_end();
			$hqtabs[] = array("Arbeiter",$content);
			$hqtabs[] = array("Überblick",		"",Query("summary.php?sid=?"));
			$hqtabs[] = array("Waren",			"",Query("waren.php?sid=?"));
			$hqtabs[] = array("Kosten",			"",Query("kosten.php?sid=?"));
			$hqtabs[] = array("Baupl&auml;ne",	"",Query("bauplan.php?sid=?"));
			$hqtabs[] = array("Diplomatie",		"",Query("diplo.php?sid=?"));
			// $hqtabs[] = array("Truppen",		"",Query("summary_units.php?sid=?"));
			// $hqtabs[] = array("Gebäude",		"",Query("summary_buildings.php?sid=?"));
			$hqtabs[] = array("KampfSim",		"",Query("kampfsim.php?sid=?"));
			$hqtabs[] = array("Forschung",		"",Query("tech.php?sid=?"));
			$hqtabs[] = array("Einstell.",		"",Query("profile.php?sid=?"));
			$hqtabs[] = array("Quests",		"",Query("quest.php?sid=?"));
			
			rob_ob_start(); 
			$this->PrintTitles();
			$content = rob_ob_end();
			if (!empty($content)) $hqtabs[] = array("Titel",$content);
			echo GenerateTabs("hqtabs",$hqtabs,"",false);
		} else { // cancontroll?>
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
			
			<?php 
			$profil = sqlgetone("SELECT `profil` FROM `userprofil` WHERE `id`=".$gObject->user);
			$ownername = sqlgetone("SELECT `name` FROM `user` WHERE `id`=".intval($gObject->user));
			?>
			<?php ImgBorderStart("s2","jpg","#ffffee","bg-s2",32,33); ?>
			<p align="center"><a href="<?=query("msg.php?sid=?&show=compose&to=".urlencode($ownername))?>"><span style="font-family:serif;font-size:18px;font-style:italic;"><?=$ownername?></span></a></p>
			<p align="center"><span style="font-family:serif;font-size:11px;">von<br><?=sqlgetone("SELECT g.`name` FROM `guild` g,`user` u WHERE g.id=u.guild AND u.id=".$gObject->user)?></span></p>
			<?php 
			$registered = sqlgetone("SELECT `registered` FROM `user` u WHERE u.id=".$gObject->user);
			if($registered > 0){ ?>
			<p align="center"><span style="font-family:serif;font-size:11px;">dabei seit <?=date("j.n.y",$registered)?></span></p>
			<?php } ?>
			<?=nl2br(htmlspecialchars($profil))?>
			<?php ImgBorderEnd("s2","jpg","#ffffee",32,33); ?>
			
			<?php $this->PrintTitles(); ?>
		<?php } // endif cancontroll?>
		
		<?php 
		
		profile_page_end(); 
		RegisterInfoTab("Verwaltung",rob_ob_end(),10);
	}
	
	function PrintTitles () {
		global $gObject;
		$t = sqlgettable("SELECT * FROM `title` WHERE `user`=".$gObject->user);
		if (!$t || count($t) == 0) return;
		?>
		<!-- titel -->
		<table cellpadding=2 cellspacing=2 border=0>
		<?php foreach($t as $x) { ?>
			<tr><td><img src="<?=g($x->image)?>" border=0></td><th><?=$x->title?></th><td><?=$x->text?></td></tr>
		<?php }?>
		</table>
		<?php
	}
	
	function PrintWorker () {
		global $gObject,$gUser,$gGlobal,$gAdjust;
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
					if (f == "lumber" || f == "stone") prod += 10;
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
		<?php
	}
}?>