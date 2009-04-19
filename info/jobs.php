<?php
require_once("../lib.main.php");
Lock();

require_once("header.php"); 

if($gUser->admin == 1){

	$t = sqlgettable("SELECT `name`,COUNT(`id`) as `count` FROM `job` WHERE `starttime`=0 AND `endtime`=0 AND `locked`=0 GROUP BY `name`");
	echo "<h1>queued jobs</h1>";
	echo "<table><tr><th>name</th><th>count</th></td>";
	foreach($t as $x){
		echo "<tr><td>$x->name</td><td>$x->count</td></tr>";
	}
	echo "</table>";
	
	$t = sqlgettable("SELECT `name`,COUNT(`id`) as `count`, (SUM(`endtime`)-SUM(`starttime`)) as `time`, MIN(`starttime`) as `start`, MAX(`endtime`) as `end` FROM `joblog` GROUP BY `name`");
	echo "<h1>stats of finished jobs</h1>";
	echo "<table><tr><th>name</th><th>count</th><th>avg t in s</th><th>avg dt in s</th></td>";
	foreach($t as $x){
		echo "<tr>";
		echo "<td>$x->name</td><td>$x->count</td>";
		echo "<td>".round($x->time / $x->count,3)."</td>";
		echo "<td>".round(($x->end - $x->start) / $x->count,3)."</td>";
		echo "</tr>";
	}
	echo "</table>";
}

require_once("footer.php"); 
?>
