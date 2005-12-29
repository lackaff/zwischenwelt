<?php

require_once("../lib.main.php");
require_once("../lib.building.php");
require_once("../lib.army.php");
require_once("../lib.map.php");
require_once("../lib.tableedit.php");

AdminLock();

$buildings = array();
$build = sqlgettable("SELECT `id`,`name` FROM `buildingtype` WHERE `special`=0");
foreach ($build as $b) {
	$buildings[$b->id]=$b->name;
}



$form = 
new cTableEditForm("?sid=?&id=$f_id","technologygroup $f_id editieren",
	new cTableEditCols(array(
		new cTableEditRows(array(
			new cTableEditTextField("technologygroup","id",$f_id,"Name","name"),
			new cTableEditTextArea("technologygroup","id",$f_id,"Beschreibung","descr"),
			new cTableEditIMGUrl("technologygroup","id",$f_id,"Bild","gfx")
		)),
		new cTableEditRows(array(
			new cTableEditRadioField("technologygroup","id",$f_id,"Gebäude","buildingtype",$buildings)
			
		))
	))
	,"technologygroup","id",$f_id,Query("listall.php?sid=?")
);


$form->HandleInput();
// regenerate typecache
require_once("../generate_types.php");
require(kTypeCacheFile);

require_once("header.php"); 
$form->Show();
require_once("footer.php"); 

?>
