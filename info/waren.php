<?php
require_once("../lib.main.php");
require_once("../lib.item.php");
Lock();


profile_page_start("waren");

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 transitional//EN"
   "http://www.w3.org/TR/html4/transitional.dtd">
<html>
<head>
<link rel="stylesheet" type="text/css" href="../styles.css">
<link rel="stylesheet" type="text/css" href="<?=GetZWStylePath()?>">
<title>Zwischenwelt - Kosten</title>

</head>
<body>
<?php
include("../menu.php");
?>

<?php if (isset($f_t)) {?>
<a href="<?=Query("?sid=?")?>"><u><b>Warenübersicht</b></u></a>
<?php } // endif?>
<table>
<tr>
<?php $i=0; foreach ($gItemType as $it) if ((intval($it->flags) & kItemFlag_Ware) && (!isset($f_t) || $it->id == intval($f_t))) {?>
		<td align="right"><?=floor(cItem::GetUserTotalItemAmount($it->id,$gUser->id))?></td>
		<td>
			<a href="<?=Query("?sid=?&t=".$it->id)?>">
			<img border=0 title="<?=$it->name?>" alt="<?=$it->name?>" src="<?=g($it->gfx)?>">
			</a>
		</td>
		<td><?=$it->name?></td>
		<?php if ($i == 4) $i += 1;?>
		<?php if ((++$i)%3==0) {?>
		</tr><tr>
		<?php } // endif?>
<?php }?>
</tr>
</table>

<?php $itemcount = 0; ?>
<table>
<tr>
<?php $i=0; foreach ($gItemType as $it) if (!(intval($it->flags) & kItemFlag_Ware) && (!isset($f_t) || $it->id == intval($f_t))) {?>
		<?php $amount = cItem::GetUserTotalItemAmount($it->id,$gUser->id); if ($amount < 1.0) continue; ?>
		<td align="right"><?=floor($amount)?></td>
		<td>
			<a href="<?=Query("?sid=?&t=".$it->id)?>">
			<img border=0 title="<?=$it->name?>" alt="<?=$it->name?>" src="<?=g($it->gfx)?>">
			</a>
		</td>
		<td><?=$it->name?></td>
		<?php if ($i == 4) $i += 1;?>
		<?php if ((++$i)%3==0) {?>
		</tr><tr>
		<?php } // endif?>
<?php }?>
</tr>
</table>

<?php if (isset($f_t)) {?>
	<table>
	<tr>
		<th>Wieviel</th>
		<th>Besitzer</th>
		<th>Wo</th>
	</tr>
		<?php		
			$userid = $gUser->id;
			$it = $gItemType[intval($f_t)];
			$myres = false;
			foreach($gRes as $n=>$f) 
				if ($gRes2ItemType[$f] == $it->id)
					$myres = $f;
			if ($myres) {?>
				<tr>
					<td><?=intval(sqlgetone("SELECT `$myres` FROM `user` WHERE `id` = ".intval($userid)))?></td>
					<td><?="Hauptlager"?></td>
					<td></td>
				</tr>
			<?php }
			$armies = sqlgettable("SELECT * FROM `army` WHERE `user` = ".intval($userid));
			foreach ($armies as $army) {
				if ($myres && intval($army->{$myres}) > 0) {?>
					<tr>
						<td><?=intval($army->{$myres})?></td>
						<td><?=$army->name?></td>
						<td><?=pos2txt($army->x,$army->y)?></td>
					</tr>
				<?php }
				$iteminarmy = intval(sqlgetone("SELECT SUM(`amount`) FROM `item` WHERE `type` = ".$it->id." AND `army` = ".$army->id));
				if ($iteminarmy > 0) {?>
					<tr>
						<td><?=$iteminarmy?></td>
						<td><?=$army->name?></td>
						<td><?=pos2txt($army->x,$army->y)?></td>
					</tr>
				<?php }
			}
			$blist = sqlgettable("SELECT (`item`.`amount`) as `sum`,`building`.* FROM `item`,`building` WHERE `item`.`type` = ".$it->id." AND `building`.`id` = `item`.`building` AND `building`.`user` = ".intval($userid));
			foreach ($blist as $building) {
				if (intval($building->sum) > 0) {?>
					<tr>
						<td><?=intval($building->sum)?></td>
						<td><?=$gBuildingType[$building->type]->name?></td>
						<td><?=pos2txt($building->x,$building->y)?></td>
					</tr>
				<?php }
			}
		?>
	</table>
<?php } // endif?>

(wir planen gerade an einem Handels-system, das hier sind die ersten Schritte, kann aber noch einige Zeit dauern)

<?php profile_page_end();?>
</body>
</html>
