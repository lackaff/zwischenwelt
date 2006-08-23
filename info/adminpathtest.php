<?php

require_once("../lib.main.php");
require_once("../lib.building.php");
require_once("../lib.army.php");
require_once("../lib.map.php");
require_once("../lib.tableedit.php");
require_once("../lib.transfer.php");

AdminLock();

// GetNextStep

if (!isset($f_xc)) {
	$f_xc = 0;$f_yc = 0;
	$f_x1 = 1;$f_y1 = 5;
	$f_x2 = 8;$f_y2 = 3;
}
$gHin = array();
$gRueck = array();
for ($x=$f_x1,$y=$f_y1;$x!=$f_x2 || $y!=$f_y2;list($x,$y)=GetNextStep($x,$y,$f_x1,$f_y1,$f_x2,$f_y2,true)) $gHin[] = "$x,$y";
echo "<hr>";
for ($x=$f_x2,$y=$f_y2;$x!=$f_x1 || $y!=$f_y1;list($x,$y)=GetNextStep($x,$y,$f_x2,$f_y2,$f_x1,$f_y1,true)) $gRueck[] = "$x,$y";

function cellbg ($x,$y) {
	global $f_xc,$f_yc,$f_x1,$f_y1,$f_x2,$f_y2;
	if ($x == $f_x1 && $y == $f_y1) return "";
	if ($x == $f_x2 && $y == $f_y2) return "";
	$var = "$x,$y";
	global $gHin,$gRueck;
	$hin = in_array($var,$gHin);
	$rueck = in_array($var,$gRueck);
	if ($hin && $rueck) return " bgcolor='green'";
	else if ($hin) 		return " bgcolor='#ff0000'";
	else if ($rueck) 	return " bgcolor='#ffff00'";
	return "";
}

function cell ($x,$y) {
	return "<a href='".Query("?sid=?&xc=?&yc=?&x1=?&y1=?&x2=".$x."&y2=".$y."")."'>".cell_sign($x,$y)."</a>";
}
function cell_sign ($x,$y) {
	global $f_xc,$f_yc,$f_x1,$f_y1,$f_x2,$f_y2;
	if ($x == $f_xc && $y == $f_yc) return "#";
	if ($x == $f_x1 && $y == $f_y1) return "1";
	if ($x == $f_x2 && $y == $f_y2) return "2";
	return "-";
}

$size = 10;
?>
<table border=1 cellspacing=0>
<tr><th></th><?php for ($x=0;$x<$size;++$x) {?><th><?=$x?></th><?php } ?></tr>
<?php for ($y=0;$y<$size;++$y) {?>
	<tr>
	<th><?=$y?></th><?php for ($x=0;$x<$size;++$x) {?><td <?=cellbg($x,$y)?>><?=cell($x,$y)?></td><?php } ?>
	</tr>
<?php } ?>
</table>
<?php

if (isset($f_xc)) {
	$pos = GetNextStep($f_xc,$f_yc,$f_x1,$f_y1,$f_x2,$f_y2);
	if ($pos) {
		echo "GetNextStep($f_xc,$f_yc) = (".$pos[0].",".$pos[1].")<br>";
		list($f_xc,$f_yc) = $pos;
	} else echo "GetNextStep($f_xc,$f_yc) = false<br>";
}
?>
<form method="post" action="<?=Query("?sid=?&xc=?&yc=?&x1=?&y1=?&x2=?&y2=?")?>">
	<input type="text" name="xc" value="<?=$f_xc?>"><input type="text" name="yc" value="<?=$f_yc?>">c<br>
	<input type="text" name="x1" value="<?=$f_x1?>"><input type="text" name="y1" value="<?=$f_y1?>">1<br>
	<input type="text" name="x2" value="<?=$f_x2?>"><input type="text" name="y2" value="<?=$f_y2?>">2<br>
	<input type="submit" name="save" value="weiter">
</form>

das hier ist dazu da um die GetNextStep() funktion zu testen.<br>
c ist die derzeite position,<br>
1 ist der startplatz,<br>
2 ist das ziel.<br>