<?php
require_once("lib.main.php");
require_once("lib.minimap.php");
define("kMaxFileSize",1024*1024*2);

Lock();
if (!($gUser && ($gUser->admin || intval($gUser->flags) & kUserFlags_TerraFormer))) exit("access denied");

if (isset($f_export)) {
	$von = explode(",",trim($f_von));
	$bis = explode(",",trim($f_bis));
	$left = intval($von[0]);
	$top  = intval($von[1]);
	$right = intval($bis[0]);
	$bottom = intval($bis[1]);
	$filename = "tmp/lg_".time()."_x".$left."_y".$top."_x".$right."_y".$bottom.".png";
	
	renderMinimap($top,$left,$bottom,$right,$filename,"terraformexport",$segment=128,$fullmap=false)
	
	?>
	<img border=0 src="<?=$filename?>" alt="" title="">
	TIPP : am besten beim bearbeiten markante Farben wie rot,lila,... verwenden, <br>
	die nicht in der normalen mimimap vorkommen, <br>
	Beim RE-Import kann man einfach dann alle "normalen" farben ignorieren, <br>
	so minimiert man das Risiko, bestehendes Terrain unabsichtlich zu verändern, <br>
	vor allem, weil manchmal mehrere terrain-typen die gleiche Farben haben.
	<?php
}

if (isset($f_openimporter)) {
	// $f_bildup
	$picok = false;
	$pictime = time();
	$picpath = "tmp/lgup_".$pictime."_".intval($gUser->id).".png";
	$pic_orig_filename = false;
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
		
		if (!move_uploaded_file($upload['tmp_name'],$picpath)) {
			echo "($origfilename) abspeichern fehlgeschlagen<br>";
			continue;
		}
		$picok = true;
		$pic_orig_filename = $origfilename;
	}
	
	if (!$picok) {
		echo "Upload fehlgeschlagen<br>";
	} else {
		?>
		Upload erfolgreich, starte analyse....<br>
		Orginal-Dateiname = <?=$pic_orig_filename?><br>
		<img border=0 src="<?=$picpath?>" alt="" title=""><br>
		<?php
		$img = imagecreatefrompng($picpath);
		imagetruecolortopalette($img,false,255);
		$totalcolors = imagecolorstotal($img);
		list($width, $height, $type, $attr) = getimagesize($picpath);
		echo "width=$width, height=$height, type=$type, attr=($attr)<br>";
		echo "totalcolors=$totalcolors<br>";
		?>
		Terrain setzten :
		<form method="post" action="<?=Query("?sid=?&x=?&y=?")?>">
		<input type="hidden" name="pictime" value="<?=$pictime?>">
		<table>
		<?php for ($i=0;$i<$totalcolors;++$i) {
			$col = imagecolorsforindex($img,$i);
			?>
			<tr>
				<td bgcolor="#<?=sprintf("%02x%02x%02x",max(0,min(255,$col["red"])),max(0,min(255,$col["green"])),max(0,min(255,$col["blue"])));?>">&nbsp;&nbsp;&nbsp;</td>
				<td>alpha=<?=$col["alpha"]?></td>
				<td>
					<select name="setterrain[<?=$i?>]">
						<option value="0" selected>ignorieren</option>
						<?php foreach ($gTerrainType as $o) {?>
						<option value="<?=$o->id?>"><?=$o->name?></option>
						<?php } // endforeach?>
					</select>
				</td>
			</tr>
			<?php
		}
		?>
		</table>
			ZW-Koordinaten für die Linke Obere Ecke= <input type="text" name="von" value="<?=intval($f_x).",".intval($f_y)?>" style="width:80px">,
			Grösse = <?=$width.",".$height?>
			<input type="submit" name="import" value="Importieren">
		</form>
		
		<?php
		imagedestroy($img);
		//int 
		// int imagecolorat ( resource image, int x, int y )
	}
} else if (isset($f_import)) {
	$picpath = "tmp/lgup_".intval($f_pictime)."_".intval($gUser->id).".png";
	if (!file_exists($picpath)) exit("datei existiert nicht");
	$img = imagecreatefrompng($picpath);
	imagetruecolortopalette($img,false,255);
	$totalcolors = imagecolorstotal($img);
	list($width, $height, $type, $attr) = getimagesize($picpath);
	
	$von = explode(",",trim($f_von));
	$left = intval($von[0]);
	$top  = intval($von[1]);
	
	
	if ($width <= 0) exit("breite passt nicht");
	if ($height <= 0) exit("breite passt nicht");
	if ($totalcolors != count($f_setterrain)) exit("farb-anzahl passt nicht");
	
	echo "importiere bild mit $width x $height pixeln und $totalcolors Farben nach ($left,$top)...<br>";
	
	vardump2($f_setterrain);
	$block_counter = 0;
	$countarr = array();
	
	$myterrain = false;
	$myterrain->creator = $gUser->id;
	
	$lastinsertedx = 0;
	$lastinsertedy = 0;
		
	for ($iy=0;$iy<$height;++$iy) 
	for ($ix=0;$ix<$width;++$ix) {
		//echo "$ix,$iy : ".imagecolorat($img,$ix,$iy)."<br>";
		$myterrain->type = intval($f_setterrain[imagecolorat($img,$ix,$iy)]);
		if ($myterrain->type == 0 || !isset($gTerrainType[$myterrain->type])) continue;
		
		$myterrain->x = $left + $ix;
		$myterrain->y = $top + $iy;
		
		// check terraform-safety
		$terraform_blocked = false;
		if (!$gUser->admin) {
			if (sqlgetone("SELECT 1 FROM `building` WHERE 
				`x`>=(".($myterrain->x-kTerraFormer_SicherheitsAbstand).") AND 
				`x`<=(".($myterrain->x+kTerraFormer_SicherheitsAbstand).") AND
				`y`>=(".($myterrain->y-kTerraFormer_SicherheitsAbstand).") AND
				`y`<=(".($myterrain->y+kTerraFormer_SicherheitsAbstand).") AND `user`>0 LIMIT 1"))
				$terraform_blocked = true;
			else if (sqlgetone("SELECT 1 FROM `army` WHERE 
				`x`>=(".($myterrain->x-kTerraFormer_SicherheitsAbstand).") AND 
				`x`<=(".($myterrain->x+kTerraFormer_SicherheitsAbstand).") AND
				`y`>=(".($myterrain->y-kTerraFormer_SicherheitsAbstand).") AND
				`y`<=(".($myterrain->y+kTerraFormer_SicherheitsAbstand).") AND `user`>0 LIMIT 1"))
				$terraform_blocked = true;
		}
		if ($terraform_blocked) { ++$block_counter; continue; }
			
			
		$lastinsertedx = $myterrain->x;
		$lastinsertedy = $myterrain->y;
	
		sql("REPLACE INTO `terrain` SET ".obj2sql($myterrain));
		if (!isset($countarr[$myterrain->type])) $countarr[$myterrain->type] = 0;
		++$countarr[$myterrain->type];
	}
	
	echo "Neu gesetztes Terrain: z.b. ($lastinsertedx,$lastinsertedy)<br>";
	foreach ($countarr as $terrtypeid => $count) echo $gTerrainType[$terrtypeid]->name.":".$count."<br>";
	
	imagedestroy($img);
} else {
	?>
	<form method="post" enctype="multipart/form-data" action="<?=Query("?sid=?&x=?&y=?")?>">
		Landschafts-Import-Dialog für Bild <input name="bildup" type="file"> (nur PNG)
		<input type="submit" name="openimporter" value="oeffnen">
	</form>
	<?php
}



	
	
?>