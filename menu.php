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
<script> 
	<?php 
	if($newpost)echo "parent.menu.displaymenunotify('postnotify','notify');\n";
	else echo "parent.menu.displaymenunotify('postnotify','reset');\n";
	if($newguild)echo "parent.menu.displaymenunotify('guildnotify','notify');\n";
	else echo "parent.menu.displaymenunotify('guildnotify','reset');\n";
	if($newpoll)echo "parent.menu.displaymenunotify('pollnotify','notify');\n";
	else echo "parent.menu.displaymenunotify('pollnotify','reset');\n";
	?>
</script>
<!-- <h1>Heute Abend findet wieder ein Chat statt. Mehr unter Umfrage oder Taverne</h1> --> 
<?php

$fmt = GetUserValue($gUser->id,"resformat",kDefaultResFormat);
drawRessources($gUser,$fmt);
/*
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



foreach($reslist as $o){
	$i = round(16*max(0,$o->cur)/(max(1,$o->max)));
	$img = "lager/lagerstand_$i.gif";
	$rel = round(100*max(0,$o->cur)/(max(1,$o->max)));
	$col = GradientRYG($rel/100,0.8);
	$alt = $o->name." ($rel%): ".kplaintrenner(floor(max(0,$o->cur)))." / ".kplaintrenner(floor($o->max));
	if ($o->imglink) {?><a href="<?=$o->imglink?>"><?php }
	?><img border=0 title="<?=$alt?>" alt="<?=$alt?>" src="<?=$o->img?>"><?php
	if ($o->imglink) {?></a> <?php }
	echo "<img src='".g($img)."' border=0 alt='$rel%' title='$rel%'> ";
	echo shortNumber($o->cur);
	 if (intval($gUser->flags) & kUserFlags_ShowMaxRes)echo " / ".shortNumber($o->max);
	echo " ";
}
*/
echo "<hr>";
?>
