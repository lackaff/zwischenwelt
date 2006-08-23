<?php
require_once("../lib.main.php");
require_once("../lib.message.php");

//Lock();
profile_page_start("archivate.php");

function show($a){print "<pre>"; print_r($a); print "</pre>";}
$user = sqlgettable("SELECT name, id FROM user");

$count = 0;
$counttoroot = 0;
foreach($user as $euser)
{
	
	if(($result = sqlgetone("SELECT count(id) FROM message WHERE `subject` = 'root' AND `unread` = '-1' AND `from` = '".$euser->id."' ")) == 0)
	{
		$count++;
		addDirectory($euser->id,"root",0);
		$id = sqlgetone("SELECT id from message where `subject` = 'root' AND `unread` = '-1' AND `from` = '".$euser->id."' ");
		addDirectory($euser->id,"SEND",$id);
	}
	
	$inbox = sqlgetone("SELECT `id` FROM `message` WHERE `subject` = 'root' AND `unread` = '-1' AND `from` = '".$euser->id."' ");
	$sentbox = sqlgetone("SELECT `id` FROM `message` WHERE `subject` = 'SEND' AND `unread` = '-1' AND `from` = '".$euser->id."' ");
	
	sql("UPDATE `message` SET `folder` = ".$inbox." WHERE `to` = ".$euser->id." AND `folder` = 0");
	sql("UPDATE `message` SET `folder` = ".$sentbox." WHERE `to` = ".$euser->id." AND `folder` = 1");
}

print $count ." neue root Verzeichnisse angelegt";
print "<br>Alle fragen auf root ausgerichtet";
profile_page_end();
?>