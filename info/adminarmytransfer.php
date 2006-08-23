<?php

require_once("../lib.main.php");
require_once("../lib.building.php");
require_once("../lib.army.php");
require_once("../lib.map.php");
require_once("../lib.tableedit.php");
require_once("../lib.transfer.php");

AdminLock();

if (isset($f_new)) { sql("INSERT INTO `armytransfer` SET `name` = 'neuerTransfer'"); $f_id = mysql_insert_id(); }

$armytypes = AF($gArmyType,"name","id");
$buildings = array();
foreach($gBuildingType as $x) if ($x->special == 0)
	$buildings[$x->id] = "<img src=\"".g($x->gfx)."\" alt=\" $x->name\" title=\" $x->name\" border=0>";
$unittypes = array();
foreach($gUnitType as $x) if (!($x->flags & kUnitFlag_Monster))
	$unittypes[$x->id] = "<img src=\"".g($x->gfx)."\" alt=\" $x->name\" title=\" $x->name\" border=0>";
	
$form = new cTableEditForm("?sid=?&id=$f_id","armeetransfer $f_id editieren",
	new cTableEditRows(array(
		new cTableEditRows(array(
			new cTableEditTextField("armytransfer","id",$f_id,"name","name"),
			new cTableEditCheckedField("armytransfer","id",$f_id,"sourcetransport?","sourcetransport"),
			new cTableEditTextField("armytransfer","id",$f_id,"idlemod","idlemod"),
		)),
		new cTableEditCols(array(
			new cTableEditRows(array(
				new cTableEditRadioField("armytransfer","id",$f_id,"sourceB","sourcebuildingtype",array_merge2(array(0=>"-"),$buildings))
			)),
			new cTableEditRows(array(
				new cTableEditRadioField("armytransfer","id",$f_id,"sourceA","sourcearmytype",array_merge2(array(0=>"-"),$armytypes))
			)),
			new cTableEditRows(array(
				new cTableEditRadioField("armytransfer","id",$f_id,"target","targetarmytype",$armytypes)
			)),
			new cTableEditRows(array(
				new cTableEditRadioField("armytransfer","id",$f_id,"targetsub","transportarmytype",array_merge2(array(0=>"--main--"),$armytypes))
			)),
			new cTableEditRows(array(
				new cTableEditRadioField("armytransfer","id",$f_id,"btyp","unitsbuildingtype",array_merge2(array(0=>"alle"),$buildings))
			)),
			new cTableEditRows(array(
				new cTableEditRadioField("armytransfer","id",$f_id,"auslader","transportertype",array_merge2(array(0=>"-"),$unittypes))
			)),
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
?>
<table>
<tr><th>sourceB/sourceA</th><td>nur eins von beiden darf gesetzt sein</td></tr>
<tr><th>sourcetransport AUS</th><td>verwende die haupt-einheiten in sourcearmy</td></tr>
<tr><th>sourcetransport AN</th><td>verwende die transportierten-einheiten in sourcearmy</td></tr>
<tr><th>sourcetransport</th><td>bedeutungslos für sourceB</td></tr>
<tr><th>target</th><td>transfer geht in diesen armee-typ</td></tr>
<tr><th>targetsub --main--</th><td>transfer geht in die haupt-einheiten in targetarmy</td></tr>
<tr><th>targetsub sonst</th><td>transfer geht in die transportierten-einheiten in targetarmy</td></tr>
<tr><th>targetsub</th><td>es werden nur einheiten transferriert, die diesem armeetyp entsprechen</td></tr>
<tr><th>btyp</th><td>es werden nur einheiten transferriert, die man in diesem gebäudetyp bauen kann</td></tr>
<tr><th>idlemod=0,auslader AUS</th><td>transfer ist zu jeder zeit möglich</td></tr>
<tr><th>idlemod=3,auslader AUS</th><td>transfer ist nur möglich wenn die beteiligten armeen mindestens 3 minuten idletime haben</td></tr>
<tr><th>idlemod=30,auslader=landungsboot</th><td>für alle 30 minuten idletime kann einmal die komplette ladekapazität aller landungsboote transferriert werden</td></tr>
<tr><th></th><td></td></tr>
</table>
<?php
require_once("footer.php"); 

?>