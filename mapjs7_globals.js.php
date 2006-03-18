<?php
require_once("lib.main.php");
//if ( extension_loaded('zlib') )ob_start('ob_gzhandler');
header('Content-Type: text/javascript');
$maxage = 60*60*24*7;
header('Last-Modified: '.date("r",floor((time()-$maxage)/$maxage)*$maxage));
header('Cache-Control: max-age='.$maxage.', must-revalidate');

?>
kCoreJSMapVersion = <?=intval(kJSMapVersion)+intval($gGlobal["typecache_version_adder"])?>;
kDefaultTerrainID = <?=kTerrain_Grass?>;
kNWSE_N = <?=kNWSE_N?>;
kNWSE_W = <?=kNWSE_W?>;
kNWSE_S = <?=kNWSE_S?>;
kNWSE_E = <?=kNWSE_E?>;
kConstructionPlanPic = "<?=kConstructionPlanPic?>";
kConstructionPic = "<?=kConstructionPic?>";
kTransCP = "<?=kTransCP?>"; // transparent construction plan
kBASEURL = "<?=BASEURL?>";

kJSMapBuildingFlag_Open				= <?=kJSMapBuildingFlag_Open?>;
kJSMapBuildingFlag_Tax				= <?=kJSMapBuildingFlag_Tax?>;
kJSMapBuildingFlag_Locked			= <?=kJSMapBuildingFlag_Locked?>;
kJSMapBuildingFlag_BeingSieged		= <?=kJSMapBuildingFlag_BeingSieged?>;
kJSMapBuildingFlag_BeingPillaged	= <?=kJSMapBuildingFlag_BeingPillaged?>;
kJSMapBuildingFlag_Shooting			= <?=kJSMapBuildingFlag_Shooting?>;
kJSMapBuildingFlag_BeingShot		= <?=kJSMapBuildingFlag_BeingShot?>;

kJSMapArmyFlag_Controllable	= <?=kJSMapArmyFlag_Controllable?>;
kJSMapArmyFlag_GC			= <?=kJSMapArmyFlag_GC?>;
kJSMapArmyFlag_Fighting		= <?=kJSMapArmyFlag_Fighting?>;
kJSMapArmyFlag_Sieging		= <?=kJSMapArmyFlag_Sieging?>;
kJSMapArmyFlag_Pillaging	= <?=kJSMapArmyFlag_Pillaging?>;
kJSMapArmyFlag_Shooting		= <?=kJSMapArmyFlag_Shooting?>;
kJSMapArmyFlag_BeingShot	= <?=kJSMapArmyFlag_BeingShot?>;

<?php if (0) {?>
kJSMapArmyFlag_Moving_N = <?=kJSMapArmyFlag_Moving_N?>;
kJSMapArmyFlag_Moving_W = <?=kJSMapArmyFlag_Moving_W?>;
kJSMapArmyFlag_Moving_S = <?=kJSMapArmyFlag_Moving_S?>;
kJSMapArmyFlag_Moving_E = <?=kJSMapArmyFlag_Moving_E?>;
kJSMapArmyFlag_Fighting_N = <?=kJSMapArmyFlag_Fighting_N?>;
kJSMapArmyFlag_Fighting_W = <?=kJSMapArmyFlag_Fighting_W?>;
kJSMapArmyFlag_Fighting_S = <?=kJSMapArmyFlag_Fighting_S?>;
kJSMapArmyFlag_Fighting_E = <?=kJSMapArmyFlag_Fighting_E?>;
<?php } // endif?>

kBuildingTypeFlag_BuildDistSource = <?=kBuildingTypeFlag_BuildDistSource?>;
kBuildingTypeFlag_Speedy = <?=kBuildingTypeFlag_Speedy?>;
kBuildingTypeFlag_Openable = <?=kBuildingTypeFlag_Openable?>;
kBuildingTypeFlag_Taxable = <?=kBuildingTypeFlag_Taxable?>;
kBuildingTypeFlag_CanShootArmy = <?=kBuildingTypeFlag_CanShootArmy?>;
kBuildingTypeFlag_CanShootBuilding = <?=kBuildingTypeFlag_CanShootBuilding?>;
kBuildingTypeFlag_OthersCanSeeUnits = <?=kBuildingTypeFlag_OthersCanSeeUnits?>;
kBuildingTypeFlag_DrawMaxTypeOnTop = <?=kBuildingTypeFlag_DrawMaxTypeOnTop?>;
kBuildingTypeFlag_Bodenschatz = <?=kBuildingTypeFlag_Bodenschatz?>;

