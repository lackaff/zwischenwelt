<?php
require_once("lib.main.php");
require_once("lib.minimap.php");
if (isset($f_mode) && $f_mode != "test") Lock();
profile_page_start("minimap.php");


$tiles_h = 3;
$tiles_v = 2;

$cookiebase = "minimap2_";
if (isset($f_set_th)) { 
	$_COOKIE[$cookiebase."th"] = intval($f_set_th); 
	setcookie($cookiebase."th",intval($f_set_th), time()+3600*24*7*8); 
}
if (isset($f_set_tv)) { 
	$_COOKIE[$cookiebase."tv"] = intval($f_set_tv); 
	setcookie($cookiebase."tv",intval($f_set_tv), time()+3600*24*7*8); 
}
if (isset($_COOKIE[$cookiebase."th"])) $tiles_h = max(0,min(10,intval($_COOKIE[$cookiebase."th"])));
if (isset($_COOKIE[$cookiebase."tv"])) $tiles_v = max(0,min(10,intval($_COOKIE[$cookiebase."tv"])));

define("kScreenOffsetX",66);
define("kScreenOffsetY",66);
define("kCrossHairOffset",15);
define("kSegmentSize",256);
define("kPinOffset",4);
define("kShowSegmentWidth",$tiles_h);
define("kShowSegmentWidthHalf",floor(kShowSegmentWidth/2));
define("kShowSegmentHeight",$tiles_v);
define("kShowSegmentHeightHalf",floor(kShowSegmentHeight/2));
$im = false;

if(!file_exists("tmp/minimap")){
	mkdir("tmp/minimap", 0777);
	chmod("tmp/minimap", 0777);
}

$left = $gGlobal["minimap_left"];
$right = $gGlobal["minimap_right"];
$top = $gGlobal["minimap_top"];
$bottom = $gGlobal["minimap_bottom"];
$dx = abs($right-$left);
$dy = abs($bottom-$top);

//echo "left=$left top=$top right=$right bottom=$bottom dx=$dx dy=$dy<br>";

$left = $left - ($left % kSegmentSize);
$top = $top - ($top % kSegmentSize);
$right = $right + (kSegmentSize - ($right % kSegmentSize));
$bottom = $bottom + (kSegmentSize - ($bottom % kSegmentSize));
$dx = abs($right-$left);
$dy = abs($bottom-$top);

$sx = floor($dx / kSegmentSize);
$sy = floor($dy / kSegmentSize);



if(!isset($f_cx))$cx = 0;else $cx = intval($f_cx);
if(!isset($f_cy))$cy = 0;else $cy = intval($f_cy);
if(!isset($f_mode))$mode = "normal";
else switch($f_mode){
	case "creep":$mode = "creep";break;
	case "guild":$mode = "guild";break;
	default:$mode = "normal";break;
}

$clicked = false;
foreach($_REQUEST as $n=>$v){
	$arr = explode("_",$n);
	if (count($arr) >= 4) {
		list($pre,$x,$y,$post) = $arr;
		if($pre == "seg" && $post == "x"){
			$base = $pre."_".$x."_".$y."_";
			$clickx = $_REQUEST[$base."x"]+$x*kSegmentSize;
			$clicky = $_REQUEST[$base."y"]+$y*kSegmentSize;
			$clicked = true;
			break;
		}
	}
}

if(isset($_REQUEST["crossx"]) && !isset($_REQUEST["cx"])){
	$cx = intval($_REQUEST["crossx"]);
	$cy = intval($_REQUEST["crossy"]);
}

$tmp_left_seg = floor($cx/kSegmentSize)-kShowSegmentWidthHalf;
$tmp_top_seg = floor($cy/kSegmentSize)-kShowSegmentHeightHalf;

$tmp_top = $tmp_top_seg*kSegmentSize;
$tmp_left = $tmp_left_seg*kSegmentSize;
$tmp_bottom = ($tmp_top_seg+kShowSegmentHeight)*kSegmentSize;
$tmp_right = ($tmp_left_seg+kShowSegmentWidth)*kSegmentSize;

if(isset($_REQUEST["cross_x"])){
	$clickx = intval($f_cross_x) + intval($f_crossx) - kCrossHairOffset;
	$clicky = intval($f_cross_y) + intval($f_crossy) - kCrossHairOffset;
	$clicked = true;
}

