<?php

require_once("lib.php");
$gGlobal = sqlgettable("SELECT `name`,`value` FROM `global`","name","value");

require_once("constants.php");

// todo : reduce these includes ??
require_once("lib.guild.php");
require_once("lib.building.php");
require_once("lib.terrain.php");
require_once("lib.quest.php");
require_once("lib.weather.php");
require_once("lib.text.php");

//readout global data

//readout globals
if (!file_exists(kTypeCacheFile))
	require_once("generate_types.php");
require_once(kTypeCacheFile);

//vardump($gTerrainType);

function GetZWStylePath () {
	// todo : replace by neutral/good/evil, or user-defined
	return kStyleServerPath.kZWStyle_Neutral."?time=".time(); // TODO : HACK : only for style dev..
}

function AdminBtn ($title,$url) {
	return "<a href='".Query($url)."'><img src='".g("icon/admin.png")."' alt='$title' title='$title' border=0></a>";
}
					
 // buildingparam : gate,portal,sign...
function GetBParam ($buildingid,$name,$default=false) {
	if (is_object($buildingid)) $buildingid = $buildingid->id;
	$res = sqlgetone("SELECT `value` FROM `buildingparam` WHERE `name`='".addslashes($name)."' AND `building`=".intval($buildingid));
	return $res?$res:$default;
}
function SetBParam ($buildingid,$name,$value) {
	if (is_object($buildingid)) $buildingid = $buildingid->id;
	sql("UPDATE `buildingparam` SET `value` = '".addslashes($value)."' WHERE `name`='".addslashes($name)."' AND `building`=".intval($buildingid));
	if (mysql_affected_rows() < 1)
		sql("INSERT INTO `buildingparam` SET `value` = '".addslashes($value)."' , `name`='".addslashes($name)."' , `building`=".intval($buildingid));
}
function ClearBParam ($buildingid,$name) {
	if (is_object($buildingid)) $buildingid = $buildingid->id;
	sql("DELETE FROM `buildingparam` WHERE `name`='".addslashes($name)."' AND `building`=".intval($buildingid));
}
// false for guild 0, (user->guild==user->guild) otherwise
function IsInSameGuild	($masteruser,$otheruser) { // obj or id
	if (!$masteruser || !$otheruser) return false;
	$masterguild = is_object($masteruser)?$masteruser->guild:sqlgetone("SELECT `guild` FROM `user` WHERE `id` = ".intval($masteruser));
	if (!$masterguild) return false;
	$otherguild = is_object($otheruser)?$otheruser->guild:sqlgetone("SELECT `guild` FROM `user` WHERE `id` = ".intval($otheruser));
	if (!$otherguild) return false;
	return $otherguild == $masterguild;
}


//returns the html code for the icon of the moral (0-200)
function Moral2HtmlIcon($moral){
	$moral = round(max(0,min(200,$moral)) / 20);
	switch($moral){
		case 0:$title="Uberböse";break;
		case 1:$title="Sadist";break;
		case 2:$title="Fiesling";break;
		case 3:$title="Bengelchen";break;
		case 4:$title="Gemein";break;
		case 5:$title="Ausgeglichen";break;
		case 6:$title="Nett";break;
		case 7:$title="Hilfsbereit";break;
		case 8:$title="Engelchen";break;
		case 9:$title="Gut";break;
		case 10:$title="Das absolut Gute";break;
		default:$title="Gesinnungslos";break;
	}
	return "<img src='".g("icon/moral/$moral.png")."' title='Gesinnung: $title' alt='Gesinnung: $title'>";
}

//change user moral deltamoral<0 -> bad, deltamoral>0 -> good
function changeUserMoral($userid,$deltamoral){
	$deltamoral = intval(round($deltamoral));
	$userid = intval($userid);
	// TODO :  moral = GREATEST(0,SMALLEST(200,moral+deltamoral)) oder so...
	// sonst hat superstarke moralaenderung keinerlei auswirkung, wenn sie über das limit kommen würde
	sql("UPDATE `user` SET `moral`=`moral`+($deltamoral) WHERE `id`=$userid AND (`moral`+($deltamoral))>=0 AND (`moral`+($deltamoral))<=200");
}

// friend or foe : kFOF_Neutral,kFOF_Friend,kFOF_Enemy
function GetFOF ($masteruserid,$otheruserid) {
	return intval(sqlgetone("SELECT `class` FROM `fof_user` WHERE `master` = ".intval($masteruserid)." AND `other` = ".intval($otheruserid)));
}

