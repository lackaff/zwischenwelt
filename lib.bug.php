<?php

require_once("lib.main.php");

define("BUG_TOPIC_MISC",0);
define("BUG_TOPIC_GRAPHIC",1);
define("BUG_TOPIC_FIGHT",2);
define("BUG_TOPIC_MAGIC",3);
define("BUG_TOPIC_TERRAIN",4);
define("BUG_TOPIC_FORUM",5);
define("BUG_TOPIC_WELTBANK",6);
define("BUG_TOPIC_GUILD",7);
define("BUG_TOPIC_MESSAGE",8);
define("BUG_TOPIC_LAYOUT",9);

//define("BUG_TOPIC_NOOB",8);
//define("BUG_TOPIC_MULTI",9);
//define("BUG_TOPIC_ADMIN",10);


define("BUG_STATUS_OPEN",0);
define("BUG_STATUS_FIXED",1);

$gBugStatusText = array(
	BUG_STATUS_OPEN => "offen",
	BUG_STATUS_FIXED => "repariert"
);

$gBugTopicText = array(
	BUG_TOPIC_GRAPHIC => "Graphik",
	BUG_TOPIC_FIGHT => "Kampf",
	BUG_TOPIC_MAGIC => "Magie",
	BUG_TOPIC_TERRAIN => "Landschaft",
	BUG_TOPIC_FORUM => "Forum",
	BUG_TOPIC_WELTBANK => "Weltbank",
	BUG_TOPIC_GUILD => "Gilde",
	BUG_TOPIC_MESSAGE => "Nachrichtensystem",
	BUG_TOPIC_LAYOUT => "Layout/Homepage",
	BUG_TOPIC_MISC => "Sonstiges"
);

function BugStats(){
	global $gBugTopicText;
	$r = array();
	foreach($gBugTopicText as $id=>$text){
		$r[$id]->unassigned = sqlgetone("SELECT COUNT(*) FROM `bug` WHERE `status`=".BUG_STATUS_OPEN." AND `assigned_user`=0 AND `topic`=".intval($id));
		$r[$id]->assigned = sqlgetone("SELECT COUNT(*) FROM `bug` WHERE `status`=".BUG_STATUS_OPEN." AND `assigned_user`>0 AND `topic`=".intval($id));
		$r[$id]->fixed = sqlgetone("SELECT COUNT(*) FROM `bug` WHERE `status`=".BUG_STATUS_FIXED." AND `topic`=".intval($id));
	}
	return $r;
}

class Bug {
	function Bug($id){
		$o = sqlgetobject("SELECT * FROM `bug` WHERE `id`=".intval($id));
		if($o){
			$o = get_object_vars($o);
			foreach($o as $n=>$v)$this->$n = $v;
		}
	}
}


?>
