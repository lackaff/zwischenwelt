<?php
require_once("lib.main.php");

if (strlen(kMapCodePass) <= 0) exit("specify kMapCodePass in defines.mysql.php");

echo time()." : ".date("H:i d-m-Y");
if (isset($f_save)) {
	if ($f_mypass == kMapCodePass) {
		echo "<h1>code updated</h1>";
		sql("REPLACE INTO `mapcode` SET `name` = 'Darian',`code` = '".addslashes($f_code)."',`css` = '".addslashes($f_css)."'");
	} else {
		echo "<h1>WRONG PASSWORD, ACCESS DENIED!</h1>";
	}
}

$gCustomMapCode = sqlgetobject("SELECT * FROM `mapcode` WHERE `name` = 'Darian'");

?>

<form method="post" action="?">
	password : <input type="text" name="mypass" value="<?=isset($f_mypass)?htmlspecialchars($f_mypass):""?>"><br>
	code : <textarea name="code" cols=60 rows=5><?=htmlspecialchars($gCustomMapCode->code)?></textarea><br>
	css : <textarea name="css" cols=60 rows=5><?=htmlspecialchars($gCustomMapCode->css)?></textarea><br>
	<input type="submit" name="save" value="speichern">
</form>