// set state
function SetFOF ($masteruserid,$otheruserid,$fof) {
	if ($fof == kFOF_Neutral) {
		sql("DELETE FROM `fof_user` WHERE `master` = ".intval($masteruserid)." AND `other` = ".intval($otheruserid));
		return;
	}
	sql("UPDATE `fof_user` SET `class` = ".intval($fof)." WHERE `master` = ".intval($masteruserid)." AND `other` = ".intval($otheruserid));
	if (mysql_affected_rows() == 0)
		sql("INSERT INTO `fof_user` SET `class` = ".intval($fof)." , `master` = ".intval($masteruserid)." , `other` = ".intval($otheruserid));
}

// get list of userids with certain state... DON'T ASK FOR NEUTRAL
function GetFOFUserlist ($masteruserid,$fof) {
	assert($fof != kFOF_Neutral);
	return sqlgetonetable("SELECT `other` FROM `fof_user` WHERE `class` = ".intval($fof)." AND `master` = ".intval($masteruserid));
}

// colorcoded $text, or colorcoded human-readable-fof state if $text=false
function GetFOFtxt	($masteruserid,$otheruserid,$text=false) {
	if (is_object($masteruserid)) $masteruserid = $masteruserid->id;
	if (is_object($otheruserid)) $otheruserid = $otheruserid->id;
	$fof = GetFOF($masteruserid,$otheruserid);
	if ($masteruserid == $otheruserid || $fof == kFOF_Friend) return "<font color='#00AA00'>".($text?$text:"Freund")."</font>";
	if ($fof == kFOF_Enemy) 							return "<font color='#FF0000'>".($text?$text:"Feind")."</font>"; 
	if (IsInSameGuild($masteruserid,$otheruserid))	return "<font color='#FF8800'>".($text?$text:"Gildenmitglied")."</font>"; 
	return "<font color='#0088FF'>".($text?$text:"Neutral")."</font>"; 
}

function SetGlobal ($name,$val) {
	global $gGlobal;
	if (isset($gGlobal[$name]))
			sql("UPDATE `global` SET `value`='".addslashes($val)."' WHERE `name`='".addslashes($name)."'");
	else	sql("REPLACE INTO `global` SET `value`='".addslashes($val)."' , `name`='".addslashes($name)."'");
	$gGlobal[$name] = $val;
}



