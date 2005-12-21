<?php
require_once("lib.main.php");
Lock();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 transitional//EN"
   "http://www.w3.org/TR/html4/transitional.dtd">
<html>
<head>
<link rel="stylesheet" type="text/css" href="../styles.css">
<title>Zwischenwelt - info</title>
</head>
<body>
<?php
if($gUser->admin == 1){
	$gTerrainType = sqlgettable("SELECT * FROM `terraintype`");
	$gBuildingType = sqlgettable("SELECT * FROM `buildingtype`");
	$gUnitType = sqlgettable("SELECT * FROM `unittype`");
	$gTechnologyType = sqlgettable("SELECT * FROM `technologytype`");
	$gTechnologyGroup = sqlgettable("SELECT * FROM `technologygroup`");
	?>
	<table cellspacing=2 cellpadding=2 border=1>
		<tr><th>Terraintype</th><th>Buildingtype</th><th>Unittype</th><th>Technologytype</th><th>Technologygroup</th></tr>
		<tr>
			<td valign=top><table>
				<?foreach ($gTerrainType as $o){?>
					<tr><td>[<?=$o->id?>]</td><td><img src="<?=g($o->gfx)?>" alt='<?=$o->name?>'></td><td><a href="<?=query("info/adminterrain.php?id=$o->id&sid=?")?>"><img alt=Admin title=Admin src="<?=g("icon/admin.png")?>" border=0></a></td></tr>
				<?}?>
			</table></td>
			<td valign=top><table>
				<?foreach ($gBuildingType as $o){?>
					<tr><td>[<?=$o->id?>]</td><td><img src="<?=g($o->gfx)?>" alt='<?=$o->name?>'></td><td><a href="<?=query("info/adminbuildingtype.php?id=$o->id&sid=?")?>"><img alt=Admin title=Admin src="<?=g("icon/admin.png")?>" border=0></a></td></tr>
				<?}?>
			</table></td>
			<td valign=top><table>
				<?foreach ($gUnitType as $o){?>
					<tr><td>[<?=$o->id?>]</td><td><img src="<?=g($o->gfx)?>" alt='<?=$o->name?>'></td><td><a href="<?=query("info/adminunittype.php?id=$o->id&sid=?")?>"><img alt=Admin title=Admin src="<?=g("icon/admin.png")?>" border=0></a></td></tr>
				<?}?>
			</table></td>
			<td valign=top><table>
				<?foreach ($gTechnologyType as $o){?>
					<tr><td>[<?=$o->id?>]</td><td><img src="<?=g($o->gfx)?>" alt='<?=$o->name?>'></td><td><a href="<?=query("info/admintech.php?id=$o->id&sid=?")?>"><img alt=Admin title=Admin src="<?=g("icon/admin.png")?>" border=0></a></td></tr>
				<?}?>
			</table></td>
			<td valign=top><table>
				<?foreach ($gTechnologyGroup as $o){?>
					<tr><td>[<?=$o->id?>]</td><td><img src="<?=g($o->gfx)?>" alt='<?=$o->name?>'></td><td><a href="<?=query("info/admintechgroup.php?id=$o->id&sid=?")?>"><img alt=Admin title=Admin src="<?=g("icon/admin.png")?>" border=0></a></td></tr>
				<?}?>
			</table></td>
		</tr>
	</table>
	<?
}
?>
