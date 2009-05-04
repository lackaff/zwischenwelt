<?php

require_once("lib.main.php");
require_once("lib.army.php");

$time = time();

//--------------------------------
//--system stats------------------
//--------------------------------

//chat
$o = new EmptyObject();
$o->time = $time;
$o->type = kStats_SysInfo_Chat;
$o->i1 = sqlgetone("SELECT COUNT(1) FROM `message`");
$o->i2 = sqlgetone("SELECT COUNT(1) FROM `guild_forum`")+sqlgetone("SELECT COUNT(1) FROM `guild_forum_comment`");
sql("INSERT INTO `stats` SET ".obj2sql($o));

//magic
$o = new EmptyObject();
$o->time = $time;
$o->type = kStats_SysInfo_Magic;
$o->i1 = sqlgetone("SELECT COUNT(1) FROM `spell`");
sql("INSERT INTO `stats` SET ".obj2sql($o));

//misc
$o = new EmptyObject();
$o->time = $time;
$o->type = kStats_SysInfo_Misc;
$o->i1 = sqlgetone("SELECT COUNT(1) FROM `user` where `admin`=0");
$o->i2 = sqlgetone("SELECT COUNT(1) FROM `guild`");
$o->i3 = sqlgetone("SELECT COUNT(b.id) FROM `building` b, `user` u where b.user=u.id and u.admin=0");
$o->f1 = sqlgetone("SELECT `value` FROM `global` WHERE `name`='crontime' LIMIT 1");
$o->f2 = sqlgetone("SELECT COUNT(1) FROM `terrain`");
$o->f3 = sqlgetone("SELECT COUNT(b.id) FROM `construction` b,`user` u where b.user=u.id and u.admin=0");
sql("INSERT INTO `stats` SET ".obj2sql($o));

//activity stats
$o = new EmptyObject();
$o->time = $time;
$o->type = kStats_SysInfo_Activity;
//aktiv in 2h
$o->i1 = sqlgetone("SELECT COUNT(`id`) FROM `user` WHERE admin=0 AND `lastlogin`>".($time-60*60*2));
//aktiv in 24h
$o->i2 = sqlgetone("SELECT COUNT(`id`) FROM `user` WHERE admin=0 AND `lastlogin`>".($time-60*60*24));
//aktiv in 3t
$o->i3 = sqlgetone("SELECT COUNT(`id`) FROM `user` WHERE admin=0 AND `lastlogin`>".($time-60*60*24*3));
//$o->f1 = 0;
//$o->f2 = 0;
//$o->f3 = 0;
sql("INSERT INTO `stats` SET ".obj2sql($o));

//army
$o = new EmptyObject();
$o->time = $time;
$o->type = kStats_SysInfo_Army;
$o->i1 = sqlgetone("SELECT COUNT(b.id) FROM `army` b,`user` u where `type`=".kArmyType_Normal." and b.user=u.id and u.admin=0");
$o->i2 = sqlgetone("SELECT COUNT(b.id) FROM `army` b,`user` u WHERE `type`=".kArmyType_Siege." and b.user=u.id and u.admin=0");
$o->i3 = sqlgetone("SELECT COUNT(1) FROM `army` WHERE `user`=0");
$o->f1 = sqlgetone("SELECT sum(u.amount) FROM unit u, building b, user o WHERE u.army =0 AND u.building = b.id AND b.user = o.id AND o.admin =0")
			+ sqlgetone("SELECT sum(u.amount) FROM unit u, army b, user o WHERE u.army >0 AND u.army = b.id AND b.user = o.id AND o.admin =0")
			+ sqlgetone("SELECT sum(u.amount) FROM unit u, unittype t where u.type=t.id"); // TODO :  and t.type=1  ??? column doesn't exist anymore, elite or monster ?

//sqlgetone("SELECT SUM(`amount`) FROM `unit`");

sql("INSERT INTO `stats` SET ".obj2sql($o));

//fight
$o = new EmptyObject();
$o->time = $time;
$o->type = kStats_SysInfo_Fight;
$o->i1 = sqlgetone("SELECT COUNT(1) FROM `fight`");
$o->i2 = sqlgetone("SELECT COUNT(1) FROM `pillage`");
$o->i3 = sqlgetone("SELECT COUNT(1) FROM `siege`");
sql("INSERT INTO `stats` SET ".obj2sql($o));

//trade
$o = new EmptyObject();
$o->time = $time;
$o->type = kStats_SysInfo_Trade;
$o->i1 = sqlgetone("SELECT COUNT(1) FROM `marketplace`");
$o->i2 = sqlgetone("SELECT SUM(`offer_count`) FROM `marketplace`");
$o->i3 = sqlgetone("SELECT SUM(`price_count`) FROM `marketplace`");
$o->f1 = GetGlobal("stats_trade_sum");
SetGlobal("stats_trade_sum",0);
sql("INSERT INTO `stats` SET ".obj2sql($o));

//environment
$o = new EmptyObject();
$o->time = $time;
$o->type = kStats_SysInfo_Environment;
$o->i1 = sqlgetone("SELECT COUNT(1) FROM `fire`");
sql("INSERT INTO `stats` SET ".obj2sql($o));


//--------------------------------
//user stats----------------------
//--------------------------------

$t = sqlgettable("SELECT * FROM `user` where `admin`=0");
foreach($t as $u)
{
	$o = new EmptyObject();
	$o->time = $time;
	$o->user = $u->id;
	$o->type = kStats_UserInfo;
	$o->i1 = floor($u->pop);
	$o->i2 = floor($u->maxpop);
	$o->i3 = $u->guildpoints;
	$o->f1 = sqlgetone("SELECT COUNT(1) FROM `building` WHERE `user`=".$u->id);
	$o->f2 = sqlgetone("SELECT COUNT(1) FROM `army` WHERE `user`=".$u->id);
	sql("INSERT INTO `stats` SET ".obj2sql($o));
}

?>