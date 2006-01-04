<?php
require_once("../lib.main.php");
require_once("../lib.message.php");

Lock();
profile_page_start("msg.php");

$root=sqlgetobject("SELECT `id`,`name` FROM `message_folder` WHERE `type`=".kFolderTypeRoot." AND `parent`=0 AND `user`=".$gUser->id);
$sent=sqlgetobject("SELECT `id`,`name` FROM `message_folder` WHERE `type`=".kFolderTypeSent." AND `parent`=0 AND `user`=".$gUser->id);
$berichte=sqlgetobject("SELECT `id`,`name` FROM `message_folder` WHERE `type`=".kFolderTypeExtra." AND `parent`=0 AND `user`=".$gUser->id);

if(!$root){
	createFolder("Eingang",0,$gUser->id,kFolderTypeRoot);
}

if(!$sent){
	createFolder("Ausgang",0,$gUser->id,kFolderTypeSent);
}

if(!$berichte){
	createFolder("Berichte",0,$gUser->id,kFolderTypeExtra);
}

if(!$root || !$sent || !$berichte)Redirect(Query("?sid=?"));

//change msg view checkbox
if(isset($f_saveprev)){
	if(isset($f_preview)){
		sql("UPDATE `user` SET `msgmode`=".intval($f_preview)." WHERE `id`=".$gUser->id);
		$gUser->msgmode = intval($f_preview);
	}
	else if(!isset($f_preview)){
		sql("UPDATE `user` SET `msgmode`=0 WHERE `id`=".$gUser->id);
		$gUser->msgmode = 0;
	}
}

