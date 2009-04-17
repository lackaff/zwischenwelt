<pre>
<?php

require_once("../lib.php");

$lUser = sqlgettable("SELECT * FROM `user`","id");
$lGuild = sqlgettable("SELECT * FROM `guild`","id");
$lArmy = sqlgettable("SELECT * FROM `army`","id");

$items = 0;

function flushOrphans($otable,$oid,$olink,$ltable,$llink){
	echo "========[ $otable ]==========\n";
	$items = 0;
	$q = "SELECT o.* FROM `$otable` o LEFT OUTER JOIN `$ltable` l ON o.`$olink` = l.`$llink` WHERE l.`$llink` IS NULL";
	echo "[$q]\n";
	$t = sqlgettable($q);
	
	foreach($t as $x){
		echo " -> [".($x->$oid)."] $olink=".($x->$olink)." $llink=".($x->$llink)." -> delete\n";
		sql("DELETE FROM `$otable` WHERE `$oid`=".($x->$oid));
		++$items;
	}
	echo "\n";
	return $items;
}

function flushOrphans2($otable,$oid,$olink1,$olink2,$ltable,$llink1,$llink2){
	echo "========[ $otable ]==========\n";
	$items = 0;
	
	$q = "SELECT o.* FROM `$otable` o LEFT OUTER JOIN `$ltable` l ON o.`$olink1` = l.`$llink1` AND o.`$olink2` = l.`$llink2` WHERE l.`$llink1` IS NULL AND  l.`$llink2` IS NULL";
	echo "[$q]\n";
	$t = sqlgettable($q);
	
	foreach($t as $x){
		echo " -> [".($x->$oid)."] $olink1=".($x->$olink1)." AND $olink2=".($x->$olink2)." AND $llink1=".($x->$llink1)." AND $llink2=".($x->$llink2)." -> delete\n";
		sql("DELETE FROM `$otable` WHERE `$oid`=".($x->$oid));
		++$items;
	}
	echo "\n";
	return $items;
}

$items += flushOrphans("action","id","building","building","id");
$items += flushOrphans("buildinglevel","id","building","building","id");
$items += flushOrphans("buildingname","id","id","building","id");
$items += flushOrphans("buildingparam","id","building","building","id");

// $items += flushOrphans2("hellhole","id","x","y","building","x","y"); // wanderblob hat kein building

$items += flushOrphans("fof_user","id","master","user","id");
$items += flushOrphans("guild_request","id","user","user","id");
$items += flushOrphans("guild_forum_read","id","user","user","id");
$items += flushOrphans("log","id","user","user","id");
$items += flushOrphans("message_folder","id","user","user","id");
$items += flushOrphans("newlog","id","user","user","id");
$items += flushOrphans("technology","id","user","user","id");
$items += flushOrphans("title","id","user","user","id");
$items += flushOrphans("uservalue","user","user","user","id");
$items += flushOrphans("userprofil","id","id","user","id");

$items += flushOrphans("waypoint","id","army","army","id");

$items += flushOrphans("guild_request","id","guild","guild","id");
$items += flushOrphans("guild_forum","id","guild","guild","id");
$items += flushOrphans("guild_forum_comment","id","guild","guild","id");
$items += flushOrphans("guild_msg","id","guild","guild","id");
$items += flushOrphans("guild_pref","id","guild","guild","id");

echo "===== army ====\n";

foreach($lArmy as $a){
	$c = sqlgetone("SELECT COUNT(*) FROM `unit` WHERE `army`=".intval($a->id));
	if($c == 0){
		sql("DELETE FROM `army` WHERE `id`=".intval($a->id));
		echo("DELETE FROM `army` WHERE `id`=".intval($a->id)."\n");
		++$items;
	}
	$c = sqlgetone("SELECT SUM( `amount` ) FROM `unit` WHERE `army`=".intval($a->id));
	if($c < 0.1){
		sql("DELETE FROM `army` WHERE `id`=".intval($a->id));
		echo("DELETE FROM `army` WHERE `id`=".intval($a->id)."\n");
		++$items;
	}

}

echo "\n";
echo "===== remove broken and old crap ====\n";

sql("DELETE FROM `technology` WHERE `type`=0");
$items += mysql_affected_rows();

sql("DELETE FROM `message` WHERE ".time()."-`date` > (60*60*24*365)");
$items += mysql_affected_rows();

echo "\n";

echo "###################################################\n";
echo "## $items items deleted\n";
echo "###################################################\n";

?>
</pre>
