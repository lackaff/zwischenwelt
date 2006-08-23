<?php
require_once("../lib.main.php");
require_once("../lib.bug.php");
Lock();


//gets a userid from a multitype form input
function getUserIDFromFormInput($input){
	if(!is_numeric($input))$input = sqlgetone("SELECT `id` FROM `user` WHERE `name`='".addslashes($input)."'");
	if(is_numeric($input) && $input > 0)return $input;
	return 0;
}

$canedit = $gUser->admin == 1 || (intval($gUser->flags) & kUserFlags_BugOperator) > 0;

if($canedit && isset($f_do_new)){
	$o = null;
	$o->topic = intval($t_topic);
	$o->creator = $gUser->id;
	$o->created = time();
	$o->name = "unbenannter Fehler";
	sql("INSERT INTO `bug` SET ".obj2sql($o));
}
if($canedit && isset($_FILES["img"])){
	$f = $_FILES["img"];
	$tmp = "../tmp/bug/";
	$md5 = md5(file_get_contents($f["tmp_name"]));
	$parts = pathinfo($f['name']);
	$ext = $parts["extension"];
	if (move_uploaded_file($f['tmp_name'],$tmp.$md5.".$ext")){
		sql("UPDATE `bug` SET `img`='".addslashes(kGfxServerPath."../tmp/bug/$md5.".$ext)."' WHERE `id`=".intval($f_bug));
	}
}
if($canedit && isset($f_do_save)){
	$o = sqlglobals();
	$o->assigned_user = getUserIDFromFormInput($f_i_assigned_user);
	$o->finder = getUserIDFromFormInput($f_i_finder);
	sql("UPDATE `bug` SET ".obj2sql($o)." WHERE `id`=".intval($f_bug));
}

$stats = BugStats();
$bugs = array();

$where = "";
if(isset($f_search))$where = "`name` LIKE '%".addslashes($f_search)."%' OR `text` LIKE '%".addslashes($f_search)."%' OR `desc` LIKE '%".addslashes($f_search)."%'";
if(isset($f_topic))$where = "`topic`=".intval($f_topic);
if(!empty($where))$bugs = sqlgettable("SELECT `id`,`name`,`topic`,`text`,`assigned_user`,`created`,`creator`,`prio`,`status` FROM `bug` WHERE $where ORDER BY `prio`");

if(isset($f_bug))$bug = new Bug($f_bug);

