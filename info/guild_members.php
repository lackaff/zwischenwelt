<?php
require_once("../lib.main.php");
require_once("../lib.guild.php");
require_once("../lib.guildforum.php");

Lock();
profile_page_start("guild.php");

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

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 transitional//EN"
   "http://www.w3.org/TR/html4/transitional.dtd">
<html>
<head>
<link rel="stylesheet" type="text/css" href="../styles.css">
<link rel="stylesheet" type="text/css" href="../zwstyle.css">
<title>Zwischenwelt - Gilde</title>

</head>
<body>
<?php include("../menu.php"); ?>
<div class="tabs"><div class="tabheader">
<?=renderGuildTabbar("Mitglieder")?>
</div><div class="tabpane">
<?php
//ist der user in einer gilde?
if($gUser->guild == 0)
{//neeee ------------------------------------------------------------
?>
	Sie befinden sich in keiner Gilde!
<?php
}
else
{//gilde vorhanden ------------------------------------------------------------
?>
<h4>Gilde '<?=$gGuild->name?>'</h4>
<?php $gRight = sqlgettable("SELECT * FROM `guild_right` ORDER BY `right` ASC","right"); ?>
	
	<table width=100% border=0>
	
	<tr><td colspan=2 valign="top" width=100%><center>
		
	<?php ImgBorderStart("s1","jpg","#ffffee","",32,33); ?>
		<center><span style="font-size:14px;">Mitglieder</span></center>
		<table><tr><th>Status</td><th></th><th>Name</th><th>Ort</th><th>Gildepunkte</th></tr>
		<?php
		foreach($members as $u){
			$hq=sqlgetobject("SELECT `id`,`x`,`y` FROM `building` WHERE `user`=".$u->id." AND `type`=1");
			if(!$hq->id) continue;
			$time=time();
			$online = sqlgetone("SELECT `id` FROM `session` WHERE `userid`=".$u->id." AND $time-`lastuse`<300");
			if($online)$online = "#338833"; 
			else $online = "#3333aa";
			if(($time-$u->lastlogin)>(60*60*24*7))$online = "#dd1111";
			if(($time-$u->lastlogin)>(60*60*24*21))$online = "#999999";
			$dead="";
			if(($time-$u->lastlogin)>(60*60*24*48)){
				$online = "#cacaca";
				$dead="&#8224;";
			}
			?>
			<tr>
			<td align=left valign=middle><?
			if($gGuild->founder!=$u->id){
				if($u->guildstatus!=1 && $u->guildstatus!=0){
					foreach($gRight as $r){
						if($u->guildstatus%$r->right==0){
							?><img src="<?=g($r->gfx)?>" title="<?=$r->desc?>">&nbsp;<?
						}
					}
				}else{
					?><img src="<?=g("tool_cancel.png")?>" title="hat keine Rechte"><?
				}
			}else{
				?><center><img src="<?=g("icon/guild-founder.png")?>" title="Gildegr�nder"></center><?
			}
			?>&nbsp;</td>
			<td style="background-color:<?=$u->color?>">&nbsp;</td>
			<td>
			<a style="font-family:verdana;color:<?=$online?>" href="<?=query("msg.php?sid=?&show=compose&to=".urlencode($u->name))?>"><?=$u->name." $dead"?></a></td>
			<td align=center><a style="font-family:verdana;font-size:10px" href="<?=query("info.php?sid=?&x=".$hq->x."&y=".$hq->y)?>">(<?=$hq->x.'/'.$hq->y?>)</a></td>
			<td align=right><?=ktrenner($u->guildpoints,"#4444cc","#aa5555")?></td></tr>
		<?}?>
		</table>
	
	<?php ImgBorderEnd("s1","jpg","ffffee",32,33); ?>
	<br></center>
	</td></tr></table>
		
	<SCRIPT LANGUAGE="JavaScript" type="text/javascript">
	<!--
		function showpos(x,y) {
			parent.map.location.href = "<?=Query("../".kMapScript."?sid=?")?>&x="+x+"&y="+y;
		}
	//-->
	</SCRIPT>
<?php
} // endif guild

?>
</div></div>
</body>
</html>
<?php profile_page_end(); ?>