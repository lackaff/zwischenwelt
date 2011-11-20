<?php

require_once("lib.php");
require_once("lib.dbversion.php");

// global page start time
define("T", time());

$gGlobal = sqlgettable("SELECT `name`,`value` FROM `global`","name","value");

if (!defined("CHECK_ZW_DB_VERSION")) define("CHECK_ZW_DB_VERSION",true);
if (!defined("CHECK_ZW_CONFIG")) define("CHECK_ZW_CONFIG",true);
if (CHECK_ZW_CONFIG) {
	// check defines.mysql.php
	$configwarnings = array();

	/*
	define("BASEPATH","/var/www/zw05/");
	define("PHP_ERROR_LOG_MAIL","admin@main.blubber");
	define("kGfxServerPath","http://localhost/zw05/gfx/");
	define("kStyleServerPath","http://localhost/zw05/");
	define("PAGELAYOUT",4);
	define("BASEURL","http://localhost/zw05/");
	*/
	
	if (substr(trim(BASEPATH),0,4) == "http") 
		$configwarnings[] = "BASEPATH soll keine url sein, sondern ein lokaler pfad,".
			"also z.b. sowas wie /var/www/zw unter linux oder C:/wwwroot/zw unter win";
	
	if (!file_exists(BASEPATH)) {
		$configwarnings[] = "BASEPATH ist nicht erreichbar, das sollte der lokale, absolute pfad zum spiele verzeichnis sein,".
			"also z.b. sowas wie /var/www/zw unter linux oder C:/wwwroot/zw unter win";
		
		// suggestions 
		$sug = "Vorschlag für den BASEPATH : ";
		$configwarnings[] = $sug.$_SERVER[SCRIPT_FILENAME]." (ohne den php file am ende)";
		$configwarnings[] = $sug.$_SERVER[PATH_TRANSLATED]." (ohne den php file am ende)";
		$configwarnings[] = $sug.$_SERVER[DOCUMENT_ROOT].$_SERVER[PHP_SELF]." (ohne den php file am ende)";
		$configwarnings[] = $sug.$_SERVER[DOCUMENT_ROOT].$_SERVER[SCRIPT_NAME]." (ohne den php file am ende)";
			
		/*
		vardump2($_SERVER);
		DOCUMENT_ROOT	string	/mnt/hda6/wwwroot
		SCRIPT_FILENAME	string	/mnt/hda6/wwwroot/zw05/info/info.php
		REQUEST_URI	string		/zw05/info/info.php?x=323&y=-19&sid=JDEkczJIeUtyQ3IkTjd4QVY0azgzVUNL
		SCRIPT_NAME	string		/zw05/info/info.php
		PATH_TRANSLATED	string	/mnt/hda6/wwwroot/zw05/info/info.php
		PHP_SELF	string		/zw05/info/info.php
		*/
	}
	
	$slashend_constants = array("BASEPATH","kGfxServerPath","kStyleServerPath","BASEURL");
	foreach ($slashend_constants as $con) {
		$v = constant($con);
		if (substr($v,strlen($v)-1) != "/") 
			$configwarnings[] = "$con muss mit ein / am Ende haben";
	}
		
	$http_constants = array("kGfxServerPath","kStyleServerPath","BASEURL");
	foreach ($http_constants as $con) if (substr(constant($con),0,7) != "http://") 
		$configwarnings[] = "$con sollte eine URL sein, also mit http:// anfangen , und im Browser aufrufbar sein";
	
	// check tmp
	if (count($configwarnings) == 0) {
		if (!file_exists(BASEPATH."tmp/")) {
			$configwarnings[] = "Bitte ein 'tmp' verzeichnis unter BASEPATH anlegen";
		}
	}
	
	if (count($configwarnings) > 0) {
		echo "Bitte dem Admin melden, dass die Konfiguration in 'defines.mysql.php' eventuell falsch ist<br>";
		echo "Um diese Warnung hier zu ignorieren einfach define(\"CHECK_ZW_CONFIG\",false); in die defines.mysql.php eintragen<br>";
		echo implode("<br>",$configwarnings);
	}
}

if (CHECK_ZW_DB_VERSION) {
	$curv = GetCurDBVersion();
	$maxv = kCheckDBMaxVersion;
	if ($curv < $maxv) {
		$gDBNeedsUpdate = true;
		echo "Die Datenbank ist veraltet, bitte dem Admin bescheidsagen (cur:$curv max:$maxv).<br>";
		if ($gUser && $gUser->admin) {
			echo "Admin Rechte beim eingeloggten user vorhanden, für genauere Infos siehe : ";
			echo  "<a href='".Query(kCheckDBVersionScript."?sid=?")."'>".kCheckDBVersionScript."</a><br>";
		}
	}
}

if (ZW_ENABLE_CALLLOG) {
	function calllog_postvar ($o) {
		if (is_array($o)) {
			if (count($o) == 0) return "";
			$res = "";
			foreach ($o as $k => $v) $res .= "<k>".urlencode($k)."</k><v>".calllog_postvar($v)."</v>";
			return "<arr>".$res."</arr>";
		}
		return urlencode($o);
	}
	
	$calllog = false;
	$calllog->script = $_SERVER["SCRIPT_NAME"];
	if (strpos($calllog->script,kMapScript) === false) { // dont log maplook
	if (strpos($calllog->script,"cron.php") === false) { // dont log cron.php and minicron.php
	if (strpos($calllog->script,"log.php") === false) { // dont log log.php (logframe with auto-refresh)
		$calllog->query = str_replace("sid=".$gSID,"",$_SERVER["QUERY_STRING"]);
		if (strpos($calllog->script,"info.php") === false || count($_POST) > 0 || $calllog->query != "x=".$_REQUEST["x"]."&y=".$_REQUEST["y"]."&") { // dont log look-tool
			$calllog->time = time();
			$calllog->user = $gUser->id;
			$calllog->ip = $_SERVER["REMOTE_ADDR"];
			$calllog->post = calllog_postvar($_POST);
			sql("INSERT DELAYED INTO ".ZW_LOGDB_PREFIX."`calllog` SET ".obj2sql($calllog));
		}
	}
	}
	}
}

