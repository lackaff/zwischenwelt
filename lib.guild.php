<?php

require_once("lib.tabs.php");

define("kGuildRight_GuildAdmin"			,1);	//this user can do everything in the guild
define("kGuildRight_SiloGet"				,2);
define("kGuildRight_SiloGive"				,4);
define("kGuildRight_GuildCommander"		,8);
define("kGuildRight_SendGuildMsg"			,16);
define("kGuildRight_SetMsgOfTheDay"		,32);
define("kGuildRight_GuildBursar"			,64);

$gRight = array(
	array("right"=>kGuildRight_GuildAdmin,		"desc"=>"Das Mitglied ist Gildeadmin","gfx"=>"icon/guild-admin.png"),
	array("right"=>kGuildRight_SiloGet,			"desc"=>"Das Mitglied kann Rohstoffe aus dem Gildelager nehmen","gfx"=>"icon/guild-out.png"),
	array("right"=>kGuildRight_SiloGive,			"desc"=>"Das Mitglied kann Rohstoffe in die Gildelager einzahlen","gfx"=>"icon/guild-in.png"),
	array("right"=>kGuildRight_GuildCommander,	"desc"=>"Das Mitglied ist Gilde-Komandeur","gfx"=>"icon/guild-gc.png"),
	array("right"=>kGuildRight_SendGuildMsg,		"desc"=>"Das Mitglied darf Gildenachrichten verschicken","gfx"=>"icon/guild-send.png"),
	array("right"=>kGuildRight_SetMsgOfTheDay,	"desc"=>"Das Mitglied darf MessageOfTheDay Texte setzen","gfx"=>"icon/guild-message.png"),
	array("right"=>kGuildRight_GuildBursar,		"desc"=>"Mitglied ist Schatzmeister","gfx"=>"icon/guild-schatz.png"),
);

//checks if the user (userobject with guildstatus) has the given guildright
function HasGuildRight($user,$right){
	$id = sqlgetone("SELECT `id` FROM `guild` WHERE `founder`=".intval($user->id)." LIMIT 1");
	if(!empty($id))return true;
	else return (($user->guildstatus & kGuildRight_GuildAdmin) > 0) || (($user->guildstatus & $right) > 0);
}

//user leaves guild, only possible if he is not the founder, or let user leave guild (if $id is given)
function leaveGuild($id=0)
{
	sql("LOCK TABLES `user` WRITE, `sqlerror` WRITE, `phperror` WRITE,  `guild` WRITE,`guild_pref` WRITE");

	if($id==0)global $gUser;
	else if($id>0)$gUser=sqlgetobject("SELECT * FROM `user` WHERE `id`=".intval($id));
	
	$guild = $gUser->guild;
	if($guild > 0)
	{
		$founder = sqlgetone("SELECT `founder` FROM `guild` WHERE `id`=".$guild);
		if($founder != $gUser->id){
			sql("UPDATE `user` SET `guild`=0,`guildstatus`=0 WHERE `id`=".$gUser->id);
			setGPLimit($gUser->id,0);
		}
		if($guild==kGuild_Weltbank){
			sql("REPLACE INTO `guild_pref` SET `guild`=".kGuild_Weltbank.",`var`='schulden_".$gUser->id."',`value`='".max(0,-ceil($gUser->guildpoints/2))."'");
		}
	}
	sql("UNLOCK TABLES");
}

