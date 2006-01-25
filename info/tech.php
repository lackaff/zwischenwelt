<?php

// THIS FILE IS UNUSED!!!
exit;

require_once("../lib.main.php");
require_once("../lib.guild.php");
require_once("../lib.technology.php");
require_once("../lib.map.php");
Lock();
profile_page_start("tech.php");




// TODO : listTechThatDependOn now returns array with objects instead of values !!
//a id=>req_level lists of all techs that depend on tech mit id
function listTechThatDependOn($id) {
	$id = intval($id);
	$t = sqlgettable("SELECT `id`,`req_tech` FROM `technologytype` WHERE `req_tech` LIKE '%$id:%'");
	$list = Array();
	foreach($t as $x){
		$req = ParseReq($x->req_tech);
		if(empty($req[$id]))continue;
		//echo "<br>$x->id $x->req_tech $id ";print_r($req);
		$list[$x->id] = $req[$id];
	}
	return $list;
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 transitional//EN"
   "http://www.w3.org/TR/html4/transitional.dtd">
<html>
<head>
<link rel="stylesheet" type="text/css" href="../styles.css">
<link rel="stylesheet" type="text/css" href="<?=GetZWStylePath()?>">
<title>Zwischenwelt - Forschung</title>

</head>
<body>
<?php
include("../menu.php");


?>
<a href="<?=query("techgraphpart.php?sid=?")?>">Technologiebaum durchsuchen</a>, 
<a target="_blank" href="../tmp/tech.png">ganzen Technologiebaum anzeigen</a>
<hr>

<table>
<tr><td valign=top>
	<b>laufende Forschungen</b><br>
	
	<table border=1>
	<tr><th></th><th>Name</th><th>Gebäude</th><th>Restzeit</th></tr>
	<?php
	$t = sqlgettable("SELECT * FROM `technology` WHERE `user`=".$gUser->id." AND `upgradetime`>0","id");
	
	foreach($t as $x){
		$b = sqlgetobject("SELECT * FROM `building` WHERE `id`=".$x->upgradebuilding);
		$infourl = Query("info.php?sid=?&x=".$b->x."&y=".$b->y."&infotechtype=".$x->type);
		?>
		<tr><td><a href="<?=$infourl?>"><img border=0 src="<?=g($gTechnologyType[$x->type]->gfx)?>"></a></td>
		<td><?=cText::Wiki("tech",$x->type)?><a href="<?=query("?sid=?&tech=".$x->type)?>"><?=$gTechnologyType[$x->type]->name?></a></td>
		<td><a href="<?=query("info.php?sid=?&x=".$b->x."&y=".$b->y)?>">(<?=$b->x?>,<?=$b->y?>)</a></td>
		<td><?=Duration2Text($x->upgradetime - time())?></td></tr>
		<?php
	}
	?>
	</table>
</td><td valign=top>
	<b>schon erforschte Technologien</b><br>
	
	<table border=1>
	<tr><th></th><th>Name</th><th>Level</th></tr>
	<?php
	$t = sqlgettable("SELECT * FROM `technology` WHERE `level`>0 AND `user`=".$gUser->id,"id");
	
	foreach($t as $x){
		//wenn es den technik typ nicht mehr gibt, dann lösch die technik
		if(!$gTechnologyType[$x->type]){
			sql("DELETE FROM `technology` WHERE `user`=".$gUser->id." AND `id`=".$x->id);
			continue;
		}
		
		$b = sqlgetobject("SELECT * FROM `building` WHERE `id`=".$x->upgradebuilding);
		$infourl = Query("info.php?sid=?&x=".$b->x."&y=".$b->y."&infotechtype=".$x->type);
		?>
		<tr><td><a href="<?=$infourl?>"><img border=0 src="<?=g($gTechnologyType[$x->type]->gfx)?>"></a></td>
			<td><?=cText::Wiki("tech",$x->type)?><a href="<?=query("?sid=?&tech=".$x->type)?>"><?=$gTechnologyType[$x->type]->name?></a></td>
		<td><?=$x->level?></td></tr>
		<?php
	}
	?>
	</table>
</td></tr>
</table>

<?php

function showGebType($id){
	global $gBuildingType;
	if(empty($gBuildingType[intval($id)]))return;
	?><img src="<?=g($gBuildingType[$id]->gfx,"ns")?>" align="middle"><?=cText::Wiki("building",$id)?><?=$gBuildingType[$id]->name?><?php
}

function showTechType($id){
	global $gTechnologyType;
	if(empty($gTechnologyType[intval($id)]))return;
	?><img src="<?=g($gTechnologyType[$id]->gfx)?>" align="middle"> <a href="<?=query("?sid=?&tech=".$id)?>"><?=cText::Wiki("tech",$id)?><?=$gTechnologyType[$id]->name?></a><?php
}

function showTechReqType($o){
	showTechType($o->type);
	echo " [Level ".$o->level.", Inc ".$o->inc."]";
}

function showGebReqType($o){
	showGebType($o->type);
	echo " [Level ".$o->level.", Inc ".$o->inc."]";
}

?>
<br>
<div class="showtechdeplist">
<form method=post action="<?=query("?sid=?")?>">
<select size=1 name=tech>
<?php foreach($gTechnologyType as $t)echo "<option value=$t->id>$t->name</option>"; ?>
</select>
<input type=submit value=anzeigen>
</form>
</div>
<?php
if(!empty($f_tech)){
	$t = $gTechnologyType[intval($f_tech)];
	if(!empty($t)){
		$req_tech = ParseReq($t->req_tech);
		$req_geb = ParseReq($t->req_geb);
?>
<br>
<div class="showtechdep">
<table border=1>
	<tr><th>Vorraussetzung für</th><th>ausgewählt</th><th>braucht man dafür</th></tr>
	<tr><td>
		<?php
		$l = listTechThatDependOn($f_tech);
		$t = GetTechnologyObject($f_tech);
		foreach($l as $o){
			showTechReqType($o);
			echo "<br>";
		}
		?>
	</td><td><?php showTechType($f_tech)?> [Level <?=$t->level?>]</td><td>
		<?php 
		foreach($req_tech as $o){
			showTechReqType($o); // TODO : pass full tech object
			echo "<br>";
		}
		foreach($req_geb as $o){
			showGebReqType($o); // TODO : pass full tech object
			echo "<br>";
		}
				?>
	</td></tr>
</table>
</div>
<?php
	}
}
?>

</form>
</body>
</html>
<?php profile_page_end(); ?>
