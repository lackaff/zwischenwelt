<?php
include("../lib.main.php");

$hq = sqlgetobject("SELECT * FROM `building` WHERE `user` = ".$gUser->id." AND `type` = ".kBuilding_HQ);
$cpost = sqlgetobject("SELECT COUNT(m.`id`) as num,mf.id as folder,mf.type as type FROM `message` m, `message_folder` mf WHERE m.`to` = ".$gUser->id." AND m.`status` = 1 AND m.folder=mf.id group by mf.id");
if($gUser->guild > 0 && (sqlgetone("SELECT `founder` FROM `guild` WHERE `id`=".$gUser->guild) == $gUser->id || HasGuildRight($gUser,kGuildRight_GuildAdmin)))
	$gpost = sqlgetone("SELECT COUNT(`id`) FROM `guild_request` WHERE `guild`=".$gUser->guild);
else $gpost = null;
$cgpost = sqlgetone("SELECT COUNT(`id`) FROM `guild_forum_read` WHERE `user`=".$gUser->id);

$folder_root=sqlgetobject("SELECT `id`,`name` FROM `message_folder` WHERE `type`=".kFolderTypeRoot." AND `parent`=0 AND `user`=".$gUser->id);
$folder_send=sqlgetobject("SELECT `id`,`name` FROM `message_folder` WHERE `type`=".kFolderTypeSent." AND `parent`=0 AND `user`=".$gUser->id);
$folder_berichte=sqlgetobject("SELECT `id`,`name` FROM `message_folder` WHERE `type`=".kFolderTypeExtra." AND `parent`=0 AND `user`=".$gUser->id);

if(!isset($cpost->num) || $cpost->num == 0)$newpost = false;
else $newpost = true;
if($gpost > 0 || $cgpost > 0)$newguild = true;
else $newguild = false;
//hat der user unbeantwortete umfragen?
if(sqlgetone("select p1.`id` from poll as p1 left outer join (SELECT id from poll as p RIGHT JOIN poll_answer as a ON  p.id=a.poll where a.user=".($gUser->id).") as p2 on p1.id=p2.id where ISNULL(p2.id) LIMIT 1")>0)$newpoll = true;
else $newpoll = false;

if(isset($cpost->folder)){
	$cpost_folder = $cpost->folder;
} else {
	$cpost_folder = "";
}

?>
/*
  // compact menu
  // by Gor@MelodyRadio.Net
  // version 2006-03-27

  - Verschachteln mit Minuszeichen
  - Entweder "Text" oder "Text PIPE HTML-Code"
  - Die JavaScript-Funktion compactmenusetpage() wird automatisch
    mit einem <div> auf die Tabs gelegt.
  - Auch ein Menüpunkt mit Unterpunkten, kann mit einem Link oder JavaScript belegt werden
    Das Menü wird dadurch nicht beeinträchtigt. (ausser wenn "return false" oder "void(0);"
    benutzt wird)
*/

var phpsid='&sid=<?=$f_sid?>';

