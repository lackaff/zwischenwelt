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
?>

<!-- ########################### GANZE NEUES MENU ################################# -->

<div class="mainmenu">
	<div class="mainmenu_links">
		<ul>
			<?php if ($hq) {?><li><a href="<?=Query("../info/info.php?sid=?&x=".$hq->x."&y=".$hq->y)?>">Haupthaus</a></li><?php } ?>
			<li <?=($newpost)?"class=\"highlight\"":""?>>
				<a href="<?=Query("../info/msg.php?sid=?&folder=".$cpost->folder)?>">Post</a>
			</li>
			<li><a href="http://zwischenwelt.org/forum/" target="_blank">Forum</a></li>
			<li><a href="<?=sessionLink("../stats/gen_pts.php")?>">Highscore</a></li>
			<li><a href="<?=sessionLink("../info/profile.php?sid=?")?>">Einstell.</a></li>
			<li><a href="http://zwischenwelt.org/wiki/" target="_blank">Hilfe</a></li>
			<li><a href="<?=sessionLink("../logout.php")?>" target="_parent">Logout</a></li>
			<li <?=($newguild)?"class=\"highlight\"":""?>>
				<a href="<?=sessionLink("../info/guild.php")?>">Gilde</a>
			</li>
		</ul>
	</div>
	<div class="mainmenu_icons">
		<ul>
			<li>
				<a href="#" onclick="javascript:window.open('<?=query("../info/note.php?sid=?")?>', 'note', 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=1,width=400,height=400');">
					<img border=0 src="<?=g("note.png")?>" alt="Notiz" title="Notiz">
				</a>
			</li>
			<li><img src="<?=g($gTimeGfx) ?>" alt="<?=$gTimeStr?>" title="<?=$gTimeStr?>"></li>
			<li><img src="<?=g($gWeatherGfx[$gWeather])?>" alt="<?=$gWeatherType[$gWeather]?>" title="<?=$gWeatherType[$gWeather]?>"></li>
			<li>
				<?php if ($gUser->admin == 1) { $o = sqlgetobject("SELECT * FROM `building` WHERE `type` = 10");?>
					<a href="<?=Query("../info/info.php?sid=?&x=".$o->x."&y=".$o->y)?>"><img src="<?=g("icon/admin.png")?>" alt="zur Entwickleranstalt springen" title="zur Entwickleranstalt springen" border=0></a>
				<?php } else { ?>
					<a href="<?=query("msg.php?show=compose&to=Admin&sid=?")?>"><img border=0 src="<?=g("icon/help2.png")?>" alt="Nachricht an die Admins schreiben" title="Nachricht an die Admins schreiben"></a>
				<?php } ?>
			</li>
		</ul>
	</div>
	<div class="mainmenu_res">
		<ul>
			<?php foreach($gRes as $n=>$f) {
				$rel = round(100*max(0,$gUser->$f)/(max(1,$gUser->{"max_".$f})));
				$alt = $n." ($rel%): ".kplaintrenner(floor(max(0,$gUser->$f)))." / ".kplaintrenner(floor($gUser->{"max_".$f}));
			?>
				<li>
					<a href="<?=Query("../info/waren.php?sid=?&t=".(isset($gRes2ItemType)?$gRes2ItemType[$f]:"?"))?>">
						<img border=0 title="<?=$alt?>" alt="<?=$alt?>" src="<?=g("res_$f.gif")?>">
					</a>
					(<?=$rel?>%) <?=ktrenner(floor(max(0,$gUser->$f)))?>
				</li>
			<?php }?>
			<li>
				<?php 
				$rel = round(100*max(0,$gUser->pop)/max(1,($gUser->maxpop)));
				$alt = "Bev&ouml;lkerung ($rel%): ".kplaintrenner(floor(max(0,$gUser->pop)))." / ".kplaintrenner(floor($gUser->maxpop));
				?>
				<img title="<?=$alt?>" alt="<?=$alt?>" src="<?=g("pop-r%R%.png","","",$gUser->race)?>">
				(<?=$rel?>%) <?=ktrenner(floor(max(0,$gUser->pop)))?>
			</li>
		</ul>
	</div>
</div>
