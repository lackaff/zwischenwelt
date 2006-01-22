<?php

require_once("../lib.main.php");
require_once("../lib.building.php");
require_once("../lib.army.php");
require_once("../lib.map.php");
require_once("../lib.tableedit.php");

AdminLock();

$userrecord = sqlgetone("SELECT `userid` FROM `userrecord` WHERE `userid`=".intval($f_id));
if(!$userrecord)sql("INSERT INTO `userrecord` SET `userid`=".intval($f_id));

$flags = array(
	kUserFlags_TerraFormer => "TerraFormer",
	kUserFlags_DropDownMenu => "DropDownMenu",
	kUserFlags_BugOperator => "BugOperator",
	kUserFlags_ShowLogFrame => "ShowLogFrame",
	kUserFlags_DontShowWikiHelp => "DontShowWikiHelp",
	kUserFlags_AutomaticUpgradeBuildingTo => "AutoUpgradeBuildingTo"
);

$races = array(
	kRace_Mensch => "Mensch",
	kRace_Gnome => "Gnome"
);

$form = new cTableEditForm("?sid=?&id=$f_id","user $f_id editieren",
	new cTableEditCols(array(
		new cTableEditRows(array(
			new cTableEditTextField("user","id",$f_id,"Name","name"),
			new cTableEditTextField("user","id",$f_id,"Passwort","pass"),
			new cTableEditTextField("user","id",$f_id,"Mail","mail"),
			new cTableEditTextField("user","id",$f_id,"Homepage","homepage"),
			new cTableEditCheckedField("user","id",$f_id,"Admin?","admin"),
			new cTableEditCheckedField("user","id",$f_id,"IPLock?","iplock"),
			new cTableEditTextField("user","id",$f_id,"GFXPath","gfxpath"),
			new cTableEditCheckedField("user","id",$f_id,"Use GFXPath?","usegfxpath"),
			new cTableEditFlagField("user","id",$f_id,"Flags","flags",$flags),
			new cTableEditRadioField("user","id",$f_id,"Rasse","race",$races),
			new cTableEditTextField("user","id",$f_id,"Pop","pop"),
			new cTableEditTextField("user","id",$f_id,"GP","guildpoints"),
			new cTableEditTextField("user","id",$f_id,"Gesinnung","moral"),
		)),
		new cTableEditRows(array(
			new cTableEditTextArea("userrecord","userid",$f_id,"Admin Akte","text",50,20),
			new cTableEditTextField("user","id",$f_id,"Holz","lumber"),
			new cTableEditTextField("user","id",$f_id,"Stein","stone"),
			new cTableEditTextField("user","id",$f_id,"Nahrung","food"),
			new cTableEditTextField("user","id",$f_id,"Metall","metal"),
			new cTableEditTextField("user","id",$f_id,"Runen","runes"),
		)),
	))
);


$form->HandleInput();

require_once("header.php"); 
$form->Show();
require_once("footer.php"); 

?>
