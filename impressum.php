<?php
include("lib.php");
if(isset($f_sid))
{
	sql("DELETE FROM `session` WHERE `sid`='".addslashes($f_sid)."'");
	header("Location: index.php");
	exit;
}

include("header.php");
?>
<span id=team><h1>Impressum</h1></span>
<pre>
HIER DAS IMPRESSUM EINFUEGEN
</pre>
<?php include("footer.php"); ?>
