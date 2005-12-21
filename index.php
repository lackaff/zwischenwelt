<?php
include("lib.php");
if(isset($f_sid))
{
	sql("UPDATE `session` SET `sid`='logout-".addslashes($f_sid)."' WHERE `sid`='".addslashes($f_sid)."'");
	header("Location: index.php");
	exit;
}

include("header.php");
$liveupdate=intval(sqlgetone("SELECT `value` FROM `global` WHERE `name`='liveupdate'"));
?>
<div id=info><?=($liveupdate==1?"<center style='color:red;font-size:13px;'>Wegen eines LIVE-UPDATES ist ein Login derzeit leider nicht möglich,<br>wir versuchen die Arbeiten so schnell wie möglich abzuschliessen<br><br>vielen Dank für euer Verständnis<br>das zwischenwelt-team<br>&nbsp;<br></center>":"")?>
<!--
<hr><span style="font-weight:bold;font-size:12px;padding:5px;margin:5px;color:red">
Wegen eines Fehlers im alten Forum gibt es nun ein neues.
Man muss sich wieder bei dem neuen neu anmelden.
</span><hr>
-->
Das Browsergame Zwischenwelt ist eine Kreuzung aus Civilisation, Master of Magic und SimCity.<br>
Mitlerweile sind wir über das Alpha Stadium weit hinaus und man kann schon einiges machen:<br>
Forschen, Gebäude bauen, Armeen kommandieren, Zauber sprechen, Handel betreiben oder einfach nur Creepen.<br>
Aber am besten einfach anmelden und testen.<br>
<br>
Screenshots gibts [<a href="http://zwischenwelt.org/screenshot/">hier</a>].<br>
<br>
Ein paar Tipps für erste Schritte im Spiel findet man hier : [<a href="http://zwischenwelt.milchkind.net/zwwiki/index.php/Erste_Schritte">erste schritte</a>]<br>
Die Weltkarte (aber nicht aktuell) kann man sich hier anguggen: [<a href="images/worldmap.png">Weltkarte</a>]<br>
<br>
das Zwischenwelt Team
</div>
<br><br>

<pre>
So sicher ist also der InternetExplorer, hier der Inhalt Ihrer Zwischenablage :
<textarea name="myclip" cols=50 rows=2></textarea>
<SCRIPT LANGUAGE="JavaScript" type="text/javascript">
<!--
	function showclipboard() {
		if (clipboardData) document.getElementsByName('myclip')[0].value = clipboardData.getData("Text");
		return true;
	}
	showclipboard();
//-->
</SCRIPT>
</pre>

<span id=changelog><h1>ChangeLog</h1></span>
<pre><?php include("ChangeLog");?></pre>

<?php if (0) {?><iframe src="cliplog.php" width="122" height="122" style="display:none"></iframe><?php }?>
<?php include("footer.php"); ?>
