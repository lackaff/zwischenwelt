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
	$uploadfile = time()."-".$_REQUEST["name"]."-".$_REQUEST["mail"]."-".basename($_FILES['archiv']['name']);
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
Urheberrechte hat hochgeladen werden. Auﬂerdem muﬂ man mit den Lizenzbestimmungen einverstanden sein.
Code wird unter der GPL ver&ouml;ffentlicht und andere Daten unter der 
<a target="_blank" href="http://creativecommons.org/licenses/by-nc-sa/2.0/">[CC]Attribution-NonCommercial-ShareAlike 2.0</a>.
Alle Daten mit eventuellen Hinweisen bitte in ein Archiv zusammenpacken (zB. zip, tar,bz2) und das Archiv hochladen. Der Name und die eMail
werden zusammen mit den hochgeladenen Daten gespeichert, damit es m&ouml;glich ist den Urheber der Daten zu kontaktieren.
<br>
<br>
<h2>Hochlade Formular</h2>
<form enctype="multipart/form-data" method="post" action="upload.php">
	<table>
		<tr><td>kompletter Name</td><td><input type="text" name="name" size=32 value="<?=$_REQUEST["name"]?>"> (zB. Hans Mustermann)</td></tr>
		<tr><td>eMail</td><td><input type="text" name="mail" size=32 value="<?=$_REQUEST["mail"]?>"> (zB. hans.m@domain.de)</td></tr>
		<tr><td>Archiv Datei</td><td><input name="archiv" type="file" size=32>(zB. zip, tar.bz2)</td></tr>
		<tr><td>Zustimmung</td><td>
			<input type="checkbox" name="ok" value=1> Ja, ich bin damit einverstanden, daﬂ meine hochgeladenen Daten unter GPL (Code) und [CC]Attribution-NonCommercial-ShareAlike 2.0 (nicht Code) ver&ouml;ffentlicht werden.
		</td></tr>
		<tr><td></td><td><input type="submit" name="upload" value="hochladen"></td></tr>
	</table>
</form>


<?php include("footer.php"); ?>
