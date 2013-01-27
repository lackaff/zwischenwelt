<?php

require_once("../lib.main.php");
require_once("../lib.technology.php");
require_once("../lib.building.php");

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 transitional//EN"
   "http://www.w3.org/TR/html4/transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<link href="http://fonts.googleapis.com/css?family=Bree+Serif" rel="stylesheet" type="text/css">
<link href="http://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="<?=BASEURL?>css/zwstyle_new_temp.css">
<title>Zwischenwelt - Research</title>

</head>
<body>
<?php
include(BASEPATH."/menu.php");

function matchCenter($center,$tleft,$left,$tright,$right){
	return ($center == "$tleft$left") || ($center == "$tright$right");
}

if(empty($f_center))$center = "b1";
else $center = $f_center;

$dot = "";

$dot .= "digraph G {";

$t = sqlgettable("SELECT * FROM `technologytype`","id");
$b = sqlgettable("SELECT * FROM `buildingtype` WHERE `special`=0","id");
$u = sqlgettable("SELECT * FROM `unittype` WHERE (`flags` & ".kUnitFlag_Monster.") = 0","id");

$used_t = array();
$used_b = array();
$used_u = array();

foreach($t as $x){
	$x->req_tech = ParseReqForATechLevel ($x->req_tech);
	$x->req_geb = ParseReqForATechLevel ($x->req_geb);
	foreach($x->req_tech as $o)if(matchCenter($center,"t",$x->id,"t",$o->type)){
		$dot .=  '"'.unhtmlentities($t[$o->type]->name).'" -> "'.unhtmlentities($x->name).'" [fontname="Verdana",fontsize=10,label="'.($o->ismax?"max ":"").$o->level.'",color="'.($o->ismax?"red":"blue").'",fontcolor="blue"];'."\n";
		$used_t[$x->id] = 1;
		$used_t[$o->type] = 1;
	}
	foreach($x->req_geb as $o)if(matchCenter($center,"t",$x->id,"b",$o->type)){
		$dot .=  '"'.unhtmlentities($b[$o->type]->name).'" -> "'.unhtmlentities($x->name).'" [fontname="Verdana",fontsize=10,label="'.($o->ismax?"max ":"").$o->level.'",color="'.($o->ismax?"red":"navy").'",fontcolor="navy"];'."\n";
		$used_b[$o->type] = 1;
		$used_t[$x->id] = 1;
	}
	if(matchCenter($center,"t",$x->id,"",0))$dot .=  '"'.unhtmlentities($x->name).'" [style=filled,color="blue",fontcolor="white"];'."\n";
	if(matchCenter($center,"t",$x->id,"b",$x->buildingtype)){
		$dot .=  '"'.unhtmlentities($b[$x->buildingtype]->name).'" -> "'.unhtmlentities($x->name).'" [fontname="Verdana",fontsize=10,fontcolor="gray"color="'.($o->ismax?"red":"gray").'"];'."\n";
		$used_b[$x->buildingtype] = 1;
		$used_t[$x->id] = 1;
	}
}
foreach($b as $x){
	$x->req_tech = ParseReqForATechLevel ($x->req_tech);
	$x->req_geb = ParseReqForATechLevel ($x->req_geb);
	foreach($x->req_tech as $o)if(matchCenter($center,"b",$x->id,"t",$o->type)){
		$dot .=  '"'.unhtmlentities($t[$o->type]->name).'" -> "'.unhtmlentities($x->name).'" [fontname="Verdana",fontsize=10,label="'.($o->ismax?"max ":"").$o->level.'",color="'.($o->ismax?"red":"blue").'",fontcolor="blue"];'."\n";
		$used_t[$o->type] = 1;
		$used_b[$x->id] = 1;
	}
	foreach($x->req_geb as $o)if(matchCenter($center,"b",$x->id,"b",$o->type)){
		$dot .=  '"'.unhtmlentities($b[$o->type]->name).'" -> "'.unhtmlentities($x->name).'" [fontname="Verdana",fontsize=10,label="'.($o->ismax?"max ":"").$o->level.'",color="'.($o->ismax?"red":"navy").'",fontcolor="navy"];'."\n";
		$used_b[$o->type] = 1;
		$used_b[$x->id] = 1;
	}
	if(matchCenter($center,"b",$x->id,"",0))$dot .=  '"'.unhtmlentities($x->name).'" [style=filled,shape=box,color="navy",fontcolor="white"];'."\n";
}
foreach($u as $x){
	$x->req_tech = ParseReqForATechLevel (trim($x->req_tech_a.",".$x->req_tech_a," ,"));
	$x->req_geb = ParseReqForATechLevel ($x->req_geb);
	foreach($x->req_geb as $o)if(matchCenter($center,"u",$x->id,"b",$o->type)){
		$dot .=  '"'.unhtmlentities($b[$o->type]->name).'" -> "'.unhtmlentities($x->name).'" [fontname="Verdana",fontsize=10,label="'.($o->ismax?"max ":"").$o->level.'",color="'.($o->ismax?"red":"navy").'",fontcolor="navy"];'."\n";
		$used_b[$o->type] = 1;
		$used_u[$x->id] = 1;
	}
	foreach($x->req_tech as $o)if(matchCenter($center,"u",$x->id,"t",$o->type)){
		$dot .=  '"'.unhtmlentities($t[$o->type]->name).'" -> "'.unhtmlentities($x->name).'" [fontname="Verdana",fontsize=10,label="'.($o->ismax?"max ":"").$o->level.'",color="'.($o->ismax?"red":"maroon").'",fontcolor="maroon"];'."\n";
		$used_t[$o->type] = 1;
		$used_u[$x->id] = 1;
	}
	if(isset($b[$x->buildingtype]))if(matchCenter($center,"u",$x->id,"b",$x->buildingtype)){
		$dot .=  '"'.unhtmlentities($b[$x->buildingtype]->name).'" -> "'.unhtmlentities($x->name).'" [fontname="Verdana",fontsize=10,color="'.($o->ismax?"red":"gray").'",fontcolor="gray"];'."\n";
		$used_b[$x->buildingtype] = 1;
		$used_u[$x->id] = 1;
	}
	if(matchCenter($center,"u",$x->id,"",0))$dot .=  '"'.unhtmlentities($x->name).'" [style=filled,shape=box,color="maroon",fontcolor="white"];'."\n";
}


