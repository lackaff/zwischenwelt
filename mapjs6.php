<?php
define("kFastSession",true);
require_once("lib.main.php");
require_once("lib.map.php");
require_once("lib.army.php");
require_once("lib.unit.php");
define("kMapColor_Hilight","#FFFFFF");
define("kMapColor_Neutral_User","#66AA55");
Lock();

// TODO : this script is heavily reloaded -> reduce requirements ??

$profile = false;
$profile_title = isset($f_big) ? (kMapScript." - big") : kMapScript;
if ($profile) profile_page_start($profile_title." - 10");

function UnitNWSE($x,$y,$armys){
	$b = (int)0;
	foreach($armys as $a){
		$units = cUnit::GetUnits($a->id);
		$type = cUnit::GetUnitsMaxType($units);
		if($type == kMonster_HyperblobID){
			if($a->x == $x){
				if($a->y-1 == $y)$b |= kNWSE_S;
				else if($a->y+1 == $y)$b |= kNWSE_N;
			} else if($a->y == $y){
				if($a->x-1 == $x)$b |= kNWSE_E;
				else if($a->x+1 == $x)$b |= kNWSE_W;
			}
		}
	}
	return $b;
}

//adds an letter to the nwse string a the right position of the tID-NWSE cssclass
function addNWSELetter($nwse,$letter){
	if($nwse{0} == 'p')return $nwse;
	list($a,$b) = explode("-",$nwse);
	
	//echo "addNWSELetter($nwse,$letter) ";
	$b = (int)$b;
	if($letter == "n")$b |= kNWSE_N;
	if($letter == "w")$b |= kNWSE_W;
	if($letter == "s")$b |= kNWSE_S;
	if($letter == "e")$b |= kNWSE_E;
	$out = $a."-".$b;
	//echo "= $out<br>";
	return $out;
}


$gCX = isset($f_cx)?(min(200,max(0,intval($f_cx)))|1):11;
$gCY = isset($f_cy)?(min(200,max(0,intval($f_cy)))|1):11;

// hilighting
$gHilightPlayer = isset($f_hilight)?intval($f_hilight):0;
if (!isset($f_mode) || is_int($f_mode)) $f_mode = "normal";


$gMapMarks = sqlgettable("SELECT * FROM `mapmark` WHERE `user` = ".$gUser->id." ORDER BY `name`","id");
// show army pos
if (isset($f_armygoto)) {
	// goto mapmark/army
	if ($f_army{0} == 'h' && $gUser->admin) {
		$f_army = substr($f_army,1);
		$hellhole = sqlgetobject("SELECT `x`,`y` FROM `hellhole` WHERE `id` = ".intval($f_army));
		if ($hellhole) {
			$f_x = $hellhole->x;
			$f_y = $hellhole->y;
		}
		unset($f_army); // TODO : fixme, this was just quick and dirty...
		unset($f_armygoto);
	} else if (intval($f_army) < 0) {
		$mapmark = sqlgetobject("SELECT `x`,`y` FROM `mapmark` WHERE `user` = ".$gUser->id." AND `id` = ".(-intval($f_army)));
		if ($mapmark) {
			$f_x = $mapmark->x;
			$f_y = $mapmark->y;
		}
		unset($f_army); // TODO : fixme, this was just quick and dirty...
		unset($f_armygoto);
	} else {
		$army = sqlgetobject("SELECT `x`,`y` FROM `army` WHERE `id` = ".intval($f_army));
		if ($army) {
			$f_x = $army->x;
			$f_y = $army->y;
		}
	}
}

if (isset($f_pos) && eregi("((-|\\+)?[0-9]+)[^0-9+\\-]*((-|\\+)?[0-9]+)",$f_pos,$r)) {
	$f_x = intval($r[1]);
	$f_y = intval($r[3]);
}

if (!isset($f_x) || !isset($f_y)) {
	$home = sqlgetobject("SELECT `x`,`y` FROM `building` WHERE `type` = 1 AND `user` = ".$gUser->id);
	if ($home) {
		// get home building
		$gX = $home->x;
		$gY = $home->y;
	} else {
		// FindRandomStartplace
		require_once("lib.map.php");
		list($gX,$gY) = FindRandomStartplace();
	}
} else {
	$gX = intval($f_x);
	$gY = intval($f_y);
}