if(($gUser->flags & kUserFlags_TerraFormer) || $gUser->admin){
	for($y=0;$y<kShowSegmentHeight;++$y)
		for($x=0;$x<kShowSegmentWidth;++$x){
			$px = $tmp_left_seg+$x;
			$py = $tmp_top_seg+$y;
			$filename = "tmp/minimap/seg_".$mode."_".$px."_".$py.".png";
			unlink($filename);
		}
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
<TITLE>MiniMap</TITLE>
</head>
<body style="margin:0px;padding:0px;border:0px;">

<?php
if($clicked){
?>
	<SCRIPT LANGUAGE="JavaScript">
	<!--
		opener.parent.map.location.href = "<?=kMapScript?>?x="+<?=$clickx?>+"&y="+<?=$clicky?>+"&sid=<?=$gSID?>";
		<?php if(UserHasBuilding($gUser->id,kBuilding_HQ)) {?>
		opener.parent.info.location.href = "info/info.php?x="+<?=$clickx?>+"&y="+<?=$clicky?>+"&sid=<?=$gSID?>";
		<?php }?>
		window.close();
	//-->
	</SCRIPT>
<?php
}

ImgBorderStart("s1","jpg","#ffffee","",32,33);
?>
<table border=0 cellpadding=0 cellspacing=0>
	<tr>
		<td></td>
		<td align=center><a href="<?=query("?army=?&sid=?&crossx=?&crossy=?&mode=?&cx=".($cx)."&cy=".($cy-kSegmentSize))?>"><img src="<?=g("minimap/up.png")?>" border=0></a></td>
		<td align=right valign=top>
			<?php if(($gUser->flags & kUserFlags_TerraFormer) || $gUser->admin){ ?>
			<a alt="Segmente neugenerieren" title="Segmente neugenerieren" 
			href="<?=query("?regen=1&army=?&sid=?&crossx=?&crossy=?&mode=?&cx=?&cy=?")?>">
				<img border=0 src="<?=g("icon/reload.png")?>">
			</a>
			<?php } ?>
		</td>
	</tr>
	<tr>
		<td valign=middle><a href="<?=query("?army=?&sid=?&crossx=?&crossy=?&mode=?&cx=".($cx-kSegmentSize)."&cy=".($cy))?>"><img src="<?=g("minimap/left.png")?>" border=0></a></td>
		<td>
			<FORM METHOD=POST ACTION="<?=Query("?mode=?&army=?&sid=?&cx=?&cy=?")?>">
			<table border=0 cellpadding=0 cellspacing=0>
			<?php
			for($y=0;$y<kShowSegmentHeight;++$y){
			?>
			<tr>
			<?php
			for($x=0;$x<kShowSegmentWidth;++$x){
				$px = $tmp_left_seg+$x;
				$py = $tmp_top_seg+$y;
				$filename = "tmp/minimap/seg_".$mode."_".$px."_".$py.".png";
				if(!file_exists($filename))renderMinimap(
					$py*kSegmentSize,
					$px*kSegmentSize,
					($py+1)*kSegmentSize,
					($px+1)*kSegmentSize,
					$filename,$mode,kSegmentSize);
				?>
				<td><input name="seg_<?=$px?>_<?=$py?>" type="image" src="<?=$filename?>"></td>
				<?php
			}
			?>
			</tr>
			<?php
			}
			?>
			</table>
			<?php if(isset($f_crossx) && $f_crossx>=$tmp_left && $f_crossy>=$tmp_top && $f_crossx<$tmp_right && $f_crossy<$tmp_bottom){?>
			<input type="hidden" name="crossx" value="<?=$f_crossx?>">
			<input type="hidden" name="crossy" value="<?=$f_crossy?>">
			<input type="image" name="cross" src="<?=g("minimap/minimapcross.gif")?>" style="position:absolute;left:<?=intval($f_crossx)-$tmp_left-kCrossHairOffset+kScreenOffsetX?>px;top:<?=intval($f_crossy)-$tmp_top-kCrossHairOffset+kScreenOffsetY?>px;">
			<?php } ?>
			</form>
		</td>
		<td valign=middle><a href="<?=query("?army=?&sid=?&crossx=?&crossy=?&mode=?&cx=".($cx+kSegmentSize)."&cy=".($cy))?>"><img src="<?=g("minimap/right.png")?>" border=0></a></td>
	</tr>
	<tr>
		<td></td>
		<td align=center><a href="<?=query("?army=?&sid=?&crossx=?&crossy=?&mode=?&cx=".($cx)."&cy=".($cy+kSegmentSize))?>"><img src="<?=g("minimap/down.png")?>" border=0></a></td>
		<td></td>
	</tr>
</table>
<?php
ImgBorderEnd("s1","jpg","#ffffee",32,33);
?>
Gr&ouml;sse des Kartenausschnitts :
<?php 
function TileLink ($x,$y) {
	$cur = $x == kShowSegmentWidth && $y == kShowSegmentHeight;
	if ($cur) echo "[";
	?><a href="<?=Query("?sid=?&mode=?&cx=?&cy=?&crossx=?&crossy=?&set_th=".$x."&set_tv=".$y."")?>"><?=$x?>x<?=$y?></a><?php
	if ($cur) echo "]";
	echo "&nbsp;";
}
TileLink(1,1);
TileLink(2,1);
TileLink(2,2);
TileLink(3,2);
TileLink(3,3);
TileLink(4,3);
TileLink(4,4);
TileLink(5,5);
?>

</BODY>
</HTML>
<?php profile_page_end();?>
