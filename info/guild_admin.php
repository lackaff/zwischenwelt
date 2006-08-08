<?php
require_once("../lib.main.php");
require_once("../lib.guild.php");

Lock();

//ist der user in einer gilde?
if($gUser->guild > 0){
//gilde vorhanden ------------------------------------------------------------
	//print_r($_POST);

	$gGuild = sqlgetobject("SELECT g.*,u.`name` as `foundername` FROM `guild` g,`user` u WHERE u.`id`=g.`founder` AND g.`id`=".$gUser->guild);
	$members = sqlgettable("SELECT * FROM `user` WHERE `guild`=".$gGuild->id." ORDER BY `general_pts`+`army_pts` DESC");

	if(!empty($f_do)){
		switch ($f_do){
			case "killguild":
				if($f_sure == 1 && HasGuildRight($gUser,kGuildRight_GuildAdmin)){
					TablesLock();
					$g = intval($gUser->guild);
					sql("DELETE FROM `guild` WHERE `id`=$g");
					sql("DELETE FROM `guild_forum` WHERE `guild`=$g");
					sql("DELETE FROM `guild_forum_comment` WHERE `guild`=$g");
					sql("DELETE FROM `guild_msg` WHERE `guild`=$g");
					sql("DELETE FROM `guild_pref` WHERE `guild`=$g");
					sql("DELETE FROM `guild_request` WHERE `guild`=$g");
					sql("UPDATE `user` SET `guild`=0 WHERE `guild`=$g");
					TablesUnlock();
					Redirect(query("guild.php?sid=?"));
					exit;
				}
			break;
			case "setright":
				if(HasGuildRight($gUser,kGuildRight_GuildAdmin))
					foreach($members as $user){
						//delete users
						if(!empty($f_deluser[$user->id]) && $user->guild == $gGuild->id)leaveGuild($user->id);
						//set right
						$status=0;
						foreach ($gRight as $r){
							if(isset($_POST["ri_".$user->id."_".$r["right"]]))$status+=$r["right"];
						}
						
						sql("UPDATE `user` SET `guildstatus`=".$status." WHERE `guild`=$gGuild->id AND `id`=".$user->id);
						//update me for runntime issue
						if($user->id==$gUser->id)$gUser->guildstatus=$status;
					}
			break;
			
			case "savesettings":
				if(HasGuildRight($gUser,kGuildRight_GuildAdmin))
					sql("UPDATE `guild` SET `internprofile`='".addslashes($f_internprofile)."', `profile`='".addslashes($f_profile)."', `forumurl`='".addslashes($f_forumurl)."', `gfx`='".addslashes($f_gfxurl)."', `name` = '".addslashes($f_changedguildname)."',`color` = '".addslashes($f_color)."' WHERE `id` = ".$gGuild->id." LIMIT 1");
			break;
			
			case "setstdright":
				if(HasGuildRight($gUser,kGuildRight_GuildAdmin)){
					$status=0;
					foreach ($gRight as $r){
						if(isset($_POST["ri_std_".$r["right"]]))$status+=$r["right"];
					}
					sql("UPDATE `guild` SET `stdstatus`=".$status." WHERE `id`=".$gGuild->id);
				}
			break;
			
			case "setallright":
				if(HasGuildRight($gUser,kGuildRight_GuildAdmin)){
					$status=0;
					foreach ($gRight as $r){
						if(isset($_POST["ri_overwrite_".$r["right"]]))$status+=$r["right"];
					}
					sql("UPDATE `user` SET `guildstatus`=".$status." WHERE `guild`=".$gGuild->id);
				}
			break;

			case "setmsgoftheday":
				if(HasGuildRight($gUser,kGuildRight_SetMsgOfTheDay))
					sql("UPDATE `guild` SET `message`='".addslashes($f_message)."' WHERE `id`=".$gGuild->id);
			break;
			
			case "decide":
				if(isset($f_decide) && isset($f_reaction) && HasGuildRight($gUser,kGuildRight_GuildAdmin))
				{
					$f_user = intval($f_user);
					if($f_reaction == "accept")$ok = true;
					else $ok = false;
					reactOnRequestGuild($f_user,$gGuild->id,$ok);
				}
			break;
			
			case "setlimit":
				if($gGuild->founder == $gUser->id || HasGuildRight($gUser,kGuildRight_GuildBursar))
					foreach ($members as $o){
						setGPLimit($o->id,$_POST['limit_'.$o->id]);
					}
			break;
			
			case "setstdlimit":
				if($gGuild->founder == $gUser->id || HasGuildRight($gUser,kGuildRight_GuildBursar))
					setStdGPLimit($gGuild->id,$f_stdlimit);
			break;
			
			case "settakealllimit":
				if($gGuild->founder == $gUser->id || HasGuildRight($gUser,kGuildRight_GuildBursar))
					setTakeAllLimit($gGuild->id,$f_takealllimit);
			break;
			
			case "sendgm":
				if(empty($f_subject) || empty($f_text))break;
				if(HasGuildRight($gUser,kGuildRight_SendGuildMsg)){
					require_once("../lib.message.php");
					$tosent=TRUE;
					foreach ($members as $o){
						sendMessage($o->id,$gUser->id,$f_subject,$f_text,kMsgTypeGM,$tosent);
						$tosent=FALSE;
					}
				}
			break;
			
			
			default:
			break;
		}
		$gGuild = sqlgetobject("SELECT g.*,u.`name` as `foundername` FROM `guild` g,`user` u WHERE u.`id`=g.`founder` AND g.`id`=".$gUser->guild);
		$members = sqlgettable("SELECT * FROM `user` WHERE `guild`=".$gGuild->id." ORDER BY `general_pts`+`army_pts` DESC");
	}
}

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 transitional//EN"
   "http://www.w3.org/TR/html4/transitional.dtd">
