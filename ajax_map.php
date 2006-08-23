<?php

require_once("lib.main.php");
require_once("lib.map.php");

$x = (int)$f_x;
$y = (int)$f_y;
$w = (int)$f_w;
$h = (int)$f_h;

$x = $x*$w;
$y = $y*$h;

$name = "tmp/ajaxmap/seg_".$x."_".$y.".html";
if(file_exists($name)){
	readfile($name);
	exit;
}

/*
header("Content-type: image/png");
$width = 27*$w;
$height = 27*$h;
$im = imagecreatetruecolor($width,$height);
$bg = imagecolorallocate($im, 0, 0, 0);
$font = imagecolorallocate($im, 255, 255, 255);
imagefilledrectangle($im,0,0,$width-1,$height-1,$bg);
imagestring($im, 3, $width/2, $height/2, "$x,$y", $font);
imagepng($im);
imagedestroy($im);

exit;
*/

ob_start();

?>
<table class="segment" border="0" cellpadding="0" cellspacing="0" align="center">
<?php

$map = getMapAtPosition($x,$y,$w,$h,true);

for($iy = 0;$iy < $h;++$iy){
	echo "<tr>";
	for($ix = 0;$ix < $w;++$ix){
		$px = $x+$ix;
		$py = $y+$iy;
		$terraintype = $map->getTerrainTypeAt($px,$py);
		$terrainnwse = $map->getTerrainNwseAt($px,$py);
		$type = $gTerrainType[$terraintype];
		$gfx = g($type->gfx,$terrainnwse,0,0,0,0);
		echo "<td><div><img src=\"".$gfx."\"></div></td>";
	}
	echo "</tr>";
}

?>
</table>
<?php

$buffer = ob_get_contents();
ob_end_flush();

if(!file_exists($name)){
	$f = fopen($name,"w");
	fwrite($f,$buffer);
	fclose($f);
}

?>
