<?php
define("kFastSession",true);
require_once("lib.main.php");
require_once("lib.map.php");
require_once("lib.army.php");
require_once("lib.unit.php");
Lock();

$gCX = isset($f_cx)?(min(200,max(0,intval($f_cx)))|1):11;
$gCY = isset($f_cy)?(min(200,max(0,intval($f_cy)))|1):11;

// show army pos
if (isset($f_gotocat)) {
	//echo "gotocat = $f_gotocat , gotocat2 = $f_gotocat2 , gotocat3 = $f_gotocat3 <br>";
	$foundobject = false;
	if ($f_gotocat != kMapNaviGotoCat_Pos) unset($f_pos);
	switch ($f_gotocat) {
		case kMapNaviGotoCat_Pos: break; // handled by $f_pos
		case kMapNaviGotoCat_Mark: 
			$foundobject = sqlgetobject("SELECT `x`,`y` FROM `mapmark` WHERE `user` = ".$gUser->id." AND `id` = ".intval($f_gotocat2));
		break;
		case kMapNaviGotoCat_Own:
			$foundobject = sqlgetobject("SELECT * FROM `army` WHERE `id` = ".intval($f_gotocat3));
			$f_army = $foundobject->id;
		break;
		case kMapNaviGotoCat_Guild:
			if ($f_gotocat2 > 0) { // if cat2>0 then armytype else userid
				$foundobject = sqlgetobject("SELECT * FROM `army` WHERE `id` = ".intval($f_gotocat3));
				$f_army = $foundobject->id;
			} else $foundobject = sqlgetobject("SELECT `x`,`y` FROM `building` WHERE `user` = ".intval($f_gotocat3)." AND `type` = ".kBuilding_HQ);
		break;
		case kMapNaviGotoCat_Friends:
		case kMapNaviGotoCat_Enemies:
			$foundobject = sqlgetobject("SELECT `x`,`y` FROM `building` WHERE `user` = ".intval($f_gotocat2)." AND `type` = ".kBuilding_HQ);
		break;
		case kMapNaviGotoCat_Search:
			// todo : on change of searchtext-field in navi : reset search-number
			// todo : list usernames, userguild, armynames, bodenschatz-type, hellholes-monstertype..
			$foundobject = sqlgetobject("SELECT `id` FROM `user` WHERE `name` LIKE '%".addslashes($f_search)."%' ORDER BY RAND() LIMIT 1");
			if ($foundobject) $foundobject = sqlgetobject("SELECT `x`,`y` FROM `building` WHERE `user` = ".$foundobject->id." AND `type` = ".kBuilding_HQ);
		break;
		case kMapNaviGotoCat_Random: // i thought this might be funny =)
			switch ($f_gotocat2) {
				case 0: // gebaeude
					$foundobject = sqlgetobject("SELECT `x`,`y` FROM `building` ORDER BY RAND() LIMIT 1");
				break;
				case 1: // landschaft
					$foundobject = sqlgetobject("SELECT `x`,`y` FROM `terrain` ORDER BY RAND() LIMIT 1");
				break;
				case 2: // pos
					$f_x = rand(intval($gGlobal["minimap_left"]),intval($gGlobal["minimap_right"]));
					$f_y = rand(intval($gGlobal["minimap_top"]),intval($gGlobal["minimap_bottom"]));
				break;
			}
		break;
		case kMapNaviGotoCat_Hellhole: // admin feature for tracking movable/nonstandard hellholes
			$foundobject = sqlgetobject("SELECT `x`,`y` FROM `hellhole` WHERE `id` = ".intval($f_gotocat2));
		break;
	}
	if ($foundobject) {
		$f_x = $foundobject->x;
		$f_y = $foundobject->y;
	}
}
	
