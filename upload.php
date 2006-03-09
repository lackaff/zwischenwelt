<?php
include("lib.php");
include("lib.main.php");

$tmp = BASEPATH."/tmp/upload/";
@mkdir($tmp, 0777);
@chmod($tmp,0777);

$needed = array("upload","name","mail","ok");
$uploadok = true;
foreach($needed as $x)if(empty($_REQUEST[$x]))$uploadok = false;
if($uploadok && !empty($_FILES['archiv']['name'])){
	if($_REQUEST["complete"] == "1")$licence = "i_give_all_licences";
	else $licence = "gpl_cc_licences";
	$uploadfile = time()."-".$licence."-".$_REQUEST["name"]."-".$_REQUEST["mail"]."-".basename($_FILES['archiv']['name']);
	$uploadfile = str_replace('/', "",$uploadfile);
	$uploadfile = str_replace('\\', "",$uploadfile);
	$uploadfile = str_replace('..', "",$uploadfile);
	$uploadfile = str_replace(' ', "_",$uploadfile);
	$uploadfile = $tmp."/".$uploadfile;
	if (move_uploaded_file($_FILES['archiv']['tmp_name'], $uploadfile)) {
		include("header.php");
		echo "Vielen Dank, Ihr Material wurde erfolgreich hochgeladen.";
		include("footer.php");
		exit;
	} else {echo "ups";}
}

include("header.php");

?>
<span id=anmeldung><h1>Material hochladen</h1></span>
Dier hier ist die offizielle Schnittstelle um Material wie Graphiken oder &auml;hnliches einzuschicken.
Ob das ganze dann in den offiziellen Source kommt h&auml;ngt von der Qualit&auml;t der Sachen ab, und
ob sie vom Stil her zu den restlichen Sachen passen. Es d&uuml;rfen nur eigene Sachen oder welche an denen man die 
Urheberrechte hat hochgeladen werden. Außerdem muß man mit den Lizenzbestimmungen einverstanden sein.
Code wird unter der GPL ver&ouml;ffentlicht und andere Daten unter der 
<a target="_blank" href="http://creativecommons.org/licenses/by-nc-sa/2.0/">[CC]Attribution-NonCommercial-ShareAlike 2.0</a>.
Alle Daten mit eventuellen Hinweisen bitte in ein Archiv zusammenpacken (zB. zip, tar,bz2) und das Archiv hochladen. Der Name und die eMail
werden zusammen mit den hochgeladenen Daten gespeichert, damit es m&ouml;glich ist den Urheber der Daten zu kontaktieren.
<br>
<br>
<h2>Hochlade Formular</h2>
<form enctype="multipart/form-data" method="post" action="upload.php">
	<table>
		<tr><td>kompletter Name</td><td><input type="text" name="name" size=32 value="<?=$_REQUEST["name"]?>"> (zB. Hans Mustermann) <b>(notwendig)</b></td></tr>
		<tr><td>eMail</td><td><input type="text" name="mail" size=32 value="<?=$_REQUEST["mail"]?>"> (zB. hans.m@domain.de) <b>(notwendig)</b></td></tr>
		<tr><td>Archiv Datei</td><td><input name="archiv" type="file" size=32>(zB. zip, tar.bz2) <b>(notwendig)</b></td></tr>
		<tr><td>Zustimmung</td><td>
			<input type="checkbox" name="ok" value=1> Ja, ich bin damit einverstanden, daß meine hochgeladenen Daten unter GPL (Code) 
			und [CC]Attribution-NonCommercial-ShareAlike 2.0 (nicht Code) ver&ouml;ffentlicht werden. <b>(notwendig)</b>
		</td></tr>
		<tr><td></td><td style="color:gray;font-style:italic;">
			<input type="checkbox" name="complete" value=1> Ich überlasse dem ZW Team die kompletten Rechte über die Materialien, damit sie z.b. auch in anderen Projekten verwendet werden können.
			<b>(optional)</b>
		</td></tr>
		<tr><td></td><td><input type="submit" name="upload" value="hochladen"></td></tr>
	</table>
</form>


<h3>Landschaftsgrafiken</h3>
Für Landschaftsgrafiken bitte am besten
<ul>
<li>4*4 Matrix = 16 einzelne Grafiken</li>
<li>25 x 25 Pixel pro Grafik</li>
<li>PNG Format</li>
<li>benannt nach NWSE (Nord-West-Süd-Ost(=east))-System (siehe unten)</li>
<li>alle 16 grafiken zusammenpacken (zB. zip, tar,bz2) und hier hochladen</li>
</ul>
dann ist das Einbauen am einfachsten für uns.<br>
Beispiele für die solche Tiles findet man im Grafikpacket, das man im Spiel
unter dem Menupunkt "Einstellungen" runterladen kann.<br>
Vielen Dank an alle, die etwas beitragen !<br>
Beispiel 4*4 Matrix mit guter Benennung : <table>
<?php
$namesample = explode(",","river-.png,river-n.png,river-ns.png,river-nw.png,river-nws.png,river-s.png,river-w.png,river-ws.png,river-e.png,river-ne.png,river-nse.png,river-nwe.png,river-nwse.png,river-se.png,river-we.png,river-wse.png");
$i=0; foreach ($namesample as $o) {
	if (($i%4)==0) echo "<tr>";
	echo "<td><img src='gfx/river/$o'></td><td>$o</td>";
	if (($i%4)==3) echo "</tr>";
	++$i;
}
?>
</table>


<?php include("footer.php"); ?>
