<?php
require_once("lib.php");
require_once("lib.main.php");
require_once("lib.guild.php");

define("kUnreadTypeA",0);
define("kUnreadTypeC",1);

function addArticle($head,$content){
	$date=time();
	global $gUser;
	sql("insert into `guild_forum` (`guild`,`user`,`date`,`content`,`head`) values (".$gUser->guild.",".$gUser->id.",$date,'".addslashes($content)."','".addslashes($head)."')");
	$ref=mysql_insert_id();
	$ulist = sqlgettable("SELECT `id` FROM `user` WHERE `guild`=".$gUser->guild);
	foreach ($ulist as $u)
		sql("INSERT INTO `guild_forum_read` SET `user`=".$u->id.",`type`=0,`ref`=$ref");
	return TRUE;
}

function editArticle($id,$head,$content){
	$date=time();
	global $gUser,$gGuild;
	if(gForumEditA($gUser->id,$id)){
		sql("update `guild_forum` set `head`='".addslashes($head)."',`content`='".addslashes($content)."' where `id`=".intval($id));
		return TRUE;
	}else
	return FALSE;
}

function getNewArticles(){
	global $gUser;
	$s= sqlgettable("SELECT g.* FROM `guild_forum`  g, `guild_forum_read` r WHERE g.id=r.ref AND r.type=0 AND r.user=".$gUser->id." ORDER BY g.date DESC","id");
	$r= sqlgettable("SELECT * FROM `guild_forum` WHERE `guild`=".$gUser->guild." ORDER BY `date` DESC LIMIT 0,5","id");
	$u= sqlgettable("SELECT gf.* FROM `guild_forum` gf, `guild_forum_comment` gc,`guild_forum_read` gr WHERE gf.guild=".$gUser->guild." AND gf.id=gc.article AND gc.id=gr.ref AND gr.user=".$gUser->id." ORDER BY `date` DESC","id");
	
	foreach ($u as $o)
		$u[$o->id]->new=FALSE;
		
	foreach ($s as $o)
		$s[$o->id]->new=TRUE;
	
	foreach ($r as $o)
		$r[$o->id]->new=FALSE;
	
	if(count($u)>0)
		$r=& array_merge2($r,$u);
		
	if(count($r)>0 && count($s)>0)
		$t=& array_merge2($r,$s);
	else if(count($s)==0)
		$t=$r;
	
	foreach ($t as $o)
		$t[$o->id]->nc=sqlgetone("SELECT COUNT(c.id) FROM guild_forum_read r, guild_forum_comment c WHERE c.id=r.ref AND c.article=".$o->id." AND r.`user`=".$gUser->id);
	
	return $t;
}

function MarkAllForumRead(){
	global $gUser;
	if($gUser)sql("DELETE FROM `guild_forum_read` WHERE `user`=".intval($gUser->id));
}

function getArticle($id){
	global $gUser;
	sql("DELETE FROM `guild_forum_read` WHERE `ref`=$id AND `user`=".$gUser->id." AND `type`=0");
	if($gUser->flatview==1) {
		$arr = sqlgettable("SELECT `id` FROM `guild_forum_comment` WHERE `article`=$id");
		foreach($arr AS $o)
			sql("DELETE FROM `guild_forum_read` WHERE `ref`=".$o->id." AND user=".$gUser->id." AND type=1");
	}
	return sqlgetobject("SELECT * FROM `guild_forum` WHERE `guild`=".$gUser->guild." AND `id`=".intval($id));
}

function getArticles($start,$limit){
	// just to be sure they are set
	if(empty($start))$start = 0;
	if(empty($limit))$limit = 10;
	global $gUser;
	$r= sqlgettable("SELECT * FROM `guild_forum` WHERE `guild`=".$gUser->guild." ORDER BY `date` DESC LIMIT ".intval($start).",".intval($limit),"id");
	$s= sqlgettable("SELECT g.* FROM `guild_forum`  g, `guild_forum_read` r WHERE g.id=r.ref AND r.type=0 AND r.user=".$gUser->id." ORDER BY g.date DESC","id");
	
	foreach ($s as $o)
		$s[$o->id]->new=TRUE;
	
	foreach ($r as $o)
		$r[$o->id]->new=FALSE;
		
	if(count($r)>0 && count($s)>0)
		$t=& array_merge2($r,$s);
	else if(count($s)==0)
		$t=$r;
		
	foreach ($t as $o)
		$t[$o->id]->nc=sqlgetone("SELECT COUNT(r.`user`) FROM guild_forum_read r, guild_forum_comment c WHERE r.ref=c.id AND c.article=".$o->id." AND r.user=".$gUser->id);
	
	return $t;
}

