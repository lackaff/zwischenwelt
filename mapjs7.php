<?php
define("kFastSession",true);
require_once("lib.main.php");
require_once("lib.map.php");
require_once("lib.army.php");
require_once("lib.unit.php");
require_once("lib.weather.php");
Lock();

$gCX = isset($f_cx)?(min(200,max(0,intval($f_cx)))|1):11;
$gCY = isset($f_cy)?(min(200,max(0,intval($f_cy)))|1):11;

// show army pos
if (isset($f_gotocat)) {
	//echo "gotocat = $f_gotocat , gotocat2 = $f_gotocat2 , gotocat3 = $f_gotocat3 <br>";
	$foundobject = false;
	if ($f_gotocat != kMapNaviGotoCat_Pos && !isset($f_armyshow)) unset($f_pos);
	switch ($f_gotocat) {
		case kMapNaviGotoCat_Pos: break; // handled by $f_pos
		case kMapNaviGotoCat_Mark: 
			$foundobject = sqlgetobject("SELECT `x`,`y` FROM `mapmark` WHERE `user` = ".$gUser->id." AND `id` = ".intval($f_gotocat2));
		break;
		case kMapNaviGotoCat_Own:
			$foundobject = sqlgetobject("SELECT * FROM `army` WHERE `id` = ".intval($f_gotocat3));
			$f_army = $foundobject->id;
			if (isset($f_armyshow)) $foundobject = 0;
		break;
		case kMapNaviGotoCat_Guild:
			if ($f_gotocat2 > 0) { // if cat2>0 then armytype else userid
				$foundobject = sqlgetobject("SELECT * FROM `army` WHERE `id` = ".intval($f_gotocat3));
				$f_army = $foundobject->id;
				if (isset($f_armyshow)) $foundobject = 0;
			} else $foundobject = sqlgetobject("SELECT `x`,`y` FROM `building` WHERE `user` = ".intval($f_gotocat3)." AND `type` = ".kBuilding_HQ);
		break;
		case kMapNaviGotoCat_Friends:
		case kMapNaviGotoCat_Enemies:
			$foundobject = sqlgetobject("SELECT `x`,`y` FROM `building` WHERE `user` = ".intval($f_gotocat2)." AND `type` = ".kBuilding_HQ);
		break;
		case kMapNaviGotoCat_Search:
			// $f_searchcounter
			$foundplayerid = 0;
			$f_search = trim($f_search);
			if (!empty($f_search)) switch ($f_gotocat2) {
				case 0: // Spieler
					$mylist = sqlgetonetable("SELECT `id` FROM `user` WHERE `name` LIKE '%".addslashes($f_search)."%'");
					if (count($mylist) > 0) $foundplayerid = $mylist[intval($f_searchcounter) % count($mylist)];
				break;
				case 1: // Gilde
					$mylist = sqlgetonetable("SELECT `user`.`id` FROM `user`,`guild` WHERE `guild`.`id` = `user`.`guild` AND `guild`.`name` LIKE '%".addslashes($f_search)."%'");
					if (count($mylist) > 0) $foundplayerid = $mylist[intval($f_searchcounter) % count($mylist)];
				break;
				case 2: // Armee
					$mylist = sqlgettable("SELECT `x`,`y` FROM `army` WHERE `name` LIKE '%".addslashes($f_search)."%'");
					if (count($mylist) > 0) $foundobject = $mylist[intval($f_searchcounter) % count($mylist)];
				break;
				case 3: // Monster
					$mylist = sqlgettable("SELECT `x`,`y` FROM `army` WHERE `name` LIKE '%".addslashes($f_search)."%'");
					if (count($mylist) > 0) $foundobject = $mylist[intval($f_searchcounter) % count($mylist)];
				break;
				case 4: // Bodenschatz
					$typelist = sqlgetonetable("SELECT `id` FROM `buildingtype` WHERE `id` IN (".implode(",",$gBodenSchatzBuildings).") AND `name` LIKE '%".addslashes($f_search)."%'");
					if (count($typelist) > 0) {
						$mylist = sqlgettable("SELECT `x`,`y` FROM `building` WHERE `type` IN (".implode(",",$typelist).") ");
						if (count($mylist) > 0) $foundobject = $mylist[intval($f_searchcounter) % count($mylist)];
					}
				break;
			}
			if ($foundplayerid) $foundobject = sqlgetobject("SELECT `x`,`y` FROM `building` WHERE `user` = ".$foundplayerid." AND `type` = ".kBuilding_HQ);
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
$jsparam = "v2=".(intval(kJSMapVersion)+intval($gGlobal["typecache_version_adder"]));
$styleparam = "?v=".(8+intval($gGlobal["typecache_version_adder"]));
$gfxpackactive = $gUser && !empty($gUser->gfxpath) && (empty($gSessionObj) || $gSessionObj->usegfx);
if ($gfxpackactive || $gUser->race != 1)
	$styleparam .= "&uid=".$gUser->id;
if ($gfxpackactive) 
	$styleparam .= "&hash=".(substr(base64_encode($gUser->gfxpath),4,8));

// calc gfx path
if($gUser && $gfxpackactive){
	if($gUser->gfxpath{strlen($gUser->gfxpath)-1} != '/')
			$gGFXBase = $gUser->gfxpath . "/";
	else	$gGFXBase = $gUser->gfxpath;
} else		$gGFXBase = kGfxServerPath;



$gUseDarianMap = intval($gUser->flags) & kUserFlags_DarianMap;
$gCustomMapCode = $gUseDarianMap ? sqlgetone("SELECT `code` FROM `mapcode` WHERE `name` = 'Darian'") : false;
$gCustomMapCSS = $gUseDarianMap ? sqlgetone("SELECT `css` FROM `mapcode` WHERE `name` = 'Darian'") : false;


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/transitional.dtd">
<html><head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<link rel="stylesheet" type="text/css" href="<?=GetZWStylePath()?>"></link>
<?php if (!$gCustomMapCode) {?><script src="mapjs7_core.js?<?=$jsparam?>" type="text/javascript"></script><?php }?>
<script src="<?="mapjs7_globals.js.php".$styleparam."&".$jsparam?>" type="text/javascript"></script>
<?php if ($gCustomMapCSS) {?>
<style type="text/css"><?=$gCustomMapCSS?></style>
<?php } // endif?>
<SCRIPT LANGUAGE="JavaScript" type="text/javascript"><!--
kBaseJSMapVersion = <?=intval(kJSMapVersion)+intval($gGlobal["typecache_version_adder"])?>;
kBaseUrl = "<?=BASEURL?>";
kMapScript = "<?=kMapScript?>";
kUserID = "<?=$gUser->id?>";
kCurTime = "<?=time()?>";
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
gMapMode = <?=isset($f_mode)?intval($f_mode):0?>;
gSlowMap = <?=(intval($gUser->flags) & kUserFlags_SlowMap)?1:0?>;
gWeatherGfx = "<?=g($gWeatherGfx[$gWeather])?>";
gWeatherType = "<?=$gWeatherType[$gWeather]?>";
gOverlay = new Array();
<?php

if ($gCustomMapCode) {

	echo $gCustomMapCode;
	
} else {

	//gather overlay images
	//fire
	$t = sqlgettable("SELECT * FROM `fire` WHERE $xylimit");
	foreach($t as $x){?>
	gOverlay["<?=($x->x)-$gLeft?>-<?=($x->y)-$gTop?>"] = "fire";
	<?php } ?>

	function MapLoad () {
	
	<?php
	// terrain
	//$map = getMapAtPosition($gLeft,$gTop,$gCX,$gCY,true);
	//$gTerrain = sqlgettable("SELECT * FROM `terrain` WHERE ".$xylimit." ORDER BY `y`,`x`");
	echo 'gTerrain = "';
	$i = 0;
	for ($y=-1;$y<$gCY+1;++$y) {
		for ($x=-1;$x<$gCX+1;++$x) {
			//echo $map->getTerrainTypeAt($gLeft+$x,$gTop+$y).",";
			echo cMap::StaticGetTerrainAtPos($gLeft+$x,$gTop+$y).",";
			/*
			if ($gTerrain[$i]->x - $gLeft == $x && $gTerrain[$i]->y - $gTop == $y) {
				echo $gTerrain[$i]->type.",";
				++$i;
			} else {
				// todo : lookup default-terrain here
				echo '0,'; // 0 becomes grass
			}
			*/
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
		echo cBuilding::GetJavaScriptBuildingData($o,false,'').";";
	}
	echo "\";\n";

	echo "gBuildingData = new Array();\n";
	foreach ($gBuildings as $o) {
		switch ($o->type) { 
			case kBuilding_Sign:
				$text = trim(cText::justifiedtext(magictext(htmlspecialchars(GetBParam($o->id,"text")),$o->user),30));
				$text = "<pre>".$text."</pre>";
				echo "gBuildingData[".$o->id."] = \"".strtr(addslashes($text),array("\n"=>"\\n","\r"=>""))."\";\n";
			break;
		}
	}

	// building type busy changes
	// contains a value between 0 and 100, if the random value 0-100 is below the value %BUSY% is 1 otherwise 0
	// if of the array is the buildingtypeid, if not set %BUSY% is 0
	$gBusy = array();
	$slots = GetProductionSlots($gUser->id);
	$totalworker = $gUser->pop;
	foreach($gResFields  as $res){
		$btype = $gGlobal["building_".$res];
		$worker_100 = $gUser->{"worker_$res"};
		$worker = round($totalworker * $worker_100 / 100);
		if($slots[$res] > 0){
			$usage = round(100*$worker/$slots[$res]);
			$usage = min(100,max(0,$usage));
		} else $usage = 0;
			
		$gBusy[$btype] = $usage;
	}
	echo "gBusy = new Array();\n";
	foreach ($gBusy as $id=>$usage) {
		echo "gBusy[$id] = $usage;\n";
	}

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
	$gActiveArmy = false;
	if (isset($f_army)) $gActiveArmy = sqlgetobject("SELECT * FROM `army` WHERE `id`=".intval($f_army)." LIMIT 1");

	$gArmies = sqlgettable("SELECT * FROM `army` WHERE ".$xylimit);
	$controllable_armies = cArmy::ListControllableArmies();
	if ($gActiveArmy) $controllable_armies[] = $gActiveArmy;
	foreach ($controllable_armies as $o) {
		$found = false;
		foreach ($gArmies as $x) if ($o->id == $x->id) { $found = true; break; }
		if (!$found) $gArmies[] = $o;
	}
	foreach ($gArmies as $o) {
		$gLocalUserIDs[] = $o->user;
		echo "jsArmy(".cArmy::GetJavaScriptArmyData($o,$gLeft,$gTop,$gCX,$gCY).");\n";
	}


	// local users
	$gLocalUserIDs = array_unique($gLocalUserIDs);
	if (count($gLocalUserIDs)>0)
			$gLocalUsers = sqlgettable("SELECT `id`,`guild`,`color`,`name`,`race`,`moral` FROM `user` WHERE `id` IN (".implode(",",$gLocalUserIDs).")");
	else	$gLocalUsers = array();
	foreach ($gLocalUsers as $o) {
		$gLocalGuildIDs[] = $o->guild;
		echo "jsUser(".obj2jsparams($o,"id,guild,color,name,race,moral").");\n";
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

	echo 'gWPs = "';
	echo "\";\n";
	// TODO : remove completely
	
	// TODO : also transmit planned army actions such as pillage, siege... , draw above waypoints (for siege through path)

	// collect data
	// $gBodenschaetze = sqlgettable("SELECT * FROM `bodenschatz` WHERE ".$xylimit);

	$gLocalGuildIDs = array_unique($gLocalGuildIDs);
	// local guilds (+ points...)

	echo "MapInit();\n"; // data completely transmitted, time to parse
	?>
	}

<?php }?>

//-->
</SCRIPT>
</head><body id="mapbody" onLoad="MapLoad()">
<?php if (1) {?><div class="mapreporttext" name="mapdebug" id="mapdebug"></div><?php }?>
<div class="tabs"><?php // prevent linefeeds ins html here...
?><div class="tabheader"><span id="mapheaderzone">JavaScript Karte wird geladen...</span></div><?php
?><div class="tabpane"><div id="totalmapborder" style="border:2px solid white;"><?php
?><span id="mapzone">...</span><?php
?></div></div></div><?php
?><div class="tabsend"></div><span id="maptipzone"></span>
<noscript><b style="color:red">JavaScript needed!</b><br></noscript>
</body></html>
