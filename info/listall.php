<?php
require_once("../lib.main.php");
Lock();

require_once("header.php"); 

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
					<tr><td>[<?=$o->id?>]</td><td><img src="<?=g($o->gfx)?>" alt='<?=$o->name?>'></td><td><a href="<?=query("adminterrain.php?id=$o->id&sid=?")?>"><img alt=Admin title=Admin src="<?=g("icon/admin.png")?>" border=0></a></td></tr>
				<?}?>
			</table></td>
			<td valign=top><table>
				<?foreach ($gBuildingType as $o){?>
					<tr><td>[<?=$o->id?>]</td><td><img src="<?=g($o->gfx)?>" alt='<?=$o->name?>'></td><td><a href="<?=query("adminbuildingtype.php?id=$o->id&sid=?")?>"><img alt=Admin title=Admin src="<?=g("icon/admin.png")?>" border=0></a></td></tr>
				<?}?>
			</table></td>
			<td valign=top><table>
				<?foreach ($gUnitType as $o){?>
					<tr><td>[<?=$o->id?>]</td><td><img src="<?=g($o->gfx)?>" alt='<?=$o->name?>'></td><td><a href="<?=query("adminunittype.php?id=$o->id&sid=?")?>"><img alt=Admin title=Admin src="<?=g("icon/admin.png")?>" border=0></a></td></tr>
				<?}?>
			</table></td>
			<td valign=top><table>
				<?foreach ($gTechnologyType as $o){?>
					<tr><td>[<?=$o->id?>]</td><td><img src="<?=g($o->gfx)?>" alt='<?=$o->name?>'></td><td><a href="<?=query("admintech.php?id=$o->id&sid=?")?>"><img alt=Admin title=Admin src="<?=g("icon/admin.png")?>" border=0></a></td></tr>
				<?}?>
			</table></td>
			<td valign=top><table>
				<?foreach ($gTechnologyGroup as $o){?>
					<tr><td>[<?=$o->id?>]</td><td><img src="<?=g($o->gfx)?>" alt='<?=$o->name?>'></td><td><a href="<?=query("admintechgroup.php?id=$o->id&sid=?")?>"><img alt=Admin title=Admin src="<?=g("icon/admin.png")?>" border=0></a></td></tr>
				<?}?>
			</table></td>
		</tr>
	</table>
	<?php
}

require_once("footer.php"); 
?>
