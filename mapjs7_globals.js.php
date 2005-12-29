<?php
require_once("lib.main.php");
//if ( extension_loaded('zlib') )ob_start('ob_gzhandler');
header('Content-Type: text/javascript');
//header('Last-Modified: '.date("r",time()-60*60*24*7));
//header('Cache-Control: max-age='.(60*60*24*7).', must-revalidate');

?>
kDefaultTerrainID = <?=kTerrain_Grass?>;
kNWSE_N = <?=kNWSE_N?>;
kNWSE_W = <?=kNWSE_W?>;
kNWSE_S = <?=kNWSE_S?>;
kNWSE_E = <?=kNWSE_E?>;
kConstructionPlanPic = "<?=kConstructionPlanPic?>";
kConstructionPic = "<?=kConstructionPic?>";
kTransCP = "<?=kTransCP?>"; // transparent construction plan

kJSMapBuildingFlag_Open = <?=kJSMapBuildingFlag_Open?>;
kJSMapBuildingFlag_Tax = <?=kJSMapBuildingFlag_Tax?>;
kJSMapArmyFlag_Moving_N = <?=kJSMapArmyFlag_Moving_N?>;
kJSMapArmyFlag_Moving_W = <?=kJSMapArmyFlag_Moving_W?>;
kJSMapArmyFlag_Moving_S = <?=kJSMapArmyFlag_Moving_S?>;
kJSMapArmyFlag_Moving_E = <?=kJSMapArmyFlag_Moving_E?>;
kJSMapArmyFlag_Fighting_N = <?=kJSMapArmyFlag_Fighting_N?>;
kJSMapArmyFlag_Fighting_W = <?=kJSMapArmyFlag_Fighting_W?>;
kJSMapArmyFlag_Fighting_S = <?=kJSMapArmyFlag_Fighting_S?>;
kJSMapArmyFlag_Fighting_E = <?=kJSMapArmyFlag_Fighting_E?>;
kJSMapArmyFlag_Shooting = <?=kJSMapArmyFlag_Shooting?>;



//kJSMapTileSize = <?=kMapTileSize?>;
kJSMapTileSize = 27;

// HACK: (hyperblob)
function UnitTypeHasNWSE (unittype) {
	return unittype == <?=kUnitType_HyperBlob?>;
}

// HACK: hardcode a few connect-to-building entries
function HackCon () {
	gBuildingType[<?=kBuilding_Gate		?>].connectto_building.push(<?=kBuilding_Wall?>);
	gBuildingType[<?=kBuilding_Wall		?>].connectto_building.push(<?=kBuilding_Gate?>);
	gBuildingType[<?=kBuilding_Path		?>].connectto_building.push(<?=kBuilding_Gate?>);
	gBuildingType[<?=kBuilding_GB		?>].connectto_building.push(<?=kBuilding_Path?>);
	gBuildingType[<?=kBuilding_Path		?>].connectto_building.push(<?=kBuilding_GB?>);
	gBuildingType[<?=kBuilding_Bridge	?>].connectto_building.push(<?=kBuilding_Path?>);
	gBuildingType[<?=kBuilding_Path		?>].connectto_building.push(<?=kBuilding_Bridge?>);
	gBuildingType[<?=kBuilding_SeaWall	?>].connectto_building.push(<?=kBuilding_SeaGate?>);
	gBuildingType[<?=kBuilding_SeaGate	?>].connectto_building.push(<?=kBuilding_SeaWall?>);
	
	gBuildingType[<?=kBuilding_Steg		?>].connectto_building.push(<?=kBuilding_Harbor?>);
	gBuildingType[<?=kBuilding_Harbor	?>].connectto_terrain.push(<?=kTerrain_Sea?>);
	gTerrainType[<?=kTerrain_Sea		?>].connectto_building.push(<?=kBuilding_Harbor?>);
}

