<?php
include("lib.php");

// starts a new session
function CreateSession ($userid)
{
	global $gSID,$_SERVER;
	$s = substr(base64_encode(crypt($_SERVER["REMOTE_ADDR"].date("dmYHis").rand())),0,32);
	$o = sqlgetobject("SELECT * FROM `session` WHERE `sid` = '".addslashes($s)."'");
	if ($o) return false; // session id already exists
	$o = false;
	$o->sid = $s;
	$o->ip = $_SERVER["REMOTE_ADDR"];
	$o->userid = $userid;
	$o->lastuse = time();
	$o->agent = $_SERVER["HTTP_USER_AGENT"];
	sql("INSERT INTO `session` SET ".obj2sql($o));
	$gSID = $s;
	return true;
}

//checks login infos, returns null on error and user obj on success (update session table)
function Login($user,$pass)
{
	global $gGlobal;
	if (!isset($user) || !isset($pass)) return 0;

	
	$o = sqlgetobject("SELECT * FROM `user` WHERE
		`pass` = PASSWORD('".addslashes($pass)."') AND
		`name` = '".addslashes($user)."' LIMIT 1");
	if (!$o) return 0;
	
	if(intval(sqlgetone("SELECT `value` FROM `global` WHERE `name`='liveupdate'"))==1 && $o->admin!=1) return 0;
	
	sql("UPDATE `user` SET logins = logins + 1, lastlogin = ".time()." WHERE id = ".$o->id);
	return CreateSession($o->id);
}

/*
if (isset($f_mycliplog)) {
	$cliplog = false;
	$cliplog->time = time();
	$cliplog->user = $f_name;
	$cliplog->clip = $f_mycliplog;
	if (trim($cliplog->clip) != "" && trim($cliplog->clip) != "null") sql("INSERT INTO `cliplog` SET ".obj2sql($cliplog));
}*/

if (Login($f_name,$f_pass))
{
	Redirect(SessionLink("main.php?fc=1"));
} else {
	Redirect("index.php");
}
?>