//RegenAreaNWSE($gX,$gY,$gX+$gCX,$gY+$gCY);

// dont change x,y below here
$xmid = ($gCX-1)/2;
$ymid = ($gCY-1)/2;
$gLeft = $gX - ($gCX-1)/2;
$gTop = $gY - ($gCY-1)/2;
$gScroll = floor($gCX/2);

$xylimit = "`x` >= ".($gLeft-1)." AND `x` < ".($gLeft+$gCX+1)." AND 
			`y` >= ".($gTop-1)." AND `y` < ".($gTop+$gCY+1);


// initialize map
$gMapClassesBG = array_fill(0,$gCY,array_fill(0,$gCX,"t1-0"));
$gMapClassesBuilding = array(); //array_fill(0,$gCY,array_fill(0,$gCX,""));
$gMapClasses = array(); //array_fill(0,$gCY,array_fill(0,$gCX,""));
$gMapBorder = array_fill(0,$gCY,array_fill(0,$gCX,false));
$gMapContent = false;
//$gMapBlocked = array_fill(0,$gCX,array_fill(0,$gCY,false)); // filled by local users , terrain and buildings

if ($profile) profile_page_start($profile_title." - 20");

// local users
$gLocalUserIDs = array();
$gMapArmy = sqlgettable("SELECT * FROM `army` WHERE ".$xylimit);
foreach($gMapArmy as $o) {
	if ($o->user > 0 && !in_array($o->user,$gLocalUserIDs))
		$gLocalUserIDs[] = $o->user;
	//$gMapBlocked[$o->x-$gLeft][$o->y-$gTop] = true;
}
$gMapBuilding = sqlgettable("SELECT * FROM `building` WHERE ".$xylimit);
foreach($gMapBuilding as $o) {
	if ($o->type != kBuilding_Path && $o->type != kBuilding_Wall &&
		$o->user > 0 && !in_array($o->user,$gLocalUserIDs))
		$gLocalUserIDs[] = $o->user;
	//if ($gBuildingType[$o->type]->speed == 0) $gMapBlocked[$o->x-$gLeft][$o->y-$gTop] = true;
	//else if ($o->type == kBuilding_Bridge && $o->construction > 0) $gMapBlocked[$o->x-$gLeft][$o->y-$gTop] = true;
}

if (count($gLocalUserIDs)>0)
		$gLocalUsers = sqlgettable("SELECT `color`,`guild`,`id`,`name` FROM `user` WHERE `id` IN (".implode(",",$gLocalUserIDs).")","id");
else	$gLocalUsers = array();
function GetUserMapColor ($user) {
	if ($user == 0) return kMapColor_Neutral_User; 
	global $gHilightPlayer;
	if ($gHilightPlayer == $user)
		return kMapColor_Hilight;
	global $gLocalUsers;
	return isset($gLocalUsers[$user])?$gLocalUsers[$user]->color:"#00ff00";
}

if ($profile) profile_page_start($profile_title." - 30");

// terrain
$gMT = array_fill(0,$gCX,array_fill(0,$gCY,1));
$gMapTerrain = sqlgettable("SELECT * FROM `terrain` WHERE ".$xylimit);
foreach ($gMapTerrain as $o) $gMT[$o->x-$gLeft][$o->y-$gTop] = $o->type;
foreach ($gMapTerrain as $o) {
	$x = $o->x-$gLeft;
	$y = $o->y-$gTop;
	//if ($gTerrainType[$o->type]->speed == 0) $gMapBlocked[$x][$y] = true;
	$gMapClassesBG[$y][$x] = NWSEReplace("t".($o->type)."-%NWSE%",$o->nwse);
}

