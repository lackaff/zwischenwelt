<?php

require_once("../lib.main.php");
require_once("../lib.building.php");
require_once("../lib.army.php");
require_once("../lib.map.php");
require_once("../lib.tableedit.php");

AdminLock();


$terrains = array(0=>"keines");
foreach($gTerrainType as $x)
	$terrains[$x->id] = "<img src=\"".g($x->gfx)."\" alt=\" $x->name [$x->id]\" title=\" $x->name [$x->id]\" border=0>";
	
$buildings = array(0=>"keines");
foreach($gBuildingType as $x)
	$buildings[$x->id] = "<img src=\"".g($x->gfx)."\" alt=\" $x->name [$x->id]\" title=\" $x->name [$x->id]\" border=0>";
	
$form = new cTableEditForm("?sid=?&id=$f_id","Building $f_id editieren",
	new cTableEditCols(array(
		new cTableEditRows(array(
			new cTableEditTextField("building","id",$f_id,"x","x"),
			new cTableEditTextField("building","id",$f_id,"y","y"),
			new cTableEditTextField("building","id",$f_id,"Owner","user"),
			new cTableEditTextField("building","id",$f_id,"Level","level"),
			new cTableEditTextField("building","id",$f_id,"Upgrades","upgrades"),
			new cTableEditTextField("building","id",$f_id,"UprTime","upgradetime"),
			new cTableEditTextField("building","id",$f_id,"HP","hp"),
			new cTableEditTextField("building","id",$f_id,"Mana","mana"),
			new cTableEditTextField("building","id",$f_id,"Baustelle","construction"),
		)),
		new cTableEditRadioField("building","id",$f_id,"Type","type",$buildings)

	))
);


$form->HandleInput();
// regenerate typecache

require_once("header.php"); 
$form->Show();
require_once("footer.php"); 

?>