// HACK: special nwse for path,gates,bridge...  also in UpdateBuildingNWSE()
function HackNWSE (buildingtype,nwsecode,relx,rely) {
	var singlenwse = false;
	var dualnwse = false;
	var inverseconnect = false;
	if (buildingtype == <?=kBuilding_Gate?> || 
		buildingtype == <?=kBuilding_GB?> ) 
			inverseconnect = new Array("",<?=kBuilding_Path?>,<?=kBuilding_Gate?>,<?=kBuilding_GB?>);
	if (buildingtype == <?=kBuilding_Bridge?>) dualnwse = true;
	if (buildingtype == <?=kBuilding_SeaGate?>) dualnwse = true;
	if (inverseconnect) dualnwse = true; 
	if (dualnwse) {
		var hhit = 0,vhit = 0;  
		if ((nwsecode & (kNWSE_N|kNWSE_S)) == (kNWSE_N|kNWSE_S)) return kNWSE_N|kNWSE_S; // double match
		if ((nwsecode & (kNWSE_W|kNWSE_E)) == (kNWSE_W|kNWSE_E)) return kNWSE_W|kNWSE_E; // double match
		if ((nwsecode & (kNWSE_N|kNWSE_S)) != 0) vhit++; // single match
		if ((nwsecode & (kNWSE_W|kNWSE_E)) != 0) hhit++; // single match
		if (inverseconnect) {
			if (InArray(GetBuildingType(relx,rely-1),inverseconnect)) hhit++;
			if (InArray(GetBuildingType(relx,rely+1),inverseconnect)) hhit++;
			if (InArray(GetBuildingType(relx-1,rely),inverseconnect)) vhit++;
			if (InArray(GetBuildingType(relx+1,rely),inverseconnect)) vhit++;
		}
		if (vhit >= hhit) return kNWSE_N|kNWSE_S;
		return kNWSE_W|kNWSE_E;
	}
	if (buildingtype == <?=kBuilding_Harbor?>) singlenwse = true;
	if (singlenwse) {
		if (nwsecode & kNWSE_N) return kNWSE_N;
		if (nwsecode & kNWSE_W) return kNWSE_W;
		if (nwsecode & kNWSE_S) return kNWSE_S;
		if (nwsecode & kNWSE_E) return kNWSE_E;
		return kNWSE_N;
	}
	return nwsecode;
}

<?php

// prints a javascript function to create a js-object from its parameters
// if global_arr_name is false, the object is returned, otherwise it is added to the end of the array (global js var)
// if indexfield is false, then the object is added to the end of the array
// used for exporting object with string fields to javascript (users,armies,type-arrays)
function php2js_objectfunction ($function_name,$fields,$global_arr_name=false,$index_field=false) {
	if (!is_array($fields)) $fields = explode(",",$fields);
	?>
	function <?=$function_name?> (<?=implode(",",$fields)?>) {
		var res = new Object();
		<?php foreach ($fields as $field) echo "res.".$field." = ".$field.";";?>
		<?php if ($global_arr_name) {?>
		<?=$global_arr_name?>[<?=$index_field?$index_field:($global_arr_name.".length")?>] = res;
		<?php } else {?>
		return res;
		<?php }?>
	}
	<?php 
}

// prints a javascript function
// for parsing arrays of objects, that have been passed to javascript as comma seperated list
function php2js_parser ($function_name,$fields,$globalarr,$sep_obj=";",$sep_val=",") {
	if (!is_array($fields)) $fields = explode(",",$fields);
	?>
	function <?=$function_name?> () {
		var i;
		<?=$globalarr?> = <?=$globalarr?>.split("<?=addslashes($sep_obj)?>");	
		<?=$globalarr?>.pop();
		for (i in <?=$globalarr?>) {
			<?=$globalarr?>[i] = <?=$globalarr?>[i].split("<?=addslashes($sep_val)?>");
			var res = new Object();
			<?php $i = 0; foreach ($fields as $field) echo "res.".$field." = ".$globalarr."[i][".($i++)."];";?>
			<?=$globalarr?>[i] = res;
		}
	}
	<?php 
}

// produces array full of objects, can be accessed using "gTerrainType[12].gfx"
// for exporting type-arrays to javascript
function php2js_objarray ($name,$arr,$fields) {
	if (!$fields) return;
	if (!is_array($fields)) $fields = explode(",",$fields);
	php2js_objectfunction("entry_".$name,$fields);
	echo $name." = new Array();\n";
	foreach ($arr as $key => $o) {
		$objfields = obj2arr($o);
		$values = array();
		foreach ($fields as $fieldname) {
			$value = $o->{$fieldname};
			if (is_array($value)) $values[] = "'".addslashes(implode(",",$value))."'"; // warning, not suitable for fancy values
			else if (is_numeric($value)) 
					$values[] = $value;
			else	$values[] = "'".addslashes($value)."'";
		}
		echo $name."[".$key."] = entry_".$name."(".implode(",",$values).");\n";
	}
}

php2js_objarray("gTerrainType",$gTerrainType,"name,speed,buildable,gfx,mod_a,mod_v,mod_f,movable_flag,connectto_terrain,connectto_building");
php2js_objarray("gBuildingType",$gBuildingType,"name,maxhp,speed,gfx,mod_a,mod_v,mod_f,connectto_terrain,connectto_building,neednear_building,require_building,exclude_building,border,movable_flag,movable_override_terrain");
php2js_objarray("gUnitType",$gUnitType,"name,orderval,a,v,f,r,speed,gfx");
php2js_objarray("gItemType",$gItemType,"name,gfx");
php2js_objarray("gTerrainPatchType",$gTerrainPatchType,"id,gfx,here,up,down,left,right");
// bodenschaetze (ressources,perks,specials,deposit...)