function delArticle($id){
	global $gUser,$gGuild;
	if(gForumDelA($gUser->id,$id)){
		sql("DELETE FROM `guild_forum` WHERE `guild`=".$gUser->guild." AND `id`=".intval($id));
		sql("DELETE FROM `guild_forum_comment` WHERE `article`=".intval($id)." AND `guild`=".$gUser->guild);
		return TRUE;}
	else
		return FALSE;
}


function numArticles(){
	global $gUser;
	return sqlgetone("SELECT COUNT(`id`) FROM `guild_forum` WHERE `guild`=".$gUser->guild);
}

function addComment($article,$ref,$head,$comment){
	$date=time();
	global $gUser;
	$ulist= sqlgettable("SELECT `id` FROM `user` WHERE `guild`=".$gUser->guild);
	sql("INSERT INTO `guild_forum_comment` (`ref`,`article`,`user`,`comment`,`head`,`date`,`guild`) values (".intval($ref).",".intval($article).",".$gUser->id.",'".addslashes($comment)."','".addslashes($head)."',$date,".$gUser->guild.")");
	$ref=mysql_insert_id();
	foreach ($ulist as $u)
		sql("INSERT INTO `guild_forum_read` SET `user`=".$u->id.",`type`=1,`ref`=$ref");
}

function getComment($id){
	global $gUser;
	sql("DELETE FROM `guild_forum_read` WHERE `ref`=$id AND `user`=".$gUser->id." AND `type`=1");
	return sqlgetobject("SELECT * FROM `guild_forum_comment` WHERE `guild`=".$gUser->guild." AND `id`=".intval($id));
}

function getComments($article,$ref=-1,$order="DESC"){
	global $gUser;
	return sqlgettable("SELECT * FROM `guild_forum_comment` WHERE (".($ref>-1?"`ref`=".intval($ref):"1").") AND `guild`=".$gUser->guild." AND `article`=".intval($article)." ORDER BY `date` $order");
}

//delete ONE comment
function delComment($id){
	global $gUser;
	if(gForumDelC($gUser->id,$id)){
		sql("DELETE FROM `guild_forum_comment` WHERE `guild`=".$gUser->guild." AND `id`=".intval($id));
		sql("UPDATE `guild_forum_comment` SET `ref`=0 WHERE `ref`=".intval($id)." AND `guild`=".$gUser->guild);
		return TRUE;
	}else
		return FALSE;
}

function editComment($id,$head,$comment){
	global $gUser;
	if(gForumEditC($gUser->id,$id))
		sql("UPDATE `guild_forum_comment` SET `head`='".addslashes($head)."',`comment`='".addslashes($comment)."' WHERE `id`=".intval($id));
}

function &getRef($ref){
	global $gUser;
	return sqlgettable("SELECT * FROM `guild_forum_comment` WHERE `id`=".intval($ref)." AND `guild`=".$gUser->guild);
}

function &getFollowers($id){
	global $gUser;
	return sqlgettable("SELECT * FROM `guild_forum_comment` WHERE `ref`=".intval($id)." AND `guild`=".$gUser->guild);
}

function numComments($article){
	global $gUser;
	return intval(sqlgetone("SELECT COUNT(`id`) FROM `guild_forum_comment` WHERE `article`=".intval($article)." AND `guild`=".$gUser->guild));
}


function getCommentsOnArticle($article,$depth)
{
	$r =getComments($article,$depth);
	foreach ($r as $o)
		$t[] = getC($o->id);
	return $t;	
}


