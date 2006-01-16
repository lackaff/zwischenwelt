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

$terrains = array(0=>"keines");
foreach($gTerrainType as $x)
	$terrains[$x->id] = "<img src=\"".g($x->gfx)."\" alt=\" $x->name [$x->id]\" title=\" $x->name [$x->id]\" border=0>";
	
$buildings = array(0=>"keines");
foreach($gBuildingType as $x)
	$buildings[$x->id] = "<img src=\"".g($x->gfx)."\" alt=\" $x->name [$x->id]\" title=\" $x->name [$x->id]\" border=0>";


$terrains2 = array();
foreach($gTerrainType as $x)
	$terrains2[$x->id] = "<img src=\"".g($x->gfx)."\" alt=\" $x->name [$x->id]\" title=\" $x->name [$x->id]\" border=0>";
	
$buildings2 = array();
foreach($gBuildingType as $x)
	$buildings2[$x->id] = "<img src=\"".g($x->gfx)."\" alt=\" $x->name [$x->id]\" title=\" $x->name [$x->id]\" border=0>";

$races = array(
	0 => "Alle",
	kRace_Mensch => "Mensch",
	kRace_Gnome => "Gnome"
);
	
$form = new cTableEditForm("?sid=?&id=$f_id","BuildingType $f_id editieren",
	new cTableEditRows(array(
		new cTableEditCols(array(
			new cTableEditRows(array(
				new cTableEditTextField("buildingtype","id",$f_id,"Name","name"),
				new cTableEditTextArea("buildingtype","id",$f_id,"Beschreibung","descr"),
				
				new cTableEditTextField("buildingtype","id",$f_id,"Kosten: Holz","cost_lumber"),
				new cTableEditTextField("buildingtype","id",$f_id,"Kosten: Stein","cost_stone"),
				new cTableEditTextField("buildingtype","id",$f_id,"Kosten: Nahrung","cost_food"),
				new cTableEditTextField("buildingtype","id",$f_id,"Kosten: Metall","cost_metal"),
				new cTableEditTextField("buildingtype","id",$f_id,"Kosten: Runen","cost_runes"),
				
				new cTableEditTextField("buildingtype","id",$f_id,"Req: Geb","req_geb"),
				new cTableEditTextField("buildingtype","id",$f_id,"Req: Tech","req_tech"),
				
				new cTableEditTimeField("buildingtype","id",$f_id,"Bauzeit","buildtime"),
				new cTableEditTextField("buildingtype","id",$f_id,"MaxHP","maxhp"),
				new cTableEditTextField("buildingtype","id",$f_id,"BaseMana","basemana"),

				new cTableEditTextField("buildingtype","id",$f_id,"Script","script"),
				
				new cTableEditColorTextField("buildingtype","id",$f_id,"Farbe","color"),
				new cTableEditTextField("buildingtype","id",$f_id,"Letter","letter"),
				new cTableEditColorTextField("buildingtype","id",$f_id,"LetterFarbe","lettercolor"),

				new cTableEditTextField("buildingtype","id",$f_id,"Geschwindigkeit","speed"),
				
				new cTableEditIMGUrl("buildingtype","id",$f_id,"Bild","gfx"),
				new cTableEditTextField("buildingtype","id",$f_id,"CSS Klasse","cssclass"),
				
				new cTableEditTextField("buildingtype","id",$f_id,"OrderVal","orderval"),
				
				new cTableEditCheckedField("buildingtype","id",$f_id,"special","special"),
				new cTableEditCheckedField("buildingtype","id",$f_id,"Rahmen","border"),
				new cTableEditCheckedField("buildingtype","id",$f_id,"override terrain movable","movable_override_terrain"),
				new cTableEditTextField("buildingtype","id",$f_id,"Mod A","mod_a"),
				new cTableEditTextField("buildingtype","id",$f_id,"Mod V","mod_v"),
				new cTableEditTextField("buildingtype","id",$f_id,"Mod F","mod_f"),
				
				new cTableEditRadioField("buildingtype","id",$f_id,"Rasse","race",$races)
			)),
			new cTableEditRows(array(
				new cTableEditFlagField("buildingtype","id",$f_id,"Begehbarkeiten","movable_flag",$flags),
				new cTableEditFlagField("buildingtype","id",$f_id,"Flags","flags",$gBuildingTypeFlagNames),
			)),
		)),
		new cTableEditCols(array(
			new cTableEditRadioField("buildingtype","id",$f_id,"Runine","ruinbtype",$buildings),
			
			new cTableEditListFlagField("buildingtype","id",$f_id,"Verbindung:<br>Building","connectto_building",$buildings2),
			new cTableEditListFlagField("buildingtype","id",$f_id,"Need near:<br>Building","neednear_building",$buildings2),
			new cTableEditListFlagField("buildingtype","id",$f_id,"Brauche:<br>Building","require_building",$buildings2),
			new cTableEditListFlagField("buildingtype","id",$f_id,"Darf nicht:<br>Building","exclude_building",$buildings2),
			
			new cTableEditRadioField("buildingtype","id",$f_id,"benötigter<br>Untergrund","terrain_needed",$terrains),
			new cTableEditRadioField("buildingtype","id",$f_id,"wird bei<br>Fertigstellung<br>zu Terrain","convert_into_terrain",$terrains),
			new cTableEditListFlagField("buildingtype","id",$f_id,"Verbindung:<br>Terrain","connectto_terrain",$terrains2),
		)),
	))
	,"buildingtype","id",$f_id,Query("listall.php?sid=?")
);


$form->HandleInput();
// regenerate typecache
RegenTypeCache();
require(kTypeCacheFile);

require_once("header.php"); 
$form->Show();
require_once("footer.php"); 

?>
