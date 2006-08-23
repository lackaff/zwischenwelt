<?php
require_once("../lib.php");
require_once("../lib.terrain.php");

$tables = array("terrain","building");

foreach($tables as $t){
	$row = 0;
	$found = 0;
	do {
		$l = sqlgettable("SELECT * FROM `$t` LIMIT $row,128");
		$row += sizeof($l);
		foreach($l as $o){
			if(!is_numeric($o->nwse)){
				$code = 0;
				if(strpos($o->nwse,"n") !== false)$code |= kNWSE_N;
				if(strpos($o->nwse,"w") !== false)$code |= kNWSE_W;
				if(strpos($o->nwse,"s") !== false)$code |= kNWSE_S;
				if(strpos($o->nwse,"e") !== false)$code |= kNWSE_E;
				$o->nwse = $code;
				sql("UPDATE `$t` SET `nwse`='$code' WHERE `id`=".$o->id);
				//echo ".";
				++$found;
				/*
				if($found % 10 == 0){
					echo "<br>";
					flush();
				}
				*/
			}
		}
	} while(sizeof($l)>0);
	echo "<hr>$row $t fields checked and $found converted<br>";
}

?>