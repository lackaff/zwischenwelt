<?php

require_once("../lib.main.php");
require_once("../lib.building.php");
require_once("../lib.army.php");
require_once("../lib.map.php");
require_once("../lib.tableedit.php");
require_once("../lib.transfer.php");

AdminLock();

$form = new cTableEditForm("?sid=?&id=$f_id","armee $f_id editieren",
	new cTableEditCols(array(
		new cTableEditRows(array(
			new cTableEditTextField("army","id",$f_id,"Name","name"),
			new cTableEditTextField("army","id",$f_id,"user","user"),
			new cTableEditRadioField("army","id",$f_id,"type","type",AF($gArmyType,"name","id")),
			new cTableEditCheckedField("army","id",$f_id,"counttolimit","counttolimit"),
			new cTableEditTimeField("army","id",$f_id,"idle","idle"),
			new cTableEditTextField("army","id",$f_id,"frags","frags"),
			new cTableEditTextField("army","id",$f_id,"hellhole","hellhole"),
			new cTableEditTextField("army","id",$f_id,"quest","quest"),
			new cTableEditTextField("army","id",$f_id,"<img src='".g("res_lumber.gif")."'>","lumber"),
			new cTableEditTextField("army","id",$f_id,"<img src='".g("res_stone.gif")."'>","stone"),
			new cTableEditTextField("army","id",$f_id,"<img src='".g("res_food.gif")."'>","food"),
			new cTableEditTextField("army","id",$f_id,"<img src='".g("res_metal.gif")."'>","metal"),
			new cTableEditTextField("army","id",$f_id,"<img src='".g("res_runes.gif")."'>","runes"),
		)),
		
		new cTableEditFlagField("army","id",$f_id,"Flags","flags",$gArmyFlagNames),
	))
);

$form->HandleInput();
// regenerate typecache
RegenTypeCache();
require(kTypeCacheFile);


require_once("header.php"); 
?><a href="<?=Query("adminpathtest.php?sid=?")?>">adminpathtest</a><br><?php
$form->Show();
require_once("footer.php");
?>