<?php
require_once("../lib.main.php");
require_once("../lib.army.php");
Lock();
$t = time();

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 transitional//EN"
   "http://www.w3.org/TR/html4/transitional.dtd">
<html>
<head>
<link rel="stylesheet" type="text/css" href="../styles.css">
<link rel="stylesheet" type="text/css" href="<?=GetZWStylePath()?>">
<title>Zwischenwelt - Statistiken</title>

</head>
<body>

<?php
include("../menu.php");
include("../stats/header.php");

?>


<table>
<tr>
        <td valign=top>

<?php ImgBorderStart(); ?>


	<table border="0">
		<?php $t = time(); ?>
		<tr><th align="left" colspan=2>Statistik</th></tr>
		<tr><td align="left" nowrap>Spieler</td><td><?=sqlgetone("SELECT COUNT(`id`) FROM `user` where `admin`=0");?></td></tr>
		<tr><td align="left" nowrap>Spieler Online</td><td><?php 
		$s = sqlgettable("SELECT `lastuse` FROM `session` WHERE $t-`lastuse`<300 GROUP BY `userid`");
		$online = 0;
		foreach($s as $x)if($t-$x->lastuse < 300)++$online;
		echo $online;
		?></td></tr>
		<tr><td align="left" nowrap>Aktive in 2h</td><td><?=sqlgetone("SELECT COUNT(`id`) FROM `user` WHERE admin=0 AND `lastlogin`>".($t-60*60*2));?></td></tr>
		<tr><td align="left" nowrap>Aktive in 24h</td><td><?=sqlgetone("SELECT COUNT(`id`) FROM `user` WHERE admin=0 AND `lastlogin`>".($t-60*60*24));?></td></tr>
		<tr><td align="left" nowrap>Aktive in 3T</td><td><?=sqlgetone("SELECT COUNT(`id`) FROM `user` WHERE admin=0 AND `lastlogin`>".($t-60*60*24*3));?></td></tr>
		<tr><td align="left" nowrap>Aktive in 7T</td><td><?=sqlgetone("SELECT COUNT(`id`) FROM `user` WHERE admin=0 AND `lastlogin`>".($t-60*60*24*7));?></td></tr>
		<tr><td align="left" nowrap>Armeen</td><td><?=sqlgetone("SELECT COUNT(*) FROM `army`");?></td></tr>
		<tr><td align="left" nowrap>Hellholes</td><td><?=sqlgetone("SELECT COUNT(`id`) FROM `hellhole`");?></td></tr>
		<tr><td align="left" nowrap>Gebäude</td><td><?=kplaintrenner($maxb=sqlgetone("SELECT COUNT(b.`id`) FROM `building` b,`user` u WHERE b.`user`=u.`id` AND u.`admin`=0"));?></td></tr>
		<tr><td align="left" nowrap>Zauber</td><td><?=$maxb=sqlgetone("SELECT COUNT(`id`) FROM `spell`");?></td></tr>
		<tr><td align="left" nowrap>Gilden</td><td><?=$maxb=sqlgetone("SELECT COUNT(`id`) FROM `guild`");?></td></tr>
		<tr><td align="left" nowrap>maximale Bevölkerung</td><td><?=kplaintrenner(round(sqlgetone("SELECT MAX(`pop`) FROM `user` WHERE `admin`=0")));?></td></tr>
		<tr><td align="left" nowrap>horizontale Weltgrösse</td>
			<td><?=sqlgetone("SELECT MIN(`x`) FROM `building`")?> bis 
				<?=sqlgetone("SELECT MAX(`x`) FROM `building`")?></td></tr>
		<tr><td align="left" nowrap>vertikale Weltgrösse</td>
			<td><?=sqlgetone("SELECT MIN(`y`) FROM `building`")?> bis 
				<?=sqlgetone("SELECT MAX(`y`) FROM `building`")?></td></tr>
		<tr><td align="left" nowrap>cron dTime</td><td><?=sprintf("%0.3f",$gGlobal["crontime"])?></td></tr>
		<?php foreach ($gUnitType as $unittype) {?>
		<tr><td align="left" nowrap><img class="picframe" align="middle" src="<?=g($unittype->gfx)?>"> <?=$unittype->name?></td><td align="right">
			<?=kplaintrenner(intval(sqlgetone("SELECT sum( u.amount ) FROM unit u WHERE u.type=".$unittype->id)));?></td></tr>
		<?php }?>
	</table>

