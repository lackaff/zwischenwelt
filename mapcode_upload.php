<?php
require_once("lib.main.php");

if (strlen(kMapCodePass) <= 0) exit("specify kMapCodePass in defines.mysql.php");

$gCustomMapCode = sqlgetone("SELECT `code` FROM `mapcode` WHERE `name` = 'Darian'");

if ($f_pass == kMapCodePass && isset($f_save)) {
	sql("REPLACE INTO `mapcode` SET `name` = 'Darian',`code` = '".addslashes($f_code)."'");
}

?>

<form method="post" action="?">
	password : <input type="password" name="pass" value=""><br>
	code : <textarea name="code" cols=60 rows=5><?=htmlspecialchars($gCustomMapCode)?></textarea><br>
	<input type="submit" name="save" value="speichern">
</form>


