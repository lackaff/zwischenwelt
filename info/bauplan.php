<?php
require_once("../lib.main.php");
require_once("../lib.map.php");
Lock();

profile_page_start("bauplan");

// move first
if (isset($f_cp_first) && isset($f_cp)) {
	if (!is_array($f_cp)) $f_cp = array(0=>$f_cp);
	array_walk($f_cp,"walkint");
	$cpidlist = implode(",",$f_cp);
	$max = sqlgetone("SELECT MAX(`priority`) FROM `construction` WHERE `user` = ".$gUser->id." AND `id` IN ($cpidlist)");
	sql("UPDATE `construction` SET `priority` = `priority` + ".($max+1)." WHERE `user` = ".$gUser->id." AND `id` NOT IN ($cpidlist)");
}
// move last
if (isset($f_cp_last) && isset($f_cp)) {
	if (!is_array($f_cp)) $f_cp = array(0=>$f_cp);
	array_walk($f_cp,"walkint");
	$cpidlist = implode(",",$f_cp);
	$max = sqlgetone("SELECT MAX(`priority`) FROM `construction` WHERE `user` = ".$gUser->id." AND `id` NOT IN ($cpidlist)");
	sql("UPDATE `construction` SET `priority` = `priority` + ".($max+1)." WHERE `user` = ".$gUser->id." AND `id` IN ($cpidlist)");
	
}
if (isset($f_cp_abort) && isset($f_cp)) {
	if (!is_array($f_cp)) $f_cp = array(0=>$f_cp);
	array_walk($f_cp,"walkint");
	$cpidlist = implode(",",$f_cp);
	sql("DELETE FROM `construction` WHERE `user` = ".$gUser->id." AND `id` IN ($cpidlist)");
	?>
	<script language="javascript">
		parent.map.location.href = parent.map.location.href;
	</script>
	<?php
}
// compacts priority
if ((isset($f_cp_first) || isset($f_cp_last)) && isset($f_cp)) {
	$i = 1;
	$cps = sqlgettable("SELECT * FROM `construction` WHERE `user` = ".$gUser->id." ORDER BY `priority`");
	foreach ($cps as $cp) sql("UPDATE `construction` SET `priority` = ".($i++)." WHERE `id` = ".$cp->id);
}

$cps = sqlgettable("SELECT * FROM `construction` WHERE `user` = ".$gUser->id." ORDER BY `priority`");
$types = AF($cps,"type");
$types = array_unique($types);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 transitional//EN"
   "http://www.w3.org/TR/html4/transitional.dtd">
<html>
<head>
<link rel="stylesheet" type="text/css" href="<?=GetZWStylePath()?>">
<title>Zwischenwelt - Baupläne</title>

</head>
<body>
<?php
include("../menu.php");
?>


insgesamt <?=count($cps)?> Pläne:
<form method="post" action="<?=Query("?sid=?")?>">
	<table><tr>
		<?php $i=0; foreach ($cps as $cp) {?>
		<td valign="center">
			<a target='map' href='<?=Query("../".kMapScript."?sid=?&planmap=1&x=".$cp->x."&y=".$cp->y)?>'>
			<?="<img border=1 src='".g($gBuildingType[$cp->type]->gfx,"we",0,$gUser->race)."'>"?>
			</a><br>
			<input type="checkbox" name="cp[]" value="<?=$cp->id?>" <?=(isset($f_type)&&$f_type==$cp->type)?"checked":""?>>
		</td>
		<?=((($i++)%15)==14)?"</tr><tr>":""?>
		<?php }?>
	</tr></table>
	<input type="submit" name="cp_first" value="Anfang">
	<input type="submit" name="cp_last" value="Ende">
	<input type="submit" name="cp_abort" value="Abbrechen">
</form>

Alle von einem Typ auswählen :<br>
<?php foreach ($types as $tid) {
	echo "<a href='".Query("?sid=?&type=".$tid)."'>";
	echo "<img border=1 src='".g($gBuildingType[$tid]->gfx,"we",0,$gUser->race)."'>";
	echo "</a>";
}?><br>


<br>
<br>
siehe auch <a href="<?=Query("kosten.php?sid=?")?>"><b>Kosten</b></a><br>
Auf der Karte kann man sich anzeigen lassen,<br>
welcher Plan was wird, wenn man den "Pläne" Knopf drückt<br>

<?php profile_page_end();?>
</body>
</html>