//creates guild if the user has enough money and the name is unique
function createGuild($name,$color)
{
	global $gUser;
	
	$lumber = 2000;
	$stone = 2000;
	$food = 2000;
	$metal = 2000;
	$runes = 0;
	
	//todo: da kann man vielleicht html code in die farbe machen um das layout zu kill
	//es wird halt noch nicht auf nur farbe gecheckt
	
	
	if(sqlgetone("SELECT `guild` FROM `user` WHERE `id`=".$gUser->id) > 0) {
		echo "erst aus Gilde austreten !";
		return false;
	}
	
	$success = false;
	
	sql("LOCK TABLES `newlog` WRITE,`phperror` WRITE,  `user` WRITE,`sqlerror` WRITE,  `guild` WRITE, `guild_pref` WRITE");
	
	$r = sql("SELECT `id` FROM `guild` WHERE `name`='$name'");
	if(mysql_num_rows($r) == 0)
	{
		$user = sqlgetobject("SELECT `lumber`,`stone`,`food`,`metal`,`runes` FROM `user` WHERE `id`=".$gUser->id);
		$ok = true;
		if ($user->lumber < $lumber) $ok = false;
		if ($user->stone < $stone) $ok = false;
		if ($user->food < $food) $ok = false;
		if ($user->metal < $metal) $ok = false;
		if ($user->runes < $runes) $ok = false;
	
		if($ok)
		{
			$text = "Neue Gilde gegründet: ".$name;
			//sql("INSERT INTO `newlog` SET `time`=".time().",`text`='$text',`user`=0");
		
			sql("UPDATE `user` SET 
				`lumber` = `lumber` - ".$lumber.",
				`stone` = `stone` - ".$stone.",
				`food` = `food` - ".$food.",
				`runes` = `runes` - ".$runes.",
				`metal` = `metal` - ".$metal."
				WHERE `id` = ".$gUser->id);
			
			sql("INSERT INTO `guild` SET `time`=".time().",`name`='$name',`founder`=".$gUser->id.",`color`='$color'");
			sql("UPDATE `user` SET `guild`=".mysql_insert_id()." WHERE `id`=".$gUser->id);
			
			$success = true;
		} else echo "nicht genug Ressourcen !";
	} else echo "Gilden-name existiert schon !";
	
	sql("UNLOCK TABLES");
	
	return $success;
}


//sends a join request, if someone sends double requests, the comment gehts edited
function requestJoinGuild($guild,$comment)
{
	global $gUser;
	$ok = false;
	
	sql("LOCK TABLES `user` READ, `phperror` WRITE,  `guild` READ,`sqlerror` WRITE, `guild_request` WRITE");
	
	$guildid = sqlgetone("SELECT `id` FROM `guild` WHERE `name`='$guild'");
	
	if($guildid > 0)
	{
		$oldcomment = sqlgetone("SELECT `comment` FROM `guild_request` WHERE `user`=".$gUser->id." AND `guild`=$guildid");
		if(!empty($oldcomment))$comment = $oldcomment."\n".$comment;
		sql("DELETE FROM `guild_request` WHERE `user`=".$gUser->id." AND `guild`=$guildid");
		sql("INSERT `guild_request` SET `user`=".$gUser->id.",`time`=".time().",`comment`='$comment',`guild`=$guildid");
	}
	sql("UNLOCK TABLES");
	return $ok;
}

//accept = true or deny = false the user waiting for the guild membership
function reactOnRequestGuild($user,$guild,$accept)
{
	$ok = false;
	$user = intval($user);
	
	sql("LOCK TABLES `phperror` WRITE,  `guild_pref` WRITE,`user` WRITE,`sqlerror` WRITE,  `guild` READ,`guild_request` WRITE");
	
	$guildid = sqlgetone("SELECT `guild` FROM `user` WHERE `id`=".$user);
	$guild = sqlgetone("SELECT `id` FROM `guild` WHERE `id`=".intval($guild));
	
	if($guild > 0){
		if($guildid > 0){
			//leave old guild
		}
		
		$request = sqlgetobject("SELECT * FROM `guild_request` WHERE `user`=".$user." AND `guild`=$guild");
		sql("DELETE FROM `guild_request` WHERE `user`=$user AND `guild`=$guild");
		if($accept){
			sql("UPDATE `user`, `guild` SET `user`.`guild`=$guild,`user`.`guildstatus`=`guild`.`stdstatus` WHERE `user`.`id`=$user AND `guild`.`id`=$guild");
			setGPLimit($user,getStdGPLimit($guild));
			sql("DELETE FROM `guild_request` WHERE `user`=$user");
		}
		
	}
	sql("UNLOCK TABLES");
	return $ok;
}

//gets or puts (<0) something from the guild ressis
function getFromGuild($user,$guild,$lumber,$stone,$food,$metal,$runes = 0)
{
	$debug = false;
	global $gRes,$gSqlShowQueries;
	//echo "getFromGuild($user,$guild,$lumber,$stone,$food,$metal,$runes)<br>";
	sql("LOCK TABLES `phperror` WRITE,  `user` WRITE,`guild` WRITE,`newlog` WRITE,`sqlerror` WRITE, `guild_pref` WRITE");
	$user = sqlgetobject("SELECT * FROM `user` WHERE `id`=".intval($user));
	$guild = sqlgetobject("SELECT * FROM `guild` WHERE `id`=".intval($guild));
	
	if($user && $guild && $user->guild == $guild->id) {
		if (!HasGuildRight($user,kGuildRight_SiloGet)) // user kann nicht abheben
			foreach($gRes as $n=>$f) if($$f > 0) $$f = 0;
		if (!HasGuildRight($user,kGuildRight_SiloGet)) // user kann nicht einzahlen
			foreach($gRes as $n=>$f) if($$f < 0) $$f = 0;
		
		// limit by available ressources and max capacity
		foreach($gRes as $n=>$f) {
			if($$f > 0)
				$$f = max(0,min(intval($$f),$guild->{$f},$user->{"max_$f"}-$user->{$f}));
			else if($$f < 0)
				$$f = -max(0,min(-intval($$f),$user->{$f},$guild->{"max_$f"}-$guild->{$f}));
		}
		
		if ($debug) {foreach($gRes as $n=>$f) echo $$f.","; echo "<br>";}
		
		$limit = getGPLimit($user->id);
		if ($limit > 0) {
			$cantake = max(0,$limit + $user->guildpoints);
			if ($debug) echo "limit:$limit,cantake=$cantake,guildpoints=".$user->guildpoints."<br>";
			foreach($gRes as $n=>$f) if($$f > 0) {
				$$f = min($cantake,$$f);
				$cantake -= $$f;
			}
		}
		
		foreach($gRes as $n=>$f) if($$f != 0) {
			sql("UPDATE `user` SET `$f`=`$f`+(".$$f.") , `guildpoints`=`guildpoints`-(".$$f.") WHERE `id`=".$user->id);
			sql("UPDATE `guild` SET `$f`=`$f`-(".$$f.") WHERE `id`=".$guild->id);
		}
	}
	sql("UNLOCK TABLES");
}


//guild as id or object
function getGuildCommander($guild=0){
	global $gUser;
	$t=array();
	if(!is_object($guild) && $guild!=0)$guild=sqlgetobject("SELECT * FROM `guild` WHERE `id`=".intval($guild));
	else if($guild==0){
		global $gGuild;
		$guild=$gGuild;
	}
	if(empty($guild))return array();
	$c= sqlgettable("SELECT * FROM `user` WHERE  `guild`=".$guild->id);
	foreach ($c as $o)
		if(HasGuildRight($o,kGuildRight_GuildCommander))$t[]=$o->id;
	return $t;
}


function ArmyChangeGC($army,$statenow) {
	/*
	if(!is_object($army)) $army = sqlgetobject("SELECT * FROM `army` WHERE `id` = ".intval($army));
	sql("DELETE FROM `armyaction` WHERE `army` = ".$army->id); // cArmy::ArmyCancelAction
	sql("DELETE FROM `waypoint` WHERE `army` = ".$army->id);
	*/
	
	GuildLogMe($army->x,$army->y,$army->user,0,
		sqlgetone("SELECT `guild` FROM `user` WHERE `id` = ".$army->user),
		0,($statenow?"GC aktiv":"GC aus"),
		("Die Armee ".$army->name." wurde dem Gildencommando ".($statenow?"unterstellt":"entzogen")));
}


function gForumEditA($user,$id){
	global $gGuild,$gUser;
	if($gUser->id!=$user) return FALSE;
	if(!$gGuild)$gGuild=sqlgetobject("SELECT * FROM `guild` WHERE `id`=".$gUser->guild);
	if(sqlgetone("SELECT `user` FROM `guild_forum` WHERE `id`=".intval($id))==$gUser->id || $gGuild->founder==$gUser->id)
		return TRUE;
	return FALSE;
}

function gForumDelA($user,$id){
	global $gGuild,$gUser;
	if($gUser->id!=$user) return FALSE;
	if(!$gGuild)$gGuild=sqlgetobject("SELECT * FROM `guild` WHERE `id`=".$gUser->guild);
	if(sqlgetone("SELECT `user` FROM `guild_forum` WHERE `id`=".intval($id))==$gUser->id || $gGuild->founder==$gUser->id)
		return TRUE;
	return FALSE;
}

function gForumEditC($user,$id){
	global $gGuild,$gUser;
	if($gUser->id!=$user) return FALSE;
	if(!$gGuild)$gGuild=sqlgetobject("SELECT * FROM `guild` WHERE `id`=".$gUser->guild);
	if(sqlgetone("SELECT `user` FROM `guild_forum_comment` WHERE `id`=".intval($id))==$gUser->id || $gGuild->founder==$gUser->id)
		return TRUE;
	return FALSE;
}

function gForumDelC($user,$id){
	global $gGuild,$gUser;
	if($gUser->id!=$user) return FALSE;
	if(!$gGuild)$gGuild=sqlgetobject("SELECT * FROM `guild` WHERE `id`=".$gUser->guild);
	if(sqlgetone("SELECT `user` FROM `guild_forum_comment` WHERE `id`=".intval($id))==$gUser->id || $gGuild->founder==$gUser->id)
		return TRUE;
	return FALSE;
}

/*
//right as int , user as id or object
function guildRight($right,$user=0){
	global $gUser,$gGuild;
	if(!is_object($user) && $user>0) $user = sqlgetobject("SELECT * FROM `user` WHERE `id`=".intval($user));
	if($user==0 || !$user){
		$id=$gUser->id;
		$user = $gUser;
	}
	if(empty($gGuild))$gGuild=sqlgetobject("SELECT * FROM `guild` WHERE `id`=".intval($user->guild));
	if((intval($user->guildstatus)%$right)==0 || $user->id==$gGuild->founder)return TRUE;
	else return FALSE;
}
*/

/*
//array of int-rights as arg
function setGuildRights($rights,$id){
	global $gUser;
	if(!guildRight(kGuildAdmin,$gUser)) return FALSE;
	$status = 0;
	foreach ($rights as $r)
		$status= $status | $r;
	sql("UPDATE `user` SET `guildstatus`=`guildstatus` | $r WHERE `id`=".$id);
	return TRUE;
}
*/

/*
//array of int-rights as arg $user as id or object
function removeGuildRights($rights,$user){
	global $gUser;
	if(guildRight(kGuildAdmin,$gUser)) return FALSE;
	if(!is_object($user))$user=sqlgetobject("SELECT * FROM `user` WHERE `id`=".intval($user));
	if(!is_object($user))return FALSE;
	foreach($rights as $r){
		$user->guildstatus = $user->guildstatus & ~$r;
		//if($user->guildstatus%$r==0)$user->guildstatus/=$r;
	}
	sql("UPDATE `user` SET `guildstatus`=".$user->guildstatus." WHERE `id`=".$user->id);
	return TRUE;
}
*/

/*
//int-right as arg, userid or object
function addGuildRight($right,$user){
	global $gUser;
	if(guildRight(kGuildAdmin,$gUser)) return FALSE;
	if(!is_object($user))$user=sqlgetobject("SELECT * FROM `user` WHERE `id`=".intval($user));
	if(!is_object($user))return FALSE;
	//if($user->guildstatus!=0 && $user->guildstatus%$right!=0)
	//	sql("UPDATE `user` SET `guildstatus`=`guildstatus`*$r WHERE `id`=".$user->id);
	//else if($user->guildstatus==0)
		sql("UPDATE `user` SET `guildstatus`=`guildstatus` | $r WHERE `id`=".$user->id);
	//else
		return TRUE;
}
*/

/*
//int-right as arg, userid or object
function delGuildRight($right,$user){
	if(guildRight(kGuildAdmin,$gUser)) return FALSE;
	if(!is_object($user))$user=sqlgetobject("SELECT * FROM `user` WHERE `id`=".intval($user));
	if(!is_object($user))return FALSE;
		if($user->guildstatus!=0 && $user->guildstatus%$right!=0)
		sql("UPDATE `user` SET `guildstatus`=`guildstatus`/$r WHERE `id`=".$user->id);
}
*/

function getGPLimit($id){
	$id = intval($id);
	$guildid = sqlgetone("SELECT `guild` FROM `user` WHERE `id`=$id");
	if($guildid == 0)return 0;
	
	$r=sqlgetone("SELECT `value` FROM `guild_pref` WHERE `var`='limit_$id' AND `guild`=$guildid");

	if(mysql_affected_rows() == 0){
		if($guildid>0) sql("INSERT INTO `guild_pref` SET `var`='limit_$id',`value`='0',`guild`=$guildid");
		return 0;
	}
	return $r;
}


function setGPLimit($id,$limit){
	$id = intval($id);
	$limit=abs(intval($limit));
	
	$guildid = sqlgetone("SELECT `guild` FROM `user` WHERE `id`=$id");
	if($guildid == 0)return;

	$oldlimit = sqlgetone("SELECT `value` FROM `guild_pref` WHERE `var`='limit_$id' AND `guild`=$guildid");
	if(mysql_affected_rows() > 0)sql("UPDATE `guild_pref` SET `value`='$limit' WHERE `var`='limit_$id' AND `guild`=".$guildid);
	else sql("INSERT INTO `guild_pref` SET `value`='$limit', `var`='limit_$id', `guild`=".$guildid);
}

function getStdGPLimit($id){
	$r=sqlgetobject("SELECT `value` FROM `guild_pref` WHERE `var`='stdlimit' AND `guild`=".intval($id));
	if(mysql_affected_rows() == 0){
		sql("INSERT INTO `guild_pref` SET `var`='stdlimit',`value`='0',`guild`=".intval($id));
		return 0;
	}
	return intval($r->value);
}

function getTakeAllLimit($id){
	$r=sqlgetobject("SELECT `value` FROM `guild_pref` WHERE `var`='takealllimit' AND `guild`=".intval($id));
	if(mysql_affected_rows() == 0){
		sql("INSERT INTO `guild_pref` SET `var`='takealllimit',`value`='0',`guild`=".intval($id));
		return 0;
	}
	return intval($r->value);
}


function setTakeAllLimit($id,$limit){
	$limit=abs(intval($limit));
	$id = intval($id);
	
	$oldlimit = sqlgetone("SELECT `value` FROM `guild_pref` WHERE `var`='takealllimit' AND `guild`=$id");
	if(mysql_affected_rows() > 0)sql("UPDATE `guild_pref` SET `value`='$limit' WHERE `var`='takealllimit' AND `guild`=".$id);
	else sql("INSERT INTO `guild_pref` SET `value`='$limit', `var`='takealllimit', `guild`=".$id);
}

function setStdGPLimit($id,$limit){
	$limit=abs(intval($limit));
	$id = intval($id);
	
	$oldlimit = sqlgetone("SELECT `value` FROM `guild_pref` WHERE `var`='stdlimit' AND `guild`=$id");
	if(mysql_affected_rows() > 0)sql("UPDATE `guild_pref` SET `value`='$limit' WHERE `var`='stdlimit' AND `guild`=".$id);
	else sql("INSERT INTO `guild_pref` SET `value`='$limit', `var`='stdlimit', `guild`=".$id);
}


function renderGuildTabbar($active=false){
	$tabs = array(
		array("Allgemein","",query("guild.php?sid=?")),
		array("Mitglieder","",query("guild_members.php?sid=?")),
		array("Forum","",query("guild_forum.php?sid=?")),
		array("Log","",query("guild_log.php?sid=?")),
		array("Verwalten","",query("guild_admin.php?sid=?")),
	);
	echo GenerateTabs("guildtabs",$tabs,"",false,$active);
	echo "<div class=\"tabpane\">";
}

?>