if (isset($f_pos) && $f_pos != "" && eregi("((-|\\+)?[0-9]+)[^0-9+\\-]*((-|\\+)?[0-9]+)",$f_pos,$r)) {
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
$gScroll = isset($f_scroll)?$f_scroll:floor($gCX/2);

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
gCX = <?=intval($gCX)?>;
gCY = <?=intval($gCY)?>;
gLeft = <?=$gLeft?>;
gTop = <?=$gTop?>;
gScroll = <?=$gScroll?>;
gSID = "<?=$gSID?>";
gActiveArmyID = <?=isset($f_army)?intval($f_army):0?>;
gThisUserID = <?=intval($gUser->id)?>;
gGFXBase = "<?=$gGFXBase?>";
gBig = <?=(isset($f_big) && $f_big)?1:0?>;
gMapMode = <?=isset($f_mode)?intval($f_mode):kJSMapMode_Normal?>;
<?php
// terrain
$gTerrain = sqlgettable("SELECT * FROM `terrain` WHERE ".$xylimit." ORDER BY `y`,`x`");
echo 'gTerrain = "';
$i = 0;
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

// buildings
$gBuildings = sqlgettable("SELECT * FROM `building` WHERE ".$xylimit);
echo 'gBuildings = "';
foreach ($gBuildings as $o) {
	$gLocalUserIDs[] = $o->user;
	$o->jsflags = 0;
	$o->hp = floor($o->hp);
	
	if ($o->type == kBuilding_Portal) {
		if (intval(GetBParam($o->id,"target"))>0) $o->jsflags |= kJSMapBuildingFlag_Open;
	} else {
		if (cBuilding::BuildingOpenForUser($o,$gUser->id)) $o->jsflags |= kJSMapBuildingFlag_Open;
	}
	echo obj2jsparams($o,"x,y,type,user,level,hp,construction,jsflags").";";
}
echo "\";\n";

// items
$gItems = sqlgettable("SELECT * FROM `item` WHERE `army` = 0 AND `building` = 0 AND ".$xylimit);
echo 'gItems = "';
foreach ($gItems as $o) {
	echo $o->x.",".$o->y.",".$o->type.",".$o->amount.";";
}
echo "\";\n";

// plans
$gPlans = sqlgettable("SELECT * FROM `construction` WHERE ".$xylimit." AND `user` = ".$gUser->id);
echo 'gPlans = "';
foreach ($gPlans as $o) {
	echo $o->x.",".$o->y.",".$o->type.",".$o->priority.";";
}
echo "\";\n";


// armies
$gActiveArmy = false; // later : load waypoints for this army, but also output army info, even if it is not visible
if (isset($f_army)) $gActiveArmy = sqlgetobject("SELECT * FROM `army` WHERE `id`=".intval($f_army)." LIMIT 1");

$gArmies = sqlgettable("SELECT * FROM `army` WHERE ".$xylimit);
if ($gActiveArmy) $gArmies[] = $gActiveArmy;
foreach ($gArmies as $o) {
	$gLocalUserIDs[] = $o->user;
	echo "jsArmy(".cArmy::GetJavaScriptArmyData($o).");\n";
}


// local users
$gLocalUserIDs = array_unique($gLocalUserIDs);
if (count($gLocalUserIDs)>0)
		$gLocalUsers = sqlgettable("SELECT `id`,`guild`,`color`,`name` FROM `user` WHERE `id` IN (".implode(",",$gLocalUserIDs).")");
else	$gLocalUsers = array();
foreach ($gLocalUsers as $o) {
	$gLocalGuildIDs[] = $o->guild;
	echo "jsUser(".obj2jsparams($o,"id,guild,color,name").");\n";
}

// build distance sources
// MIN(SQRT((`x`-$x)*(`x`-$x) + (`y`-$y)*(`y`-$y)))
$bd_sources = sqlgettable("SELECT * FROM `building` WHERE `user` = ".intval($gUser->id)." AND `construction` = 0 AND `type` IN (".implode(",",$gBuildDistanceSources).")");
// now pic out the relevant ones for this map frame...
$bd_relevant_sources = array(); // two dimensional [$gCY+2][$gCX+2]
$debug_sources = false;
foreach ($bd_sources as $o) {
	// zone x is relative x coord limited to one unit outside the visible range
	$zonex = max(-1,min($gCX,($o->x-$gLeft)));
	$zoney = max(-1,min($gCY,($o->y-$gTop)));
	if ($zonex >= 0 && $zonex < $gCX && $zoney >= 0 && $zoney < $gCX) {
		// inside -> ok
		$bd_relevant_sources[$zoney+1][$zonex+1] = $o;
	} else {
		// edgexy is the therest tile inside visible range
		$edgex = max(0,min($gCX-1,($zonex)));
		$edgey = max(0,min($gCY-1,($zoney)));
		if (isset(	$bd_relevant_sources[$edgey+1][$edgex+1])) continue; // a source is at the edge, no need to look beyond
		$o->dist = ($o->x-$gLeft-$edgex)*($o->x-$gLeft-$edgex) + ($o->y-$gTop-$edgey)*($o->y-$gTop-$edgey); // quadratic distance is enough
		if ($debug_sources) echo "// $o->x,$o->y : $zonex,$zoney -> $edgex,$edgey : $o->dist ";
		if (!isset(	$bd_relevant_sources[$zoney+1][$zonex+1]->dist) ||
					$bd_relevant_sources[$zoney+1][$zonex+1]->dist > $o->dist) {
					$bd_relevant_sources[$zoney+1][$zonex+1] = $o;
					if ($debug_sources) echo "GOT\n";
		} else if ($debug_sources) {
			$b = $bd_relevant_sources[$zoney+1][$zonex+1];
			echo "dropped in favor of : $b->x,$b->y,$b->dist\n";
		}
	}
}
echo 'gBuildSources = "';
foreach ($bd_relevant_sources as $arr) foreach ($arr as $o) echo ($o->x).",".($o->y).";";
//foreach ($bd_sources as $o) echo ($o->x).",".($o->y).";";
echo "\";\n";

// waypoints
echo 'gWPs = "';
if ($gActiveArmy && cArmy::CanControllArmy($gActiveArmy,$gUser)) 
	echo cArmy::GetJavaScriptWPs($gActiveArmy->id,$gLeft,$gTop,$gCX,$gCY);
echo "\";\n";
// TODO : also transmit planned army actions such as pillage, siege... , draw above waypoints (for siege through path)

// collect data
// $gBodenschaetze = sqlgettable("SELECT * FROM `bodenschatz` WHERE ".$xylimit);

$gLocalGuildIDs = array_unique($gLocalGuildIDs);
// local guilds (+ points...)
?>
//-->
</SCRIPT>
</head><body id="mapbody" onLoad="MapInit()">
<span id="mapzone">JavaScript needed</span>
<?php if (1) {?><div name="mapdebug"></div><?php }?>
</body></html>
