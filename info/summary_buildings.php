<?php
require_once("../lib.main.php");
require_once("../lib.building.php");
require_once("../lib.army.php");
require_once("../lib.map.php");
require_once("../lib.spells.php");
Lock();
profile_page_start("summary_buildings.php");

if (isset($f_bup)) {
	$upgroups = sqlgettable("SELECT `building`,COUNT(`id`) as c , MAX(`time`) as mtime FROM `oldupgrade` GROUP BY `building`");
	//vardump2($upgroups);
	foreach ($upgroups as $o) {
		sql("UPDATE `building` SET `upgrades` = ".intval($o->c)." , `upgradetime` = ".intval($o->mtime)." WHERE `id` = ".intval($o->building));
		sql("DELETE FROM `oldupgrade` WHERE `building` = ".intval($o->building));
	}
}


if(isset($f_upgrades)) {
	foreach ($f_upgrades as $id => $up) {
		$building = sqlgetobject("SELECT * FROM `building` WHERE `id` = ".intval($id));
		if ($building && $building->user == $gUser->id)
			cBuilding::SetBuildingUpgrades($id,$up);
	}

	// close the tab
	unset($f_open);
}

if (isset($f_typeup_toall)) {
	foreach ($f_typeup as $key => $val) {
		sql("UPDATE `building` SET `upgrades` = GREATEST(IF(`upgradetime`=0,0,1),".intval($val)." - `level`) WHERE `construction` = 0 AND `user` = ".$gUser->id." AND `type` = ".intval($key));
	}
}
if (isset($f_typeup_to)) {
	foreach ($f_typeup_to as $key => $igno) { $val = $f_typeup[$key];
		sql("UPDATE `building` SET `upgrades` = GREATEST(IF(`upgradetime`=0,0,1),".intval($val)." - `level`) WHERE `construction` = 0 AND `user` = ".$gUser->id." AND `type` = ".intval($key));
	}
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 transitional//EN"
   "http://www.w3.org/TR/html4/transitional.dtd">
<html>
<head>
<link rel="stylesheet" type="text/css" href="../styles.css">
<title>Zwischenwelt - Übersicht</title>
</head>
<body>
<?php include("../menu.php"); ?>


<?php
	// spell testing
	$spelltestimg = "<img border=0 src='".g("icon/admin.png")."' alt='spelltest' title='spelltest'>";
	if ($gUser->admin && isset($f_spelltest)) {
		ImgBorderStart("s1","jpg","#ffffee","",32,33);
		$o = sqlgetobject("SELECT * FROM `spell` WHERE `id` = ".intval($f_spelltest));
		echo "<h3>Spelltest : ".$f_spelltestrepeat." Minuten , ".$gSpellType[$o->type]->name." [$o->id] </h3>";
		vardump2($o);
		for ($i=0;$i<intval($f_spelltestrepeat);++$i) {
			$o = sqlgetobject("SELECT * FROM `spell` WHERE `id` = ".intval($f_spelltest));
			if ($o) {
				$spell = GetSpellInstance($o->type,$o);
				$spell->Cron(60);
			}
		}
		ImgBorderEnd("s1","jpg","#ffffee",32,33);
	}
	if ($gUser->admin && isset($f_spellexpire)) {
		ImgBorderStart("s1","jpg","#ffffee","",32,33);
		$o = sqlgetobject("SELECT * FROM `spell` WHERE `id` = ".intval($f_spellexpire));
		echo "<h3>Spell-Expire , ".$gSpellType[$o->type]->name." [$o->id] </h3>";
		$spell = GetSpellInstance($o->type,$o);
		$spell->Expire();
		ImgBorderEnd("s1","jpg","#ffffee",32,33);
	}
	
	$myspells = sqlgettable("SELECT * FROM `spell` WHERE `owner`=".$gUser->id." ORDER BY `target`,`type`");
	$onme = sqlgettable("SELECT * FROM `spell` WHERE `target`=".$gUser->id." ORDER BY `type`");
?>

<a href="<?=Query("waren.php?sid=?")?>"><b><u>Waren</u></b></a> /
<a href="<?=Query("kosten.php?sid=?")?>"><b><u>Kostenübersicht</u></b></a> /
<a href="<?=Query("bauplan.php?sid=?")?>"><b><u>Baupl&auml;ne</u></b></a> /
<a href="<?=Query("diplo.php?sid=?")?>"><b><u>Diplomatie</u></b></a> /
<a href="<?=Query("summary_units.php?sid=?")?>"><b><u>Einheiten</u></b></a> /
<a href="<?=Query("summary_buildings.php?sid=?")?>"><b><u>Gebäude</u></b></a>
<br>
<br>
	
	<?php
	$type = $gBuildingType;
	$plannedupgradessum = 0;
	$hqlevel = sqlgetone("SELECT `level` FROM `building` WHERE `user`=".$gUser->id." AND `type`=".kBuilding_HQ." LIMIT 1");
	$tt = $gBuildingType;
	if (isset($f_open)) 
			$open = explode(",",$f_open);
	else	$open = array();
	
	$js_ids = array();$js_ids[] = 0;
	$js_levels = array();$js_levels[] = 0;
	?>
	<h4>Gebäude</h4>
	<a href="<?=Query("?sid=?&open=".implode(",",AF($gBuildingType,"id")))?>">Alle aufklappen</a>
	<form action='?sid=<?=$_REQUEST["sid"]?>' method='post'>
	<?php if (isset($f_open)) {?>
	<input type="hidden" name="open" value="<?=$f_open?>">
	<?php } // endif?>
	<table border='0' id='summary'>
	<tr>
		<th>Gebäude</th>
		<th>Level MIN/MAX <?=cText::Wiki("summary_minmax")?></th>
		<th>Upgr. <?=cText::Wiki("summary_upgr")?></th>
		<?php if (count($open) == 0) {?>
			<th>bis Level upgraden <?=cText::Wiki("summary_bislevel")?></th>
		<?php } else { // ?>
			<th>Restzeit</th>
		<?php } // endif?>
	</tr>
		
		<?php 
		$js_types = array();
		foreach($tt as $typ)
		{
			if(!sqlgetone("SELECT `id` FROM `building` WHERE `type`=".$typ->id." AND `user`=".$gUser->id." LIMIT 1"))continue;
			$stats = sqlgetobject("SELECT COUNT(`id`) as bcount,MIN(`level`) as minlevel,MAX(`level`) as maxlevel,SUM(`upgrades`) as totalups FROM `building` WHERE `construction`=0 AND `type`=".$typ->id." AND `user`=".$gUser->id);
			$plannedupgradessum += $stats->totalups;
			
			if($stats->maxlevel<10)
				$lpic="0";
			else
				$lpic="1";
			?>
			<tr>
				<th><a href="<?=query("?sid=?".(in_array($typ->id,$open)?"":"&open=".$typ->id))?>">
					<img align="middle" src="<?=g($typ->gfx,"ns",$lpic,$gUser->race)?>" border=1></a>
					<?=cText::Wiki("building",$typ->id)?>
					<a href="<?=query("?sid=?".(in_array($typ->id,$open)?"":"&open=".$typ->id))?>">
					<?=$typ->name?>(<?=$stats->bcount?>)</a>
				</th>
				<th align="right"><?=$stats->minlevel?>/<?=$stats->maxlevel?></th>
				<th align="right">
					<?=intval(sqlgetone("SELECT SUM(IF(`upgradetime`=0,`upgrades`,`upgrades`-1)) FROM `building` WHERE `construction` = 0 AND `user` = ".$gUser->id." AND `type` = ".$typ->id))?> 
					+ 
					<?=intval(sqlgetone("SELECT SUM(IF(`upgradetime`=0,0,1)) FROM `building` WHERE `construction` = 0 AND `user` = ".$gUser->id." AND `type` = ".$typ->id))?>
				</th>
				<th nowrap>
					<?php if (count($open) == 0) { $js_types[] = $typ->id;?>
						<input type="text" style="width:30px" name="typeup[<?=$typ->id?>]" value="<?=sqlgetone("SELECT MIN(`level`+`upgrades`) FROM `building` WHERE `user` = ".$gUser->id." AND `type` = ".$typ->id)?>">
						<input type='submit' name='typeup_to[<?=$typ->id?>]' value='bis zu Level stufen'>
					<?php } // endif?>
				</th>
			</tr>
			<?php
			if(!in_array($typ->id,$open))continue;
			$colorcounter = 1;
			$t = sqlgettable("SELECT * FROM `building` WHERE `type`=".$typ->id." AND `construction`=0 AND `user`=".$gUser->id." ORDER BY `type` ASC, `level` ASC","id");
			foreach($t as $x)
			{
				// $x->upgradetime is now the time at which the upgrade is complete
				if($x->upgradetime > 0)
						$upgradetimeleft = Duration2Text($x->upgradetime-time());
				else	$upgradetimeleft = false;
				$plannedupgrades = $x->upgrades;
				if($colorcounter++ % 2) $color="style='background-color:#DDDDDD'"; else $color="";
				
				?>
				<tr <?=$color?>>
					<td><a href="<?=query("info.php?sid=?&x=".$x->x."&y=".$x->y)?>"><?=$colorcounter-1?>. <?=$type[$x->type]->name?>(<?=$x->x.",".$x->y?>)</a></td>
					<td align="right"><?=$x->level?></td>
					<td><input style='text-align:right' type='text' name='upgrades[<?=$x->id?>]' size='5' value='<?=$plannedupgrades;?>'></td>
					<?php $js_ids[] = $x->id; $js_levels[] = $x->level;?>
					<td align="right"><?=$upgradetimeleft?></td>
				</tr>
			<?php
			}
			?>
			<tr><td colspan="4" bgcolor="#000000"></td></tr>
			<?php
		}
		?>
		
		<?php if (0) {?>
		<!-- the new and improved javascript upgrader -->
		<script lang="javascript">
		<!--
		var ids = new Array(<?=implode(",",$js_ids)?>);
		var levels = new Array(<?=implode(",",$js_levels)?>);
		function upgradeto()
		{
			tolevel = parseInt(document.getElementsByName("tolevel")[0].value,10);
			if (tolevel < 0) tolevel = 0;
			for(i=1;i<ids.length;++i) {
				d = tolevel - levels[i];
				if(d > 0)	document.getElementsByName("upgrades["+ids[i]+"]")[0].value = d;
				else		document.getElementsByName("upgrades["+ids[i]+"]")[0].value = 0;
			}
		}
		-->
		</script>
		<?php }?>
		
		<tr>
			<th align="right" style='border-bottom:none ; border-top:solid 1px black' colspan="3">max Level mit Haupthaus Level <?=$hqlevel?>:</th>
			<td style='border-top:solid 1px black' align="right"><?=3*($hqlevel+1)?></td>
		</tr>
		<tr>
			<th align="right" style='border-bottom:none' colspan="3">geplante Upgrades insgesamt:</th>
			<td align="right"><?=$plannedupgradessum?></td>
		</tr>
		<?php if (0) {?>
			<tr><td style='text-align:right' colspan='4' align="right">auf Level <input align="right" type="text" value="0" name="tolevel" size="4"> <input type="button" value="aufstufen" OnClick="upgradeto();"></td></tr>
		<?php } // endif?>
		
		<tr><td style='text-align:right' colspan='4' align="right">
			<?php if (count($open) == 0) {?>
				alles auf <input type="text" name="js_typeto" value="0" style="width:30px"> <a href="javascript:SetAllTo()">(setzen)</a><br><br>
				
				<SCRIPT LANGUAGE="JavaScript">
				<!--
					gTypes = new Array(<?=implode(",",$js_types)?>);
					function SetAllTo () {
						var tolevel = parseInt(document.getElementsByName("js_typeto")[0].value,10);
						var i;
						for (i=0;i<gTypes.length;i++) {
							document.getElementsByName("typeup["+gTypes[i]+"]")[0].value = tolevel;
						}
					}
				//-->
				</SCRIPT>
				
				<input type='submit' name='typeup_toall' value='Alle Gebäude bis zum angegebenen Level aufstufen'><br>
				

			<?php } else { // ?>
				<input type='submit' name='upgrade' value='Alle Upgrades Bestätigen'>
			<?php } // endif?>
		</td></tr>
	</table>
	</form>
	
	

</body>
</html>
<?php profile_page_end(); ?>
