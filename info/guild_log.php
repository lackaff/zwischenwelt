<?
require_once("../lib.main.php");
require_once("../lib.guild.php");
require_once("../lib.guildforum.php");

Lock();
profile_page_start("guild_log.php");
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 transitional//EN"
   "http://www.w3.org/TR/html4/transitional.dtd">
<html>
<head>
<link rel="stylesheet" type="text/css" href="../styles.css">
<title>Zwischenwelt - Gildelog</title>

</head>
<body>
<?php
include("../menu.php");

$gGuild = sqlgetobject("SELECT g.*,u.`name` as `foundername` FROM `guild` g,`user` u WHERE u.`id`=g.`founder` AND g.`id`=".$gUser->guild);
echo "<center>";
$guildlogs_page = 50;
	if (!isset($f_guildlogs_start)) $f_guildlogs_start = 0;
	$guildlogs_max = sqlgetone("SELECT COUNT(*) FROM `guildlog` WHERE (
		`guild1` = ".$gGuild->id." OR 
		`guild2` = ".$gGuild->id.")");
	$guildlogs = sqlgettable("SELECT * FROM `guildlog` WHERE (
		`guild1` = ".$gGuild->id." OR 
		`guild2` = ".$gGuild->id.") ORDER BY `time` DESC LIMIT ".intval($f_guildlogs_start).",".$guildlogs_page);
	function guild_echo_user($userid) {
		if ($userid > 0 && ($user = sqlgetobject("SELECT * FROM `user` WHERE `id` = ".intval($userid)))) {
			$userhq = sqlgetobject("SELECT * FROM `building` WHERE `user` = ".$user->id." AND `type` = 1");
			echo ($userhq)?pos2txt($userhq->x,$userhq->y,$user->name):$user->name;
		}
	}
	?>
	<?php if (count($guildlogs) > 0) {?>
		<h3>GildenLog </h3>
		<a href="<?=Query("?sid=?&guildlogs_start=".max(0,$f_guildlogs_start-$guildlogs_page))?>">&lt;&lt;</a>
		<?=floor($f_guildlogs_start/$guildlogs_page)+1?> / <?=floor($guildlogs_max/$guildlogs_page)+1?>
		<a href="<?=Query("?sid=?&guildlogs_start=".min($guildlogs_max-1,$f_guildlogs_start+$guildlogs_page))?>">&gt;&gt;</a>
		<table border=1 cellspacing=0>
		<tr>
			<th>Zeit</th>
			<th>Was</th>
			<th>Wo</th>
			<th colspan="2">Angreifer</th>
			<th colspan="2">Verteidiger</th>
			<th>&nbsp;</th>
		</tr>
		<?php foreach ($guildlogs as $o) {?>
		<tr>
			<td nowrap><?=date("d.m H:i",$o->time);?></td>
			<td nowrap><?=$o->trigger?></td>
			<td nowrap><?=pos2txt($o->x,$o->y)?></td>
			<td nowrap><?php guild_echo_user($o->user1)?></td>
			<td nowrap><?=($o->guild1 > 0)?sqlgetone("SELECT `name` FROM `guild` WHERE `id` = ".$o->guild1):""?></td>
			<td nowrap><?php guild_echo_user($o->user2)?></td>
			<td nowrap><?=($o->guild2 > 0)?sqlgetone("SELECT `name` FROM `guild` WHERE `id` = ".$o->guild2):""?></td>
			<td nowrap><?=$o->what?></td>
			<td nowrap><?=$o->count?>x</td>
		</tr>
		<?php }?>
		</table>
	<?php } // count($guildlogs) > 0 ?>
<p><a href="<?=Query("guild.php?sid=?")?>"><img src="<?=g("gildeforum/back.png")?>" border=0 alt="Zur&uuml;ck" title="zur&uuml;ck zur Gilde"></a></p></center>
<?php
profile_page_end(); 
?>
</body>
</html>