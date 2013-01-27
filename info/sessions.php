<?php
require_once("../lib.main.php");
Lock();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 transitional//EN"
   "http://www.w3.org/TR/html4/transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<link href="http://fonts.googleapis.com/css?family=Bree+Serif" rel="stylesheet" type="text/css">
<link href="http://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="<?=BASEURL?>css/zwstyle_new_temp.css">
<title>Zwischenwelt - Diplomacy</title>
</head>
<body>
<?php 
include(BASEPATH."/menu.php");

$s = sqlgettable("SELECT * FROM `session` WHERE `userid`=".intval($gUser->id)." ORDER BY `id` DESC");

?>
<table border=1>
<tr>
	<th>ID</th><th>IP</th><th>LastUse</th><th>Browser</th>
</tr>
<?php
foreach($s as $x){
?>
<tr>
	<td><?=$x->id?></td><td><?=$x->ip?></td><td><?=date("r",$x->lastuse)?></td><td><?=$x->agent?></td>
</tr>
<?php } ?>
</table>

</body>
</html>