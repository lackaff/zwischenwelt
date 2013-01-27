<?php
include("lib.php");
include("lib.main.php");

Lock();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta http-equiv="refresh" content="300; URL=<?=sessionLink("log.php")?>">
<link href="http://fonts.googleapis.com/css?family=Bree+Serif" rel="stylesheet" type="text/css">
<link href="http://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="<?=BASEURL?>css/zwstyle_new_temp.css">
<title>Zwischenwelt - log</title>
</head>
<body leftMargin="0" topMargin="0" marginwidth="0" marginheight="0">
<table cellpadding=1 border=0 cellspacing=1 style="width:100%">
<tr>
<td rowspan="8" valign="top" align="left">
<?php
if(isset($f_topic))$t = " `topic`=".intval($f_topic);
else $t = "1";

$log = sqlgettable("SELECT * FROM `newlog` WHERE `user`=".$gUser->id." AND $t ORDER BY `time` DESC,`id` DESC LIMIT 15");
//$log = sqlgettable("SELECT * FROM `newlog` WHERE 1 AND $t ORDER BY `time` DESC,`id` DESC LIMIT 50");

foreach($log as $x)
{
	$i1 = $x->i1;
	$i2 = $x->i2;
	$i3 = $x->i3;
	$s1 = $x->s1;
	$s2 = $x->s2;
	$color="";
	
	switch($x->type)
	{
		case NEWLOG_PILLAGE_ATTACKER_START:
			$text = "Your army $s1 started pillaging ".$s2."'s storehouse at <a target='info' href=".query("info/info.php?sid=?&x=$i1&y=$i2").">($i1|$i2)</a>.";break;
		case NEWLOG_PILLAGE_ATTACKER_STOP:
			$text = "Your army $s1 stopped pillaging ".$s2."'s strorehouse at <a target='info' href=".query("info/info.php?sid=?&x=$i1&y=$i2").">($i1|$i2)</a>.";break;
		case NEWLOG_PILLAGE_DEFENDER_START:
			$text = $s2."'s army $s1 started pillaging your storehouse at <a target='info' href=".query("info/info.php?sid=?&x=$i1&y=$i2").">($i1|$i2)</a>.";break;
		case NEWLOG_PILLAGE_DEFENDER_STOP:
			$text = $s2."'s army $s1 stopped pillaging your storehouse at <a target='info' href=".query("info/info.php?sid=?&x=$i1&y=$i2").">($i1|$i2)</a>.";break;
		case NEWLOG_PILLAGE_ATTACKER_CANCEL:
			$text = "Your army $s1 has called of the pillaging of ".$s2."'s storehouse at <a target='info' href=".query("info/info.php?sid=?&x=$i1&y=$i2").">($i1|$i2)</a>.";break;
		case NEWLOG_PILLAGE_DEFENDER_CANCEL:
			$text = $s2."'s army $s1 has called of the pillaging of your storehouse at <a target='info' href=".query("info/info.php?sid=?&x=$i1&y=$i2").">($i1|$i2)</a>.";break;
		case NEWLOG_FIGHT_START:
			$text = "A battle between the armies $s1 and $s2 has started at <a target='info' href=".query("info/info.php?sid=?&x=$i1&y=$i2").">($i1|$i2)</a>.";break;
		case NEWLOG_FIGHT_STOP:
			$text = "A battle at <a target='info' href=".query("info/info.php?sid=?&x=$i1&y=$i2").">($i1|$i2)</a> ended : $s1";break;
		case NEWLOG_TRADE:
			$text = "A trade with $s1 has been completed, $i1 $s2 received";break;
		case NEWLOG_UPGRADE_FINISHED:
			$text = "$s1 at position <a target='info' href=".query("info/info.php?sid=?&x=$i1&y=$i2").">($i1|$i2)</a> is now level $i3";break;
		case NEWLOG_BUILD_FINISHED:
			$text = "$s1 at position <a target='info' href=".query("info/info.php?sid=?&x=$i1&y=$i2").">($i1|$i2)</a> is completed.";break;
		case NEWLOG_RAMPAGE_ATTACKER_START:
			$text = "Your ram $s1 started to batter ".$s2."'s building at <a target='info' href=".query("info/info.php?sid=?&x=$i1&y=$i2").">($i1|$i2)</a>.";break;
		case NEWLOG_RAMPAGE_ATTACKER_CANCEL:
			$text = "Your ram $s1 stopped battering ".$s2."'s building at <a target='info' href=".query("info/info.php?sid=?&x=$i1&y=$i2").">($i1|$i2)</a>.";break;
		case NEWLOG_RAMPAGE_DEFENDER_START:
			$text = $s2."'s ram $s1 started to batter your building at <a target='info' href=".query("info/info.php?sid=?&x=$i1&y=$i2").">($i1|$i2)</a>.";break;
		case NEWLOG_RAMPAGE_DEFENDER_CANCEL:
			$text = $s2."'s ram $s1 stopped battering your building at <a target='info' href=".query("info/info.php?sid=?&x=$i1&y=$i2").">($i1|$i2)</a>.";break;
		case NEWLOG_RAMPAGE_ATTACKER_DESTROY:
			$text = "Your ram $s1 demolished ".$s2."'s building at <a target='info' href=".query("info/info.php?sid=?&x=$i1&y=$i2").">($i1|$i2)</a>.";break;
		case NEWLOG_RAMPAGE_DEFENDER_DESTROY:
			$text = $s2."'s ram $s1 demolished your building at <a target='info' href=".query("info/info.php?sid=?&x=$i1&y=$i2").">($i1|$i2)</a>.";break;
		case NEWLOG_ARMY_RES_PUTDOWN:
			$text = "The army $s1 unloaded '$s2' into your storehouse at <a target='info' href=".query("info/info.php?sid=?&x=$i1&y=$i2").">($i1|$i2)</a> geladen";break;
		case NEWLOG_ARMY_RES_GETOUT:
			$text = "The army $s1 loaded '$s2' from your storehouse at <a target='info' href=".query("info/info.php?sid=?&x=$i1&y=$i2").">($i1|$i2)</a> genommen";break;
		case NEWLOG_GUILD_TRANSFER_ERROR:
			$text = "The resource transfer with $s1 was not successful";break;
		case NEWLOG_MAGIC_CAST_FAIL:
			$color= "#8d0000";
			switch($i3){
				case MTARGET_PLAYER:
					$target=nick($s2);
				break;
			
				case MTARGET_AREA:
					$target="the surrounding area";
				break;
			
				case MTARGET_ARMY:
					$target="the army";
				break;
			
				default:
					$target="?";
				break;
			}

			$text = "Your wizards attempted to cast $s1 on $target at <a target='info' href=".query("info/info.php?sid=?&x=$i1&y=$i2").">($i1|$i2)</a>, but failed.";break;
		case NEWLOG_MAGIC_CAST_SUCCESS:
			$color= "green";
			switch($i3){
				case MTARGET_PLAYER:
					$target=nick($s2);
				break;
			
				case MTARGET_AREA:
					$target="the surrounding area";
				break;
			
				case MTARGET_ARMY:
					$target="the army";
				break;
			
				default:
					$target="?";
				break;
			}

			$text = "Your wizards successfully cast $s1 on $target at <a target='info' href=".query("info/info.php?sid=?&x=$i1&y=$i2").">($i1|$i2)</a> zu sprechen";break;
		case NEWLOG_MAGIC_HELP_TARGET:
			$color= "darkblue";
			switch($i3){
				case MTARGET_PLAYER:
					$target="you";
				break;
			
				case MTARGET_AREA:
					$target="the surrounding area";
				break;
			
				case MTARGET_ARMY:
					$target="your army";
				break;
			
				default:
					$target="?";
				break;
			}
			$text = "The spell $s1 was cast by ".nick($s2)." at <a target='info' href=".query("info/info.php?sid=?&x=$i1&y=$i2").">($i1|$i2)</a> on $target.";break;
		case NEWLOG_MAGIC_DAMAGE_TARGET:
			switch(intval($i3)){
				case MTARGET_PLAYER:
					$target="you";
				break;
			
				case MTARGET_AREA:
					$target="your house";
				break;
			
				case MTARGET_ARMY:
					$target="your army";
				break;
			
				default:
					$target="?";
				break;
			}
			
			$color= "darkred";
			$text = "The curse $s1 was cast by ".nick($s2)." at <a target='info' href=".query("info/info.php?sid=?&x=$i1&y=$i2").">($i1|$i2)</a> on $target.";break;
		default:
			//$text = "";break;
			$text = "unknown log type ".$x->type;break;
	}
	
	if($color==""){
		switch($x->topic)
		{
			case NEWLOG_TOPIC_FIGHT:$color = "red";break;
			case NEWLOG_TOPIC_BUILD:$color = "blue";break;
			case NEWLOG_TOPIC_SYSTEM:$color = "green";break;
			case NEWLOG_TOPIC_TRADE:$color = "#ff00ff";break;
			case NEWLOG_TOPIC_MAGIC:$color = "#8d0000";break;
			default:$color = "black";break;
		}
	}
	
	if(isset($x->url) && $x->url)
	{
		if(strpos($x->url,"://"))
		{
			$url = $x->url;
			$frame = '_blank';
		} else	{
			$url = SessionLink($x->url);
			$frame = 'info';
		}
		if($x->frame) $frame = $x->frame;
				
		$text = "<a style=\"color:$color\" href=\"$url\" target=\"$frame\">$text</a>";
	}
	echo "<span style=\"color:#aaaaaa\">".date("d.m H:i",$x->time).":</span><span style=\"color:$color\">".$text." (".($x->count)."x)</span><br>";
}
?>
</td>
<td style="width:10px;height:10px">
	<a href="<?=sessionLink("log.php")?>"><img alt="all" border=0 src="<?=g("icon/log-all.png")?>"></a>
