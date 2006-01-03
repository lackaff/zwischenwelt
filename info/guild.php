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

if(isset($f_do) && $f_do=="leaveguild" && $f_sure == 1 && $f_id==$gUser->id) {
	leaveGuild();
	Redirect(Query("?sid=?"));
}
if(isset($f_do) && $f_do=="markallforumread") {
	MarkAllForumRead();
	Redirect(query("?sid=?"));
}

if(isset($f_create) && !empty($f_guildname) && !empty($f_color))
{
	if (createGuild(addslashes($f_guildname),addslashes($f_color))) 
		Redirect(Query("?sid=?"));
}
else
if(isset($f_join) && !empty($f_guildname) && !empty($f_comment))
{
	requestJoinGuild(addslashes($f_guildname),addslashes($f_comment));
	Redirect(Query("?sid=?"));
}
else if(isset($f_get) || isset($f_put))
{
	foreach($gRes as $n=>$f)${$f} = intval(${"f_".$f});
	if(isset($f_get))$faktor = 1;
	else $faktor = -1;
	getFromGuild($gUser->id,$gGuild->id,$faktor*$lumber,$faktor*$stone,$faktor*$food,$faktor*$metal,$faktor*$runes);
	$gUser = sqlgetobject("SELECT * FROM `user` WHERE `id` = ".$gUser->id);
}
else if(isset($f_getall))
{	
	foreach($gRes as $n=>$f) $$f = $gGuild->{$f};
	$limit = getTakeAllLimit($gGuild->id);
	if($limit > 0)foreach($gRes as $n=>$f) $$f = max(0,$$f - $limit);
	getFromGuild($gUser->id,$gGuild->id,$lumber,$stone,$food,$metal,$runes);
	$gUser = sqlgetobject("SELECT * FROM `user` WHERE `id` = ".$gUser->id);
}
else if(isset($f_putall))
{	
	foreach($gRes as $n=>$f) $$f = -$gUser->{$f};
	getFromGuild($gUser->id,$gGuild->id,$lumber,$stone,$food,$metal,$runes);
	$gUser = sqlgetobject("SELECT * FROM `user` WHERE `id` = ".$gUser->id);
}
else if(isset($f_send) && !empty($f_text))
{
    $o = null;
    $o->text = $f_text;
    $o->user = $gUser->id;
    $o->guild = $gGuild->id;
    $o->time = time();
    
    sql("INSERT INTO `guild_msg` SET ".obj2sql($o));
    Redirect(Query("?sid=?"));
}

