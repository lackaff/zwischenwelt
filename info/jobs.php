<?php
require_once("../lib.main.php");
Lock();

require_once("header.php"); 

if($gUser->admin == 1){
	if(intval($f_kill) > 0){
		sql("UPDATE `job` SET `endtime`=".time().", `locked`=2 WHERE `id`=".intval($f_kill));
	}
	if(intval($f_removeerror) > 0){
		sql("UPDATE `joblog` SET `error`=NULL WHERE `id`=".intval($f_removeerror));
	}
	echo "<table><tr><td valign=\"top\">";
	
	$t = sqlgettable("SELECT `name`,COUNT(`id`) as `count` FROM `job` WHERE 
		`locked`=0 GROUP BY `name`");
	echo "<h1>queued jobs</h1>";
	echo "<table border=1><tr><th>name</th><th>count</th></tr>";
	foreach($t as $x){
		echo "<tr><td>$x->name</td><td>$x->count</td></tr>";
	}
	echo "</table>";
	
	echo "</td><td valign=\"top\">";
	
	$t = sqlgettable("SELECT `name`,`time` FROM `job` WHERE `time`>".time()." AND `locked`=0 ORDER BY `time` ASC");
	echo "<h1>next jobs</h1>";
	echo "<table border=1><tr><th>name</th><th>time left in s</th></tr>";
	foreach($t as $x){
		echo "<tr>";
		echo "<td>$x->name</td>";
		echo "<td>".round($x->time - time())."</td>";
		echo "<td><a href=\"".Query("?kill=$x->id&sid=?")."\">kill</a></td>";
		echo "</tr>";
	}
	echo "</table>";
	
	echo "</td><td valign=\"top\">";
	
	$t = sqlgettable("SELECT `id`,`name`,`time`,`starttime`,`endtime` FROM `job` WHERE 
		`locked`=1");
	echo "<h1>running jobs</h1>";
	echo "<table border=1><tr>";
	echo "<th>id</th><th>name</th><th>running t in s</th></tr>";
	foreach($t as $x){
		echo "<tr>";
		echo "<td>$x->id</td>";
		echo "<td>$x->name</td>";
		echo "<td>".round(time() - $x->starttime)."</td>";
		echo "<td><a href=\"".Query("?kill=$x->id&sid=?")."\">kill</a></td>";
		echo "</tr>";
	}
	echo "</table>";
	
	$t = sqlgettable("SELECT `id`,`name`,`time`,`locked`,`starttime`,`endtime`,`tries` FROM `job` 
		WHERE 
			`time`<".time()." AND 
			`locked`=0 ORDER BY `time` ASC");
	echo "<h1>overdue jobs</h1>";
	echo "<table border=1><tr><th>name</th><th>time in s</th><th>locked</th><th>start</th><th>end</th><th>tries</th></tr>";
	foreach($t as $x){
		echo "<tr>";
		echo "<td>$x->name</td>";
		echo "<td>".round($x->time - time())."</td>";
		echo "<td>$x->locked</td>";
		echo "<td>$x->starttime</td>";
		echo "<td>$x->endtime</td>";
		echo "<td>$x->tries</td>";
		echo "<td><a href=\"".Query("?kill=$x->id&sid=?")."\">kill</a></td>";
		echo "</tr>";
	}
	echo "</table>";
	
	echo "</td></tr><td colspan=3 valign=\"top\">";
	
	$t = sqlgettable("SELECT `name`,COUNT(`id`) as `count`, (SUM(`endtime`)-SUM(`starttime`)) as `time`, MIN(`starttime`) as `start`, MAX(`endtime`) as `end` FROM `joblog` GROUP BY `name`");
	echo "<h1>stats of finished jobs</h1>";
	echo "<table border=1><tr><th>name</th><th>count</th><th>avg t in s</th><th>avg dt in s</th></tr>";
	foreach($t as $x){
		echo "<tr>";
		echo "<td>$x->name</td><td>$x->count</td>";
		echo "<td>".round($x->time / $x->count,3)."</td>";
		echo "<td>".round(($x->end - $x->start) / $x->count,3)."</td>";
		echo "</tr>";
	}
	echo "</table>";

	echo "</td></td></table>";
	
	$t = sqlgettable("SELECT * FROM `joblog` WHERE `error` IS NOT NULL ORDER BY `time` DESC");
	echo "<h1>jobs with errors and warnings</h1>";
	echo "<table border=1><tr><th>id</th><th>name</th><th>time</th><th>error/warning</th></tr>";
	foreach($t as $x){
		echo "<tr>";
		echo "<td>$x->id</td><td>$x->name</td>";
		echo "<td>".date("r",$x->time)."</td>";
		echo "<td><pre>".str_replace("|","\n",$x->error)."</pre></td>";
		echo "<td><a href=\"".Query("?removeerror=$x->id&sid=?")."\">remove</a></td>";
		echo "</tr>";
		echo "<tr><td colspan=5><pre>$x->output</pre></td></tr>";
	}
	echo "</table>";

	echo "</td></td></table>";
}

require_once("footer.php"); 
?>