// terrain patch
foreach ($gMapTerrain as $o) {
	//fluss see verbindung
	$x = $o->x-$gLeft;
	$y = $o->y-$gTop;
	//if ($gTerrainType[$o->type]->speed == 0) $gMapBlocked[$x][$y] = true;
	
	if(!empty($gTerrainPatchTypeMap[$o->type])){
		//echo "check patches<br>";
		//there are patches for this terraintype so check if one matches
		foreach($gTerrainPatchTypeMap[$o->type] as $oo){
			//echo " patch $id l".($gMT[$x-1][$y])." r".($gMT[$x+1][$y])." u".($gMT[$x][$y-1])." d".($gMT[$x][$y+1])."<br>";
			//print_r($oo);
			if(
				($oo->left==0 || ($oo->left>0 && isset($gMT[$x-1][$y]) && $gMT[$x-1][$y] == $oo->left)) &&
				($oo->right==0 || ($oo->right>0 && isset($gMT[$x+1][$y]) && $gMT[$x+1][$y] == $oo->right)) &&
				($oo->up==0 || ($oo->up>0 && isset($gMT[$x][$y-1]) && $gMT[$x][$y-1] == $oo->up)) &&
				($oo->down==0 || ($oo->down>0 && isset($gMT[$x][$y+1]) && $gMT[$x][$y+1] == $oo->down))
			) {
				$gMapClassesBG[$y][$x] = "p".($oo->id);
				if($oo->left>0)$gMapClassesBG[$y][$x-1] = addNWSELetter($gMapClassesBG[$y][$x-1],"e");
				if($oo->right>0)$gMapClassesBG[$y][$x+1] = addNWSELetter($gMapClassesBG[$y][$x+1],"w");
				if($oo->up>0)$gMapClassesBG[$y-1][$x] = addNWSELetter($gMapClassesBG[$y-1][$x],"s");
				if($oo->down>0)$gMapClassesBG[$y+1][$x] = addNWSELetter($gMapClassesBG[$y+1][$x],"n");
				//echo "match !!!";
			}
		}
	}
}


// Buildings
foreach ($gMapBuilding as $o) {
	$x = $o->x-$gLeft;
	$y = $o->y-$gTop;
	$blocked = false;
	if (!cBuilding::BuildingOpenForUser($o,$gUser->id)) {
		$blocked = true;
		//$gMapBlocked[$x][$y] = true;
	}
	$gMapClassesBuilding[$y][$x] = GetBuildingCSS($o,$blocked);
		
	if ($f_mode == "health") {
		$gMapBorder[$y][$x] = GradientRYG(GetFraction($o->hp,cBuilding::calcMaxBuildingHp($o->type,$o->level)));
	} else {
		// those Buildings have no border
		if ($gBuildingType[$o->type]->border > 0)
			if($o->user > 0)$gMapBorder[$y][$x] = GetUserMapColor($o->user);
	}
}

if ($profile) profile_page_start($profile_title." - 40");

// construction plans
$gMapCons = sqlgettable("SELECT * FROM `construction` WHERE ".$xylimit." AND `user` = ".$gUser->id);
switch($f_mode){
	case "bauplan":
		$concount = 0;
		// show constructions as complete buildings
		foreach($gMapCons as $o) {
			++$concount;
			$o->construction = 0;
			$o->level = 0;
			$o->nwse = kNWSE_W | kNWSE_E;
			$gMapClasses[$o->y-$gTop][$o->x-$gLeft] = GetBuildingCSS($o,false);
			
			// those Buildings have no border
			$gMapBorder[$o->y-$gTop][$o->x-$gLeft] = "red";
		}
	break;
	case "bauzeit":
		foreach($gMapCons as $o) 
			$gMapClasses[$o->y-$gTop][$o->x-$gLeft] = "tcp";
	break;
	default:
		foreach($gMapCons as $o) 
			$gMapClasses[$o->y-$gTop][$o->x-$gLeft] = "cp";
	break;
}

if ($profile) profile_page_start($profile_title." - 50");