<?php ImgBorderEnd(); ?>
	</td>

	<td valign=top>

	<?php
	$t = sqlgettable("SELECT * FROM `stats` WHERE `type`=".kStats_SysInfo_Misc." AND `time`+60*60*24*7*4>".time()." ORDER BY `time`");

	$x = Array();
	$y = Array();
	$title = Array();
	
	foreach($t as $o)
	{
		//$x[] = $o->time /60 /60 /24;
		$x[] = date("G\h_j.n.y",$o->time);
		$y[1][] = $o->i1;
		$y[2][] = $o->i2;
		$y[3][] = $o->i3;
		$y[4][] = $o->f1;
		$y[5][] = $o->f2;
		$y[6][] = $o->f3;
	}

	$title[1] = "User";
	$title[2] = "Gilden";
	$title[3] = "Gebäude";
	$title[4] = "cron dTime";
	$title[5] = "Landschaft";
	$title[6] = "Baupläne";

	for($i=1;$i<=6;++$i){ ImgBorderStart(); ?>
	<b><?=$title[$i]?></b><br>
	<img src="../plot.php?title=<?=$title[$i]?>&x=<?=implode(",",$x)?>&y=<?=implode(",",$y[$i])?>">
	<?php ImgBorderEnd(); } ?>


	<?php
	$t = sqlgettable("SELECT * FROM `stats` WHERE `type`=".kStats_SysInfo_Activity." AND `time`+60*60*24*7*4>".time()." ORDER BY `time`");

	$x = Array();
	$y = Array();
	$title = Array();
	
	foreach($t as $o)
	{
		//$x[] = $o->time /60 /60 /24;
		$x[] = date("G\h_j.n.y",$o->time);
		$y[1][] = $o->i1;
		$y[2][] = $o->i2;
		$y[3][] = $o->i3;
	}

	$title[1] = "Aktiv in 2h";
	$title[2] = "Aktiv in 24h";
	$title[3] = "Aktiv in 3T";

	for($i=1;$i<=3;++$i){ ImgBorderStart(); ?>
	<b><?=$title[$i]?></b><br>
	<img src="../plot.php?title=<?=$title[$i]?>&x=<?=implode(",",$x)?>&y=<?=implode(",",$y[$i])?>">
	<?php ImgBorderEnd(); } ?>


	<?php
	$t = sqlgettable("SELECT * FROM `stats` WHERE `type`=".kStats_SysInfo_Army." AND `time`+60*60*24*7*4>".time()." ORDER BY `time`");

	$x = Array();
	$y = Array();
	$title = Array();
	
	foreach($t as $o)
	{
		//$x[] = $o->time /60 /60 /24;
		$x[] = date("G\h_j.n.y",$o->time);
		$y[1][] = $o->i1;
		$y[2][] = $o->i2;
		$y[3][] = $o->i3;
		$y[4][] = $o->f1 / 1000;
	}

	$title[1] = "Armeen";
	$title[2] = "Rammen";
	$title[3] = "Monster";
	$title[4] = "Einheiten gesammt in k";

	for($i=1;$i<=4;++$i){ ImgBorderStart(); ?>
	<b><?=$title[$i]?></b><br>
	<img src="../plot.php?title=<?=$title[$i]?>&x=<?=implode(",",$x)?>&y=<?=implode(",",$y[$i])?>">
	<?php ImgBorderEnd(); } ?>


	<?php
	$t = sqlgettable("SELECT * FROM `stats` WHERE `type`=".kStats_SysInfo_Trade." AND `time`+60*60*24*7*4>".time()." ORDER BY `time`");

	$x = Array();
	$y = Array();
	$title = Array();
	
	foreach($t as $o)
	{
		//$x[] = $o->time /60 /60 /24;
		$x[] = date("G\h_j.n.y",$o->time);
		$y[1][] = $o->i1;
		$y[2][] = $o->i2;
		$y[3][] = $o->i3;
		$y[4][] = $o->f1;
	}

	$title[1] = "Marktplatz: Angebote";
	$title[2] = "Marktplatz: Rohstoffsumme der Angebote";
	$title[3] = "Marktplatz: Rohstoffsumme der Preise";
	$title[4] = "Marktplatz: Summe der gehandelten Waren";

	for($i=1;$i<=4;++$i){ ImgBorderStart(); ?>
	<b><?=$title[$i]?></b><br>
	<img src="../plot.php?title=<?=$title[$i]?>&x=<?=implode(",",$x)?>&y=<?=implode(",",$y[$i])?>">
	<?php ImgBorderEnd(); } ?>


	</td>
</tr>
</table>
<?php
include("../stats/footer.php");
?>

</body>
</html>
