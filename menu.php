<?php
$agent = $_SERVER["HTTP_USER_AGENT"];
$newmenu = intval($gUser->flags) & kUserFlags_DropDownMenu;
//!(strpos($agent,"Firefox")===false);

$hq = sqlgetobject("SELECT * FROM `building` WHERE `user` = ".$gUser->id." AND `type` = ".kBuilding_HQ);
$cpost = sqlgetobject("SELECT COUNT(m.`id`) as num,mf.id as folder,mf.type as type FROM `message` m, `message_folder` mf WHERE m.`to` = ".$gUser->id." AND m.`status` = 1 AND m.folder=mf.id group by mf.id");
if($gUser->guild > 0 && (sqlgetone("SELECT `founder` FROM `guild` WHERE `id`=".$gUser->guild) == $gUser->id || $gUser->guildstatus % kGuildAdmin==0))
	$gpost = sqlgetone("SELECT COUNT(`id`) FROM `guild_request` WHERE `guild`=".$gUser->guild);
else $gpost = null;
$cgpost= sqlgetone("SELECT COUNT(`id`) FROM `guild_forum_read` WHERE `user`=".$gUser->id);

if($cpost->num == 0)$newpost = false;
else $newpost = true;
if($gpost > 0 || $cgpost > 0)$newguild = true;
else $newguild = false;