// waypoints & paths
if ($f_mode != "bauzeit" && isset($f_army) && $f_army>0) {
	$gMapContent = array_fill(0,$gCY,array_fill(0,$gCX,false));
	$army = sqlgetobject("SELECT * FROM `army` WHERE `id`=".intval($f_army)." LIMIT 1");
	$army->units = cUnit::GetUnits($army->id);
	if($army){
		$gWaypoints = sqlgettable("SELECT * FROM `waypoint` WHERE `army` = ".intval($f_army)." ORDER BY `priority`");
		for ($i=0,$imax=count($gWaypoints);$i<$imax-1;$i++) {
			$x1 = $gWaypoints[$i]->x;
			$y1 = $gWaypoints[$i]->y;
			$x2 = $gWaypoints[$i+1]->x;
			$y2 = $gWaypoints[$i+1]->y;
			for ($x=$x1,$y=$y1;$x!=$x2||$y!=$y2;) {
				list($x,$y) = GetNextStep($x,$y,$x1,$y1,$x2,$y2);
				if ($x >= $gLeft && $x-$gLeft < $gCX && $y >= $gTop && $y-$gTop < $gCY) 
					//$gMapClasses[$y-$gTop][$x-$gLeft] = $gMapBlocked[$x-$gLeft][$y-$gTop]?"pathb":"path";
					$gMapClasses[$y-$gTop][$x-$gLeft] = (cArmy::GetPosSpeed($x,$y,$army->user,$army->units) == 0)?"pathb":"path";
			}
		}
		foreach($gWaypoints as $o) if ($o->x >= $gLeft && $o->x-$gLeft < $gCX && $o->y >= $gTop && $o->y-$gTop < $gCY) {
			$x = $o->x-$gLeft;
			$y = $o->y-$gTop;
			$gMapContent[$y][$x] = $o->priority;
			//$gMapClasses[$y][$x] = $gMapBlocked[$x][$y]?"pathb":"wp";
			$gMapClasses[$y][$x] = (cArmy::GetPosSpeed($o->x,$o->y,$army->user,$army->units) == 0)?"pathb":"wp";
		}
	}
}

if ($profile) profile_page_start($profile_title." - 60");

if ($f_mode != "bauzeit" && $f_mode != "health") {
	$gMapItem = sqlgettable("SELECT * FROM `item` WHERE `army`=0 AND ".$xylimit);
	foreach($gMapItem as $o) if ($o->amount >= 1.0)
		$gMapClasses[$o->y-$gTop][$o->x-$gLeft] = "item_$o->type";
	foreach($gMapArmy as $o) {
		$units = cUnit::GetUnits($o->id);
		$maxtype = cUnit::GetUnitsMaxType($units);
		if($maxtype == kMonster_HyperblobID){
			$nwse = UnitNWSE($o->x,$o->y,$gMapArmy);
			$gMapClasses[$o->y-$gTop][$o->x-$gLeft] = NWSEReplace(kMonster_HyperblobCSS,$nwse);
		} else $gMapClasses[$o->y-$gTop][$o->x-$gLeft] = "unit_".$maxtype;
		if($o->user > 0)$gMapBorder[$o->y-$gTop][$o->x-$gLeft] = GetUserMapColor($o->user);
	}
}

if ($f_mode == "bauzeit") {
	require_once("lib.construction.php");
	$gMapContent = array_fill(0,$gCY,array_fill(0,$gCX,false));
	for ($x=0;$x<$gCX;++$x)
	for ($y=0;$y<$gCY;++$y) {
		if (//$gMapClassesBG[$y][$x] == "t1-0" && 
			(!isset($gMapClasses[$y][$x]) || $gMapClasses[$y][$x] == false || $gMapClasses[$y][$x] == "tcp")) {
			$tf = GetBuildDistFactor(GetBuildDistance($x+$gLeft,$y+$gTop,$gUser->id));
			$gMapBorder[$y][$x] = GradientRYG(1.0-GetFraction($tf-1.0,1.0),1.0);
			$gMapContent[$y][$x] = ($tf<10)?sprintf("%0.1f",$tf):"";
		}
	}
}

// produce session-independent querry, to enable caching
$styleparam = "?v=8";
if ($gUser->usegfxpath || $gUser->race != 1)
	$styleparam .= "&uid=".$gUser->id;
if ($gUser->usegfxpath) 
	$styleparam .= "&hash=".(substr(base64_encode($gUser->gfxpath),4,8));

