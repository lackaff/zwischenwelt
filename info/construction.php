<?php
require_once("../lib.main.php");
require_once("../lib.construction.php");
Lock();
profile_page_start("construction.php");
?>
<!--construction plan-->
<?php if ($gObject->user == $gUser->id) {?>
	<?php 		
	$buildingtype = $gBuildingType[$gObject->type];
	$owner = sqlgetobject("SELECT * FROM `user` WHERE `id` = ".$gObject->user);

	$minprio = sqlgetone("SELECT MIN(`priority`) FROM `construction` WHERE `user` = ".$gUser->id);

	if ($gObject->priority > $minprio) {	
		$prevcon = sqlgetobject("SELECT * FROM `construction` WHERE `user` = ".$gUser->id." AND `priority` < ".$gObject->priority." ORDER BY `priority` DESC LIMIT 1");
		$firstcon = sqlgetobject("SELECT * FROM `construction` WHERE `user` = ".$gUser->id." AND `priority` = ".$minprio);
	}
	$nextcon = sqlgetobject("SELECT * FROM `construction` WHERE `user` = ".$gUser->id." AND `priority` > ".$gObject->priority." ORDER BY `priority` LIMIT 1");
	?>
	<FORM METHOD=POST ACTION="<?=Query("?sid=?&x=?&y=?")?>">
	<INPUT TYPE="hidden" NAME="do" VALUE="cancelconstructionplan">
	<INPUT TYPE="hidden" NAME="id" VALUE="<?=$gObject->id?>">
	<INPUT TYPE="submit" NAME="buildnext" VALUE="als n&auml;chstes bauen">
	<INPUT TYPE="submit" NAME="cancelone" VALUE="abbrechen">
	</FORM>
	<a href="<?=Query("bauplan.php?sid=?")?>"><b>Baupl&auml;ne</b></a>&nbsp;
	<a href="<?=Query("kosten.php?sid=?")?>"><b>Kosten</b></a><br>
	<?php if (isset($prevcon)) {?>
		<a href="<?=Query("?sid=?&x=".$firstcon->x."&y=".$firstcon->y)?>">zum ersten Plan</a><br>
		<a href="<?=Query("?sid=?&x=".$prevcon->x."&y=".$prevcon->y)?>">zum vorherigen Plan</a><br>
	<?php }?>
	<?php if (isset($nextcon)) {?>
		<a href="<?=Query("?sid=?&x=".$nextcon->x."&y=".$nextcon->y)?>">zum n&auml;chsten Plan</a><br>
	<?php }?>
<?php }?>
<?php profile_page_end(); ?>
