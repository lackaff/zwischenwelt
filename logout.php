<?php
include("lib.php");
sql("UPDATE `session` SET `sid`='logout-".addslashes($gSID)."' WHERE `sid` = '".addslashes($gSID)."'");
Redirect("index.php");
?>