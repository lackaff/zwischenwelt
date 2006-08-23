<?php

include("lib.php");
include("constants.php");
Lock();
if (!$gUser->admin)
	exit(error("access denied"));

if (!isset($f_uid))
	exit(error("User ID fehlt"));

$uid = intval($f_uid);
if($uid==kGuild_Weltbank_Founder){ 
	// TODO : FIND OUT WHAT THIS IS FOR ?? 
	// -- this is so that the admin acc doesn't appear gray in the highscore if nobody logs in directly but only via symbiosis
	sql("UPDATE `user` SET `lastlogin`=".time()." WHERE `id`=$uid");
}
sql("UPDATE `session` SET `usegfx`=0, `userid`='$uid', `lastuse`=".time()." WHERE `sid`='$gSID' AND userid=".$gUser->id);
Redirect("main.php?sid=".$gSID);
?>
