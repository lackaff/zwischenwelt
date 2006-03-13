<?php
include("lib.php");
include("lib.main.php");

Lock();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/transitional.dtd">
<html>
<head>
<meta http-equiv="refresh" content="300; URL=<?=sessionLink("log.php")?>">
<link rel="stylesheet" type="text/css" href="<?=GetZWStylePath()?>">
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
			$text = "Ihre Armee $s1 hat begonnen das Lager bei <a target='info' href=".query("info/info.php?sid=?&x=$i1&y=$i2").">($i1|$i2)</a> von $s2 zu plündern";break;
		case NEWLOG_PILLAGE_ATTACKER_STOP:
			$text = "Ihre Armee $s1 aufgehört das Lager bei <a target='info' href=".query("info/info.php?sid=?&x=$i1&y=$i2").">($i1|$i2)</a> von $s2 zu plündern";break;
		case NEWLOG_PILLAGE_DEFENDER_START:
			$text = "Die Armee $s1 von $s2 hat begonnen ihr Lager bei <a target='info' href=".query("info/info.php?sid=?&x=$i1&y=$i2").">($i1|$i2)</a> zu plündern";break;
		case NEWLOG_PILLAGE_DEFENDER_STOP:
			$text = "Die Armee $s1 von $s2 hat aufgehört ihr Lager bei <a target='info' href=".query("info/info.php?sid=?&x=$i1&y=$i2").">($i1|$i2)</a> zu plündern";break;
		case NEWLOG_PILLAGE_ATTACKER_CANCEL:
			$text = "Ihre Armee $s1 das Plündern von dem Lager bei <a target='info' href=".query("info/info.php?sid=?&x=$i1&y=$i2").">($i1|$i2)</a> von $s2 abgebrochen";break;
		case NEWLOG_PILLAGE_DEFENDER_CANCEL:
			$text = "Die Armee $s1 von $s2 hat das Plündern von ihrem Lager bei <a target='info' href=".query("info/info.php?sid=?&x=$i1&y=$i2").">($i1|$i2)</a> abgebrochen";break;
		case NEWLOG_FIGHT_START:
			$text = "Kampf zwischen den Armeen $s1 und $s2 bei <a target='info' href=".query("info/info.php?sid=?&x=$i1&y=$i2").">($i1|$i2)</a> start";break;
		case NEWLOG_FIGHT_STOP:
			$text = "Kampf bei <a target='info' href=".query("info/info.php?sid=?&x=$i1&y=$i2").">($i1|$i2)</a> beendet : $s1";break;
		case NEWLOG_TRADE:
			$text = "Ein Handel wurde mit $s1 abgeschlossen, $i1 $s2 erhalten";break;
		case NEWLOG_UPGRADE_FINISHED:
			$text = "$s1 an Position <a target='info' href=".query("info/info.php?sid=?&x=$i1&y=$i2").">($i1|$i2)</a> ist nun Level $i3";break;
		case NEWLOG_BUILD_FINISHED:
			$text = "$s1 an Position <a target='info' href=".query("info/info.php?sid=?&x=$i1&y=$i2").">($i1|$i2)</a> fertiggestellt";break;
		case NEWLOG_RAMPAGE_ATTACKER_START:
			$text = "Ihre Ramme $s1 hat begonnen das Gebäude bei <a target='info' href=".query("info/info.php?sid=?&x=$i1&y=$i2").">($i1|$i2)</a> von $s2 zu zerstören";break;
		case NEWLOG_RAMPAGE_ATTACKER_CANCEL:
			$text = "Ihre Ramme $s1 hat aufgehört das Gebäude bei <a target='info' href=".query("info/info.php?sid=?&x=$i1&y=$i2").">($i1|$i2)</a> von $s2 zu zerstören";break;
		case NEWLOG_RAMPAGE_DEFENDER_START:
			$text = "Die Ramme $s1 von $s2 hat begonnen ihr Gebäude bei <a target='info' href=".query("info/info.php?sid=?&x=$i1&y=$i2").">($i1|$i2)</a> zu zerstören";break;
		case NEWLOG_RAMPAGE_DEFENDER_CANCEL:
			$text = "Die Ramme $s1 von $s2 hat aufgehört ihr Gebäude bei <a target='info' href=".query("info/info.php?sid=?&x=$i1&y=$i2").">($i1|$i2)</a> zu zerstören";break;
		case NEWLOG_RAMPAGE_ATTACKER_DESTROY:
			$text = "Ihre Ramme $s1 hat das Gebäude bei <a target='info' href=".query("info/info.php?sid=?&x=$i1&y=$i2").">($i1|$i2)</a> von $s2 zerstört";break;
		case NEWLOG_RAMPAGE_DEFENDER_DESTROY:
			$text = "Die Ramme $s1 von $s2 hat ihr Gebäude bei <a target='info' href=".query("info/info.php?sid=?&x=$i1&y=$i2").">($i1|$i2)</a> zerstört";break;
		case NEWLOG_ARMY_RES_PUTDOWN:
			$text = "Die Armee $s1 hat '$s2' in ihr Lager bei <a target='info' href=".query("info/info.php?sid=?&x=$i1&y=$i2").">($i1|$i2)</a> geladen";break;
		case NEWLOG_ARMY_RES_GETOUT:
			$text = "Die Armee $s1 hat '$s2' aus ihrem Lager bei <a target='info' href=".query("info/info.php?sid=?&x=$i1&y=$i2").">($i1|$i2)</a> genommen";break;
		case NEWLOG_GUILD_TRANSFER_ERROR:
			$text = "Rohstofftransfer mit Gilde $s1 hat nicht geklappt";break;
		case NEWLOG_MAGIC_CAST_FAIL:
			$color= "#8d0000";
			switch($i3){
				case MTARGET_PLAYER:
					$target=nick($s2);
				break;
			
				case MTARGET_AREA:
					$target="das umliegende Gebiet";
				break;
			
				case MTARGET_ARMY:
					$target="die Armee";
				break;
			
				default:
					$target="?";
				break;
			}

			$text = "Eure Magier haben bei dem Versuch $s1 auf $target bei <a target='info' href=".query("info/info.php?sid=?&x=$i1&y=$i2").">($i1|$i2)</a> zu sprechen versagt";break;
		case NEWLOG_MAGIC_CAST_SUCCESS:
			$color= "green";
			switch($i3){
				case MTARGET_PLAYER:
					$target=nick($s2);
				break;
			
				case MTARGET_AREA:
					$target="das umliegende Gebiet";
				break;
			
				case MTARGET_ARMY:
					$target="die Armee";
				break;
			
				default:
					$target="?";
				break;
			}

			$text = "Eure Magier hatten Erfolg bei dem Versuch $s1 auf $target bei <a target='info' href=".query("info/info.php?sid=?&x=$i1&y=$i2").">($i1|$i2)</a> zu sprechen";break;
		case NEWLOG_MAGIC_HELP_TARGET:
			$color= "darkblue";
			switch($i3){
				case MTARGET_PLAYER:
					$target="euch";
				break;
			
				case MTARGET_AREA:
					$target="das umliegende Gebiet";
				break;
			
				case MTARGET_ARMY:
					$target="eure Armee";
				break;
			
				default:
					$target="?";
				break;
			}
			$text = "Der Spruch $s1 wurde von ".nick($s2)." bei <a target='info' href=".query("info/info.php?sid=?&x=$i1&y=$i2").">($i1|$i2)</a> auf $target gesprochen, er wird euch helfen";break;
		case NEWLOG_MAGIC_DAMAGE_TARGET:
			switch(intval($i3)){
				case MTARGET_PLAYER:
					$target="euch";
				break;
			
				case MTARGET_AREA:
					$target="euer Haus";
				break;
			
				case MTARGET_ARMY:
					$target="eure Armee";
				break;
			
				default:
					$target="?";
				break;
			}
			
			$color= "darkred";
			$text = "Der Spruch $s1 wurde von ".nick($s2)." bei <a target='info' href=".query("info/info.php?sid=?&x=$i1&y=$i2").">($i1|$i2)</a> auf $target gesprochen, es wurde Schaden angerichtet";break;
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
