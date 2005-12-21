<?php

require_once("../lib.main.php");
$g = sqlgettable("SELECT * FROM `guild`","id");
$t = sqlgettable("SELECT `ip`,count(*) as `count` FROM `session` GROUP BY `ip` HAVING `count`>1");
foreach($t as $s){
	$tt = sqlgettable("SELECT u.* FROM `user` u,`session` s WHERE u.id=s.userid AND s.ip='$s->ip' GROUP BY u.`id`");
	if(sizeof($tt) <= 1)continue;
	echo "====[ $s->ip ($s->count times)]===========<br>";
	$lastagent = "";
	$lasttime = 0;
	$dt = 0;
	foreach($tt as $u){
		$agent = sqlgetone("SELECT `agent` FROM `session` WHERE `userid`=".intval($u->id)." AND `ip`='".addslashes($s->ip)."'");
		echo "<b>".($u->guild?"[".$g[$u->guild]->name."] ":"")."$u->name ($u->mail):</b> <b ".($agent==$lastagent?"style=\"color:red\"":"").">agent</b>=$agent <b>lastlogin</b>= ".(time()-$u->lastlogin)." sec ago<br>";
		$lastagent = $agent;
		if($lasttime > 0)$dt += abs($lasttime - (time() - $u->lastlogin));
		$lasttime = time() - $u->lastlogin;
	}
	if($dt > 5*60)echo "<b>dt</b>=$dt<br><br>";
	else echo "<b style=\"color:red\">dt</b>=$dt<br><br>";
}

?>
