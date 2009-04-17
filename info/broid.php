<?php
require_once("../lib.main.php");

$gClassName = "cInfoBroid";
class cInfoBroid extends cInfoBuilding {
	function mycommand () {
		foreach ($_REQUEST as $name=>$val) ${"f_".$name} = $val;
		global $gObject;
		global $gUser;
		global $gRes;
		global $gRes2ItemType;
		
		require_once("../lib.broid.php");
		switch ($f_do) {
			case "zap":
				if ($gUser->admin && ($gObject->x != $f_target_x || $gObject->y != $f_target_y))
					zap($f_target_x,$f_target_y);
			break;
		}
	}
	
	function mydisplay() {
		foreach ($_REQUEST as $name=>$val) ${"f_".$name} = $val;
		global $gUser;
		global $gObject;
		global $gItemType;
		global $gSpellType;
		global $gRes;
		global $gResTypeNames;
		global $gResTypeVars;
		global $gTechnologyType;
		global $gTechnologyGroup;
		?>
		
		B.R.O.I.D. - Blue Ray Of Instant Death<br>
		<br>
		Dieser Turm ist für Administrative Maßnahmen, 
		damit kann	man alles auf beliebigen Koordinaten zerstören.<br>
		<br>
		
		<?php if($gObject->user == $gUser->id) { ?>
			<form action="<?=Query("?sid=?&x=?&y=?")?>" method="post">
			<input type="hidden" name="building" value="broid">
			<input type="hidden" name="id" value="<?=$gObject->id?>">
			<input type="hidden" name="do" value="zap">
			Ziel Koordinaten (<input type="text" size="4" name="target_x">|<input type="text" size="4" name="target_y">)
			<input type="submit" value="zap!!">
			</form>
		<?php }
		
	}
}?>