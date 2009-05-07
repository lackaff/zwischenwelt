<?php
require_once("../lib.main.php");
require_once("../jobs/job.php");
Lock();

require_once("header.php"); 

if($gUser->admin == 1){
	if(isset($f_show)){
		$output = sqlgetone("SELECT `output` FROM `joblog` 
			WHERE `name`='".mysql_real_escape_string($f_show)."' AND LENGTH(`output`)>0
			ORDER BY `time` DESC LIMIT 1");
	}
	if(intval($f_kill) > 0){
		sql("UPDATE `job` SET `endtime`=".time().", `locked`=2 WHERE `id`=".intval($f_kill));
	}
	if(intval($f_run) > 0){
		Job::runJob(intval($f_run));		
	}
	if(intval($f_removeerror) > 0){
		sql("UPDATE `joblog` SET `error`=NULL WHERE `id`=".intval($f_removeerror));
	}
	if(intval($f_removeallerrors) > 0){
		$o = sqlgetobject("SELECT * FROM `joblog` WHERE `id`=".intval($f_removeallerrors));
		if($o !== false){
			sql("UPDATE `joblog` SET `error`=NULL WHERE 
				`name`='".mysql_real_escape_string($o->name)."' AND
				`error`='".mysql_real_escape_string($o->error)."'");
		}
	}
	
	$errors = sqlgetone("SELECT COUNT(*) FROM `joblog` WHERE `error` IS NOT NULL");
	
	echo "<center>
		<a href=\"".Query("?sid=?")."\">reload</a> | 
		<a href=\"#errors\">errors ($errors)</a> |
		<a href=\"#stats\">stats</a>
	</center>";
	
	if(isset($output)){
		echo "<hr><pre>$output</pre><hr>";
	}
	
	echo "<table><tr><td valign=\"top\">";
	
	$t = sqlgettable("SELECT `name`,COUNT(`id`) as `count` FROM `job` WHERE 
		`locked`=0 GROUP BY `name`");
	echo "<h1>queued jobs</h1>";
	echo "<table border=1><tr><th>name</th><th>count</th></tr>";
	foreach($t as $x){
		echo "<tr><td><a href=\"".Query("?show=$x->name&sid=?")."\">$x->name</a></td><td>$x->count</td></tr>";
	}
	echo "</table>";
	
	echo "</td><td valign=\"top\">";
	
	$t = sqlgettable("SELECT `id`,`name`,`time` FROM `job` WHERE `time`>".time()." AND `locked`=0 ORDER BY `time` ASC");
	echo "<h1>next jobs</h1>";
	echo "<table border=1><tr><th>name</th><th>time left in s</th></tr>";
	foreach($t as $x){
		echo "<tr>";
		echo "<td>$x->name</td>";
		echo "<td>".round($x->time - time())."</td>";
		echo "<td><a href=\"".Query("?kill=$x->id&sid=?")."\">kill</a></td>";
		echo "<td><a href=\"".Query("?run=$x->id&sid=?")."\">run</a></td>";
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
		$avgt = sqlgetone("SELECT AVG(`endtime`-`starttime`) FROM `joblog` 
			WHERE `name`='".mysql_real_escape_string($x->name)."'");
		$p = (time() - $x->starttime) / $avgt;
		
		echo "<tr>";
		echo "<td>$x->id</td>";
		echo "<td>$x->name</td>";
		echo "<td>".round(time() - $x->starttime)."</td>";
		echo "<td>".round(100 * $p)."%<br>";
		DrawBar(min(1.0,$p),1);
		echo "</td>";
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
		echo "<td><a href=\"".Query("?run=$x->id&sid=?")."\">run</a></td>";
		echo "</tr>";
	}
	echo "</table>";
	
	echo "</td></tr><td colspan=3 valign=\"top\">";
	
	$t = sqlgettable("SELECT `name`,COUNT(`id`) as `count`, (SUM(`endtime`)-SUM(`starttime`))/COUNT(`id`) as `avg`, (SUM(`endtime`)-SUM(`starttime`)) as `time`, MIN(`starttime`) as `start`, MAX(`endtime`) as `end` FROM `joblog` GROUP BY `name` ORDER BY `avg` DESC");
	echo "<h1><a name=\"stats\">stats of finished jobs</a></h1>";
	echo "<table border=1><tr><th>name</th><th>count</th><th>avg t in s</th><th>avg dt in s</th><th>errors</th></tr>";
	foreach($t as $x){
		echo "<tr>";
		echo "<td>$x->name</td><td>$x->count</td>";
		echo "<td>".round($x->time / $x->count,1)."</td>";
		echo "<td>".round(($x->end - $x->start) / $x->count,1)."</td>";
		echo "<td>".sqlgetone("SELECT COUNT(`id`) FROM `joblog` WHERE 
			`error` IS NOT NULL AND `name`='".mysql_real_escape_string($x->name)."'")."</td>";
		echo "</tr>";
	}
	echo "</table>";

	echo "</td></td></table>";
	
	$t = sqlgettable("SELECT * FROM `joblog` WHERE `error` IS NOT NULL ORDER BY `time` DESC LIMIT 15");
	echo "<h1><a name=\"errors\">jobs with errors and warnings</a></h1>";
	echo "<table border=1><tr><th>id</th><th>name</th><th>time</th><th>error/warning</th></tr>";
	foreach($t as $x){
		echo "<tr>";
		echo "<td>$x->id</td><td>$x->name</td>";
		echo "<td>".date("r",$x->time)."</td>";
		echo "<td><pre>".str_replace("|","\n",$x->error)."</pre></td>";
		echo "<td><a href=\"".Query("?removeerror=$x->id&sid=?")."\">remove</a><br><a href=\"".Query("?removeallerrors=$x->id&sid=?")."\">remove all</a></td>";
		echo "</tr>";
		echo "<tr><td colspan=5><pre>$x->output</pre></td></tr>";
	}
	echo "</table>";

	echo "</td></td></table>";
}

require_once("footer.php"); 
?>
