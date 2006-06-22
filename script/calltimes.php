<?php

require_once("../lib.main.php");

$gAllUsers = sqlgettable("SELECT * FROM `user`","id");
$gAllGuilds = sqlgettable("SELECT * FROM `guild`","id");
$gAdminUserIDs = sqlgetonetable("SELECT `id` FROM `user` WHERE `admin` = 1");
$gAdminIPs = (count($gAdminUserIDs) > 0) ? sqlgetonetable("SELECT `ip` FROM `calllog` WHERE `user` IN (".implode(",",$gAdminUserIDs).") GROUP BY `ip`") : array();

define("kCompareSessionTimeout",20*60); // a call starts a new session if the last call is this much away
define("kCompareShortMax",5*60); // max time considered to be short for breaks between sessions

// return = array(session1,session2,...) where session = object(start:timestamp,end:timestamp,startid=calllog,endid:calllog,ip)
// use GetSessions for caching
function CalcSessions ($uid) {
	//echo "CalcSession(".$uid.")<br>"; flush();
	global $gAdminIPs;
	$res = array();
	$last = false;
	$session = false;
	$r = sql("SELECT * FROM `calllog` WHERE `user` = ".intval($uid)." ORDER BY `time`");
	while ($o = mysql_fetch_object($r)) {
		if (in_array($o->ip,$gAdminIPs)) continue; // ignore sessions possibly caused by symbiosis
		if (!$last) {
			// first entry in loop, session started
			$session->startid = $o->id;
			$session->start = $o->time;
			$session->ip = $o->ip; 
		} else {
			$sessionchange = false;
			if ($last->ip != $o->ip) $sessionchange = true; // ipchange -> new session
			if ($last->time < $o->time - kCompareSessionTimeout) $sessionchange = true; // has not been used for a while -> new session
			
			if ($sessionchange) {
				// $last ist last of old session
				$session->endid = $last->id;
				$session->end = $last->time;
				$res[] = copyobj($session);
				
				// $o is first of new session
				$session = false;
				$session->startid = $o->id;
				$session->start = $o->time;
				$session->ip = $o->ip; 
			}
		}
		$last = $o;
	}
	mysql_free_result($r);
	if ($last) $res[] = $last->time; // end last session
	return $res;
}

// caching
$gAllSessions = array();
function GetSessions ($uid) {
	$uid = intval($uid);
	global $gAllSessions;
	if (!isset($gAllSessions[$uid])) $gAllSessions[$uid] = CalcSessions($uid);
	return $gAllSessions[$uid];
}

$gCol1 = "#00ff88";
$gCol2 = "#0088ff";

$gCmps = array();
function printcall ($o,$uid1,$uid2,$comment="") {
	global $gAllUsers,$gCol1,$gCol2;
	if (!is_object($o)) $o = sqlgetobject("SELECT * FROM `calllog` WHERE `id` = ".intval($o));
	$col = ($o->user == $uid1) ? $gCol1 : $gCol2;
	?>
	<tr>
	<td><?=date("Y-m-d H:i:s",$o->time)?></td>
	<td><font color="<?=$col?>"><?=$gAllUsers[$o->user]->name?>[<?=$o->user?>]</font></td>
	<td><?=$o->ip?></td>
	<td><?=$o->script?></td>
	<td><?=$comment?></td>
	</tr>
	<?php
}