php2js_objectfunction("jsUser","id,guild,color,name","gUsers","id");
php2js_objectfunction("jsArmy","id,x,y,name,type,user,units,items,jsflags","gArmies","id");
php2js_parser("jsParseBuildings","x,y,type,user,level,hp,construction,jsflags","gBuildings");
php2js_parser("jsParseItems","x,y,type,amount","gItems");
php2js_parser("jsParsePlans","x,y,type,priority","gPlans");



/*




// bauzeit
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
*/

/* // css notes

 ***** OLD-TABS ***** 
BACKGROUND-COLOR : #efd39c;
BACKGROUND-COLOR : #deb67b;

display:Wert;
Für Wert einen der folgenden Werte notieren.
block = Erzwingt einen Block - das Element erzeugt eine neue Zeile.
inline = Erzwingt die Anzeige im Text - das Element wird im laufenden Textfluss angezeigt.
list-item = wie block, jedoch mit einem Aufzählungszeichen (Bullet) davor.
marker = deklariert automatisch generierten Text für das Element.
run-in und compact = bewirken, dass das Element kontext-abhängig als Block-Element oder als Inline-Element dargestellt wird.
none = Element wird nicht angezeigt und es wird auch kein Platzhalter freigelassen.

visibility:hidden;
visibility:visible;
Für Wert einen der folgenden Werte notieren:
hidden = Der Inhalt des Element wird zunächst versteckt (nicht angezeigt).
visible = Der Inhalt des Element wird zunächst angezeigt (Normaleinstellung).

line-height:0pt; font-size:0pt; font-weight:0;
clip:rect(Wert1 Wert2 Wert3 Wert4);

cursor:Wert;
Zugeordnetes Element erhält beim Überfahren mit der Maus einen anderen Cursor. Für Wert einen der folgenden Werte notieren:
auto = automatischer Cursor (Normaleinstellung).
default = Plattformunabhängiger Standard-Cursor.
crosshair = Cursor in Form eines einfachen Fadenkreuzes.
pointer = Cursor in Form eines Zeigers.
move = Cursor in Form eines Kreuzes, das die Fähigkeit zum Bewegen des Elements signalisiert.
n-resize = Cursor in Form Pfeils, der nach oben zeigt (n = Norden).
ne-resize = Cursor in Form Pfeils, der nach rechts oben zeigt (ne = Nordost).
e-resize = Cursor in Form Pfeils, der nach rechts zeigt (e = Osten).
se-resize = Cursor in Form Pfeils, der nach rechts unten zeigt (se = Südost).
s-resize = Cursor in Form Pfeils, der nach unten zeigt (s = Süden).
sw-resize = Cursor in Form Pfeils, der nach links unten zeigt (sw = Südwest).
w-resize = Cursor in Form Pfeils, der nach links zeigt (w = Westen).
nw-resize = Cursor in Form Pfeils, der nach links oben zeigt (nw = Nordwest).
text = Cursor in einer Form, die normalen Text symbolisiert.
wait = Cursor in Form eines Symbols, das einen Wartezustand signalisiert.
help = Cursor in Form Symbols, das Hilfe zu dem Element signalisiert.
url([URI]) = Beliebiger Cursor, URI sollte eine GIF- oder JPG-Grafik sein.

.tabs A:link, 
.tabs A:visited,
.tabs A:active, 
.tabs A:hover
*/


if (0) {?>
	.wp { background-color:#00FF00; }
	.pathb { background-color:#FF8888; }
	.path { background-color:#88FF88; }
	.cp { background-image:url(<?=g(kConstructionPlanPic)?>); }
	.tcp { background-image:url(<?=g(kTransCP)?>); }
	.con { background-image:url(<?=g(kConstructionPic)?>); }
	.gr { background-image:url(<?=g("grass.png")?>); }

	define("kMapColor_Hilight","#FFFFFF");
	define("kMapColor_Neutral_User","#66AA55");

	document.getElementById("age").innerHTML = gAge + " =)";
	
	gHalloWeltInterval = window.setInterval("HalloWeltStep()",50);
	window.clearInterval(gHalloWeltInterval);
	
	parseInt(..);

	function getWindowWidth()
	{
		if (window.innerWidth)return window.innerWidth;
		else if (document.documentElement && document.documentElement.clientWidth != 0)return document.documentElement.clientWidth;
		else if (document.body)return document.body.clientWidth;
		return 0;
	}

	var mapwidth = Math.floor((getWindowWidth()-2*40)/<?=$gTilesize?>);
	//alert(mapwidth+" "+getWindowWidth());

	<?php if (isset($f_big)) { // navi ?>
	function nav(x,y) {
		var scroll = document.getElementsByName("mapscroll")[0].value;
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
	<?php 
}
?>
