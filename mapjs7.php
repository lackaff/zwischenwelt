<?php
define("kFastSession",true);
require_once("lib.main.php");
require_once("lib.map.php");
require_once("lib.army.php");
require_once("lib.unit.php");
Lock();

$gCX = isset($f_cx)?(min(200,max(0,intval($f_cx)))|1):11;
$gCY = isset($f_cy)?(min(200,max(0,intval($f_cy)))|1):11;

if (isset($f_pos) && eregi("((-|\\+)?[0-9]+)[^0-9+\\-]*((-|\\+)?[0-9]+)",$f_pos,$r)) {
	$f_x = intval($r[1]);
	$f_y = intval($r[3]);
}

// if no position passed, look at home building, or find random startplace, if none exists
if (!isset($f_x) || !isset($f_y)) { 
	$home = sqlgetobject("SELECT `x`,`y` FROM `building` WHERE `type` = 1 AND `user` = ".$gUser->id);
	if ($home) {
		$gX = $home->x;
		$gY = $home->y;
	} else {
		require_once("lib.map.php");
		list($gX,$gY) = FindRandomStartplace();
	}
} else {
	$gX = intval($f_x);
	$gY = intval($f_y);
}

// dont change x,y below here
$xmid = ($gCX-1)/2;
$ymid = ($gCY-1)/2;
$gLeft = $gX - ($gCX-1)/2;
$gTop = $gY - ($gCY-1)/2;
$gScroll = floor($gCX/2);

$xylimit = "`x` >= ".($gLeft-1)." AND `x` < ".($gLeft+$gCX+1)." AND 
			`y` >= ".($gTop-1)." AND `y` < ".($gTop+$gCY+1);
			
// produce session-independent querry, to enable caching
$styleparam = "?v=8";
if ($gUser->usegfxpath || $gUser->race != 1)
	$styleparam .= "&uid=".$gUser->id;
if ($gUser->usegfxpath) 
	$styleparam .= "&hash=".(substr(base64_encode($gUser->gfxpath),4,8));

// calc gfx path
if($gUser && $gUser->usegfxpath && !empty($gUser->gfxpath)){
	if($gUser->gfxpath{strlen($gUser->gfxpath)-1} != '/')
			$gGFXBase = $gUser->gfxpath . "/";
	else	$gGFXBase = $gUser->gfxpath;
} else		$gGFXBase = kGfxServerPath;
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/transitional.dtd">
<html><head>
<link rel="stylesheet" type="text/css" href="<?=GetZWStylePath()?>"></link>
<script src="mapjs7_core.js?v=1" type="text/javascript"></script>
<script src="<?="mapjs7_globals.js.php".$styleparam?>" type="text/javascript"></script>
<SCRIPT LANGUAGE="JavaScript" type="text/javascript"><!--
gCX = <?=$gCX?>;
gCY = <?=$gCY?>;
gLeft = <?=$gLeft?>;
gTop = <?=$gTop?>;
gSID = "<?=$gSID?>";
gThisUserID = "<?=$gUser->id?>";
gGFXBase = "<?=$gGFXBase?>";
gBig = <?=isset($f_big)?"true":"false"?>;
gMapMode = <?=isset($f_mode)?intval($f_mode):kJSMapMode_Normal?>;
<?php
$gTerrain = sqlgettable("SELECT * FROM `terrain` WHERE ".$xylimit." ORDER BY `y`,`x`");
echo 'gTerrain = "';
$i = 0; // g
for ($y=-1;$y<$gCY+1;++$y) {
	for ($x=-1;$x<$gCX+1;++$x) {
		if ($gTerrain[$i]->x - $gLeft == $x && $gTerrain[$i]->y - $gTop == $y) {
			echo $gTerrain[$i]->type.",";
			++$i;
		} else {
			// todo : lookup default-terrain here
			echo '0,'; // 0 becomes grass
		}
	}
	echo ';';
}
echo "\";\n";

$gLocalUserIDs = array();
$gLocalGuildIDs = array();

$gBuildings = sqlgettable("SELECT * FROM `building` WHERE ".$xylimit);
echo 'gBuildings = "';
foreach ($gBuildings as $o) {
	$gLocalUserIDs[] = $o->user;
	echo $o->x.",".$o->y.",".$o->type.",".$o->user.",".$o->level.",".floor($o->hp).",".($o->construction).";";
}
echo "\";\n";

$gArmies = sqlgettable("SELECT * FROM `army` WHERE ".$xylimit);
echo 'gArmies = "';
foreach ($gArmies as $o) {
	$gLocalUserIDs[] = $o->user;
	$units = cUnit::GetUnits($o->id);
	$units_js = ""; foreach ($units as $u) $units_js .= $u->type.":".floor($u->amount)."|";
	$items_js = "";// $items = cUnit::GetUnits($o->id);
	$flags_js = 0;// $flags = subset for walking, fighting, shooting...
	echo $o->x.",".$o->y.",".$o->type.",".$o->user.",".$units_js.",".$items_js.",".$flags_js.";";
}
echo "\";\n";

$gItems = sqlgettable("SELECT * FROM `item` WHERE `army` = 0 AND `building` = 0 AND ".$xylimit);
echo 'gItems = "';
foreach ($gItems as $o) {
	echo $o->x.",".$o->y.",".$o->type.",".$o->amount.";";
}
echo "\";\n";

$gPlans = sqlgettable("SELECT * FROM `construction` WHERE ".$xylimit." AND `user` = ".$gUser->id);
echo 'gPlans = "';
foreach ($gPlans as $o) {
	echo $o->x.",".$o->y.",".$o->type.",".$o->priority.";";
}
echo "\";\n";

$gLocalUserIDs = array_unique($gLocalUserIDs);
if (count($gLocalUserIDs)>0)
		$gLocalUsers = sqlgettable("SELECT `id`,`guild`,`color`,`name` FROM `user` WHERE `id` IN (".implode(",",$gLocalUserIDs).")");
else	$gLocalUsers = array();
foreach ($gLocalUsers as $o) {
	$gLocalGuildIDs[] = $o->guild;
	echo "gLocalUsers[".$o->id."] = lu(".intval($o->guild).",'".addslashes($o->color)."','".addslashes($o->name)."');\n";
}

// collect data
// $gBodenschaetze = sqlgettable("SELECT * FROM `bodenschatz` WHERE ".$xylimit);

$gLocalGuildIDs = array_unique($gLocalGuildIDs);
// local guilds (+ points...)
?>
//-->
</SCRIPT>
</head><body id="mapbody" onLoad="MapInit()">
<span id="mapzone">JavaScript needed</span>
<?php if (1) {?><div name="mapdebug"></span><?php }?>
</body></html>
