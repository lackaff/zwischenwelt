<?php
require_once("../lib.main.php");
require_once("../lib.building.php");
require_once("../lib.army.php");
require_once("../lib.map.php");
require_once("../lib.spells.php");
require_once("../lib.tabs.php");
require_once("../lib.text.php");
Lock();
profile_page_start("summary_techs.php");


// if (isset($f_plan)) SetTechnologyUpgrades($f_techtype,$gObject->id,$f_upcount);
if (isset($f_techs)) {
	foreach ($f_plan as $typeid => $targetlevel) {
		$typeid = intval($typeid);
		if (!isset($gTechnologyType[$typeid])) continue;
		$typeobj = $gTechnologyType[$typeid];
		$targetlevel = min($typeobj->maxlevel,intval($targetlevel));
		$curlevel = GetTechnologyLevel($typeid,$gUser->id);
		$found_id = 0;
		$found_minplanned = 0;
		$tech = GetTechnologyObject($typeid,$gUser->id);
		$debug = false;
		
		if ($debug) echo "plan $typeid : cl=$curlevel tl=$targetlevel tut=$tech->upgradetime tub=$tech->upgradebuilding<br>";
		
		if ($tech->upgradetime > 0) $found_id = $tech->upgradebuilding; // already running in building
		else {
			// not running, search for a good building (not busy, low level preferred, so higher techs are not blocked)
			$cond = "`user` = ".intval($gUser->id)." AND `type` = ".$typeobj->buildingtype." AND `level` >= ".$typeobj->buildinglevel;
			$buildings = sqlgettable("SELECT * FROM `building` WHERE ".$cond." ORDER BY `level` ASC");
			foreach ($buildings as $o) {
				// count all upgrades planned here
				$cond2 = "`user` = ".intval($gUser->id)." AND `upgrades` > 0 AND `upgradebuilding` = ".$o->id;
				if ($debug) echo "SELECT COUNT(*) FROM `technology` WHERE ".$cond2."<br>";
				$curplanned = sqlgetone("SELECT COUNT(*) FROM `technology` WHERE ".$cond2);
				if ($debug) echo "consider $o->id($o->x,$o->y) : cp=$curplanned , fid=$found_id fmin=$found_minplanned ";
				if ($found_id == 0 || $found_minplanned > $curplanned) {	
					$found_id = $o->id;
					$found_minplanned = $curplanned;
					if ($debug) echo "SET!";
				}
				if ($debug) echo "<br>";
			}
		}
		
		$target_ups = max(0,$targetlevel - $curlevel);
		if ($found_id) if ($debug) echo "SetTechnologyUpgrades($typeid,$found_id,".max(0,$targetlevel - $curlevel).");<br>";
		if ($found_id) SetTechnologyUpgrades($typeid,$found_id,$target_ups);
		else echo "ERROR : no building found, this should not happen, as requirements demand at least one building<br>";
	}
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 transitional//EN"
   "http://www.w3.org/TR/html4/transitional.dtd">
<html>
<head>
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
	$timetip = "Restzeit des derzeit lauffenden Upgrades";
	$techgroups = array();
	$btypes = array();
	$btypes[] = 0;
	// buildingtype,buildinglevel,group,name,descr,
	// basecost_lumber,basecost_stone,basecost_food,basecost_metal,basecost_runes,basetime
	// maxlevel,increment,req_tech,req_geb,gfx

	$techtypes = sqlgettable("SELECT * FROM `technologytype` ORDER BY `buildingtype`,`group`");
	foreach ($techtypes as $o) if (!in_array($o->buildingtype,$btypes)) $btypes[] = $o->buildingtype;
	$mytabs = array();

	foreach ($btypes as $btype) {
		$countplanner = 0;
		if (!isset($f_selbtype)) $f_selbtype = $btype;
		if ($btype != 0) {
			$cond = "`type` = ".intval($btype)." AND `user` = ".intval($gUser->id);
			$highest_building = sqlgetobject("SELECT * FROM `building` WHERE ".$cond." ORDER BY `level` DESC LIMIT 1");
			$x = $highest_building ? $highest_building->x : 0;
			$y = $highest_building ? $highest_building->y : 0;
			$bcount = sqlgetone("SELECT COUNT(*) FROM `building` WHERE ".$cond);
		}
		rob_ob_start();
		?>
		<?php if ($btype != 0) {?>
			<?=($bcount>0)?($bcount." Gebäude von diesem Typ"):"<font color='red'>Noch kein Gebäude von diesem Typ</font>"?><br>
			<?=$highest_building?("Höchstes Gebäude von diesem Typ hat Stufe <b>".$highest_building->level."</b> bei ".oposinfolink($highest_building)):""?>
		<?php } // endif?>
		<form method="post" action="<?=Query("?sid=?&selbtype=".$btype)?>">
			<table border=1 cellspacing=0>
			<tr>
				<th colspan=2>Technologie</th>
				<th>Level</th>
				<th>Max</th>
				<th>Upgrades</th>
				<th>nächstes Upgrade</th>
				<th><img src="<?=g("sanduhrklein.gif")?>" alt="<?=$timetip?>" title="<?=$timetip?>"></th>
				<th>geplant bis</th>
			</tr>
			<?php foreach ($techtypes as $o) if ($o->buildingtype == $btype || $btype == 0) {?>
				<?php
				$curlevel = GetTechnologyLevel($o->id);
				$cond = "`type` = ".intval($o->buildingtype)." AND `user` = ".intval($gUser->id);
				$highest_building = sqlgetobject("SELECT * FROM `building` WHERE ".$cond." ORDER BY `level` DESC LIMIT 1");
				$x = $highest_building ? $highest_building->x : 0;
				$y = $highest_building ? $highest_building->y : 0;
				$detaillink = Query("info.php?sid=?&x=".$x."&y=".$y."&infotechtype=".$o->id);
				$hasreq = $highest_building && $highest_building->level >= $o->buildinglevel && HasReq($o->req_geb,$o->req_tech,$gUser->id,$curlevel+1);
				$tech = GetTechnologyObject($o->id);
				$plannedbuilding = false;
				if ($tech->upgrades > 0 && $tech->upgradebuilding) $plannedbuilding = sqlgetobject("SELECT * FROM `building` WHERE `id` = ".intval($tech->upgradebuilding));
				$upmod = cTechnology::GetUpgradeMod($tech->type,$tech->level+$tech->upgrades);
				if ($tech->level+$tech->upgrades < $o->maxlevel) {
					$time = cTechnology::GetUpgradeDuration($tech->type,$tech->level+$tech->upgrades);
					$costarr = array(
						$o->basecost_lumber * $upmod,
						$o->basecost_stone * $upmod,
						$o->basecost_food * $upmod,
						$o->basecost_metal * $upmod,
						$o->basecost_runes * $upmod
						);
				}
				?>
				<tr>
					<td><a href="<?=$detaillink?>"><img border=0 src="<?=g($o->gfx)?>" alt="" title=""></a></td>
					<td><a href="<?=$detaillink?>"><?=$o->name?></a></td>
					<td align="right"><?=$curlevel?></td>
					<td align="right"><?=$o->maxlevel?></td>
					<td align="right"><?=$tech->upgrades?></td>
					<td align="left">
					<?php if ($tech->level+$tech->upgrades < $o->maxlevel) {?>
						<img src="<?=g("sanduhrklein.gif")?>"><?=Duration2Text($time)?> <?=cost2txt($costarr,$gUser)?>
					<?php } else { // ?>
						<span style="color:green">max erreicht</span>
					<?php } // endif?>
					</td>
					<td norwap>
						<?php /* #### FORSCHUNG LÄUFT in $tech->upgradebuilding #### */?>
						<?php if ($tech->upgradetime > 0) {?>
							<?php 
								$max = max(1,cTechnology::GetUpgradeDuration($tech->type,$tech->level));
								$timeleft = max(0,$tech->upgradetime - time());
								$cur = $max - $timeleft;
								$percent = min(100,max(0,floor((100.0*$cur)/$max)));
							?>
							<table border=0 width="100%"><tr><td norwap>
							<?="$percent% ".Duration2Text($timeleft)?>
							</td></tr><tr>
							<td height=5 style="border:1px solid black"><?=DrawBar($cur,$max,GradientRYG(GetFraction($cur,$max)),"black")?></td>
							</tr></table>
						<?php } else { ?>
						&nbsp;
						<?php } // endif ?>
					</td>
					<td align="right" norwap>
						<?php if ($hasreq) {?>
							<?=$plannedbuilding?oposinfolink($plannedbuilding):""?>
							<input type="text" style="width:20px" id="planner_<?=$btype?>_<?=$countplanner++?>" name="plan[<?=$o->id?>]" value="<?=$tech->upgrades+$curlevel?>">
						<?php } else { // ?>
							<a href="<?=$detaillink?>"><font color="red"><b>Anforderungen</b></font></a>
						<?php } // endif?>
					</td>
				</tr>
			<?php } // endforeach?>
			<tr>
				<th colspan=7 align="left">&nbsp;</th>
				<th align="right">
					<a href="javascript:planall('planner_<?=$btype?>_',<?=$countplanner?>,'plansetvalue<?=$btype?>')">
					<img border=0 src="<?=g("scroll/n.png")?>" alt="alle setzten" title="alle setzten"></a>
					<input align="right"  type="text" id="plansetvalue<?=$btype?>" name="plansetvalue<?=$btype?>" value="0" style="width:40px">
				</th>
			</tr>
			</table>
			<input type="submit" name="techs" value="speichern">
		</form>
		<?php
		if ($btype == 0)
				$header = "<img border=0 src=\"".g("tool_look.png")."\" alt=\"komplette Liste\" title=\"komplette Liste\">";
		else	$header = "<img src=\"".GetBuildingPic($btype,$gUser)."\">";
		$mytabs[$btype] = array($header,rob_ob_end());
	}
	
	echo GenerateTabsMultiRow("techssummarytabs",$mytabs,14,$f_selbtype);
?>

<a href="<?=query("techgraphpart.php?sid=?")?>">[Technologiebaum durchsuchen]</a><br>
<a target="_blank" href="../tmp/tech.png">[ganzen Technologiebaum anzeigen]</a><br>
Der Punkt "nächstes Upgrade" stellt die Kosten vom nächsten Upgrade dar, das kommt nach dem alle geplanten fertig sind.<br>

</body>
</html>
<?php profile_page_end(); ?>


