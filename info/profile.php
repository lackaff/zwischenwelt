<?php
require_once("../lib.main.php");
require_once("../lib.score.php");
Lock();


//sets the color of player id, html color value
function setPlayerColor($id,$color) {
	if(eregi("^[a-zA-Z]+$",$color) || eregi("#[0-9a-fA-F][0-9a-fA-F][0-9a-fA-F][0-9a-fA-F][0-9a-fA-F][0-9a-fA-F]$",$color))sql("UPDATE `user` SET `color`='$color' WHERE `id`=".intval($id));
}

function setPlayerProfil($id,$profil) {
	sql("DELETE FROM `userprofil` WHERE `id`=".intval($id));
	sql("INSERT INTO `userprofil` SET `profil`='".addslashes($profil)."' , `id`=".intval($id));
}

$flaglist = array(
//	"DropDownMenu benutzen (geht nur im Firefox!)?" => kUserFlags_DropDownMenu,
	"LogFrame anzeigen?" => kUserFlags_ShowLogFrame,
	"WikiHilfe ausblenden" => kUserFlags_DontShowWikiHelp,
	"Monsterkampfberichte entfernen" => kUserFlags_NoMonsterFightReport,
	"bei neuen Gebäuden automatisch Upgrades planen" => kUserFlags_AutomaticUpgradeBuildingTo,
	"NoobTips ausblenden" => kUserFlags_DontShowNoobTip,
	"Lagerkapazität anzeigen" => kUserFlags_ShowMaxRes,
	"Tabs Deaktivieren (bei Abstürzen im IE)" => kUserFlags_NoTabs,
);

