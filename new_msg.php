<?
exit(1);
require_once("lib.php");
require_once("lib.message.php");

$user=sqlgettable("SELECT `id` FROM `user`");
foreach($user as $u){
	$root=sqlgetobject("SELECT `id`,`name` FROM `message_folder` WHERE `type`=".kFolderTypeRoot." AND `parent`=0 AND `user`=".$u->id);
	$sent=sqlgetobject("SELECT `id`,`name` FROM `message_folder` WHERE `type`=".kFolderTypeSent." AND `parent`=0 AND `user`=".$u->id);
	$berichte=sqlgetobject("SELECT `id`,`name` FROM `message_folder` WHERE `type`=".kFolderTypeExtra." AND `parent`=0 AND `user`=".$u->id);

	if(!$root){
		createFolder("Eingang",0,$u->id,kFolderTypeRoot);
	}
	
	if(!$sent){
		createFolder("Ausgang",0,$u->id,kFolderTypeSent);
	}
	
	if(!$berichte){
		createFolder("Berichte",0,$u->id,kFolderTypeExtra);
	}
	
	$inbox=sqlgetone("SELECT `id` FROM `message_folder` WHERE `parent`=0 AND `type`=".kFolderTypeRoot." AND `user`=".$u->id);
	$sent=sqlgetone("SELECT `id` FROM `message_folder` WHERE `parent`=0 AND `type`=".kFolderTypeSent." AND `user`=".$u->id);
	$folders = sqlgettable("SELECT id,subject from message where `status` = '-1' AND `from` = '".$u->id."' ");
	foreach ($folders as $f){
		if($f->subject!="SEND")sql("UPDATE `message` SET `folder`=$inbox WHERE `folder`=".$f->id." AND `status`<>-1");
		else sql("UPDATE `message` SET `folder`=$sent WHERE `folder`=".$f->id." AND `status`<>-1");
		sql("DELETE FROM `message` WHERE `id`=".$f->id);
	}
}

?>