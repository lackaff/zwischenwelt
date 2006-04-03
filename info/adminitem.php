<?php

require_once("../lib.main.php");
require_once("../lib.building.php");
require_once("../lib.army.php");
require_once("../lib.map.php");
require_once("../lib.tableedit.php");

AdminLock();

$form = 
new cTableEditForm("?sid=?&id=$f_id","ItemType $f_id editieren",
	new cTableEditCols(array(
		new cTableEditRows(array(
			new cTableEditTextField("itemtype","id",$f_id,"Name","name"),
			new cTableEditTextArea("itemtype","id",$f_id,"Beschreibung","descr"),
			new cTableEditIMGUrl("itemtype","id",$f_id,"Bild","gfx"),
			new cTableEditTextField("itemtype","id",$f_id,"Gewicht","weight"),
			new cTableEditTextField("itemtype","id",$f_id,"Max. Anzahl","maxamount"),
			new cTableEditTextField("itemtype","id",$f_id,"gammeltype","gammeltype"),
			new cTableEditTextField("itemtype","id",$f_id,"gammeltime","gammeltime"),
			new cTableEditTextField("itemtype","id",$f_id,"Wert","value"),
			new cTableEditTextField("itemtype","id",$f_id,"Gebäude","buildings"),
			
			new cTableEditTextField("itemtype","id",$f_id,"Kosten: Holz","cost_lumber"),
			new cTableEditTextField("itemtype","id",$f_id,"Kosten: Stein","cost_stone"),
			new cTableEditTextField("itemtype","id",$f_id,"Kosten: Nahrung","cost_food"),
			new cTableEditTextField("itemtype","id",$f_id,"Kosten: Metall","cost_metal"),
			new cTableEditTextField("itemtype","id",$f_id,"Kosten: Runen","cost_runes"),
						
			new cTableEditFlagField("itemtype","id",$f_id,"Flags","flags",$gItemFlagNames),
			
		)),
	))
	,"itemtype","id",$f_id,Query("listall.php?sid=?")
);


$form->HandleInput();
// regenerate typecache
RegenTypeCache();
require(kTypeCacheFile);

require_once("header.php"); 
$form->Show();
?>
Treasure:  44:1,45:1,46:1,47:1  // lumber,stone,food,metal<br>
<?php
require_once("footer.php"); 

?>
