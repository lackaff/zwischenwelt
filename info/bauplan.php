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
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<link href="http://fonts.googleapis.com/css?family=Bree+Serif" rel="stylesheet" type="text/css">
<link href="http://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="<?=BASEURL?>css/zwstyle_new_temp.css">
<title>Zwischenwelt - Building Plans</title>

</head>
<body>
<?php
include(BASEPATH."/menu.php");
?>


All <?=count($cps)?> plans:
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
	<input type="submit" name="cp_first" value="First">
	<input type="submit" name="cp_last" value="Last">
	<input type="submit" name="cp_abort" value="Cancel">
</form>

Select all of a type:<br>
<?php foreach ($types as $tid) {
	echo "<a href='".Query("?sid=?&type=".$tid)."'>";
	echo "<img border=1 src='".g($gBuildingType[$tid]->gfx,"we",0,$gUser->race)."'>";
	echo "</a>";
}?><br>


<br>
<br>
Also see <a href="<?=Query("kosten.php?sid=?")?>"><b>Expenses</b></a><br>
Selecting the "Plans" view on the map will also show
which buildings are planned.<br>

<?php profile_page_end();?>
</body>
</html>