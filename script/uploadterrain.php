<?php

require_once("../lib.main.php");
require_once("../lib.map.php");

// red or #ff0000 to Array(255,0,0)
function hexcolor2rgb($hex)
{
	switch($hex)
	{
		case "red":return Array(255,0,0);
		case "green":return Array(0,255,0);
		case "blue":return Array(0,0,255);
		case "black":return Array(0,0,0);
		case "white":return Array(255,255,255);
		case "yellow":return Array(255,255,0);
		case "gray":return Array(204,204,204);
		default:return Array(hexdec(substr($hex,1,2)),hexdec(substr($hex,3,2)),hexdec(substr($hex,5,2)));
	}
}

//calculates the distance between two colors (r,g,b),(r,g,b)
function colorDist($color1,$color2){
	$d1 = $color1[0]-$color2[0];
	$d2 = $color1[1]-$color2[1];
	$d3 = $color1[2]-$color2[2];
	return sqrt($d1*$d1+$d2*$d2+$d3*$d3);
}

//search in the colors array (id=>(r,g,b),id=>(r,g,b),...) for
// the best color (r,g,b) match and returns the id
function bestMatchColor($colors,$color,$delta=5){
	assert(sizeof($colors)>0);
	$minfound = false;
	$mindist = 0;
	$minid = 0;
	
	foreach($colors as $id=>$x){
		if($minfound == false || colorDist($x,$color)<$mindist){
			$minfound = true;
			$mindist = colorDist($x,$color);
			$minid = $id;
		}
		
		if($mindist < $delta)return $minid;
	}
	
	return $minid;
}



/*
$colors = array();
foreach($gTerrainType as $x){
	$colors[$x->id] = hexcolor2rgb($x->color);
}
*/

//hier ist die liste der terrain id farb zuweisungen,
//er versucht immer die passendste zu finden
//$colors[TERRAINID] = array(RED,GREEN,BLUE);
//rgb sind jeweils von 0-255
$colors = array();
$colors[1] = array(100,170,80);
$colors[6] = array(0,0,255);
$colors[3] = array(72,72,72);
$colors[7] = array(255,255,0);
$colors[4] = array(50,150,50);
$colorsalloc = array();

$basex = intval($f_x);
$basey = intval($f_y);

if(isset($f_submit)){
	if (is_uploaded_file($_FILES['userfile']['tmp_name'])) {
		eregi('^.+\.([a-zA-Z]+)$',$_FILES['userfile']['name'],$r);
		$ext = strtolower($r[1]);
		switch($ext){
			case "jpg":
			case "jpeg":
				$type = "image/jpeg";
				$im = imagecreatefromjpeg($_FILES['userfile']['tmp_name']);
			break;
			case "gif":
				$type = "image/gif";
				$im = imagecreatefromgif($_FILES['userfile']['tmp_name']);
			break;
			default:
				$type = "image/png";
				$im = imagecreatefrompng($_FILES['userfile']['tmp_name']);
			break;
		}
				
		$dx = imagesx($im);
		$dy = imagesy($im);
		for($x=0;$x<$dx;++$x)
			for($y=0;$y<$dy;++$y){
				$rgb = ImageColorAt($im, $x, $y);
				$r = ($rgb >> 16) & 0xFF;
				$g = ($rgb >> 8) & 0xFF;
				$b = $rgb & 0xFF;
				$id = bestMatchColor($colors,array($r,$g,$b));
				list($r2,$g2,$b2) = $colors[$id];
				//echo "[$r,$g,$b - $id - $r2,$g2,$b2]<br>\n";
				if(isset($f_write))setTerrain($basex+$x,$basey+$y,$id);
				$c = $colors[$id];
				
				if(!isset($colorsalloc[$id]))$colorsalloc[$id] = imagecolorallocate($im, $c[0], $c[1], $c[2]);
				$c = $colorsalloc[$id];
				
				imagesetpixel($im,$x,$y,$c);
				unset($c);
			}
		//exit;
		header("Content-type: $type");
		imagepng($im);
		imagedestroy($im);
		exit;
	} else {
		echo "Possible file upload attack: ";
		echo "filename '". $_FILES['userfile']['tmp_name'] . "'.";
	}
}

?>
<!-- The data encoding type, enctype, MUST be specified as below -->
<form enctype="multipart/form-data" action="?" method="POST">
	<!-- MAX_FILE_SIZE must precede the file input field -->
	<input type="hidden" name="MAX_FILE_SIZE" value="1000000" />
	<!-- Name of input element determines name in $_FILES array -->
	TerrainImage: <input name="userfile" type="file" /><br>
	X: <input type="text" name="x" value="0">
	Y: <input type="text" name="y" value="0"><br>
	<input type=checkbox name=write value=1> reinschreiben?
	<input type="submit" name="submit" value="Send File" />
</form>