function UserHasBuilding ($user,$type,$level=0) {
	// returns false if user does not have building, id otherwise
	$r = sqlgetone("SELECT `id` FROM `building` WHERE 
		`type` = ".intval($type)." AND `level` >= ".intval($level)." AND 
		`user` = ".intval($user)." LIMIT 1");
	return $r;
}

// $o must be building object, returns css-class for map
// TODO: DOOMED, OBSOLETE
function GetBuildingCSS ($o,$blocked) {
	global $gBuildingType;
	if (!isset($o->level) || $o->level < 10)
			$lpic="0";
	else	$lpic=1; //$lpic=floor($o->level/10);
	$css = $gBuildingType[$o->type]->cssclass;
	$css = str_replace("%L%",$lpic,$css);
	if($gBuildingType[$o->type]->race == 0) {
		$race = $o->user?intval(sqlgetone("SELECT `race` FROM `user` WHERE `id` = ".intval($o->user))):kRace_Mensch;
		$css = str_replace("%R%",$race,$css);
	}  else $css = str_replace("%R%",$gBuildingType[$o->type]->race,$css);
	
	if ($o->construction && !isset($f_planmap))
			return "con";
	else if ($o->type == kBuilding_Gate && !$blocked)
			return NWSEReplace("gate_%NWSE%_open_$lpic",$o->nwse);
	else if ($o->type == kBuilding_GB && !$blocked)
			return NWSEReplace("gb_%NWSE%_open_".$lpic,$o->nwse);
	else if ($o->type == kBuilding_Portal && intval(GetBParam($o->id,"target"))>0)
			return NWSEReplace("portal_open_".$lpic,$o->nwse);
	else if ($o->type == kBuilding_SeaGate && !$blocked)
			return NWSEReplace("seagate_%NWSE%_open_".$lpic,$o->nwse);
	else return NWSEReplace($css,$o->nwse);
	// TODO : use strtr ??
}

// replace "%NWSE%" by $nwse in $source path
function NWSEReplace ($source,$nwse) {
	return str_replace("%NWSE%",$nwse,$source);
}

// papyrus-border
function ImgBorderStart($style="p1",$type="jpg",$bgcolor="#F9EDCD",$bgpic="",$tilesize=14,$bordersize=13,$rootpath="papyrus/") {?>
	<table cellspacing="0" cellpadding="0" border=0>
	<tr>
		<td style="background:url(<?=g($rootpath."tl-".$style.".".$type)?>) no-repeat bottom right">
			<img src="<?=g("1px.gif")?>" alt="pfadfehler"
				width="<?=$bordersize?>" height="<?=$tilesize?>"></td>
		<td style="background:url(<?=g($rootpath."t-".$style.".".$type)?>) bottom repeat-x"></td>
		<td style="background:url(<?=g($rootpath."tr-".$style.".".$type)?>) no-repeat bottom left">
			<img src="<?=g("1px.gif")?>" alt="pfadfehler"
				width="<?=$bordersize?>" height="<?=$tilesize?>"></td>
	</tr>
	<tr>
		<td style="background:url(<?=g($rootpath."l-".$style.".".$type)?>) repeat-y right"></td>
		<td bgcolor="<?=$bgcolor?>" style="<?=($bgpic!=""?"background-image:url(".g($rootpath.$bgpic.".".$type).")":"")?>">
	<?php 
}

// papyrus-border
function ImgBorderEnd($style="p1",$type="jpg",$bgcolor="#F9EDCD",$tilesize=14,$bordersize=15,$rootpath="papyrus/") {?>
	</td>
		<td style="background:url(<?=g($rootpath."r-".$style.".".$type)?>) repeat-y left"></td>
	</tr>
	<tr>
		<td style="background:url(<?=g($rootpath."bl-".$style.".".$type)?>) no-repeat top right">
			<img src="<?=g("1px.gif")?>" alt="pfadfehler"
				width="<?=$bordersize?>" height="<?=$tilesize?>"></td>
		<td style="background:url(<?=g($rootpath."b-".$style.".".$type)?>) top repeat-x"></td>
		<td style="background:url(<?=g($rootpath."br-".$style.".".$type)?>) no-repeat top left">
			<img src="<?=g("1px.gif")?>" alt="pfadfehler"
				width="<?=$bordersize?>" height="<?=$tilesize?>"></td>
	</tr>
	</table>
	<?php 
}

// the cute bar for building healt, mana, ressources, armylimit...
// colored process bar
function DrawBar ($cur,$max,$color="green",$bgcolor="#eeeeee",$border=false) {
	if($border)$border = "style=\"border:solid black 1px\"";
	else $border = "";
	
	if ($cur <= 0)
	{
		?>
		<table width="100%" height="100%" cellspacing=0 cellspacing=2 <?=$border?>>
		<tr><td bgcolor="<?=$bgcolor?>"></td></tr></table>
		<?php
		return;
	}
	if ($cur >= $max)
	{
		?>
		<table width="100%" height="100%" cellspacing=0 cellspacing=2 <?=$border?>>
		<tr><td bgcolor="<?=$color?>"></td></tr></table>
		<?php
		return;
	}
	$factor = $cur / $max;
	?>
	<table width="100%" height="100%" cellspacing=0 cellspacing=2 <?=$border?>
	<tr>
		<td width="<?=floor($factor*100)?>%" bgcolor="<?=$color?>"></td>
		<td width="<?=floor((1-$factor)*100)?>%" bgcolor="<?=$bgcolor?>"></td>
	</tr>
	</table>
	<?php
}

// new and improved.... 
function GetBuildingPic ($type,$user=false,$level=10,$nwse="we") {
	global $gObject,$gUser,$gBuildingType;
	if ($user === false) $user = $gUser;
	if (!is_object($user)) $user = $user?sqlgetobject("SELECT * FROM `user` WHERE `id` = ".intval($user)):false;
	if (!is_object($type)) $type = $gBuildingType[$type];
	$race = $user ? $user->race : 1;
	$moral = $user ? $user->moral : 100;
	if ($level < 10) $level = 0; else $level = 1; // pic level
	return g($type->gfx,$nwse,$level,$race,$moral);
}
	
// userlog, visible in logwindow, see log.php
//writes a log entry
function LogMe($user,$topic,$type,$i1,$i2,$i3,$s1,$s2)
{
	//check if this message can be merged with almost an identical message
	$id = sqlgetone("SELECT `id` FROM `newlog` WHERE 
		`count`<100 AND `type`=".intval($type)." AND 
		`topic`=".intval($topic)." AND 
		`user`=".intval($user)." AND
		`time`>=(".time()."-(60*60*1)) AND
		`i1`=(".intval($i1).") AND 
		`i2`=(".intval($i2).") AND 
		`i3`=(".intval($i3).") AND 
		`s1`='".addslashes($s1)."' AND 
		`s2`='".addslashes($s2)."' LIMIT 1");
	if($id > 0){
		//oki, same message found, so increase the counter
		sql("UPDATE `newlog` SET `count`=`count`+1,`time`=".time()." WHERE `id`=".intval($id));
		return;
	}

	//no merging of log so post a new entry
	$o = null;
	$o->user = $user;
	$o->topic = $topic;
	$o->type = $type;
	$o->time = time();
	$o->i1 = $i1;
	$o->i2 = $i2;
	$o->i3 = $i3;
	$o->s1 = $s1;
	$o->s2 = $s2;
	sql("INSERT INTO `newlog` SET ".obj2sql($o));
}

// guildlog, visible in guild and guildlog window, see info/guildlog.php
//writes a log entry
function GuildLogMe($x,$y,$user1,$user2,$guild1,$guild2,$trigger,$what)
{
	//check if this message can be merged with almost an identical message
	$id = sqlgetone("SELECT `id` FROM `guildlog` WHERE 
		`count`<100 AND 
		`x`=".intval($x)." AND 
		`y`=".intval($y)." AND 
		`user1`=".intval($user1)." AND
		`user2`=".intval($user2)." AND
		`time`>=(".time()."-(60*60*1)) AND
		`guild1`=(".intval($guild1).") AND 
		`guild2`=(".intval($guild2).") AND 
		`trigger`='".addslashes($trigger)."' AND 
		`what`='".addslashes($what)."' LIMIT 1");
	if($id > 0){
		//oki, same message found, so increase the counter
		sql("UPDATE `guildlog` SET `count`=`count`+1,`time`=".time()." WHERE `id`=".intval($id));
		return;
	}

	//no merging of log so post a new entry
	$o = null;
	$o->user1 = $user1;
	$o->user2 = $user2;
	$o->x = $x;
	$o->y = $y;
	$o->guild1 = $guild1;
	$o->guild2 = $guild2;
	$o->time = time();
	$o->trigger = $trigger;
	$o->what = $what;
	sql("INSERT INTO `guildlog` SET ".obj2sql($o));
}

// get next step from current pos (x,y)  on the way from (x1,y1) to (x2,y2)
// needed by lib.army,map,siege etc..  independant, straight pathfinding
function GetNextStep ($x,$y,$x1,$y1,$x2,$y2,$debug=false) {
	if ($x == $x2 && $y == $y2) return array($x,$y); // already arrived
	
	// find back on track, should not happen
	if ($x1 < $x2) 
			{ $minx = $x1;$maxx = $x2; } 
	else	{ $minx = $x2;$maxx = $x1; }
	if ($y1 < $y2) 
			{ $miny = $y1;$maxy = $y2; } 
	else	{ $miny = $y2;$maxy = $y1; }
	$backx = ($x < $minx) ? ($minx - $x) : ( ($x > $maxx) ? ($maxx - $x) : 0 );
	$backy = ($y < $miny) ? ($miny - $y) : ( ($y > $maxy) ? ($maxy - $y) : 0 );
	if ($backx != 0 || $backy != 0) {
		if ($debug) echo "find back on track : $backx,$backy<br>";
		if (abs($backx) > abs($backy)) 
				return array($x+(($backx>0)?1:-1),$y);
		else	return array($x,$y+(($backy>0)?1:-1));
	}
	
	// waylength zero
	if ($x1 == $x2 && $y1 == $y2) return array($x1,$y1);
	
	$xdif = $x2-$x1;
	$ydif = $y2-$y1;
	if (abs($xdif) >= abs($ydif)) {
		// horizontal movement
		$line_y1 = ($y1+(($x-0.5-$x1)/$xdif)*$ydif);
		$line_y2 = ($y1+(($x+0.5-$x1)/$xdif)*$ydif);
		$miny = round(min($line_y1,$line_y2));
		$maxy = round(max($line_y1,$line_y2));
		
		if ($ydif > 0 && $y < $maxy) return array($x,$y+1); // move verti
		if ($ydif < 0 && $y > $miny) return array($x,$y-1); // move verti
		return array($x+(($xdif > 0)?1:-1),$y); // move hori
		
		if (0) {
			// old code
			$ideal_y = round($y1+(($x-$x1)/$xdif)*$ydif);
			if ($debug) echo "$ideal_y,";
			if ($y == $ideal_y)
					return array($x+(($xdif > 0)?1:-1),$y); // move hori
			else	return array($x,$y+(($ideal_y > $y)?1:-1)); // move verti
		}
	} else {
		// vertical movement
		$line_x1 = ($x1+(($y-0.5-$y1)/$ydif)*$xdif);
		$line_x2 = ($x1+(($y+0.5-$y1)/$ydif)*$xdif);
		$minx = round(min($line_x1,$line_x2));
		$maxx = round(max($line_x1,$line_x2));
		
		if ($xdif > 0 && $x < $maxx) return array($x+1,$y); // move hori
		if ($xdif < 0 && $x > $minx) return array($x-1,$y); // move hori
		return array($x,$y+(($ydif > 0)?1:-1)); // move verti
		
		
		if (0) {
			// old code
			$ideal_x = round($x1+(($y-$y1)/$ydif)*$xdif);
			if ($debug) echo "$ideal_x,";
			if ($x == $ideal_x)
					return array($x,$y+(($ydif > 0)?1:-1)); // move verti
			else	return array($x+(($ideal_x > $x)?1:-1),$y); // move hori
		}
	}
}


// pay res, true on success, atomar, $res>0 , uses $gPayCache_Users if available
// only needed in cron,cronlib and portal so far
function UserPay($uid,$lumber,$stone,$food,$metal,$runes=0) {
	if ($lumber+$stone+$food+$metal+$runes == 0) return true;
	global $gPayCache_Users;
	if (isset($gPayCache_Users)) {
		if (!isset($gPayCache_Users[$uid])) return false;
		$user =& $gPayCache_Users[$uid];
		if (max(0,$user->lumber) < $lumber) return false;
		if (max(0,$user->stone) < $stone) return false;
		if (max(0,$user->food) < $food) return false;
		if (max(0,$user->metal) < $metal) return false;
		if (max(0,$user->runes) < $runes) return false;
		$user->lumber -= $lumber;
		$user->stone -= $stone;
		$user->food -= $food;
		$user->metal -= $metal;
		$user->runes -= $runes;
		//echo "UserPay($uid,$lumber,$stone,$food,$metal,$runes)(cached) : success<br>";
		return true;
	}
	
	// new variant using mysql_affected_rows()
	sql("UPDATE `user` SET 
			`lumber` = `lumber` - ".$lumber.",
			`stone` = `stone` - ".$stone.",
			`food` = `food` - ".$food.",
			`metal` = `metal` - ".$metal.",
			`runes` = `runes` - ".$runes." 
			WHERE `id` = ".intval($uid)." AND
			GREATEST(0,`lumber`) >= ".$lumber." AND
			GREATEST(0,`stone`) >= ".$stone." AND
			GREATEST(0,`food`) >= ".$food." AND
			GREATEST(0,`metal`) >= ".$metal." AND
			GREATEST(0,`runes`) >= ".$runes." 
			LIMIT 1");
	if (mysql_affected_rows() > 0) {
		//echo "UserPay($uid,$lumber,$stone,$food,$metal,$runes) : success<br>";
		return true;
	} else {
		return false;
	}
}

// human readable duration in seconds
function Duration2Text ($dur) {
	// $dur is in seconds, outputs "T H:M:S"
	$dur = intval($dur);
	if ($dur <= 0) return "-";
	$s = $dur % 60;
	$m = floor($dur / 60) % 60;
	$h = floor($dur / (60*60)) % 24;
	$t = floor($dur / (60*60*24));
	if ($dur < 60)
			return sprintf("%ds",$s);
	else if ($t >= 1)
			return sprintf("%dT %d:%02d",$t,$h,$m);
	else	return sprintf("%d:%02d",$h,$m);
}

// exit if user is not admin
function AdminLock(){
	global $gUser;
	if($gUser->admin == 0)exit(error("admin access needed"));
	Lock();
}

// efficiency of workers
function GetProductionFaktoren ($uid) {
	$rpf = 0.8;
	$mml = GetTechnologyLevel(kTech_MagieMeisterschaft,$uid);
	if($mml>=1) $rpf += 0.6;
	if($mml>=2) $rpf += 0.6;
	
	return array(	
		"runes"		=> $rpf + GetTechnologyLevel(kTech_EffRunen,$uid)*0.2, //effiziente runen prod
		"stone"		=> 1+GetTechnologyLevel(kTech_Hammer,$uid)*0.05, //hammer
		"lumber"	=> 1+GetTechnologyLevel(kTech_Axt,$uid)*0.05, //axt
		"metal"		=> 1+GetTechnologyLevel(kTech_Spitzhacke,$uid)*0.05, //spitzhacke
		"food"		=> 1+GetTechnologyLevel(kTech_Sense,$uid)*0.05 //sense
		);
}

// so many workerst can work efficiently, $buildings is cache from cron
function GetProductionSlots ($uid,$buildings=false) {
	global $gGlobal,$gRes;
	
	$schichtarbeit = GetTechnologyLevel(kTech_SchichtArbeit,$uid)*0.5;//schichtarbeit
	
	if (!$buildings) // precalced in cron
		$buildings = sqlgettable("SELECT count( `id` ) AS `count` , `type` AS `type` , sum(`supportslots`) as `supportslots`, sum( `level` ) AS `level` 
						FROM `building` WHERE `construction`=0 AND `user`=".intval($uid)." GROUP BY `type`","type");
	if (!isset($buildings[kBuilding_HQ])) {
		$buildings[kBuilding_HQ]->count = 0;
		$buildings[kBuilding_HQ]->level = 0;
		$buildings[kBuilding_HQ]->supportslots = 0;
	}
	
	$slotlist = array();
	foreach($gRes as $resname=>$resfield) {
		//$slotlist[$resfield] = 0;
		$btype = $gGlobal["building_".$resfield];
		if(!$btype)$btype = 0;
		
		if (!isset($buildings[$btype])) {
			$buildings[$btype]->count = 0;
			$buildings[$btype]->level = 0;
			$buildings[$btype]->supportslots = 0;
		}
		if($resfield!="runes")
			$hq=$buildings[kBuilding_HQ]->count + $buildings[kBuilding_HQ]->level;
		else $hq=0;
		$slotlist[$resfield] = $buildings[$btype]->supportslots + ($gGlobal["prod_slots_".$resfield] + $schichtarbeit) * ( $hq +
			$buildings[$btype]->count + $buildings[$btype]->level);
		// level is sum of all levels for this type, count is added so level 0 does not result in 0 slots
	}
	return $slotlist;
}

// terrain-bonus-slots
function getSlotAddonFromSupportFields($b){ // id or object	
	if (!is_object($b)) $b = sqlgetobject("SELECT * FROM `building` WHERE `id`=".intval($b));
	if(!$b) return 0;
	global $gGlobal;
	
	$supporter=array();
	switch($b->type){
		case $gGlobal["building_runes"]:
			$supporter[] = kTerrain_River;
			$supporter[] = kTerrain_Sea;
		break;
		case $gGlobal["building_food"]:
			$supporter[] = kTerrain_Field;
			$supporter[] = kTerrain_River;
			$supporter[] = kTerrain_Sea;
		break;
		case $gGlobal["building_lumber"]:
			$supporter[] = kTerrain_Forest;
		break;
		case $gGlobal["building_stone"]:
			$supporter[] = kTerrain_Rubble;
			$supporter[] = kTerrain_Mountain;
		break;
		case $gGlobal["building_metal"]:
			$supporter[] = kTerrain_Mountain;
		break;
		default:
			return 0;
		break;
	}
	
	// TODO : DAS HIER BRAUCHT EWIG ZUM BERECHNEN !!!!
	$fields = sqlgetone("SELECT COUNT(`id`) FROM `terrain` WHERE `type` IN (".implode(",",$supporter).") AND 
		`x` >= ".($b->x-1)." AND `x` <= ".($b->x+1)." AND 
		`y` >= ".($b->y-1)." AND `y` <= ".($b->y+1));
	
	$slots = ceil($fields + $fields/10*$b->level);
	sql("UPDATE `building` SET `supportslots`=".intval($slots)." WHERE `id`=".intval($b->id)." LIMIT 1");
	return $slots;
}


// returns username or "Server" if 0,  see also cArmy::GetArmyOwnerName()
//if aslink = true <a..>NICK</a> will be returned
function nick($id=0,$fallback="Server",$aslink=false){
	if($id==0)return $fallback;
	$nick=sqlgetone("SELECT `name` FROM `user` WHERE 1 AND `id`=".intval($id)." LIMIT 1");
	if(empty($nick))return $fallback;
	else if($aslink){
		$ownerhq = sqlgetobject("SELECT `x`,`y` FROM `building` WHERE `type` = ".kBuilding_HQ." AND `user` = ".intval($id));
		if(empty($ownerhq))return $nick;
		else return "<a href=".query("?sid=?&x=".$ownerhq->x."&y=".$ownerhq->y).">$nick</a>";
	}
	else return $nick;
}

// 0 if unknown
function GetTechnologyLevel ($typeid,$userid=0) {
	global $gUser,$gTechnologyLevelsOfAllUsers;
	if ($userid == 0) $userid = $gUser->id;
	if (is_object($userid)) $userid = $userid->id;
	if (isset($gTechnologyLevelsOfAllUsers))
		return isset($gTechnologyLevelsOfAllUsers[$userid][$typeid])?($gTechnologyLevelsOfAllUsers[$userid][$typeid]):0;
	return intval(sqlgetone("SELECT `level` FROM `technology` WHERE `user` = ".intval($userid)." AND `type` = ".intval($typeid)." LIMIT 1"));
}

// 1.234.456 instead of 123456 + coloring
//uebergebe den integer wert , farbe ist die std farbe, neg_farbe ist die farbe fuer negative werte (default = farbe)
function ktrenner($wert,$farbe="#000000",$neg_farbe="",$fontsize=11){
	$s="";
	if($neg_farbe=="")$neg_farbe=$farbe;
	if($wert<0){
		$s="-";
		$wert=-1*$wert;
		$farbe=$neg_farbe;
	}
	$wert=intval($wert);
	$i=1;
	$o=array();
	while($wert>0){
		if($i>3){
			$i=1;
			$o[]=".";
		}
		$o[]=$wert%10;
		$wert=floor($wert/10);
		$i++;
	}
	if(count($o)<1)$o[]=0;
	$o[]=$s;
	$o=array_reverse($o);
	return "<span style='font-size:".$fontsize."pts;font-family:verdana;color:$farbe'>".join("",$o)."</span>";
}

// 1.234.456 instead of 123456
//uebergebe den integer wert , farbe ist die std farbe, neg_farbe ist die farbe fuer negative werte (default = farbe)
function kplaintrenner($wert){
	$s="";
	if($wert<0){
		$s="-";
		$wert=-1*$wert;
	}
	$wert=intval($wert);
	$i=1;
	$o=array();
	while($wert>0){
		if($i>3){
			$i=1;
			$o[]=".";
		}
		$o[]=$wert%10;
		$wert=floor($wert/10);
		$i++;
	}
	if(count($o)<1)$o[]=0;
	$o[]=$s;
	$o=array_reverse($o);
	return join("",$o);
}

$gProfilPagePage = "";
$gProfilPageTime = 0;
$gProfilPageMysql = 0;


//for profiling complete pages
function profile_page_start($page,$echo=false){
	global $gSqlQueries,$gProfilPageMysql, $gProfilPagePage, $gProfilPageTime;
	if ($gProfilPagePage != "") profile_page_end();
	
	$gProfilPageMysql = $gSqlQueries;
	$gProfilPageTime = microtime_float();
	$gProfilPagePage = $page;
	if ($echo)
		echo "------------------<br>$page<br>-------------------<br>";
}

// profiler
if (!function_exists("memory_get_usage")) {
	// my poor old php didn't know this one
	function memory_get_usage () {return 0;}
}

// profiler
function profile_page_end() {
	global $gProfilPageMysql,$gSqlQueries,$gProfilPagePage, $gProfilPageTime;
	if ($gProfilPagePage == "") return;
	
	$t = microtime_float();
	$dt = $t - $gProfilPageTime;
	$p = sqlgetobject("SELECT * FROM `profile` WHERE `page`='".addslashes($gProfilPagePage)."'");
	$q = $gSqlQueries-$gProfilPageMysql;
	if($p){
		$p->max = max($dt,$p->max);
		$p->sqlmax = max($q,$p->sqlmax);
		$p->memmax = max(memory_get_usage(),isset($p->memmax)?$p->memmax:0);
		$p->mem = (isset($p->mem)?$p->mem:0) + memory_get_usage();
		$p->sql += $q;
		$p->hits++;
		$p->time += $dt;
		sql("UPDATE `profile` SET ".obj2sql($p)." WHERE `page`='".addslashes($gProfilPagePage)."'");
	} else {
		$p->max = $dt;
		$p->sqlmax = $q;
		$p->sql = $q;
		$p->mem = memory_get_usage();
		$p->memmax = memory_get_usage();
		$p->hits = 1;
		$p->time = $dt;
		$p->page = $gProfilPagePage;
		sql("INSERT INTO `profile` SET ".obj2sql($p));
	}
	$gProfilPagePage = "";
}


//speichert wie die global name value paare ab, aber zu usern
//user = id/object
//name = name des feldes
//value = neuer wert des feldes
function SetUserValue($user,$name,$value){
	if(is_numeric($user))$id = $user;
	else $id = $user->id;
	$where = "`user`=".intval($id)." AND `name`='".addslashes($name)."'";
	$c = sqlgetone("SELECT count(*) FROM `uservalue` WHERE $where");
	if($c == 0)sql("INSERT INTO `uservalue` SET `user`=".intval($user->id).",`name`='".addslashes($name)."',`value`='".addslashes($value)."'");
	else sql("UPDATE `uservalue` SET `value`='".addslashes($value)."' WHERE $where");
}

//liest wie die global name value paare aus, aber zu usern
//user = id/object
//name = name des feldes
//default = falls der wert nicht gesetzt nimm das als default
function GetUserValue($user,$name,$default=""){
	if(is_numeric($user))$id = $user;
	else $id = $user->id;
	$where = "`user`=".intval($id)." AND `name`='".addslashes($name)."'";
	$c = sqlgetone("SELECT count(*) FROM `uservalue` WHERE $where");
	if($c == 0)return $default;
	else return sqlgetone("SELECT `value` FROM `uservalue` WHERE $where");
}

//converts bit flag nwse codes to old nwse string
function NWSECodeToStr($code){
	$out = "";
	if($code & kNWSE_N)$out .= "n";
	if($code & kNWSE_W)$out .= "w";
	if($code & kNWSE_S)$out .= "s";
	if($code & kNWSE_E)$out .= "e";
	return $out;
}


// for export to javascript, list object fields in $fields as comma seperated string (used by mapjs7.php)
function obj2jsparams ($obj,$fields) {
	$res = array();
	$fields = explode(",",$fields);
	foreach ($fields as $field) { $v = $obj->{$field}; $res[] = is_numeric($v)?$v:("\"".addslashes($v)."\""); }
	return implode(",",$res);
}

//retuns the complete path to the graphic given by a relative path from gfx/
//ie. item/drachenei.png
//todo: local path replacement
//done: %L% %NWSE% path replacement
function g($path,$nwse="ns",$level="0",$race="0",$moral="100"){
	global $gUser;
	$moral = max(0,min(200,$moral));
	//moral range from 0 - 4
	$moral = round($moral/200*4);
	if(is_numeric($nwse))$nwse = NWSECodeToStr($nwse);
	if($race == 0) $race = $gUser?$gUser->race:1;
	if($gUser && $gUser->usegfxpath && !empty($gUser->gfxpath)){
		if($gUser->gfxpath{strlen($gUser->gfxpath)-1} != '/')$base = $gUser->gfxpath . "/";
		else $base = $gUser->gfxpath;
	} else $base = kGfxServerPath;
	//return str_replace("%M%",$moral,str_replace("%R%",$race,str_replace("%NWSE%",$nwse,str_replace("%L%",$level,$base.$path))));
	return $base.str_replace("%M%",$moral,str_replace("%R%",$race,str_replace("%NWSE%",$nwse,str_replace("%L%",$level,$path))));
}


$gTableLockCounter = 0; //never change this by hand
$gTableLockQuery = "";
// lock all tables
function TablesLock(){
	global $gTableLockCounter,$gTableLockQuery;
	assert($gTableLockCounter>=0);
	if($gTableLockCounter == 0){
		if(empty($gTableLockQuery)){
			$r = sql("SHOW TABLES");
			$l = array();
			while($row = mysql_fetch_row($r))$l[] = "`".$row[0]."` WRITE";
			$gTableLockQuery = "LOCK TABLES ".implode(" , ",$l);
		}
		sql($gTableLockQuery);
	}
	++$gTableLockCounter;
}

// unlock all tables
function TablesUnlock(){
	global $gTableLockCounter;
	assert($gTableLockCounter>0);
	--$gTableLockCounter;
	if($gTableLockCounter == 0)sql("UNLOCK TABLES");
}



/**
* Berechnet den Nahrungsverbraucht von n Bewohnern für den Zeitraum dt
* @param n Bewohnerzahl
* @param dt Zeitraum in Sekunden
**/
function calcFoodNeed($n,$dt){
	return $n*$dt/24/60/60;
}


?>
