<?php
require_once("lib.main.php");
Lock();
if (!$gUser->admin) exit("admin only");

// see config/defines.php for kCheckDBMaxVersion

$gDoRegenTypeCache = false; /// usually set to true by upgrade functions

/*
This file is for upgrading the db for newer zw versions,
the current version of the database is always found in the table named "global", in the "value" of the column, where "name"="dbversion"
*/

function SetDoRegenTypeCache () {
	global $gDoRegenTypeCache; $gDoRegenTypeCache = true;
}

function GetUpgradeFuncName ($vfrom) { return "UpgradeDB_".intval($vfrom)."_to_".(intval($vfrom)+1); }
function GetUpgradeLink ($vfrom) {
	return "<a href='".Query("?sid=?&upgradefrom=".intval($vfrom))."'>".GetUpgradeFuncName($vfrom)."</a>";
}

/// from here on are the actual upgrade functions
	// warning ! Upgrade Functions can not use global vars or constants, as those might change over time

	function UpgradeDB_0_to_1 () {
		RequireExactDBVersion(0);
		
		sql("
			CREATE TABLE IF NOT EXISTS `dbversion` (
			`version` INT NOT NULL
			) ENGINE = MYISAM ;
		");
		sql("INSERT INTO `dbversion` VALUES (0)");
		
		UpgradeDBVersion(1);
		SetDoRegenTypeCache();
	}

// command line like user interface

	if (isset($_REQUEST["upgradefrom"]) && !isset($_REQUEST["sure"])) {
		$vfrom = $_REQUEST["upgradefrom"];
		$func = GetUpgradeFuncName(intval($_REQUEST["upgradefrom"]));
		echo "About to Execute $func<br>";
		echo "Are you sure ? ";
		echo " <a href='".Query("?sid=?&sure=1&upgradefrom=".intval($vfrom))."'>YES</a>";
		echo " <a href='".Query("?sid=?")."'>NO</a>";
		echo "<hr>";
	}

	if (isset($_REQUEST["upgradefrom"]) && isset($_REQUEST["sure"])) {
		$func = GetUpgradeFuncName(intval($_REQUEST["upgradefrom"]));
		echo "Executing $func<br>";
		echo "<hr>";
		$func();
		echo "<hr>";
	}
	
	if ($gDoRegenTypeCache && GetCurDBVersion() >= kCheckDBMaxVersion) {
		echo "RegenTypeCache";
		echo "<hr>";
		RegenTypeCache();
		require(kTypeCacheFile);
		echo "<hr>";
	}

	$curv = GetCurDBVersion();
	echo "Current Database Version : $curv<br>";
	echo "Maximum Database Version : ".kCheckDBMaxVersion."<br>";
	if ($curv < kCheckDBMaxVersion) {
		echo "Suggested Upgrade : ".GetUpgradeLink($curv)."<br>";
	} else { 
		echo "Already up to date, no change needed<br>";
	}
	echo "<br>";
	//echo "All Upgrades (don't click those unless you know EXACTLY what you are doing...):<br>";
	//for ($i=0;$i<kCheckDBMaxVersion;++$i) echo GetUpgradeLink($i)."<br>";
	echo "<hr>";
	
	echo "<a href='".BASEURL."'>back to the Game</a>";
?>