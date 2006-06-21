<?php
require_once("lib.main.php");

define("kFolderTypeRoot",0);
define("kFolderTypeSub",1);
define("kFolderTypeExtra",2);
define("kFolderTypeExtraSub",4);
define("kFolderTypeSent",3);
define("kFolderTypeSentSub",5);

define("kMsgTypeNormal",0);
define("kMsgTypeReport",1);
define("kMsgTypeGM",2);
define("kMsgTypeSpecial",3);

define("kMsgStatusUnread",1);
define("kMsgStatusRead",0);
define("kMsgStatusReply",2);

function sendMessage($to,$from,$subject,$text,$type=0,$tosent=TRUE)
{
	if(empty($subject)){
		if(file_exists(MSG_BELEIDIGUNG)){
			$l = file(MSG_BELEIDIGUNG);
			$len = sizeof($l);
			$i = rand(0,$len-1);
			$subject = trim($l[$i]);
			$text = "Der Betreff enthält einen zufälligen Text,\nda der Absender keinen eingegeben hat!\n----------------------------------------------------\n".$text;
		} else $subject = " - KEIN BETREFF - ";
	}
	if(!empty($to) && !empty($subject) && !empty($text)){
		if(is_numeric($to))$msg->to=intval($to);
		else $msg->to=intval(sqlgetone("SELECT `id` FROM `user` WHERE `name`='".addslashes($to)."'"));
		if($msg->to==0)return FALSE;
		if(is_numeric($from))$msg->from=intval($from);
		else $msg->from=intval(sqlgetone("SELECT `id` FROM `user` WHERE `name`='".addslashes($from)."'"));
		$msg->subject=$subject;
		$msg->text=$text;
		$msg->type=$type;
		$msg->date=time();
		switch($type){
			case kMsgTypeReport:
				$msg->folder=sqlgetone("SELECT `id` FROM `message_folder`  WHERE `parent`=0 AND `type`=".kFolderTypeExtra." AND `user`=".$msg->to);
			break;
			
			default:
				$msg->folder=sqlgetone("SELECT `id` FROM `message_folder`  WHERE `parent`=0 AND `type`=".kFolderTypeRoot." AND `user`=".$msg->to);
			break;
		}
		sql("INSERT INTO `message` SET ".obj2sql($msg));
		//send igm via mail
		if($msg->to > 0){
			$u = sqlgetobject("SELECT * FROM `user` WHERE `id`=".intval($msg->to)." LIMIT 1");
			$f = sqlgetobject("SELECT * FROM `user` WHERE `id`=".intval($msg->from)." LIMIT 1");
			if(empty($f))$u->name = "Server";
			if(!empty($u) && $u->flags & kUserFlags_SendIgmPerMail>0 && !empty($u->mail)){
				//this user wants igms per mail and has set a mail addy
				mail($u->mail, "[ZW IGM] $msg->subject von $f->name", "$f->name hat Ihnen folgende Nachricht geschickt\n\n   ~~~\n\n$msg->text","From: ".ZW_MAIL_SENDER."\r\nReply-To: ".ZW_MAIL_SENDER."\r\nX-Mailer: PHP/" . phpversion()); 
			}
		}
		//store in send folder
		if($tosent){
			if($from!=0)
				$msg->folder=sqlgetone("SELECT `id` FROM `message_folder` WHERE `parent`=0 AND `type`=".kFolderTypeSent." AND `user`=".$msg->from);
			$msg->status=kMsgStatusRead;
			sql("INSERT INTO `message` SET ".obj2sql($msg));
		}
	}
}

function time_output($timestamp, $show)
{
	$returnstring = "";

	switch(date("w", $timestamp))
	{
		case 0: $wday = "Sonntag"; break;
		case 1: $wday = "Montag"; break;
		case 2: $wday = "Dienstag"; break;
		case 3: $wday = "Mittwoch"; break;
		case 4: $wday = "Donnerstag"; break;
		case 5: $wday = "Freitag"; break;
		case 6: $wday = "Samstag"; break;
	}
	
	$one_day = (24*60)*60;
	
	$today = mktime(0,0,1);
	$yesterday = $today-$one_day;
	
	if($show == "overview")
	{
		if($timestamp > $today)
			$returnstring = "Heute um ".date("G:i", $timestamp);
		elseif($timestamp > $yesterday)
			$returnstring = "Gestern um ".date("G:i", $timestamp);
		elseif($timestamp > ($today - (7 * $one_day)))
			$returnstring = "letzten ".$wday;
		elseif($timestamp > ($today - (14 * $one_day)))
			$returnstring = "Is a bissal her";
		elseif($timestamp > ($today - (30 * $one_day)))
			$returnstring = "Lang ist's her";
		elseif($timestamp > ($today - (60 * $one_day)))
			$returnstring = "Es war einmal...";
		elseif($timestamp > ($today - (120 * $one_day)))
			$returnstring = "Anno Domini";
	}
	elseif($show == "detail")
	{
		if($timestamp > $today)
			$returnstring = "Heute um ".date("G:i", $timestamp);
		elseif($timestamp > $yesterday)
			$returnstring = "Gestern um ".date("G:i", $timestamp);
		else
		{
			$returnstring = $wday." den ".date("d.m.Y", $timestamp)." um ".date("G:i", $timestamp);
		}
	}
	
	return $returnstring;
}

