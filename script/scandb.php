<?php

require_once("../lib.php");
require_once("../lib.building.php");

//$list = Array("terrain","army","building");
$list = Array("army");

foreach($list as $t)
{
	echo "scanning for doubles in `$t`...<br>";
	$r = sql("SELECT `x`,`y` FROM `$t`");
	while($row = mysql_fetch_row($r))
	{
		$x = $row[0];
		$y = $row[1];
		$tbl = sqlgettable("SELECT `id` FROM `$t` WHERE `x`=$x AND `y`=$y");
		$i = mysql_affected_rows();
		if($i > 1){
			echo " - $i things at ($x|$y)<br>";
			echo " + removing low id rows<br>";
			$max = 0;
			foreach($tbl as $row)$max = max($max,$row->id);
			sql("DELETE FROM `$t` WHERE `x`=$x AND `y`=$y AND `id`<$max");
		}
	}
	echo "done<br><br>";
}

echo "scanning for actions without buildings ...<br>";
$t = sqlgettable("SELECT * FROM `action`");
foreach($t as $a)
{
	$b = sqlgetobject("SELECT * FROM `building` WHERE `id`=".$a->building);
	if(empty($b))echo "action ".$a->id." has no building<br>";
}
echo "done<br><br>";


if(isset($f_delid))foreach($f_delid as $id=>$v){
	$id = intval($id);
	sql("DELETE FROM `user` WHERE `id`=$id");
	$b = sqlgetobject("SELECT * FROM `building` WHERE `type`=1 AND `user`=$id");
	if($b)cBuilding::removeBuilding($b,$id,false,false);
	echo "user $id deleted.<br>";
}

echo "<form method=post action=?do=del>";
$blimit = 30;
echo "scanning for players with less than $blimit buildings ...<br>";
$t = sqlgettable("SELECT * FROM `user` WHERE `dont_delete`=0");
foreach($t as $u)
{
	$b = sqlgetone("SELECT COUNT(*) FROM `building` WHERE `user`=".$u->id);
	$c = sqlgetone("SELECT COUNT(*) FROM `construction` WHERE `user`=".$u->id);
	$dt = floor((time() - $u->lastlogin)/60/60/24);
	$dtr = floor((time() - $u->registered)/60/60/24);
	
	if($dt > 30 && ($dt < 13000 || $dtr > 30)){
		$checked = "checked";
		$color = "red";
	} else {
		$checked = "";
		$color = "black";
	}
	
	if($b<$blimit)echo "<span style='color:$color;'> <input value=1 type=checkbox name=\"delid[$u->id]\" $checked> user ".$u->id." '".$u->name."' has <b>$b</b> buildings, <b>$c</b> constructions, <b>$u->logins</b> logins and last login was <b>$dt</b> days ago (registered <b>$dtr</b> days ago)</span><br>";
}
echo "done<br><br>";
echo "<input type=submit value=delete></form>";
?>
