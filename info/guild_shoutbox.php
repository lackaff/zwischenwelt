<?php
require_once("../lib.main.php");
require_once("../lib.guild.php");
require_once("../lib.guildforum.php");

Lock();
profile_page_start("guild_shoutbox.php");

function guild_echo_user($userid) {
	if ($userid > 0 && ($user = sqlgetobject("SELECT * FROM `user` WHERE `id` = ".intval($userid)))) {
		$userhq = sqlgetobject("SELECT * FROM `building` WHERE `user` = ".$user->id." AND `type` = 1");
		echo ($userhq)?pos2txt($userhq->x,$userhq->y,$user->name):$user->name;
	}
}

$gGuild = sqlgetobject("SELECT g.*,u.`name` as `foundername` FROM `guild` g,`user` u WHERE u.`id`=g.`founder` AND g.`id`=".$gUser->guild);
if($gGuild){
	$members = sqlgettable("SELECT * FROM `user` WHERE `guild`=".$gGuild->id." ORDER BY `general_pts`+`army_pts` DESC");
} else {
	$members = Array();
}

$gGuild = sqlgetobject("SELECT g.*,u.`name` as `foundername` FROM `guild` g,`user` u WHERE u.`id`=g.`founder` AND g.`id`=".$gUser->guild);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 transitional//EN"
   "http://www.w3.org/TR/html4/transitional.dtd">
<html>
<head>
<link rel="stylesheet" type="text/css" href="<?=GetZWStylePath()?>">
<title>Zwischenwelt - Gilde</title>

</head>
<body>
<?php include("../menu.php"); 

if(!empty($gGuild)){ ?>

<table>
    <tr><th colspan=2>Schreischachtel 7 Tage Archiv</th></tr>
    
    <?php
    $time = time();
    $t = sqlgettable("SELECT * FROM `guild_msg` WHERE `guild`=".$gGuild->id." AND `time`>($time-(60*60*24*7)) ORDER BY `time` DESC");
    foreach($t as $x) {
	$u = sqlgetobject("SELECT * FROM `user` WHERE `id`=".$x->user);
    ?>
    <tr><td style="background-color:#cfcfcf"><?=$u->name?></td><td style="background-color:#cfcfcf" align="right"><?=date("d.m H:i",$x->time)?></td></tr>
    <tr><td colspan=2><?=nl2br(htmlspecialchars($x->text))?></td></tr>
    <?php } ?>
</table>

<?php } ?>

</body>
</html>
<?php profile_page_end(); ?>