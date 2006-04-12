<?php
require_once("lib.main.php");
require_once("lib.minimap.php");
define("kMaxFileSize",1024*1024*2);

Lock();
if (!($gUser && ($gUser->admin || intval($gUser->flags) & kUserFlags_TerraFormer))) exit("access denied");



if (isset($f_export)) {
	$mid = explode(",",trim($f_mid));
	$size = explode(",",trim($f_size));
	$f_x = intval($mid[0]);
	$f_y = intval($mid[1]);
	$width = intval($size[0]);
	$height = intval($size[1]);
	$left = intval($mid[0])-floor($width/2);
	$top  = intval($mid[1])-floor($height/2);
	$right = $left+$width;
	$bottom = $top+$height;
	
	$filename = "tmp/lg_".time()."_mx".intval($mid[0])."_my".intval($mid[1])."_w".$width."_h".$height.".png";
	renderMinimap($top,$left,$bottom,$right,$filename,"terraformexport");
	
	?>
	<img border=0 src="<?=$filename?>" alt="" title=""><br>
	Mitte(x,y) : (<?=intval($mid[0])?>,<?=intval($mid[1])?>)<br>
	Grösse(x,y) : (<?=$width?>,<?=$height?>)<br>
	<hr>
	TIPP : am besten beim Bearbeiten markante Farben verwenden, wie z.b. hellgrün,rot,orange,knallrosa,...<br>
	die nicht in der normalen Mimimap vorkommen.<br>
	Beim Re-Import kann man einfach dann alle "normalen" Farben ignorieren, <br>
	so minimiert man das Risiko, bestehendes Terrain unabsichtlich zu verändern, <br>
	vor allem, weil manchmal mehrere terrain-typen die gleiche Farben haben.<br>
	<hr>
	WICHTIG : beim bearbeiten keine Pinsel mit "weichem Rand" benutzten, die Farbverläufe am Rand erzeugen,<br>
	Es ist wichtig, dass das Bild möglichst wenige, klar voneinander unterscheidbare Farben hat,<br>
	denn beim Reimport kann man in einer Farb-Liste FÜR JEDE FARBE einen Terrain-Typ wählen (oder sie ignorieren).<br>
	Wenn man einen weichen Pinsel verwendet, oder irgendwie anders Farbverläufe ins Bild bringt,<br>
	hat man schnell eine Farbliste mit mehreren hundert Einträgen, da wird das zuweisen sehr mühselig ;)<br>
	<hr>
	<?php
}

