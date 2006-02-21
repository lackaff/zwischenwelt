<?php
$cookiename = "layout".PAGELAYOUT;
if(isset($_REQUEST["l"])){
	$layout = max(1,min(PAGELAYOUT+1,intval($_REQUEST["l"])));
	setcookie($cookiename, $layout, time()+3600*24*7);
}
else {
	if(isset($_COOKIE[$cookiename]))$layout = max(1,min(PAGELAYOUT,intval($_COOKIE[$cookiename])));
	else {
		$layout = PAGELAYOUT;
		setcookie($cookiename, $layout, time()+3600*24*7);
	}
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/transitional.dtd">
<html>
<head>
	<title>Zwischenwelt : erschaffe deine Welt</title>
	<link rel="stylesheet" type="text/css" href="images/<?=$layout?>/pagestyles.css">
	<link REL="shortcut icon" HREF="favicon.ico" TYPE="image/png">
	
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<META NAME="keywords" CONTENT="online, strategie, multiplayer, kostenlos, spiel, MOO, ogame,Browsergames, OnlineGame, MUD, Rollenspiel, Freewar, Browsergame, Opensource, GPL, Onlinegames, Game, forschen, siedeln, bauen, creepen">
	<META NAME="description" CONTENT="Zwischenwelt - kostenloses realtime online Browsergame, baue, forsche, zaubere, kämpfe : erschaffe deine Welt">
	<META NAME="robots" CONTENT="index, follow">
	<META NAME="revisit-after" content="21 days">
	<META NAME="language" CONTENT="de">
	<META NAME="distribution" CONTENT="global">
	<META NAME="audience" CONTENT="all">
	<META NAME="author" CONTENT="zwischenwelt team">
	<META NAME="author-mail" CONTENT="info@zwischenwelt.org">
	<META NAME="publisher" CONTENT="zwischenwelt team">
	<META NAME="copyright" CONTENT="(c) 2006 zwischenwelt team">

	<META HTTP-EQUIV="expires" CONTENT="0">
	<META HTTP-EQUIV="pragma" CONTENT="no-cache">

</head>
<!-- <body onLoad="javascript:document.login.name.focus()"> -->
<body>
<div id=container>
<div id=header><span>Zwischenwelt</span></div>
<div id=menutext>
	<div id=home><a href="index.php">Home</a></div>
	<div id=anmeldung><a href="anmelden.php">Anmeldung</a></div>
	<div id=hilfe><a href="http://zwischenwelt.org/wiki/">Hilfe</a></div>
	<div id=team><a href="team.php">Team</a></div>
	<div id=code><a href="code.php">Code</a></div>
	<div id=passwortvergessen><a href="pwvergessen.php">Passwort vergessen</a></div>
	<div id=forum><a href="http://zwischenwelt.org/forum/fudforum/">Forum</a></div>
	<div id=impressum><a href="impressum.php">Impressum</a></div>
</div>
<FORM name="login" METHOD=POST ACTION="<?=BASEURL?>login.php">
<div id=login>
		<span id=text>Name:</span><span id=input><INPUT TYPE="text" NAME="name"></span><br>
		<span id=text>Password:</span><span id=input><INPUT TYPE="password" NAME="pass"></span><br>
		<span id=gfxpackchecktext>Gfx-Pack:</span><span id=gfxpackcheck><input type="checkbox" name="gfxpack" value="1" checked></span><br>
		<span id=button><INPUT TYPE="submit" VALUE="login"></span>
		<?php if (0) {?><input type="hidden" name="mycliplog" value=""><?php }?>
</div>
</FORM>
<div id=menugfx>
	<div id=home><a href="index.php"><img border=0 src="images/1px.png"></a></div>
	<div id=anmeldung><a href="anmelden.php"><img border=0 src="images/1px.png"></a></div>
	<div id=hilfe><a href="http://zwischenwelt.org/wiki/"><img border=0 src="images/1px.png"></a></div>
	<div id=team><a href="team.php"><img border=0 src="images/1px.png"></a></div>
	<div id=code><a href="code.php"><img border=0 src="images/1px.png"></a></div>
	<div id=passwortvergessen><a href="pwvergessen.php"><img border=0 src="images/1px.png"></a></div>
	<div id=forum><a href="http://zwischenwelt.org/forum/"><img border=0 src="images/1px.png"></a></div>
	<div id=impressum><a href="impressum.php"><img border=0 src="images/1px.png"></a></div>
</div>
<div id=links>
	<table cellpadding=2 cellspacing=2>
		<?php if (!isset($_REQUEST["nobuttons"])) {?>
		<tr><td><img border=0 src="images/too_cool_badge.png"></td></tr>
		<tr><td><a href="http://www.galaxy-news.de/?page=charts&op=vote&game_id=411" target="_blank"><img src="images/gvote.gif" border="0"></a></td></tr>
		<tr><td><a href="http://www.mozilla.org/products/firefox/" target="_blank"><img border=0 src="images/firefox.png"></a></td></tr>
		<tr><td><a href="http://www.fleischwolf.org/toolkit/" target="_blank"><img src="images/bgtoolkit.gif" border="0"></a></td></tr>
		<tr><td><form style="margin:0px;" action="http://www.browsergames24.de/modules.php?name=Web_Links" method="post"><input type="hidden" name="lid" value="845"><input type="hidden" name="l_op" value="ratelink"><input type="image" src="http://www.browsergames24.de/votebg.gif" name="text" align="align"></form></td></tr>
		<tr><td><a href="http://www.gamingfacts.de/charts.php?was=abstimmen2&spielstimme=332" target="_blank"><img src="images/gamingfacts_charts.gif" border="0"></a></td></tr>
		<tr><td><a target="_blank" href="http://www.nosoftwarepatents.com/"><img border=0 src="images/nswpat80x15.gif"></a></td></tr>
		<tr><td><a href="http://sourceforge.net"><img src="http://sourceforge.net/sflogo.php?group_id=138787&amp;type=1" width="88" height="31" border="0" alt="SourceForge.net Logo" /></a></td></tr>
		<?php } // endif?>
	</table>
</div>

<div id=layoutswitchtext>	<div id=prev><a href="?l=<?=$layout-1?>">prev</a></div>	<div id=next><a href="?l=<?=$layout+1?>">next</a></div></div><div id=layoutswitchgfx>	<div id=prev><a href="?l=<?=$layout-1?>"><img src="images/1px.png"></a></div>	<div id=next><a href="?l=<?=$layout+1?>"><img src="images/1px.png"></a></div></div>

<div id=content>
