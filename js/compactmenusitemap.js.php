<?php
include("../lib.main.php");
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
  'Übersicht',
  '- Gebäude | <a href="info/summary_buildings.php?'+phpsid+'" target="info">Gebäude</a>',
  '- Forschung | <a href="info/summary_techs.php?'+phpsid+'" target="info">Forschung</a>',
  '- Truppen | <a href="info/summary_units.php?'+phpsid+'" target="info">Truppen</a>',
  '- - Alle | <a href="info/summary_units.php?armytype=0'+phpsid+'" target="info">Alle</a>',
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
*/  
  '- - Leere anzeigen | <a href="info/summary_units.php?armytype=-1&showempty=1'+phpsid+'" target="info">Leere Anzeigen</a>',
  '- Zauber | <a href="info/summary.php?'+phpsid+'" target="info">Zauber</a>',
  '- Waren | <a href="info/waren.php?'+phpsid+'" target="info">Waren</a>',
  '- Kosten | <a href="info/kosten.php?'+phpsid+'" target="info">Kosten</a>',
  '- Baupläne | <a href="info/bauplan.php?'+phpsid+'" target="info">Baupläne</a>',
  '- Quests | <a href="info/quest.php?'+phpsid+'" target="info">Quests</a>',
  '- Diplomatie | <a href="info/main.php?TabPane0Activate=5'+phpsid+'" target="info">Diplomatie</a>',
  'Gebäude',
  <?php
    foreach($gBuildingType as $id=>$obj){
      $x = sqlgetobject("SELECT `x`,`y`,`level` FROM `building` WHERE `user`=".$gUser->id." AND `type`=".$obj->id." ORDER BY `level` DESC LIMIT 1");
      if(!empty($x)){?>
        '- <?=$obj->name?> | <a target=info href="info/info.php?x=<?=$x->x?>&y=<?=$x->y?>'+phpsid+'" title="<?=$obj->name?> (<?=$x->x?>,<?=$x->y?>)"><img src="<?=GetBuildingPic($obj,false,$x->level)?>" border="0" title="<?=$obj->name?> (<?=$x->x?>,<?=$x->y?>)"></a>',
      <?php }
    }
  ?>
  'Einheiten',
  <?php
    $l = sqlgettable("SELECT * FROM `army` WHERE `user`=".$gUser->id." ORDER BY `type` ASC,`name` ASC");
    foreach($l as $x){?>
        '- <?=$x->name?> | <a target=info href="info/info.php?x=<?=$x->x?>&y=<?=$x->y?>'+phpsid+'" title="<?=$obj->name?> (<?=$x->x?>,<?=$x->y?>)"><?=$x->name?></a>',
    <?php }
  ?>
  'Gilde | <a href="info/guild.php?'+phpsid+'" target="info">Gilde</a>',
  '- Allgemein | <a href="info/guild.php?'+phpsid+'" target="info">Allgemein</a>',
  '- Mitglieder | <a href="info/guild_members.php?'+phpsid+'" target="info">Mitglieder</a>',
  '- Forum | <a href="info/guild_forum.php?'+phpsid+'" target="info">Forum</a>',
  '- Log | <a href="info/guild_log.php?'+phpsid+'" target="info">Log</a>',
  '- Verwalten | <a href="info/guild_admin.php?'+phpsid+'" target="info">Verwalten</a>',
  'Post',
  '- Eingang | <a href="info/msg.php?show=content&folder=5183'+phpsid+'" target="info"><img src="gfx/post/inbox.png" border="0" title="Eingang"></a>',
  '- Ausgang | <a href="info/msg.php?show=content&folder=5184'+phpsid+'" target="info"><img src="gfx/post/outbox.png" border="0" title="Ausgang"></a>',
  '- Berichte | <a href="info/msg.php?show=content&folder=5185'+phpsid+'" target="info"><img src="gfx/post/berichte.png" border="0" title="Berichte"></a>',
  '- Neu | <a href="info/msg.php?show=compose'+phpsid+'" target="info"><img src="gfx/gildeforum/neu.png" border="0" title="Neu"></a>',
  '- Einstellungen | <a href="info/msg.php?show=foldertree'+phpsid+'" target="info"><img src="gfx/post/einstellungen.png" border="0" title="Einstellungen"></a>',
  'Umfrage',
  '- offene Umfragen | <a href="info/poll.php?'+phpsid+'" target="info">offene Umfragen</a>',
  '- schon beantwortete Umfragen | <a href="info/poll.php?tab=1'+phpsid+'" target="info">schon beantwortete Umfragen</a>',
  'Scores',
  '- Spieler',
  '- - totale Punkte | <a href="stats/gen_pts.php?what=p'+phpsid+'" target="info">totale Punke</a>',
  '- - ohne Militär | <a href="stats/gen_pts.php?what=pnm'+phpsid+'" target="info">ohne Militär</a>',
  '- - nur Militär | <a href="stats/gen_pts.php?what=pom'+phpsid+'" target="info">nur Militär</a>',
  '- Bevölkerung | <a href="stats/population.php?'+phpsid+'" target="info">Bevölkerung</a>',
  '- Statistik | <a href="stats/stats.php?'+phpsid+'" target="info">Statistik</a>',
  '- Gilden',
  '- - totale Punkte | <a href="stats/gen_guild_pts.php?what=g'+phpsid+'" target="info">totale Punkte</a>',
  '- - ohne Militär | <a href="stats/gen_guild_pts.php?what=gnm'+phpsid+'" target="info">ohne Militär</a>',
  '- - nur Militär | <a href="stats/gen_guild_pts.php?what=gom'+phpsid+'" target="info">nur Militär </a>',
  '- Armeen | <a href="stats/armeen.php?'+phpsid+'" target="info">Armeen</a>',
  'Account',
  '- Daten | <a href="info/profile.php?'+phpsid+'" target="info">Daten</a>',
  '- Punkte | <a href="info/profile.php?'+phpsid+'" target="info">Punkte</a>',
  '- Schulden | <a href="info/profile.php?'+phpsid+'" target="info">Schulden</a>',
  '- Anzeige | <a href="info/profile.php?'+phpsid+'" target="info">Anzeige</a>',
  '- Browser | <a href="info/profile.php?'+phpsid+'" target="info">Browser</a>',
  'Forum | <a href="http://zwischenwelt.org/forum/?'+phpsid+'" target="_blank">Forum</a>',
  '- Private Nachrichten | <a href="forum/index.php?t=pmsg'+phpsid+'" target="_blank">Private Nachrichten</a>',
  '- Ungelesene Beiträge | <a href="forum/index.php?t=selmsg&unread=1&frm_id=0'+phpsid+'" target="_blank">Ungelesene Beiträge</a>',
  'Hilfe',
  '- ZW Wiki | <a href="http://zwischenwelt.org/wiki/?'+phpsid+'" target="_blank">Nachschlagewerk</a>',
  '- Admin fragen | <a href="info/msg.php?show=compose&to=Admin'+phpsid+'" target="info">Admin fragen</a>',
  '- Notiz schreiben | <a href="info/note.php?'+phpsid+'" target="_blank">Notiz schreiben</a>',
  'Logout',
  '- Wirklich? | <a href="logout.php?'+phpsid+'" target="_blank">Wirklich?</a>',
  ''
);
