<?php
$gClassName = "cInfoTaverne";
class cInfoTaverne extends cInfoBuilding {
	function mydisplay() {
		foreach ($_REQUEST as $name=>$val) ${"f_".$name} = $val;
		global $gUser;
		global $gObject;
		?>
		Taverne [<a href="http://zwischenwelt.org/irc/index.php?nick=<?=$gUser->name?>" target="_blank">betreten</a>]
		host : zwischenwelt.org<br>
		port : 7777<br>
		channel : #taverne<br>
		<?php
	}
}?>