</td></tr>
<tr><td style="width:10px;height:10px">
	<a href="<?=sessionLink("log.php?topic=".NEWLOG_TOPIC_FIGHT)?>"><img alt="fight" border=0 src="<?=g("icon/log-fight.png")?>"></a>
</td></tr>
<tr><td style="width:10px;height:10px">
	<a href="<?=sessionLink("log.php?topic=".NEWLOG_TOPIC_MAGIC)?>"><img alt="magic" border=0 src="<?=g("icon/log-magic.png")?>"></a><br>
</td></tr>
<tr><td style="width:10px;height:10px">
	<a href="<?=sessionLink("log.php?topic=".NEWLOG_TOPIC_BUILD)?>"><img alt="build" border=0 src="<?=g("icon/log-build.png")?>"></a><br>
</td></tr>
<tr><td style="width:10px;height:10px">
	<a href="<?=sessionLink("log.php?topic=".NEWLOG_TOPIC_SYSTEM)?>"><img alt="system" border=0 src="<?=g("icon/log-system.png")?>"></a><br>
</td></tr>
<tr><td style="width:10px;height:10px">
	<a href="<?=sessionLink("log.php?topic=".NEWLOG_TOPIC_TRADE)?>"><img alt="trade" border=0 src="<?=g("icon/log-trade.png")?>"></a><br>
</td></tr>
<tr><td style="width:10px;height:10px">
	<a href="<?=sessionLink("log.php?topic=".NEWLOG_TOPIC_GUILD)?>"><img alt="trade" border=0 src="<?=g("icon/log-guild.png")?>"></a><br>
</td></tr>
<tr><td>&nbsp;</td></tr>
</table>
</body>
</html>
