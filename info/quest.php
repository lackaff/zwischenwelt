<?php
require_once("../lib.main.php");
Lock();

if (!$gUser->admin) profile_page_start("quest");

$gTimeRes = array((3600*24*7)=>"Wochen",(3600*24)=>"Tage",3600=>"Stunden",60=>"Minuten",1=>"Sekunden");
$gTimeResSing = array((3600*24*7)=>"Woche",(3600*24)=>"Tag",3600=>"Stunde",60=>"Minute",1=>"Sekunde");
function gettimeres ($dur) {
	if ($dur == 0) return 1;
	global $gTimeRes;
	foreach ($gTimeRes as $key => $name) 
		if (($dur % $key) == 0) return $key;
	return 1;
}
function printtimeres ($dur) {
	if ($dur <= 0) return "";
	$res = gettimeres($dur);
	$num = floor($dur/$res);
	global $gTimeResSing,$gTimeRes;
	return $num." ".(($num>1)?$gTimeRes[$res]:$gTimeResSing[$res]);
}



if ($gUser->admin) {
	$realtype_sample_params = array(1=>"8|9|10#0,0|1,0|2,0#0,5|0,-5#1");
	if (isset($f_newitem)) {
		sql("INSERT INTO `itemtype` SET ".arr2sql(array("gfx"=>"item/.png","name"=>"name","descr"=>"Edelstein")));
		$gItemType = sqlgettable("SELECT * FROM `itemtype`","id");
	}
	if (isset($f_saveallitem)) {
		foreach ($f_i_name as $key => $igno)
			$f_i_flags[$key] = isset($f_i_flags[$key])?array_sum($f_i_flags[$key]):0;
		ISaveAll("itemtype");
		$gItemType = sqlgettable("SELECT * FROM `itemtype`","id");
	}
	if (isset($f_delitem)) {
		foreach ($f_sel as $id) {
			sql("DELETE FROM `itemtype` WHERE `id` = ".intval($id)." LIMIT 1");
			sql("DELETE FROM `item` WHERE `type` = ".intval($id));
			echo mysql_affected_rows()." items vom typ $id gelöscht<br>";
		}
		$gItemType = sqlgettable("SELECT * FROM `itemtype`","id");
	}
	
	if (isset($f_newquest)) {
		$quest = false;
		$quest->start = ceil(time()/3600 + 1)*3600;
		$quest->dur = 3600*24;
		$quest->repeat = 3600*24*2;
		$quest->type = intval($f_i_type);
		$quest->flags = kQuestFlag_Delete_QuestItems_On_Finish | kQuestFlag_Delete_QuestArmy_On_Finish;
		$quest->name = "Questname";
		$quest->descr = "Beschreibungstext";
		$quest->params = $realtype_sample_params[realquesttype($quest->type)];
		sql("INSERT INTO `quest` SET ".obj2sql($quest));
		$f_editquest = mysql_insert_id();
	}
	if (isset($f_questid) && intval($f_questid) > 0) {
		$quest = sqlgetobject("SELECT * FROM `quest` WHERE `id` = ".intval($f_questid));
		QuestSplit($quest,$quest->id);
		if (isset($f_savequest)) {
			$o = sqlglobals();
			$o->start = parsetime($o->start);
			$o->flags = isset($o->flags)?array_sum($o->flags):0;
			$o->dur *= $f_durscale;
			$o->repeat *= $f_repeatscale;
			sql("UPDATE `quest` SET ".obj2sql($o)." WHERE `id` = ".intval($f_questid)." LIMIT 1");
		}
		if (isset($f_deletequestitems)) {
			sql("DELETE FROM `item` WHERE `quest` = ".intval($f_questid));
			echo mysql_affected_rows()." items gelöscht";
		}
		if (isset($f_deletequestarmies)) {
			sql("DELETE FROM `army` WHERE `quest` = ".intval($f_questid));
			echo mysql_affected_rows()." armeen gelöscht";
		}
		if (isset($f_startquest)) { 
			if ($quest->running) Quest_Timeout($quest);
			Quest_Start($quest);
			sql("UPDATE `quest` SET `running` = 1 , `start` = ".time()." WHERE `id` = ".intval($f_questid));
		}
		if (isset($f_deletequest_sure) && isset($f_deletequest) && $f_deletequest_sure == 1) {
			if ($quest->running) Quest_Timeout($quest);
			sql("DELETE FROM `quest` WHERE `id` = ".intval($f_questid));
		}
		if (isset($f_timeoutquest)) Quest_Timeout($quest);
	}
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 transitional//EN"
   "http://www.w3.org/TR/html4/transitional.dtd">
<html>
<head>
<link rel="stylesheet" type="text/css" href="<?=GetZWStylePath()?>">
<title>Zwischenwelt - Quests</title>

</head>
<body>
<?php
include("../menu.php");
?>


<?php if (!isset($f_notnormal)) {?>
<h3>Quests</h3>
<?php $quests = sqlgettable("SELECT * FROM `quest` WHERE `running` = 1 ORDER BY `flags` & ".kQuestFlag_Permanent." DESC,`start`"); if (count($quests) > 0) {?>
wdh. = wiederholung in
<?php foreach ($quests as $o) { $repeatres = gettimeres($o->repeat);?>
	<table border=1 cellpadding=2 cellspacing=0>
	<tr>
		<th>start</th>
		<th>ende</th>
		<th>wdh.</th>
		<th>name</th>
		<th>typ</th>
		<?php foreach($gRes as $n=>$f) if ($o->{$f} > 0) echo '<th align=center><img alt="'.$f.'" src="'.g('res_'.$f.'.gif').'"></th>'; ?>
		<?php if ($o->rewarditemtype > 0) {?>
		<th colspan=2>Belohnung</th>
		<?php }?>
	</tr>
	<tr>
		<?php if (intval($o->flags) & kQuestFlag_Permanent) {?>
			<td nowrap colspan="3">permanent</td>
		<?php } else {?>
			<td nowrap><?=date("d.m H:i",$o->start)?></td>
			<td nowrap><?=($o->dur<=0)?"unbegrenzt":date("d.m H:i",$o->start+$o->dur)?></td>
			<td nowrap><?=printtimeres($o->repeat)?></td>
		<?php } // endif?>
		<td nowrap><?=pos2txt($o->x,$o->y,$o->name)?></td>
		<td align="right"><?=$gQuestTypeNames[$o->type]?></td>
		<?php foreach ($gRes as $n=>$f) if ($o->{$f} > 0) {?>
			<td align="right"><?=$o->{$f}?></td>
		<?php }?>
		<?php if ($o->rewarditemtype > 0) {?>
			<td nowrap><img src="<?=g($gItemType[$o->rewarditemtype]->gfx)?>" alt="."><?=$gItemType[$o->rewarditemtype]->name?></td>
			<td nowrap><?=($o->rewarditemamount>1)?floor($o->rewarditemamount):""?></td>
		<?php }?>
	</tr>
	<tr>
		<td colspan="11"><?=magictext($o->descr)?></td>
	</tr>
	</table>
	<br>
	<br>
<?php }?>
<?php } // endif count(quests) > 0?>

<pre>
Alle Gegenstände müssen in einer Armee sein beim abgeben.
Um sie abzugeben einfach mit der Armee das ZielFeld betreten.
Wenn mehrere Spieler gesammelt haben, müssen sie die Gegenstände ablegen,
und dann mit einer Armee aufsammeln, dann könnt ihr sie mit dieser Armee abgeben.
Man kann sich natürlich auch darum streiten, wenn eine Armee getötet wird,
fallen die Gegenstände, die sie dabei hatte auf den Boden und können vom Sieger eingesammelt werden.
Bei QuestEnde verschwinden alle zu diesem Quest gehörigen Gegenstände.
Das Lagerlimit kann für die Belohnung überschritten werden, 
der Überschuss geht dann ins Gildenlager, solange bis das auch voll ist.
Den Belohnungsgegenstand erhält die Armee die die Questgegenstände abgegeben hat.
Viel Spass ^^
</pre>
<?php }?>

<?php if ($gUser->admin) {?>
<br><br><hr>admin-bereich, Serverzeit : <?=date("d.m H:i")?>
<ul>
<li><a href="<?=Query("?sid=?&notnormal=1&actvieplayers=1")?>">50 aktive spieler</a></li>
<li><a href="<?=Query("?sid=?&notnormal=1&actvieplayers=2")?>">50 aktive neue spieler</a></li>
<li><a href="<?=Query("?sid=?&notnormal=1&guildlog=1")?>">guildlog</a></li>
<li><a href="<?=Query("?sid=?&notnormal=1&triggerlog=1")?>">triggerlog</a></li>
<li><a href="<?=Query("?sid=?&notnormal=1&triggerlog=2")?>">triggerlog(all)</a></li>
<li><a href="<?=Query("?sid=?&notnormal=1&questcontrol=1")?>">questcontrol</a></li>
<li><a href="<?=Query("?sid=?&notnormal=1&itemcontrol=1")?>">Gegenstände</a></li>
<li><a href="<?=Query("?sid=?&notnormal=1&repairhellhole=1")?>">repairhellhole</a></li>
</ul>

<?php if (isset($f_actvieplayers)) {?>
	<?php 
	if ($f_actvieplayers == 2)
			$them = sqlgettable("SELECT * FROM `user` WHERE `pop` > 0 AND `pop` < 200 ORDER BY `lastlogin` DESC LIMIT 50");
	else	$them = sqlgettable("SELECT * FROM `user` WHERE 1 ORDER BY `lastlogin` DESC LIMIT 50");
	?>
	<table>
	<tr>
		<th>lastlogin</th>
		<th>Name</th>
		<th>Pop</th>
		<th>Pos</th>
	</tr>
	<?php foreach ($them as $o) { $hq = sqlgetobject("SELECT * FROM `building` WHERE `type` = 1 AND `user` = ".$o->id);?>
	<tr>
		<td><?=date("d.m H:i",$o->lastlogin)?></td>
		<td>&nbsp;<?=$o->name?></td>
		<td><?=$o->pop?></td>
		<td><?=pos2txt($hq->x,$hq->y)?></td>
		<td><?=$o->pop?></td>
	</tr>
	<?php }?>
	</table>

<?php }?>

<?php if (isset($f_guildlog)) {?>
	<?php 
		$guildlogs_page = 50;
		if (!isset($f_guildlogs_start)) $f_guildlogs_start = 0;
		$guildlogs_max = sqlgetone("SELECT COUNT(*) FROM `guildlog`");
		$guildlogs = sqlgettable("SELECT * FROM `guildlog` ORDER BY `time` DESC LIMIT ".intval($f_guildlogs_start).",".$guildlogs_page);
		function guild_echo_user($userid) {
			if ($userid > 0 && ($user = sqlgetobject("SELECT * FROM `user` WHERE `id` = ".intval($userid)))) {
				$userhq = sqlgetobject("SELECT * FROM `building` WHERE `user` = ".$user->id." AND `type` = 1");
				echo ($userhq)?pos2txt($userhq->x,$userhq->y,$user->name):$user->name;
			}
		}
	?>
	<?php if (count($guildlogs) > 0) {?>
		<h3>GildenLog </h3>
		<a href="<?=Query("?sid=?&notnormal=?&quildlog=?&guildlogs_start=".max(0,$f_guildlogs_start-$guildlogs_page))?>">&lt;&lt;</a>
		<?=floor($f_guildlogs_start/$guildlogs_page)+1?> / <?=floor($guildlogs_max/$guildlogs_page)+1?>
		<a href="<?=Query("?sid=?&notnormal=?&quildlog=?&guildlogs_start=".min($guildlogs_max-1,$f_guildlogs_start+$guildlogs_page))?>">&gt;&gt;</a>
		<table border=1 cellspacing=0>
		<tr>
			<th>Zeit</th>
			<th>Was</th>
			<th>Wo</th>
			<th colspan="2">Angreifer</th>
			<th colspan="2">Verteidiger</th>
			<th>&nbsp;</th>
		</tr>
		<?php foreach ($guildlogs as $o) {?>
		<tr>
			<td nowrap><?=date("d.m H:i",$o->time);?></td>
			<td nowrap><?=$o->trigger?></td>
			<td nowrap><?=pos2txt($o->x,$o->y)?></td>
			<td nowrap><?php guild_echo_user($o->user1)?></td>
			<td nowrap><?=($o->guild1 > 0)?sqlgetone("SELECT `name` FROM `guild` WHERE `id` = ".$o->guild1):""?></td>
			<td nowrap><?php guild_echo_user($o->user2)?></td>
			<td nowrap><?=($o->guild2 > 0)?sqlgetone("SELECT `name` FROM `guild` WHERE `id` = ".$o->guild2):""?></td>
			<td nowrap><?=$o->what?></td>
			<td nowrap><?=$o->count?></td>
		</tr>
		<?php }?>
		</table>
	<?php } // count($guildlogs) > 0 ?>
<?php }?>




<?php if (isset($f_triggerlog)) {?>
	<?php 
		$typecolor = array("PickUpItem"=>"#FF8800","DropItem"=>"#FF8800",
			"Quest_Start"=>"green","Quest_Finish"=>"green","Quest_Timeout"=>"green",
			"CollectTerrain"=>"brown",
			"CastSpell(3)"=>"red","FightStart(p)"=>"red","PillageStart"=>"red","SiegeStart"=>"red",
			"LagerTransfer"=>"brown","TeleportArmy"=>"blue");
		$pagesize = 100;
		$where = "1";
		if ($f_triggerlog == 1) $where = "`trigger` IN ('CastSpell(3)','PickUpItem','DropItem','Quest_Finish','FightStart(p)','PillageStart','SiegeStart','TeleportArmy')";
		if ($f_triggerlog == 2) $triggertypes = sqlgetonetable("SELECT `trigger` FROM `triggerlog` GROUP BY `trigger`");
		if (isset($f_triggertypelist)) {
			if (!is_array($f_triggertypelist)) $f_triggertypelist = explode(",",$f_triggertypelist);
			$where = "`trigger` IN (''";
			foreach ($f_triggertypelist as $tt)
				$where .= ",'".addslashes($tt)."'";
			$where .= ")";
			$f_triggertypelist = implode(",",$f_triggertypelist);
		}
		$max = sqlgetone("SELECT COUNT(`id`) FROM `triggerlog` WHERE $where");
		if (!isset($f_start)) $f_start = 0;
		if(!empty($f_searchstring))$where .= " AND `what` LIKE '%".addslashes($f_searchstring)."%'";
		$f_start = min(floor($max / $pagesize) * $pagesize,intval($f_start));
		$gTriggerLog = sqlgettable("SELECT * FROM `triggerlog` WHERE $where ORDER BY `time` DESC LIMIT ".intval($f_start).",$pagesize");
	?>
	<h3>triggerlog</h3>
	<form method="post" action="<?=Query("?sid=?&notnormal=?&triggerlog=?&start=?")?>">
		<?php foreach ($triggertypes as $o) {?>
		<input type="checkbox" name="triggertypelist[]" value="<?=$o?>"><?=$o?>
		<?php } // endforeach?>
		Search: <input type=text name=searchstring value="<?=$f_searchstring?>">
		<input type="submit" value="suchen">
	</form>
	<a href="<?=Query("?sid=?&notnormal=?&triggerlog=?&triggertypelist=?&start=".max(0,$f_start-$pagesize))?>">&lt;&lt;</a>
	<?=floor($f_start/$pagesize)+1?> / <?=floor($max/$pagesize)+1?>
	<a href="<?=Query("?sid=?&notnormal=?&triggerlog=?&triggertypelist=?&start=".($f_start+$pagesize))?>">&gt;&gt;</a>
	<table border=1 cellpadding=2 cellspacing=0>
	<tr>
		<th>time</th>
		<th>trigger</th>
		<th>what</th>
	</tr>
	<?php foreach ($gTriggerLog as $o) { $color = isset($typecolor[$o->trigger])?$typecolor[$o->trigger]:"black";?>
	<tr>
		<td nowrap><?=date("d.m H:i",$o->time);?></td>
		<td nowrap><?=pos2txt($o->x,$o->y,"<font color='".$color."'>".$o->trigger."</font>")?></td>
		<td nowrap><?=magictext($o->what)?></td>
	</tr>
	<?php }?>
	</table>
<?php }?>


<?php if (isset($f_questcontrol)) {?>
	<h3>Quest-Control-Center ;)</h3>
	<?php $quests = sqlgettable("SELECT * FROM `quest` ORDER BY `id`");?>
	<?php $itemtypechoice = array(0=>arr2obj(array("id"=>0,"name"=>"--nix--")))+$gItemType; ?>
	<?php foreach ($quests as $o) { $durres = gettimeres($o->dur); $repeatres = gettimeres($o->repeat);?>
		<form method="post" action="<?=Query("?sid=?&notnormal=?&questcontrol=?")?>">
		<table border=1>
		<tr>
			<th>start</th>
			<th>ende</th>
			<th>wdh.</th>
			<th>name</th>
			<th>typ</th>
			<?php foreach($gRes as $n=>$f)echo '<th align=center><img alt="'.$f.'" src="'.g('res_'.$f.'.gif').'"></th>'; ?>
			<th>Belohnung</th>
		</tr>
		<tr>
			<td nowrap><input type="text" name="i_start" value="<?=date("d.m.Y H:i",$o->start)?>"></td>
			<td nowrap>
				<input type="text" name="i_dur" value="<?=$o->dur/$durres?>" style='width:30px'>
				<select name="durscale"><?=PrintOptions($gTimeRes,$durres)?></select>
			</td>
			<td nowrap>
				<input type="text" name="i_repeat" value="<?=$o->repeat/$repeatres?>" style='width:30px'>
				<select name="repeatscale"><?=PrintOptions($gTimeRes,$repeatres)?></select>
			</td>
			<td nowrap><?=IText($o,"name","style='width:80px'")?><?=IText($o,"x","style='width:40px'")?><?=IText($o,"y","style='width:40px'")?></td>
			<td align="right"><select name="i_type"><?=PrintOptions($gQuestTypeNames,$o->type)?></select></td>
			<td align="right"><?=IText($o,"lumber","style='width:30px'")?></td>
			<td align="right"><?=IText($o,"stone","style='width:30px'")?></td>
			<td align="right"><?=IText($o,"food","style='width:30px'")?></td>
			<td align="right"><?=IText($o,"metal","style='width:30px'")?></td>
			<td align="right"><?=IText($o,"runes","style='width:30px'")?></td>
			<td nowrap><select name="i_rewarditemtype"><?=PrintObjOptions($itemtypechoice,"id","name",$o->rewarditemtype)?></select></td>
			<td align="right"><?=IText($o,"rewarditemamount","style='width:30px'")?></td>
		</tr>
		<tr><td colspan="11">
			<?=IText($o,"params","style='width:100%'");?>
		</td></tr>
		<tr><td colspan="11">
			<?=ereg_replace("([-+0-9]+),([-+0-9]+)","<a target='map' href='".Query("../".kMapScript."?sid=?")."&x=\\1&y=\\2'>\\1,\\2</a>",$o->params)?>
		</td></tr>
		<tr><td colspan="11">
			<?=ITextArea($o,"descr","style='width:100%' rows=5");?>
		</td></tr>
		<tr><td colspan="11">
			<?=magictext($o->descr)?>
		</td></tr>
		<tr><td colspan="11">
			running=<?=$o->running?>,realtype=<?=$gQuestTypeNames[realquesttype($o->type)]?> |
			<?=IFlagCheck($o,"flags",kQuestFlag_Delete_QuestItems_On_Finish)?>Delete_QuestItems_On_Finish
			<?=IFlagCheck($o,"flags",kQuestFlag_Delete_QuestArmy_On_Finish)?>Delete_QuestArmy_On_Finish
			<?=IFlagCheck($o,"flags",kQuestFlag_Permanent)?>Permanent
			<?=IFlagCheck($o,"flags",kQuestFlag_RepeatOnFinish)?>RepeatOnFinish
			<br>
			QuestItems(<?=intval(sqlgetone("SELECT COUNT(`id`) FROM `item` WHERE `quest` = ".$o->id))?>) :
				<?php $items = sqlgettable("SELECT * FROM `item` WHERE `quest` = ".$o->id);?>
				<?php foreach ($items as $i) echo "<br>".magictext("(i".$i->type.")".item2txt($i));?>
			<br>
			QuestArmies(<?=intval(sqlgetone("SELECT COUNT(`id`) FROM `army` WHERE `quest` = ".$o->id))?>) : not_implemented
			<br>
			<input type="hidden" name="questid" value="<?=$o->id?>">
			<input type="submit" name="savequest" value="save">
			<input type="submit" name="deletequestitems" value="deleteitems">
			<input type="submit" name="deletequestarmies" value="deletearmies">
			<input type="submit" name="startquest" value="start">
			<input type="submit" name="timeoutquest" value="timeout">
			| <input type="submit" name="deletequest" value="delete">
			  <input type="checkbox" name="deletequest_sure" value="1">sure
		</td></tr>
	</table>
	</form>
	<br><br>
	<?php } // endforeach?>
	
	<form method="post" action="<?=Query("?sid=?&notnormal=?&questcontrol=?")?>">
	<select name="i_type"><?=PrintOptions($gQuestTypeNames,$o->type)?></select>
	<input type="submit" name="newquest" value="newquest">
	</form>

<h3>Doku</h3>
<pre>
(ja sowas schreib ich auch manchmal;)
Questtypen : 
	Monster-Jagt : noch nicht implementiert
	Rettung , Transport , Sammeln , Stehlen   sind vom code her ein und dasselbe (realtype=Rettung)

magictext Kürzel :
Koordinaten Angaben in dem Beschreibungstext mit der form (-120,44) werden automatisch clickbar gemacht.
(i44) wird durch das Bild von item typ 44 ersetzt.
(u22) wird durch das Bild von unit typ 22 ersetzt.
Dasselbe gilt übrigends ab sofort für Schild-Texte.

Der Button startquest setzt den startzeitpunkt auf jetzt, und startet das quest sofort.
Die beiden nummern nach dem Namen sind die Koordinaten auf die gesprungen wird, wenn man auf den Questnamen clickt.
Zumal noch keine armeen von den quests selber generiert werden ist Delete_QuestArmy_On_Finish im moment nutzlos.
Hab übrigends einen teleportarmy knopf unten in info eingebaut, 
der löst auch questtrigger aus, somit kann man damit schnell durchcheaten.

params beschreibung :
die einzelnen parameter sind mit # getrennt
ist ein parameter eine liste (x_list), so sind die einzelnen einträge mit | getrennt
x und y koordinaten von positionen sind mit , getrennt
11,22|33,44|55,66  sind die drei positionen (11,22) (33,44) (55,66)

typ = Rettung , Transport , Sammeln , Stehlen :
	paramsyntax = itemtype_list#startpos_list#endpos_list#itemsetmode
	beispiel = 8|9|10#0,0|1,0|2,0#0,5|0,-5#1
	itemsetmode kann 0 oder 1 sein
	itemsetmode 0 "zufällig" : 
		bei queststart wird zufällig ein item aus itemtype_list generiert
		es wird auf eine zufällig ausgewählte position aus startpos_list gesetzt
		es genügt ein item auf irgendeine der positionen aus endpos_list zu bringen, um das quest zu erfüllen
	itemsetmode 1 "reihenfolge" : 
		bei queststart wird jedes der items in itemtype_list auf der entsprechenden position in startpos_list gesetz
		man muss alle items sammeln und auf irgendeine der positionen aus endpos_list zu bringen, um das quest zu erfüllen
</pre>

<?php } // endif questcontrol?>



<?php if (isset($f_repairhellhole)) {
	$hellholes = sqlgettable("SELECT * FROM `hellhole` WHERE `ai_type` = 0");
	echo "repairing hellholes, ".count($hellholes)." total<br>";
	foreach ($hellholes as $o) {
		$building = sqlgetobject("SELECT * FROM `building` WHERE `x` = ".$o->x." AND `y` = ".$o->y);
		if (!$building) {
			echo "hellhole at ".pos2txt($o->x,$o->y)." had no building<br>";
			sql("INSERT INTO `building` SET `type` = ".kBuilding_Hellhole.", `hp` = 100 , `x` = ".$o->x." , `y` = ".$o->y);
		}
	}
	echo "<br>";
}?>


<?php if (isset($f_itemcontrol)) {?>
	<h3>Gegenstände</h3>
	<?php $items = sqlgetgrouptable("SELECT * FROM `item`","type");?>
	<form method="post" action="<?=Query("?sid=?&notnormal=?&itemcontrol=?")?>">
	<table border=1>
	<tr>
		<th>type</th>
		<th>gfx</th>
		<th>gfx</th>
		<th>name</th>
		<th>descr</th>
		<th>num</th>
		<th>pos</th>
	</tr>
	<?php foreach($gItemType as $it) {?>
	<tr>
		<td nowrap><input type="checkbox" name="sel[]" value="<?=$it->id?>"><?=$it->id?></td>
		<td><img src="<?=g($it->gfx)?>" alt="."></td>
		<td><?=IText($it,"gfx","style='width:160px'","","i_",$it->id)?></td>
		<td nowrap><?=IText($it,"name","style='width:90px'","","i_",$it->id)?></td>
		<td nowrap><?=IText($it,"descr","style='width:130px'","","i_",$it->id)?></td>
		<td><?=isset($items[$it->id])?count($items[$it->id]):0?></td>
		<td nowrap>
			<?php $count = 0;$max = 10; if (isset($items[$it->id])) foreach ($items[$it->id] as $o) { if($count<$max)++$count;else break; ?>
				<?=($o->quest>0)?(" Q:".$o->quest):" "?>
				<?php if ($o->army > 0) $o = sqlgetobject("SELECT * FROM `army` WHERE `id`=".$o->army); ?>
				<?=pos2txt($o->x,$o->y)?>
			<?php } ?>
		</td>
	</tr>
	<tr>
		<td></td>
		<td colspan=6>
			<?php foreach ($gItemFlagNames as $flag => $name) {?>
			<?=IFlagCheck($it,"flags",$flag,"",0,"i_",$it->id)?><?=$name?>&nbsp;
			<?php }?>
		</td>
	</tr>
	<?php } ?>
	</table>
	<input type="submit" name="delitem" value="del">
	<input type="submit" name="newitem" value="new">
	<input type="submit" name="saveallitem" value="saveall">
	</form>
<?php } else {?>
	<?php foreach($gItemType as $it) {?>
	<?=$it->id?><img src="<?=g($it->gfx)?>" alt=".">
	<?php } ?>
	<br>
	<?php foreach($gUnitType as $o) if ($o->gfx) {?>
	<?=$o->id?><img src="<?=g($o->gfx)?>" alt=".">
	<?php } ?>
<?php } // endif itemcontrol?>

<?php } /*endif user->admin*/?>

<?php if (!$gUser->admin) profile_page_end();?>
</body>
</html>