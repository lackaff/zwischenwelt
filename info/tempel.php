<?php
$gClassName = "cInfoTempel";
class cInfoTempel extends cInfoBuilding {
	function mydisplay() {
		foreach ($_REQUEST as $name=>$val) ${"f_".$name} = $val;
		global $gUser;
		global $gObject;
		profile_page_start("tempel.php"); ?>
		<hr><br>
		<?php
		
		if($gUser->id == $gObject->user) { 
			if(isset($f_sacrifice))
			{
				$x = abs(intval($f_sacrifice));
				sql("UPDATE `user` SET `pop`=`pop`-$x WHERE `id`=".$gUser->id);
				sql("UPDATE `user` SET `pop`=0 WHERE `pop`<0 AND `id`=".$gUser->id);
				$type = rand(1,9);
				switch ($type)
				{
					case 1:
					case 2:
						?>
						Die geopferten Bewohner werden ganz braun und kriegen krustige Haut,<br>
						auf einmal wachsen ihnen Blätter und sie fangen an Wurzeln zu schlagen.<br>
						Das ist die wahre Macht von rein biologischen Schampoo, du erhälst <?=$x?> Holz !<br>
						<?php
						changeUserMoral($gUser->id,-round($x/10000));
						sql("UPDATE `user` SET `lumber`=`lumber`+$x WHERE `id`=".$gUser->id);
					break;
					case 3:
					case 4:
						?>
						Er hat Jehova gesagt ! Steinigt ihn ! <br>
						Bääärte ! schöööne flauschige falsche Bääääärte !<br>
						Kann es sein das Weibsfolk anwesend ist ?<br>
						Eine Steinigung frei nach Monty Python.<br>
						Du erhälst <?=$x?> Stein !<br>
						<?php
						changeUserMoral($gUser->id,-round($x/10000));
						sql("UPDATE `user` SET `stone`=`stone`+$x WHERE `id`=".$gUser->id);
					break;
					case 5:
					case 6:
						?>
						Happy Kannibalen-Wochen bei McTempel, du erhälst <?=$x?> Nahrung !<br>
						<?php
						changeUserMoral($gUser->id,-round($x/10000));
						sql("UPDATE `user` SET `food`=`food`+$x WHERE `id`=".$gUser->id);
					break;
					case 7:
					case 8:
						?>
						Den armen Opfern werden sämtliche Zahnspangen, Plomben<br>
						und sonstige Implantate entfernt.<br>
						Du erhälst <?=$x?> Metall !<br>
						<?php
						changeUserMoral($gUser->id,-round($x/10000));
						sql("UPDATE `user` SET `metal`=`metal`+$x WHERE `id`=".$gUser->id);
					break;
					case 9:
						$x = floor($x / 100);
						?>
						*zap*rauch*bruzzel* Oh da haben sich wohl ein paar Leute aufgelöst.<br>
						Ah aber ein paar Zaubersteinchen sind geblieben.<br>
						Du erhälst <?=$x?> Runen !<br>
						<?php
						changeUserMoral($gUser->id,-round($x/10000));
						sql("UPDATE `user` SET `runes`=`runes`+$x WHERE `id`=".$gUser->id);
					break;
				}
				$gUser = sqlgetobject("SELECT * FROM `user` WHERE `id` = ".$gUser->id);
			}
		?>
		<form method="post" action="<?=query("info.php?sid=?&x=?&y=?")?>">
		<!--<INPUT TYPE="hidden" NAME="building" VALUE="<?=$gObject->id?>">-->
		<input name="sacrifice" value="0"> Bewohner
		<input type="submit" name="buttom_sacrifice" value="opfern">
		</form>
		<?php } ?>
		<?php profile_page_end(); 
	}
}
?>