function getC($id)
{
	global $gUser;
	$o = getComment($id);
	$o->tree = getCommentSubTree($id);
	return $o;
}

function getCommentSubTree($id)
{
	$t=array();
	global $gUser;
	$r = sqlgettable("SELECT `id` FROM `guild_forum_comment` WHERE `ref`=".intval($id)." AND `guild`=".$gUser->guild);
	foreach ($r as $o)
		$t[] = getC($o->id);
	return $t;
}

function render_tree($t)
{
	global $gUser;
	$mark="style='color:#9d0000;'";
	if(empty($t))return "";
	else
	{
		$s = "";
		$l = sizeof($t);
		
		$s = "<table cellpadding=\"0\" cellspacing=\"0\" border='0'>";
		
		for($i = 0;$i < $l; ++$i)
		{
			if($i == $l-1)$last = true; else $last = false;
			$k = $t[$i];
			
			$img = g("gildeforum/tree_".(($last)?"l":"t").".gif");
			$s .= "<tr><td><img src='$img'></td><td><a ".($k->date-$gUser->lastlogin>0?$mark:"")." href='".Query("?sid=?&guild=".$k->guild."&article=".$k->article."&id=".$k->id)."'>&nbsp;".substr($k->head,0,37).(strlen($k->head)>37?"...":"")."</a> von <a href='".query("../info/msg.php?sid=?&show=compose&to=".nick($k->user))."' >".nick($k->user)."</a> am ".date("j.m. G:i",$k->date)."</td></tr>";
			
			if(is_array($k->tree))
				$s .= "<tr><td".($last?"":" style=\"background:url(".g("gildeforum/tree_i.gif").") repeat-y top left\"").">".(count($k->tree)>0 && !$last?"&nbsp;":"")."</td><td>".render_tree($k->tree)."</td></tr>";
		}
		$s .= "</table>";

		return $s;
	}
}

function showcomments($article){
	global $gUser,$a;
	$cs=getComments($article,-1,"ASC");
	$mark="style='color:#9d0000;'";
	if(count($cs)<1)
		$s="Keine Kommentare";
	else{
		echo "<table cellpadding=\"0\" cellspacing=\"0\">";
		foreach($cs as $c){?>
			<tr><td colspan=3>&nbsp;</td></tr>
			<tr><td colspan=3 style=""><a href="<?=query("msg.php?sid=?&show=compose&to=".urlencode(nick($c->user)))?>"><?=nick($c->user)?></a> schreibt dazu am <?=date("j.m. G:i",$c->date)?></td></tr>
			<tr><td colspan=3 style="border-bottom:1px dotted grey;" align=right><?=htmlspecialchars($c->head)?></td></tr>
			<tr><td colspan=3 style="padding-top:5px;"><?=nl2br(htmlspecialchars($c->comment))?></td></tr>
			<tr><td colspan=3 style="padding-top:10px;">
			<table cellspacing=0 cellpadding=0 border=0 width=100%>
			<tr>
			<td width=100%>&nbsp;</td>
			<td style="padding-left:3px;padding-right:3px;"><a href="<?=Query("?sid=?&article=".$a->id."&do=cc&id=".$c->id)?>"><img border=0 title="Kommentieren" alt="K" src="<?=g("gildeforum/comment.png")?>"></a></td>
			<td style="padding-left:3px;padding-right:3px;"><?=(gForumEditC($gUser->id,$c->id)?'<a href="'.Query("?sid=?&article=".$a->id."&do=ec&id=".$c->id).'"><img border=0  title="Editieren" alt="E" src="'.g("gildeforum/edit.png").'"></a>':"")?></td>
			<td style="padding-left:3px;padding-right:3px;"><?=(gForumDelC($gUser->id,$c->id)?'<a href="'.Query("?sid=?&article=".$a->id."&do=dc&id=".$c->id).'"><img border=0  title="L&ouml;schen" alt="L" src="'.g("gildeforum/delete.png").'"></a>':"")?></td>
			</tr>
			</table>
			</td>
			</tr>
			<tr><td colspan=3 style="padding-top:10px;"><hr></tr></tr>
		<?
			
		}
		echo "</table>";
	}
}
?>