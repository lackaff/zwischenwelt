<?php
require_once("../lib.guildforum.php");

Lock();

profile_page_start("guild_log.php");

function jumplane($start,$num){?>
	<table cellspacing=0 border=0 cellpadding=0><tr>
<?	for($i=0;$i<$num;$i+=10){?>
		<td align=center>[<?=($i!=$start?'<a href="'.Query("?sid=?&start=$i").'">'.$i.'-'.($i+10).'</a>':$i.'-'.($i+10))?>]</td>
	<?}
	?></tr></table><?
}
//ist der user in einer gilde?
if($gUser->guild > 0)
{//gilde vorhanden ------------------------------------------------------------

	if(isset($f_flatview) && $gUser->flatview!=$f_flatview){
		sql("UPDATE `user` SET `flatview`=".intval($f_flatview)." WHERE `id`=".$gUser->id);
		$gUser->flatview=$f_flatview;
	}

	$gGuild = sqlgetobject("SELECT g.*,u.`name` as `foundername` FROM `guild` g,`user` u WHERE u.`id`=g.`founder` AND g.`id`=".$gUser->guild);



	if(isset($f_do)){
		switch($f_do){
			case "savec":
				if(empty($f_ref))$f_ref=0;
				if(!empty($f_article))addComment($f_article,$f_ref,$f_head,$f_comment);
			break;
			case "savea":
				if(!empty($f_head) && !empty($f_content)) addArticle($f_head,$f_content);
				$f_article=sqlgetone("SELECT `id` FROM `guild_forum` WHERE `guild`=".$gGuild->id." ORDER BY `date` DESC LIMIT 1");
				$f_do="";
			break;
			case "saveec":
				if(!empty($f_head) && !empty($f_comment) && !empty($f_id)) editComment($f_id,$f_head,$f_comment);
			break;
			case "saveea":
				if(!empty($f_head) && !empty($f_content) && !empty($f_article)) editArticle($f_article,$f_head,$f_content);
			break;
			case "dc":
					if(gForumDelC($gUser->id,$f_id)){
						delComment($f_id);
						$f_id="";
						$f_do="";
					}
				break;
			case "da":
					if(gForumDelA($gUser->id,$f_article)){
						delArticle($f_article);
						$f_article="";
						$f_id="";
					}
			break;
			default:
			break;
		}
	}
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 transitional//EN"
   "http://www.w3.org/TR/html4/transitional.dtd">
<html>
<head>
<link rel="stylesheet" type="text/css" href="<?=GetZWStylePath()?>">
<title>Zwischenwelt - Gilde</title>

</head>
<body style="font-family:serif">

<?php include("../menu.php"); ?>
<?=renderGuildTabbar(2)?>
<?php

//ist der user in einer gilde?
if($gUser->guild == 0)
{//neeee ------------------------------------------------------------
?>
	Sie befinden sich in keiner Gilde!
<?php
}
else
{//gilde vorhanden ------------------------------------------------------------

if(!empty($gGuild->forumurl)){
        ?>
	<br><br>
	<center>
	Das Gildenforum befindet sich hier: <a target="_blank" href="<?=$gGuild->forumurl?>"><?=$gGuild->forumurl?></a>
	</center>
	<?php
	exit;
}
			


if(!isset($f_article) && (!isset($f_do))){
	if(empty($f_start))$f_start=0;
	$article=getArticles($f_start,10);
	$bg='style="background:url('.g("papyrus/buch.jpg").');padding-left:60px;"';
}
else if(!isset($f_do) && (!isset($f_article)))
	$bg="";
else if(isset($f_do) && $f_do=="da"){
	if(empty($f_start))$f_start=0;
	$article=getArticles($f_start,10);
	$bg='style="background:url('.g("papyrus/buch.jpg").');padding-left:60px;"';
}else
	$bg="";

?>
<div width="100%" align=center <?=$bg?>>
<?
if(!empty($f_article)){
	$a=getArticle($f_article);
	if(empty($f_ref))$f_ref=0;
	if(!empty($f_id))$c=getComment($f_id);
	ImgBorderStart("s2","jpg","#ffffee","bg-s2",32,33);?>
	<table border=0 cellspacing=0>
	<tr  width="100%"><td>Author:</td><td width=40>&nbsp;</td><td style="padding-left:15px;"><a href="<?=query("msg.php?sid=?&show=compose&to=".urlencode(nick($a->user)))?>"><?=nick($a->user)?></a></td></tr>
	<tr><td>Datum:</td><td width=40>&nbsp;</td><td style="padding-left:15px;"><?=date("j.m. G:i",$a->date)?></td></tr>
	<tr><td>Betreff:</td><td width=40>&nbsp;</td><td style="padding-left:15px;"><?=$a->head?></td></tr>
	<tr><td colspan=3 style="padding-top:10px;border-top:1px dotted grey;"><?=nl2br(htmlspecialchars($a->content))?></td></tr>
	<tr><td colspan=3 style="padding-top:10px;">
	<table cellspacing=0 cellpadding=0 border=0 width=100%>
	<tr>
	<td width=100%>&nbsp;</td>
	<td style="padding-left:3px;padding-right:3px;"><a href="<?=Query("?sid=?&article=".$a->id."&do=ca")?>"><img border=0 title="Kommentieren" alt="K" src="<?=g("gildeforum/comment.png")?>"></a></td>
	<td style="padding-left:3px;padding-right:3px;"><?=(gForumEditA($gUser->id,$a->id)?'<a href="'.Query("?sid=?&article=".$a->id."&do=ea").'"><img border=0  title="Editieren" alt="E" src="'.g("gildeforum/edit.png").'"></a>':"")?></td>
	<td style="padding-left:3px;padding-right:3px;"><?=(gForumDelA($gUser->id,$a->id)?'<a href="'.Query("?sid=?&article=".$a->id."&do=da").'"><img border=0  title="L&ouml;schen" alt="L" src="'.g("gildeforum/delete.png").'"></a>':"")?></td>
	</tr>
	</table>
	</td></tr>
	<?if(numComments($f_article)>0){?>
	<tr><td colspan=3><hr></td></tr>
	<tr><td colspan=3><?=($gUser->flatview==1?showcomments($a->id):render_tree(getCommentsOnArticle($a->id,0)))?></td></tr>
	<tr><td colspan=3>&nbsp;</td></tr>
	<?if(!isset($f_article))$f_article=$a->id;
	if(!isset($f_id))$f_id=0;
	?>
	<tr><td colspan=3 align="right"><a href="<?=Query("?sid=?&flatview=".($gUser->flatview==1?"0":"1")."&article=".$f_article."&id=".$f_id)?>"><?=($gUser->flatview==1?"Baumansicht":"Flatview")?></a></td></tr>
	<?}?>
	<?if(isset($c))if($c){?>
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
	</td></tr>
	
	<?}
	if(isset($f_do)){
	?><tr><td colspan=3 style="padding-top:15px;border-top:1px solid grey"><?
		switch ($f_do){
			case "ca":?>
			<form class="guild" method="post" action="<?=Query("guild_forum.php?sid=?")?>">
			<table cellpadding=0 cellspacing=0 border=0>
			<input type="hidden" name="article" value="<?=$f_article?>">
			<input type="hidden" name="do" value="savec">
			<tr><td>Titel:</td><td style="padding-left:3px;"><input class="guild" name="head" type="text" size="40" value="Titel nicht vergessen"></td></tr>
			<tr><td colspan=2 align=center>
			<textarea name="comment" class="guild" cols="70" rows="15">Hier der Kommentar</textarea></td></tr>
			<tr><td></td><td align=right><input type="submit" name="send" value="Kommentieren"></td></tr>
			</table>
			</form>
			<?
			break;
			case "ea":
				if(gForumEditA($gUser->id,$f_article)){
				?>
			<form class="guild" method="post" action="<?=Query("guild_forum.php?sid=?")?>">
			<table cellpadding=0 cellspacing=0 border=0>
			<input type="hidden" name="article" value="<?=$f_article?>">
			<input type="hidden" name="do" value="saveea">
			<tr><td>Titel:</td><td style="padding-left:3px;"><input class="guild" name="head" type="text" size="40" value="<?=$a->head?>"></td></tr>
			<tr><td colspan=2 align=center>
			<textarea name="content" class="guild" cols="70" rows="15"><?=$a->content?></textarea></td></tr>
			<tr><td></td><td align=right><input type="submit" name="send" value="Save"></td></tr>
			</table>
			</form><?
				}
			break;
			case "cc":?>
			<form class="guild" method="post" action="<?=Query("guild_forum.php?sid=?")?>">
			<table cellpadding=0 cellspacing=0 border=0>
			<input type="hidden" name="article" value="<?=$f_article?>">
			<input type="hidden" name="ref" value="<?=$f_id?>">
			<input type="hidden" name="do" value="savec">
			<tr><td>Titel:</td><td style="padding-left:3px;"><input class="guild" name="head" type="text" size="40" value="<?=($f_id>0?"Re: ".$c->head:"Titel nicht vergessen")?>"></td></tr>
			<tr><td colspan=2 align=center>
			<textarea name="comment" class="guild" cols="70" rows="15">Hier der Kommentar</textarea></td></tr>
			<tr><td></td><td align=right><input type="submit" name="send" value="Kommentieren"></td></tr>
			</table>
			</form>
			<?
			break;
			case "ec":
				if(gForumEditC($gUser->id,$f_id)){?>
			<form class="guild" method="post" action="<?=Query("guild_forum.php?sid=?")?>">
			<table cellpadding=0 cellspacing=0 border=0>
			<input type="hidden" name="article" value="<?=$f_article?>">
			<input type="hidden" name="id" value="<?=$f_id?>">
			<input type="hidden" name="do" value="saveec">
			<tr><td>Titel:</td><td style="padding-left:3px;"><input class="guild" name="head" type="text" size="40" value="<?=$c->head?>"></td></tr>
			<tr><td colspan=2 align=center>
			<textarea name="comment" class="guild" cols="70" rows="15"><?=$c->comment?></textarea></td></tr>
			<tr><td></td><td align=right><input type="submit" name="send" value="Save"></td></tr>
			</table>
			</form>
			<?	}
			break;
			default:
			break;
		}
		?></td></tr><?
	}
	?>
	
	</table>
	<? ImgBorderEnd("s2","jpg","#ffffee",32,33);
	?>
	<p><a href="<?=Query("?sid=?")?>"><img src="<?=g("gildeforum/back.png")?>" border=0 alt="Zur&uuml;ck" title="zur&uuml;ck zum Gildeforum"></a></p>
	<?
}else if(isset($f_do) && $f_do=="na"){
ImgBorderStart("s2","jpg","#ffffee","bg-s2",32,33);?>
			<form class="guild" method="post" action="<?=Query("?sid=?")?>">
			<table cellpadding=0 cellspacing=0 border=0>
			<input type="hidden" name="do" value="savea">
			<tr><td>Titel:</td><td style="padding-left:3px;"><input class="guild" name="head" type="text" size="40" value="Thema"></td></tr>
			<tr><td colspan=2 align=center>
			<textarea name="content" class="guild" cols="70" rows="15">Hier den Artikel</textarea></td></tr>
			<tr><td></td><td align=right><input type="submit" name="send" value="Artikel einstellen"></td></tr>
			</table>
			</form>
<?
ImgBorderEnd("s2","jpg","#ffffee",32,33);
?><p><a href="<?=Query("?sid=?")?>"><img src="<?=g("gildeforum/back.png")?>" border=0 alt="Zur&uuml;ck" title="zur&uuml;ck zum Gildeforum"></a></p><?
}else{
?>
<?
//ImgBorderStart(); ?>
<table border=0 cellspacing=0 width=100%>
<tr><td align=center>Gildeforum<br><span style="font-style:italic;font-size:16px;font-family:serif"><?=$gGuild->name?></span></td></tr>
<tr><td>
<table cellspacing=0 cellpadding=5>
<tr><th style="border-bottom:1px dotted grey;">Thema</th><th style="border-bottom:1px dotted grey;">Author</th><th style="border-bottom:1px dotted grey;">Datum</th><th style="border-bottom:1px dotted grey;">Kommentare</th></tr>
<?foreach($article as $o){
$n=numComments($o->id);
$s=$o->nc;
if($s>0)
	$n-=$s;
?>
<tr><td><a style="<?=($o->new?'color:#9d0000;':"")?>" href="<?=Query("?sid=?&article=".$o->id)?>"><?=htmlspecialchars($o->head)?></a></td><td><a href="<?=Query("msg.php?sid=?&show=compose&to=".urlencode(nick($o->user)))?>"><?=nick($o->user)?></a></td><td align=center><?=date("j.m. G:i",$o->date)?></td><td align=center><?=(($n+$s)>0?$n.($s>0?" <span style='color:#9d0000'>+".$s."</span>":""):"--")?></td></tr>
<?}?>
</table>
</td></tr>
<tr><td align=right style="padding-right:25px;"><a href="<?=Query("?sid=?&do=na")?>"><img src="<?=g("gildeforum/neu.png")?>" border=0 title="neuer artikel" alt="Neu"></a></td></tr>
<tr><td >&nbsp;</td></tr>
<tr><td align=center><?=(numArticles()>10?jumplane($f_start,numArticles()):"")?></td></tr>
<tr><td >&nbsp;</td></tr>
</table>
<?
//ImgBorderEnd();
}

}

profile_page_end(); 
?>
</div>
</div></div>
</body>
</html>
