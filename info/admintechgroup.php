<?php

require_once("../lib.main.php");
require_once("../lib.building.php");
require_once("../lib.army.php");
require_once("../lib.map.php");
require_once("../lib.tableedit.php");

AdminLock();

if (isset($f_killtechgroup)) {
	sql("DELETE FROM `technologytypegroup` WHERE `id` = ".intval($f_id)." LIMIT 1");
	sql("DELETE FROM `technologytype` WHERE `group` = ".intval($f_id));
	
	// regenerate typecache
	require_once("../generate_types.php");
	require(kTypeCacheFile);
	
	require_once("header.php"); 
	echo "GEKILLT..";
	require_once("footer.php"); 
	exit();
}


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
);


$form->HandleInput();
// regenerate typecache
require_once("../generate_types.php");
require(kTypeCacheFile);

require_once("header.php"); 
$form->Show();
echo '<td><a href="'.Query("?sid=?&id=?&killtechgroup=1").'">(delete entry)</a></td>';
require_once("footer.php"); 

?>