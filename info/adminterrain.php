<?php

require_once("../lib.main.php");
require_once("../lib.building.php");
require_once("../lib.army.php");
require_once("../lib.map.php");
require_once("../lib.tableedit.php");

AdminLock();

$flags = array(
	kTerrain_Flag_Moveable_Land => "Land",
	kTerrain_Flag_Moveable_Wood => "Wald",
	kTerrain_Flag_Moveable_Mountain => "Gebirge",
	kTerrain_Flag_Moveable_River => "Fluss",
	kTerrain_Flag_Moveable_Sea => "See",
	kTerrain_Flag_Moveable_DeepSea => "tiefe See"
);

$connectto_terrain = array();
foreach($gTerrainType as $x)
	$connectto_terrain[$x->id] = "<img src=\"".g($x->gfx)."\" alt=\" $x->name\" title=\" $x->name\" border=0>";
$connectto_building = array();
foreach($gBuildingType as $x)
	$connectto_building[$x->id] = "<img src=\"".g($x->gfx)."\" alt=\" $x->name\" title=\" $x->name\" border=0>";
	
$form = new cTableEditForm("?sid=?&id=$f_id","TerrainType $f_id editieren",
	new cTableEditCols(array(
		new cTableEditRows(array(
			new cTableEditTextField("terraintype","id",$f_id,"Name","name"),
			new cTableEditTextArea("terraintype","id",$f_id,"Beschreibung","descr"),
			new cTableEditTextField("terraintype","id",$f_id,"Geschwindigkeit","speed"),
			new cTableEditCheckedField("terraintype","id",$f_id,"Bebaubar?","buildable"),
			new cTableEditColorTextField("terraintype","id",$f_id,"Farbe","color"),
			new cTableEditIMGUrl("terraintype","id",$f_id,"Bild","gfx"),
			new cTableEditTextField("terraintype","id",$f_id,"CSS Klasse","cssclass"),
			new cTableEditTextField("terraintype","id",$f_id,"Mod A","mod_a"),
			new cTableEditTextField("terraintype","id",$f_id,"Mod V","mod_v"),
			new cTableEditTextField("terraintype","id",$f_id,"Mod F","mod_f"),
			new cTableEditFlagField("terraintype","id",$f_id,"Begehbarkeiten","movable_flag",$flags)
		)),
		new cTableEditListFlagField("terraintype","id",$f_id,"verbindet sich mit Terrain","connectto_terrain",$connectto_terrain),
		new cTableEditListFlagField("terraintype","id",$f_id,"verbindet sich mit Building","connectto_building",$connectto_building)
	))
	,"terraintype","id",$f_id,Query("listall.php?sid=?")
);


$form->HandleInput();
// regenerate typecache
require_once("../generate_types.php");
require(kTypeCacheFile);

require_once("header.php"); 
$form->Show();
require_once("footer.php"); 

?>
