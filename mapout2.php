<?php
require("lib.php");
$fp = fopen("map.dat","wb");

/*
function nwse2num ($nwse) {
	$len = strlen($nwse);
	$sum = 0;
	for ($i=0;$i<$len;++$i) switch ($nwse{$i}) {
		case 'n' : $sum += 1;break;
		case 'w' : $sum += 2;break;
		case 's' : $sum += 4;break;
		case 'e' : $sum += 8;break;
	}
	return $sum;
}
*/

function byte1 ($num) { return sprintf("%c",intval($num) + 128); }
function byte2 ($num) { 
	$num = intval($num) + 256*128; 
	return sprintf("%c%c",($num >> 0) & 0x00ff,($num >> 8) & 0x00ff); 
}
function byte4 ($num) { 
	$num = intval($num) + 256*256*256*128; 
	return sprintf("%c%c%c%c",($num >> 0) & 0x00ff,($num >> 8) & 0x00ff,($num >> 16) & 0x00ff,($num >> 24) & 0x00ff); 
}

// general : 8 chars type, 8 chars len, then data

// version info , type=0,len=8,version=1


if (0) {
	fwrite($fp,byte2(0).byte2(-1).byte2(-999)); 
} else if (0) {
	fwrite($fp,byte1(0).byte1(1).byte1(2).byte1(3).byte1(-1).byte1(-2).byte1(-3).byte1(-4)); 
	fwrite($fp,byte1(0).byte1(0).byte1(0).byte1(0).byte1(0).byte1(0).byte1(0).byte1(0)); 
	fwrite($fp,byte2(0).byte2(1).byte2(2).byte2(3).byte2(-1).byte2(-2).byte2(-3).byte2(-4)); 
} else {

	fwrite($fp,byte2(0).byte4(2).byte2(222)); 
	
	// TODO : FIXME : terrainsegmente..
	
	// terrain
	fwrite($fp,byte2(1).byte4(6*sqlgetone("SELECT COUNT(*) FROM `terrain`")));
	$r = sql("SELECT `type`,`nwse`,`x`,`y` FROM `terrain`");
	while ($x = mysql_fetch_row($r)) fwrite($fp,byte1($x[0]).byte1($x[1]).byte2($x[2]).byte2($x[3]));
	
	// buildings
	fwrite($fp,byte2(2).byte4(9*sqlgetone("SELECT COUNT(*) FROM `building`")));
	$r = sql("SELECT b.`type`,b.`nwse`,b.`level`,b.`user`,b.`x`,b.`y` FROM `building` b,`buildingtype` t
		WHERE b.`type`=t.`id` AND t.`race`<=1");
	while ($x = mysql_fetch_row($r)) fwrite($fp,byte1($x[0]).byte1($x[1]).byte1($x[2]).byte2($x[3]).byte2($x[4]).byte2($x[5]));
}

fclose($fp);
echo "done".time();
?>