gMapModiHelp = "<?=addslashes(cText::Wiki("MapModi"))?>";

//kJSMapTileSize = <?=kMapTileSize?>;
kJSMapTileSize = 27;
kJSForceIESpaceCX = kJSMapTileSize;
kJSForceIESpaceCY = kJSMapTileSize;


// equals the php function GetBuildDistFactor in lib.construction.php
function GetBuildDistFactor (dist) {
	if (dist <= 4.0)
			return 1.0;
	else	return 1.0  + (dist-4.0) * 0.1;
}

// equals the php function GetNextStep in lib.main.php
function GetNextStep (x,y,x1,y1,x2,y2) {
	if (x == x2 && y == y2) return new Array(x,y); // already arrived
	var minx,maxx,backx,xdif,line_x1,line_x2;
	var miny,maxy,backy,ydif,line_y1,line_y2;
	
	// find back on track, should not happen
	if (x1 < x2) 
			{ minx = x1;maxx = x2; } 
	else	{ minx = x2;maxx = x1; }
	if (y1 < y2) 
			{ miny = y1;maxy = y2; } 
	else	{ miny = y2;maxy = y1; }
	backx = (x < minx) ? (minx - x) : ( (x > maxx) ? (maxx - x) : 0 );
	backy = (y < miny) ? (miny - y) : ( (y > maxy) ? (maxy - y) : 0 );
	if (backx != 0 || backy != 0) {
		if (Math.abs(backx) > Math.abs(backy)) 
				return new Array(x+((backx>0)?1:-1),y);
		else	return new Array(x,y+((backy>0)?1:-1));
	}
	
	// waylength zero
	if (x1 == x2 && y1 == y2) return new Array(x1,y1);
	
	xdif = x2-x1;
	ydif = y2-y1;
	if (Math.abs(xdif) >= Math.abs(ydif)) {
		// horizontal movement
		line_y1 = (y1+((x-0.5-x1)/xdif)*ydif);
		line_y2 = (y1+((x+0.5-x1)/xdif)*ydif);
		miny = Math.round(Math.min(line_y1,line_y2));
		maxy = Math.round(Math.max(line_y1,line_y2));
		
		if (ydif > 0 && y < maxy) return new Array(x,y+1); // move verti
		if (ydif < 0 && y > miny) return new Array(x,y-1); // move verti
		return new Array(x+((xdif > 0)?1:-1),y); // move hori
	} else {
		// vertical movement
		line_x1 = (x1+((y-0.5-y1)/ydif)*xdif);
		line_x2 = (x1+((y+0.5-y1)/ydif)*xdif);
		minx = Math.round(Math.min(line_x1,line_x2));
		maxx = Math.round(Math.max(line_x1,line_x2));
		
		if (xdif > 0 && x < maxx) return new Array(x+1,y); // move hori
		if (xdif < 0 && x > minx) return new Array(x-1,y); // move hori
		return new Array(x,y+((ydif > 0)?1:-1)); // move verti
	}
}

// equals the php function GetUnitsMovableMask in lib.unit.php
function GetUnitsMovableMask (units) {
	var mask = 0;
	var maskset = false;
	for (i in units) if (units[i] >= 1 && gUnitType[i]) {
		var flag = parseInt(gUnitType[i].movable_flag);
		mask = (maskset)?(mask & flag):(flag);
		maskset = true;
	}
	return mask;
}

