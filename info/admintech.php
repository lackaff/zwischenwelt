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

$gg = sqlgettable("SELECT g.`id`,g.`name` FROM `technologygroup` g,`technologytype` t where g.`buildingtype`=t.`buildingtype` AND t.id=".intval($f_id));
$groups=array(0=>"keine");
foreach ($gg as $g)
	$groups[$g->id]=$g->name;
	
$form = 
new cTableEditForm("?sid=?&id=$f_id","TechnologyType $f_id editieren",
	new cTableEditCols(array(
		new cTableEditRows(array(
			new cTableEditTextField("technologytype","id",$f_id,"Name","name"),
			new cTableEditTextArea("technologytype","id",$f_id,"Beschreibung","descr"),
			
			new cTableEditTextField("technologytype","id",$f_id,"Kosten: Holz","basecost_lumber"),
			new cTableEditTextField("technologytype","id",$f_id,"Kosten: Stein","basecost_stone"),
			new cTableEditTextField("technologytype","id",$f_id,"Kosten: Nahrung","basecost_food"),
			new cTableEditTextField("technologytype","id",$f_id,"Kosten: Metall","basecost_metal"),
			new cTableEditTextField("technologytype","id",$f_id,"Kosten: Runen","basecost_runes"),
			new cTableEditTextField("technologytype","id",$f_id,"Increment (Time + Kosten)","increment"),
			new cTableEditTimeField("technologytype","id",$f_id,"Time","basetime"),
			new cTableEditTextField("technologytype","id",$f_id,"Maxlevel","maxlevel"),
			new cTableEditTextField("technologytype","id",$f_id,"Buildiglevel","buildinglevel"),
			new cTableEditTextField("technologytype","id",$f_id,"Req: Geb","req_geb"),
			new cTableEditTextField("technologytype","id",$f_id,"Req: Tech","req_tech"),
			
			new cTableEditIMGUrl("technologytype","id",$f_id,"Bild","gfx")
		)),
		new cTableEditRows(array(
			new cTableEditRadioField("technologytype","id",$f_id,"Gebäude","buildingtype",$buildings),
			new cTableEditRadioField("technologytype","id",$f_id,"Gruppe","group",$groups)
		))
	))
	,"technologytype","id",$f_id,Query("listall.php?sid=?")
);


$form->HandleInput();
// regenerate typecache
RegenTypeCache();
require(kTypeCacheFile);

require_once("header.php"); 
$form->Show();
echo '<td>	SYNTAX : type&gt;minlevel+inc  OR  type&lt;maxlevel+inc   inc can be float : "4&gt;5+0.5"  for id 4 at least level 5<br></td>';

require_once("footer.php"); 

?>