if ($profile) profile_page_start($profile_title." - 70");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/transitional.dtd">
<html><head>
<link rel="stylesheet" type="text/css" href="<?="styles.css".$styleparam?>"></link>
<link rel="stylesheet" type="text/css" href="<?="mapstyle.php".$styleparam?>"></link>
<link rel="stylesheet" type="text/css" href="<?="mapstyle_terrain.php".$styleparam?>"></link>
<link rel="stylesheet" type="text/css" href="<?="mapstyle_building.php".$styleparam?>"></link>
<SCRIPT LANGUAGE="JavaScript" type="text/javascript"><!--
function getWindowWidth()
{
	if (window.innerWidth)return window.innerWidth;
	else if (document.documentElement && document.documentElement.clientWidth != 0)return document.documentElement.clientWidth;
	else if (document.body)return document.body.clientWidth;
	return 0;
}

var mapwidth = Math.floor((getWindowWidth()-2*40)/<?=$gTilesize?>);
//alert(mapwidth+" "+getWindowWidth());

<?php if (isset($f_big)) { /* navi */?>
function nav(x,y) {
	var scroll = document.getElementsByName("myscroll")[0].value;
	x = <?=intval($f_x)?> + x * scroll;
	y = <?=intval($f_y)?> + y * scroll;
	location.href = "<?=Query("?sid=?&big=?&army=?&mode=?&cx=?&cy=?&")?>x="+(x)+"&y="+(y); 
}
<?php } // endif?>
function getmode() { return "<?=$f_mode?>";}
function getleft() { return <?=$gLeft?>;}
function gettop() { return <?=$gTop?>;}
function getx() { return <?=$gX?>;}
function gety() { return <?=$gY?>;}
function getcx() { return <?=$gCX?>;}
function getcy() { return <?=$gCY?>;}
function m(x,y) {
	<?php if (isset($f_big)) {?>
	//opener.parent.info.location.href = "info/info.php?x="+(x+<?=$gLeft?>)+"&y="+(y+<?=$gTop?>)+"&sid=<?=$gSID?>";
	opener.parent.navi.map(x+<?=$gLeft?>,y+<?=$gTop?>);
	<?php } else {?>
	parent.navi.map(x+<?=$gLeft?>,y+<?=$gTop?>);
	<?php }?>
}
<?php if (!isset($f_naviset)) {?>
if (parent.navi != null && parent.navi.updatepos != null)
	parent.navi.updatepos(<?=$gX?>,<?=$gY?>);
<?php }?>
<?php if ($f_mode == "bauplan" && $concount == 0 && 0) {?> 
alert("Der Knopf Pläne zeigt Baupläne als fertige Gebäude an,\n damit man eine übersicht hat, was man wo geplant hat.");
<?php }?>
//-->
</SCRIPT>
</head><body>
<b>Für das beste Surferlebnis empfehlen wir <a href='http://www.firefox-browser.de/' target="_blank">FireFox</a>.</b><br>
<table><tr>
<?php if (isset($f_big)) { /* navi */?>
	<td valign="top" align="left">
		<!--Navigation-->
		<table class="mapnav" cellpadding="0" cellspacing="0"><?php
		?><tr><?php
		?><td><img src="<?=g("scroll/nw.png")?>" onClick="nav(-1,-1)"></td><?php
		?><td><img src="<?=g("scroll/n.png")?>" onClick="nav(0,-1)"></td><?php
		?><td><img src="<?=g("scroll/ne.png")?>" onClick="nav(1,-1)"></td><?php
		?></tr><tr><?php
		?><td><img src="<?=g("scroll/w.png")?>" onClick="nav(-1,0)"></td><?php
		?><td><img src="<?=g("scroll/r.png")?>" onClick="nav(0,0)"></td><?php
		?><td><img src="<?=g("scroll/e.png")?>" onClick="nav(1,0)"></td><?php
		?></tr><tr><?php
		?><td><img src="<?=g("scroll/sw.png")?>" onClick="nav(-1,1)"></td><?php
		?><td><img src="<?=g("scroll/s.png")?>" onClick="nav(0,1)"></td><?php
		?><td><img src="<?=g("scroll/se.png")?>" onClick="nav(1,1)"></td><?php
		?></tr><?php
		?></table>
		<FORM METHOD="POST" ACTION="<?=Query(kMapScript."?sid=?&big=?&cx=$gCX&cy=$gCY")?>">
		<INPUT TYPE="hidden" NAME="sid" VALUE="<?=$gSID?>">
		<INPUT TYPE="hidden" NAME="x" VALUE="0" style="width:30px">
		<INPUT TYPE="hidden" NAME="y" VALUE="0" style="width:30px">
		<INPUT TYPE="text" NAME="pos" VALUE="0" style="width:60px">
		<INPUT TYPE="submit" VALUE="Goto">
		scroll:
		<a href="javascript:void(document.getElementsByName('myscroll')[0].value = Math.floor(document.getElementsByName('myscroll')[0].value / 2))">-</a>
		<a href="javascript:void(document.getElementsByName('myscroll')[0].value *= 2)">+</a>
		<INPUT TYPE="text" NAME="myscroll" VALUE="<?=$gScroll?>" style="width:30px">
		</FORM>
	</td>
<?php } // endif?>
<?php if ($profile) profile_page_start($profile_title." - 90"); ?>