$gGuild = sqlgetobject("SELECT g.*,u.`name` as `foundername` FROM `guild` g,`user` u WHERE u.`id`=g.`founder` AND g.`id`=".$gUser->guild);

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
<?=renderGuildTabbar("Allgemein")?>
</div><div class="tabpane">
<?php
//ist der user in einer gilde?
if($gUser->guild == 0)
{//neeee ------------------------------------------------------------
?>
	<form method="post" action="<?=Query("guild.php?sid=?")?>">
	<h4>Gilde gr&uuml;nden</h4>
	Um eine Gilde zu gründen benötigst du folgende Rohstoffe: 2000,2000,2000,2000<br>
	Gewünschter Name: <input type="text" name="guildname"><br>
	Farbe: <input type="text" name="color"><br>
	<input type="submit" name="create" value="gr&uuml;nden">
	</form>

	<form method="post" action="<?=Query("guild.php?sid=?")?>">
	<h4>Gilde beitreten</h4>
	Um eine Gilde zu beizutreten mu&szlig;t du dich bei einer bewerben:<br>
	Name der Gilde: <select name="guildname"><?=PrintObjOptions(sqlgettable("SELECT `name` FROM `guild` ORDER BY `name`"),"name","name")?></select><br>
	Kommentar:<br>
	<textarea name="comment" rows="4" cols="40">hier einen Bewerbungstext</textarea><br>
	<input type="submit" name="join" value="abschicken">
	</form>
<?php
}
else
{//gilde vorhanden ------------------------------------------------------------
?>
<h4>Gilde '<?=$gGuild->name?>'</h4>
<?php $gRight = sqlgettable("SELECT * FROM `guild_right` ORDER BY `right` ASC","right"); ?>
<form method="post" action="<?=Query("guild.php?sid=?")?>">
	<div style="padding-top:20px;padding-left:0;padding-right:0;padding-bottom:0;">
	<table>
		<tr>
			<td>Rohstoffe:</td>
			<?php foreach($gRes as $n=>$f)echo '<td><img alt="'.$f.'" src="'.g('res_'.$f.'.gif').'"></td><td>'.ktrenner(floor($gGuild->$f)).'</td>'; ?>		
			<td></td>
		</tr>
		<tr>
			<td>Maximum:</td>
			<?php foreach($gRes as $n=>$f){
				$name="max_$f";
				echo '<td></td><td>'.ktrenner(floor($gGuild->$name),"grey");
				echo '</td>';
			} ?>
			<td></td>
		</tr>
		<tr>
			<td></td>
			<?php foreach($gRes as $n=>$f){
				$name="max_$f";
				echo "<td></td><td>";
				DrawBar($gGuild->$f,$gGuild->$name);
				echo "</td>";
			} ?>
			<td></td>
		</tr>
		<tr>
			<td></td>
			<?php foreach($gRes as $n=>$f)echo '<td><img alt="'.$f.'" src="'.g('res_'.$f.'.gif').'"><td align="right"><input value="0" type="text" size="8" name="'.$f.'"></td>'; ?>
			<td></td>
		</tr>
		<tr>
		<td align="right" colspan="<?=(count($gRes)*2+2)?>">
			<input type="submit" name="get" value="rausholen">
			<input type="submit" name="getall" value="alles rausholen">
			<input type="submit" name="put" value="reintun">
			<input type="submit" name="putall" value="alles reintun">
		</td>
		</tr>
	</table>
	</div>
	</form>
<br>
	
	<table width=100% border=0>
	<tr><td valign="top" style="padding-left:20px;padding-bottom:15px;">
	Gründer: <?=$gGuild->foundername?><br>
	Bewerbungen: <?=sqlgetone("SELECT COUNT(`user`) FROM `guild_request` WHERE `guild`=".$gGuild->id)?>
	
	<p><span style="font-size:14px;font-weight:bold;">* <a style="font-size:14px;" href="<?=Query("guild_forum.php?sid=?")?>">Gildeforum</a></span></p>
	<?php if(empty($gGuild->forumurl)){ ?>
	<table border=0 cellspacing=0 cellpadding=0>
	<?$a=getnewArticles(); foreach ($a as $o){
		$neu="";
		if($o->nc>0) $neu="<span style='color:#9d0000'>&nbsp;(".$o->nc." ".($o->nc>1?"neue Kommentare":"neuer Kommentar").")</span>";?>
	<tr><td style="padding-left:10px;color:<?=($o->new?"red":"black")?>;"> * <?="<a href='".Query("guild_forum?sid=?&guild=".$o->guild."&article=".$o->id)."'>".substr($o->head,0,60).(strlen($o->head)>60?"...":"")?></a><?=$neu?></td></tr>
	<?}?>
	<tr><td>&nbsp;</td></tr>
	<tr><td>[<a href="<?=query("?sid=?&do=markallforumread")?>">alles gelesen markieren</a>]</td></tr>
	</table>
	<?php } else { ?>
		Das Gildenforum befindet sich hier:<br><a target="_blank" href="<?=$gGuild->forumurl?>"><?=$gGuild->forumurl?></a>
	<?php } ?>
	</p>
	
	<p><span style="font-size:14px;font-weight:bold;">* <a style="font-size:14px;" href="<?=Query("guild_log.php?sid=?")?>">Gildenlog</a></span></p>
	<table border=0 cellspacing=0 cellpadding=0>
	<?$g = sqlgettable("SELECT * FROM `guildlog` WHERE (
		`guild1` = ".$gGuild->id." OR 
		`guild2` = ".$gGuild->id.") ORDER BY `time` DESC LIMIT 0,3");
	 foreach ($g as $o){?>
	<a href="<?=Query("guild_log.php?sid=?")?>"><tr><td style="padding-left:10px;"><?=date("d.m H:i",$o->time);?>, <?=$o->trigger?> von <?php guild_echo_user($o->user1)?> gegen <?php guild_echo_user($o->user2)?> (<?=$o->count?>x)</td></tr></a>
	<?}?>
	</table>
	</td>
	<td valign=top>
	 <form action="<?=Query("?sid=?")?>" method=post>
	 <table>
	    <tr><th colspan=2>Schreischachtel</th></tr>

	    <tr><td><?=$gUser->name?></td><td align="right"><input type="submit" name="send" value="senden"></td></tr>
	    <tr><td colspan=2><textarea name="text" rows=1 cols=20 style="width:400px"></textarea></td></tr>
	    
	    <?php
	    $t = sqlgettable("SELECT * FROM `guild_msg` WHERE `guild`=".$gGuild->id." ORDER BY `time` DESC LIMIT 8");
	    foreach($t as $x) {
		$u = sqlgetobject("SELECT * FROM `user` WHERE `id`=".$x->user);
	    ?>
	    <tr><td style="background-color:#cfcfcf"><?=$u->name?></td><td style="background-color:#cfcfcf" align="right"><?=date("d.m H:i",$x->time)?></td></tr>
	    <tr><td colspan=2><?=nl2br(htmlspecialchars($x->text))?></td></tr>
	    <?php } ?>
	</table>
	</form>
	</td>
	</tr>
	
	</table>
	
	<?php ImgBorderStart(); ?>
		<p align="center"><?=(!empty($gGuild->gfx)?"<img src='".$gGuild->gfx."' align='middle'> ":"")?></p>
		<p align="center"><span style="font-size:13px;font-weight:bold;" >Internes Profil:</span></p>
		<?=nl2br(htmlentities($gGuild->internprofile))?>
		<p align="center"><span style="font-size:13px;font-weight:bold;" >Externes Profil:</span></p>
		<?=nl2br(htmlentities($gGuild->profile))?>
	<?php ImgBorderEnd(); ?>
	
	<SCRIPT LANGUAGE="JavaScript" type="text/javascript">
	<!--
		function showpos(x,y) {
			parent.map.location.href = "<?=Query("../".kMapScript."?sid=?")?>&x="+x+"&y="+y;
		}
	//-->
	</SCRIPT>
			

		<FORM METHOD=POST ACTION="<?=Query("?sid=?&x=?&y=?")?>">
			<INPUT TYPE="hidden" NAME="do" VALUE="leaveguild">
			<INPUT TYPE="hidden" NAME="id" VALUE="<?=$gUser->id?>">
			<INPUT TYPE="submit" NAME="verlassen" VALUE="verlassen">
			<INPUT TYPE="checkbox" NAME="sure" VALUE="1">sicher !
		</FORM>
		<p></p>	
<?php
} // endif guild

?>
</div></div>
</body>
</html>
<?php profile_page_end(); ?>
