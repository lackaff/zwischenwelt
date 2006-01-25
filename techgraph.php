<?php

require_once("lib.main.php");
require_once("lib.technology.php");
require_once("lib.building.php");

?>
digraph G {
<?php

$t = sqlgettable("SELECT * FROM `technologytype`","id");
$b = sqlgettable("SELECT * FROM `buildingtype` WHERE `special`=0","id");
$u = sqlgettable("SELECT * FROM `unittype` WHERE (`flags` & ".kUnitFlag_Monster.") = 0","id");

foreach($t as $x){
	$x->req_tech = ParseReqForATechLevel ($x->req_tech);
	$x->req_geb = ParseReqForATechLevel ($x->req_geb);
	foreach($x->req_tech as $id=>$o)echo '"'.unhtmlentities($t[$id]->name).'" -> "'.unhtmlentities($x->name).'" [fontname="Verdana",fontsize=10,label="lvl '.$o->level.'",color="blue",fontcolor="blue"];'."\n";
	foreach($x->req_geb as $id=>$o)echo '"'.unhtmlentities($b[$id]->name).'" -> "'.unhtmlentities($x->name).'" [fontname="Verdana",fontsize=10,label="lvl '.$o->level.'",color="navy",fontcolor="navy"];'."\n";
	echo '"'.unhtmlentities($x->name).'" [style=filled,color="blue",fontcolor="white"];'."\n";
	echo '"'.unhtmlentities($b[$x->buildingtype]->name).'" -> "'.unhtmlentities($x->name).'" [fontname="Verdana",fontsize=10,label="ermöglicht",fontcolor="gray"color="gray"];'."\n";
}
foreach($b as $x){
	$x->req_tech = ParseReqForATechLevel ($x->req_tech);
	$x->req_geb = ParseReqForATechLevel ($x->req_geb);
	foreach($x->req_tech as $id=>$o)echo '"'.unhtmlentities($t[$id]->name).'" -> "'.unhtmlentities($x->name).'" [fontname="Verdana",fontsize=10,label="lvl '.$o->level.'",color="blue",fontcolor="blue"];'."\n";
	foreach($x->req_geb as $id=>$o)echo '"'.unhtmlentities($b[$id]->name).'" -> "'.unhtmlentities($x->name).'" [fontname="Verdana",fontsize=10,label="lvl '.$o->level.'",color="navy",fontcolor="navy"];'."\n";
	echo '"'.unhtmlentities($x->name).'" [style=filled,shape=box,color="navy",fontcolor="white"];'."\n";
}
foreach($u as $x){
	$x->req_tech = ParseReqForATechLevel (trim($x->req_tech_a.",".$x->req_tech_a," ,"));
	$x->req_geb = ParseReqForATechLevel ($x->req_geb);
	foreach($x->req_geb as $id=>$o)echo '"'.unhtmlentities($b[$id]->name).'" -> "'.unhtmlentities($x->name).'" [fontname="Verdana",fontsize=10,label="lvl '.$o->level.'",color="navy",fontcolor="navy"];'."\n";
	foreach($x->req_tech as $id=>$o)echo '"'.unhtmlentities($t[$id]->name).'" -> "'.unhtmlentities($x->name).'" [fontname="Verdana",fontsize=10,label="lvl '.$o->level.'",color="maroon",fontcolor="maroon"];'."\n";
	if(isset($b[$x->buildingtype]))echo '"'.unhtmlentities($b[$x->buildingtype]->name).'" -> "'.unhtmlentities($x->name).'" [fontname="Verdana",fontsize=10,label="ermöglicht",color="gray",fontcolor="gray"];'."\n";
	echo '"'.unhtmlentities($x->name).'" [style=filled,shape=box,color="maroon",fontcolor="white"];'."\n";
}


?>
}