function createFolder($name,$parent=0,$user=0,$type=kFolderTypeSub){
	global $gUser;
	if($user==0)$user=$gUser->id;
	if($parent!=0){
		$p=sqlgetobject("SELECT * FROM `message_folder` WHERE `id`=".$parent);
		if($p->type==kFolderTypeRoot || $p->type==kFolderTypeSub) $type=kFolderTypeSub;
		if($p->type==kFolderTypeSent || $p->type==kFolderTypeSentSub) $type=kFolderTypeSentSub;
		if($p->type==kFolderTypeExtra || $p->type==kFolderTypeExtraSub) $type=kFolderTypeExtraSub;
	}
	sql("INSERT INTO `message_folder` set `name`='$name',`parent`=".intval($parent).",`user`=".intval($user).",`type`=".intval($type));
}

//subtree "append" || "delete" ... append changes the parent of the subtreeroots to the folders parent
//messages "delete" || "inbox"  ...delete all messages in this folder or move them to inbox
function deleteFolder($id,$subtree="append",$messages="delete"){
	$id=intval($id);
	$folder=sqlgetobject("SELECT * FROM `message_folder` WHERE `id`=".$id);
	if($folder->parent!=0)$parent=sqlgetobject("SELECT * FROM `message_folder` WHERE `id`=".$folder->parent);
	if($messages=="delete"){
		sql("DELETE FROM `message` WHERE `folder`=$id");
	}else{
		if(isset($parent) && $parent)
			$type=$parent->type;
		else
			$type=$folder->type;
		sql("UPDATE `message` m, `message_folder` mf SET m.`folder`=mf.`id` WHERE m.`folder`=$id AND mf.`user`=".$folder->user." AND mf.`type`=$type");
	}
	if($subtree=="delete"){
		$subfolders = sqlgettable("SELECT * FROM `message_folder` WHERE `parent`=".$id);
		foreach ($subfolders as $sf)
			deleteFolder($sf->id,$subtree,$messages);
	}else{
		sql("UPDATE `message_folder` SET `parent`=".$folder->parent." WHERE `parent`=$id");
	}
	sql("DELETE FROM `message_folder` WHERE `id`=$id");
}

function renderHirachy($t){
	if(empty($t))return "";
	else
	{
		$s = "";
		$l = sizeof($t);
		
		$s = "<table cellpadding=\"0\" cellspacing=\"0\" border='0' width=100%>";
		
		for($i = 0;$i < $l; ++$i)
		{
			if($i == $l-1)$last = true; else $last = false;
			$k = $t[$i];
			
			$img = g("gildeforum/tree_".(($last)?"l":"t").".gif");
			$s .= "<tr><td><img src='".$img."'></td><td><a href='".Query("?sid=?&folder=".$k->id."&show=content")."'>".$k->name."</a> (".($k->nummsg>0?($k->nummsg>1?$k->nummsg." Nachrichten":$k->nummsg. "Nachricht"):"Keine Nachrichten").")</td>
			<td style='padding-left:7px;padding-right:7px;'><a href=".Query("?sid=?&folder=".$k->id."&show=edit")."><img title='Postfach bearbeiten' border=0 src='".g("edit.png")."'></a></td>
			".(sqlgetone("SELECT `parent` FROM `message_folder` WHERE `id`=".$k->parent)!=0?"<td style='padding-left:7px;padding-right:7px;'><img border=0 src='".g("up.png")."'></td>":"<td style='padding-left:7px;padding-right:7px;'><img src='".g("12px.png")."'></td>")."
			<td style='padding-left:7px;padding-right:7px;'><a href=".Query("?sid=?&folder=".$k->id."&show=newsub")."><img title='Unterordner' border=0 src='".g("qmark.png")."'></a></td>
			<td style='padding-left:7px;padding-right:7px;'><a href=".Query("?sid=?&folder=".$k->id."&do=deletefolder")."><img border=0 src='".g("del.png")."'></a></td>
			</tr>";
			if(is_array($k->tree))
				$s .= "<tr><td".($last?"":" style=\"background:url(".g("gildeforum/tree_i.gif").") repeat-y top left\"").">".(count($k->tree)>0 && !$last?"&nbsp;":"")."</td><td colspan=5>".renderHirachy($k->tree)."</td></tr>";
		}
		$s .= "</table>";

		return $s;
	}
}

function getRoots($user=0){
	if($user==0){
		global $gUser;
		$user=$gUser->id;
	}else
		$user=intval($user);
	$r = sqlgettable("SELECT * FROM `message_folder` WHERE `parent`=0 AND `user`=$user","id");
	foreach($r as $k=>$root)
		$r[$k]->nummsg=sqlgetone("SELECT COUNT(id) FROM `message` WHERE `folder`=".$root->id);
	return $r;
}

function getSubfolders($id){
	$r = sqlgettable("SELECT * FROM `message_folder` WHERE `parent`=".intval($id));
	$i=0;
	$t=array();
	if(count($r>0))
		foreach ($r as $o){
			$t[$i]=$o;
			$t[$i]->nummsg=sqlgetone("SELECT COUNT(`id`) FROM `message` WHERE `folder`=".intval($o->id));
			$t[$i++]->tree=getSubfolders($o->id);
		}
	return $t;
}

function msgTypePic($type){
	switch($type){
		case kMsgTypeGM:
			return "<img title='Gildennachricht' src='".g("post/mail-guild.png")."' border=0>";
		case kMsgTypeReport:
			return "<img title='Bericht' src='".g("post/berichte.png")."' border=0>";
		case kMsgTypeSpecial:
			return "<img title='Spezial' src='".g("post/mail-spezial.png")."' border=0>";
	}
}

function msgStatusPic($status){
	switch ($status){
		case kMsgStatusUnread:
			return "<img title='Ungelesene Nachricht' src='".g("post/mail-new.png")."' border=0>";
		case kMsgStatusReply:
			return "<img title='Beantwortete Nachricht' src='".g("post/mail-replied.png")."' border=0>";
	}
}
?>
