<?php

require_once("../lib.main.php");
require_once("../lib.building.php");
require_once("../lib.army.php");
require_once("../lib.map.php");
require_once("../lib.tableedit.php");

$ai_types = array(0=>"stupid creep-spot",1=>"raider-base(type2=siege)",2=>"wandering horde(type2=core/boss)",3=>"ant-hole(type2=antking)");

AdminLock();

$monsters = array(0=>"random");
$type2_possible = array();
foreach ($gUnitType as $o) if (intval($o->flags) & kUnitFlag_Monster) $monsters[$o->id] = "<img src='".g($o->gfx)."'>";
foreach ($gUnitType as $o) $type2_possible[$o->id] = "<img src='".g($o->gfx)."'>";
	
$form = new cTableEditForm("?sid=?&id=$f_id","Hellhole $f_id editieren",
	new cTableEditCols(array(
		new cTableEditRows(array(
			new cTableEditTextField("hellhole","id",$f_id,"x","x"),
			new cTableEditTextField("hellhole","id",$f_id,"y","y"),
			new cTableEditTextField("hellhole","id",$f_id,"type","type"),
			new cTableEditRadioField("hellhole","id",$f_id,"ai_type","ai_type",$ai_types),
			new cTableEditTextField("hellhole","id",$f_id,"ai_data","ai_data"),
			new cTableEditTextField("hellhole","id",$f_id,"lastupgrade","lastupgrade"),
			new cTableEditTextField("hellhole","id",$f_id,"level","level"),
			new cTableEditTextField("hellhole","id",$f_id,"maxlevel","maxlevel"),
			new cTableEditTextField("hellhole","id",$f_id,"armysize","armysize"),
			new cTableEditTextField("hellhole","id",$f_id,"armysize2","armysize2"),
			new cTableEditTextField("hellhole","id",$f_id,"num","num"),
			new cTableEditTimeField("hellhole","id",$f_id,"spawndelay","spawndelay"),
			new cTableEditTextField("hellhole","id",$f_id,"spawntime","spawntime"),
			new cTableEditTextField("hellhole","id",$f_id,"totalspawns","totalspawns"),
			new cTableEditTextField("hellhole","id",$f_id,"radius","radius"),
		)),
		new cTableEditRadioField("hellhole","id",$f_id,"Type","type",$monsters),
		new cTableEditRadioField("hellhole","id",$f_id,"Type2","type2",$type2_possible),

	))
);


$form->HandleInput();
// regenerate typecache
RegenTypeCache();
require(kTypeCacheFile);

require_once("header.php"); 
$form->Show();
require_once("footer.php"); 

?>