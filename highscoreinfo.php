<?php
require_once("lib.main.php");


$userlist=sqlgettable("SELECT `lastlogin`,`id`,`guild`,`name`,`general_pts`+`army_pts` AS pts FROM `user` WHERE `admin`=0 ORDER BY pts DESC","id");
$i = 0;

foreach ($userlist as $o) {
	echo $i.",".$o->name.",".$o->pts."<br>\n";
	++$i;
};
?>