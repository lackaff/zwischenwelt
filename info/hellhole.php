<?php
require_once("../lib.main.php");
require_once("../lib.army.php");



$gClassName = "cInfoHellHole";
class cInfoHellHole extends cInfoBuilding {
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
				
		profile_page_start("hellhole.php");
		?>
		
		<?php
		
		$hellhole = sqlgetobject("SELECT * FROM `hellhole` WHERE `x` = ".$gObject->x." AND `y` = ".$gObject->y." LIMIT 1");
		
		if ($gUser->admin) {
			$gMonsterType = sqlgettable("SELECT * FROM `unittype` WHERE `flags` & ".kUnitFlag_Monster,"id");
			
			if (!$hellhole) {
				$hellhole->x = $gObject->x;
				$hellhole->y = $gObject->y;
				$hellhole->type = 0;
				$hellhole->num = 5; // no more than 5 monsters
				$hellhole->armysize = 50; // how many monsters per army
				$hellhole->spawndelay = 60*60*2; // 1 monster every 2 hours
				$hellhole->spawntime = time()+120; 
				$hellhole->lastupgrade = time(); 
				$hellhole->totalspawns = 0; 
				$hellhole->radius = 8; // monsters move within this radius
				sql("INSERT INTO `hellhole` SET ".obj2sql($hellhole));
				$hellhole->id = mysql_insert_id(); 
			}
			$monsters = sqlgettable("SELECT * FROM `army` WHERE `hellhole`=".$hellhole->id);
			echo "<pre>";
			foreach($monsters as $m)echo "-$m->name ($m->x,$m->y)\n";
			echo sizeof($monsters)." Monster im Bereich des Hellholes\n";
			echo "</pre>";
		}
		$hellhole->level=$gObject->level;
		?>
		
		totalspawns : <?=$hellhole->totalspawns?><br>
		level: <?=$hellhole->level?><br>
		bis zum nächsten spawn: <?=round(($hellhole->spawntime - time()) / 3600,2)?>h<br>
		<br>
		
		<a target="_blank" href="<?=kURL_Nethack?>" style="font:12px;color:red">Hellhole betreten</a>
		
		<?php profile_page_end(); 	
	}
}?>