// equals the php function GetPosSpeed in lib.main.php
function GetPosSpeed (relx,rely,movablemask,skiparmyid) {
	// check army
	var army = SearchPos(gArmies,relx,rely);
	if (army && army.id != skiparmyid) return 0;
	
	// vars
	var override = true;
	var b_speed = -1;
	var t_speed = -1;
	
	// check building
	var building = GetBuilding(relx,rely);
	if(building && gBuildingType[building.type]) {
		// is open for user?
		b_speed = (parseInt(building.jsflags) & kJSMapBuildingFlag_Open) ? gBuildingType[building.type].speed : 0;
		override = gBuildingType[building.type].movable_override_terrain == 1;
		if ((movablemask & parseInt(gBuildingType[building.type].movable_flag)) == 0) {
			b_speed = 0;
		}
	} else building = false;
	
	// check terrain
	var terraintype = GetTerrainType(relx,rely);
	if (gTerrainType[terraintype]) {
		t_speed = gTerrainType[terraintype].speed;
		// check movable
		if ((movablemask & parseInt(gTerrainType[terraintype].movable_flag)) == 0) {
			t_speed = 0;
		}
	} else t_speed = 0;
	
	//check if building movable overrides terrain
	if (building && override) {
		//only building counts, terrain will be ignored
		speed = b_speed;
	} else if(building){
		//building and terrain, no override
		speed = Math.max(t_speed,b_speed);
	} else {
		//only terrain
		speed = t_speed;
	}

	return speed;
}

// HACK: (hyperblob)
function UnitTypeHasNWSE (unittype) {
	return unittype == <?=kUnitType_HyperBlob?>;
}

// HACK: hardcode a few connect-to-building entries
function HackCon () {
	<?php 
	function js_print_legacy_push($arrname,$val) {
		// internet explorer 5.0 doesn't know push/pop
		echo $arrname."[".$arrname.".length] = ".$val.";\n";
	}
	?>
	var i;
	for (i in 	gBuildingType[<?=kBuilding_Gate?>].connectto_building)
		if (	gBuildingType[<?=kBuilding_Gate?>].connectto_building[i] == <?=kBuilding_Gate?>)
				gBuildingType[<?=kBuilding_Gate?>].connectto_building[i] = -2;
	for (i in 	gBuildingType[<?=kBuilding_SeaGate?>].connectto_building)
		if (	gBuildingType[<?=kBuilding_SeaGate?>].connectto_building[i] == <?=kBuilding_SeaGate?>)
				gBuildingType[<?=kBuilding_SeaGate?>].connectto_building[i] = -2;
	<?php 
	js_print_legacy_push("gBuildingType[".kBuilding_Gate		."].connectto_building",kBuilding_Wall);
	js_print_legacy_push("gBuildingType[".kBuilding_Wall		."].connectto_building",kBuilding_Gate);
	js_print_legacy_push("gBuildingType[".kBuilding_Path		."].connectto_building",kBuilding_Gate);
	js_print_legacy_push("gBuildingType[".kBuilding_GB		."].connectto_building",kBuilding_Path);
	js_print_legacy_push("gBuildingType[".kBuilding_Path		."].connectto_building",kBuilding_GB);
	js_print_legacy_push("gBuildingType[".kBuilding_Bridge	."].connectto_building",kBuilding_Path);
	js_print_legacy_push("gBuildingType[".kBuilding_Path		."].connectto_building",kBuilding_Bridge);
	js_print_legacy_push("gBuildingType[".kBuilding_SeaWall	."].connectto_building",kBuilding_SeaGate);
	js_print_legacy_push("gBuildingType[".kBuilding_SeaGate	."].connectto_building",kBuilding_SeaWall);
	js_print_legacy_push("gBuildingType[".kBuilding_SeaWall	."].connectto_building",kBuilding_Wall);
	js_print_legacy_push("gBuildingType[".kBuilding_Wall	."].connectto_building",kBuilding_SeaWall);
	js_print_legacy_push("gBuildingType[".kBuilding_SeaGate	."].connectto_building",kBuilding_Wall);
	js_print_legacy_push("gBuildingType[".kBuilding_Wall	."].connectto_building",kBuilding_SeaGate);
	
	js_print_legacy_push("gBuildingType[".kBuilding_Steg		."].connectto_building",kBuilding_Harbor);
	js_print_legacy_push("gBuildingType[".kBuilding_Harbor	."].connectto_terrain",kTerrain_Sea);
	js_print_legacy_push("gTerrainType[".kTerrain_Sea		."].connectto_building",kBuilding_Harbor);
	?>
}