if(isset($f_do)){
	switch ($f_do){
	
		case "edfolder":
			if(isset($f_delete) && $f_delete=="y"){
				$sub="append";
				if(isset($f_delsub) && $f_delsub=="y")
					$sub="delete";
				$msg="inbox";
				if(isset($f_delmsg) && $f_delmsg=="y")
					$msg="delete";
				deleteFolder($f_folder,$sub,$msg);
				
			}
			if(!empty($f_name)){
				if(!isset($f_parent))$f_parent=0;
				sql("UPDATE `message_folder` SET `name`='$f_name',`parent`=".intval($f_parent)." WHERE `user`=".intval($gUser->id)." AND `id`=".intval($f_folder));
			}
			Redirect(Query("?sid=?&show=foldertree"));
		break;
		
		case "deletefolder":
			if(isset($f_folder))
				deleteFolder($f_folder);
			Redirect(Query("?sid=?&show=foldertree"));
		break;
		
		case "newsub":
			if(!empty($f_name) && isset($f_folder))
				createFolder($f_name,$f_folder);
			Redirect(Query("?sid=?&show=foldertree"));
		break;
		
		case "sendmsg":
			if(!empty($f_to) && (!empty($f_text) || !empty($f_fwd))) {
				$f_text = nl2br(htmlspecialchars($f_text));
				if ($f_fwd) {
					$m_fwd=sqlgetobject("SELECT * FROM `message` WHERE `id`=".intval($f_fwd)." AND (`from`=".intval($gUser->id)." OR `to`=".intval($gUser->id).")");
					if ($m_fwd) $f_text .= "<hr>".$m_fwd->text;
				}
				sendMessage($f_to,$gUser->id,$f_subject,$f_text);
				if(isset($f_reply))
					sql("UPDATE `message` SET `status`=".kMsgStatusReply." WHERE (`to`=".intval($gUser->id)." OR `from`=".intval($gUser->id).") AND `id`=".intval($f_reply));
				Redirect(Query("?sid=?&show=content"));
			}
			else Redirect(Query("?sid=?&show=compose&continue_input=1&to=$f_to&text=$f_text&subject=$f_subject"));
		break;
		
		case "nachrichtenliste":
			if(!isset($f_folder))break;
			$msg=sqlgettable("SELECT `id` FROM `message` WHERE `folder`=".intval($f_folder));
			if(isset($f_delete)){
				foreach ($msg as $m)
					if(isset($_POST["c_".$m->id]))sql("DELETE FROM `message` WHERE (`to`=".intval($gUser->id)." OR `from`=".intval($gUser->id).") AND `id`=".intval($m->id));
			}else if(isset($f_verschieben)){
				if(isset($f_target))
					foreach ($msg as $m)
						if(isset($_POST["c_".$m->id]))sql("UPDATE `message` SET `folder`=".intval($f_target)." WHERE (`to`=".intval($gUser->id)." OR `from`=".intval($gUser->id).") AND `id`=".intval($m->id));
			}else if(isset($f_markread)){
				foreach($msg as $m)
					if(isset($_POST["c_".$m->id]))sql("UPDATE `message` SET `status`=".kMsgStatusRead." WHERE (`to`=".intval($gUser->id)." OR `from`=".intval($gUser->id).") AND `id`=".intval($m->id));
			}else if(isset($f_markunread)){
				foreach($msg as $m)
					if(isset($_POST["c_".$m->id]))sql("UPDATE `message` SET `status`=".kMsgStatusUnread." WHERE (`to`=".intval($gUser->id)." OR `from`=".intval($gUser->id).") AND `id`=".intval($m->id));
			}
		break;
		
		case "msgprefs":
			if(!isset($f_message))break;
			if(isset($f_verschieben))
				if(!empty($f_target))sql("UPDATE `message` SET `folder`=".intval($f_target)." WHERE (`to`=".intval($gUser->id)." OR `from`=".intval($gUser->id).") AND `id`=".intval($f_message));
			if(isset($f_del))
				sql("DELETE FROM `message` WHERE (`to`=".intval($gUser->id)." OR `from`=".intval($gUser->id).") AND `id`=".intval($f_message));
			Redirect(Query("?sid=?&show=content&folder=$f_folder"));
		break;
		
		default:
		break;
	}
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 transitional//EN"
   "http://www.w3.org/TR/html4/transitional.dtd">
<html>
<head>
<link rel="stylesheet" type="text/css" href="../styles.css">
<link rel="stylesheet" type="text/css" href="../zwstyle.css">
<title>Zwischenwelt - Nachrichten</title>
<script>
function CheckAll() {
  for (var i = 0; i < document.nachrichtenform.elements.length; i++) {
    if(document.nachrichtenform.elements[i].type == 'checkbox'){
      document.nachrichtenform.elements[i].checked = !(document.nachrichtenform.elements[i].checked);
    }
  }
}
</script>

</head>
<body>

<?php include("../menu.php");

?>
<div align=center width=100% style="vertical-align:top;text-align=center;">
<?ImgBorderStart();?>
<table width=100%>
<tr>
<td style="padding:5px;" align=center valign=middel><a href="<?=Query("?sid=?&show=compose")?>"><img title="Neu Mitteilung verfassen" src="<?=g("gildeforum/neu.png")?>" border=0></a></td>
<td  style="padding-left:30px;">&nbsp;</td>
<td style="padding:5px;" align=center><a href="<?=Query("?sid=?&show=content&folder=".$root->id)?>"><img src="<?=g("post/".(sqlgetone("SELECT COUNT(`id`) FROM `message` WHERE (`to`=".intval($gUser->id)." OR `from`=".intval($gUser->id).") AND `folder`=".$root->id." AND `status`=".kMsgStatusUnread)>0?"inbox-new.png":"inbox.png"))?>" border=0 title="<?=$root->name?>"></a></td>
<td  style="padding-left:30px;">&nbsp;</td>
<td style="padding:5px;" align=center><a href="<?=Query("?sid=?&show=content&folder=".$sent->id)?>"><img src="<?=g("post/".(sqlgetone("SELECT COUNT(`id`) FROM `message` WHERE (`to`=".intval($gUser->id)." OR `from`=".intval($gUser->id).") AND `folder`=".$sent->id." AND `status`=".kMsgStatusUnread)>0?"outbox-new.png":"outbox.png"))?>"  border=0 title="<?=$sent->name?>"></a></td>
<td  style="padding-left:30px;">&nbsp;</td>
<td style="padding:5px;" align=center><a href="<?=Query("?sid=?&show=content&folder=".$berichte->id)?>"><img src="<?=g("post/".(sqlgetone("SELECT COUNT(`id`) FROM `message` WHERE (`to`=".intval($gUser->id)." OR `from`=".intval($gUser->id).") AND `folder`=".$berichte->id." AND `status`=".kMsgStatusUnread)>0?"berichte-new.png":"berichte.png"))?>"  border=0 title="<?=$berichte->name?>"></a></td>
<td  style="padding-left:30px;">&nbsp;</td>
<td style="padding:5px;" align=center><a href="<?=Query("?sid=?&show=foldertree")?>"><img src="<?=g("post/einstellungen.png")?>" border=0 title=Einstellungen></a></td>
<td  style="padding-left:30px;">&nbsp;</td>
<td><form method="post" action="<?=query("?sid=?")?>"><input value=1 type="checkbox" name="preview"<?=($gUser->msgmode==1?" checked":"")?>> Vorschau? <input type="submit" name="saveprev" value="Übernehmen"></form></td>
</tr>
</table>
<?ImgBorderEnd();?>
<br>
<?
if(!isset($f_show))$f_show="content";
if($f_show=="content" && (!isset($f_folder) || empty($f_folder)))$f_folder=$root->id;

switch ($f_show){
	case "message":
		if(!isset($f_message))break;
		$m=sqlgetobject("SELECT * FROM `message` WHERE `id`=".intval($f_message)." AND (`from`=".intval($gUser->id)." OR `to`=".intval($gUser->id).")");
		if(empty($m))break;
		sql("UPDATE `message` SET `status`=".kMsgStatusRead." WHERE `id`=".$m->id);
		ImgBorderStart("s1","jpg","#ffffee","",32,33);?>
		<form method="post" action="<?=Query("?sid=?")?>">
		<input type=hidden name=do value="msgprefs">
		<input type=hidden name=message value="<?=$m->id?>">
		<input type=hidden name=folder value="<?=$m->folder?>">
		<table width=500px;>
		<tr>
		<td>Absender:</td>
		<td><?=($m->type==kMsgTypeGM?sqlgetone("SELECT g.`name` FROM `guild` g, `user` u WHERE g.`id`=u.`guild` AND u.`id`=".intval($m->from))." (".nick($m->from).")":nick($m->from))?></td>
		</tr>
		<tr>
		<td>Empfänger:</td>
		<td><?=($m->type==kMsgTypeGM?sqlgetone("SELECT g.`name` FROM `guild` g, `user` u WHERE g.`id`=u.`guild` AND u.`id`=".intval($m->to))." (".nick($m->to).")":nick($m->to))?></td>
		</tr>
		<tr><td>Datum:</td><td><?=time_output($m->date,"detail")?></td></tr>
		<tr><td>Betreff:</td><td><?=htmlspecialchars($m->subject)?></td></tr>
		<tr><td colspan=2 style="border-top:1px dotted grey;">&nbsp;</td></tr>
		<tr><td colspan=2><?=magictext($m->from==0 || $m->html==1?str_replace("sid=XXX","sid=".$gSID,$m->text):(($m->text)))?></td></tr>
		<tr><td colspan=2>&nbsp;</td></tr>
		<tr><td colspan=2 style="border-top:1px dotted grey;">
			<table>
			<tr>
				<td><input type=submit name=del value="Nachricht L&ouml;schen"></td>
				<td style="padding-left:15px;">&nbsp;</td>
				<td>Nachricht nach </td>
				<td><select name=target><?=PrintObjOptions(sqlgettable("SELECT * FROM `message_folder` WHERE `user`=".$gUser->id),"id","name",$m->folder)?></select></td>
				<td><input type=submit name=verschieben value=Verschieben></td>
				<td style="padding-left:15px;">&nbsp;</td>
				<td><a href="<?=Query("?sid=?&reply=".$m->id."&show=compose")?>"><img title="Antwort verfassen" border=0 src="<?=g("post/reply.png")?>"></a></td>
			</tr>
			<tr>
				<td colspan=7 align="right"><a href="<?=Query("?sid=?&fwd=".$m->id."&show=compose")?>">(weiterleiten)</a></td>
			</tr>
			</table></td></tr>
		</table>
		</form>
		<?ImgBorderEnd("s1","jpg","#ffffee",32,33);
			$next=sqlgetone("SELECT `id` FROM `message` WHERE (`to`=".intval($gUser->id)." OR `from`=".intval($gUser->id).") AND `folder`=".$m->folder." AND `date`<".$m->date." AND `status`=".kMsgStatusUnread." ORDER BY `date` DESC LIMIT 0,1");
			$prev=sqlgetone("SELECT `id` FROM `message` WHERE (`to`=".intval($gUser->id)." OR `from`=".intval($gUser->id).") AND `folder`=".$m->folder." AND `date`>".$m->date." AND `status`=".kMsgStatusUnread." ORDER BY `date` ASC LIMIT 0,1");
		?>
		<p>
		<table>
			<tr>
				<td><?=($next>0?"<a href='".Query("?sid=?&message=$next&show=message")."'>&lt;-- n&auml;chste ungelesene ":"")?></td>
				<td><?=($prev>0?"<a href='".Query("?sid=?&message=$prev&show=message")."'> vorherige ungelesene --&gt;":"")?></td>
			</tr>
		</table>
		</p>
		<?
	break;
	
	case "foldertree":
		ImgBorderStart("s2","jpg","#ffffee","bg-s2",32,33);
		$roots=getRoots();
		?>
		<table border=0 cellspacing=5 cellpadding=2>
		<?foreach($roots as $k){?>
			<tr>
				<td>
					<a href='<?=Query("?sid=?&folder=".$k->id."&show=content")?>'><?=$k->name?></a> (<?=($k->nummsg>0?($k->nummsg>1?$k->nummsg." Nachrichten":$k->nummsg. "Nachricht"):"Keine Nachrichten")?>)
				</td>
				<td><a href="<?=Query("?sid=?&folder=".$k->id."&show=edit")?>"><img title="Postfach bearbeiten" border=0 src="<?=g("edit.png")?>"></a></td>
				<td><a href="<?=Query("?sid=?&folder=".$k->id."&show=newsub")?>"><img title="Unterordner" border=0 src="<?=g("qmark.png")?>"></a></td>
			</tr>
			<tr>
				<td colspan=3 style='padding-left:5px;'>
					<?=renderHirachy(getSubFolders($k->id))?>
				</td>
			</tr>
		<?}?>
		</table>
		<?
		ImgBorderEnd("s2","jpg","#ffffee",32,33);
	break;
	
	case "newsub":
		ImgBorderStart("s2","jpg","#ffffee","bg-s2",32,33);
		if(!isset($f_folder))Redirect(Query("?sid=?"));
		?>
		<form method="post" action="<?=Query("?sid=?")?>">
		<input type="hidden" name="do" value="newsub">
		<input type="hidden" name="folder" value="<?=$f_folder?>">
		<table border=0 cellpadding=0 cellspacing=0>
		<tr><td colspan=2 style="padding:5px;">Einen Unterordner in '<?=sqlgetone("SELECT `name` FROM `message_folder` WHERE `id`=".intval($f_folder)." AND `user`=".intval($gUser->id))?>' anlegen</td></tr>
		<tr><td>Name: </td><td><input type=text size=15 name="name" value=""></td></tr>
		<tr><td colspan=2 align=right><input type=submit name=save value=Anlegen></td></tr>
		</table>
		</form>
		<?ImgBorderEnd("s2","jpg","#ffffee",32,33);
	break;
	
	case "compose":
		ImgBorderStart("s2","jpg","#ffffee","bg-s2",32,33);
		if(!empty($f_to)){
			if(is_numeric($f_to))$m->from=intval($f_to);
			else $m->from=sqlgetone("SELECT `id` FROM `user` WHERE `name`='".addslashes($f_to)."'");
		}
		if(!empty($f_reply)){
			$m=sqlgetobject("SELECT `from`,`subject` FROM `message` WHERE `id`=".intval($f_reply)." AND (`from`=".intval($gUser->id)." OR `to`=".intval($gUser->id).")");
			if(!eregi("^Re:",$m->subject))$m->subject = "Re: ".$m->subject;
		}
		$m_fwd = false;
		if(!empty($f_fwd)){
			$m_fwd=sqlgetobject("SELECT * FROM `message` WHERE `id`=".intval($f_fwd)." AND (`from`=".intval($gUser->id)." OR `to`=".intval($gUser->id).")");
			if(!eregi("^Fwd:",$m_fwd->subject))$m->subject = "Fwd: ".$m_fwd->subject;
		}
		if(!empty($f_subject))$m->subject = $f_subject;
		if(!empty($f_text))$m->text = $f_text;
		if (!isset($m)) $m = false;
		if (!isset($m->subject)) $m->subject = "";
		
		?>
		<form action="<?=query("?sid=?")?>" method="post">
		<input type=hidden name=do value=sendmsg>
		<input type=hidden name=reply value="<?=isset($f_reply)?$f_reply:0?>">
		<input type=hidden name=fwd value="<?=isset($f_fwd)?$f_fwd:0?>">
		<table border=0 style='width:480px;'>
			<tr><th align="left">To:</th><td align="left"><input type=text size=64 name=to value="<?=(isset($f_continue_input) || isset($f_to)?$f_to:nick($m->from,""))?>"></td></tr>
			<tr><th align="left">Subject:</th><td align="left"><input name="subject" type="text" size="64" value="<?=$m->subject?>"></td></tr>
			<tr><td colspan="2" align=center>
			<textarea name="text" cols="75" rows="<?=$m_fwd?4:15?>"></textarea>
			</td></tr>
			<tr><td colspan="2" align="right"><input type="submit" name="send" value="Abschicken"></td></tr>
		</table>
		</form>
		<?php if ($m_fwd) {?>
		<hr>Anhang:</hr>
		<?=magictext($m_fwd->from==0 || $m_fwd->html==1?str_replace("sid=XXX","sid=".$gSID,$m_fwd->text):(($m_fwd->text)))?>
		<?php }?>
		<?	
		ImgBorderEnd("s2","jpg","#ffffee",32,33);
	break;
	
	case "edit":
		$f_folder=intval($f_folder);
		$folder=sqlgetobject("SELECT * FROM `message_folder` WHERE `user`=".intval($gUser->id)." AND `id`=".intval($f_folder));
		ImgBorderStart("s2","jpg","#ffffee","bg-s2",32,33);
		?>
		<form method="post" action="<?=Query("?sid=?")?>">
		<input type="hidden" name="do" value="edfolder">
		<input type="hidden" name="folder" value="<?=$f_folder?>">
		<table border=0 cellpadding=0 cellspacing=0>
		<tr><td>Name: <td><input type=text size=15 name="name" value="<?=$folder->name?>"></td></tr>
		<?if($folder->parent!=0){?><tr><td>Parent: <td><select name="parent"><?=PrintObjOptions(sqlgettable("SELECT `id`,`name` FROM `message_folder` WHERE `user`=".$gUser->id." AND `id`<>".$folder->id." AND (`type`=".$folder->type." OR (`type`=".kFolderTypeRoot." AND ".$folder->type."=".kFolderTypeSub.") OR (`type`=".kFolderTypeSent." AND ".$folder->type."=".kFolderTypeSentSub.") OR (`type`=".kFolderTypeExtra." AND ".$folder->type."=".kFolderTypeExtraSub.")  )"),"id","name",$folder->parent) ?></select></tr><?}?>
		<tr><td colspan=2><table><tr><td>Löschen?</td><td> <input type=checkbox name=delete value=y></td><td>dabei Unterordner löschen?</td><td><input type=checkbox name=delsub value=y></td><td> dabei Nachrichten löschen </td><td><input type=checkbox name=delmsg value=y></td></tr></table></td></tr>
		<tr><td colspan=2>&nbsp;</td></tr>
		<tr><td colspan=2 align=right><input type=submit name=save value=Save></td></tr>
		</table>
		</form>
		<?
		ImgBorderEnd("s2","jpg","#ffffee",32,33);
	break;
	
	case "content": // kein break damit fuer foldercontent automatisch auf default gegangen wird
	default:
		$folder=sqlgetobject("SELECT * FROM `message_folder` WHERE `user`=".intval($gUser->id)." AND `id`=".intval($f_folder));
		$inbox = $folder->type==kFolderTypeSent || $folder->type==kFolderTypeSentSub;
		ImgBorderStart("s2","jpg","#ffffee","bg-s2",32,33);
		$messagescount=sqlgetone("SELECT COUNT(*) FROM `message` WHERE `folder`=".intval($f_folder)." AND (`from`=".intval($gUser->id)." OR `to`=".intval($gUser->id).") ORDER BY `date` DESC");
		$messages=sqlgettable("SELECT `id`,`folder`,`from`,`to`,`subject`,`date`,`status`,`type`,`html`".($messagescount<100?",`text`":"")." FROM `message` WHERE `folder`=".intval($f_folder)." AND (`from`=".intval($gUser->id)." OR `to`=".intval($gUser->id).") ORDER BY `date` DESC");
		?>
		<form method=post action="<?=Query("?sid=?")?>" id="nachrichtenform" name="nachrichtenform">
		<input type="hidden" name="do" value="nachrichtenliste">
		<input type="hidden" name="folder" value="<?=$f_folder?>">
		<table cellspacing=2 cellpadding=2 border=0  width=490px;>
			<tr><th></th><th></th><th><?=$inbox?"Empfänger":"Absender"?></th><th>Betreff</th><th>Datum</th><th></th></tr>
		<?if(count($messages)>0){
			foreach ($messages as $m){?>
			<tr>
			<td><?=msgTypePic($m->type)?></td>
			<td><?=msgStatusPic($m->status)?></td>
			<td align=center><?=htmlspecialchars(nick($inbox?$m->to:$m->from))?></td>
			<td><a href="<?=Query("?sid=?&show=message&message=".$m->id)?>"><?=(strlen($m->subject)>45?htmlspecialchars(substr($m->subject,0,45))."...":htmlspecialchars($m->subject))?></a></td>
			<td align=center><?=time_output($m->date,"overview")?></td>
			<td><input type=checkbox name="c_<?=$m->id?>" value="<?=$m->id?>"></td>
			</tr>
			<?if($gUser->msgmode==1){?>
				<tr><td></td><td></td><td style="padding:5px;background-color:#f0f0f0;border-bottom:solid black 1px" colspan=3><?=nl2br(substr(strip_tags($m->text),0,64))?>...</td></tr>
			<?}?>
		<?}?>
		<tr><td colspan=6 align=right style="padding-top=10px;"><a href="javascript:void(0)" onClick="CheckAll();">Alle Nachrichten markieren</a></td></tr>
		<tr><td colspan=6 align=right style="padding-top=5px;border-bottom:1px dotted grey;">&nbsp;</td></tr>
		<tr>
		<td colspan=6 align=right style="padding-right=10px;padding-top:3px;">
			<table>
				<tr>
					<td>Markiert Nachrichten</td>
					<td>nach</td>
					<td>
						<select name=target>
							<?=PrintObjOptions(sqlgettable("SELECT * FROM `message_folder` WHERE `user`=".$gUser->id),"id","name",$m->folder)?>
						</select>
					</td>
					<td><input type=submit name=verschieben value=Verschieben></td>
				</tr>
				<tr>
					<td></td>
					<td></td>
					<td><input type=submit name=markread value="als gelesen markieren"></td>
					<td><input type=submit name=markunread value="als ungelesen markieren"></td>
				</tr>
				<tr>
					<td></td>
					<td></td>
					<td></td>
					<td><input type=submit name=delete value="L&ouml;schen"></td>
				</tr>
			</table>
		</td>
		</tr>
		<?} else{?>
			<tr><td colspan=6 align=center>Keine Nachrichten in diesem Ordner</td></tr>
		<?}?>
		</table>
		</form>
		<?ImgBorderEnd("s2","jpg","#ffffee",32,33);
	break;

}

?>

</div>
</body>
</html>
<?php profile_page_end(); ?>
