<pre>
<?php

set_time_limit(0);
require_once("../lib.php");

$cs = "utf8";
$cl = "utf8_general_ci";

sql("ALTER DATABASE `".MYSQL_DB."` DEFAULT CHARACTER SET $cs COLLATE $cl");
echo "convert db ".MYSQL_DB."<br>\n";

$tables = sqlgettable("SHOW TABLE STATUS FROM ".MYSQL_DB);
foreach($tables as $t){
	if($t->Collation != $cl){
		sql("ALTER TABLE `$t->Name` DEFAULT CHARACTER SET $cs COLLATE $cl");
		echo "convert table $t->Name<br>\n";
	}

	$fields = sqlgettable("SHOW FULL FIELDS FROM `$t->Name`");

	foreach($fields as $f){
		if($f->Collation !== null && $f->Collation != $cl){
			if ( $f->Null == 'YES' ){
				$nullable = ' NULL ';
			} else {
				$nullable = ' NOT NULL';
			}
				
			// Does the field default to null, a string, or nothing?
			if ( $f->Default === null && $f->Null == 'YES'){
				$default = " DEFAULT NULL";
			} else if ( $f->Default != '' ){
				$default = " DEFAULT '".mysql_real_escape_string($f->Default)."'";
			} else {
				$default = " DEFAULT ''";
			}
			// Alter field collation:
			// ALTER TABLE `tab` CHANGE `fiel` `fiel` CHAR( 5 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL
			sql("ALTER TABLE `$t->Name` CHANGE `$f->Field` `$f->Field` $f->Type CHARACTER SET $cs COLLATE $cl $nullable $default\n");
			echo "convert table $t->Name field $f->Field<br>\n";
		}
	}
}

?>
</pre>