// give water a blue background(=gridlines), green lines on water suck !
function HackBackgroundColor (relx,rely) {
	var terraintype = GetTerrainType(relx,rely);
	var nwsecode = gTerrainMap_raw[rely+1][relx+1].nwse;
	var nwseadcount = 0; // adjacted nwse
	if ((nwsecode & 3) == 3) ++nwseadcount;
	if ((nwsecode & 9) == 9) ++nwseadcount;
	if ((nwsecode & 6) == 6) ++nwseadcount;
	if ((nwsecode & 12) == 12) ++nwseadcount;
	var nwsecount = (nwsecode & 1) + ((nwsecode & 2)/2) + ((nwsecode & 4)/4) + ((nwsecode & 8)/8);
	var terraintype = GetTerrainType(relx,rely);
	//if (terraintype == <?=kTerrain_River?>) return "#0000ff";
	if (terraintype == <?=kTerrain_Sea?> && nwseadcount >= 1) return "#3060E0";
	if (terraintype == <?=kTerrain_DeepSea?>) return "#284cbb";
	if (terraintype == <?=kTerrain_Swamp?>) return "#419db5";
	//if (terraintype == <?=kTerrain_Swamp?>) return "#0000ff";
	/*
	define("kTerrain_Grass",1);
	define("kTerrain_River",2);
	define("kTerrain_Mountain",3);
	define("kTerrain_Hole",5);
	define("kTerrain_Sea",6);
	define("kTerrain_Forest",4);
	define("kTerrain_Field",8);
	define("kTerrain_Flowers",10);
	define("kTerrain_Rubble",11);
	define("kTerrain_TreeStumps",12);
	define("kTerrain_YoungForest",13);
	define("kTerrain_SnowyMountain",15);
	define("kTerrain_Swamp",16);
	define("kTerrain_Oasis",9);
	define("kTerrain_Desert",7);
	define("kTerrain_DeepSea",18);
	*/
	return false;
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
		var i,arr;
		<?=$globalarr?> = <?=$globalarr?>.split("<?=addslashes($sep_obj)?>");	
		<?=$globalarr?>.length=<?=$globalarr?>.length-1;
		for (i in <?=$globalarr?>) if (<?=$globalarr?>[i] == "") {
			<?=$globalarr?>[i] = false;
		} else {
			arr = <?=$globalarr?>[i].split("<?=addslashes($sep_val)?>");
			var res = new Object();
			<?php $i = 0; foreach ($fields as $field) echo "res.".$field." = arr[".($i++)."];";?>
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

php2js_objarray("gTerrainType",$gTerrainType,"name,speed,buildable,gfx,mod_a,mod_v,mod_f,movable_flag,connectto_terrain,connectto_building,maxrandcenter,maxrandborder");
php2js_objarray("gBuildingType",$gBuildingType,"name,maxhp,speed,gfx,flags,mod_a,mod_v,mod_f,connectto_terrain,connectto_building,neednear_building,require_building,exclude_building,border,movable_flag,movable_override_terrain,buildingtype,maxgfxlevel,maxrandcenter,maxrandborder");
php2js_objarray("gUnitType",$gUnitType,"name,orderval,a,v,f,r,speed,gfx,movable_flag");
php2js_objarray("gItemType",$gItemType,"name,gfx");
php2js_objarray("gTerrainPatchType",$gTerrainPatchType,"id,gfx,here,up,down,left,right");
// bodenschaetze (ressources,perks,specials,deposit...)

php2js_objectfunction("jsUser","id,guild,color,name,race,moral","gUsers","id");
php2js_objectfunction("jsArmy","id,x,y,name,type,user,unitstxt,itemstxt,jsflags,wpstxt,lastwpx,lastwpy,wpmaxprio,fill_limit,fill_last","gArmies","id");
// php2js_parser("jsParseBuildings","x,y,type,user,level,hp,construction,jsflags","gBuildings"); // special for speed
php2js_parser("jsParseItems","x,y,type,amount","gItems");
php2js_parser("jsParsePlans","x,y,type,priority","gPlans");
php2js_parser("jsParseBuildSources","x,y","gBuildSources");
//php2js_parser("jsWPs","x,y","gWPs");



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
	<?php 
}
?>
