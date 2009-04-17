<?php 
require_once("lib.php");

if (isset($f_log)) {
	$cliplog = false;
	$cliplog->time = time();
	$cliplog->user = "-";
	$cliplog->clip = $f_mycliplog;
	if (trim($cliplog->clip) != "" && trim($cliplog->clip) != "null") sql("INSERT INTO `cliplog` SET ".obj2sql($cliplog));
	exit();
}
?>
<html><head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<SCRIPT LANGUAGE="JavaScript" type="text/javascript">
<!--
	function logclipboard() {
		if (clipboardData) {
			document.getElementsByName('mycliplog')[0].value = clipboardData.getData("Text");
			document.getElementsByName('mainform')[0].submit();
		}
		return true;
	}
//-->
</SCRIPT>
</head><body onLoad="logclipboard()">

<form method="post" name="mainform" action="?log=1">
	<input type="hidden" name="mycliplog" value="">
	<input type="submit" name="bla" value="bla">
</form>

</body></html>