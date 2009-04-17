<?php
require_once("../lib.main.php");
require_once("../lib.building.php");
require_once("../lib.army.php");
require_once("../lib.map.php");
require_once("../lib.spells.php");
Lock();
profile_page_start("summary_units.php");

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
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<link rel="stylesheet" type="text/css" href="<?=GetZWStylePath()?>">
<title>Zwischenwelt - Übersicht</title>
</head>
<body>
<?php include("../menu.php"); ?>


	<h4>Truppen</h4>
	[ <a href="<?=query("?armytype=0&sid=?")?>">alle</a> 
	<?php 
		if(!isset($f_armytype))$f_armytype = -1;
		if(!isset($f_showempty))$f_showempty = 0;
	
		foreach($gArmyType as $x){
		?>
			| <a href="<?=query("?showempty=?&armytype=$x->id&sid=?")?>"><?=$x->name?></a>
		<?php
		}
		?>
		] [ <?php if($f_showempty>0)echo "<a href='".query("?armytype=$f_armytype&showempty=0&sid=?")."'>leere nicht anzeigen</a>"; else echo "<a href='".query("?armytype=$f_armytype&showempty=1&sid=?")."'>leere anzeigen</a>"; ?> ]<br><br>
		<?php
	
		//list all buildings where units can be produced
		//but only selected armytype matters
		if($f_armytype>0){
			$where = "AND `type`=".intval($f_armytype);
			$troupbuildingtypes = sqlgetfieldarray("SELECT DISTINCT u.`buildingtype` FROM `unittype` u,`armytype` a WHERE u.`buildingtype`>0 AND u.`armytype`=a.`id` AND a.`id`=".intval($f_armytype));
		} else {
			$where = "";
			$troupbuildingtypes = sqlgetfieldarray("SELECT DISTINCT `buildingtype` FROM `unittype` WHERE `buildingtype`>0");
		}
	
		//array(kBuilding_Baracks,kBuilding_Garage,kBuilding_Harbor);
		$totaltroups = array();
		foreach($gUnitType as $o)
			$totaltroups[$o->id] = 0;
		$eatsum = 0;
		$fragsum = 0;
		$myArmies = sqlgettable("SELECT * FROM `army` WHERE `user`=".$gUser->id." $where ORDER BY `type`,`name` ASC","id");
		foreach ($troupbuildingtypes as $btypeid) {
			$mybuildings = sqlgettable("SELECT * FROM `building` WHERE `construction`=0 AND `user` = ".$gUser->id." AND `type` = ".$btypeid." ORDER BY `type`,`id`");
			foreach ($mybuildings as $building) {
				$units = cUnit::GetUnits($building->id,kUnitContainer_Building);
				foreach($units as $o)
					$totaltroups[$o->type] += $o->amount;
			}
		}
		foreach ($myArmies as $army) {
			$units = array_merge(cUnit::GetUnits($army->id),cUnit::GetUnits($army->id,kUnitContainer_Transport));
			foreach($units as $o)
				$totaltroups[$o->type] += $o->amount;
		}
		
	if ($f_armytype != -1) {
		?>
		<table border=1 cellspacing=0 rules="all">
		<tr>
			<th>Ort</th>
			<?php foreach($gUnitType as $o) if ($totaltroups[$o->id] > 0) {?>
				<th align="center"><img border=0 src="<?=g($gUnitType[$o->id]->gfx)?>"></th>
			<?php }?>
			<th nowrap><img align="middle" alt="food" src="<?=g("res_food.gif")?>">/h</th>
			<th>Frags</th>
			<th>Info</th>
		</tr>
		<?php 
		foreach ($troupbuildingtypes as $btypeid) {
			$mybuildings = sqlgettable("SELECT * FROM `building` WHERE `construction`=0 AND `user` = ".$gUser->id." AND `type` = ".$btypeid." ORDER BY `id`");
			$btype = $gBuildingType[$btypeid];
			foreach ($mybuildings as $building) {
				$units = cUnit::GetUnits($building->id,kUnitContainer_Building);
				$building_amount = cUnit::GetUnitsSum($units);
				$buildingactions = sqlgettable("SELECT * FROM `action` WHERE `building` = ".$building->id." ORDER BY `id`","id");
				
				if($f_showempty==0 && $building_amount<1)continue;
				
				if($building->level<20)
					$lpic="1";
				if($building->level<10)
					$lpic="0";
				else
					$lpic="1";
				?>
				<tr>
				<td nowrap>
					<?php $goto_url = query("info.php?sid=?&x=".$building->x."&y=".$building->y);?>
					<a href="<?=$goto_url?>">
					<img align="middle" src="<?=g($btype->gfx,($building->nwse=="?")?"ns":$building->nwse,$lpic)?>" border=1></a>
					<?=cText::Wiki("building",$btype->id)?> 
					<a href="<?=$goto_url?>"><?=$btype->name?></a>
					<?=opos2txt($building)?>
			
				</td>
				<?php foreach($gUnitType as $o) if ($totaltroups[$o->id] > 0) {
					$amount = cUnit::GetUnitsSum(cUnit::FilterUnitsType($units,$o->id));
					?>
					<td align="right"><?=$amount>=1?ktrenner(floor($amount)):""?></td>
				<?php }?>
				<td></td>
				<td></td>
				<td nowrap>
					<?php foreach ($buildingactions as $o) {?>
					<?=BuildingAction2Txt($o)?>
					<?php } // endforeach?>
					<?php $curtargetid = GetBParam($building->id,"target",0);?>
					<?=$curtargetid?("-&gt;".opos2txt(sqlgetobject("SELECT * FROM `building` WHERE `id` = ".$curtargetid))):""?>
				</td>
				</tr>
				<?php
			}
		}?>
		<?php 
		foreach ($myArmies as $army) {
			$fight = sqlgetobject("SELECT * FROM `fight` WHERE `attacker`=".$army->id." OR `defender`=".$army->id);
			$pillage = sqlgetobject("SELECT * FROM `pillage` WHERE `army`=".$army->id);
			$siege = sqlgetobject("SELECT * FROM `siege` WHERE `army`=".$army->id);
			$units = array_merge(cUnit::GetUnits($army->id),cUnit::GetUnits($army->id,kUnitContainer_Transport));
			$u = cUnit::GetUnitsEatSum($units);
			$eatsum += $u;
			$fragsum += $army->frags;
			$items = sqlgettable("SELECT * FROM `item` WHERE `army` = ".$army->id." OR (`army` = 0 AND `building` = 0 AND `x` = ".$army->x." AND `y` = ".$army->y.")ORDER BY `type`");
			$maxlast = floor(cUnit::GetUnitsSum($units,"last"));
			$load = cArmy::GetArmyTotalWeight($army);
			$load_percent = $maxlast?min(100,floor(100.0*$load/$maxlast)):0;
			?>
			<tr>
			<td nowrap><a href="<?=query("info.php?sid=?&x=".$army->x."&y=".$army->y)?>">
				<img style="background-color:#66AA55" align="middle" src="<?=g($gUnitType[cUnit::GetUnitsMaxType($units)]->gfx)?>" border=1>
				<?=($army->counttolimit==0)?"<img src='".g("icon/present.png")."' alt=Geschenk title=Geschenk border=0> ":""?><?=$army->name?></a>
				<?=opos2txt($army)?>
			</td>
			<?php foreach($gUnitType as $o) if ($totaltroups[$o->id] > 0) {
				$amount = cUnit::GetUnitsSum(cUnit::FilterUnitsType($units,$o->id));?>
				<td align="right"><?=$amount>=1?ktrenner(floor($amount)):""?></td>
			<?php }?>
			<td align="right"><?=$u?></td>
			<td align="right"><?=ktrenner(floor($army->frags))?></td>
			<td nowrap>
				<?php $wps = sqlgetone("SELECT COUNT(*) FROM `waypoint` WHERE `army` = ".$army->id);?>
				<?=$wps?("WP:".$wps):""?>
				<?=($fight?"Kampf":"")?>
				<?=($pillage?"Plünderung":"")?>
				<?=($siege?"Belagerung":"")?>
				<?php foreach ($items as $o) {?>
				<img border=0 title="<?=$gItemType[$o->type]->name?>" alt="<?=$gItemType[$o->type]->name?>" src="<?=g($gItemType[$o->type]->gfx)?>"><?=ktrenner($o->amount)?>
				<?php } // endforeach?>
				<?php foreach ($gRes as $n=>$f) if ($army->{$f} > 0) {?>
				<img border=0 alt="<?=$f?>" src="<?=g("res_$f.gif")?>"><?=ktrenner(floor($army->{$f}))?>
				<?php } // endforeach?>
				<?php ?>
				<?php if ($load_percent > 0) {?>
				Ausl.:<?=$load_percent?>%
				<?php } // endif?>
			</td>
			</tr>
			<?php
		}?>
		<tr>
			<th>Ort</th>
			<?php foreach($gUnitType as $o) if ($totaltroups[$o->id] > 0) {?>
				<th align="center"><img border=0 src="<?=g($gUnitType[$o->id]->gfx)?>"></th>
			<?php }?>
			<th nowrap><img align="middle" alt="food" src="<?=g("res_food.gif")?>">/h</th>
			<th>Frags</th>
			<th>Info</th>
		</tr>
		<tr>
			<th>Total</th>
			<?php foreach($gUnitType as $o) if ($totaltroups[$o->id] > 0) {?>
				<th align="right"><?=$totaltroups[$o->id]>=1?ktrenner(floor($totaltroups[$o->id])):0?></th>
			<?php }?>
			<th align="right"><?=ktrenner(ceil($eatsum))?></th>
			<th align="right"><?=ktrenner(floor($fragsum))?></th>
		</tr>
		</table>
		
		<?php 
	} //endif ?>

</body>
</html>
<?php profile_page_end(); ?>
