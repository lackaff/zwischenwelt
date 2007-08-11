<?php
require_once("lib.main.php");

if (strlen(kMapCodePass) <= 0) exit("specify kMapCodePass in defines.mysql.php");

if (isset($f_save)) {
	if ($f_pass == kMapCodePass) {
		echo "<h1>code updated</h1>";
		sql("REPLACE INTO `mapcode` SET `name` = 'Darian',`code` = '".addslashes($f_code)."',`css` = '".addslashes($f_css)."'");
	} else {
		echo "<h1>WRONG PASSWORD, ACCESS DENIED!</h1>";
	}
}

$gCustomMapCode = sqlgetone("SELECT * FROM `mapcode` WHERE `name` = 'Darian'");

?>

<form method="post" action="?">
	password : <input type="password" name="pass" value="<?=htmlspecialchars($f_pass)?>"><br>
	code : <textarea name="code" cols=60 rows=5><?=htmlspecialchars($gCustomMapCode->code)?></textarea><br>
	css : <textarea name="css" cols=60 rows=5><?=htmlspecialchars($gCustomMapCode->css)?></textarea><br>
	<input type="submit" name="save" value="speichern">
</form>


