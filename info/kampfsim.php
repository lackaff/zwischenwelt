<?php
require_once("../lib.main.php");
require_once("../lib.fight.php");
Lock();
profile_page_start("kampfsim");

$simunittypes = array();
$techids_a = array();
$techids_v = array();

$techbuffer = array(1=>array(),2=>array());

$army = isset($f_army)?sqlgetobject("SELECT * FROM `army` WHERE `id` = ".intval($f_army)):false;
if (!$gUser->admin && $army->user != $gUser->id) $army = false;
if ($army) $f_frags[1] = intval($army->frags);

// turmzauberer, ramme, schatzkiste nicht
foreach ($gUnitType as $o)  {
	if ($o->id != kUnitType_TowerMage && $o->id != kUnitType_Ramme && $o->id != kUnitType_Schatzkiste && $o->gfx) {
		$simunittypes[] = $o;
		$req = ParseReqForATechLevel($o->req_tech_a);
		foreach ($req as $key => $val) if (!$val->ismax) $techids_a[] = $key;
		$req = ParseReqForATechLevel($o->req_tech_v);
		foreach ($req as $key => $val) if (!$val->ismax) $techids_v[] = $key;
	}
}

// user techs holen
$techids = array_unique(array_merge($techids_a,$techids_v));

$simtechtypes = array();
foreach ($techids as $tid) {
	$simtechtypes[] = $gTechnologyType[$tid];
	$techbuffer[1][$tid] = isset($f_tech[1][$tid])?$f_tech[1][$tid]:GetTechnologyLevel($tid,($army)?($army->user):0);
	$techbuffer[2][$tid] = isset($f_tech[2][$tid])?$f_tech[2][$tid]:0;
}