function callcmp ($uid1,$uid2,$showdetails=false) {
	global $gAllUsers;
	global $gCmps;
	
	//echo "callcmp($uid1,$uid2)<br>";
	
	if ($uid1 == -1 && $uid2 == -1) {
		$list1 = sqlgettable("SELECT * FROM `user`");
		$list2 = $gAllUsers;
		$cur = 1;
		$max = count($list1);
		foreach ($list1 as $a) {
			//echo "cmp (".$cur."/".$max.") $a->id with *<br>"; flush();
			foreach ($list2 as $b) if ($b->id > $a->id) callcmp($a->id,$b->id,$showdetails);
			++$cur;
		}
		return;
	}
	
	if ($uid1 == -1) return; // use uid2 !
	
	if ($uid2 == -1) {
		$list = $gAllUsers;
		foreach ($list as $o) callcmp($uid1,$o->id,$showdetails);
		return;
	}
	
	if ($uid1 == $uid2) return; // useless comparison
	
	$sess1 = GetSessions($uid1);
	$sess2 = GetSessions($uid2);
	if (count($sess1) == 0 || count($sess2) == 0) return;
	
	$c_overlap = 0;
	$c_shortwait = 0;
	$c_sameip = 0;
	
	foreach ($sess1 as $a) {
		// make sure to compare only "near" sessions
		$timebefore = 0;
		$timeafter = 0;
		foreach ($sess2 as $b) {
			if ($b->end <= $a->start) $timebefore = $b->start;
			if ($b->start >= $a->end) { $timeafter = $b->start; break; }
		}
		
		foreach ($sess2 as $b) {
			if ($b->start < $timebefore) continue;
			if ($b->start > $timeafter) break;
			$overlap = true;
			if ($b->start >= $a->end) $overlap = false;
			if ($a->start >= $b->end) $overlap = false;
			
			if ($overlap) ++$c_overlap;
			if (!$overlap) {
				$timediff = ($a->end <= $b->start) ? 
							($b->start - $a->end) :	// a is before b
							($a->start - $b->end);	// b is before a
				if ($timediff < kCompareShortMax) { 
					++$c_shortwait;
					// print a and b
					if ($showdetails) {
						if ($a->end <= $b->start) 
								{ $one = $a; $two = $b;} // a is before b
						else	{ $one = $b; $two = $a;} // b is before a
						?>
						<table border=1>
						<tr><td colspan="5"><font color="red"><?=Duration2Text($timediff)?>...(<?=$timediff?>s)</font></td></tr>
						<?php printcall($one->startid,$uid1,$uid2,"session-start"); ?>
						<?php printcall($one->endid,$uid1,$uid2,"session-end"); ?>
						<?php printcall($two->startid,$uid1,$uid2,"session-start"); ?>
						<?php printcall($two->endid,$uid1,$uid2,"session-end"); ?>
						</table>
						<br>
						<?php 
					}
				} else {
					//longwait, irrelevant
				}
			}
			
			if ($a->ip == $b->ip) ++$c_sameip;
		}
	}
		
	/*	
	old code
	$uid1_has_calls = sqlgetone("SELECT 1 FROM `calllog` WHERE `user` = $uid1 LIMIT 1");
	$uid2_has_calls = sqlgetone("SELECT 1 FROM `calllog` WHERE `user` = $uid2 LIMIT 1");	
	if (!$uid1_has_calls || !$uid2_has_calls) return; // not comparable, no log data for one of them
	if ($uid1_has_calls && $uid2_has_calls) {
		$calls = sqlgettable("SELECT * FROM `calllog` WHERE `user` IN ($uid1,$uid2) ORDER BY `time`");
		
		// FROM_UNIXTIME(`time`),user,ip,script
		$sessionstart = false; // the first call of a consecutive session
		$last1 = false; // last call from uid1
		$last2 = false; // last call from uid2
		$debug = true;
		
		$shortwait_maxtime = 5*60; // 5 minutes
		
		// todo : filter out overlapping sessions ! currently just counted
		
		if ($showdetails) echo "<table border=1>";
		
		$starttime = 0;
		
		
		$last = false;
		foreach ($calls as $o) {
			if ($last && $last->user != $o->user) break;
			$starttime = $o->time-5*60;
			$last = $o;
		}
		
		
		
		$last = false;
		$last_printed = false;
		foreach ($calls as $o) {
			$printme = false;
			if ($o->time < $starttime) continue;
			if (!$last) {
				// first entry in loop
				//if ($debug) echo "first entry in loop<br>";
				$printme = true;
				$sessionstart = $o;
			} else if ($last->user != $o->user) {
				if ($showdetails) if ($last_printed && $last_printed->id != $last->id) printcall($last,$uid1,$uid2); 
				
				$printme = true;
				// session changed
				$time_since_last = $o->time - $last->time;
				//if ($debug) echo "session changed<br>";
				
				// ip cmp
				if ($last->ip == $o->ip) {
					// same ip, different user !
					if ($showdetails) echo "<tr><td colspan=5>same ip, different user !</td></tr>";
					++$c_sameip;
				}
				
				//if ($debug) echo "time_since_last = $time_since_last<br>";
				
				$isoverlap = false;
				$last_of_this_user = ($o->user == $uid1)?$last1:$last2;
				if ($o->ip == $last_of_this_user->ip) {
					$isoverlap = true;
					++$c_overlap;
					if ($showdetails) echo "<tr><td colspan=5>c_overlap</td></tr>";
				}
				
				if ($time_since_last > $shortwait_maxtime) {
					++$c_longwait;
					if ($showdetails) echo "<tr><td colspan=5><font color='green'>pause:lang</font> ".Duration2Text($time_since_last)."</td></tr>";
				} else {
					++$c_shortwait;
					if ($showdetails) echo "<tr><td colspan=5><font color='red'>PAUSE:KURZ</font> ".Duration2Text($time_since_last)."</td></tr>";
				}
				
				$sessionstart = $o;
			}
			
			if ($last_printed && $last_printed->ip != $o->ip) $printme = true;
			if ($last_printed && $o->time - $last_printed->time > 60*10) $printme = true;
			//$printme = true;

			if ($showdetails && $printme) {
				printcall($o,$uid1,$uid2); 
				$last_printed = $o;
			}
			
			if ($o->user == $uid1) $last1 = $o;
			if ($o->user == $uid2) $last2 = $o;
			$last = $o;
		}
		
		if ($showdetails) echo "</table>";
	}
	*/
	
	$mycmp = false;
	$mycmp->c_overlap = $c_overlap;
	$mycmp->c_shortwait = $c_shortwait;
	$mycmp->c_sameip = $c_sameip;
	$mycmp->uid1 = $uid1;
	$mycmp->uid2 = $uid2;
	
	// bestimmung der multi-wahrscheinlichkeit
	// anzahl der sessions wo die user direkt nacheinander online waren (wird durch überlappung verfäscht)
	$mycmp->score = $mycmp->c_shortwait; 
	
	// wenn beide gleichzeitig unabhaengig voneinander online sind (überlappung=overlap), ist es unwahrscheinlicher, aber hier von hand untersuchen
	if ($mycmp->c_overlap > 0)  $mycmp->score *= 0.5; 
	//if ($mycmp->c_overlap > 0)  $mycmp->score = 1; 
	
	// gleiche ip gehabt -> multi oder nur wg/familie?
	$mycmp->score += $mycmp->c_sameip * 0.2;
	
	if ($mycmp->score < 2) return;
	
	$gCmps[] = $mycmp;
}

