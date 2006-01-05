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

if (!isset($f_selbtype)) $f_selbtype = kBuilding_HQ;

if (isset($f_upgrades)) {
	// plan[type][level][oldups] = level + planup
	// planup = (`upgrades` + IF(`upgradetime`>0,1,0))
	foreach ($f_plan as $typeid => $arr1)
	foreach ($arr1 as $level => $arr2)
	foreach ($arr2 as $oldups => $targetlevel) {
		$targetups = max(0,$targetlevel - $level);
		sql("UPDATE `building` SET `upgrades` = GREATEST(IF(`upgradetime`>0,1,0),".intval($targetups).") WHERE 
			`user` = ".$gUser->id." AND 
			`construction` = 0 AND
			`type` = ".intval($typeid)." AND 
			`level` = ".intval($level)." AND 
			`upgrades` = ".intval($oldups));
		$f_selbtype = $typeid;
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
$totalbuildings = sqlgetone("SELECT COUNT(*) FROM `building` WHERE `user`=".$gUser->id);
$hqlevel = sqlgetone("SELECT `level` FROM `building` WHERE `user`=".$gUser->id." AND `type`=".kBuilding_HQ." LIMIT 1");
$maxlevel = 3*($hqlevel+1);
?>
insgesamt <?=$totalbuildings?> Gebäude

<?php
if (isset($f_listtype)) {
	// &listtype=".$o->type."&listlevel=".$o->level."&listup=".$o->upgrades
	$buildings = sqlgettable("SELECT * FROM `building` WHERE 
		`user` = ".$gUser->id." AND 
		`construction` = 0 AND 
		`type` = ".intval($f_listtype)." AND 
		`level` = ".intval($f_listlevel)." AND 
		`upgrades` = ".intval($f_listup));
	$typepic = "<img src=\"".GetBuildingPic(intval($f_listtype),$gUser)."\">";
	?>
	<a href="<?=Query("?sid=?&selbtype=".$f_listtype)?>">(zurück zur Übersicht)</a><br>
	<?php
	echo "Liste aller ".$typepic." auf Stufe ".intval($f_listlevel)." mit ".intval($f_listup)." geplanten Upgrades :<br>";
	foreach ($buildings as $o) echo opos2txt($o)." ";
} else {
	$buildinggroups = sqlgettable("SELECT *,COUNT(*) as `c` FROM `building` WHERE `user` = ".$gUser->id." AND `construction` = 0 GROUP BY `type`,`level`,`upgrades` ORDER BY `type`,`level` DESC,`upgrades` DESC");
	$btypes = array();
	foreach ($buildinggroups as $o) if (!in_array($o->type,$btypes)) $btypes[] = $o->type;
	$mytabs = array();
	
	foreach ($btypes as $btype) {
		$typepic = "<img src=\"".GetBuildingPic($btype,$gUser)."\">";
		rob_ob_start();
		$totalcost = array_fill(0,count($gRes),0);
		$totaltime = 0;
		$totalcount = 0;
		$totalups = 0;
		?>
		<form method="post" action="<?=Query("?sid=?")?>">
			<table border=1 cellspacing=0>
			<tr>
				<th></th>
				<th>#</th>
				<th>Stufe</th>
				<th>Upgrades</th>
				<th>nächstes Upgrade</th>
				<th><img src="<?=g("sanduhrklein.gif")?>"></th>
				<th>geplant bis</th>
			</tr>
			<?php foreach ($buildinggroups as $o) if ($o->type == $btype) {?>
				<?php
				$nextlevel = $o->level + $o->upgrades + 1;
				$upmod = cBuilding::calcUpgradeCostsMod($nextlevel); 
				$time = cBuilding::calcUpgradeTime($o->type,$nextlevel);
				$costarr = array(
					$gBuildingType[$btype]->cost_lumber * $upmod,
					$gBuildingType[$btype]->cost_stone * $upmod,
					$gBuildingType[$btype]->cost_food * $upmod,
					$gBuildingType[$btype]->cost_metal * $upmod,
					$gBuildingType[$btype]->cost_runes * $upmod
					);
				// calc total cost
				$curtime = 0;
				for ($i=$o->level;$i<$o->level+$o->upgrades;++$i) {
					$curtime += cBuilding::calcUpgradeTime($o->type,$i+1);
					$j = 0; foreach ($gRes as $n=>$f) 
						$totalcost[$j++] += $o->c * $gBuildingType[$btype]->{"cost_".$f} * cBuilding::calcUpgradeCostsMod($i+1);
				}
				$totaltime = max($totaltime,$curtime);
				$totalcount += $o->c;
				$totalups += $o->c * $o->upgrades;
				?>
				<tr>
					<td><a href="<?=Query("?sid=?&listtype=".$o->type."&listlevel=".$o->level."&listup=".$o->upgrades)?>"><?="<img border=0 src=\"".GetBuildingPic($o->type,$gUser,$o->level)."\">"?></a></td>
					<td align="right"><?=$o->c?></td>
					<td align="right"><?=$o->level?></td>
					<td align="right"><?=$o->upgrades?></td>
					<td align="left"><?=cost2txt($costarr,$gUser)?></td>
					<td align="right"><?=Duration2Text($time)?></td>
					<td align="right"><input align="right" style="width:40px" type="text" name="plan[<?=$o->type?>][<?=$o->level?>][<?=$o->upgrades?>]" value="<?=$o->level + $o->upgrades?>"></td>
				</tr>
			<?php } // endforeach?>
				<tr>
					<th></th>
					<th><?=$totalcount?></th>
					<th></th>
					<th><?=$totalups?></th>
					<th><?=cost2txt($totalcost)?></th>
					<th><?=Duration2Text($totaltime)?></th>
					<th></th>
				</tr>
			</table>
			<input type="submit" name="upgrades" value="speichern">
			(max Gebäudestufe <b><?=$maxlevel?></b> bei <?="<img src=\"".GetBuildingPic(kBuilding_HQ,$gUser)."\">"?> Stufe <b><?=$hqlevel?></b>)
		</form>
		<?php
		$mytabs[$btype] = array($typepic,rob_ob_end());
	}
	//foreach ($mytabs as $arr) echo $arr[1];
	echo GenerateTabsMultiRow("buildingsummarytabs",$mytabs,14,$f_selbtype);
}
?>

</body>
</html>
<?php profile_page_end(); ?>