profile_page_start("profile.php");
$s=sqlgetobject("SELECT `value` FROM `guild_pref` WHERE `var`='schulden_".$gUser->id."'");
if(isset($f_ok)){
	$o = null;
	$o->mail = $f_mail;
	$o->homepage = $f_homepage;
	$o->gfxpath = $f_gfxpath;
	if(isset($f_localstyles) && $f_localstyles)$o->localstyles = 1;
	else $o->localstyles = 0;
	if(isset($f_iplock) && $f_iplock)$o->iplock = 1;
	else $o->iplock = 0;
	$o->flags = intval($gUser->flags);
	foreach($flaglist as $name=>$flag){
		if(isset(${"f_flag_".$flag}) && ${"f_flag_".$flag}) {$o->flags |= intval($flag);}
		//else if($o->flags & $flag) {$o->flags ^= $flag;$outi .= "xor $flag";}
		else {$o->flags &= ~intval($flag);}
	}
	if(strlen($f_pass) >= 6)sql("UPDATE `user` SET `pass`=PASSWORD('".addslashes($f_pass)."') WHERE `id`=".$gUser->id);
	sql("UPDATE `user` SET ".obj2sql($o)." WHERE `id`=".$gUser->id);
	header("Location: ".query("profile.php?sid=?"));
	exit;
}
if(isset($f_do)){
	switch ($f_do){
		case "change color":
			if (isset($f_random)) $f_color = sprintf("#%02X%02X%02X",rand(0,255),rand(0,255),rand(0,255));
			setPlayerColor($gUser->id,addslashes($f_color));
			$gUser = sqlgetobject("SELECT * FROM `user` WHERE `id` = ".$gUser->id);
		break;
		case "change profil":
			setPlayerProfil($gUser->id,addslashes($f_profil));
		break;
		
		case "payweltbank":
			$sum=0;
			TablesLock();
			$user = sqlgetobject("SELECT * FROM `user` WHERE `id`=".$gUser->id);
			$guild = sqlgetobject("SELECT * FROM `guild` WHERE `id`=".kGuild_Weltbank);
			if($user && $guild)foreach ($gRes as $n=>$f){
				if($_REQUEST[$f] < 0)$x = 0;
				else $x = $_REQUEST[$f];
				$x = min($x,$user->{$f}); //nicht mehr als user hat
				$x = min($x,abs($guild->{$f}-$guild->{"max_$f"})); //nicht mehr als noch ins lager passt
				$sum += $x;
				
				$gUser->{$f} -= $x;
				
				sql("UPDATE `guild` SET `$f`=`$f`+(".floatval($x).") WHERE  `id`=".kGuild_Weltbank);
				sql("UPDATE `user` SET `$f`=`$f`-(".floatval($x).") WHERE `id`=".$gUser->id);
			}
			$s = sqlgetobject("SELECT `id`,`value` FROM `guild_pref` WHERE `var`='schulden_".$gUser->id."' AND `guild`=".kGuild_Weltbank);
			if(is_object($s) && $sum>0){
				$x = intval($s->value)-$sum;
				sql("UPDATE `guild_pref` SET `value`=".intval($x)." WHERE `id`=".$s->id);
				if($x<=0)sql("DELETE FROM `guild_pref` WHERE `id`=".$s->id);
			}
			sql("UPDATE `user` SET `guildpoints`=`guildpoints`+".intval($sum)."*1.2 WHERE `id`=".$gUser->id);
			TablesUnlock();
			//Redirect(Query("?sid=?"));
		break;
		
		default:
		break;
	}
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 transitional//EN"
   "http://www.w3.org/TR/html4/transitional.dtd">
<html>
<head>
<link rel="stylesheet" type="text/css" href="<?=GetZWStylePath()?>">
<title>Zwischenwelt - Einstellungen</title>

</head>
<body>
<?php
include("../menu.php");
?>

<!-- Punkte -->
<a style="font-weight:bold" href="<?=query("../info/sessions.php?sid=?")?>">Session</a>
<hr>
<span style="font-size:14px;font-weight:bold;">Punkte</span>
<?
$gpts=getBuildingPts($gUser->id);
$mpts=getBasePts($gUser->id);
$tpts=getTechPts($gUser->id);
$apts=getArmyPts($gUser->id);
echo "<table>";
echo "<tr><td>Punkte fuer Gebaeude + Upgrades</td><td align=right>$gpts</td></tr>";
echo "<tr><td>Punkte fuer Technologie </td><td align=right>$tpts</td></tr>";
echo "<tr><td>Punkte fuer Misc </td><td align=right>$mpts</td></tr>";
echo "<tr><td>Punkte fuer Einheiten </td><td align=right>$apts</td></tr>";
echo "<tr><td style='border-top:1px dotted grey;'>Total</td><td align=right style='border-top:1px solid black;'>".($gpts+$mpts+$tpts+$apts)."</td></tr>";
echo "</table>";
?>
<hr>
<span style="font-size:14px;font-weight:bold;">Gilde</span>
<?
if($gUser->guild==0){?>
<p><b>Keine Gilde</b><br>
<? if(is_object($s)){?>noch <span style="color:red;"> -<?=ceil($s->value)?></span> Pts Schulden bei der Weltbank<?}?>
</p>
<?}
else{
$gRight = sqlgettable("SELECT * FROM `guild_right` ORDER BY `right` ASC","right");
require_once("../lib.guild.php");
?>
<table cellspacing=4 cellpadding=1>
<tr><th style="border-bottom:1px solid grey;">Status</th><th style="border-bottom:1px solid grey;">Minuspunktelimit</th></tr>
<td align=left valign=middle><?
if(sqlgetone("SELECT `founder` FROM `guild` WHERE `id`=".$gUser->guild)!=$gUser->id){
		if($gUser->guildstatus!=1 && $gUser->guildstatus!=0){
			foreach($gRight as $r){
			if($gUser->guildstatus%$r->right==0){
				?><img src="<?=g($r->gfx)?>" title="<?=$r->desc?>">&nbsp;<?
			}
		}
	}else{
	?><img src="<?=g("tool_cancel.png")?>" title="hat keine Rechte"><?
	}
}else{
	?><center><img src="<?=g("icon/guild-founder.png")?>" title="Gildegründer"></center><?
}
?>&nbsp;</td><td align=right><?=ktrenner((-1)*getGPLimit($gUser->id),"#4444cc","#aa5555")?>
</table><br>
<center><span style="font-size:14px;font-weight:bold;">Nachricht des Tages</span><br>
<?ImgBorderStart();?>
<span style="font-size:12px;font-style:italic;"><?=sqlgetone("SELECT `message` FROM `guild` WHERE `id`=".$gUser->guild)?></span>
<?ImgBorderEnd();?></center>
<?
}
?>
<hr>
<h4>Zahlung an die Weltbank</h4><?if($gUser->guild!=kGuild_Weltbank){?>
<? if(is_object($s)){?>noch <span style="color:red;"> -<?=ceil($s->value)?></span> Pts Schulden bei der Weltbank<br>&nbsp;<br><?}?>
<form method="post" action="<?=query("profile.php?sid=?")?>">
<input type=hidden name=do value=payweltbank>
<table>
<tr><?php foreach($gRes as $n=>$f)echo '<td><img alt="'.$f.'" src="'.g('res_'.$f.'.gif').'"><td align="right"><input value="0" type="text" size="8" name="'.$f.'"></td>'; 
$s=sqlgetobject("SELECT `value` FROM `guild_pref` WHERE `var`='schulden_".$gUser->id."'");?>
<td><input type=submit name=give value="<?=(is_object($s)?"Zurückzahlen":"Spenden")?>"></td>
</tr>
</table>
</form><?}else echo "du bist in der weltbank<br>"?>


<hr>
<form method="post" action="<?=query("profile.php?sid=?")?>">
<table>
	<tr><th>Graphikpfad</td><td><input type="text" size="64" name="gfxpath" value="<?=$gUser->gfxpath?>"></td></tr>
	<tr><th>lokales CSS-Stylesheet</td><td><input type="checkbox" name="localstyles" value="1" <?=($gUser->localstyles?"checked":"")?>> benutzten ? (zwstyle.css im gfx-pack)<br>(geht bei manchen Browsern nicht)</td></tr>
	<tr><td colspan=2>hier kann man den Pfad zu den lokalen Graphiken eintragen oder leer lassen, um die online Graphiken zu verwenden. <a href="http://zwischenwelt.org/gfx.zip"><u><b>hier</b></u></a> kann man das Graphik Packet runterladen. Einfach lokal enpacken (es wird ein gfx/ Ordner angelegt) und den Browserkompatiblen Pfad zum Verzeichnis, in das es entpackt wurde + gfx/, angeben.</td></tr>
	<tr><th>GFX-Pack unter Firefox 1.5</th><td>
		Für Firefox 1.5 muss man aus Sicherheitsgründen Webseiten extra erlauben, solche Grafikpackete zu verwenden, wie das geht steht <a href="http://www.firefox-browser.de/wiki/Lokale_Bilder"><u><b>hier</b></u></a>.<br>
		Im Prinzip muss man in die user.js nur folgende Zeilen einfügen:
		<pre>
		user_pref("capability.policy.policynames", "localfilelinks"); 
		user_pref("capability.policy.localfilelinks.sites", "http://www.zwischenwelt.org"); 
		user_pref("capability.policy.localfilelinks.checkloaduri.enabled", "allAccess"); 
		</pre>
		Achtung, NICHT http://zwischenwelt.org  (ohne www) , sondern immer MIT "www.", scheint sonst Probleme zu geben.<br>
		Vielen Dank an scara2 für diese Tipps !
		</td></tr>
	<tr><th>IP lock benutzen?</th><td><input type="checkbox" name="iplock" value="1" <?=($gUser->iplock?"checked":"")?>> anklicken, wenn die Benutzersession an eine IP gebunden werden soll</td></tr>
	<tr><th>eMail</td><td><input type="text" size="64" name="mail" value="<?=$gUser->mail?>"></td></tr>
	<tr><td></td><td>Mailadresse - taucht im Spiel nirgends auf</td></tr>
	<tr><th>Homepage</td><td><input type="text" size="64" name="homepage" value="<?=$gUser->homepage?>"></td></tr>
	<tr><td></td><td>eigene Homepage</td></tr>
	<tr><th>Password</td><td><input type="password" size="64" name="pass" value=""></td></tr>
	<tr><td></td><td>mindestens 6 Zeichen</td></tr>
	<?php foreach($flaglist as $text=>$flag){ ?>
	<tr><td colspan=2><input type="checkbox" name="flag_<?=$flag?>" value="1" <?=(intval($gUser->flags) & $flag?"checked":"")?>> <b><?=$text?></b> 
	<?php } ?>
	<tr><td colspan=2 align=left><input type="submit" name="ok" value="übernehmen"></td></tr>
</table>
</form>


<!--Farbe-->
<p>&nbsp;</p>
<form action="<?=Query("profile.php?sid=?")?>" method="post">
	<input type="hidden" name="do" value="change color">
	<b>Farbe:</b> <input style="border:solid <?=$gUser->color?> 2px" type="text" size="8" name="color" value="<?=$gUser->color?>"> 
	<input type="submit" name="set" value="übernehmen">
	<input type="submit" name="random" value="zufall">
</form>
	
<!-- profil -->
<p>&nbsp;</p>
<?php
$profil = sqlgetone("SELECT `profil` FROM `userprofil` WHERE `id`=".$gUser->id);
if($gUser->id) {
	?>
	<form action="<?=Query("profile.php?sid=?")?>" method="post">
	<input type="hidden" name="do" value="change profil">
	<TEXTAREA NAME="profil" ROWS="5" COLS="50"><?=htmlspecialchars($profil)?></TEXTAREA><br>
	<input type="submit" value="Profil eintragen">
	</form>
	<?php
} else {
	?>
	Profil:
	<div style="border:1px solid black">
	<?=nl2br(htmlspecialchars($profil))?>
	</div>
	<?php
}
?>
</body>
</html>
<?php profile_page_end(); ?>
