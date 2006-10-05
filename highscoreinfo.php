<?php
require_once("lib.main.php");

$guilds = sqlgettable("SELECT * FROM `guild` WHERE 1","id");
$userlist=sqlgettable("SELECT `lastlogin`,`id`,`guild`,`name`,`general_pts`+`army_pts` AS pts FROM `user` WHERE `admin`=0 ORDER BY pts DESC","id");
$i = 0;

foreach ($userlist as $o) {
	$guild = ($o->guild > 0) ? $guilds[$o->guild] : false;
	echo $i.",".$o->name.",".$o->pts.",".($guild ? $guild->name : "").",".($guild ? $guild->color : "")."<br>\n";
	++$i;
};
?>