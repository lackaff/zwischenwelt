<?php
/*
OBSOLETE FILE !
spring based tech-dependance diagram
*/
require_once("../lib.main.php");

require_once("libspring.php");

//Lock();

profile_page_start("viewtechtree.php");



$t = sqlgettable("SELECT * FROM `technologytype`");

$b = sqlgettable("SELECT * FROM `buildingtype` WHERE `special`=0");

$u = sqlgettable("SELECT * FROM `unittype` WHERE `type`<>1");

foreach ($u as $unit)
	$unit->req_tech=$unit->req_tech_a.",".$unit->req_tech_v;

$links = Array();

$nodes = Array();



function &findNode($id,&$table){

	for($i=0;$i<sizeof($table);++$i)

		if($table[$i]->id == $id)return $table[$i]->node;

	return null;

}



function interpretReqAndBuildNodes(&$t,&$nodes,$start,$size=10){

for($i=0;$i<sizeof($t);++$i)

{

	$t[$i]->node =& new Node(new Vector(($i+$start) % $size,floor(($i+$start) / $size)));

	$nodes[] =& $t[$i]->node;

	

	$x = $t[$i]->req_tech;

	if(!empty($x))

	{

		$x = explode(",",$x);

		$l = Array();

		foreach($x as $xx)

		{

			$xx = explode(":",$xx);

			$o = null;

			$o->type = $xx[0];

			$o->level = $xx[1];

			$l[] = $o;

		}

		$t[$i]->req_tech = $l;

	}



	$x = $t[$i]->req_geb;

	if(!empty($x))

	{

		$x = explode(",",$x);

		$l = Array();

		foreach($x as $xx)

		{

			$xx = explode(":",$xx);

			$o = null;

			$o->type = $xx[0];

			$o->level = $xx[1];

			$l[] = $o;

		}

		$t[$i]->req_geb = $l;

	}

}

}





function buildLinksFromTable(&$t,&$tech,&$geb,&$links){

for($i=0;$i<sizeof($t);++$i){

	$me = $t[$i]->id;

	$x =& $t[$i]->req_tech;

	if(!empty($x)){

		for($a=0;$a<sizeof($x);++$a){

			$x[$a]->link =& new Link(findNode($me,$t),findNode($x[$a]->type,$tech),$x[$a]->level);

			$links[] =& $x[$a]->link;

		}

	}

	

	$x =& $t[$i]->req_geb;

	if(!empty($x)){

		for($a=0;$a<sizeof($x);++$a){

			 $x[$a]->link =& new Link(findNode($me,$t),findNode($x[$a]->type,$geb),$x[$a]->level);

			 $links[] =& $x[$a]->link;

		}

	}

		

}

}



$max = sizeof($t)+sizeof($b)+sizeof($u);

$max = round(sqrt($max));

interpretReqAndBuildNodes($t,$nodes,0,$max);

interpretReqAndBuildNodes($b,$nodes,sizeof($t),$max);

interpretReqAndBuildNodes($u,$nodes,sizeof($t)+sizeof($b),$max);



buildLinksFromTable($t,$t,$b,$links);

buildLinksFromTable($b,$t,$b,$links);

buildLinksFromTable($u,$t,$b,$links);





if(isset($f_steps))$steps = intval($f_steps);

else $steps = 10;





for($i=0;$i<$steps;++$i)

{

	bounceNodes($nodes);

	moveNodes($nodes,$links,0.1);

}





$w = 600;               

$h = 400;



MakeNodesNice($nodes,$w,$h);



$w += 50;

$h += 50;



//render this stuff





function imagearrow(&$im,$x0,$y0,$x1,$y1,&$color){

	imageline($im,$x0+10,$y0+10,$x1,$y1,$color);

	imagefilledellipse ($im, $x1,$y1,5,5,$color);

}



header("Content-type: image/png");

$im = imagecreatetruecolor($w,$h);



$techcolor = imagecolorallocate($im, 0, 255, 0);

$gebcolor = imagecolorallocate($im, 0, 0, 255);

$unitcolor = imagecolorallocate($im, 255, 0, 0);

$bgcolor = imagecolorallocate($im,255,255,255);

$arrowcolor = imagecolorallocate($im,150,150,150);



imagefilledrectangle($im,0,0,$w,$h,$bgcolor);





function renderList(&$im,&$list,$color){

	$iconsize = 20;



	foreach($list as $x){

		$px = round($x->node->p->x);

		$py = round($x->node->p->y);

		

		$path = str_replace("%NWSE%","",$x->gfx);
		$path = str_replace("%L%","0",$path);

		if(file_exists(g($path)))$path = "../gfx/".$path;

		else if(file_exists("../".$path))$path = "../".$path;

		else $path = null;

		if($path){

			$icon = @imagecreatefrompng($path);

			if($icon){

				@imagecopyresampled($im, $icon, $px, $py, 0, 0, $iconsize, $iconsize, imagesx($icon), imagesy($icon));

				@imagedestroy($icon);

			}

		}

		imagestring($im,0,$px,$py+$iconsize,html_entity_decode($x->name),$color);	

	}

}





renderList($im,$t,$techcolor);

renderList($im,$b,$gebcolor);

renderList($im,$u,$unitcolor);



foreach($links as $x){

        imagearrow($im,$x->left->p->x-2,$x->left->p->y+4,$x->right->p->x,$x->right->p->y,$arrowcolor);

}	



imagepng($im);

imagedestroy($im);



profile_page_end();

?>

