<?php
require_once("../lib.main.php");
require_once("../lib.guild.php");
require_once("../lib.technology.php");
require_once("../lib.map.php");
Lock();
profile_page_start("viewtechgraph.php");

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 transitional//EN"
   "http://www.w3.org/TR/html4/transitional.dtd">
<html>
<head>
<link rel="stylesheet" type="text/css" href="<?=GetZWStylePath()?>">
<title>Zwischenwelt - Forschungsbaum</title>

</head>
<body>
<?php
include("../menu.php");
?>

<div style="overflow:auto;max-width:100%;border:solid black 1px;">
<img src="../tmp/tech.png">
</div>

</body>
</html>
<?php profile_page_end(); ?>
