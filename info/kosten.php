<?php
require_once("../lib.main.php");
require_once("../lib.map.php");
require_once("../lib.construction.php");
Lock();

profile_page_start("kosten");

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 transitional//EN"
   "http://www.w3.org/TR/html4/transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<link rel="stylesheet" type="text/css" href="<?=GetZWStylePath()?>">
<title>Zwischenwelt - Expenses</title>

</head>
<body>
<?php
include(BASEPATH."/menu.php");
?>

<?php 
	$c_buildingplans = sqlgettable("SELECT *, COUNT(`id`) as c
		FROM `construction`
		WHERE `user` = ".$gUser->id."
		GROUP BY `type`");
	$c_buildings = sqlgettable("SELECT *, COUNT(`id`) as c,
		IF(`upgradetime`,`level`+1,`level`) as `reallevel`,
		IF(`upgradetime`,`upgrades`-1,`upgrades`) as `realupgrades`
		FROM `building`
		WHERE `user` = ".$gUser->id." AND (`upgrades` > 1 OR (`upgrades` > 0 AND `upgradetime` = 0)) 
		GROUP BY `type`,`reallevel`,`realupgrades`");
	$c_techs = sqlgettable("SELECT *,
		IF(`upgradetime`,`level`+1,`level`) as `reallevel`,
		IF(`upgradetime`,`upgrades`-1,`upgrades`) as `realupgrades`
		FROM `technology`
		WHERE `user` = ".$gUser->id." AND (`upgrades` > 1 OR (`upgrades` > 0 AND `upgradetime` = 0))");
	$c_units = sqlgettable("SELECT a.*,b.*,a.`id` as `id` FROM `action` a,`building` b WHERE
		a.`cmd` = ".kActionCmd_Build." AND a.`building` = b.`id` AND b.`user` = ".$gUser->id);
	foreach($gRes as $n=>$f) ${"grandtotalcost_".$f} = 0;
	$grandtotaltimesum = 0;
?>

<table border=1 cellspacing=0>
	<?php /* ***** Neubauten ***** */ ?>
	<tr>
		<td colspan=3 align="center">Neubauten</td>
		<?php foreach($gRes as $n=>$f) echo '<td align="center"><img src="'.g('res_'.$f.'.gif').'"></td>'; ?>
		<td align="center"><img src="<?=g("sanduhrklein.gif")?>"></td>
	</tr>
	<?php $timesum = 0; foreach($gRes as $n=>$f) ${"totalcost_".$f} = 0;?>
	<?php foreach ($c_buildingplans as $o) { ?>
	<tr>
		<td>
			<?=$o->c?>
		</td>
		<td align="center" valign="middle">
			<a target='map' href='<?=Query("../".kMapScript."?sid=?&x=".$o->x."&y=".$o->y)?>'>
			<img src="<?=g($gBuildingType[$o->type]->gfx,"we",0,$gUser->race)?>" border=1>
			</a>
		</td>
		<td></td>
		<?php 
			$parttime = 0;
			$cps = sqlgettable("SELECT * FROM `construction` WHERE `user` = ".$gUser->id." AND `type` = ".$o->type);
			foreach ($cps as $cp)
				$parttime += GetBuildTime($cp->x,$cp->y,$cp->type,$cp->priority,$cp->user);
			$timesum += $parttime;
			foreach($gRes as $n=>$f) ${"partcost_".$f} = $o->c * $gBuildingType[$o->type]->{"cost_".$f};
			foreach($gRes as $n=>$f) ${"totalcost_".$f} += ${"partcost_".$f};
		?>
		<?php foreach($gRes as $n=>$f) echo '<td align="right">'.kplaintrenner(round(${"partcost_".$f},0)).'</td>'; ?>
		<td align="right"><?=Duration2Text($parttime)?></td>
	</tr>
	<?php } ?>
	<tr>
		<td align="right" colspan=3><b>Summe</b></td>
		<?php foreach($gRes as $n=>$f) echo '<td align="right"><b>'.kplaintrenner(round(${"totalcost_".$f},0)).'</b></td>'; ?>
		<td align="right"><?=Duration2Text($timesum)?></td>
	</tr>
	<?php foreach($gRes as $n=>$f) ${"grandtotalcost_".$f} += ${"totalcost_".$f};?>
	<?php if ($grandtotaltimesum < $timesum) $grandtotaltimesum = $timesum;?>


	<?php /* ***** Upgrades ***** */ ?>
	<tr>
		<td colspan=3 align="center">Upgrades</td>
		<?php foreach($gRes as $n=>$f) echo '<td align="center"><img src="'.g('res_'.$f.'.gif').'"></td>'; ?>
		<td align="center"><img src="<?=g("sanduhrklein.gif")?>"></td>
	</tr>
	<?php $timesum = 0; foreach($gRes as $n=>$f) ${"totalcost_".$f} = 0;?>
	<?php foreach ($c_buildings as $o) {?>
	<tr>
		<td>
			<?=$o->c?>
		</td>
		<td align="center" valign="middle">
			<a target='map' href='<?=Query("../".kMapScript."?sid=?&x=".$o->x."&y=".$o->y)?>'>
			<img src="<?=g($gBuildingType[$o->type]->gfx,($o->nwse=="?")?"ns":$o->nwse,($o->level>=10)?"1":0)?>" border=1>
			</a>
		</td>
		<td>
			<?=$o->reallevel?> -&gt; <?=$o->reallevel+$o->realupgrades?>
		</td>
		<?php 
			foreach($gRes as $n=>$f) ${"partcost_".$f} = 0;
			$parttimesum = 0;
			for ($L=$o->reallevel;$L<$o->reallevel+$o->realupgrades;$L++) {
				$upmod = cBuilding::calcUpgradeCostsMod($L+1); 
				$parttimesum += cBuilding::calcUpgradeTime($o->type,$L+1);
				foreach($gRes as $n=>$f) ${"partcost_".$f} += round($upmod*$gBuildingType[$o->type]->{"cost_".$f},0);
			}
			foreach($gRes as $n=>$f) ${"totalcost_".$f} += $o->c*${"partcost_".$f};
			if ($timesum < $parttimesum)
				$timesum = $parttimesum;
		?>
		<?php foreach($gRes as $n=>$f) echo '<td align="right">'.kplaintrenner(round($o->c*${"partcost_".$f},0)).'</td>'; ?>
		<td align="right"><?=Duration2Text($parttimesum)?></td>
	</tr>
	<?php }?>
	<tr>
		<td align="right" colspan=3><b>summe</b></td>
		<?php foreach($gRes as $n=>$f) echo '<td align="right"><b>'.kplaintrenner(round(${"totalcost_".$f},0)).'</b></td>'; ?>
		<td align="right"><?=Duration2Text($timesum)?></td>
	</tr>
	<?php foreach($gRes as $n=>$f) ${"grandtotalcost_".$f} += ${"totalcost_".$f};?>
	<?php if ($grandtotaltimesum < $timesum) $grandtotaltimesum = $timesum;?>
	
	
	
	
	<?php /* ***** Techs ***** */ ?>
	<tr>
		<td colspan=3 align="center">Techs</td>
		<?php foreach($gRes as $n=>$f) echo '<td align="center"><img src="'.g('res_'.$f.'.gif').'"></td>'; ?>
		<td align="center"><img src="<?=g("sanduhrklein.gif")?>"></td>
	</tr>
	<?php $timesum = 0; foreach($gRes as $n=>$f) ${"totalcost_".$f} = 0;?>
	<?php foreach ($c_techs as $o) { $b = sqlgetobject("SELECT * FROM `building` WHERE `id` = ".intval($o->upgradebuilding));?>
	<tr>
		<td></td>
		<td align="center" valign="middle">
			<a target='map' href='<?=Query("../".kMapScript."?sid=?&x=".$b->x."&y=".$b->y)?>'>
			<img src="<?=g($gTechnologyType[$o->type]->gfx)?>" border=1>
			</a>
		</td>
		<td>
			<?=$o->reallevel?> -&gt; <?=$o->reallevel+$o->realupgrades?>
		</td>
		<?php 
			foreach($gRes as $n=>$f) ${"partcost_".$f} = 0;
			$parttimesum = 0;
			for ($L=$o->reallevel;$L<$o->reallevel+$o->realupgrades;$L++) {
				$upmod = $L*$gTechnologyType[$o->type]->increment + 1.0;
				$parttimesum += $upmod*$gTechnologyType[$o->type]->basetime;
				foreach($gRes as $n=>$f) ${"partcost_".$f} += round($upmod*$gTechnologyType[$o->type]->{"basecost_".$f},0);
			}
			foreach($gRes as $n=>$f) ${"totalcost_".$f} += ${"partcost_".$f};
			if ($timesum < $parttimesum)
				$timesum = $parttimesum;
		?>
		<?php foreach($gRes as $n=>$f) echo '<td align="right">'.kplaintrenner(round(${"partcost_".$f},0)).'</td>'; ?>
		<td align="right"><?=Duration2Text($parttimesum)?></td>
	</tr>
	<?php }?>
	<tr>
		<td align="right" colspan=3><b>summe</b></td>
		<?php foreach($gRes as $n=>$f) echo '<td align="right"><b>'.kplaintrenner(round(${"totalcost_".$f},0)).'</b></td>'; ?>
		<td align="right"><?=Duration2Text($timesum)?></td>
	</tr>
	<?php foreach($gRes as $n=>$f) ${"grandtotalcost_".$f} += ${"totalcost_".$f};?>
	<?php if ($grandtotaltimesum < $timesum) $grandtotaltimesum = $timesum;?>
	
	
	
	
	<?php /* ***** Units ***** */ ?>
	<tr>
		<td colspan=3 align="center">Units</td>
		<?php foreach($gRes as $n=>$f) echo '<td align="center"><img src="'.g('res_'.$f.'.gif').'"></td>'; ?>
		<td align="center"><img src="<?=g("sanduhrklein.gif")?>"></td>
	</tr>
	<?php $timesum = 0; foreach($gRes as $n=>$f) ${"totalcost_".$f} = 0;?>
	<?php foreach ($c_units as $o) { $amount = ($o->starttime > 0)?($o->param2-1):$o->param2; if ($amount == 0) continue;?>
	<tr>
		<td></td>
		<td align="center" valign="middle">
			<a target='map' href='<?=Query("../".kMapScript."?sid=?&x=".$o->x."&y=".$o->y)?>'>
			<img src="<?=g($gUnitType[$o->param1]->gfx)?>" border=1>
			</a>
		</td>
		<td>
			<?=$amount?>
		</td>
		<?php 
			$parttime = $amount*$gUnitType[$o->param1]->buildtime;
			if ($timesum < $parttime)
				$timesum = $parttime;
			foreach($gRes as $n=>$f) ${"partcost_".$f} = $amount * $gUnitType[$o->param1]->{"cost_".$f};
			foreach($gRes as $n=>$f) ${"totalcost_".$f} += ${"partcost_".$f};
		?>
		<?php foreach($gRes as $n=>$f) echo '<td align="right">'.kplaintrenner(round(${"partcost_".$f},0)).'</td>'; ?>
		<td align="right"><?=Duration2Text($parttime)?></td>
	</tr>
	<?php }?>
	<tr>
		<td align="right" colspan=3><b>summe</b></td>
		<?php foreach($gRes as $n=>$f) echo '<td align="right"><b>'.kplaintrenner(round(${"totalcost_".$f},0)).'</b></td>'; ?>
		<td align="right"><?=Duration2Text($timesum)?></td>
	</tr>
	<?php foreach($gRes as $n=>$f) ${"grandtotalcost_".$f} += ${"totalcost_".$f};?>
	<?php if ($grandtotaltimesum < $timesum) $grandtotaltimesum = $timesum;?>
	
	
	
	
	<?php /* ***** Grand Total ***** */ ?>
	<tr>
		<td align="right" colspan=3><b>Summe total</b></td>
		<?php foreach($gRes as $n=>$f) echo '<td align="right"><b style="color:'.((round(${"grandtotalcost_".$f},0)<=$gUser->$f)?"green":"red").'">'.kplaintrenner(round(${"grandtotalcost_".$f},0)).'</b></td>'; ?>
		<td align="right"><?=Duration2Text($grandtotaltimesum)?></td>
	</tr>
</table>

<br>
<br>
siehe auch <a href="<?=Query("bauplan.php?sid=?")?>"><b>Baupl&auml;ne</b></a><br>
Die Zeitberechnung hier berücksichtigt nicht alle Faktoren, und gibt nur einen groben Hinweis auf die tatsächliche Dauer.<br>
Auf der Karte kann man sich anzeigen lassen,<br>
was die Entfernung von Bauplänen zum nächsten Lager bewirkt, wenn man den "Bauzeit" Knopf drückt<br>
<?php profile_page_end();?>
</body>
</html>
