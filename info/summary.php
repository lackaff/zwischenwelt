<?php
require_once("../lib.main.php");
require_once("../lib.building.php");
require_once("../lib.army.php");
require_once("../lib.map.php");
require_once("../lib.spells.php");
Lock();
profile_page_start("summary.php");

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
<link rel="stylesheet" type="text/css" href="<?=GetZWStylePath()?>">
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

	<?php if (count($myspells) > 0 || count($onme) > 0) {?>	
			<h4>Zauber</h4>
			<table id='summary'>
				<tr><th colspan=5>Meine Zauber</th></tr>
			<?php foreach ($myspells as $spell){
				$spellobj = GetSpellInstance($spell->type,$spell);
				$effect = $spellobj->Effect();
				?>
				<tr>
					<td nowrap><?=cText::Wiki("spell",$spell->type)?><a href="<?=Query("?sid=?&x=?&y=?&infospelltype=".$spell->type)?>"><?=$gSpellType[$spell->type]->name?></a></td>
					<td nowrap>auf <?=($spell->targettype==MTARGET_AREA)?opos2txt($spell):nick($spell->target,"Server",true)?></td>
					<td nowrap>noch <?=Duration2Text($spell->lasts-time())?></td>
					<td nowrap><?=$effect?></td>
					<?php if ($gUser->admin) {?>
						<td nowrap>
						<a href="<?=query("adminspell.php?sid=?&id=".$spell->type)?>"><img alt=Admin title=Admin src="<?=g("icon/admin.png")?>" border=0></a>
						<a href="<?=Query("?sid=?&spelltest=".$spell->id."&spelltestrepeat=1")?>">1</a>
						<a href="<?=Query("?sid=?&spelltest=".$spell->id."&spelltestrepeat=5")?>">5</a>
						<a href="<?=Query("?sid=?&spelltest=".$spell->id."&spelltestrepeat=60")?>">60</a>
						<a href="<?=Query("?sid=?&spellexpire=".$spell->id)?>">expire<?=$spelltestimg?></a>
						</td>
					<?php } // endif?>
				</tr>
				<?php
			}?>
			<tr><th colspan=5><img src="<?=g("1px.gif")?>"></th></tr>
			<tr><th colspan=5>Zauber auf mich</th></tr>
			<?php foreach ($onme as $spell){
				$spellobj = GetSpellInstance($spell->type,$spell);
				$effect = $spellobj->Effect();
				?>
				<tr>
					<td nowrap><?=cText::Wiki("spell",$spell->type)?> <a href="<?=Query("?sid=?&x=?&y=?&infospelltype=".$spell->type)?>"><?=$gSpellType[$spell->type]->name?></a></td>
					<td nowrap>von <?=nick($spell->owner,"Server",true)?></td>
					<td nowrap>noch <?=Duration2Text($spell->lasts-time())?></td>
					<td nowrap><?=$effect?></td>
					<?php if ($gUser->admin) {?>
						<td nowrap>
						<a href="<?=query("adminspell.php?sid=?&id=".$spell->type)?>"><img alt=Admin title=Admin src="<?=g("icon/admin.png")?>" border=0></a>
						<a href="<?=Query("?sid=?&spelltest=".$spell->id."&spelltestrepeat=1")?>">1</a>
						<a href="<?=Query("?sid=?&spelltest=".$spell->id."&spelltestrepeat=5")?>">5</a>
						<a href="<?=Query("?sid=?&spelltest=".$spell->id."&spelltestrepeat=60")?>">60</a>
						<a href="<?=Query("?sid=?&spellexpire=".$spell->id)?>">expire<?=$spelltestimg?></a>
						</td>
					<?php } // endif?>
				</tr>
				<?php
			}?>
		</table>
	<?php } // endif?>

</body>
</html>
<?php profile_page_end(); ?>
