<?php
require_once("../lib.main.php");
Lock();
$t = time();

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 transitional//EN"
   "http://www.w3.org/TR/html4/transitional.dtd">
<html>
<head>
<link rel="stylesheet" type="text/css" href="<?=GetZWStylePath()?>">
<title>Zwischenwelt - Statistiken</title>

</head>
<body>

<?php
include("../menu.php");
include("../stats/header.php");
ImgBorderStart();
?>

<table border="0">
	<tr>
		<th colspan="3">Armeen</th>
	</tr>
	<tr>
		<th>Name</th>
		<th>Besitzer</th>
		<th>Frags</th>
	</tr>
	<?php
		$t = sqlgettable("SELECT * FROM `army` WHERE `frags` > 0 ORDER BY `frags` DESC");
		foreach($t as $u)
			echo "<tr><td>".$u->name."</td><td>".sqlgetone("SELECT `name` FROM `user` WHERE `id`=".$u->user)."</td><td align=\"right\">".kplaintrenner(floor($u->frags))."</td></tr>";
	?>
</table>

<?php 
ImgBorderEnd();
include("../stats/footer.php");
?>

</body>
</html>