// units in den armeen und gebäuden des users zählen
if (!isset($f_units[1])) if ($army) {
	$f_units[1] = AF(cUnit::GetUnits($army->id),"amount","type");
	array_walk($f_units[1],"walkint");
} else {
	$f_units[1] = sqlgettable("SELECT SUM(`amount`) as `c`,u.`type` as `type` FROM `unit` u,`army` a	
	WHERE u.`army` = a.`id` AND a.`user` = ".$gUser->id." GROUP BY u.`type`","type","c");
	$buildingunits = sqlgettable("SELECT SUM(`amount`) as `c`,u.`type` as `type` FROM `unit` u,`building` b	
	WHERE u.`building` = b.`id` AND b.`user` = ".$gUser->id." GROUP BY u.`type`","type","c");
	foreach ($buildingunits as $type => $num)
		$f_units[1][$type] = intval((isset($f_units[1][$type])?$f_units[1][$type]:0)+$num);
}

// override for techlevel
$gTechnologyLevelsOfAllUsers = $techbuffer; // from here on GetTechnologyLevel uses this as cache
		
$armyname = array(1=>"eigene",2=>"Feind");

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 transitional//EN"
   "http://www.w3.org/TR/html4/transitional.dtd">
<html>
<head>
<link rel="stylesheet" type="text/css" href="../styles.css">
<link rel="stylesheet" type="text/css" href="<?=GetZWStylePath()?>">
<title>Zwischenwelt - Kampfsim</title>
</head>
<body>
<?php
include("../menu.php");
?>

<form method="post" action="<?=Query("?sid=?")?>">
	<?php /* units header */ ?>
	<table cellpadding=0 cellspacing=0>
	<tr>
	<?php foreach ($simunittypes as $o) {?>
		<th><img alt="<?=$o->name?>" title="<?=$o->name?>" class="picframe" src="<?=g($o->gfx)?>"></th>
	<?php }?>
	</tr><tr>
	<?php foreach ($simunittypes as $o) {?><th><?=$o->a?></th><?php }?>	<td>A</td>
	</tr><tr>
	<?php foreach ($simunittypes as $o) {?><th><?=$o->v?></th><?php }?>	<td>V</td>
	</tr><tr>
	<?php foreach ($simunittypes as $o) {?><th><?=$o->f?></th><?php }?>	<td>F</td>
	</tr><tr>
	
	<?php /* units */ ?>
	<?php for ($i=1;$i<=2;$i++) {?>
		<?php foreach ($simunittypes as $o) {?>
			<td><INPUT TYPE="text" NAME="units[<?=$i?>][<?=$o->id?>]" VALUE="<?=isset($f_units[$i][$o->id])?intval($f_units[$i][$o->id]):0?>" style="width:35px"></td>
		<?php }?>
		<td><?=$armyname[$i]?></td>
		</tr><tr>
	<?php }?>
	</tr>
	</table>
	(<?=cText::Wiki("Kämpfen",0,true)?>Seekampf funktioniert nach anderen Regeln, hierfür gibt es keinen Simulator )
	
	<h4>Techs</h4>
	<table><tr><td valign="top">
		<table border=1 cellpadding=0 cellspacing=0>
		<?php /* techs header */ ?>
		<?php foreach ($simtechtypes as $o) {?>
			<th><img alt="<?=$o->name?>" title="<?=$o->name?>" class="picframe" src="<?=g($o->gfx)?>"></th>
		<?php }?>
		</tr><tr>
		<?php /* techs */ ?>
		<?php for ($i=1;$i<=2;$i++) {?>
			<?php foreach ($simtechtypes as $o) {?>
				<td><INPUT TYPE="text" NAME="tech[<?=$i?>][<?=$o->id?>]" VALUE="<?=$gTechnologyLevelsOfAllUsers[$i][$o->id]?>" style="width:30px"></td>
			<?php }?>
			<td><?=$armyname[$i]?></td>
			</tr><tr>
		<?php }?>
		
		<?php /* tech-affected units */ ?>
		<?php if (isset($f_techsplit)) {?>
			<td colspan=<?=count($simtechtypes)+2?> height=1 bgcolor="black"></td>
			</tr><tr>
			<?php foreach ($simtechtypes as $tech) {?>
				<td valign=top>			
				<?php foreach ($simunittypes as $o) { $req = array_keys(ParseReqForATechLevel($gUnitType[$o->id]->req_tech_a)); if (in_array($tech->id,$req)) { ?>
					<img alt="<?=$o->name?>" title="<?=$o->name?>" class="picframe" src="<?=g($o->gfx)?>"><br>
				<?php } }?>
				</td>
			<?php }?>	
			<td valign="top" colspan=2>AngriffsBonus</td>
			</tr><tr>
			<td colspan=<?=count($simtechtypes)+2?> height=1 bgcolor="black"></td>
			</tr><tr>
			<?php foreach ($simtechtypes as $tech) {?>
				<td valign=top>			
				<?php foreach ($simunittypes as $o) { $req = array_keys(ParseReqForATechLevel($gUnitType[$o->id]->req_tech_v)); if (in_array($tech->id,$req)) { ?>
					<img alt="<?=$o->name?>" title="<?=$o->name?>" class="picframe" src="<?=g($o->gfx)?>"><br>
				<?php } }?>
				</td>
			<?php }?>	
			<td valign="top" colspan=2>VerteidigungsBonus</td>
			</tr>
		<?php } else {?>
			<td colspan=<?=count($simtechtypes)+2?> height=1 bgcolor="black"></td>
			</tr><tr>
			<?php foreach ($simtechtypes as $tech) {?>
				<td valign=top>			
				<?php foreach ($simunittypes as $o) { 
					$reqa = array_keys(ParseReqForATechLevel($gUnitType[$o->id]->req_tech_a)); 
					$reqv = array_keys(ParseReqForATechLevel($gUnitType[$o->id]->req_tech_v)); 
					if (in_array($tech->id,$reqa) || in_array($tech->id,$reqv)) { ?>
					<img alt="<?=$o->name?>" title="<?=$o->name?>" class="picframe" src="<?=g($o->gfx)?>"><br>
				<?php } }?>
				</td>
			<?php }?>	
			<td valign="top" colspan=2>TechBonus</td>
			</tr>
		<?php }?>
		</table>
		<input type="checkbox" name="showverlauf" value="1" <?=isset($f_showverlauf)?"checked":""?>>verlauf anzeigen
		<input type="submit" name="calc" value="berechnen">	
		
	</td><td valign="top">
	
		<h4>Armeen</h4>
		<?php
			$where = $gUser->admin ? "`army`.`user` > 0" : ("`army`.`user` = ".$gUser->id);
			$armylist = sqlgettable("SELECT *,`army`.`id` as `id`,SUM(`amount`) as `size`,`army`.`user` as `user` FROM `army`,`unit` 
				WHERE $where AND `army`.`id` = `unit`.`army` AND `unit`.`type` = ".kArmyType_Normal." GROUP BY `army` ORDER BY `size` DESC");
			if ($gUser->admin) $armyusers = sqlgettable("SELECT * FROM `user`","id");
		?>
		<table>
		<?php foreach ($armylist as $o) {?>
			<tr>
			<td><a href="<?=Query("?sid=?&army=".$o->id)?>"><?=$o->name?></a></td>
			<td><?=pos2txt($o->x,$o->y)?></td>
			<?=($gUser->admin)?("<td>".$armyusers[$o->user]->name."</td>"):""?>
			<td><?=floor($o->size)?></td>
			</tr>
		<?php }?>
		</table>
	</td></tr></table>
	
	
</form>

<?php 
// kampfberechnung
if (isset($f_calc)) { 
	$army1 = false;
	$army2 = false;
	$army1->type = kArmyType_Normal;
	$army2->type = kArmyType_Normal;
	$army1->user = 1;
	$army2->user = 2;
	$army1->fightcount = 1;
	$army2->fightcount = 1;
	$army1->vorherfrags = 0;
	$army2->vorherfrags = 0;
	$army1->frags = 0;
	$army2->frags = 0;
	$army1->units = array();
	$army2->units = array();
	foreach ($f_units[1] as $key => $val) $army1->units[] = arr2obj(array("type"=>$key,"amount"=>$val));
	foreach ($f_units[2] as $key => $val) $army2->units[] = arr2obj(array("type"=>$key,"amount"=>$val));
	$army1->size = cUnit::GetUnitsSum($army1->units);
	$army2->size = cUnit::GetUnitsSum($army2->units);
	$army1->anfang_units = $army1->units;
	$army2->anfang_units = $army2->units;
	$armies = array(1=>$army1,2=>$army2);
	
	echo "<hr><h3>Stärke</h3>";

	?>
	<?php /* stärke */ ?>
	<?php for ($i=1;$i<=2;$i++) {?>	
		<?=$armyname[$i]?><br>
		insgesamt <?=cUnit::GetUnitsSum($armies[$i]->units)?> Einheiten<br>
		Chaos Faktor : <?=round(100.0-100.0*cUnit::GetUnitsChaosFaktor($armies[$i]->units),0)?>%
		<table border="1" cellpadding=2 cellspacing=0>
		<tr>		
			<?php foreach ($simunittypes as $o) {?>
				<th><img alt="<?=$o->name?>" title="<?=$o->name?>" class="picframe" src="<?=g($o->gfx)?>"></th>
			<?php }?>
			<td>Total</td>
		</tr><tr>	
			<?php foreach ($simunittypes as $o) foreach ($armies[$i]->units as $u) if ($u->type == $o->id) {?>
				<th align="right"><?=$u->amount?></th>
			<?php }?>
			<th align="right"><?=floor(cUnit::GetUnitsSum($armies[$i]->units))?></th>
			<td>Einheiten</td>
		</tr><tr>
			<?php foreach ($simunittypes as $o) {?><td align="right"><?=$o->a?></td><?php }?>	
			<td align="right"><?=floor(cUnit::GetUnitsSum($armies[$i]->units,"a"))?></td>
			<td>A</td>
		</tr><tr>
			<?php foreach ($simunittypes as $o) {?>
				<td align="right"><?=floor(cUnit::GetUnitBonus($o->id,$armies[$i]->user,"a"))?></th>
			<?php }?>
			<td align="right"><?=floor(cUnit::GetUnitsBonusSum($armies[$i]->units,$armies[$i]->user,"a"))?></td>
			<td>TechBonus</td>
		</tr><tr>
			<?php foreach ($simunittypes as $o) {?><td align="right"><?=$o->v?></td><?php }?>
			<td align="right"><?=round(cUnit::GetUnitsSum($armies[$i]->units,"v"),0)?></td>
			<td>V</td>
		</tr><tr>
			<?php foreach ($simunittypes as $o) {?>
				<td align="right"><?=floor(cUnit::GetUnitBonus($o->id,$armies[$i]->user,"v"))?></th>
			<?php }?>
			<td align="right"><?=cUnit::GetUnitsBonusSum($armies[$i]->units,$armies[$i]->user,"v")?></td>
			<td>TechBonus</td>
		</tr>
		</table>
		<br>
	<?php }?>
	<?php
	
	echo "<h3>Kampfbericht</h3>";
	$showverlauf = isset($f_showverlauf)?true:false;
	
	$army1->anfang_units = $army1->units;
	$army2->anfang_units = $army2->units;
	for ($runde=0;$army1->size > 1.0 && $army2->size > 1.0;$runde++) {
		/* ***** FIGHT_ROUND_START ***** */
		$m1 = array("a"=>1.0,"v"=>1.0,"f"=>1.0);
		$m2 = array("a"=>1.0,"v"=>1.0,"f"=>1.0);
		cFight::FightCalcStep($army1,$army2,$m1,$m2,array(),$showverlauf);
		/* ***** FIGHT_ROUND_END ***** */
	}
	$army1->lost_units = cUnit::GroupUnits(cUnit::GetUnitsDiff($army1->anfang_units,$army1->units,true));
	$army2->lost_units = cUnit::GroupUnits(cUnit::GetUnitsDiff($army2->anfang_units,$army2->units,true));
	
	/*
	cText::UnitsList($army1->anfang_units,0,"",false);
	cText::UnitsList($army1->lost_units,0,"",false);
	cText::UnitsList($army1->units,0,"",false);
	*/
		
	$armies = array(1=>$army1,2=>$army2);
	?>
	Dauer : <?=$runde?> Minuten, Sieger : <?=($army1->size > 1.0)?$armyname[1]:$armyname[2]?><br>
	Gewonnene Erfahrung <?=$armyname[1]?> : <?=ceil($army1->frags-$army1->vorherfrags)?><br>
	Gewonnene Erfahrung <?=$armyname[2]?> : <?=ceil($army2->frags-$army2->vorherfrags)?><br>
	Verluste :<br>
	<table border="1" cellpadding=2 cellspacing=0>
	<?php /* units header */ ?>
	<tr>
	<?php foreach ($simunittypes as $o) {?>
		<th><img alt="<?=$o->name?>" title="<?=$o->name?>" class="picframe" src="<?=g($o->gfx)?>"></th>
	<?php }?>
	</tr><tr>
	<?php /* units */ ?>
	<?php for ($i=1;$i<=2;$i++) {?>
		<?php foreach ($simunittypes as $o) {?>
			<?php $amount = 0; foreach ($armies[$i]->lost_units as $u) if ($u->type == $o->id) $amount += $u->amount; ?>
			<td align="right"><?=(ceil($amount)!=0)?ktrenner(ceil($amount)):""?></td>
		<?php }?>
		<td><?=$armyname[$i]?></td>
		</tr><tr>
	<?php }?>
	</tr>
	</table>
	<?php
}
?>

</body>
</html>
<?php profile_page_end(); ?>
