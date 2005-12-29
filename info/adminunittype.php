<?php

require_once("../lib.main.php");
require_once("../lib.building.php");
require_once("../lib.army.php");
require_once("../lib.map.php");
require_once("../lib.tableedit.php");

AdminLock();


$armytypes = AF($gArmyType,"name","id");

$moveflags = array();
foreach ($gMovableFlagMainTerrain as $k => $ttype) 
	$moveflags[$k] = "<img src='".g($gTerrainType[$ttype]->gfx)."'>";

$buildings = array();
$buildings = array(0=>" - keines - ");
foreach($gBuildingType as $x) if ($x->special == 0)
	$buildings[$x->id] = "<img src=\"".g($x->gfx)."\" alt=\" $x->name\" title=\" $x->name\" border=0>";
$unittypes = array(0=>" - keine - ");
foreach($gUnitType as $x) if ($x->flags & kUnitFlag_Elite)
	$unittypes[$x->id] = "<img src=\"".g($x->gfx)."\" alt=\" $x->name\" title=\" $x->name\" border=0>";

$form = 
new cTableEditForm("?sid=?&id=$f_id","UnitType $f_id editieren",
	new cTableEditCols(array(
		new cTableEditRows(array(
			new cTableEditTextField("unittype","id",$f_id,"Name","name"),
			new cTableEditTextArea("unittype","id",$f_id,"Beschreibung","descr"),
			new cTableEditTextField("unittype","id",$f_id,"Orderval","orderval"),
			
			new cTableEditTextField("unittype","id",$f_id,"Kosten: Holz","cost_lumber"),
			new cTableEditTextField("unittype","id",$f_id,"Kosten: Stein","cost_stone"),
			new cTableEditTextField("unittype","id",$f_id,"Kosten: Nahrung","cost_food"),
			new cTableEditTextField("unittype","id",$f_id,"Kosten: Metall","cost_metal"),
			new cTableEditTextField("unittype","id",$f_id,"Kosten: Runen","cost_runes"),
			new cTableEditTimeField("unittype","id",$f_id,"Buildtime","buildtime"),
			
			new cTableEditTimeField("unittype","id",$f_id,"Geschwindigkeit","speed"),
			new cTableEditTextField("unittype","id",$f_id,"Gewicht","weight"),
			new cTableEditTextField("unittype","id",$f_id,"Angriff","a"),
			new cTableEditTextField("unittype","id",$f_id,"Verteidigung","v"),
			new cTableEditTextField("unittype","id",$f_id,"Fernkampf","f"),
			new cTableEditTextField("unittype","id",$f_id,"Reichweite","r"),
			new cTableEditTextField("unittype","id",$f_id,"last","last"),
			new cTableEditTextField("unittype","id",$f_id,"Cooldown","cooldown"),
			new cTableEditTextField("unittype","id",$f_id,"Plündern","pillage"),
			
			new cTableEditFlagField("unittype","id",$f_id,"Flags","flags",$gUnitFlagName),
			new cTableEditTextField("unittype","id",$f_id,"Eff. Segeln","eff_sail"),
			new cTableEditTextField("unittype","id",$f_id,"Eff. Übernehmen","eff_capture"),
			new cTableEditTextField("unittype","id",$f_id,"Eff. Seekampf","eff_fightondeck"),
			new cTableEditTextField("unittype","id",$f_id,"Eff. Belagerung","eff_siege"),
			
			new cTableEditTextField("unittype","id",$f_id,"Req: Geb","req_geb"),
			new cTableEditTextField("unittype","id",$f_id,"Req: Tech A","req_tech_a"),
			new cTableEditTextField("unittype","id",$f_id,"Req: Tech V","req_tech_v"),
			new cTableEditTextField("unittype","id",$f_id,"Treasure","treasure"),
			
			new cTableEditIMGUrl("unittype","id",$f_id,"Bild","gfx")
		)),
		new cTableEditRows(array(
			new cTableEditFlagField("unittype","id",$f_id,"Begehbarkeiten","movable_flag",$moveflags),
			new cTableEditRadioField("unittype","id",$f_id,"armytype","armytype",$armytypes),
			new cTableEditRadioField("unittype","id",$f_id,"Elite","elite",$unittypes),
			new cTableEditRadioField("unittype","id",$f_id,"Gebäude","buildingtype",$buildings)
		))
	))
	,"unittype","id",$f_id,Query("listall.php?sid=?")
);


$form->HandleInput();
// regenerate typecache
require_once("../generate_types.php");
require(kTypeCacheFile);

require_once("header.php"); 
$form->Show();
?>
Treasure:  44:1,45:1,46:1,47:1  // lumber,stone,food,metal<br>
<?php
require_once("footer.php"); 

?>
