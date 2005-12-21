<?php
require_once("../lib.main.php");
require_once("../lib.map.php");
Lock();
profile_page_start("bauplan");


//$list = array(kDiplo_BreakFriendOnAttack=>"Freundschaft bei einem Angriff automatisch beenden");
$list = array();

if(isset($f_save))foreach($list as $name=>$text)SetUserValue($gUser,$name,$_REQUEST[$name]>0?1:0);


if (isset($f_remove)) {
	$f_sel_friend = intarray(isset($f_sel_friend)?$f_sel_friend:false);
	$f_sel_enemy = intarray(isset($f_sel_enemy)?$f_sel_enemy:false);
	foreach ($f_sel_friend as $uid)	SetFOF($gUser->id,intval($uid),kFOF_Neutral);
	foreach ($f_sel_enemy as $uid)	SetFOF($gUser->id,intval($uid),kFOF_Neutral);
}
if (isset($f_accept)) {
	$f_sel_friendoffer = intarray(isset($f_sel_friendoffer)?$f_sel_friendoffer:false);
	foreach ($f_sel_friendoffer as $uid) SetFOF($gUser->id,intval($uid),kFOF_Friend);
} // endif isset


$friends = sqlgettable("SELECT `user`.*,`user`.`general_pts`+`user`.`army_pts` as `pts` FROM `fof_user`,`user` 
	WHERE `class` = ".kFOF_Friend." AND `master` = ".$gUser->id." AND `other` = `user`.id ORDER BY `pts` DESC");
$enemies = sqlgettable("SELECT `user`.*,`user`.`general_pts`+`user`.`army_pts` as `pts` FROM `fof_user`,`user` 
	WHERE `class` = ".kFOF_Enemy." AND `master` = ".$gUser->id." AND `other` = `user`.id ORDER BY `pts` DESC");
$friend_offers = sqlgettable("SELECT `user`.*,`user`.`general_pts`+`user`.`army_pts` as `pts` FROM `fof_user`,`user` 
	WHERE `class` = ".kFOF_Friend." AND `other` = ".$gUser->id." AND `master` = `user`.id ORDER BY `pts` DESC");
$friendids = intarray(AF($friends,"id"));
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 transitional//EN"
   "http://www.w3.org/TR/html4/transitional.dtd">
<html>
<head>
<link rel="stylesheet" type="text/css" href="../styles.css">
<title>Zwischenwelt - Diplomatie</title>
<SCRIPT LANGUAGE="JavaScript" type="text/javascript">
<!--
	function setallchecks (name,check) {
		for (var i in document.getElementsByName(name))
			document.getElementsByName(name)[i].checked = check;
	}
//-->
</SCRIPT>
</head>
<body>
<?php
include("../menu.php");
?>


<?php $count = 0; foreach ($friend_offers as $o) if (!in_array($o->id,$friendids)) $count++; ?>
<?php if ($count == 0) {?>
<?php } else { // endif not all empty?>
	<form method="post" action="<?=Query("?sid=?")?>">
		<table>
		<tr><th>Freundschafts angebote</th></tr>
		<tr><td valign="top">
			<?php if (count($friend_offers)) {?>
				<?php /*Freunde*/ ?>
				<table>
				<tr>
					<th><input type="checkbox" name="dummy" value="1" onChange="setallchecks('sel_friendoffer[]',this.checked)"></th>
					<th>Name</th>
					<th>Pos</th>
					<th>Punkte</th>
				</tr>
				<?php foreach ($friend_offers as $o) if (!in_array($o->id,$friendids)) {?>
					<tr>
						<td><input type="checkbox" name="sel_friendoffer[]" value="<?=$o->id?>"></td>
						<td><?=usermsglink($o)?></td>
						<td><?=opos2txt(sqlgetobject("SELECT * FROM `building` WHERE `type` = ".kBuilding_HQ." AND `user` = ".$o->id))?></td>
						<td align="right"><?=$o->general_pts+$o->army_pts?></td>
					</tr>
				<?php } // endforeach?>
				</table>
			<?php } // endif nonempty?>
		</td></tr>
		</table>
		<input type="submit" name="accept" value="annehmen">
	</form>
	<br><br>
<?php } // endif not all empty?>
		
		
<?php if (count($friends) == 0 && count($enemies) == 0) {?>
	keine diplomatischen Beziehungen vorhanden
<?php } else { // endif not all empty?>
	<form method="post" action="<?=Query("?sid=?")?>">
		<table>
		<tr><th>Freunde</th><th>Feinde</th></tr>
		<tr><td valign="top">
			<?php if (count($friends)) {?>
				<?php /*Freunde*/ ?>
				<table>
				<tr>
					<th><input type="checkbox" name="dummy" value="1" onChange="setallchecks('sel_friend[]',this.checked)"></th>
					<th>Name</th>
					<th>Pos</th>
					<th>Punkte</th>
				</tr>
				<?php foreach ($friends as $o) {?>
					<tr>
						<td><input type="checkbox" name="sel_friend[]" value="<?=$o->id?>"></td>
						<td><?=usermsglink($o)?><?=GetFOF($o->id,$gUser->id)==kFOF_Friend?"":"<font color='#0088FF'>(einseitig)</font>"?></td>
						<td><?=opos2txt(sqlgetobject("SELECT * FROM `building` WHERE `type` = ".kBuilding_HQ." AND `user` = ".$o->id))?></td>
						<td align="right"><?=$o->general_pts+$o->army_pts?></td>
					</tr>
				<?php } // endforeach?>
				</table>
			<?php } // endif nonempty?>
		</td><td valign="top">
			<?php if (count($enemies)) {?>
				<?php /*Feinde*/ ?>
				<table>
				<tr>
					<th><input type="checkbox" name="dummy" value="1" onChange="setallchecks('sel_enemy[]',this.checked)"></th>
					<th>Name</th>
					<th>Pos</th>
					<th>Punkte</th>
				</tr>
				<?php foreach ($enemies as $o) {?>
					<tr>
						<td><input type="checkbox" name="sel_enemy[]" value="<?=$o->id?>"></td>
						<td><?=usermsglink($o)?></td>
						<td><?=opos2txt(sqlgetobject("SELECT * FROM `building` WHERE `type` = ".kBuilding_HQ." AND `user` = ".$o->id))?></td>
						<td align="right"><?=$o->general_pts+$o->army_pts?></td>
					</tr>
				<?php } // endforeach?>
				</table>
			<?php } // endif nonempty?>
		</td></tr>
		</table>
		<input type="submit" name="remove" value="entfernen">
	</form>
<?php } // endif not all empty ?>
<hr>
<?php if (count($list) > 0) {?>
	<form method=post action="<?=query("?sid=?")?>">
	<table>
		<tr><th>Einstellungen</th></tr>
		<?php foreach($list as $name=>$text)echo "<tr><td><input name=\"$name\" type=checkbox value=1 ".(GetUserValue($gUser,$name,0)?"checked":"")."> $text</td></tr>"; ?>
		<tr><td align=right><input name=save type=submit value="übernehmen"></td></tr>
	</table>
	</form>
<?php } // endif?>
<?php profile_page_end(); ?>
</body>
</html>