if (isset($f_openimporter)) {
	// $f_bildup
	$picok = false;
	$pictime = time();
	$path_upload = "tmp/lgup_".$pictime."_".intval($gUser->id).".png";
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
		
		if (!move_uploaded_file($upload['tmp_name'],$path_upload)) {
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
		<img border=0 src="<?=$path_upload?>" alt="" title=""><br>
		<?php
		$img = imagecreatefrompng($path_upload);
		imagetruecolortopalette($img,false,255);
		$totalcolors = imagecolorstotal($img);
		list($width, $height, $type, $attr) = getimagesize($path_upload);
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
			ZW-Koordinaten für die Mitte (x,y) : <input type="text" name="mid" value="<?=intval($f_x).",".intval($f_y)?>" style="width:80px">,
			Grösse = <?=$width.",".$height?>
			<input type="submit" name="import_preview" value="weiter zur Vorschau">
		</form>
		
		<?php
		imagedestroy($img);
		//int 
		// int imagecolorat ( resource image, int x, int y )
	}
	
	
	
} else if (isset($f_import_preview)) {
	$time = intval($f_pictime);
	$path_upload = "tmp/lgup_".$time."_".intval($gUser->id).".png";
	if (!file_exists($path_upload)) exit("datei existiert nicht");
	$img_dif = imagecreatefrompng($path_upload);
	imagetruecolortopalette($img_dif,false,255);
	$totalcolors = imagecolorstotal($img_dif);
	list($width, $height, $type, $attr) = getimagesize($path_upload);
	
	$mid = explode(",",trim($f_mid));
	$left = intval($mid[0])-floor($width/2);
	$top  = intval($mid[1])-floor($height/2);
	
	if ($width <= 0) exit("breite passt nicht");
	if ($height <= 0) exit("breite passt nicht");
	if ($totalcolors != count($f_setterrain)) exit("farb-anzahl passt nicht");
	
	$right = $left + $width;
	$bottom = $top + $height;
	
	echo "Vorschau für Import von Bild mit $width x $height Pixeln und $totalcolors Farben nach (".intval($mid[0]).",".intval($mid[1]).")(Mitte)...<br>";
	echo "(der Landschaftsgestalter-Sicherheitsabstand wird hier noch nicht berücksichtig,<br> beim endgültigen Import aber schon)<br>";
	
	$path_old = "tmp/lgold_".$time."_x".$left."_y".$top."_x".$right."_y".$bottom.".png";
	$path_new = "tmp/lgnew_".$time."_x".$left."_y".$top."_x".$right."_y".$bottom.".png";
	$path_dif = "tmp/lgdif_".$time."_x".$left."_y".$top."_x".$right."_y".$bottom.".png";
	
	
	renderMinimap($top,$left,$bottom,$right,$path_old,"terraformexport");
	$img_new = imagecreatefrompng($path_old);
	
	$img_dif_changed	= hex2imgcolor($img_dif,"#000000");
	$img_dif_unchanged	= hex2imgcolor($img_dif,"#DDDDDD");
	$img_new_colors = array();
	foreach ($f_setterrain as $colorindex => $terrtypeid) if ($terrtypeid > 0) {
		$img_new_colors[$colorindex] = hex2imgcolor($img_new,$gTerrainType[$terrtypeid]->color);
	}
	//	= hex2imgcolor($img_dif,"#ffffff");
	
	$countarr = array();
	$myterrain = false;
	for ($iy=0;$iy<$height;++$iy) 
	for ($ix=0;$ix<$width;++$ix) {
		$colorindex = imagecolorat($img_dif,$ix,$iy);
		$myterrain->type = intval($f_setterrain[$colorindex]);
		if ($myterrain->type == 0 || !isset($gTerrainType[$myterrain->type])) {
			imagesetpixel($img_dif,$ix,$iy,$img_dif_unchanged);
		} else {
			imagesetpixel($img_dif,$ix,$iy,$img_dif_changed);
			imagesetpixel($img_new,$ix,$iy,$img_new_colors[$colorindex]);
			
			if (!isset($countarr[$myterrain->type])) $countarr[$myterrain->type] = 0;
			++$countarr[$myterrain->type];
		}
	}
	
	foreach ($countarr as $terrtypeid => $count) echo "Neu : ".$count." Felder ".$gTerrainType[$terrtypeid]->name."<br>";
	
	imagepng($img_dif,$path_dif);
	imagepng($img_new,$path_new);
	
	?>
	<table>
	<tr>
		<th>Upload</th>
		<th>Änderungen</th>
		<th>Vorher</th>
		<th>Nachher</th>
	</tr><tr>
		<td><img border=0 src="<?=$path_upload?>" alt="" title=""></td>
		<td><img border=0 src="<?=$path_dif?>" alt="" title=""></td>
		<td><img border=0 src="<?=$path_old?>" alt="" title=""></td>
		<td><img border=0 src="<?=$path_new?>" alt="" title=""></td>
	</tr>
	</table>
	
	<form method="post" action="<?=Query("?sid=?&x=?&y=?")?>">
		<input type="hidden" name="pictime" value="<?=$time?>">
		<?php foreach ($f_setterrain as $colorindex => $terrtypeid) {?>
			<input type="hidden" name="setterrain[<?=$colorindex?>]" value="<?=$terrtypeid?>">
		<?php } // endforeach?>
		<input type="hidden" name="mid" value="<?=$f_mid?>" style="width:80px">
		<input type="submit" name="import" value="Import Durchführen">
	</form>
	<?php
	
} else if (isset($f_import)) {
	$time = intval($f_pictime);
	$path_upload = "tmp/lgup_".$time."_".intval($gUser->id).".png";
	if (!file_exists($path_upload)) exit("datei existiert nicht");
	$img = imagecreatefrompng($path_upload);
	imagetruecolortopalette($img,false,255);
	$totalcolors = imagecolorstotal($img);
	list($width, $height, $type, $attr) = getimagesize($path_upload);
	
	$mid = explode(",",trim($f_mid));
	$left = intval($mid[0])-floor($width/2);
	$top  = intval($mid[1])-floor($height/2);
	
	if ($width <= 0) exit("breite passt nicht");
	if ($height <= 0) exit("breite passt nicht");
	if ($totalcolors != count($f_setterrain)) exit("farb-anzahl passt nicht");
	
	$right = $left + $width;
	$bottom = $top + $height;
	
	echo "Import von Bild mit $width x $height Pixeln und $totalcolors Farben nach (".intval($mid[0]).",".intval($mid[1]).")(Mitte)...<br>";
	
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
	echo "Import erfolgreich beendet =)<br>";
	imagedestroy($img);
	
	// $path_upload
	$path_old = "tmp/lgold_".$time."_x".$left."_y".$top."_x".$right."_y".$bottom.".png";
	$path_new = "tmp/lgnew_".$time."_x".$left."_y".$top."_x".$right."_y".$bottom.".png";
	$path_dif = "tmp/lgdif_".$time."_x".$left."_y".$top."_x".$right."_y".$bottom.".png";
	$path_result = "tmp/lgresult_".$time."_x".$left."_y".$top."_x".$right."_y".$bottom.".png";
	renderMinimap($top,$left,$bottom,$right,$path_result,"terraformexport");
	
	?>
	<table>
	<tr>
		<th>Upload</th>
		<th>Änderungen</th>
		<th>Vorher</th>
		<th>Nachher-Soll</th>
		<th>Ergebnis</th>
	</tr><tr>
		<td><img border=0 src="<?=$path_upload?>" alt="" title=""></td>
		<td><img border=0 src="<?=$path_dif?>" alt="" title=""></td>
		<td><img border=0 src="<?=$path_old?>" alt="" title=""></td>
		<td><img border=0 src="<?=$path_new?>" alt="" title=""></td>
		<td><img border=0 src="<?=$path_result?>" alt="" title=""></td>
	</tr>
	</table>
	(nur das "Ergebnis" berücksichtigt den Terraformer-Sicherheitsabstand)
	<?php
} else {
	?>
	<form method="post" enctype="multipart/form-data" action="<?=Query("?sid=?&x=?&y=?")?>">
		Landschafts-Import-Dialog für Bild <input name="bildup" type="file"> (nur PNG)
		<input type="submit" name="openimporter" value="oeffnen">
	</form>
	<?php
}



	
	
?>