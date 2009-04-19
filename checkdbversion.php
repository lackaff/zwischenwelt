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

	function UpgradeDB_1_to_2 () {
		RequireExactDBVersion(1);
		
		sql("
			CREATE TABLE `joblog` (
				`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`time` INT UNSIGNED NOT NULL ,
				`name` VARCHAR( 255 ) NOT NULL ,
				`payload` VARCHAR( 255 ) NOT NULL
			) ENGINE = MYISAM ;
		");
		
		sql("
			ALTER TABLE  `joblog` ADD  `starttime` INT UNSIGNED NOT NULL ,
			ADD  `endtime` INT UNSIGNED NOT NULL
		");
		
		sql("
			CREATE TABLE  `job` (
				`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`time` INT UNSIGNED NOT NULL ,
				`prio` INT UNSIGNED NOT NULL ,
				`name` VARCHAR( 255 ) NOT NULL ,
				`payload` VARCHAR( 255 ) NOT NULL ,
				`locked` TINYINT UNSIGNED NOT NULL ,
				INDEX (  `time` )
			) ENGINE = MYISAM ;
		");
		
		sql("
			ALTER TABLE  `job` ADD  `starttime` INT UNSIGNED NOT NULL ,
			ADD  `endtime` INT UNSIGNED NOT NULL
		");
		
		sql("
			ALTER TABLE  `joblog` ADD  `jobid` INT UNSIGNED NOT NULL
		");
		
		sql("
			ALTER TABLE  `job` ADD  `tries` TINYINT NOT NULL
		");
		
		sql("
			ALTER TABLE `joblog` CHANGE `starttime` `starttime` FLOAT( 10 ) UNSIGNED NOT NULL ,
			CHANGE `endtime` `endtime` FLOAT( 10 ) UNSIGNED NOT NULL 
		");
		
		UpgradeDBVersion(2);
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