<?php
require_once("../lib.main.php");
Lock();
$t = time();

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
$gGuilds=sqlgettable("SELECT * FROM `guild` ORDER BY `id`","id");
$gTitle = sqlgettable("SELECT * FROM `title` ORDER BY `title`","user");
ImgBorderStart();
?>

	<table border="0">
	<tr><th colspan="3">Player</th></tr>
	<tr><th></th><th>Name</th><th>HQ</th><th>Guild</th><th>Population</th></tr>
	<?php
		$i=1;
		$time = time();
		$t = sqlgettable("SELECT * FROM `user` ORDER BY `pop` DESC");
		foreach($t as $u)
		{
		  if($u->admin!=1){
			$hq=sqlgetobject("SELECT `id`,`x`,`y` FROM `building` WHERE `user`=".$u->id." AND `type`=1");
			if($hq->id){
				$time=time();
				$online = sqlgetone("SELECT `id` FROM `session` WHERE `userid`=".$u->id." AND $time-`lastuse`<300");
				if($online)$online = "#338833"; 
				else $online = "#3333aa";
				if(($time-$u->lastlogin)>(60*60*24*7))$online = "#dd1111";
				if(($time-$u->lastlogin)>(60*60*24*21))$online = "#999999";
				$dead="";
				if(($time-$u->lastlogin)>(60*60*24*48)){
					$online = "#cacaca";
					$dead="&#8224;";
				}
				if(isset($gTitle[$u->id]))$orden="<img style='vertical-align:middle' src='../gfx/".$gTitle[$u->id]->image."' alt='".$gTitle[$u->id]->title."' title='".$gTitle[$u->id]->title."'>";
				else $orden = "";
			?>
			<tr>
				<td align=right><?=$i++?></td>
				<td valign="middle"><?=GetUserLink($u,false,true,$online)?>&nbsp;<?=$orden?>
				<?php if(isset($title))foreach($title as $x)echo "<img align=\"absmiddle\" src=\"".$x->image."\" alt=\"".$x->title."\">"; ?>
				</td>
				<td align=center><?=($hq?opos2txt($hq):"")?></td>
				<td align=center><?php if($u->guild > 0){ ?>
			<a href='<?=query("../info/viewguild.php?id=".$u->guild."&sid=?")?>'><?=$gGuilds[$u->guild]->name?></a>
		<?php } ?></td>
				<td align=right><?=kplaintrenner(ceil($u->pop))?></td>
			</tr>
			<?php
			}
		}
		}
	?>
	</table>

<?php 
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
