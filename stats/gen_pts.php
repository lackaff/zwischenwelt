<?php
require_once("../lib.main.php");
require_once("../lib.building.php");
Lock();
$t = time();
// &#8224;
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 transitional//EN"
   "http://www.w3.org/TR/html4/transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<link href="http://fonts.googleapis.com/css?family=Bree+Serif" rel="stylesheet" type="text/css">
<link href="http://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="<?=BASEURL?>css/zwstyle_new_temp.css">
<title>Zwischenwelt - Statistiken</title>

</head>
<body>

<?php
include("../menu.php");
include("../stats/header.php");
ImgBorderStart();

$gGuilds=sqlgettable("SELECT * FROM `guild` ORDER BY `id`","id");
$gTitle = sqlgettable("SELECT * FROM `title` ORDER BY `title`","user");
if($gUser->admin > 0)$gRecords = sqlgettable("SELECT `userid` FROM `userrecord` WHERE `text` NOT LIKE ''","userid");
else $gRecord = array();

if (!isset($f_what)) $f_what = false;
switch($f_what){
	
	case 'pom':
		$userlist=sqlgettable("SELECT `lastlogin`,`id`,`guild`,`name`,`army_pts` AS pts FROM `user` WHERE `admin`=0 ORDER BY pts DESC","id");
	break;
	
	case 'pnm':
		$userlist=sqlgettable("SELECT `lastlogin`,`id`,`guild`,`name`,`general_pts` AS pts FROM `user` WHERE `admin`=0 ORDER BY pts DESC","id");
	break;
	
	case 'p':
		$userlist=sqlgettable("SELECT `lastlogin`,`id`,`guild`,`name`,`general_pts`+`army_pts` AS pts FROM `user` WHERE `admin`=0 ORDER BY pts DESC","id");
	break;
	
	default:
		$userlist=sqlgettable("SELECT `lastlogin`,`id`,`guild`,`name`,`general_pts`+`army_pts` AS pts FROM `user` WHERE `admin`=0 ORDER BY pts DESC","id");
	break;
}

$i=1;
?>
<table>
<tr><th>Rank</th><th>Name</th><th>Guilds</th><th>HQ</th><th>Points</th></tr>
<?
foreach ($userlist as $user){
  $hq=sqlgetobject("SELECT `id`,`x`,`y` FROM `building` WHERE `user`=".$user->id." AND `type`=1");
  if($hq->id){
	$time=time();
	$online = sqlgetone("SELECT `id` FROM `session` WHERE `userid`=".$user->id." AND $time-`lastuse`<300");
   if($online)$online = "#338833"; 
	else $online = "#3333aa";
	if(($time-$user->lastlogin)>(60*60*24*7))$online = "#dd1111";
	if(($time-$user->lastlogin)>(60*60*24*21))$online = "#999999";
	$dead="";
	if(($time-$user->lastlogin)>(60*60*24*48)){
		$online = "#cacaca";
		$dead="&#8224;";
	}
	if(isset($gTitle[$user->id]))$orden="<img style='vertical-align:middle' src='../gfx/".$gTitle[$user->id]->image."' alt='".$gTitle[$user->id]->title."' title='".$gTitle[$user->id]->title."'>";
	else $orden = "";
	
	$name="<a style='color:".$online.";' href='".query("../info/msg.php?sid=?&show=compose&to=".urlencode($user->name))."' >".$user->name." ".$dead."</a>&nbsp;$orden ";
	$hqxy="<a href='".query("../info/info.php?sid=?&x=".$hq->x."&y=".$hq->y)."'>".$hq->x."/".$hq->y."</a>";
	if($gUser->admin > 0 && isset($gRecords[$user->id]))$info = "<a href=\"".query("../info/adminuser.php?sid=?&id=$user->id")."\"><img src=\"".g("icon/info.png")."\" border=0 title=\"Akte vorhanden\" alt=\"Akte vorhanden\"></a>";
	else $info = "";
	?>
	
		<tr>
		<td align=right><?=$i?></td>
		<td align=left><?=$info?><?=GetUserLink($user,false,true,$online)?></td>
		<td align=center>
		<?php if($user->guild > 0){ ?>
			<a href='<?=query("../info/viewguild.php?id=".$user->guild."&sid=?")?>'><?=$gGuilds[$user->guild]->name?></a>
		<?php } ?>
		</td><td align=center><?=opos2txt($hq)?></td><td align=right><?=ktrenner($user->pts,"#000000","#ff0000",11)?></td></tr>
		<?$i++;
	}
}

echo "</table>";

ImgBorderEnd();
include("../stats/footer.php");
?>
<h3>Legend</h3>
<table style="text-align: left;" border="0" cellpadding="2" cellspacing="2">
	<tr><td style="color: #338833;">green</td>
	<td>Player is currently online</td></tr>
	<tr><td style="color: #3333AA;">blue</td>
	<td>Player is active, but not online</td></tr>
	<tr><td style="color: #ff0000;">red</td>
	<td>Player has not been active in last week</td></tr>
	<tr><td style="color: #999999;">grey</td>
	<td>Player will soon be deleted</td></tr>
</table>
<br>

<h3>Titles</h3>
<table border=0 cellpadding=2 cellspacing=2>
<?php foreach($gTitle as $t){ ?>
	<tr><td><img src='../gfx/<?=$t->image?>' title='<?=$t->title?>' alt='<?=$t->title?>'></td>
	<th><?=$t->title?></th><td><?=$t->text?></td></tr>
<?php } ?>
</table>
</body>
</html>
