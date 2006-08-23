<?php

include("lib.php");

$db1 = "zw";
$db2 = "zw_dev";

function ListMoreKeys($base,$test){
	$more = array();
	foreach($base as $k=>$v)if(!isset($test[$k]))$more[] = $k;
	return $more;
}

function ListCommonKeys($base,$test){
	$common = array();
	foreach($base as $k=>$v)if(isset($test[$k]))$common[] = $k;
	return $common;
}

function CmpObjMembers($a,$b){
	$la = obj2arr($a);
	$lb = obj2arr($b);
	foreach($la as $k=>$v){
		if($lb[$k] != $v)return false;
	}
	return true;
}

function ShowFieldDiff($c1,$c2){
	if(!CmpObjMembers($c1,$c2)){
		$l = obj2arr($c1);
		?>
		<table border=1>
		<tr><?php foreach($l as $x=>$v)echo "<th>$x</th>"; ?></tr>
		<tr><?php foreach($l as $x=>$v)echo "<td>".($c1->$x)."</td>"; ?></tr>
		<tr><?php foreach($l as $x=>$v)echo "<td>".($c2->$x)."</td>"; ?></tr>
		</table>
		<?php
	}
}

function ShowTableDiff($db1,$db2,$tbl){
	$cols1s = sqlgettable("SHOW FULL COLUMNS FROM `".addslashes($db1)."`.`".addslashes($tbl)."`","Field");
	$cols2s = sqlgettable("SHOW FULL COLUMNS FROM `".addslashes($db2)."`.`".addslashes($tbl)."`","Field");
	
	$m1 = ListMoreKeys($cols1s,$cols2s);
	$m2 = ListMoreKeys($cols2s,$cols1s);
	
	echo "<h1>$tbl</h1>";
	if(sizeof($m1)>0)echo "Felder mehr in <b>$db1.$tbl</b>: ".implode($m1,", ")."<br>";
	if(sizeof($m2)>0)echo "Felder mehr in <b>$db2.$tbl</b>: ".implode($m2,", ")."<br>";
	
	$c = ListCommonKeys($cols1s,$cols2s);
	foreach($c as $k=>$x){
		ShowFieldDiff($cols1s[$x],$cols2s[$x]);
	}
}

$tbl1s = array_flip(sqlgetonetable("SHOW TABLES FROM `".addslashes($db1)."`"));
$tbl2s = array_flip(sqlgetonetable("SHOW TABLES FROM `".addslashes($db2)."`"));

$m1 = ListMoreKeys($tbl1s,$tbl2s);
$m2 = ListMoreKeys($tbl2s,$tbl1s);
echo "<p>";
if(sizeof($m1)>0)echo "Tabellen mehr in <b>$db1.$tbl</b>: ".implode($m1,", ")."<br>"; 
if(sizeof($m2)>0)echo "Tabellen mehr in <b>$db2.$tbl</b>: ".implode($m2,", ")."<br>"; 
echo "</p>";

foreach($tbl1s as $t1=>$v){
	if(isset($tbl2s[$t1]))ShowTableDiff($db1,$db2,$t1);
}

?>