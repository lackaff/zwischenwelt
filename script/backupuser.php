<?php

include("../lib.main.php");

function TableToSqlQuery($table,$tablename){
	if(empty($table) || sizeof($table)==0)return "";
	$tablename = mysql_escape_string ($tablename);
	$s = "";
	foreach($table as $x){
		$s .= "INSERT INTO `$tablename` WHERE ";
		$l = array();
		foreach($x as $n=>$v){
			$n = mysql_escape_string($n);
			$v = mysql_escape_string($v);
			$l[] = "`$n`='$v'";
		}
		$s .= implode(", ",$l).";\n";
	}
	return $s;
}

?>
<html>
<form method=post action=?>
Enter User ID: <input type=text name=userid value="<?=$f_userid?>">
<input type=submit name=ok>
</form>
<?php

if(!empty($f_userid)){
	$userid = (int)$f_userid;
	$backup = "";
	
	//building
	$l = sqlgettable("SELECT * FROM `building` WHERE `user`=$userid");
	$backup .= TableToSqlQuery($l,"building")."\n\n";
	//buildingparam
	foreach($l as $x){
		$ll = sqlgettable("SELECT * FROM `buildingparam` WHERE `building`=$x->id");
		if(sizeof($ll)>0)$backup .= TableToSqlQuery($ll,"buildingparam")."\n";
	}
	//unit in building
	foreach($l as $x){
		$ll = sqlgettable("SELECT * FROM `unit` WHERE `building`=$x->id");
		if(sizeof($ll)>0)$backup .= TableToSqlQuery($ll,"unit")."\n";
	}
	//army
	$l = sqlgettable("SELECT * FROM `army` WHERE `user`=$userid");
	$backup .= TableToSqlQuery($l,"army")."\n\n";
	//unit in army
	foreach($l as $x){
		$ll = sqlgettable("SELECT * FROM `unit` WHERE `army`=$x->id");
		if(sizeof($ll)>0)$backup .= TableToSqlQuery($ll,"unit")."\n";
	}
	//construction
	$l = sqlgettable("SELECT * FROM `construction` WHERE `user`=$userid");
	$backup .= TableToSqlQuery($l,"construction")."\n\n";
	//mapmark
	$l = sqlgettable("SELECT * FROM `mapmark` WHERE `user`=$userid");
	$backup .= TableToSqlQuery($l,"mapmark")."\n\n";
	//technology
	$l = sqlgettable("SELECT * FROM `technology` WHERE `user`=$userid");
	$backup .= TableToSqlQuery($l,"technology")."\n\n";
	//userprofil
	$l = sqlgettable("SELECT * FROM `userprofil` WHERE `id`=$userid");
	$backup .= TableToSqlQuery($l,"userprofil")."\n\n";
	//userrecord
	$l = sqlgettable("SELECT * FROM `userrecord` WHERE `userid`=$userid");
	$backup .= TableToSqlQuery($l,"userrecord")."\n\n";
	//uservalue
	$l = sqlgettable("SELECT * FROM `uservalue` WHERE `user`=$userid");
	$backup .= TableToSqlQuery($l,"uservalue")."\n\n";
	
	
	
	
	
	
} else $backup = "";

?>
<textarea style="width:100%;height:80%"><?=$backup?></textarea>
</html>