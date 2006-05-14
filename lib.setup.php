<?php
//=====================================================
//=====================================================
define("CHECK_PATTERN_HTTP","^http://.+$");
//=====================================================
//=====================================================
function Check_FileExists($o){
	if(file_exists($o["name"]))
		if(is_readable($o["name"]))return null;
		else return "not readable";
	else return "file not found";
}

function Check_MysqlAccess($o){
	$link = mysql_connect($o["host"],$o["user"],$o["pass"]);
	if (!$link)return 'Could not connect: ' . mysql_error();
	mysql_close($link);
	return null;
}

function Check_MysqlExistsDB($o){
	$link = mysql_connect($o["host"],$o["user"],$o["pass"]);
	if (!$link)return 'Could not connect: ' . mysql_error();
	
	$db_selected = mysql_select_db($o["db"], $link);
	if (!$db_selected){
		mysql_close($link);
		return 'Can\'t use foo : ' . mysql_error();
	}
	mysql_close($link);
	return null;
}

function Check_MysqlValidQuery($o){
	$link = mysql_connect($o["host"],$o["user"],$o["pass"]);
	if (!$link)return 'Could not connect: ' . mysql_error();
	
	$db_selected = mysql_select_db($o["db"], $link);
	if (!$db_selected){
		mysql_close($link);
		return 'Can\'t use foo : ' . mysql_error();
	}
	$result = mysql_query($o["query"],$link);
	if (!$result){
		mysql_close($link);
		return 'Invalid query: ' . mysql_error();
	}
	mysql_close($link);
	return null;
}

function Check_PregMatch($o){
	if(preg_match($o["pattern"],$o["subject"])>0)return null;
	else return "'".$o["subject"]."' don't matches '".$o["pattern"]."'";
}

function Check_EregMatch($o){
	if(ereg($o["pattern"],$o["subject"]) === false)return "'".$o["subject"]."' don't matches '".$o["pattern"]."'";
	else return null;
}

function Check_EregNotMatch($o){
	if(ereg($o["pattern"],$o["subject"]) === false)null;
	else return "'".$o["subject"]."' matches '".$o["pattern"]."' but should not";
}

function Check_CmdExists($o){
	$last_line = exec($o["name"], $output, $retval);
	$output = implode(" ",$output);
	if($last_line === false)return "can't exec command [$retval]: $output";
	else if(empty($output))return "no output [$retval]: $output";
	else return null;
}

function Check_FunctionExists($o){
	if(!function_exists($o["name"]))return "function don't exists";
	else return null;
}

function Check_Writeable($o){
	if(file_exists($o["name"]))
		if(is_writable($o["name"]))return null;
		else return "not readable";
	else return "file not found";
}
//=====================================================
//=====================================================
function CheckStart(){
	echo "<span style='color:green'>ok</span> is good.<br>";
	echo "<span style='color:red'>failed</span> must not happen.<br>";
	echo "<span style='color:blue'>failed</span> is not so good, but could be ok.<br>";
	echo "<span style='color:black'>black lines</span> are required.<br>";
	echo "<span style='color:gray'>gray lines</span> are optional.<br>";
	echo "<hr>";
	echo "<table>";
}

function CheckStop(){
	echo "</table>";
}

function PerformCheck($check,$o){
	$checkfkt = "Check_$check";
	if(function_exists($checkfkt)){
		return $checkfkt($o);
	} else return "'$check' is not a valid check";
}

function CheckRequired($check,$desc,$o){
	$error = PerformCheck($check,$o);
	if(empty($error))
		$status = "<span style='color:green'>ok</span>";
	else
		$status = "<span style='color:red'>failed</span> [$error]";
	echo "<tr><td style='color:black'>$desc ... </td><td>$status</td></tr>";
}

function CheckOptional($check,$desc,$o){
	$error = PerformCheck($check,$o);
	if(empty($error))
		$status = "<span style='color:green'>ok</span>";
	else
		$status = "<span style='color:blue'>failed</span> [$error]";
	echo "<tr><td style='color:gray'>$desc ... </td><td>$status</td></tr>";
}

?>