<html>
<head>
<link rel="stylesheet" type="text/css" href="<?=GetZWStylePath()?>">
<title>Zwischenwelt - Gilde</title>

</head>
<body>

<?php 

include("../menu.php");
echo renderGuildTabbar(4);

//ist der user in einer gilde?
if($gUser->guild == 0)
{//neeee ------------------------------------------------------------
?>
	Sie befinden sich in keiner Gilde!
<?php
} else {
//gilde vorhanden ------------------------------------------------------------
?>
<center>
<table>
<tr><td valign="top" align="center">
<?
if(HasGuildRight($gUser,kGuildRight_GuildAdmin)){ 
	ImgBorderStart("s1","jpg","#ffffee",32,33); ?>
	<table>
		<tr><?foreach ($gRight as $r){?>
				<th><img src="<?=g($r["gfx"])?>" title="<?=$r["desc"]?>"></th>
			<?}?>
			<th></th>
			<th><img src="<?=g("del.png")?>" title="user aus der gilde entfernen"></th>
			<th></th><th>Mitglieder</th>
		</tr>
		<form method="post" action="<?=Query("?sid=?")?>">
		<input type=hidden name=do value=setright>
		<?foreach($members as $u){
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
			$hq=sqlgetobject("SELECT `id`,`x`,`y` FROM `building` WHERE `user`=".$u->id." AND `type`=1");?>
			<tr>
			<?foreach ($gRight as $r){?>
				<td><input type="checkbox" name="ri_<?=$u->id."_".$r["right"]?>" value="<?=$r["right"]?>" <?=(($u->guildstatus & $r["right"]) > 0?"checked":"")?>></td>
			<?}?>
			<td>&nbsp;</td><td><input type=checkbox name="deluser[<?=$u->id?>]" value=1></td>
			<td style="background-color:<?$u->color?>">&nbsp;</td>
			<td>
			<a style="font-family:verdana;color:<?=$online?>" href="<?=query("msg.php?sid=?&show=compose&to=".urlencode($u->name))?>"><?=$u->name." $dead"?></a>
			<a style="font-family:verdana;font-size:10px" href="<?=query("info.php?sid=?&x=".$hq->x."&y=".$hq->y)?>">(<?=$hq->x.'/'.$hq->y?>)</a></td>
			<td>(<?=ktrenner($u->guildpoints,"#4444cc","#aa5555")?>&nbsp;GP)</td></tr>
			
		<?}?>
		<tr><td colspan="<?=count($gRight)+5?>" align=right><input type=submit name=saveright value=Save></td></tr>
			</form>
	</table>
	<p>Standardrechte f&uuml;r neue Mitglieder:
	<table>
		<tr>
		<?foreach ($gRight as $r){?>
			<th><img src="<?=g($r["gfx"])?>" title="<?=$r["desc"]?>"></th>
			<?}?>
			<th></th>
		</tr>
		<tr>
			<form method="post" action="<?=Query("?sid=?")?>">
			<input type=hidden name="do" value="setstdright"><tr>
			<?foreach ($gRight as $r){?>
				<td><input type="checkbox" name="ri_<?="std_".$r["right"]?>" value="<?=$r["right"]?>" <?=(($gGuild->stdstatus & $r["right"]) > 0?"checked":"")?>></td>
			<?}?><td><input type=submit name=savestdrights value=Save></td></tr>
			</form>
		</tr>
	</table>
	</p>
	<p>Rechte f&uuml;r alle Mitglieder überschreiben:
	<table>
		<tr>
		<?foreach ($gRight as $r){?>
			<th><img src="<?=g($r["gfx"])?>" title="<?=$r["desc"]?>"></th>
			<?}?>
			<th></th>
		</tr>
		<tr>
			<form method="post" action="<?=Query("?sid=?")?>">
			<input type=hidden name="do" value="setallright"><tr>
			<?foreach ($gRight as $r){?>
				<td><input type="checkbox" name="ri_overwrite_<?=$r["right"]?>" value="<?=$r["right"]?>"></td>
			<?}?><td><input type=submit name=setallright value=Save></td></tr>
			</form>
		</tr>
	</table>
	</p>
	<?
	ImgBorderEnd("s1","jpg","ffffee",32,33);
} ?>

</td></tr>
</table>
</center>
<br>
<div align=left>
	<table>
		<?if(HasGuildRight($gUser,kGuildRight_GuildAdmin)){?>
			<form method="post" action="<?=Query("?sid=?")?>">
			<input type=hidden name=do value=savesettings>
			<tr><th>Gildenname</th><td><INPUT type="text" size="30" name="changedguildname" VALUE="<?=htmlspecialchars($gGuild->name)?>"></td>
			<tr><th>LogoURL</th><td><INPUT type="text" name="gfxurl" VALUE="<?=htmlspecialchars($gGuild->gfx)?>"></td>
			<tr><th>externe Forums URL <br>(internes Forum wird unerreichbar, <br>wenn diese URL gesetzt ist)</th><td><INPUT type="text" name="forumurl" VALUE="<?=htmlspecialchars($gGuild->forumurl)?>"></td>
			<tr><th>Farbe</th><td><INPUT style="background-color:<?=htmlspecialchars($gGuild->color)?>" type="text" name="color" VALUE="<?=htmlspecialchars($gGuild->color)?>"></td>
			<tr><th colspan=2>Externes Profil:</th></tr>
			<tr><td colspan=2><textarea name="profile" rows=10 cols=55><?=htmlentities($gGuild->profile)?></textarea></td>
			<tr><th colspan=2>Internes Profil:</th></tr>
			<tr><td colspan=2><textarea name="internprofile" rows=10 cols=55><?=htmlentities($gGuild->internprofile)?></textarea></td>
			<tr><td colspan=2 align=left><input type="submit" name="saveguildname" value="speichern"></td></tr>
			</form>
			<tr><td colspan=2>&nbsp;</td></tr>
			<tr><td colspan=2>&nbsp;</td></tr>
			<tr><td colspan=2><table border=0 cellspacing=0 cellpadding=0>
		<?}?>
		<form method="post" action="<?=Query("?sid=?")?>"><input type=hidden name=do value=setmsgoftheday>
		<tr><td>MessageOfTheDay:&nbsp;</td>
		<td><?=(HasGuildRight($gUser,kGuildRight_SetMsgOfTheDay)?"<input type=text size=50 name=message value='".$gGuild->message."'>":htmlspecialchars($gGuild->message))?></td>
		<td><?=(HasGuildRight($gUser,kGuildRight_SetMsgOfTheDay)?"<input type=submit name=set_message value='Set'>":"")?></td></tr>
		</form>
		</table></td></tr><?if(HasGuildRight($gUser,kGuildRight_GuildAdmin)){?>
		<form method="post" action="<?=Query("?sid=?")?>">
		</table></td></tr>
		<tr><td colspan=2>&nbsp;</td></tr>
		<tr><td colspan=2>&nbsp;</td></tr>
		<tr><td colspan=2>
			<table border="0"><tr><th align="left" colspan="3">Bewerbungen</th></tr>
			<?php
			$bewerber = sqlgettable("SELECT u.`guildpoints`,u.`general_pts`+u.`army_pts` AS `punkte`,u.`id`,u.`name`,u.`color`,r.`time`,r.`comment` FROM `user` u,`guild_request` r WHERE r.`guild`=".$gGuild->id." AND u.`id`=r.`user` ORDER BY r.`time` ASC");
			foreach($bewerber as $u){
				$hq=sqlgetobject("SELECT `id`,`x`,`y` FROM `building` WHERE `user`=".$u->id." AND `type`=1");
				$hqxy="(<a href='".query("../info/info.php?sid=?&x=".$hq->x."&y=".$hq->y)."'>".$hq->x."/".$hq->y."</a>)";
				$name="<a href='".query("../info/msg.php?sid=?&show=compose&to=".urlencode($u->name))."' >".$u->name."</a>";
				?><form method="post" action="<?=Query("?sid=?")?>">
				<input type="hidden" name="do" value="decide">
				<tr>
					<td align="left" valign="top">
						<span style="background-color:<?=$u->color?>">&nbsp;</span>&nbsp;'<?=$name.'  ('.ktrenner($u->guildpoints).' GP, '.$u->punkte.' Punkte, auf '.$hqxy.') am '.date("D, j. M G:i",$u->time)?>
					</td>
				</tr>
				<tr>
					<td colspan="3"><i><?=nl2br($u->comment)?></i></td>
				</tr>
				<tr>
					<td align="left" valign="top">
						<input type="radio" name="reaction" value="accept"> annehmen 
						<input type="radio" name="reaction" value="reject"> ablehnen
					</td>
				</tr>
				<tr valign="top">
					<td align="left">
						<input type="hidden" name="user" value="<?=$u->id?>"><input type="submit" name="decide" value="entscheiden">
					</td>
				</tr>
				</form>
			<?}?>
			</table>
		</td></tr>
		<?php } ?>
	</table>
	<hr>
</div>

<? if(HasGuildRight($gUser,kGuildRight_GuildBursar)){
ImgBorderStart();?>
<table border=0 cellspacing=1 cellpadding=0>
<tr><td colspan=3><h4>Minuspunktelimits</h4>
(Limit als Zahl angeben, 0 für unbeschränkt)</td></tr>
<td colspan=3>&nbsp;</td></tr>
<tr><th>Limit</th><th>User</th><th>Gildepunkte</th></tr>
<form method="post" action="<?=Query("?sid=?")?>">
<input type=hidden name=do value=setlimit>
<?
	foreach ($members as $o){?>
		<tr><td><input align=right type=text size=15 name="limit_<?=$o->id?>" value="<?=getGPLimit($o->id)?>">&nbsp;</td><td><?=$o->name?></td><td align=right><?=ktrenner($o->guildpoints,"#4444cc","#aa5555")?></td></tr>
	<?}?>
<td colspan=3>&nbsp;</td></tr>
<tr><td colspan=3 align=right><input type=submit name=setlimit value=Set></td></tr>
</form>
</table>
<br>

<table>
<td colspan=3>&nbsp;</td></tr>

<form method="post" action="<?=Query("?sid=?")?>">
<input type=hidden name=do value="setstdlimit">
<tr><td>Standardlimit für neue Mitglieder</td><td align=right><input align=right type=text size=15 name="stdlimit" value="<?=getStdGPLimit($gGuild->id)?>"></td><td><input type=submit name=setstdlimit value=Set></td></tr>
</form>

<form method="post" action="<?=Query("?sid=?")?>">
<input type=hidden name=do value="settakealllimit">
<tr><td>"Alles rausnehmen" Limit</td><td align=right><input align=right type=text size=15 name="takealllimit" value="<?=getTakeAllLimit($gGuild->id)?>"></td><td><input type=submit name=settakealllimit value=Set></td></tr>
</form>

<td colspan=3>&nbsp;</td></tr>
</table>


<?ImgBorderEnd();
}
if(HasGuildRight($gUser,kGuildRight_GuildBursar)){
ImgBorderStart();?>
<h4>Minuspunktelimits</h4>
(Limit als Zahl angeben, 0 für unbeschränkt)
<table border=0 cellspacing=0 cellpadding=0><tr><th>Limit</th><th>User</th><th>Gildepunkte</th></tr>
<?
	foreach ($members as $o){?>
		<tr><td align=right><?=getGPLimit($o->id)?></td><td><?=$o->name?></td><td align=right><?=ktrenner($o->guildpoints,"#4444cc","#aa5555")?></td></tr>
	<?}?>
</table>
<br>
<table>
<tr><td>Standardlimit für neue Mitglieder</td><td><?=getStdGPLimit($gGuild->id)?></td><td></td></tr>
</table>
</form>
<?ImgBorderEnd();
}
if(HasGuildRight($gUser,kGuildRight_SendGuildMsg)){
ImgBorderStart("s2","jpg","#ffffee","bg-s2",32,33);?>
<form action="<?=query("?sid=?")?>" method="post">
<input type=hidden name=do value=sendgm>
<table border=0 style='width:520px'>
	<tr><th align="left">To:</th>		<td align="left">Gilde</td></tr>
	<tr><th align="left">Subject:</th>	<td align="left"><input name="subject" type="text" size="64" value="<?=((isset($m)&&$m)?($m->subject):"Betreff nicht vergessen!")?>"></td></tr>
	<tr><td colspan="2">
	<textarea name="text" cols="75" rows="15"></textarea>
	</td></tr>
	<tr><td colspan="2" align="right"><input type="submit" name="send" value="abschicken"></td></tr>
</table>
</form>
<?
ImgBorderEnd("s2","jpg","#ffffee",32,33);}
?>

<?php if(HasGuildRight($gUser,kGuildRight_GuildAdmin)) { ?>
<form method=post action="<?=query("?sid=?");?>">
<input type="hidden" name="do" value="killguild">
	<div style="padding:2px;border:solid red 2px;">
	<input type=checkbox name=sure value=1> Diese Gilde <input type=submit value="löschen">
	</div>
</form>
<?php } ?>

<?php } ?>

</div></div>
</body>
</html>