if($newmenu){
	$root=sqlgetobject("SELECT `id`,`name` FROM `message_folder` WHERE `type`=".kFolderTypeRoot." AND `parent`=0 AND `user`=".$gUser->id);
	$sent=sqlgetobject("SELECT `id`,`name` FROM `message_folder` WHERE `type`=".kFolderTypeSent." AND `parent`=0 AND `user`=".$gUser->id);
	$berichte=sqlgetobject("SELECT `id`,`name` FROM `message_folder` WHERE `type`=".kFolderTypeExtra." AND `parent`=0 AND `user`=".$gUser->id);

?>
<!-- ########################### NEUES MENU ################################# -->
<table border=0 cellpadding=1 cellspacing=1 width="100%">
<tr>
	<?php foreach($gRes as $n=>$f) {
		$alt = $n.": ".kplaintrenner(floor(max(0,$gUser->$f)))."/".kplaintrenner(floor($gUser->{"max_".$f}));
		?>
		<td align=left width="15%">
		<a href="<?=Query("../info/waren.php?sid=?&t=".(isset($gRes2ItemType)?$gRes2ItemType[$f]:"?"))?>">
		<img border=0 title="<?=$alt?>" alt="<?=$alt?>" src="<?=g("res_$f.gif")?>"></a>
		<?=kplaintrenner(floor(max(0,$gUser->$f)))?>
		</td>
	<?php }
	$alt = "Bevölkerung: ".kplaintrenner(floor(max(0,$gUser->pop)))."/".kplaintrenner(floor($gUser->maxpop));
	?>
	<td align=left width="20%">
		<img alt="pop" alt="<?=$alt?>" title="<?=$alt?>" src="<?=g("pop-r%R%.png","","",$gUser->race)?>">
		<?=kplaintrenner(floor(max(0,$gUser->pop)))?>
	</td>
	<td rowspan=3>
		<table border=0 cellpadding=0 cellspacing=0>
			<tr>
				<td align=center valign=middle><img src="<?=g($gTimeGfx)?>" alt="<?=$gTimeStr?>" title="<?=$gTimeStr?>"></td>
				<td align=center valign=middle><a href="#" onclick="javascript:window.open('<?=query("../info/note.php?sid=?")?>', 'note', 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=1,width=400,height=400');"><img border=0 src="<?=g("note.png")?>" alt="Notiz" title="Notiz"></a></td>
			</tr><tr>
				<td align=center valign=middle><img src="<?=g($gWeatherGfx[$gWeather])?>" alt="<?=$gWeatherType[$gWeather]?>" title="<?=$gWeatherType[$gWeather]?>"></td>
				<td align=center valign=middle>
				<?php if ($gUser->admin == 1) { $o = sqlgetobject("SELECT * FROM `building` WHERE `type` = 10");?>
					<a href="<?=Query("../info/info.php?sid=?&x=".$o->x."&y=".$o->y)?>"><img src="<?=g("icon/admin.png")?>" alt="zur Entwickleranstalt springen" title="zur Entwickleranstalt springen" border=0></a>
				<?php } else { ?>
					<a href="<?=query("msg.php?show=compose&to=Admin&sid=?")?>"><img border=0 src="<?=g("icon/help2.png")?>" alt="Nachricht an die Admins schreiben" title="Nachricht an die Admins schreiben"></a>
				<?php } ?>
				</td>
			</tr>
		</table>
	</td>
</tr><tr>
	<?php foreach($gRes as $n=>$f){ echo '<td height=4>'; DrawBar(max(0,$gUser->$f),$gUser->{"max_".$f},"green","#eeeeee",true); echo '</td>'; } ?>
	<td height=4><?php DrawBar(max(0,$gUser->pop),$gUser->maxpop,"green","#eeeeee",true) ?></td>
</tr><tr>
	<td colspan=6>
	<!-- drop down menu start -->
	
	<div id="mainnaviborder">
	<ul id="mainnavi">
		<li><?php if ($hq) {?><a href="<?=Query("../info/info.php?sid=?&x=".$hq->x."&y=".$hq->y)?>">Haupthaus</a><?php } else {?>Haupthaus<?php }?>
			<ul>
				<li><a href="<?=query("../info/summary.php?sid=?")?>">Überblick</a></li>
				<li><a href="<?=query("../info/summary_units.php?sid=?")?>">Einheiten</a></li>
				<li><a href="<?=query("../info/summary_buildings.php?sid=?")?>">Gebäude</a></li>
				<li><a href="<?=query("../info/kosten.php?sid=?")?>">Kosten</a></li>
				<li><a href="<?=query("../info/bauplan.php?sid=?")?>">Baupläne</a></li>
				<li><a href="<?=query("../info/diplo.php?sid=?")?>">Diplom.</a></li>
				<li><a href="<?=query("../info/kampfsim.php?sid=?")?>">KampfSim</a></li>
				<li><a href="<?=query("../info/tech.php?sid=?")?>">Forschung</a></li>
				<li><a href="<?=query("../info/waren.php?sid=?")?>">Waren</a></li>
				<li><a href="<?=query("../info/profile.php?sid=?")?>">Einstell.</a></li>
			</ul>
		</li>
		<li><a <?php if($newpost)echo "id=\"new\"";?> href="<?=query("../info/msg.php?sid=?&folder=".$cpost->folder)?>">Post</a>
			<ul>
				<li><a href="<?=query("../info/msg.php?sid=?&show=compose")?>">Neu</a></li>
				<li><a href="<?=query("../info/msg.php?sid=?&show=content&folder=".$root->id)?>">Eingang</a></li>
				<li><a href="<?=query("../info/msg.php?sid=?&show=content&folder=".$sent->id)?>">Ausgang</a></li>
				<li><a href="<?=query("../info/msg.php?sid=?&show=content&folder=".$berichte->id)?>">Berichte</a></li>
				<li><a href="<?=query("../info/msg.php?sid=?&show=foldertree")?>">Einstell.</a></li>
			</ul>
		</li>
		<li><a href="<?=query("../stats/gen_pts.php?sid=?")?>">Highscore</a>
			<ul>
				<li><a href="<?=query("../stats/gen_pts.php?sid=?&what=p")?>">Spieler (totale Punkte)</a></li>
				<li><a href="<?=query("../stats/gen_pts.php?sid=?&what=pnm")?>">Spieler (ohne Militär)</a></li>
				<li><a href="<?=query("../stats/gen_pts.php?sid=?&what=pom")?>">Spieler (nur Militär)</a></li>
				<li><a href="<?=query("../stats/population.php?sid=?")?>">Bevölkerung</a></li>
				<li><a href="<?=query("../stats/stats.php?sid=?")?>">Stats</a></li>
				<li><a href="<?=query("../stats/gen_guild_pts.php?sid=?&what=g")?>">Gilden (totale Punkte)</a></li>
				<li><a href="<?=query("../stats/gen_guild_pts.php?sid=?&what=gnm")?>">Gilden (ohne Militär)</a></li>
				<li><a href="<?=query("../stats/gen_guild_pts.php?sid=?&what=gom")?>">Gilden (nur Militär)</a></li>
				<li><a href="<?=query("../stats/armeen.php?sid=?&")?>">Armeen</a></li>
			</ul>
		</li>
		<li><a target="_blank" href="http://zwischenwelt.org/wiki/">Hilfe</a>
			<ul>
				<li><a target="_blank" href="http://zwischenwelt.org/forum/">Forum</a></li>
				<li><a target="_blank" href="http://zwischenwelt.org/wiki/">Wiki</a></li>
				<li><a target="_blank" href="http://zwischenwelt.milchkind.net/zwwiki/index.php/FAQ">FAQ</a></li>
				<li><a href="<?=query("../info/bug.php?sid=?")?>">Bug</a></li>
			</ul>
		</li>
		<li><a href="<?=query("../info/quest.php?sid=?")?>">Quest</a>
			<?php if($gUser->admin){ ?><ul>
				<li><a href="<?=query("../info/quest.php?sid=?&notnormal=1&actvieplayers=1")?>">50 aktive Spieler</a></li>
				<li><a href="<?=query("../info/quest.php?sid=?&notnormal=1&actvieplayers=2")?>">50 aktive neue Spieler</a></li>
				<li><a href="<?=query("../info/quest.php?sid=?&notnormal=1&guildlog=1")?>">GuildLog</a></li>
				<li><a href="<?=query("../info/quest.php?sid=?&notnormal=1&triggerlog=1")?>">TriggerLog</a></li>
				<li><a href="<?=query("../info/quest.php?sid=?&notnormal=1&triggerlog=2")?>">TriggerLog(all)</a></li>
				<li><a href="<?=query("../info/quest.php?sid=?&notnormal=1&questcontrol=1")?>">QuestControl</a></li>
				<li><a href="<?=query("../info/quest.php?sid=?&notnormal=1&itemcontrol=1")?>">Gegenstände</a></li>
				<li><a href="<?=query("../info/quest.php?sid=?&notnormal=1&repairhellhole=1")?>">RepairHellhole</a></li>
			</ul>
			<?php } ?>
		</li>
		<li><a <?php if($newguild)echo "id=\"new\"";?> href="<?=query("../info/guild.php?sid=?")?>">Gilde</a>
			<ul>
				<li><a href="<?=query("../info/guild_forum.php?sid=?")?>">Forum</a></li>
				<li><a href="<?=query("../info/guild_admin.php?sid=?")?>">Verwaltung</a></li>
			</ul>
		</li>
		<li><a target="_parent" href="<?=query("../logout.php?sid=?")?>">Logout</a></li>
	</ul>
	</div>
	<!-- ende -->
	</td>
</tr>
</table>
<?php } else { ?>
<!-- ########################### ALTES MENU ################################# -->
<table border=0 cellspacing=0 width="100%">
<tr><td valign="top">
	<table border=0 cellspacing=0 cellpadding=1 width="100%">
	<tr>
	<?php foreach($gRes as $n=>$f) {?>
		<td align=center width="15%">
		<a href="<?=Query("../info/waren.php?sid=?&t=".(isset($gRes2ItemType)?$gRes2ItemType[$f]:"?"))?>">
		<img border=0 alt="<?=$f?>" src="<?=g("res_$f.gif")?>">
		</a></td>
	<?php }?>
	<td align=center width="15%"><img alt="pop" src="<?=g("pop-r%R%.png","","",$gUser->race)?>"></td>
	<?php if ($gUser->admin == 1) { $o = sqlgetobject("SELECT * FROM `building` WHERE `type` = 10");?>
	<td align=center width="15%"><a href="<?=Query("../info/info.php?sid=?&x=".$o->x."&y=".$o->y)?>">A</a></td>
	<?php }?>
	</tr>
	<tr>
	<?php foreach($gRes as $n=>$f)echo '<td align="right">'.ktrenner(floor(max(0,$gUser->$f))).'</td>'; ?>
	<td align="right"><div style='font-family:verdana;'><?=ktrenner(floor(max(0,$gUser->pop)))?></div></td>
	</tr>
	<tr>
	<?php foreach($gRes as $n=>$f)echo '<td style="color:#a0a0a0" align="right">'.ktrenner($gUser->{"max_".$f},"#a0a0a0").'</td>'; ?>
	<td style="color:#a0a0a0" align="right"><?=ktrenner($gUser->maxpop,"#a0a0a0")?></td>
	</tr>
	<tr height="7">
	<?php foreach($gRes as $n=>$f){ echo '<td>'; DrawBar(max(0,$gUser->$f),$gUser->{"max_".$f}); echo '</td>'; } ?>
	<td><?php DrawBar(max(0,$gUser->pop),$gUser->maxpop) ?></td>
	</tr>
	</table>
</td>
<td align=left valign=top>
	<table border=0 cellpadding=1 cellspacing=1>
		<tr><td align=center><a href="#" onclick="javascript:window.open('<?=query("../info/note.php?sid=?")?>', 'note', 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=1,width=400,height=400');"><img border=0 src="<?=g("note.png")?>" alt="Notiz" title="Notiz"></a></td></tr>
		<tr><td align=center><img src="<?=g($gTimeGfx) ?>" alt="<?=$gTimeStr?>" title="<?=$gTimeStr?>"></td></tr>
		<tr><td align=center><img src="<?=g($gWeatherGfx[$gWeather])?>" alt="<?=$gWeatherType[$gWeather]?>" title="<?=$gWeatherType[$gWeather]?>"></td></tr>
		<tr><td align=center><a href="<?=query("msg.php?show=compose&to=Admin&sid=?")?>"><img border=0 src="<?=g("icon/help2.png")?>" alt="Nachricht an die Admins schreiben" title="Nachricht an die Admins schreiben"></a></td></tr>
	</table>
</td>
<td valign="top" align="right">
	<table border=0 cellspacing=0 cellpadding=1>
	<tr>
	<?php if ($hq) {?><td style="border:solid #a0a0a0 1px;background-color:#f5f5f5"><a href="<?=Query("../info/info.php?sid=?&x=".$hq->x."&y=".$hq->y)?>">Haupthaus</a></td><?php } else {?><td></td><?php }?>
	<td style="border:solid #a0a0a0 1px;<?=($cpost->num==0)?"":($cpost->type==kFolderTypeExtra?"":"text-decoration:blink;")?>background-color:<?=($cpost==0)?"#f5f5f5":"#33CC33"?>"><a href="<?=Query("../info/msg.php?sid=?&folder=".$cpost->folder)?>">Post</a></td>
	<td style="border:solid #a0a0a0 1px;background-color:#f5f5f5"><a href="<?=sessionLink("../stats/gen_pts.php")?>">Highscore</a></td>
	<td style="border:solid #a0a0a0 1px;background-color:#f5f5f5"><a href="http://zwischenwelt.org/forum/" target="_blank">Forum</a></td>
	</tr><tr>
	<td style="border:solid #a0a0a0 1px;background-color:#f5f5f5"><a href="<?=sessionLink("../info/summary.php")?>">Überblick</a></td>
	<td style="border:solid #a0a0a0 1px;background-color:#f5f5f5"><a href="<?=sessionLink("../info/quest.php")?>">Quest</a></td>
	<td style="border:solid #a0a0a0 1px;background-color:#f5f5f5"><a href="<?=sessionLink("../info/kampfsim.php")?>">KampfSim</a></td>
	<td style="border:solid #a0a0a0 1px;background-color:#f5f5f5"><a href="http://zwischenwelt.org/wiki/" target="_blank">Hilfe</a></td>
	</tr><tr>
	<td style="border:solid #a0a0a0 1px;background-color:#f5f5f5"><a href="<?=sessionLink("../info/tech.php")?>">Forschung</a></td>
	<td style="border:solid #a0a0a0 1px;<?=($gpost==0)?"":"text-decoration:blink;"?>background-color:<?=($gpost==0)?($cgpost>0?"#CC33CC":"#f5f5f5"):"#33CC33"?>"><a href="<?=sessionLink("../info/guild.php")?>">Gilde</a></td>
	<td style="border:solid #a0a0a0 1px;background-color:#f5f5f5"><a href="<?=sessionLink("../info/profile.php")?>">Einstell.</a></td>
	<td style="border:solid #a0a0a0 1px;background-color:#f5f5f5"><a href="<?=sessionLink("../logout.php")?>" target="_parent">Logout</a></td>
	</tr>
	</table>
</td></tr></table>
<?php } ?>
<!-- ################################################################################ -->
<hr>