compactmenusitemap=new Array
(
  'HQ | <a target=info href="info/info.php?x=<?=$hq->x?>&y=<?=$hq->y?>&selectedtab=1'+phpsid+'" title="HQ (<?=$hq->x?>,<?=$hq->y?>)">HQ</a>', 
  '- Town Center | <a target=info href="info/info.php?x=<?=$hq->x?>&y=<?=$hq->y?>selectedtab=1'+phpsid+'" title="HQ (<?=$hq->x?>,<?=$hq->y?>)">Town Center</a>', 
//  '- Research | <a href="info/info.php?x=<?=$hq->x?>&y=<?=$hq->y?>&selectedtab=2'+phpsid+'" target="info">Research</a>',
  '- Production | <a href="info/info.php?x=<?=$hq->x?>&y=<?=$hq->y?>&selectedtab=3'+phpsid+'" target="info">Production</a>',
  '- Magic | <a href="info/info.php?x=<?=$hq->x?>&y=<?=$hq->y?>&selectedtab=6'+phpsid+'" target="info">Magic</a>',
  'Sprung',
  <?php
    foreach($gBuildingType as $id=>$obj)if($obj->flags & kBuildingTypeFlag_IsInQuickJump){
      $x = sqlgetobject("SELECT `x`,`y`,`level` FROM `building` WHERE `user`=".$gUser->id." AND `type`=".$obj->id." ORDER BY `level` DESC LIMIT 1");
      if(!empty($x)){?>
        '- <?=$obj->name?> | <a target=info href="info/info.php?x=<?=$x->x?>&y=<?=$x->y?>'+phpsid+'" title="<?=$obj->name?> (<?=$x->x?>,<?=$x->y?>)"><img src="<?=GetBuildingPic($obj,false,$x->level)?>" border="0" title="<?=$obj->name?> (<?=$x->x?>,<?=$x->y?>)"></a>',
      <?php }
    }
  ?>
  'Overview',
  '- Buildings | <a href="info/summary_buildings.php?'+phpsid+'" target="info">Buildings</a>',
  '- Research | <a href="info/summary_techs.php?'+phpsid+'" target="info">Research</a>',
  '- Armies | <a href="info/summary_units.php?'+phpsid+'" target="info">Armies</a>',
  '- - All | <a href="info/summary_units.php?armytype=0'+phpsid+'" target="info">All</a>',
    <?php foreach($gArmyType as $id=>$obj){ ?>
        '- - <?=$obj->name?> | <a target=info href="info/summary_units.php?armytype=<?=$obj->id?>&showempty=0'+phpsid+'" title="<?=$obj->name?>"><?=$obj->name?></a>',
    <?php } ?>
/*
  '- - Maschinen | <a href="info/summary_units.php?armytype=1&showempty=0'+phpsid+'" target="info">Maschinen</a>',
  '- - Flotten | <a href="info/summary_units.php?armytype=3&showempty=0'+phpsid+'" target="info">Flotten</a>',
  '- - Armeen | <a href="info/summary_units.php?armytype=4&showempty=0'+phpsid+'" target="info">Armeen</a>',
  '- - Karawanen | <a href="info/summary_units.php?armytype=5&showempty=0'+phpsid+'" target="info">Karawanen</a>',
  '- - Arbeiter | <a href="info/summary_units.php?armytype=6&showempty=0'+phpsid+'" target="info">Arbeiter</a>',
  '- - Magier | <a href="info/summary_units.php?armytype=7&showempty=0'+phpsid+'" target="info">Magier</a>',
  '- - Leere anzeigen | <a href="info/summary_units.php?armytype=-1&showempty=1'+phpsid+'" target="info">Leere Anzeigen</a>',
*/  
  '- Magic | <a href="info/summary.php?'+phpsid+'" target="info">Magic</a>',
  '- Goods | <a href="info/waren.php?'+phpsid+'" target="info">Goods</a>',
  '- Costs | <a href="info/kosten.php?'+phpsid+'" target="info">Costs</a>',
  '- Building Plans | <a href="info/bauplan.php?'+phpsid+'" target="info">Building Plans</a>',
  '- Quests | <a href="info/quest.php?'+phpsid+'" target="info">Quests</a>',
  '- Diplomacy | <a href="info/info.php?x=<?=$hq->x?>&y=<?=$hq->y?>&selectedtab=5'+phpsid+'" target="info">Diplomacy</a>',
  'Units',
  '- neu laden | <a href="?setpage=__MENUID__&sid=<?=$f_sid?>"><img border=0 src="<?=g("icon/reload.png")?>" alt=reload title=reload></a>',
  <?php
    foreach($gArmyType as $id=>$type){
      $l = sqlgettable("SELECT `id`,`name`,`x`,`y` FROM `army` WHERE `type`=".$type->id." AND `user`=".$gUser->id." ORDER BY `name` ASC");
      if(sizeof($l)>0){
        ?>
        '- <?=$type->name?>',        
        <?php foreach($l as $x){?>
        '- - <?=$x->name?> | <a target=info href="info/info.php?jumptoarmy=<?=$x->id?>'+phpsid+'" title="<?=$x->name?>"><?=$x->name?></a>',
        <?php } }
    }
  ?>
  <?php
    $l = sqlgettable("SELECT `id`,`name`,`x`,`y` FROM `army` WHERE `user`=".$gUser->id." ORDER BY `type` ASC,`name` ASC");
  ?>
  'Guilds | <a href="info/guild.php?'+phpsid+'" target="info"><span id="guildnotify">Guilds</span></a>',
  '- General | <a href="info/guild.php?'+phpsid+'" target="info">General</a>',
  '- Mambers | <a href="info/guild_members.php?'+phpsid+'" target="info">Members</a>',
  '- Forum | <a href="info/guild_forum.php?'+phpsid+'" target="info">Forum</a>',
  '- Log | <a href="info/guild_log.php?'+phpsid+'" target="info">Log</a>',
  '- Settings | <a href="info/guild_admin.php?'+phpsid+'" target="info">Settings</a>',
  'Mail | <a href="<?=Query("info/msg.php?sid=?&folder=".$cpost_folder)?>" target=info title=Post><span id="postnotify">Mail</span></a>',
  '- Inbox | <a href="info/msg.php?show=content&folder=<?=$folder_root->id?>'+phpsid+'" target="info"><img src="gfx/post/inbox.png" border="0" title="Inbox"></a>',
  '- Outbox | <a href="info/msg.php?show=content&folder=<?=$folder_send->id?>'+phpsid+'" target="info"><img src="gfx/post/outbox.png" border="0" title="Outbox"></a>',
  '- Reports | <a href="info/msg.php?show=content&folder=<?=$folder_berichte->id?>'+phpsid+'" target="info"><img src="gfx/post/berichte.png" border="0" title="Reports"></a>',
  '- Compose New | <a href="info/msg.php?show=compose'+phpsid+'" target="info"><img src="gfx/gildeforum/neu.png" border="0" title="Compose New"></a>',
  '- Settings | <a href="info/msg.php?show=foldertree'+phpsid+'" target="info"><img src="gfx/post/einstellungen.png" border="0" title="Settings"></a>',
<?php if($gUser->admin){
$dev = sqlgetobject("SELECT * FROM `building` WHERE `type` = 10 LIMIT 1"); ?>
  'Admin | <a href="<?=Query("info/info.php?sid=?&x=".$dev->x."&y=".$dev->y)?>" target=info>Admin</a>',
  '- Admin | <a href="<?=Query("info/info.php?sid=?&x=".$dev->x."&y=".$dev->y)?>" target=info>Admin</a>',
  '- List all | <a href="<?=Query("info/listall.php?sid=?")?>" target=info>List All</a>',
  '- Jobs | <a href="<?=Query("info/jobs.php?sid=?")?>" target=info>Jobs</a>',
<?php } ?>
  'Polls | <a href="info/poll.php?'+phpsid+'" target="info"><span id="pollnotify">Polls</span></a>',
  '- Open Polls | <a href="info/poll.php?'+phpsid+'" target="info"><span id="pollnotify">Open Polls</span></a>',
  '- Completed Polls | <a href="info/poll.php?tab=1'+phpsid+'" target="info">Completed Polls</a>',
  '- Archives | <a href="work/polls.txt" target="_blank">Archives</a>',
  'Stats | <a href="<?= sessionLink("stats/gen_pts.php")?>" title=stats target=info>Stats</a>',
  '- Players',
  '- - Total Score | <a href="stats/gen_pts.php?what=p'+phpsid+'" target="info">Total Score</a>',
  '- - Economic Score | <a href="stats/gen_pts.php?what=pnm'+phpsid+'" target="info">Economic Score</a>',
  '- - Military Score | <a href="stats/gen_pts.php?what=pom'+phpsid+'" target="info">Military Score</a>',
  '- Population | <a href="stats/population.php?'+phpsid+'" target="info">Population</a>',
  '- Statistics | <a href="stats/stats.php?'+phpsid+'" target="info">Statistics</a>',
  '- Guilds',
  '- - Total Score | <a href="stats/gen_guild_pts.php?what=g'+phpsid+'" target="info">Total Score</a>',
  '- - Economic Score | <a href="stats/gen_guild_pts.php?what=gnm'+phpsid+'" target="info">Economic Score</a>',
  '- - Military Score | <a href="stats/gen_guild_pts.php?what=gom'+phpsid+'" target="info">Military Score</a>',
  '- Armies | <a href="stats/armeen.php?'+phpsid+'" target="info">Armies</a>',
  'Forum | <a href="" target="_blank">Forum</a>',
  '- Private Messages | <a href="forum/index.php?t=pmsg'+phpsid+'" target="_blank">Private Messages</a>',
  '- Unread Posts | <a href="forum/index.php?t=selmsg&unread=1&frm_id=0'+phpsid+'" target="_blank">Unread Posts</a>',
  'Help',
  '- Reference | <a href="" target="_blank">Reference</a>',
  '- Contact Admin | <a href="info/msg.php?show=compose&to=Admin'+phpsid+'" target="info">Contact Admin</a>',
  '- Reload Menu | <a href="compactmenu.php?sid='+phpsid+'">Reload Menu</a>',
  '- Battle Simulator | <a href="info/kampfsim.php?sid='+phpsid+'" target=info>Battle Simulator</a>',
  '- Technology Tree | <a href="info/techgraphpart.php?sid='+phpsid+'" target=info>Technology Tree</a>',
  'Account',
  '- Scratchpad | <a href="info/note.php?'+phpsid+'" target="_blank">Scratchpad</a>',
  '- Settings | <a href="info/profile.php?'+phpsid+'" target="info">Settings</a>',
  '- Show Resources | <a href="info/showressource.php?'+phpsid+'" target="info">Show Resources</a>',
  '- Menu Layout',
  <?php foreach($gMenuStyles as $name=>$css){?>
  '- - <?=$name?> | <a href="?setpage=__MENUID__&style=<?=$name?>'+phpsid+'" title="<?=$name?>"><?=$name?></a>',
  <?php } ?>
  '- Logout | <a href="logout.php?'+phpsid+'" target="_parent">Logout</a>',
  /*
  '- Punkte | <a href="info/profile.php?'+phpsid+'" target="info">Punkte</a>',
  '- Schulden | <a href="info/profile.php?'+phpsid+'" target="info">Schulden</a>',
  '- Anzeige | <a href="info/profile.php?'+phpsid+'" target="info">Anzeige</a>',
  '- Browser | <a href="info/profile.php?'+phpsid+'" target="info">Browser</a>',
  */
  ''
);
