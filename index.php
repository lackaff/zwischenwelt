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
Zwischenwelt is a browser-based strategy game in a medieval fantasy setting. Players build and manage cities,
 raise armies to attack enemies and defend their territories, engage in diplomacy, research and cast magic
 spells, and much more. Although the game is still under development, the game is very playable. Log in and
 try it out!<br><br> 
<!--
Das Browsergame Zwischenwelt ist eine Kreuzung aus Civilisation, Master of Magic und SimCity.
Mitlerweile sind wir über das Alpha Stadium weit hinaus und man kann schon einiges machen:
Forschen, Gebäude bauen, Armeen kommandieren, Zauber sprechen, Handel betreiben oder einfach nur Creepen.<br>
Aber am besten einfach anmelden und testen.<br>
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
A map of the Zwischenwelt (dated <?=date("d-m-Y H:i",$lastminimap)?>) can be viewed here: [<a target="_blank" href="<?=$minimapfile?>">World Map</a>]<br>
<?php } // endif?>
<br>
das Zwischenwelt Team
</div>
<br>



<span id=changelog><h1>Zwischenwelt Updates</h1></span>
<div class="changelog">
<?php
	$cl = file("ChangeLog");
	$l = array();
	$hd = $cl[0];
	$l[$hd] = "";
	for($i=1;$i<sizeof($cl);++$i){
		$line = trim($cl[$i]);
		if(empty($line)){
			$hd = trim($cl[$i+1]);
			$l[$hd] = "";
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
