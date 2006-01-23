<?php
$gClassName = "cInfoTempel";
class cInfoTempel extends cInfoBuilding {
	function mydisplay() {
		foreach ($_REQUEST as $name=>$val) ${"f_".$name} = $val;
		global $gUser;
		global $gObject;
		global $gRes;
		profile_page_start("tempel.php"); ?>
		<hr><br>
		<?php
		
		if($gUser->id == $gObject->user) { 
			if(isset($f_sacrifice))
			{
        $moral = $gUser->moral;
        $moral_good = $moral / 100;
        $moral_bad = 2 - $moral_good;
        
        $pop = min(abs(intval($f_pop)),$gUser->pop);
        foreach($gRes as $name=>$field)$res[$field] = min(abs(intval(${"f_res_$field"})),$gUser->$field);
        $allres = array_sum($res);
        
        //sacrifice population and become evil
        if($pop > 0){
          sql("UPDATE `user` SET `pop`=`pop`-$pop WHERE `id`=".$gUser->id." AND `pop`-$pop>=0");
          $type = rand(1,9);
          if(mysql_affected_rows()>0)switch ($type)
          {
            case 1:
            case 2:
              $x = $pop * $moral_bad;
              $msg = "Die geopferten Bewohner werden ganz braun und kriegen krustige Haut,
              auf einmal wachsen ihnen Blätter und sie fangen an Wurzeln zu schlagen.
              Das ist die wahre Macht von rein biologischen Schampoo, du erhälst $x Holz !";
              changeUserMoral($gUser->id,-round($pop/10000));
              sql("UPDATE `user` SET `lumber`=`lumber`+$x WHERE `id`=".$gUser->id);
            break;
            case 3:
            case 4:
              $x = $pop * $moral_bad;
              $msg = "Er hat Jehova gesagt ! Steinigt ihn !
              Bääärte ! schöööne flauschige falsche Bääääärte !
              Kann es sein das Weibsfolk anwesend ist ?
              Eine Steinigung frei nach Monty Python.
              Du erhälst $x Stein !";
              changeUserMoral($gUser->id,-round($pop/10000));
              sql("UPDATE `user` SET `stone`=`stone`+$x WHERE `id`=".$gUser->id);
            break;
            case 5:
            case 6:
              $x = $pop * $moral_bad;
              $msg = "Happy Kannibalen-Wochen bei McTempel, du erhälst $x Nahrung !";
              changeUserMoral($gUser->id,-round($pop/10000));
              sql("UPDATE `user` SET `food`=`food`+$x WHERE `id`=".$gUser->id);
            break;
            case 7:
            case 8:
              $x = $pop * $moral_bad;
              $msg = "Den armen Opfern werden sämtliche Zahnspangen, Plomben
              und sonstige Implantate entfernt.
              Du erhälst $x Metall !";
              changeUserMoral($gUser->id,-round($pop/10000));
              sql("UPDATE `user` SET `metal`=`metal`+$x WHERE `id`=".$gUser->id);
            break;
            case 9:
              $pop = floor($pop / 100);
              $x = $pop * $moral_bad;
              $msg = "*zap*rauch*bruzzel* Oh da haben sich wohl ein paar Leute aufgelöst.
              Ah aber ein paar Zaubersteinchen sind geblieben.
              Du erhälst $x Runen !";
              changeUserMoral($gUser->id,-round($pop/10000));
              sql("UPDATE `user` SET `runes`=`runes`+$x WHERE `id`=".$gUser->id);
            break;
          }
          $gUser = sqlgetobject("SELECT * FROM `user` WHERE `id` = ".$gUser->id);
				}
        
        //sacrifice res and become good
        if($allres > 0){
          //init weights of the res to 1.0
          foreach($res as $n=>$x)$resvalue[$n] = 1.0;
          //runes count double
          $resvalue["runes"] = 2.0;
        
          $sum = 0;
          $set = array();
          $where = array();
          //calculate how many res get sacrificed
          foreach($res as $n=>$x){
            $sum += $x*$resvalue[$n];
            //build sql query parts
            if($x>0){
              $set[] = "`$n`=`$n`-$x";
              $where[] = "`$n`-$x>=0";
            }
          }
          
          //oki there is something to sacrifice
          if($sum>0){
            $x = floor($sum * 0.8 * $moral_good);
              
            sql("UPDATE `user` SET ".implode(",",$set)." WHERE `id`=".$gUser->id." AND ".implode(" AND ",$where));
            $type = rand(1,16);
            if(mysql_affected_rows()>0)switch ($type)
            {
              case 1:
              case 2:
                $msg = "Die geopferten Bewohner werden ganz braun und kriegen krustige Haut,
                auf einmal wachsen ihnen Blätter und sie fangen an Wurzeln zu schlagen.
                Das ist die wahre Macht von rein biologischen Schampoo, du erhälst $x Holz !";
                changeUserMoral($gUser->id,round($sum/10000));
                sql("UPDATE `user` SET `lumber`=`lumber`+$x WHERE `id`=".$gUser->id);
              break;
              case 3:
              case 4:
                $msg = "Er hat Jehova gesagt ! Steinigt ihn !
                Bääärte ! schöööne flauschige falsche Bääääärte !
                Kann es sein das Weibsfolk anwesend ist ?
                Eine Steinigung frei nach Monty Python.
                Du erhälst $x Stein !";
                changeUserMoral($gUser->id,round($sum/10000));
                sql("UPDATE `user` SET `stone`=`stone`+$x WHERE `id`=".$gUser->id);
              break;
              case 5:
              case 6:
                $msg = "Happy Kannibalen-Wochen bei McTempel, du erhälst $x Nahrung !";
                changeUserMoral($gUser->id,round($sum/10000));
                sql("UPDATE `user` SET `food`=`food`+$x WHERE `id`=".$gUser->id);
              break;
              case 7:
              case 8:
                $msg = "Den armen Opfern werden sämtliche Zahnspangen, Plomben
                und sonstige Implantate entfernt.
                Du erhälst $x Metall !";
                changeUserMoral($gUser->id,round($sum/10000));
                sql("UPDATE `user` SET `metal`=`metal`+$x WHERE `id`=".$gUser->id);
              break;
              case 9:
                $x = floor($x / 100);
                $msg = "*zap*rauch*bruzzel* Oh da haben sich wohl ein paar Leute aufgelöst.
                Ah aber ein paar Zaubersteinchen sind geblieben.
                Du erhälst $x Runen !";
                changeUserMoral($gUser->id,round($sum/10000));
                sql("UPDATE `user` SET `runes`=`runes`+$x WHERE `id`=".$gUser->id);
              break;
              case 10:
                $x = floor($x / 50);
                $msg = "Nach Stunden der Zeremonie ziehen sich die geopferten Rohstoffe zu seltsamen
                Formen zusammen und es beginnt stark zu stinken. Durch die dann geöffneten Fenster weht ein
                Windhauch, der dem Haufen neues Leben einhaucht. Bereits nach einiger Zeit kann man schon
                fast $x Menschen erkennen.";
                changeUserMoral($gUser->id,round($sum/10000));
                sql("UPDATE `user` SET `pop`=LEAST(`pop`+$x,`maxpop`) WHERE `id`=".$gUser->id);
              break;
              default:
                $msg = "Nichts passiert!";
              break;
            }
            $gUser = sqlgetobject("SELECT * FROM `user` WHERE `id` = ".$gUser->id);
          }
        }
			}
		?>
		<div class="sacrifice">
    <div class="effect"><?=$msg?></div>
		<form method="post" action="<?=query("info.php?sid=?&x=?&y=?")?>">
    <div class="desc">Hier kann man Allerlei opfern, was ein paar interessante Auswirkungen haben kann. 
    Bedenke aber, daß man seine Gesinnung durch das Opfern verändert und es auch keine präzise Wissenschaft ist.</div>
		<ul>
			<li><input name="pop" value="0"> <img alt="Bewohner" title="Bewohner" src="<?=g("pop-r%R%.png")?>"></li>
			<?php foreach($gRes as $name=>$field){ ?>
        <li><input name="res_<?=$field?>" value="0"> <img alt="<?=$name?>" title="<?=$name?>" src="<?=g("res_$field.gif")?>"></li>
      <?php } ?>
		</ul>
		<input type="submit" name="sacrifice" value="opfern">
		</form>
		</div>
		<?php } ?>
		<?php profile_page_end(); 
	}
}
?>
