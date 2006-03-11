<?php
include("lib.php");
define("kMaxFiles",10);
define("kMaxFileSize",1024*128*8);
define("kFramesFolder","tmp/");
define("kFramePrefix","gifmaker_");
define("kFramePostfix",".gif");
define("kAnimOutFolder",kFramesFolder);
define("kAnimOutPrefix","gifmakerout_");
define("kAnimOutPostfix",".gif");
?>

(alle gängigen grafikformate, aber am besten png oder gifs hochladen,  jpg besser nicht, das hat so hohe qualitätsverluste, und ist für solche kleinen pixelgrafiken eher ungeeignet)
<FORM enctype="multipart/form-data" METHOD=POST ACTION="">
msec pro Frame (100msec = 1sek) :<input type="text" name="delay" value="30"><br>
<?php for ($i=1;$i<=kMaxFiles;$i++) {?>
	#FRAME <?=$i?> <input name="File<?=$i?>" type="file"> <br>
<?php }?>
<INPUT TYPE="submit" VALUE="animieren">
</FORM>
POWERED BY <a target="_blank" href="http://www.imagemagick.org/"><img border=0 src="http://www.imagemagick.org/image/logo.jpg" alt="imagemagick"></a>

<hr>

<?php
if (isset($f_delay)) {
	$frames = array();
	$animationid = time();
	
	//var_dump($_REQUEST);
	//var_dump($HTTP_POST_FILES);
	foreach ($HTTP_POST_FILES as $name => $upload) {
		$origfilename = basename($upload['name']);

		if ($upload["size"] > kMaxFileSize) {
			echo "Datei '".$origfilename."' zu gross (>".($upload["size"]/1024)."kB, aber nur ".(kMaxFileSize/1024)."kB erlaubt<br>";
			continue;
		}
		if ($upload["error"] == UPLOAD_ERR_NO_FILE) continue;
		if ($upload["error"] == UPLOAD_ERR_PARTIAL)
		{ echo $name." wurde nur teilweise hochgeladen.<br>\n"; continue; }
		if ($upload["error"] == UPLOAD_ERR_FORM_SIZE)
		{ echo $name." ist zu gross (form).<br>\n"; continue; }
		if ($upload["error"] == UPLOAD_ERR_INI_SIZE)
		{ echo $name." ist zu gross (php.ini).<br>\n"; continue; }
		if ($upload["error"] != UPLOAD_ERR_OK)
		{ echo "Unbekannter Fehler bei ".$name."<br>\n"; continue; }
		if (!is_uploaded_file($upload['tmp_name']))
		{ echo "Mögliche File Upload Attack bei ".$name.".<br>\n"; continue; }

		//echo "($name)($origfilename)(".$upload['tmp_name'].")<br>";
		$framepath = kFramesFolder.kFramePrefix.$animationid."_".count($frames).kFramePostfix;
		
		if (!move_uploaded_file($upload['tmp_name'],$framepath)) {
			echo "($origfilename) abspeichern fehlgeschlagen<br>";
			continue;
		}
		
		$frames[] = $framepath;
		
		/*
		<input type="hidden" name="MAX_FILE_SIZE" value="">
		*/
	}
	
	
	$outpath = kAnimOutFolder.kAnimOutPrefix.$animationid.kAnimOutPostfix;
	
	$command = "convert -delay ".intval($f_delay)." ";
	foreach ($frames as $o) $command .= " -page +0+0 ".$o;
	$command .= " -loop 0 ".$outpath;
	system($command);
	
	?>
	<img src="<?=$outpath?>">
	<?php
	
	// convert -delay 30 -size 100x100 -page +0+0 lavaflusswse1.gif -page +0+0 lavaflusswse2.gif -page +0+0 lavaflusswse3.gif -loop 0 lavaflusswse_anim.gif
	
}
?>