<td valign="top" align="left">
	<table>
	<?php $i=0; foreach($gLocalUsers as $o) if ($o->id > 0) { if (($i%3)==0) echo "<tr>";?>
		<td width=12 bgcolor="<?=GetUserMapColor($o->id)?>"></td>
		<td><a href="<?=Query("?sid=?&x=?&y=?&hilight=".$o->id)?>"><?=$o->name?></a></td>
	<?php if (($i%3)==2) echo "</tr>"; ++$i;}?>
	<?php if (($i%3)==2) echo "</tr>";?>
	</table>
</td><td valign="top" align="right"><?=$f_mode?("modus : ".$f_mode):""?></td></tr></table>

<table class="map" border=0 cellpadding="0" cellspacing="0" bgcolor='#66AA55' style="width:<?=(2+$gCX)*$gTilesize?>px;table-layout:fixed">
	<tr><th></th>
	<?php for ($x=$gLeft;$x<$gLeft+$gCX;$x++) {?><th><?=($x>=0&&$x<=9)?"&nbsp;$x":$x?></th><?php }?>
	<th></th></tr>
	<?php for ($y=0;$y<$gCY;$y++) { ?>
	<tr><th><?=$gTop+$y?></th>
	<?php for ($x=0;$x<$gCX;$x++) {
		$b = (isset($gMapBorder[$y])&&isset($gMapBorder[$y][$x]))?$gMapBorder[$y][$x]:false;
		
		$layers = array();
		if(!empty($gMapClassesBG[$y][$x]))$layers[] = $gMapClassesBG[$y][$x];
		if(!empty($gMapClassesBuilding[$y][$x]))$layers[] = $gMapClassesBuilding[$y][$x];
		if(!empty($gMapClasses[$y][$x]))$layers[] = $gMapClasses[$y][$x];
		if(sizeof($layers)>0)$layers = array($layers[sizeof($layers)-1]);
?>
<td class='<?=$gMapClassesBG[$y][$x]?>'><?php
for($i=0;$i<sizeof($layers);++$i){
	if($layers[$i] != $gMapClassesBG[$y][$x])$id = $layers[$i];
	else $id = "";
	if($i == 0 && $b)$bgcolor = " style='background-color:$b'";else $bgcolor = "";
	if($i == sizeof($layers)-1)$click = " onclick='m($x,$y)'";else $click = "";
	
	echo "<div class='$id'".$bgcolor.$click.">";
}
if($x==$xmid && $y==$ymid)echo "<img src='gfx/crosshair.png'>";
else if(isset($gMapContent[$y])&&isset($gMapContent[$y][$x]))echo $gMapContent[$y][$x];
else echo "";
for($i=0;$i<sizeof($layers);++$i)echo "</div>";
?></td>
	<?php }?>
	<th><?=$gTop+$y?></th></tr>
	<?php } ?>
	<tr><th></th>
	<?php for ($x=$gLeft;$x<$gLeft+$gCX;$x++) {?><th><?=$x?></th><?php }?>
	<th></th></tr>
</table>

</body></html>
<?php if ($profile) profile_page_end(); ?>
