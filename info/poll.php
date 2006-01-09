<?php 
require_once("header.php"); 

function DrawPollBar($votes,$totalvotes,$width=100,$height=10,$color="green"){
  $x = round($width*$votes/max(1,$totalvotes));
  return "<div style='display:block;height:".$height."px;width:".$x."px;background-color:$color'></div>";
}

if(isset($f_poll_ok)){
  $vote = null;
  $vote->poll = $f_poll;
  $vote->number = $f_number;
  $vote->user = $gUser->id;
  $vote->time = time();
  sql("REPLACE INTO `poll_answer` SET ".obj2sql($vote));
  Redirect(query("?sid=?"));
}

if(empty($f_tab))$tab = 0;
else $tab = intval($f_tab);

$tabs = array(
  array("Umfrage","",query("poll.php?sid=?")),
  array("Alle anzeigen","",query("poll.php?tab=1&sid=?")),
);
if($gUser->admin){
  //das hier kann nur der admin (löschen, erstellen)
  $tabs[] = array("Verwalten","",query("poll.php?tab=2&sid=?"));
  //eine umfrage löschen
  if(isset($f_delpoll)){
    $id = intval($f_delpoll);
    sql("DELETE FROM `poll` WHERE `id`=$id");
    sql("DELETE FROM `poll_choice` WHERE `poll`=$id");
    sql("DELETE FROM `poll_answer` WHERE `poll`=$id");
    Redirect(query("?tab=1&sid=?"));
  }
  //eine neue umfrage erstellen
  if(isset($f_poll_create) && !empty($f_name)){
    sql("INSERT INTO `poll` SET `name`='".addslashes($f_name)."'");
    $id = mysql_insert_id();
    $count = 1;
    for($i=0;$i<10;++$i){
      $text = ${"f_text_$i"};
      if(!empty($text)){
        sql("INSERT INTO `poll_choice` SET `poll`=$id,`number`=$count,`text`='".addslashes($text)."'");
        ++$count;
      }
    }
    Redirect(query("?tab=1&sid=?"));
  }
}

echo GenerateTabs("polltabs",$tabs,"",false,$tab);
echo "<div class=\"tabpane\">";

switch($tab){
  case 0://Umfrage
    $poll = sqlgetobject("select p1.`id`,p1.`name` from poll as p1 left outer join (SELECT id from poll as p RIGHT JOIN poll_answer as a ON  p.id=a.poll where a.user=".($gUser->id).") as p2 on p1.id=p2.id where ISNULL(p2.id) ORDER BY p1.id DESC LIMIT 1");
    if($poll){
      $choices = sqlgettable("SELECT * FROM `poll_choice` WHERE `poll`=".($poll->id));
    ?>
    <div class="poll"><form method="post" action=""><ul>
    <h1><?=$poll->name?></h1>
    <?php
    foreach($choices as $o)echo "<li><input type=radio name=number value=".($o->number)."> ".($o->text)."</li>";
    ?>
    </ul>
    <input type="hidden" name="poll" value="<?=$poll->id?>">
    <input type="submit" name="poll_ok" value="abschicken"></form></div>
    <?php 
    } else {
      echo "Sie haben an allen Umfragen teilgenommen. Danke!";
    }
    break;
  case 1://Alle anzeigen
      ?>
      <table>
      <?php
      $polls = sqlgettable("
      SELECT p.*,t.totalvotes FROM (
        SELECT p.id, p.name, p.created , p.number, p.votes,c.text
        FROM (
          SELECT p.id, p.name, p.created , a.number, count( * ) AS votes
          FROM poll AS p
          LEFT JOIN poll_answer AS a ON p.id = a.poll
          GROUP BY a.poll, a.number
        ) AS p
        LEFT JOIN poll_choice AS c ON c.poll = p.id
        AND c.number = p.number
        ORDER BY p.id DESC, p.number ASC
      ) as p
      LEFT JOIN (
        SELECT a.poll AS id, count( * ) AS totalvotes
        FROM poll_answer AS a
        GROUP BY a.poll
      ) as t ON p.id=t.id
      ");
      
/*

*/
      $name = "";
      foreach($polls as $p){
        if($p->totalvotes < 3)continue;
        if($name != $p->name){
          $name = $p->name;
          if($gUser->admin)$del = "[<a href='".query("?sid=?&delpoll=$p->id")."'>l&ouml;schen</a>]";
          else $del = "";
          echo "<tr><td colspan=4><hr>$del ".date("r",$p->created)."</td></tr>";
          echo "<tr><th>$p->id. $name</th>";
        } else echo "<tr><th></th>";
        echo "<td>$p->text</td>";
        echo "<td>$p->votes</td>";
        echo "<td>".DrawPollBar($p->votes,$p->totalvotes)."</td>";
        echo "</tr>";
      }
      ?>
      </table>
      <?php
    break;
  case 2://Verwalten
    ?>
      <h1>Umfrage erstellen</h1>
      <form method="post" action="<?=query("?tab=1&sid=?")?>">
      <table>
        <tr><th>Name</th><td><input size=64 type="text" name="name" value=""></td></tr>
        <?php for($i=0;$i<10;++$i){ ?>
        <tr><th><?=$i?>. </th><td><input size=64 type="text" name="text_<?=$i?>" value=""></td></tr>        
        <?php } ?>
        <tr><td colspan=2 align=right><input type=submit name=poll_create value=erstellen></td></tr>
      </table>
      </form>
    <?php
    break;
}
echo "</div>";
require_once("footer.php"); 
?>