require_once("header.php"); 
?>
<table><tr>
<?php foreach($stats as $id=>$o){ ?>
<td><a href="<?=query("?sid=?&topic=$id")?>"><?=$gBugTopicText[$id]?></a> (<span style="color:red"><?=$o->unassigned?></span>|<span style="color:blue"><?=$o->assigned?></span>|<span style="color:green"><?=$o->fixed?></span>)</td>
<?php } ?>
</tr></table>
<?php if(isset($bug)){if($canedit){ ?>
	<hr>
	<form enctype="multipart/form-data" action="<?=query("?sid=?&bug=?")?>" method="post">
    <input type="hidden" name="MAX_FILE_SIZE" value="1048576">
    Bild hochladen: <input name="img" type="file">
    <input type="submit" value="upload">
	</form>
	<form method=post action="<?=query("?sid=?&bug=?")?>"><table border=1>
		<tr><th>#ID</th><td><?=$bug->id?></td></tr>
		<tr><th>Name</th><td><input type="text" name="i_name" value="<?=$bug->name?>"></td></tr>
		<tr><th>erstellt</th><td><?=date("j.m. G:i",$bug->created)?></td></tr>
		<tr><th>geschlossen</th><td><?=$bug->closed>0?date("j.m. G:i",$bug->closed):"-"?></td></tr>
		<tr><th>Ersteller</th><td><?=nick($bug->creator,"0")?></td></tr>
		<tr><th>Finder</th><td><input type="text" name="i_finder" value="<?=nick($bug->finder,"0")?>"></td></tr>
		<tr><th>zuständig</th><td><input type="text" name="i_assigned_user" value="<?=nick($bug->assigned_user,"0")?>"></td></tr>
		<tr><th>Prio</th><td><input type="text" name="i_prio" value="<?=$bug->prio?>"></td></tr>
		<tr><th>Status</th><td>
		<select name="i_status" size=1><?=PrintOptions($gBugStatusText,$bug->status)?></select>
		</td></tr>
		<tr><th>Bereich</th><td>
		<select name="i_topic" size=1><?=PrintOptions($gBugTopicText,$bug->topic)?></select>
		</td></tr>
		<tr><th>Position</th><td><input type="text" name="i_x" value="<?=$bug->x?>" size=4>,<input type="text" name="i_y" value="<?=$bug->y?>" size=4> <a href="<?=query("info.php?sid=?&x=$bug->x&y=$bug->y")?>">anzeigen</a></td></tr>
		<tr><th>Bild</th><td><input type="text" name="i_img" value="<?=$bug->img?>"> <a href="<?=$bug->img?>" target="_blank">anzeigen</a></td></tr>
		<tr><th colspan=2>Kurzinfo</th></tr>
		<tr><td colspan=2><textarea rows=5 cols=80 name=i_text><?=htmlentities($bug->text)?></textarea></td></tr>
		<tr><th colspan=2>Langinfo</th></tr>
		<tr><td colspan=2><textarea rows=15 cols=80 name=i_desc><?=htmlentities($bug->desc)?></textarea></td></tr>
		<tr><td colspan=2 align=center><input type=submit name=do_save value=speichern></td></tr>
	</table></form>
<?php } else { ?>
	<hr>
	<table border=1>
		<tr><th>#ID</th><td><?=$bug->id?></td></tr>
		<tr><th>Name</th><td><?=$bug->name?></td></tr>
		<tr><th>erstellt</th><td><?=date("j.m. G:i",$bug->created)?></td></tr>
		<tr><th>geschlossen</th><td><?=$bug->closed>0?date("j.m. G:i",$bug->closed):"-"?></td></tr>
		<tr><th>Ersteller</th><td><?=nick($bug->creator,"-")?></td></tr>
		<tr><th>Finder</th><td><?=nick($bug->finder,"-")?></td></tr>
		<tr><th>zuständig</th><td><?=nick($bug->assigned_user,"-")?></td></tr>
		<tr><th>Prio</th><td><?=$bug->prio?></td></tr>
		<tr><th>Status</th><td><?=$gBugStatusText[$bug->status]?></td></tr>
		<tr><th>Bereich</th><td><?=$gBugTopicText[$bug->topic]?></td></tr>
		<tr><th colspan=2>Kurzinfo</th></tr>
		<tr><td colspan=2><?=nl2br($bug->text)?></td></tr>
		<tr><th colspan=2>Langinfo</th></tr>
		<tr><td colspan=2><?=nl2br($bug->desc)?></td></tr>
	</table>
<?php }} ?>
<hr>
<form method=post action="<?=query("?sid=?")?>">nach <input type="text" name="search" value="<?php if(isset($f_search))echo $f_search;?>"> <input type=submit name=do_search value=suchen></form>
<table border=1>
	<tr>
		<th>#ID</th><th>Name</th><th>Info</th><th>Prio</th><th>erstellt</th><th>Status</th><th>zuständig</th>
	</tr>
<?php foreach($bugs as $b){ ?>
	<tr>
		<td><a href="<?=query("?topic=?&bug=$b->id&sid=?")?>"><?=$b->id?></a></td><td><a href="<?=query("?topic=?&bug=$b->id&sid=?")?>"><?=$b->name?></a></td><td><a href="<?=query("?topic=?&bug=$b->id&sid=?")?>"><?=nl2br($b->text)?></a></td>
		<td><?=$b->prio?></td><td><?=date("j.m. G:i",$b->created)?></td><td><?=$gBugStatusText[$b->status]?></td><td><?=nick($b->assigned_user,"-")?></td>
	</tr>
<?php } ?>
</table>
<?php if($canedit){ ?>
<hr>
<a href="<?=query("?sid=?&topic=?&do_new=1")?>">neuen Bug erstellen</a>
<?php } ?>
<?php require_once("footer.php"); ?>