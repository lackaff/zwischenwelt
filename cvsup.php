<?php
require_once("lib.main.php");
Lock();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 transitional//EN"
   "http://www.w3.org/TR/html4/transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<link rel="stylesheet" type="text/css" href="<?=GetZWStylePath()?>">
<title>Zwischenwelt - info</title>
</head>
<body>
<?php
if ($gUser->admin){
	// cvs up :
	?><pre><?php passthru("sudo -u hagish /usr/bin/svn up /home/hagish/zw")?></pre><?php
}
?>
