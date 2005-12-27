<?php
require_once("../lib.main.php");
require_once("../lib.guild.php");

Lock();
profile_page_start("viewguild.php");

$gGuild = sqlgetobject("SELECT * FROM `guild` WHERE `id`=".intval($f_id));
if($gGuild){
	$members = sqlgettable("SELECT `id`,`name`,`color`,`guildpoints` FROM `user` WHERE `guild`=".$gGuild->id." ORDER BY `name` ASC");
	$gcs=getGuildCommander($gGuild->id);
} else {
	$members = Array();
	$gcs = Array();
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 transitional//EN"
   "http://www.w3.org/TR/html4/transitional.dtd">
<html>
<head>
<link rel="stylesheet" type="text/css" href="../styles.css">
<title>Zwischenwelt - Gildendetails</title>

</head>
<body>
<?php
include("../menu.php");

?>
	
<table border=0 cellpadding=2 cellspacing=2>
<tr><td align="center">
  <?php ImgBorderStart("s1","jpg","#ffffee","",32,33); ?>
  <div align="center">
    <p><span style="font-family:serif;font-size:16px;"><?=$gGuild->name?></span></p>
    <p><span style="font-family:serif;font-size:12px;font-style:italic;">Gr&uuml;nder: <?=sqlgetone("SELECT `name` FROM `user` WHERE `id`=".$gGuild->founder)?></span></p>
  </div>
  <table align="center">
	  <tr><th></th><th>Mitglieder</th></tr>
		<?php
		foreach($members as $u){
			echo '<tr><td style="background-color:'.$u->color.'">&nbsp;</td><td><a style="font-family:verdana;" href="'.query("msg.php?sid=?&show=compose&to=".urlencode($u->name)).'">'.$u->name.'</a></span></td></tr>';
		}
		?>
		</table>
	
	<?php ImgBorderEnd("s1","jpg","#ffffee",32,33); ?>
	</td>
	<td rowspan=2 valign="top" style="width=500px;">
		<p align="center">
        <?=(!empty($gGuild->gfx)?"<img src='".$gGuild->gfx."' align='middle'> ":"")?>
      	</p>
		<?php ImgBorderStart(); ?>
		<div style="width:100%;">
		<?=nl2br(htmlspecialchars($gGuild->profile))?>
		</div>
		<?php ImgBorderEnd(); ?>
	</td>
</tr>
</table>

<form method="post" action="<?=Query("guild.php?sid=?")?>">
<h4>Sich bei dieser Gilde bewerben</h4>
Name der Gilde: <input type="hidden" name="guildname" value="<?=$gGuild->name?>"><?=$gGuild->name?><br>
Kommentar:<br>
<textarea name="comment" rows="4" cols="35">hier ein Bewerbungstext</textarea><br>
<input type="submit" name="join" value="abschicken">
</form>

</body>
</html>
<?php profile_page_end(); ?>
