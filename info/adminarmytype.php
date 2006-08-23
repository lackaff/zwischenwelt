<?php

require_once("../lib.main.php");
require_once("../lib.building.php");
require_once("../lib.army.php");
require_once("../lib.map.php");
require_once("../lib.tableedit.php");
require_once("../lib.transfer.php");

AdminLock();

if (isset($f_new)) { sql("INSERT INTO `armytype` SET `name` = 'neuerTyp'"); $f_id = mysql_insert_id(); }


$form = new cTableEditForm("?sid=?&id=$f_id","armeetyp $f_id editieren",
	new cTableEditCols(array(
		new cTableEditRows(array(
			new cTableEditTextField("armytype","id",$f_id,"Name","name"),
			new cTableEditTextField("armytype","id",$f_id,"Limit-Anzahl","limit"),
			new cTableEditTextField("armytype","id",$f_id,"Limit-Gewicht","weightlimit"),
			new cTableEditTextField("armytype","id",$f_id,"AddTechs(tech:+erhöhung)","addtechs"),
			new cTableEditTextField("armytype","id",$f_id,"SubTechs(tech:+senkung)","subtechs"),
			new cTableEditFlagField("armytype","id",$f_id,"Flags, die der Spieler einstellen kann","ownerflags",$gArmyFlagNames),
			//new cTableEditFlagField("armytype","id",$f_id,"TypeFlags","flags",$gArmyTypeFlagNames),
		)),
	))
);

$form->HandleInput();
// regenerate typecache
RegenTypeCache();
require(kTypeCacheFile);


require_once("header.php"); 
foreach ($gArmyType as $o) echo "<a href='".Query("adminarmytype.php?sid=?&id=".$o->id)."'>edit armytype ".$o->name."</a><br>";
echo "<a href='".Query("adminarmytype.php?sid=?&new=1")."'>[new armytype]</a><br>";
foreach ($gArmyTransfer as $o) echo "<a href='".Query("adminarmytransfer.php?sid=?&id=".$o->id)."'>edit armytransfer ".cTransfer::GetArmyTransferName($o->id)."</a><br>";
echo "<a href='".Query("adminarmytransfer.php?sid=?&new=1")."'>[new armytransfer]</a><br>";
$form->Show();
echo "SpielerFlags : Diese Flags kann der Spieler für diesen Armeetyp setzen<br>";
require_once("footer.php"); 

?>