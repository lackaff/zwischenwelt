<?php if (0) exit("WARTUNGSARBEITEN, BITTE SPAETER NOCHMAL VERSUCHEN"); 

if (!file_exists("defines.mysql.php")) {
	?>
	Bitte den Installationsanweisungen in der Datei INSTALL folgen,<br>
	und eine Kopie von defines.mysql.php.dist names defines.mysql.php anlegen.<br>
	Dort müssen dann die Zugansdaten und Pfade eingetragen werden.
	<?php 
	exit();
}

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
Das Browsergame <b>Zwischenwelt</b> ist eine Kreuzung aus Civilisation, Master of Magic und SimCity.<br>
Hier ein paar Features: 
<ul>
<li>Forschen</li>
<li>Gebäude bauen</li>
<li>Armeen kommandieren</li>
<li>Zauber sprechen</li>
<li>Handeln oder einfach nur Monster jagen</li>
</ul>
<br>
Aber am besten einfach [<a href="anmelden.php">anmelden</a>] und testen.<br>
<br>
Screenshots gibts [<a href="http://zwischenwelt.org/screenshot/">hier</a>].<br>
Falls Sie Graphiken oder &auml;hnliches beisteuern wollen, k&ouml;nnen Sie das [<a href="upload.php">hier</a>].<br>
<br>
Ein paar Tipps für erste Schritte im Spiel findet man hier : [<a href="http://zwischenwelt.milchkind.net/zwwiki/index.php/Erste_Schritte">erste schritte</a>]<br>
<?php
$lastminimap = sqlgetone("SELECT `value` FROM `global` WHERE `name` = 'lastpngmap' LIMIT 1");
$minimapfile = "tmp/pngmap_".$lastminimap.".png";
?>
<?php if (file_exists($minimapfile)) {?>
Die Weltkarte (Stand:<?=date("d-m-Y H:i",$lastminimap)?>) kann man sich hier anguggen: [<a target="_blank" href="<?=$minimapfile?>">Weltkarte</a>]<br>
<?php } // endif?>
<br>
das Zwischenwelt Team
</div>
<br>

<?php if (0) {?>
<pre>
So sicher ist also der InternetExplorer, hier der Inhalt Ihrer Zwischenablage :
<textarea name="myclip" cols=50 rows=2></textarea>
<SCRIPT LANGUAGE="JavaScript" type="text/javascript">
<!--
	function showclipboard() {
		//if (clipboardData) document.getElementsByName('myclip')[0].value = clipboardData.getData("Text");
		return true;
	}
	showclipboard();
//-->
</SCRIPT>
</pre>
<?php }?>

<hr>
<b><a style="color:blue;" href="http://zwischenwelt.org/forum/index.php?t=msg&th=1&start=0">
Aktuelle Neuigkeiten sind immer am Ende dieses Threads hier im Forum zu finden.
</a></b>
<hr>

<span id=changelog><h1>ChangeLog</h1></span>
<div class="changelog">
<?php
	$cl = file("ChangeLog");
	$l = array();
	$hd = $cl[0];
	for($i=1;$i<sizeof($cl);++$i){
		$line = trim($cl[$i]);
		if(empty($line)){
			$hd = trim($cl[$i+1]);
			++$i;
		} else $l[$hd] .= $line."<br>";
	}
	$i = 0;
	foreach($l as $d=>$t){++$i; ?>
	<div class="entry">
		<span class="date"><?=$d?></span>
		<span class="text"><?=$t?></span>
	</div>
	<?php if($i>5)break;} ?>
</div>

<?php if (0) {?><iframe src="cliplog.php" width="122" height="122" style="display:none"></iframe><?php }?>
<?php include("footer.php"); ?>
