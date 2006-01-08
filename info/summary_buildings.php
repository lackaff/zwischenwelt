<?php
require_once("../lib.main.php");
require_once("../lib.building.php");
require_once("../lib.army.php");
require_once("../lib.map.php");
require_once("../lib.spells.php");
require_once("../lib.tabs.php");
require_once("../lib.text.php");
Lock();
profile_page_start("summary_buildings.php");

if (!isset($f_selbtype)) $f_selbtype = 0;

if (isset($f_upgrades)) {
	// plan[type][level][oldups] = level + planup
	// planup = (`upgrades` + IF(`upgradetime`>0,1,0))
	foreach ($f_plan as $typeid => $arr1)
	foreach ($arr1 as $level => $arr2)
	foreach ($arr2 as $oldups => $targetlevel) {
		sql("UPDATE `building` SET `upgrades` = GREATEST(IF(`upgradetime`>0,1,0),".intval($targetlevel - $level).") WHERE 
			`user` = ".$gUser->id." AND 
			`construction` = 0 AND
			`type` = ".intval($typeid)." AND 
			`level` = ".intval($level)." AND 
			`upgrades` = ".intval($oldups));
		$f_selbtype = $typeid;
	}
}
if (isset($f_allupgrades)) {
	// plan[type][level][oldups] = level + planup
	// planup = (`upgrades` + IF(`upgradetime`>0,1,0))
	foreach ($f_plan as $typeid => $arr1)
	foreach ($arr1 as $level => $arr2)
	foreach ($arr2 as $oldups => $targetlevel) {
		sql("UPDATE `building` SET `upgrades` = GREATEST(IF(`upgradetime`>0,1,0),".intval($targetlevel)." - `level`) WHERE 
			`user` = ".$gUser->id." AND 
			`construction` = 0 AND
			`type` = ".intval($typeid));
	}
	$f_selbtype = 0;
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 transitional//EN"
   "http://www.w3.org/TR/html4/transitional.dtd">
<html>
<head>
<link rel="stylesheet" type="text/css" href="../styles.css">
<link rel="stylesheet" type="text/css" href="<?=GetZWStylePath()?>">
<title>Zwischenwelt - Übersicht</title>
<SCRIPT LANGUAGE="JavaScript" type="text/javascript">
<!--
	function planall (name,maxindex,setvalname) {
		//var i,arr = document.getElementsByName(name);
		var i,setval = document.getElementById(setvalname).value;
		for (i=0;i<maxindex;++i) document.getElementById(name+i).value = setval;
	}
//-->
</SCRIPT>
</head>
<body>
<?php include("../menu.php"); ?>

<?php
$totalbuildings = sqlgetone("SELECT COUNT(*) FROM `building` WHERE `user`=".$gUser->id);
$hqlevel = sqlgetone("SELECT `level` FROM `building` WHERE `user`=".$gUser->id." AND `type`=".kBuilding_HQ." LIMIT 1");
$maxlevel = 3*($hqlevel+1);
?>
insgesamt <?=$totalbuildings?> Gebäude

<?php
if (isset($f_listtype)) {
	// &listtype=".$o->type."&listlevel=".$o->level."&listup=".$o->upgrades
	$cond = array();
	$cond[] = "`user` = ".$gUser->id;
	$cond[] = "`construction` = 0";
	$cond[] = "`type` = ".intval($f_listtype);
	if ($f_listlevel > -1)	$cond[] = "`level` = ".intval($f_listlevel);
	if ($f_listup > -1)		$cond[] = "`upgrades` = ".intval($f_listup);
	$buildings = sqlgettable("SELECT * FROM `building` WHERE ".implode(" AND ",$cond));
	$typepic = "<img src=\"".GetBuildingPic(intval($f_listtype),$gUser)."\">";
	?>
	<a href="<?=Query("?sid=?&selbtype=".$f_listtype)?>">(zurück zur Übersicht)</a><br>
	<?php
	echo "Liste aller(".count($buildings).") ".$typepic." auf Stufe ".intval($f_listlevel)." mit ".intval($f_listup)." geplanten Upgrades :<br>";
	foreach ($buildings as $o) echo opos2txt($o)." ";
} else {
	$buildinggroups = sqlgettable("SELECT *,COUNT(*) as `c`,`level`+`upgrades` as `planlevel` FROM `building` WHERE `user` = ".$gUser->id." AND `construction` = 0 GROUP BY `type`,`level`,`upgrades` ORDER BY `type`,`level` DESC,`upgrades` DESC");
	$buildinggroups2 = sqlgettable("SELECT *,COUNT(*) as `c`,MAX(`level`+`upgrades`) as `planlevel`,MAX(`level`) as `level_max`,MIN(`level`) as `level_min` FROM `building` WHERE `user` = ".$gUser->id." AND `construction` = 0 GROUP BY `type` ORDER BY `type`");
	$btypes = array();
	$btypes[] = 0; // summary for all buildings
	foreach ($buildinggroups as $o) if (!in_array($o->type,$btypes)) $btypes[] = $o->type;
	$mytabs = array();
	
	foreach ($btypes as $btype) {
		rob_ob_start();
		$totalcost = array_fill(0,count($gRes),0);
		$totaltime = 0;
		$totalcount = 0;
		$totalups = 0;
		$maxplan = 0;
		$countplanner = 0;
		?>
		<form method="post" action="<?=Query("?sid=?")?>">
			<table border=1 cellspacing=0>
			<tr>
				<th></th>
				<th>Stufe</th>
				<th>Anzahl</th>
				<?php if ($btype!=0) {?>
					<th>Upgrades</th>
					<th>nächstes Upgrade</th>
					<th><img src="<?=g("sanduhrklein.gif")?>"></th>
				<?php } // endif?>
				<th>geplant bis</th>
			</tr>
			<?php 
			$arr = ($btype==0)?$buildinggroups2:$buildinggroups;
			foreach ($arr as $o) if ($o->type == $btype || $btype == 0) {?>
				<?php
				if ($btype!=0) $o->level_max = $o->level;
				$maxplan = max($maxplan,$o->level + $o->upgrades);
				$nextlevel = $o->level + $o->upgrades + 1;
				$upmod = cBuilding::calcUpgradeCostsMod($nextlevel); 
				$time = cBuilding::calcUpgradeTime($o->type,$nextlevel);
				$costarr = array(
					$gBuildingType[$o->type]->cost_lumber * $upmod,
					$gBuildingType[$o->type]->cost_stone * $upmod,
					$gBuildingType[$o->type]->cost_food * $upmod,
					$gBuildingType[$o->type]->cost_metal * $upmod,
					$gBuildingType[$o->type]->cost_runes * $upmod
					);
				// calc total cost
				$curtime = 0;
				for ($i=$o->level;$i<$o->level+$o->upgrades;++$i) {
					$curtime += cBuilding::calcUpgradeTime($o->type,$i+1);
					$j = 0; foreach ($gRes as $n=>$f) 
						$totalcost[$j++] += $o->c * $gBuildingType[$o->type]->{"cost_".$f} * cBuilding::calcUpgradeCostsMod($i+1);
				}
				$totaltime = max($totaltime,$curtime);
				$totalcount += $o->c;
				$totalups += $o->c * $o->upgrades;
				?>
				<tr>
					<td><a href="<?=Query("?sid=?&listtype=".$o->type."&listlevel=".$o->level."&listup=".$o->upgrades)?>"><?="<img border=0 src=\"".GetBuildingPic($o->type,$gUser,$o->level_max)."\">"?></a></td>
					<td align="right"><?=($btype==0)?($o->level_min."-".$o->level_max):$o->level?></td>
					<td align="right"><?=$o->c?></td>
					<?php if ($btype!=0) {?>
						<td align="right"><?=$o->upgrades?></td>
						<td align="left"><?=cost2txt($costarr,$gUser)?></td>
						<td align="right"><?=Duration2Text($time)?></td>
					<?php } // endif?>
					<td align="right"><input align="right" style="width:40px" type="text" id="planner_<?=$btype?>_<?=$countplanner++?>" name="plan[<?=$o->type?>][<?=$o->level?>][<?=$o->upgrades?>]" value="<?=$o->planlevel?>"></td>
				</tr>
			<?php } // endforeach?>
				<tr>
					<th colspan=2 align="left">Summe:</th>
					<th><?=$totalcount?></th>
					<?php if ($btype!=0) {?>
						<th><?=$totalups?></th>
						<th><?=cost2txt($totalcost)?></th>
						<th><?=Duration2Text($totaltime)?></th>
					<?php } // endif?>
					<th align="right">
						<?php if ($btype!=0) {?>
							<a href="javascript:planall('planner_<?=$btype?>_',<?=$countplanner?>,'plansetvalue_<?=$btype?>')">
							<img border=0 src="<?=g("scroll/n.png")?>" alt="alle setzten" title="alle setzten"></a>
							<input align="right"  type="text" id="plansetvalue_<?=$btype?>" name="plansetvalue_<?=$btype?>" value="<?=$maxplan?>" style="width:40px">
						<?php } else { // ?>
							max:<?=$maxplan?>
						<?php } // endif?>
					</th>
				</tr>
			</table>
			<input type="submit" name="<?=($btype == 0)?"allupgrades":"upgrades"?>" value="speichern">
			(max Gebäudestufe <b><?=$maxlevel?></b> bei <?="<img src=\"".GetBuildingPic(kBuilding_HQ,$gUser)."\">"?> Stufe <b><?=$hqlevel?></b>)
		</form>
		<?php
		if ($btype == 0)
				$header = "<img border=0 src=\"".g("tool_look.png")."\" alt=\"\" title=\"\">";
		else	$header = "<img src=\"".GetBuildingPic($btype,$gUser)."\">";
		$mytabs[$btype] = array($header,rob_ob_end());
	}
	//foreach ($mytabs as $arr) echo $arr[1];
	echo GenerateTabsMultiRow("buildingsummarytabs",$mytabs,14,$f_selbtype);
}
?>

</body>
</html>
<?php profile_page_end(); ?>