foreach($t as $x){
	if(isset($x->race))$race = $x->race;
	else $race = 1;
	if($race == 0)$race = 1;
	$gfx = str_replace("%NWSE%","ns",$x->gfx);
	$gfx = str_replace("%L%","0",$gfx);
	$gfx = str_replace("%R%",$race,$gfx);
	
	if(isset($used_t[$x->id]))$dot .=  '"'.unhtmlentities($x->name).'" [label="",shapefile="../gfx/'.$gfx.'",href="techgraphpart.php?sid=&center=t'.$x->id.'",style=filled,shape=plaintext,color="white",fontcolor="blue"];'."\n";
}
foreach($b as $x){
	if(isset($x->race))$race = $x->race;
	else $race = 1;
	if($race == 0)$race = 1;
	$gfx = str_replace("%NWSE%","ns",$x->gfx);
	$gfx = str_replace("%L%","0",$gfx);
	$gfx = str_replace("%R%",$race,$gfx);

	if(isset($used_b[$x->id]))$dot .=  '"'.unhtmlentities($x->name).'" [label="",shapefile="../gfx/'.$gfx.'",href="techgraphpart.php?sid=&center=b'.$x->id.'",style=filled,shape=plaintext,color="white",fontcolor="navy"];'."\n";
}
foreach($u as $x){
	if(isset($x->race))$race = $x->race;
	else $race = 1;
	if($race == 0)$race = 1;
	$gfx = str_replace("%NWSE%","ns",$x->gfx);
	$gfx = str_replace("%L%","0",$gfx);
	$gfx = str_replace("%R%",$race,$gfx);

	if(isset($used_u[$x->id]))$dot .=  '"'.unhtmlentities($x->name).'" [label="",shapefile="../gfx/'.$gfx.'",href="techgraphpart.php?sid=&center=u'.$x->id.'",style=filled,shape=plaintext,color="white",fontcolor="maroon"];'."\n";
}

$dot .= "}";

$md5 = md5($dot);

$tmp = BASEPATH."tmp/graph-";
$file_dot = $tmp.$md5.".dot";
$file_png = $tmp.$md5.".png";
$file_map = $tmp.$md5.".map";
$url_png = "../tmp/graph-$md5.png";

if(!file_exists($file_dot)){
	$f = fopen($file_dot,"w");
	fwrite($f,$dot);
	fclose($f);
	
	$cmd = "/usr/bin/fdp";
	
	$out_png = `$cmd -Tpng -o$file_png -Tcmapx -o$file_map $file_dot`;
}

$map = implode("",file($file_map));
echo str_replace("sid=","sid=$f_sid",$map);

$mode = $center{0};
$id = intval(substr($center,1));
?>
<table>
<tr><td><img border=0 usemap="#G" src="<?=$url_png?>"></td>
<td valign=top>
<?php
switch($mode){
	case "b":
		?>
		<b><?=$gBuildingType[$id]->name?></b><hr>
		<?php
		$t = sqlgettable("SELECT * FROM `building` WHERE `user`=".intval($gUser->id)." AND `type`=".intval($id));
		foreach($t as $x){
		?>
			<a href="<?=query("info.php?x=$x->x&y=$x->y&sid=?")?>">Level <?=$x->level?> auf (<?=$x->x?>,<?=$x->y?>)</a><br>
		<?php 
		}
	break;
	case "t":
		?>
		<b><?=$gTechnologyType[$id]->name?></b><hr>
		<?php
		$t = sqlgettable("SELECT * FROM `technology` WHERE `user`=".intval($gUser->id)." AND `type`=".intval($id));
		foreach($t as $x){
		?>
			Level <?=$x->level?><br>
		<?php 
		}
	break;
	case "u":
		$type = $gUnitType[$id];
		?>
		<b><?=$type->name?></b><hr>
		A: <?=$type->a?><br>
		V: <?=$type->a?><br>
		F: <?=$type->f?> /<?=$type->cooldown?><br>
		R: <?=$type->r?>
		Speed: <?=$type->speed?><br>
		<?php
	break;
	default:
		echo " - ";
	break;
}
?>
</td></tr>
</body>
</html>