//readout globals
if (!isset($gTempTypeOverride)) {
	$gTmpTypesOk = false;
	if (!file_exists(kTypeCacheFile))
		require_once("generate_types.php");
	if (!file_exists(kTypeCacheFile)) {
		echo "Beim erstellen der Typecache Datei ist ein Fehler aufgetreten.<br>";
		echo "Dies könnte dadran liegen, dass der Webserver oder PHP keine Schreibrechte im BASEPATH/tmp Verzeichnis hat<br>";
		echo "Die Rechte sind im moment auf '".get_readable_permission(BASEPATH."tmp/")."' eingestellt, sie sollten auf 'rwxrwxrwx' stehen<br>";
		echo "Unter Linux kann man das mit dem Befehl 'chmod a+rwx tmp' erreichen<br>";
		echo "Unter Win kann man vielleicht mit rechtsclick auf den Ordner Lese&Schreibrechte geben.<br>";
		echo "Wenn man über ein FTP Programm zugriff hat, sollte man nach 'chmod' suchen, und da entweder alle rechte ankreuzen, oder auf 777 stellen.<br>";
		echo "<hr>Wichtig ist auch dass die Mysql-Datenbank richtig aufgesetzt wurde (Siehe Anleitung in 'INSTALL') <br>";
		exit();
	} else {
		require_once(kTypeCacheFile);
		if (!$gTmpTypesOk) {
			?>
			<?=kTypeCacheFile?> scheint kaputt zu sein<br>
			bitte <a href="<?="generate_types.php"?>">generate_types.php</a> im Browser aufrufen um den Fehler zu suchen<br>
			<?php
			exit();
		}
	} 
}

require_once("constants.php");

// todo : reduce these includes ??
require_once("lib.guild.php");
require_once("lib.building.php");
require_once("lib.terrain.php");
require_once("lib.quest.php");
require_once("lib.weather.php");
require_once("lib.text.php");

//readout global data


// be sure to require(kTypeCacheFile); again after this function
function RegenTypeCache ($newadder = -1) {
	//@unlink(kTypeCacheFile);
	//@unlink("info/".kTypeCacheFile);
	//@unlink("stats/".kTypeCacheFile);
	require_once("generate_types.php");
	// the following affects javascript version(for mapjs7_globals.php.js->types) and stylesheet
	// $gGlobal["typecache_version_adder"]
	global $gGlobal;
	$n = "typecache_version_adder";
	if ($newadder == -1) $newadder = intval($gGlobal[$n])+1;
	SetGlobal($n,intval($newadder));
}

//vardump($gTerrainType);

function GetZWStylePath () {
	global $gGlobal;
	global $gUser,$gSessionObj;
	
	if($gUser && $gUser->localstyles && !empty($gUser->gfxpath) && (empty($gSessionObj) || $gSessionObj->usegfx)){
		if($gUser->gfxpath{strlen($gUser->gfxpath)-1} != '/')$base = $gUser->gfxpath . "/";
		else $base = $gUser->gfxpath;
	} else $base = kGfxServerPath;
	
	
	// todo : replace by neutral/good/evil, or user-defined
	//return kStyleServerPath.kZWStyle_Neutral."?cssv=".(intval(kStyleSheetVersion)+intval($gGlobal["typecache_version_adder"]));
	return $base.kZWStyle_Neutral."?cssv=".(intval(kStyleSheetVersion)+intval($gGlobal["typecache_version_adder"]));
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
	if (empty($masteruser) || empty($otheruser)) return false;
	$masterguild = is_object($masteruser)?$masteruser->guild:sqlgetone("SELECT `guild` FROM `user` WHERE `id` = ".intval($masteruser));
	if (empty($masterguild)) return false;
	$otherguild = is_object($otheruser)?$otheruser->guild:sqlgetone("SELECT `guild` FROM `user` WHERE `id` = ".intval($otheruser));
	if (empty($otherguild)) return false;
	return $otherguild == $masterguild;
}


//returns the html code for the icon of the moral (0-200)
function Moral2HtmlIcon($moral){
	$moral = round(max(0,min(200,$moral)) / 20);
	switch($moral){
		case 0:$title="Evil";break;
		case 1:$title="Sadistic";break;
		case 2:$title="Nasty";break;
		case 3:$title="Mean";break;
		case 4:$title="Unpleasant";break;
		case 5:$title="Average";break;
		case 6:$title="Nice";break;
		case 7:$title="Helpful";break;
		case 8:$title="Friendly";break;
		case 9:$title="Good";break;
		case 10:$title="Really Good";break;
		default:$title="Angelic";break;
	}
	return "<img src='".g("icon/moral/$moral.png")."' title='Gesinnung: $title' alt='Gesinnung: $title'>";
}

//change user moral deltamoral<0 -> bad, deltamoral>0 -> good
function changeUserMoral($userid,$deltamoral){
	$deltamoral = intval(round($deltamoral));
	$userid = intval($userid);
	// DONE :  moral = GREATEST(0,SMALLEST(200,moral+deltamoral)) oder so...
	// sonst hat superstarke moralaenderung keinerlei auswirkung, wenn sie über das limit kommen würde
	//sql("UPDATE `user` SET `moral`=`moral`+($deltamoral) WHERE `id`=$userid AND (`moral`+($deltamoral))>=0 AND (`moral`+($deltamoral))<=200");
	sql("UPDATE `user` SET `moral`=GREATEST(0,LEAST(200,`moral`+($deltamoral))) WHERE `id`=$userid");
}

