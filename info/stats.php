<?php
require_once("../lib.main.php");
Lock();
profile_page_start("stats.php");

$t = time();

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 transitional//EN"
   "http://www.w3.org/TR/html4/transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<link rel="stylesheet" type="text/css" href="<?=GetZWStylePath()?>">
<title>Zwischenwelt - Statistics</title>

</head>
<body>

<table>
<tr>
	<td>
<?php
include(BASEPATH."/menu.php");
include(BASEPATH."/stats/header.php");
ImgBorderStart();
?>

<table>
	<tr><td align="left">angemeldete Spieler</td><td><?=sqlgetone("SELECT COUNT(`id`) FROM `user`");?></td></tr>
	<tr><td align="left">letztes Login &lt; 10min</td><td><?=sqlgetone("SELECT COUNT(`id`) FROM `user` WHERE `lastlogin`>".($t-60*10));?></td></tr>
	<tr><td align="left">letztes Login &lt; 2h</td><td><?=sqlgetone("SELECT COUNT(`id`) FROM `user` WHERE `lastlogin`>".($t-60*60*2));?></td></tr>
	<tr><td align="left">letztes Login &lt; 24h</td><td><?=sqlgetone("SELECT COUNT(`id`) FROM `user` WHERE `lastlogin`>".($t-60*60*24));?></td></tr>
	<tr><td align="left">Armeen</td><td><?=sqlgetone("SELECT COUNT(`id`) FROM `army`");?></td></tr>
	<tr><td align="left">Gebäude</td><td><?=$maxb=sqlgetone("SELECT COUNT(`id`) FROM `building`");?></td></tr>
	<tr><td align="left">Gilden</td><td><?=$maxb=sqlgetone("SELECT COUNT(`id`) FROM `guild`");?></td></tr>
	<tr><td align="left">maximale Population</td><td><?=sqlgetone("SELECT MAX(`pop`) FROM `user`");?></td></tr>
	<tr><td align="left">genutze Fläche</td><td>
	<?php 
	$dx = sqlgetone("SELECT MAX(`x`)-MIN(`x`) FROM `building`");
	$dy = sqlgetone("SELECT MAX(`y`)-MIN(`y`) FROM `building`");
	echo "$dx*$dy=".($dy*$dy).", ".round(100*$maxb/($dy*$dy))."% Dichte";
	?>
	</td></tr>
	<tr><td align="left">cron dTime</td><td><?=$gGlobal["crontime"]?></td></tr>
</table>

<?php 
ImgBorderEnd();
?>

	</td>
	<td>
<?php ImgBorderStart(); ?>
test
<?php ImgBorderEnd(); ?>
	</td>
</tr>
</table>

<?php
include(BASEPATH."/stats/footer.php");
?>

</body>
</html>
<?php profile_page_end(); ?>