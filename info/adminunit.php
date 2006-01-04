<?php

require_once("../lib.main.php");
require_once("../lib.building.php");
require_once("../lib.army.php");
require_once("../lib.map.php");

AdminLock();

if (isset($f_add)) {
	if ($f_containertype == kUnitContainer_Building)
			$containeruser = sqlgetone("SELECT `user` FROM `building` WHERE `id` = ".intval($f_containerid)." LIMIT 1");
	else	$containeruser = sqlgetone("SELECT `user` FROM `army` WHERE `id` = ".intval($f_containerid)." LIMIT 1");
	cUnit::AddUnits($f_containerid,$f_add,1,$f_containertype,$containeruser);
}

if (isset($f_save)) {
	foreach ($f_unit as $id => $arr) {
		if ($arr["amount"] > 0)
				sql("UPDATE `unit` SET ".arr2sql(array("amount"=>$arr["amount"],"user"=>$arr["user"],"spell"=>$arr["spell"]))." WHERE `id` = ".intval($id));
		else	sql("DELETE FROM `unit` WHERE `id` = ".intval($id));
	}
}

if (isset($f_search_stuckarmy)) {
	$armies = sqlgettable("SELECT * FROM `army` WHERE `user` > 0");
	foreach ($armies as $army) {
		$army->units = cUnit::GetUnits($army->id);
		$speed = cArmy::GetPosSpeed($army->x,$army->y,$army->user,$army->units,false); 
		if ($speed <= 0) echo opos2txt($army)." speed=$speed<br>";
		
	}
}

if (isset($f_repair_unit_owner)) {
	$arr = sqlgettable("SELECT * FROM `unit` WHERE `user` = 0");
	echo "repair_unit_owner : ".count($arr)."<br>";
	foreach ($arr as $o) {
		if ($o->army)
			$user = sqlgetone("SELECT `user` FROM `army` WHERE `id` = ".intval($o->army)." LIMIT 1");
		else if ($o->transport)
			$user = sqlgetone("SELECT `user` FROM `army` WHERE `id` = ".intval($o->transport)." LIMIT 1");
		else if ($o->building)
			$user = sqlgetone("SELECT `user` FROM `building` WHERE `id` = ".intval($o->building)." LIMIT 1");
		if ($user) {
			$merge = sqlgetobject("SELECT * FROM `unit` WHERE `type` = ".$o->type." AND `spell` = ".$o->spell." AND `user` = ".intval($user));
			if ($merge) {
				sql("UPDATE `unit` SET `amount` = `amount` + ".$o->amount." WHERE `id` = ".$merge->id." LIMIT 1");
				sql("DELETE FROM `unit` WHERE `id` = ".$o->id." LIMIT 1");
			} else sql("UPDATE `unit` SET `user` = ".intval($user)." WHERE `id` = ".$o->id." LIMIT 1");
		}
	}
}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 transitional//EN"
   "http://www.w3.org/TR/html4/transitional.dtd">
<html>
<head>
<link rel="stylesheet" type="text/css" href="../styles.css">
<link rel="stylesheet" type="text/css" href="<?=GetZWStylePath()?>">
<title>Zwischenwelt - Armee Administration</title>
</head>
<body>
<?php 

include("../menu.php");

$units = cUnit::GetUnits($f_containerid,$f_containertype);
$building = ($f_containertype==kUnitContainer_Building)?sqlgetobject("SELECT * FROM `building` WHERE `id` = ".intval($f_containerid)):false;
$army = ($f_containertype!=kUnitContainer_Building)?sqlgetobject("SELECT * FROM `army` WHERE `id` = ".intval($f_containerid)):false;
?>

<form method="post" action="<?=Query("?sid=?&containerid=?&containertype=?")?>">
<table>
	<tr><th></th><th>amount</th><th>user</th><th>spell</th></tr>
	<?php foreach ($units as $o) { $ut = $gUnitType[$o->type];?>
	<tr>
		<th><img src="<?=g($ut->gfx)?>"></th>
		<td><input type="text" name="unit[<?=$o->id?>][amount]" value="<?=ceil($o->amount)?>"></td>
		<td><input type="text" name="unit[<?=$o->id?>][user]" value="<?=$o->user?>"></td>
		<td><input type="text" name="unit[<?=$o->id?>][spell]" value="<?=$o->spell?>"></td>
		<?php if ($o->spell && ($spell = sqlgetobject("SELECT * FROM `spell` WHERE `id` = ".intval($o->spell)))) {?>
			<td>
			<?=$gSpellType[$spell->type]->name?> von
			<?=sqlgetone("SELECT `name` FROM `user` WHERE `id` = ".intval($spell->owner))?>, 
			noch <?=Duration2Text($spell->lasts - time())?>
			</td>
		<?php } // endif?>
	</tr>
	<?php } // endforeach?>
</table>
<input type="submit" name="save" value="speichern">
</form>

add :
<?php foreach ($gUnitType as $ut) if (1 || ($building && $ut->buildingtype == $building->type) || 
										($army && $ut->armytype == $army->type)) {?>
<a href="<?=Query("?sid=?&containerid=?&containertype=?&add=".$ut->id)?>"><img src="<?=g($ut->gfx)?>" border=0></a>
<?php } // endforeach?>

<br>
<a href="<?=Query("?sid=?&containerid=?&containertype=?&repair_unit_owner=1")?>">repair_unit_owner</a><br>
<a href="<?=Query("?sid=?&containerid=?&containertype=?&search_stuckarmy=1")?>">search_stuckarmy</a><br>

</body>
</html>