// friend or foe : kFOF_Neutral,kFOF_Friend,kFOF_Enemy
function GetFOF ($masteruserid,$otheruserid) {
	if ($masteruserid == $otheruserid) return kFOF_Friend; // one is always one selves friend
	if ($masteruserid == 0 && $otheruserid > 0) return kFOF_Enemy; // =0 means server, usually monsters
	if ($otheruserid == 0 && $masteruserid > 0) return kFOF_Enemy; // =0 means server, usually monsters
	return intval(sqlgetone("SELECT `class` FROM `fof_user` WHERE `master` = ".intval($masteruserid)." AND `other` = ".intval($otheruserid)));
}
function IsFriendlyServerBuilding ($building) {
	if (!is_object($building)) $building = sqlgetobject("SELECT * FROM `building` WHERE `id` = ".intval($building));
	if (empty($building)) return false;
	if ($building->user != 0) return false;
	if (sqlgetone("SELECT 1 FROM `hellhole` WHERE `x` = ".$building->x." AND `y` = ".$building->y)) return false;
	return true;
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

function ExistGlobal ($name){
	global $gGlobal;
	if (isset($gGlobal[$name])){
		return true;
	} else {
		return sqlgetone("SELECT 1 WHERE `name`='".mysql_real_escape_string($name)."' LIMIT 1") == 1;
	}
}

function GetGlobal ($name) {
	global $gGlobal;
	if (isset($gGlobal[$name])){
		return $gGlobal[$name];
	} else {
		$val = sqlgetone("SELECT `value` FROM `global` WHERE `name`='".mysql_real_escape_string($name)."' LIMIT 1");
		$gGlobal[$name] = $val;
		return $val;
	}	
}



function UserHasBuilding ($user,$type,$level=0) {
	// returns false if user does not have building, id otherwise
	$r = sqlgetone("SELECT `id` FROM `building` WHERE 
		`type` = ".intval($type)." AND `level` >= ".intval($level)." AND 
		`user` = ".intval($user)." LIMIT 1");
	return $r;
}

function CountUserBuildingType ($userid,$typeid,$plans_also=true) {
	if (is_object($userid)) $userid = $userid->id;
	if (is_object($typeid)) $typeid = $typeid->id;
	$res = sqlgetone("SELECT COUNT(*) FROM `building` WHERE `type` = ".intval($typeid)." AND `user` = ".intval($userid)." LIMIT 1");
	if ($plans_also) $res += sqlgetone("SELECT COUNT(*) FROM `construction` WHERE `type` = ".intval($typeid)." AND `user` = ".intval($userid)." LIMIT 1");
	return intval($res);
}

function CountUserUnitType ($userid,$typeid) {
	if (is_object($userid)) $userid = $userid->id;
	if (is_object($typeid)) $typeid = $typeid->id;
	$res = sqlgetone("SELECT SUM(`amount`) FROM `unit`,`building` WHERE 
		`unit`.`type` = ".intval($typeid)." AND 
		`unit`.`building` = `building`.`id` AND 
		`building`.`user` = ".intval($userid)." LIMIT 1");
	$res += sqlgetone("SELECT SUM(`amount`) FROM `unit`,`army` WHERE 
		`unit`.`type` = ".intval($typeid)." AND 
		`unit`.`army` = `army`.`id` AND 
		`army`.`user` = ".intval($userid)." LIMIT 1");
	return intval($res);
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

$gBorderParamStack = array();

// papyrus-border
function ImgBorderStart($style="p1",$type="jpg",$bgcolor="#F9EDCD",$bgpic="",$tilesize=14,$bordersize=13,$rootpath="papyrus/") {
	global $gBorderParamStack;
	$p = array("style"=>$style,"type"=>$type,"bgcolor"=>$bgcolor,
			"bgpic"=>$bgpic,"tilesize"=>$tilesize,"bordersize"=>$bordersize,"rootpath"=>$rootpath);
	array_push($gBorderParamStack,$p);
?>
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
//all parameters are ignored, becase they come from the new stack
function ImgBorderEnd($style="p1",$type="jpg",$bgcolor="#F9EDCD",$tilesize=14,$bordersize=15,$rootpath="papyrus/") {
	global $gBorderParamStack;
	$p = array_pop($gBorderParamStack);
	$style = $p["style"];
	$type = $p["type"];
	$bgcolor = $p["bgcolor"];
	$tilesize = $p["tilesize"];
	$bordersize = $p["bordersize"];
	$rootpath = $p["rootpath"];
?>
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
		<table width="100%" style="height:100%" cellspacing=0 <?=$border?>>
		<tr><td bgcolor="<?=$bgcolor?>"></td></tr></table>
		<?php
		return;
	}
	if ($cur >= $max)
	{
		?>
		<table width="100%" style="height:100%" cellspacing=0 <?=$border?>>
		<tr><td bgcolor="<?=$color?>"></td></tr></table>
		<?php
		return;
	}
	$factor = $cur / $max;
	?>
	<table width="100%" style="height:100%" cellspacing=0 <?=$border?>>
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
	if ($level < 10) $level = 0; 
	else if ($level < 50) $level = 1;
	else if ($level < 100) $level = 2;
	else if ($level < 200) $level = 3;
	else $level = 4; // pic level
	$level = min($level,$type->maxgfxlevel);
	return g($type->gfx,$nwse,$level,$race,$moral);
}
	
// userlog, visible in logwindow, see log.php
//writes a log entry
//if bStackMessages is true then it will be tried to stack identical messages
function LogMe($user,$topic,$type,$i1,$i2,$i3,$s1,$s2,$bStackMessages=true)
{
	//check if this message can be merged with almost an identical message
	if (is_object($s2)) { echo "LogMe,s2 is obj:".stacktrace()."<br>"; vardump2($s2); }
	if (is_object($s1)) { echo "LogMe,s1 is obj:".stacktrace()."<br>"; vardump2($s1); }
	
	if($bStackMessages){
		//try to stack messages
		$id = sqlgetone("SELECT `id` FROM `newlog` WHERE 
			`user`=".intval($user)." AND
			`time`>=(".time()."-(60*60*1)) AND
			`topic`=".intval($topic)." AND 
			`count`<100 AND `type`=".intval($type)." AND 
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
	}

	//no merging of log so post a new entry
	$o = new EmptyObject();
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


// short version of the GuildLogMe function
function GuildLogMeShort($x,$y,$user1,$user2,$trigger,$what){
	$guild1 = sqlgetone("SELECT `guild` FROM `user` WHERE `id` = ".intval($user1));
	$guild2 = sqlgetone("SELECT `guild` FROM `user` WHERE `id` = ".intval($user2));
	GuildLogMe($x,$y,$user1,$user2,$guild1,$guild2,$trigger,$what);
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


function FillUserCache(){
	global $gPayCache_Users;
	$gPayCache_Users = sqlgettable("SELECT * FROM `user` ORDER BY `id`","id");
}

function ClearUserCache(){
	global $gPayCache_Users;
	unset($gPayCache_Users);
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
	else if ($dur < 60*5) 
			return sprintf("%d:%02d:%02d",$h,$m,$s);
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
	
	if (empty($buildings)) // precalced in cron
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
		if(empty($btype))$btype = 0;
		
		if (!isset($buildings[$btype])) {
			$buildings[$btype] = new EmptyObject();
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
	if(empty($b)) return 0;
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
function nick($id=0,$fallback="Server",$aslink=false) {
	global $gUser;
	if($id==0)return $fallback;
	$owner = sqlgetobject("SELECT * FROM `user` WHERE 1 AND `id`=".intval($id)." LIMIT 1");
	$nick = $owner->name;
	if(empty($nick))return $fallback;
	else if($aslink){
		$ownerhq = $owner->id ? sqlgetobject("SELECT `x`,`y` FROM `building` WHERE `type` = ".kBuilding_HQ." AND `user` = ".intval($owner->id)) : false;
		rob_ob_start();
		?>
		<a href="<?=query("?sid=?&x=".($ownerhq?$ownerhq->x:0)."&y=".($ownerhq?$ownerhq->y:0))?>"><?=GetFOFtxt($gUser->id,$owner->id,$nick)?></a>
		<?php if ($owner) {?>
			<a href="<?=query("msg.php?show=compose&to=".urlencode($owner->name)."&sid=?")?>"><img border=0 src="<?=g("icon/guild-send.png")?>"></a>
		<?php } // endif?>
		<?=$ownerhq?opos2txt($ownerhq):""?>
		<?php 
		$text = rob_ob_end();
		return $text;
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
function ktrenner($wert,$farbe="#000000",$neg_farbe="",$fontsize=9){
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
	return "<span style='font-size:".$fontsize."pt;font-family:verdana;color:$farbe'>".join("",$o)."</span>";
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


$gLoopProfiler = array();
$gLastLoopProfilerName = false;
$gLoopProfiler_LastQuerries = 0;
$gLoopProfiler_LastTime = 0;
$gLoopProfiler_LastFlushTime = 0;

// the LoopProfiler is used to profile small portions of a lengthy loop,
// for example the different parts (think,act,move,..) of the army loop in minicron.php

// call bevore start and after end
function LoopProfiler_flush ($echo=false) {
	if (ZW_ENABLE_PROFILING) {
		global $gLoopProfiler,$gLastLoopProfilerName,$gSqlQueries;
		global $gLoopProfiler_LastQuerries,$gLoopProfiler_LastTime,$gLoopProfiler_LastFlushTime;
		$now = microtime_float();
		LoopProfiler_endlast($now);
		
		if ($echo) echo "\nLoopProfiler_flush : totaltime = ".sprintf("%0.3f",$now - $gLoopProfiler_LastFlushTime)."<br>\n";
		foreach ($gLoopProfiler as $name => $o) {
			if ($echo) {
				// echo report
				echo "\nLoopProfiler:".$name.":";
				echo "\n totaltime=".sprintf("%0.3f",$o->totaltime).";";
				echo "\n maxtime=".sprintf("%0.6f",$o->maxtime).";";
				echo "\n avgtime=".sprintf("%0.6f",(($o->count>0)?($o->totaltime/$o->count):0)).";";
				echo "\n querries=".$o->querries.";";
				echo "\n count=".$o->count.";";
				echo "<br>";
			}
			// enter into db
			$q = $o->querries;
			$p = sqlgetobject("SELECT * FROM `profile` WHERE `page`='".addslashes($name)."'");
			if ($p) {
				$p->max = max($o->maxtime,$p->max);
				$p->sqlmax = max($q,$p->sqlmax);
				//$p->memmax = max(memory_get_usage(),isset($p->memmax)?$p->memmax:0);
				//$p->mem = (isset($p->mem)?$p->mem:0) + memory_get_usage();
				$p->sql += $q;
				$p->hits += $o->count;
				$p->time += $o->totaltime;
				sql("UPDATE `profile` SET ".obj2sql($p)." WHERE `page`='".addslashes($name)."'");
			} else {
				$p->max = $o->maxtime;
				$p->sqlmax = $q;
				$p->sql = $q;
				//$p->mem = memory_get_usage();
				//$p->memmax = memory_get_usage();
				$p->hits = $o->count;
				$p->time = $o->totaltime;
				$p->page = $name;
				sql("INSERT INTO `profile` SET ".obj2sql($p));
			}
		}
		$gLoopProfiler = array();
		$gLastLoopProfilerName = new EmptyObject();
		$gLoopProfiler_LastQuerries = $gSqlQueries;
		$gLoopProfiler_LastTime = $now;
		$gLoopProfiler_LastFlushTime = $now;
	}
}

// no need to call me directly, completes last loopprofiler entry
function LoopProfiler_endlast ($now) {
	if (ZW_ENABLE_PROFILING) {
		global $gLoopProfiler,$gLastLoopProfilerName,$gSqlQueries,$gLoopProfiler_LastQuerries,$gLoopProfiler_LastTime;
		if ($gLastLoopProfilerName) {
			if (!isset($gLoopProfiler[$gLastLoopProfilerName])) {
				// create new entry
				$gLoopProfiler[$gLastLoopProfilerName] = new EmptyObject();
				$o =& $gLoopProfiler[$gLastLoopProfilerName];
				$o->totaltime = 0; 
				$o->maxtime = 0; 
				$o->querries = 0; 
				$o->count = 0;
			} else {
				// update existing entry
				$o =& $gLoopProfiler[$gLastLoopProfilerName];
				$dt = $now - $gLoopProfiler_LastTime;
				$o->totaltime += $dt; 
				$o->maxtime = max($o->maxtime,$dt); 
				$o->querries += $gSqlQueries - $gLoopProfiler_LastQuerries; 
				$o->count++;
			}
		}
		$gLoopProfiler_LastQuerries = $gSqlQueries;
		$gLoopProfiler_LastTime = $now;
	}
}

// call me before each section
function LoopProfiler ($name) {
	if (ZW_ENABLE_PROFILING) {
		global $gLastLoopProfilerName;
		$now = microtime_float();
		LoopProfiler_endlast($now);
		$gLastLoopProfilerName = $name;
	}
}


$gProfilPagePage = "";
$gProfilPageTime = 0;
$gProfilPageMysql = 0;

//for profiling complete pages
function profile_page_start($page,$echo=false){
	if (ZW_ENABLE_PROFILING) {
		global $gSqlQueries,$gProfilPageMysql, $gProfilPagePage, $gProfilPageTime;
		if ($gProfilPagePage != "") profile_page_end();
		
		$gProfilPageMysql = $gSqlQueries;
		$gProfilPageTime = microtime_float();
		$gProfilPagePage = $page;
	}
	if ($echo)
		echo "<br>\n------------------ $page -------------------<br>\n";
}

// profiler
if (!function_exists("memory_get_usage")) {
	// my poor old php didn't know this one
	function memory_get_usage () {return 0;}
}

// profiler
function profile_page_end() {
	if (ZW_ENABLE_PROFILING) {
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
}


//speichert wie die global name value paare ab, aber zu usern
//user = id/object
//name = name des feldes
//value = neuer wert des feldes
function SetUserValue($user,$name,$value){
	if(is_numeric($user))$id = $user;
	else $id = $user->id;
	$where = "`user`=".intval($id)." , `name`='".addslashes($name)."'";
	sql("REPLACE INTO `uservalue` SET `value`='".addslashes($value)."' , $where");
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
function obj2jsparams ($obj,$fields,$quote='"') {
	$res = array();
	$fields = explode(",",$fields);
	foreach ($fields as $field) { $v = $obj->{$field}; $res[] = is_numeric($v)?$v:($quote.addslashes($v).$quote); }
	return implode(",",$res);
}

//retuns the complete path to the graphic given by a relative path from gfx/
//ie. item/drachenei.png
//todo: local path replacement
//done: %L% %NWSE% path replacement
function g($path,$nwse="ns",$level="0",$race="0",$moral="100",$random=0){
	global $gUser,$gSessionObj;
	$moral = max(0,min(200,$moral));
	//moral range from 0 - 4
	$moral = round($moral/200*4);
	if(is_numeric($nwse))$nwse = NWSECodeToStr($nwse);
	if($race == 0) $race = $gUser?$gUser->race:1;
	if($gUser && !empty($gUser->gfxpath) && (empty($gSessionObj) || $gSessionObj->usegfx)){
		if($gUser->gfxpath{strlen($gUser->gfxpath)-1} != '/')$base = $gUser->gfxpath . "/";
		else $base = $gUser->gfxpath;
	} else $base = kGfxServerPath;
	//return str_replace("%M%",$moral,str_replace("%R%",$race,str_replace("%NWSE%",$nwse,str_replace("%L%",$level,$base.$path))));
	//%BUSY% is used for switching on and of the animation in the production buildings
	return $base.str_replace("%M%",$moral,str_replace("%R%",$race,str_replace("%NWSE%",$nwse,str_replace("%L%",$level,str_replace("%RND%",$random,str_replace("%BUSY%","0",$path))))));
}


$gTableLockCounter = 0; //never change this by hand

// if a table lock should be skipped (skip) or readonly lock (read)
// you can overwrite the default write lock behaviour
$gTableLockPreferences = array(
	'session' => 'read',
	//'calllog' => 'read',
	//'fightlog' => 'read',
	//'guildlog' => 'read',
	//'log' => 'read',
	'armytype' => 'read',
	'buildingtype' => 'read',
	'itemtype' => 'read',
	'technologytype' => 'read',
	'terrainpatchtype' => 'read',
	'terrainsubtype' => 'read',
	'armytype' => 'read',
	'terraintype' => 'read',
	'unittype' => 'read',
);

// lock all tables
function TablesLock($ignore_lock_preferences = false){
	global $gTableLockCounter,$gTableLockPreferences;
	assert($gTableLockCounter>=0);
	if($gTableLockCounter == 0){
		$r = sql("SHOW TABLES");
		$l = array();
		while($row = mysql_fetch_row($r)){
			$mode = 'WRITE';
			$t = $row[0];
			if($ignore_lock_preferences == false && isset($gTableLockPreferences[$t])){
				if($gTableLockPreferences[$t] == 'skip')continue;
				else if($gTableLockPreferences[$t] == 'read')$mode = 'READ';
			}

			$l[] = "`".$t."` $mode";
		}
		sql("LOCK TABLES ".implode(" , ",$l));
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

// not currently used
function remove_from_array ($needle,$haystack) {
	$res = array();
	foreach ($haystack as $key => $val) if ($val !== $needle) $res[$key] = $val;
	return $res;
}

// implements brush functionality from mapnavi to info/info.php and info/infoadmincmd.php
// lineon=false->force normal, irgnore x2,y2
// linetrue=false : start at x2y2, end at x1y1, remove first circle
// brushmode=0:normal;=1,=2:line;=3:rect
// returns an array with all affected coordinates
function GetBrushFields ($x1,$y1,$brushrad=0,$brushdensity=100,$brushmode=0,$lineon=false,$x2=0,$y2=0) {
	$x1 = intval($x1);
	$y1 = intval($y1);
	$x2 = intval($x2);
	$y2 = intval($y2);
	$brushdensity = intval($brushdensity);
	$brushrad = intval($brushrad);
	$brushmode = intval($brushmode);
	$r2 = $brushrad + 0.5; $r2 = $r2 * $r2;
	
	//if ($brushmode == 1 && !$lineon) return;
	if ($brushmode == 3 && !$lineon) return array(); // only draw rect at full click
	
	$debug = false;
	if ($debug) echo "GetBrushFields($x1,$y1,$brushrad,$brushmode,$lineon,$x2,$y2):";
	
	$res = array();
	$firstdot = array();
	// calc the first dot, so it can be left out of the following line
	if ($brushmode == 0 || $brushmode == 1 || $brushmode == 2) {
		// draw circle
		$bx = $lineon?$x2:$x1;
		$by = $lineon?$y2:$y1;
		if ($debug) echo "draw circle;";
		for ($x=-$brushrad;$x<=$brushrad;$x++)
		for ($y=-$brushrad;$y<=$brushrad;$y++) {
			if ($x*$x+$y*$y > $r2) continue;
			$firstdot[] = array($bx+$x,$by+$y);
		}
	}
	
	
	if ($brushmode == 0 || !$lineon) {
		$res = $firstdot;
	} else if ($brushmode == 3) {
		// draw rect
		if ($debug) echo "draw rect;";
		for ($x=$x2;$x<=max($x1,$x2) && $x>=min($x1,$x2);$x+=($x1>=$x2)?1:-1)
		for ($y=$y2;$y<=max($y1,$y2) && $y>=min($y1,$y2);$y+=($y1>=$y2)?1:-1)
			$res[] = array($x,$y);
	} else {
		// draw line
		if ($debug) echo "draw line;";
		$bx = $x2;
		$by = $y2;
		do {
			for ($x=-$brushrad;$x<=$brushrad;$x++)
			for ($y=-$brushrad;$y<=$brushrad;$y++) {
				if ($x*$x+$y*$y > $r2) continue;
				$new = array($bx+$x,$by+$y);
				if (!in_array($new,$res) && !in_array($new,$firstdot)) $res[] = $new;
			}
			if ($bx == $x1 && $by == $y1) break;
			list($bx,$by) = GetNextStep($bx,$by,$x2,$y2,$x1,$y1);
		} while (1);
	}
	// if density is used (<100) then break up the solid brush
	if ($brushdensity < 100) {
		$softened = array();
		foreach ($res as $pos) if (rand(1,100) <= $brushdensity) $softened[] = $pos;
		return $softened;
	}
	return $res;
}


/**
* Berechnet den Nahrungsverbraucht von n Bewohnern für den Zeitraum dt
* @param n Bewohnerzahl
* @param dt Zeitraum in Sekunden
**/
function calcFoodNeed($n,$dt){
	return $n*$dt/24/60/60;
}

//generates a color
//position 0-100
  function getacolor($position,$bright=1,$gamma=0,$min=0,$max=100)
  {
    $colorset=array
    (
      0 => array(128,0,0),
      15 => array(200,0,0),
      30 => array(255,128,0),
      55 => array(255,255,0),
      70 => array(192,255,0),
      100 => array(0,128,0),
    );
    ksort($colorset);
    $position=round($position);
    if ($position<$min) $position=$min;
    if ($position>$max) $position=$max;
    if (!isset($colorset[$position]))
    {
      $knownpos=array_keys($colorset);
      foreach ($knownpos as $i=>$p)
        if ($p>$position) break;
      $x1=$knownpos[$i-1];
      $x2=$knownpos[$i];
      $r=$colorset[$x1][0]+($colorset[$x2][0]-$colorset[$x1][0])/($x2-$x1)*($position-$x1);
      $g=$colorset[$x1][1]+($colorset[$x2][1]-$colorset[$x1][1])/($x2-$x1)*($position-$x1);
      $b=$colorset[$x1][2]+($colorset[$x2][2]-$colorset[$x1][2])/($x2-$x1)*($position-$x1);
      $colorset[$position]=array($r,$g,$b);
    }
    $rgb=$colorset[$position];
    $rgb[0]=round($rgb[0]*$bright)+$gamma;
    $rgb[1]=round($rgb[1]*$bright)+$gamma;
    $rgb[2]=round($rgb[2]*$bright)+$gamma;

    // overbright
    if ($rgb[0]>255) { $rgb[1]+=floor(($rgb[0]-255)/2); $rgb[2]+=floor(($rgb[0]-255)/2); }
    if ($rgb[1]>255) { $rgb[0]+=floor(($rgb[1]-255)/2); $rgb[2]+=floor(($rgb[1]-255)/2); }
    if ($rgb[2]>255) { $rgb[0]+=floor(($rgb[2]-255)/2); $rgb[1]+=floor(($rgb[2]-255)/2); }

    // undderbright
    if ($rgb[0]<0) { $rgb[1]+=ceil($rgb[0]/2); $rgb[2]+=ceil($rgb[0]/2); }
    if ($rgb[1]<0) { $rgb[0]+=ceil($rgb[1]/2); $rgb[2]+=ceil($rgb[1]/2); }
    if ($rgb[2]<0) { $rgb[0]+=ceil($rgb[2]/2); $rgb[1]+=ceil($rgb[2]/2); }

    // limiting
    if ($rgb[0]<0) $rgb[0]=0; if ($rgb[0]>255) $rgb[0]=255;
    if ($rgb[1]<0) $rgb[1]=0; if ($rgb[1]>255) $rgb[1]=255;
    if ($rgb[2]<0) $rgb[2]=0; if ($rgb[2]>255) $rgb[2]=255;
    return sprintf('#%02s%02s%02s',dechex($rgb[0]),dechex($rgb[1]),dechex($rgb[2]));
  } // getacolor()

function shortNumber($x){
	$unit = "";
	if($x>10000000){
		$unit = "M";
		$x = round($x / 1000000);
	} else if($x>100000){
		$unit = "k";
		$x = round($x / 1000);
	}
	return ktrenner($x).$unit;
}


function drawressource_cmp($a, $b)
{
   $a = strlen($a);
   $b = strlen($b);
   if ($a == $b) {
       return 0;
   }
   return ($a > $b) ? -1 : 1;
}

function drawressource($resname,$resimg,$resact,$resmax,$fmt)
{
 if ($resmax>0)
   $p=$resact/$resmax;
 else
   $p=0;
 $resproz=round(100*$p);
 $res16=round(16*$p);
 $rescolor=getacolor($resproz,1.5,-60);
 $resbcolor=getacolor($resproz,0.8,100);
 $info = "$resname: $resact / $resmax ($resproz%)";
 $lagerstandcode=array
 (

   'JUSTIFY' => '',
   'CENTER' => '',
   'RIGHT' => '',
   'LEFT' => '',

   '[B]'  => '<b>',
   '[/B]' => '</b>',
   '[I]'  => '<i>',
   '[/I]' => '</i>',
   '[U]'  => '<u>',
   '[/U]' => '</u>',
   '[TT]'  => '<tt>',
   '[/TT]' => '</tt>',
   '[PRE]'  => '<pre>',
   '[/PRE]' => '</pre>',

   '[L]'  => '<div style="text-align: left;">',
   '[R]'  => '<div style="text-align: right;">',
   '[C]'  => '<div style="text-align: center;">',
   '[/L]' => '</div>',
   '[/R]' => '</div>',
   '[/C]' => '</div>',

   '[BIG]'  => '<span style="font-size: 14px;">',
   '[/BIG]' => '</span>',
   '[SMALL]'  => '<span style="font-size: 9px;">',
   '[/SMALL]' => '</span>',

   'PROZ'=> $resproz.'%',
   'TCOL'=> '<span style="color:'.$rescolor.'">'.$resproz.'%</span>',
   'VERT'=> '',
   'SEP' => '</td><td style="border-left: 3px solid #d0d0d0;">',
   'TAB' => '</td><td>',

   'AXC' => '<span style="color:'.$rescolor.'">'.number_format($resact,0,',','.').'</span>',
   'AKC' => '<span style="color:'.$rescolor.'">'.number_format(round($resact/100),0,',','.').'</span>',
   'ASC' => '<span style="color:'.$rescolor.'">'.shortNumber($resact).'</span>',
   'AXB' => '<span style="background-color:'.$rescolor.'">'.number_format($resact,0,',','.').'</span>',
   'AKB' => '<span style="background-color:'.$rescolor.'">'.number_format(round($resact/100),0,',','.').'</span>',
   'ASB' => '<span style="background-color:'.$rescolor.'">'.shortNumber($resact).'</span>',
   'MXC' => '<span style="color:'.$rescolor.'">'.number_format($resmax,0,',','.').'</span>',
   'MKC' => '<span style="color:'.$rescolor.'">'.number_format(round($resmax/100),0,',','.').'</span>',
   'MSC' => '<span style="color:'.$rescolor.'">'.shortNumber($resmax).'</span>',
   'MXB' => '<span style="background-color:'.$rescolor.'">'.number_format($resmax,0,',','.').'</span>',
   'MKB' => '<span style="background-color:'.$rescolor.'">'.number_format(round($resmax/100),0,',','.').'</span>',
   'MSB' => '<span style="background-color:'.$rescolor.'">'.shortNumber($resmax).'</span>',

   'HOR' => '',
   'VER' => '',
   'MAX' => number_format($resmax,0,',','.'),
   'ACT' => number_format($resact,0,',','.'),

   'TC'  => '<span style="color:'.$rescolor.'">'.$resproz.'%</span>',
   'HR' => '</td><td style="border-left: 1px solid #e0e0e0;">',
   'RT'  => $resname,
   'RN'  => $resname,
   'RG'  => '<img alt="'.$info.'" title="'.$info.'" src="'.g($resimg).'">',
   'RI'  => '<img alt="'.$info.'" title="'.$info.'" src="'.g($resimg).'">',
   'T1'  => $resproz.'%',
   'T2'  => '<span style="color:'.$rescolor.'">'.$resproz.'%</span>',
   'T3'  => '<span style="background-color:'.$resbcolor.'">'.$resproz.'%</span>',
   'TB'  => '<span style="background-color:'.$resbcolor.'">'.$resproz.'%</span>',
   'G1'  => '<img alt="'.$resproz.'%" title="'.$resproz.'%" src="'.g('lager/breit/lagerstand_'.$res16.'.gif').'">',
   'G2'  => '<img alt="'.$resproz.'%" title="'.$resproz.'%" src="'.g('lager/schmal/lagerstand_'.$res16.'.gif').'">',
   'G3'  => '<img alt="'.$resproz.'%" title="'.$resproz.'%" src="'.g('lager/blass/lagerstand_'.$res16.'.gif').'">',
   'G4'  => '<img alt="'.$resproz.'%" title="'.$resproz.'%" src="'.g('lager/grau/lagerstand_'.$res16.'.gif').'">',
   'G5'  => '<img alt="'.$resproz.'%" title="'.$resproz.'%" src="'.g('lager/normal/lagerstand_'.$res16.'.gif').'">',
   'G6'  => '<img alt="'.$resproz.'%" title="'.$resproz.'%" src="'.g('lager/bubble/lagerstand_'.$res16.'.gif').'">',
   'G7'  => '<img alt="'.$resproz.'%" title="'.$resproz.'%" src="'.g('lager/bubbletech/lagerstand_'.$res16.'.gif').'">',
   'MX'  => number_format($resmax,0,',','.'),
   'MK'  => number_format(round($resmax/1000),0,',','.').'k',
   'MS'  => shortNumber($resmax),
   'AX'  => number_format($resact,0,',','.'),
   'AK'  => number_format(round($resact/1000),0,',','.').'k',

   'AS'  => shortNumber($resact),
   'BR'  => '<br>',
 );

 uksort($lagerstandcode, "drawressource_cmp");
 $output=htmlentities($fmt);
 $output=str_replace("  "," &nbsp;",$output); // PRE
 foreach ($lagerstandcode as $key=>$value)
 {
   $output=str_replace($key,$value,$output);
 }
 $output='<td>'.$output.'</td>';
 if (strpos($fmt,'HOR')===false)
   $output='<tr>'.$output.'</tr>';
 echo $output."\n";
} // drawressource() 

//echoes a formated ($fmt) table with the ressources of the $user
//showrealcontent - draw user content or testvalues to the the bars?
function drawRessources($user,$fmt,$showrealcontent=true) 
{ 
 global $gRes; 
 $reslist = array(); 
 foreach($gRes as $n=>$f) 
 { 
   $o = new EmptyObject();
   $o->cur = $user->$f; 
   $o->max = $user->{"max_".$f}; 
   $o->name = $n; 
   $o->img = "res_$f.gif"; 
   $o->imglink = false; 
   $reslist[] = $o; 
 } 
 if (1) 
 { 
   $o = new EmptyObject();
   $o->cur = $user->pop; 
   $o->max = $user->maxpop; 
   $o->name = "Bev&ouml;lkerung"; 
   $o->img = "pop-r1.png"; 
   $o->imglink = false; 
   $reslist[] = $o; 
 } 

 if (strpos($fmt,'JUSTIFY')!==false) 
   echo '<table cellpadding="2" cellspacing="0" border="0" class="resinfo" width="100%">'; 
 else 
 if (strpos($fmt,'LEFT')!==false) 
   echo '<table cellpadding="2" cellspacing="0" border="0" class="resinfo" align="left">'; 
 else 
 if (strpos($fmt,'RIGHT')!==false) 
   echo '<table cellpadding="2" cellspacing="0" border="0" class="resinfo" align="right">'; 
 else 
 if (strpos($fmt,'CENTER')!==false) 
   echo '<table cellpadding="2" cellspacing="0" border="0" class="resinfo" align="center">'; 
 else 
   echo '<table cellpadding="2" cellspacing="0" border="0" class="resinfo">'; 

 if (strpos($fmt,'HOR')!==false) echo '<tr class="hor">'; 
 $imax = sizeof($reslist)-1; 
 $i = 0; 
 foreach($reslist as $x){ 
   if($showrealcontent)$cur = $x->cur; 
   else $cur = $x->max*$i/$imax; 
   drawressource($x->name,$x->img,$cur,$x->max,$fmt); 
   ++$i; 
 } 
 if (strpos($fmt,'HOR')!==false) echo '</tr>'; 
 echo "</table>"; 
} // drawRessources()

function message2paper($message)
{
	?>
	<table class="zwpaper" cellpadding="0" cellspacing="0" border="0">
	<tr>
	  <td class="tl<?php echo mt_rand(1,3); ?>"><img src="x.gif" width="20" height="20"></td>
	  <td class="tc<?php echo mt_rand(1,3); ?>"></td>
	  <td class="tr<?php echo mt_rand(1,3); ?>"></td>
	</tr>
	<tr>
	  <td class="cl<?php echo mt_rand(1,3); ?>"></td>
	  <td class="cc<?php echo mt_rand(1,3); ?>"><?php echo nl2br($message); ?></td>
	  <td class="cr<?php echo mt_rand(1,3); ?>"></td>
	</tr>
	<tr>
	  <td class="bl<?php echo mt_rand(1,3); ?>"></td>
	  <td class="bc<?php echo mt_rand(1,3); ?>"></td>
	  <td class="br<?php echo mt_rand(1,3); ?>"><img src="x.gif" width="20" height="20"></td>
	</tr>
	</table>
	<?php
} // message2paper()

//puts out all firefields with radius(x>=xpos-r && x<=xpos-r and ...) around the given position
function FirePutOutObj ($o) {
	//is there a building?
	$id = sqlgetone("SELECT `user` FROM `building` WHERE `x`=$o->x AND `y`=$o->y LIMIT 1");
	if($id>0){
			//decrease user count of burning buildings
			sql("UPDATE `user` SET `buildings_on_fire`=`buildings_on_fire`-1 WHERE `id`=".intval($id)." LIMIT 1");
	}
	sql("DELETE FROM `fire` WHERE `x` =".$o->x." AND `y` = ".$o->y." LIMIT 1");
}

function FirePutOut($x,$y,$radius=0){
	$x = (int)$x;$y = (int)$y;$radius = (int)$radius;
	if ($radius == 0) {
		$o = sqlgetobject("SELECT * FROM `fire` WHERE `x` = ".$x." AND `y` = ".$y);
		FirePutOutObj($o);
	} else {
		$t = sqlgettable("SELECT * FROM `fire` WHERE `x`>=".($x-$radius)." AND `x`<=".($x+$radius).
											   " AND `y`>=".($y-$radius)." AND `y`<=".($y+$radius));
		foreach($t as $o) FirePutOutObj($o);
	}
}

//calculates the probability that the fire puts out
//ie. water or wells influence this
function FireGetFieldPutOutProb($x,$y){
		$x = (int)$x;$y = (int)$y;
		$prob = 0;

		//todo: at the moment terrainsegment* data will be ignored on terrain checks
		//todo: at the moment terrainsegment* data will be ignored on terrain checks
		$radius = kFireWaterLoeschRadius;
		$t = sqlgetone("SELECT COUNT(*) FROM `terrain` WHERE `x`>=$x-$radius AND `x`<=$x+$radius AND `y`>=$y-$radius AND `y`<=$y+$radius AND `type` in (".kFireWaterTerrainTypeSelect.")"); 
		$b = sqlgetone("SELECT SUM(`level`) FROM `building` WHERE `x`>=$x-$radius AND `x`<=$x+$radius AND `y`>=$y-$radius AND `y`<=$y+$radius AND `type` in (".kFireWaterBuildingTypeSelect.")"); 
		
		$prob += $t*15;
		$prob += $b*2;
		
		return max(25,min($prob,100));
}

//stets fire on a given field
function FireSetOn($x,$y){
		$x = (int)$x;$y = (int)$y;
		//echo "[FireSetOn($x,$y)]";
		$o = sqlgetone("SELECT 1 FROM `fire` WHERE `x`=$x AND `y`=$y LIMIT 1");
		//echo "[o=$o]";
		if(intval($o) == 0){
				//echo "[FireSetOn burn!!!]";
				//create fire
				$o = null;
				$o->created = time();
				$o->nextspread = time()+kFireSpreadTimeout;
				$o->nextdamage = time()+kFireDamageTimeout;
				$o->x = $x;
				$o->y = $y;
				$o->putoutprob = FireGetFieldPutOutProb($x,$y);
				sql("REPLACE INTO `fire` SET ".obj2sql($o));
				//is there a building?
				$user = sqlgetone("SELECT `user` FROM `building` WHERE `x`=$x AND `y`=$y LIMIT 1");
				if($user>0){
						//increase user count of burning buildings
						sql("UPDATE `user` SET `buildings_on_fire`=`buildings_on_fire`+1 WHERE `id`=".intval($user)." LIMIT 1");
						$fires = sqlgetone("SELECT `buildings_on_fire` FROM `user` WHERE `id`=".intval($user)." LIMIT 1");
						//first burning building, send message to user
						if($fires == 1){
								$text = "";
								$text .= "Das Gebäude and er Position ($x,$y) steht in Flammen. Es besteht die Gefahr, daß ";
								$text .= "sich das Feuer auf umliegende Felder ausbreitet. Diese Nachricht wird nur bei dem ";
								$text .= "ersten brennenden Gebäude geschickt.";
								sendMessage($user,0,"Eines Ihrer Gebäude brennt!",$text,0,false);
						}
				}
		}
}

//handle all stuff that need to be done after a field completly burned down
function FirePutOutBurnedDown($x,$y){
		global $gTerrainType;
		$x = (int)$x;
		$y = (int)$y;

		FirePutOut($x,$y);
		//is there a building?
		$b = sqlgetone("SELECT 1 FROM `building` WHERE `x`=$x AND `y`=$y LIMIT 1");
		//should the terrain transform into a burned down terrain? (only if there is not building)
		if (intval($b) == 0) {
				$t = cMap::StaticGetTerrainAtPos($x,$y);
				$t = $gTerrainType[$t]->fire_burnout_type;
				//echo "[FirePutOutBurnedDown($x,$y) to $t]\n";
				if($t > 0)setTerrain($x,$y,$t);
		}
}

require_once("jobs/job.php");

// execute jobs in userspace
if(NUMBER_OF_JOBS_TO_RUN_IN_USERSPACE > 0){
	Job::runJobs(NUMBER_OF_JOBS_TO_RUN_IN_USERSPACE);
}

?>
