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
//hat der user unbeantwortete umfragen?
if(sqlgetone("select p1.`id` from poll as p1 left outer join (SELECT id from poll as p RIGHT JOIN poll_answer as a ON  p.id=a.poll where a.user=".($gUser->id).") as p2 on p1.id=p2.id where ISNULL(p2.id) LIMIT 1")>0)$newpoll = true;
else $newpoll = false;

?>
<a href="javascript:displaymenunotify('postnotify','notify'); void(0);">Notify</a> 
<a href="javascript:displaymenunotify('postnotify','reset'); void(0);">Reset</a> 


<!-- ########################### GANZE NEUES MENU ################################# -->
<!-- <h1>Heute Abend findet wieder ein Chat statt. Mehr unter Umfrage oder Taverne</h1> --> 
<div class="mainmenu">
	<!-- 
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
			<li <?=($newguild)?"class=\"highlight\"":""?>>
				<a href="<?=sessionLink("../info/guild.php")?>">Gilde</a>
			</li>
			<li <?=($newpoll)?"class=\"highlight\"":""?>>
				<a href="<?=Query("../info/poll.php?sid=?")?>">Umfrage</a>
			</li>
			<li><a href="<?=sessionLink("../logout.php")?>" target="_parent">Logout</a></li>
		</ul>
	</div>
	<div class="mainmenu_icons">
		<div class="mainmenu_icons_show">
			<ul>
				<li><img src="<?=g($gTimeGfx) ?>" alt="<?=$gTimeStr?>" title="<?=$gTimeStr?>"></li>
				<li><img src="<?=g($gWeatherGfx[$gWeather])?>" alt="<?=$gWeatherType[$gWeather]?>" title="<?=$gWeatherType[$gWeather]?>"></li>
			</ul>
		</div>
		<div class="mainmenu_icons_click">
			<ul>
				<li>
					<a href="#" onclick="javascript:window.open('<?=query("../info/note.php?sid=?")?>', 'note', 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=1,width=400,height=400');">
						<img border=0 src="<?=g("note.png")?>" alt="Notiz" title="Notiz">
					</a>
				</li>
				<li>
					<?php if ($gUser->admin == 1) { $o = sqlgetobject("SELECT * FROM `building` WHERE `type` = 10 LIMIT 1");?>
						<a href="<?=Query("../info/info.php?sid=?&x=".$o->x."&y=".$o->y)?>"><img src="<?=g("icon/admin.png")?>" alt="zur Entwickleranstalt springen" title="zur Entwickleranstalt springen" border=0></a>
					<?php } else { ?>
						<a href="<?=query("msg.php?show=compose&to=Admin&sid=?")?>"><img border=0 src="<?=g("icon/help2.png")?>" alt="Nachricht an die Admins schreiben" title="Nachricht an die Admins schreiben"></a>
					<?php } ?>
				</li>			
			</ul>
		</div>
	</div>
	-->
	<div class="mainmenu_res">
		<?php
			$reslist = array();
			foreach($gRes as $n=>$f) {
				$o = false;
				$o->cur = $gUser->$f;
				$o->max = $gUser->{"max_".$f};
				$o->name = $n;
				$o->img = g("res_$f.gif");
				$o->imglink = Query("../info/waren.php?sid=?&t=".(isset($gRes2ItemType)?$gRes2ItemType[$f]:"?"));
				$reslist[] = $o;
			}
			if (1) {
				$o = false;
				$o->cur = $gUser->pop;
				$o->max = $gUser->maxpop;
				$o->name = "Bev&ouml;lkerung";
				$o->img = g("pop-r%R%.png","","",$gUser->race);
				$o->imglink = false;
				$reslist[] = $o;
			}
			$i = 0;
			$resperrow = 4;
		?>
		
		<table cellspacing=0 cellpadding=0><tr>
		<?php foreach($reslist as $o) {
			$rel = round(100*max(0,$o->cur)/(max(1,$o->max)));
			$col = GradientRYG($rel/100,0.8);
			$alt = $o->name." ($rel%): ".kplaintrenner(floor(max(0,$o->cur)))." / ".kplaintrenner(floor($o->max));
			?>
				<td align=right>
					<?php if ($o->imglink) {?> <a href="<?=$o->imglink?>">  <?php }?>
						<img border=0 title="<?=$alt?>" alt="<?=$alt?>" src="<?=$o->img?>">
					<?php if ($o->imglink) {?> </a>  <?php }?>
				</td>
				<td align=right class="menuResPercent">(<span style="color:<?=$col?>"><?=$rel?>%</span>)</td>
				<td align=right class="menuCurRes"><?=ktrenner(floor(max(0,$o->cur)))?></td>
				<?php if (intval($gUser->flags) & kUserFlags_ShowMaxRes) {?>
					<td class="menuMaxResSlash">/</td>
					<td class="menuMaxRes" align=right><?=ktrenner(floor(max(0,$o->max)))?></td>
				<?php } // endif?>
				<?php if (($i%$resperrow)!=$resperrow-1) {?><td>&nbsp;</td><?php } // endif?>
				<?php if (($i%$resperrow)==$resperrow-1) {?></tr><tr><?php } // endif?>
			<?php ++$i; } ?>
		</table>
	</div>
</div>