if (isset($f_cmp_1_1)) 		callcmp(intval($f_uid1),intval($f_uid2),true);
if (isset($f_cmp_1_all))	callcmp(intval($f_uid1),-1);
if (isset($f_cmp_all_all))	callcmp(-1,-1);

// pretty text for uid :  name[uid](guildname)
function uid2txt ($uid) {
	global $gAllUsers;
	global $gAllGuilds;
	$user = $gAllUsers[$uid];
	if (!$user) return "unknown_user[".$uid."]";
	$guild = $gAllGuilds[$user->guild];
	return $user->name."[".$uid."](".($guild?$guild->name:"").")";
}

function uidcmpsort($a, $b) {
    if ($a->score == $b->score) return 0;
    return ($a->score > $b->score) ? -1 : 1;
}

usort($gCmps, "uidcmpsort");

// todo : sort cmps by multi-propability  ( overlap=-1 short=+5 long=0 )
if (count($gCmps) > 0) {
	?>
	<table border=1>
	<tr>
		<th>score</th>
		<th>short<br>wait</th>
		<th>over<br>lap</th>
		<th>same<br>ip</th>
		<th>uid1</th>
		<th>uid2</th>
	</tr>
	
	<?php foreach ($gCmps as $o) {
		?>
		<tr>
		<td><?=$o->score?></td>
		<td><?=$o->c_shortwait?></td>
		<td><?=$o->c_overlap?></td>
		<td><?=$o->c_sameip?></td>
		<td><?=uid2txt($o->uid1)?></td>
		<td><?=uid2txt($o->uid2)?></td>
		<td><a href="<?=Query("?cmp_1_1=cmp_1_1&uid1=".$o->uid1."&uid2=".$o->uid2)?>">(details)</a></td>
		</tr>
	<?php }?>
	</table>
	<?php
}

?>
Zeitangaben : <br>
1:02 sind stunden:minuten<br>
0:01:02 sind stunden:minuten:sekunden<br>
12s sind sekunden<br>
Bei andauernden Sessions wird hier nur alle 10 minuten ein call angezeigt, <br>
&nbsp;der letzte call vor einem user Wechsel wird aber schon immer angezeigt.<br>
Score ist die multi-wahrscheinlichkeit, hoch = sehr wahrscheinlich multi<br>

<br><br>
<form method="post" action="<?=Query("?sid=?")?>">
	uid1=<input type="text" name="uid1" value="1881">
	uid2=<input type="text" name="uid2" value="2496">
	<input type="submit" name="cmp_1_1" value="cmp_1_1">
</form>

<form method="post" action="<?=Query("?sid=?")?>">
	Compare one with all, 
	uid1=<input type="text" name="uid1" value="1881">
	<input type="submit" name="cmp_1_all" value="cmp_1_all">
	 (WARNING ! takes a while)
</form>

<form method="post" action="<?=Query("?sid=?")?>">
	Compare all with all
	<input type="submit" name="cmp_all_all" value="cmp_all_all">
	(WARNING ! takes a while)
</form>