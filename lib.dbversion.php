<?php

// db version stuff
function GetCurDBVersion () {
	if(TableExists("dbversion")){
		$v = sqlgetone("SELECT `version` FROM `dbversion` LIMIT 1");
		return $v ? intval($v) : 0;
	} else {
		return 0;
	}
}

function RequireExactDBVersion ($v) {
	$cur = GetCurDBVersion();
	if ($v != $cur) exit( "ERROR, dbversion $v required, found $cur<br>" );
}

function SetCurDBVersion ($v) {
	sql("UPDATE `dbversion` SET `version`=".intval($v));
}

function UpgradeDBVersion ($min) {
	if (GetCurDBVersion() < $min) SetCurDBVersion($min);
}


?>