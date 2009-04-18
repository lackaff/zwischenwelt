<?

require_once("defines.php");
srand(microtime_float());

/*
// timing/
sscanf($f_aborttime,"%u:%u %u-%u-%u",$h,$m,$day,$month,$year);
$time = mktime($h,$m,0,$month,$day,$year);
echo "$h,$m,$day,$month,$year ".date("H:i d-m-Y",$time)."<br>";

// notes
rob_ob_start();
$outbuf = rob_ob_end();
echo $outbuf;

bool in_array ( mixed needle, array haystack [, bool strict])
array array_map ( mixed callback, array arr1 [, array arr2...])
	function cube($n) { return $n*$n*$n; }
array array_merge ( array array1, array array2 [, array ...])
void uksort	( array array, function cmp_function)	// by key
void usort	( array array, string cmp_function)		// by value
void uasort	( array array, function cmp_function)	// associative sort
	function cmp ($a, $b) {
		if ($a == $b) return 0;
		return ($a < $b) ? -1 : 1; // ascending
	}s
array array_filter ( array input [, mixed callback])
	function even($var) { return ($var % 2 == 0); }
int array_walk ( array array, string func [, mixed userdata])
	function test_alter (&$item1, $key, $prefix) {
		$item1 = "$prefix: $item1";
	}
mixed array_sum ( array array)

array explode ( string separator, string string [, int limit])
string implode ( string glue, array pieces)
array split ( string pattern, string string [, int limit])

int strpos ( string haystack, string needle [, int offset]) // 0=beginning , false=not_found
int strrpos ( string haystack, char needle) // only one char needle !
string substr ( string string, int start [, int length])
mixed str_replace ( mixed search, mixed replace, mixed subject)
int ord ( string string) <|> string chr ( int ascii)
string htmlentities ( string string [, int quote_style [, string charset]])
int substr_count ( string haystack, string needle)
int strspn ( string str, string mask) , also strcspn
int strncmp ( string str1, string str2, int len) , also strcmp == 0 if equal

int ereg ( string pattern, string string [, array regs])
// if (eregi($pattern,$text,$r)) $r[0] ist der komplette match, nicht der komplette text
string ereg_replace ( string pattern, string replacement, string string)

header("Content-type: image/jpeg");
//header("Content-Disposition: attachment; filename=downloaded.jpg");
*/


//##########################################################################################
//####################### error reporting ##################################################
//##########################################################################################


// we will do our own error handling

function my_array_combine($keys,$values)
{
	$l = array();
	$size = min(sizeof($keys),sizeof($values));
	for($i = 0;$i < $size; ++$i)$l[$keys[$i]] = $values[$i];
	return $l;
}

$gPHP_Errors = array();
// user defined error handling function
function userErrorHandler($errno, $errmsg, $filename, $linenum, $vars)
{
   // timestamp for the error entry
   $dt = date("Y-m-d H:i:s (T)");

   // define an assoc array of error string
   // in reality the only entries we should
   // consider are E_WARNING, E_NOTICE, E_USER_ERROR,
   // E_USER_WARNING and E_USER_NOTICE
   $errortype = array (
               E_ERROR          => "Error",
               E_WARNING        => "Warning",
               E_PARSE          => "Parsing Error",
               E_NOTICE          => "Notice",
               E_CORE_ERROR      => "Core Error",
               E_CORE_WARNING    => "Core Warning",
               E_COMPILE_ERROR  => "Compile Error",
               E_COMPILE_WARNING => "Compile Warning",
               E_USER_ERROR      => "User Error",
               E_USER_WARNING    => "User Warning",
               E_USER_NOTICE    => "User Notice"
               );
	if (defined("E_STRICT")) 
		$errortype[E_STRICT] = "Runtime Notice";
   // set of errors for which a var trace will be saved
   $user_errors = array(E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE);
  
  /*
   $err = "<errorentry>\n";
   $err .= "\t<datetime>" . $dt . "</datetime>\n";
   $err .= "\t<errornum>" . $errno . "</errornum>\n";
   $err .= "\t<errortype>" . $errortype[$errno] . "</errortype>\n";
   $err .= "\t<errormsg>" . $errmsg . "</errormsg>\n";
   $err .= "\t<scriptname>" . $filename . "</scriptname>\n";
   $err .= "\t<scriptlinenum>" . $linenum . "</scriptlinenum>\n";

   if (in_array($errno, $user_errors)) {
       $err .= "\t<vartrace>" . wddx_serialize_value($vars, "Variables") . "</vartrace>\n";
   }
   $err .= "</errorentry>\n\n";
  
   // for testing
   //echo $err;

   // save to the error log, and e-mail me if there is a critical user error
   error_log($err, 3, PHP_ERROR_LOG);
   //error_log($err, 1, PHP_ERROR_LOG_MAIL);
   */
   $err = null;
   $err->datetime = $dt;
   $err->errornum = $errno;
   $err->errortype = $errortype[$errno];
   $err->errormsg = $errmsg;
   $err->scriptname = $filename;
   $err->scriptlinenum = $linenum;
   
   $err->code = file($filename);
   $err->code = shorttrace(2).":<br>\n".$err->code[$linenum-1].$err->code[$linenum].$err->code[$linenum+1];
   sql("INSERT INTO `phperror` SET ".obj2sql($err));
   global $gPHP_Errors;
   $gPHP_Errors[] = $err;
}

// we will do our own error handling
//error_reporting(0);
//$gOldErrorHandler = set_error_handler("userErrorHandler");
//##########################################################################################
//##########################################################################################
//##########################################################################################

function get_readable_permission ($path) {
	$perms = fileperms($path);
	$info = "";
	// Owner
	$info .= (($perms & 0x0100) ? 'r' : '-');
	$info .= (($perms & 0x0080) ? 'w' : '-');
	$info .= (($perms & 0x0040) ?
				(($perms & 0x0800) ? 's' : 'x' ) :
				(($perms & 0x0800) ? 'S' : '-'));

	// Group
	$info .= (($perms & 0x0020) ? 'r' : '-');
	$info .= (($perms & 0x0010) ? 'w' : '-');
	$info .= (($perms & 0x0008) ?
				(($perms & 0x0400) ? 's' : 'x' ) :
				(($perms & 0x0400) ? 'S' : '-'));

	// World
	$info .= (($perms & 0x0004) ? 'r' : '-');
	$info .= (($perms & 0x0002) ? 'w' : '-');
	$info .= (($perms & 0x0001) ?
				(($perms & 0x0200) ? 't' : 'x' ) :
				(($perms & 0x0200) ? 'T' : '-'));
	return $info;
}


// keeps even numeric keys
function &array_merge2 ($arr1,&$arr2,$dump=TRUE) {
	foreach ($arr2 as $k => $v)
		$arr1[$k] = $dump?$v:(($v==0 || $v=="")?$arr1[$k]:$v);
	return $arr1;
}
function &array_add ($arr1,$arr2) {
	foreach ($arr2 as $key=>$val)
		$arr1[$key] += $val;
	return $arr1;
}
function intarray ($var=null) {
	if (!$var) return array();
	if (!is_array($var)) return array(0=>intval($var));
	array_walk($var,"walkint");
	return $var;
}

// returns an array with key=name_of_constant value=value_of_constant
function GetConstants ($prefix="") {
	if ($prefix == "") return get_defined_constants();
	$res = array();
	$prefixlen = strlen($prefix);
	$arr = get_defined_constants();
	foreach ($arr as $k=>$v) if (strlen($k) >= $prefixlen && strncmp($k,$prefix,$prefixlen) == 0) $res[$k] = $v;
	return $res;
}
function PrintPHPConstantsToJS ($prefix) {
	$arr = GetConstants($prefix);
	foreach ($arr as $k => $v) {
		if (is_numeric($v))	
				echo "$k = $v;\n";
		else	echo "$k = \"$v\";\n";
	}
}

function microtime_float()
{
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}
	
function array_glue($a1,$a2)
{
	$l = array();
	$size = min(sizeof($a1),sizeof($a2));
	for($i = 0;$i < $size; ++$i)$l[] = $a1[$i].$a2[$i];
	return $l;
}

function GetFraction ($cur,$max) { return ($cur <= 0.0 || $max == 0)?0.0:(($cur >= $max)?1.0:($cur / $max));}
function GradientRYG ($factor,$brightness=1.0) { // red-yellow-green
	$dist = abs($factor - 0.5)*2.0;
	$factor = 0.5 + 0.5*(($factor>0.5)?1.0:(-1.0))*$dist*$dist;
	return sprintf("#%02x%02x00",255.0*$brightness*min(1.0,2.0-$factor*2.0),255.0*$brightness*min(1.0,$factor*2.0));
}
function GradientCB ($factor,$brightness=1.0) { // cyan blue
	$dist = abs($factor - 0.5)*2.0;
	$factor = 0.5 + 0.5*(($factor>0.5)?1.0:(-1.0))*$dist*$dist;
	return sprintf("#00%02xFF",255.0*$brightness*(1.0-0.5*$factor));
}

function RegenQuery ($list) {
	$params = array();
	foreach($list as $var)
		if (isset($GLOBALS["f_".$var])) $params[] = $var."=".$GLOBALS["f_".$var];
	if (count($params) == 0) return "";
	return "?".implode("&",$params);
}

function vardump($var)
{echo "<pre>";var_dump($var);echo "</pre>";}
function vardump2($var)
{
	// vardump2(array(3=>"doener",8=>"wein",9=>10,10=>11.05,55=>array(1=>2,5,6,7=>array("bla"=>"test",2,4,5))));
	if (!is_object($var) && !is_array($var))
	{
		echo htmlspecialchars($var);
		return;
	}
	echo "<table border=1 cellspacing=0 cellpadding=0>";
	if (is_object($var)) $var = object2array($var);
	foreach ($var as $name=>$val)
	{
		echo "<tr><td>".$name."</td><td>".gettype($val)."</td><td>";
		vardump2($val);
		echo "</td></tr>";
	}
	echo "</table>";
}

function Redirect ($url)
{
	@header("Location: ".$url);
	?>
<SCRIPT LANGUAGE="JavaScript">
<!-- 
window.location="<?=$url?>";
// -->
</script>
<a href='<?=$url?>'>weiter: <?=$url?></a><br>
	<?php
}

function error	($msg)
{
	return '<a href="'.BASEURL.'" target="_top" style="color:red;">[ ERROR: '.$msg.' ]</a>';
}


function parsetime ($text) {
	sscanf($text,"%u.%u.%u %u:%u",$day,$month,$year,$hour,$minute);
	return mktime($hour,$minute,0,$month,$day,$year);
}

// generate object from globals
function sqlglobals ($searchprefix="f_i_",$fieldprefix="")
{
	$obj = false;
	$splen = strlen($searchprefix);
	foreach($GLOBALS as $key => $val)
		if (strncmp($key,$searchprefix,$splen) == 0)
			$obj->{$fieldprefix.substr($key,$splen)} = $val;
	return $obj;
}

// used by saveall, sqlglobals with $searchprefix.$fieldname[id] to seperate objects in an id-keyed array
function sqlglobalslist ($searchprefix,$fieldprefix) {
	$mutant = obj2array(sqlglobals($searchprefix,$fieldprefix));
	$res = array();
	foreach (current($mutant) as $id => $ignore) {
		$obj = false;
		foreach ($mutant as $field => $valarr) 
			$obj->{$field} = $valarr[$id];
		$res[$id] = $obj;
	}
	return $res;
}

function &array2object ($arr)
{
	$r = false;
	foreach($arr as $key => $val)
		$r->{$key} = $val;
	return $r;
}
function arr2obj ($arr) { return array2object($arr);}

function arrayquote ($arr,$quot="'")
{
	$result = array();
	foreach ($arr as $key => $val)
		$result[$key] = $quot.addslashes($val).$quot;
	return $result;
}

function object2array ($obj)
{ return get_object_vars($obj); }
function obj2array ($obj)
{ return get_object_vars($obj); }
function obj2arr ($obj)
{ return get_object_vars($obj); }


function copyobj ($obj) { 
	$arr = get_object_vars($obj); 
	$res = false;
	foreach ($arr as $k => $v) $res->$k = $v;
	return $res;
}

// generate save xml attribute from object
function obj2attr ($obj)
{
	if (empty($obj)) return "";
	$arr = get_object_vars($obj);
	$parts = array();
	foreach($arr as $key => $val)
		if (!is_array($val) && !is_object($val))
			$parts[] = $key."='".str_replace("\r","&#13;",str_replace("\n","&#10;",htmlspecialchars($val)))."'";
	return implode(" ",$parts);
}

// return an object with only certain fields set
function filterfields ($obj,$fields)
{
	if (empty($obj)) return false;
	$newobj = false;
	$arr = get_object_vars($obj);
	foreach($arr as $key => $val)
		if (in_array($key,$fields))
			$newobj->{$key} = $val;
	return $newobj;
}

//unset($obj->field); // removes field from object

// generate save sql assignment from object `c` = '6' , `d` = '7'
function obj2sql ($obj,$div=" , ") {
	if (empty($obj)) return "";
	return arr2sql(get_object_vars($obj),$div);
}

function arr2sql ($arr,$div=" , ") { 
	$parts = array();
	foreach($arr as $key => $val)
		if (!is_array($val) && !is_object($val)){
			if(is_numeric($val))$v=$val;
			else $v="'".addslashes($val)."'";
			$parts[] = "`".$key."` = $v";
		}
		$i = implode($div,$parts);
		return $i;
}

function shorttrace ($startoffset=1) {
	$shorttrace = array_slice(debug_backtrace(),$startoffset);
	$res = array();
	foreach ($shorttrace as $o) {
		$args = array();
		if (isset($o["args"])) 
			foreach ($o["args"] as $arg) 
				$args[] = is_array($arg)?"array":is_object($arg)?"object":$arg;
		$res[] =	(isset($o["file"])?$o["file"]:"nofile").":".
					(isset($o["line"])?$o["line"]:"noline").":\t".
					$o["function"]."(".implode(",",arrayquote($args)).")";
	}
	return implode("<br>\n",$res);
}

// warning function, combined with trace. TODO : LogMe ???
function warning ($msg) { echo "WARNING ! ".$msg."<br>".shorttrace()."<br>"; }

$gConnected = false;
$gSqlShowQueries = false;
$gSqlQueries = 0;
$gSqlLastNonSelectQuery = false;
//do an sql query on the database and return the result
function sql	($query) {
	global $gSqlQueries,$gConnected,$gUser,$gSqlLastNonSelectQuery;
	global $gSqlShowQueries;
	if(!$gConnected) {
		mysql_connect(MYSQL_HOST,MYSQL_USER,MYSQL_PASS)
		//	or exit("Could not connect to database");
			or exit("Could not connect to database ".MYSQL_HOST." with ".MYSQL_USER);
		mysql_select_db(MYSQL_DB)
		//	or exit("Could not select database");
			or exit("Could not select database ".MYSQL_DB);
		mysql_query("SET NAMES 'utf8'");
		$gConnected = true;
	}
	$gSqlQueries++;
	if($gSqlShowQueries)echo "[sqlquery=$query]";
	$r = mysql_query($query);
	if (1) if (isset($gUser) && $gUser->admin) if (!eregi("SELECT",$query)) $gSqlLastNonSelectQuery = $query." (".mysql_affected_rows()." affected rows)";
	if (!$r) {	
		$errormsg = mysql_error();
		$s = "MYSQL QUERRY FAILED IN DB '".MYSQL_DB."': ####<br>".$query."<br>####".$errormsg." ".shorttrace();
		if(defined("MYSQL_ERROR_LOG")) {
			$s = date("r") . " " . $_SERVER['PHP_SELF'] . " " . $_SERVER['QUERY_STRING'] . " " . $s . "\n";
			$f = fopen(MYSQL_ERROR_LOG,"a");
			if($f) {
				//echo "<hr><hr>";
				fwrite($f,$s);
				fclose($f);
			}
		}	
		
		global $gProfilPagePage;
		$sqlerror = false;
		$sqlerror->time = time();
		$sqlerror->self = $_SERVER['PHP_SELF'].(isset($gProfilPagePage)?(" : ".$gProfilPagePage):"");
		$sqlerror->query = $_SERVER['QUERY_STRING'];
		$sqlerror->sqlquery = $query;
		$sqlerror->error = $errormsg;
		$sqlerror->stacktrace = stacktrace();
		mysql_query("INSERT INTO `sqlerror` SET ".obj2sql($sqlerror));

		exit($s);
	}
	return $r;
}

function stacktrace(){
	$stack = "";
	$st = array_reverse(debug_backtrace());
	foreach($st as $line)$stack .= $line["file"].":".$line["line"].":".$line["function"]."(".(isset($line["args"])?(is_array($line["args"])?implode(",",$line["args"]):$line["args"]):"").")\n";
	return $stack;
}

//gets an array of fields (0. field in each row)
function sqlgetfieldarray($query){
	$res = sql($query);
	$list = array();
	while($row = mysql_fetch_row($res))$list[] = $row[0];
	mysql_free_result($res);
	return $list;
}

// get a whole sql table as array of objects
function sqlgettable ($query,$field=false,$valuefield=false) {
	$r = sql($query);
	if ($r === true) return true;
	if ($r === false) return false;
	$arr = array();
	if ($valuefield) {
		if ($field)
				while ($o = mysql_fetch_object($r)) $arr[$o->{$field}] = $o->{$valuefield};
		else	while ($o = mysql_fetch_object($r)) $arr[] = $o->{$valuefield};
	} else {
		if ($field)
				while ($o = mysql_fetch_object($r)) $arr[$o->{$field}] = $o;
		else	while ($o = mysql_fetch_object($r)) $arr[] = $o;
	}
	mysql_free_result($r);
	return $arr;
}

// get a whole sql table as array of objects or values, grouped to arrays by field $groupby
function sqlgetgrouptable ($query,$groupby,$field=false,$valuefield=false) {
	$r = sql($query);
	$arr = array();
	if ($valuefield) {
		if ($field)
				while ($o = mysql_fetch_object($r)) {
					if (!isset($arr[$o->{$groupby}])) $arr[$o->{$groupby}] = array();
					$arr[$o->{$groupby}][$o->{$field}] = $o->{$valuefield};
				}
		else	while ($o = mysql_fetch_object($r))  {
					if (!isset($arr[$o->{$groupby}])) $arr[$o->{$groupby}] = array();
					$arr[$o->{$groupby}][] = $o->{$valuefield};
				}
	} else {
		if ($field)
				while ($o = mysql_fetch_object($r)) {
					if (!isset($arr[$o->{$groupby}])) $arr[$o->{$groupby}] = array();
					$arr[$o->{$groupby}][$o->{$field}] = $o;
				}
		else	while ($o = mysql_fetch_object($r))  {
					if (!isset($arr[$o->{$groupby}])) $arr[$o->{$groupby}] = array();
					$arr[$o->{$groupby}][] = $o;
				}
	}
	mysql_free_result($r);
	return $arr;
}

// get a single sql object
function sqlgetobject ($query) {
	$r = sql($query);
	$o = mysql_fetch_object($r);
	mysql_free_result($r);
	return $o;
}

function sqlgetone ($query) {
	$r = sql($query);
	if ($r === false) return false;
	$e = mysql_fetch_row($r);
	mysql_free_result($r);
	if ($e === false) return false;
	if (!isset($e[0])) return false;
	return $e[0];
}


// get a whole sql table as array of objects
function sqlgetonetable ($query) {
	$r = sql($query);
	$arr = array();
	while ($o = mysql_fetch_row($r)) $arr[] = $o[0];
	mysql_free_result($r);
	return $arr;
}

// mysql_fetch_object(sql($query));
// mysql_fetch_row(sql($query));

function ExtractArrayField ($array,$field,$newkey=false)
{
	$arr = array();
	if (!$newkey)
		foreach ($array as $key => $o)
			$arr[$key] = $o->{$field};
	else
		foreach ($array as $o)
			$arr[$o->{$newkey}] = $o->{$field};
			
	return $arr;
}
function AF ($array,$field,$newkey="")
{ return ExtractArrayField($array,$field,$newkey); }

function AF2($array,$field,$nk,$nk2)
{
	$arr = array();
	foreach ($array as $o){
		if(empty($arr[$o->{$nk}]))
			$arr[$o->{$nk}] = array($o->{$nk2} => $o->{$field});
		else
			$arr[$o->{$nk}][$o->{$nk2}] = $o->{$field};
	}
			
	return $arr;
}


function amerge($a1,$a2,$protectids=TRUE){
	if($protectids){
		$a=array();
		foreach ($a1 as $k=>$v)
			$a[$k]=$v;
		foreach ($a2 as $k=>$v)
			if(array_key_exists($k,$a))
				$a["dublicatekey_".$k]=$v;
			else
				$a[$k]=$v;
		return $a;
	}else
		return array_merge($a1,$a2);
}
// mktime(hour,minute,second,month,day,year); time()

import_request_variables("gp", "f_");

if (get_cfg_var("magic_quotes_gpc"))
{
	// TODO : OR if (ini_get('gpc_magic_quotes')=='on') 
	// unescape globals if neccessary
	foreach($GLOBALS as $key => $val)
		if (strncmp($key,"f_",2) == 0)
			if (!is_array($GLOBALS[$key]))
			$GLOBALS[$key] = stripslashes($GLOBALS[$key]);
}

// session stuff

unset($gSessionObj);
unset($gSID);
unset($gUser);

function GeneratePassword()
{
	return strtoupper(substr(base64_encode(crypt(rand().$_SERVER["REMOTE_ADDR"].date("dmYHis"))),4,8));
}

function Lock()
{
	global $gSID;
	if (!isset($gSID))
		exit(error("access denied, please log in"));
}

// check session id
function UpdateSession ($sid)
{
	global $gSID;
	global $gSessionObj;
	global $gUser;
	global $gUID;
	
	$ip = $_SERVER["REMOTE_ADDR"];
	$fastsession = defined("kFastSession");
	
	//todo: uncommented due to multi hunting
	if (!$fastsession && (time()%30) == 0)
		sql("DELETE FROM `session` WHERE `lastuse` < ".(time()-kSessionTimeout));

	$gSessionObj = sqlgetobject("SELECT * FROM `session` WHERE `sid` = '".addslashes($sid)."' AND `lastuse` > ".(time()-kSessionTimeout));
	if (empty($gSessionObj))
		exit(error("no session found (timeout), please log in again"));
	$gUser = sqlgetobject("SELECT * FROM `user` WHERE `id` = '".$gSessionObj->userid."'");
	if ($gSessionObj->ip != $ip && $gUser->iplock == 1)
		exit(error("ip changed during session, please log in again"));

	$gUID = $gSessionObj->userid;
	if (empty($gUser))
		exit(error("session user deleted : access denied"));

	if (!$fastsession)
		sql("UPDATE `session` SET `lastuse`=".time()." WHERE `id`='".$gSessionObj->id."'");

	$gSID = addslashes($sid);
}

// check session
if (isset($f_sid))
	UpdateSession($f_sid);



function QueryAdd ($query,$url=false)
{
	if (!$url) $url = $_SERVER["REQUEST_URI"];
	if(strpos($url,"?") === false)$sep = "?"; else $sep = "&";
	//echo "[$url|$sep|$query]";
	$x = $url.$sep.$query;
	return $x;
}
function SessionLink ($url=false)
{
	global $gSID;
	return QueryAdd("sid=".$gSID,$url);
}

function Query ($url=false)
{
	if ($url === false) return $_SERVER["REQUEST_URI"];
	$pos = strpos($url,"?");
	if ($pos === false) return $pos; // keine query in der url

	$path = substr($url,0,$pos);
	$query = explode("&",substr($url,$pos+1));
	foreach ($query as $key => $o)
	{
		if (strstr($o,"="))
			list($name,$value) = explode("=",$o);
		else {$name = $o;unset($value);}
		if (isset($value) && $value == "?")
			if (isset($GLOBALS["f_".$name]))
				$query[$key] = $name."=".$GLOBALS["f_".$name];
			else unset($query[$key]);
	}
	return $path."?".implode("&",$query);
}

function PrintOptions ($arr,$sel=false)
{
	foreach($arr as $k => $v)
		echo "<OPTION VALUE='".addslashes($k)."' ".(($sel==$k)?"selected":"").">".htmlspecialchars($v)."</OPTION>\n";
}
function PrintSimpleOptions ($arr,$sel=false)
{
	foreach($arr as $v)
		echo "<OPTION VALUE='".addslashes($v)."' ".(($sel==$v)?"selected":"").">".htmlspecialchars($v)."</OPTION>\n";
}
function PrintObjOptions ($arr,$valfield="id",$namefield="name",$sel=false){
	foreach($arr as $o)
		echo "<OPTION VALUE='".addslashes($o->{$valfield})."' ".(($sel==$o->{$valfield})?"selected":"").">".htmlspecialchars($o->{$namefield})."</OPTION>\n";
}
function PrintObjOptionsId ($arr,$valfield="id",$namefield="name",$sel=false) { // [id]name
	foreach($arr as $o)
		echo "<OPTION VALUE='".addslashes($o->{$valfield})."' ".(($sel==$o->{$valfield})?"selected":"").">".htmlspecialchars("[".$o->{$valfield}."]".$o->{$namefield})."</OPTION>\n";
}

function dirfilelist ($path)
	{
		// plakat/  last slash is important !
		$list = array();
		if (!file_exists($path)) return $list;
		$dir = opendir($path);
		if (!$dir) return $list;
		
		while (($file = readdir($dir)) !== false)
			if ($file != "." && $file != ".." && is_file($path.$file)) $list[] = $file;
		closedir($dir);
		return $list;
	}
	function dirdirlist ($path)
	{
		// plakat/  last slash is important !
		$list = array();
		if (!file_exists($path)) return $list;
		$dir = opendir($path);
		if (!$dir) return $list;
		while (($file = readdir($dir)) !== false)
			if ($file != "." && $file != ".." && !is_file($path.$file)) $list[] = $file;
		closedir($dir);
		return $list;
	}
	function dirempty ($path)
	{
		// plakat/  last slash is important !
		if (!file_exists($path)) return true;
		$dir = opendir($path);
		if (!$dir) return true;
		while (($file = readdir($dir)) !== false)
			if ($file != "." && $file != "..") return false;
		closedir($dir);
		return true;
	}

	function in_objarray ($needle,$haystack,$field="id") {
		foreach ($haystack as $test) 
			if ($test->{$field} == $needle) 
				return true;
		return false;
	}
	function objarraygroupby ($arr,$field) {
		$groups = array();
		$res = array();
		foreach ($arr as $o) 
			if (!in_array($o->{$field},$groups)) {
				$groups[] = $o->{$field};
				$res[] = $o;
			}
		return $res;
	}
	
	function NameVal ($o,$field,$default="",$prefix="i_",$arrindex=false) {
		return	'NAME="'.$prefix.$field.(($arrindex===false)?"":("[".$arrindex."]")).'" '.
				'VALUE="'.htmlspecialchars($o?$o->{$field}:$default).'"';
	}
	function IText ($o,$field,$attr="",$default="",$prefix="i_",$arrindex=false) {
		return '<INPUT TYPE="text" '.NameVal($o,$field,$default,$prefix,$arrindex).' '.$attr.'>';
	}
	function ITextArea ($o,$field,$attr="rows=5 style='width:300px'",$default="",$prefix="i_",$arrindex=false) {
		return	'<textarea NAME="'.$prefix.$field.(($arrindex===false)?"":("[".$arrindex."]")).'" '.
				$attr.'>'.htmlspecialchars($o?$o->{$field}:$default).'</textarea>';
	}
	function IRadio ($o,$field,$val,$attr="",$default=0,$prefix="i_",$arrindex=false) {
		$name = $prefix.$field.(($arrindex===false)?"":("[".$arrindex."]"));
		return	'<INPUT TYPE="radio" NAME="'.$name.'" VALUE="'.addslashes($val).'" '.
				((intval($o?$o->{$field}:$default) == $val)?'checked ':'').$attr.'>';
	}
	function ICheck ($o,$field,$attr="",$default=0,$prefix="i_",$arrindex=false) {
		$name = $prefix.$field.(($arrindex===false)?"":("[".$arrindex."]"));
		return	'<INPUT TYPE="hidden" NAME="'.$name.'" VALUE="0">'.
				'<INPUT TYPE="checkbox" NAME="'.$name.'" VALUE="1" '.
				((intval($o?$o->{$field}:$default) != 0)?'checked ':'').$attr.'>';
	}
	function IFlagCheck ($o,$field,$flagvalue,$attr="",$default=0,$prefix="i_",$arrindex=false) {
		// dont forget to array_sum() the result !
		$name = $prefix.$field.(($arrindex===false)?"":("[".$arrindex."]"));
		return	'<INPUT TYPE="checkbox" NAME="'.$name.'[]" VALUE="'.$flagvalue.'" '.
				(((intval($o?$o->{$field}:$default) & $flagvalue) != 0)?'checked ':'').$attr.'>';
	}
	function ISave ($table,$id,$prefix="f_i_") {
		$o = sqlglobals($prefix,"");
		sql("UPDATE `".addslashes($table)."` SET ".obj2sql($o)." WHERE `id` = ".intval($id)." LIMIT 1");
	}
	function ISaveAll ($table,$prefix="f_i_") {
		$list = sqlglobalslist($prefix,"");
		foreach ($list as $id => $o)
			sql("UPDATE `".addslashes($table)."` SET ".obj2sql($o)." WHERE `id` = ".intval($id)." LIMIT 1");
	}
	function IDel ($table,$idlist=false) {
		if ($idlist===false) $idlist = $GLOBALS["f_sel"];
		if (!is_array($idlist))
			$idlist = array(0=>$idlist);
		foreach ($idlist as $id)
			sql("DELETE FROM `".addslashes($table)."` WHERE `id` = ".intval($id)." LIMIT 1");
	}
	function INew ($table,$prefix="f_i_") {
		$o = sqlglobals($prefix,"");
		sql("INSERT INTO `".addslashes($table)."` SET ".obj2sql($o));
	}
	function IUp ($table,$id,$cond="1") {
		$table = addslashes($table);
		$obj = sqlgetobject("SELECT * FROM `".$table."` WHERE `id` = ".intval($id)." LIMIT 1");
		$switchval = sqlgetone("SELECT MAX(`orderval`) FROM `".$table."` WHERE ".$cond." AND `orderval` < ".$obj->orderval);
		if ($switchval) {
			sql("UPDATE `".$table."` SET `orderval` = ".$obj->orderval." WHERE `orderval` = ".$switchval);
			sql("UPDATE `".$table."` SET `orderval` = ".$switchval." WHERE `id` = ".$obj->id);
		}
	}
	function IDown ($table,$id,$cond="1") {
		$table = addslashes($table);
		$obj = sqlgetobject("SELECT * FROM `".$table."` WHERE `id` = ".intval($id)." LIMIT 1");
		$switchval = sqlgetone("SELECT MIN(`orderval`) FROM `".$table."` WHERE ".$cond." AND `orderval` > ".$obj->orderval);
		if ($switchval) {
			sql("UPDATE `".$table."` SET `orderval` = ".$obj->orderval." WHERE `orderval` = ".$switchval);
			sql("UPDATE `".$table."` SET `orderval` = ".$switchval." WHERE `id` = ".$obj->id);
		}
	}
	


function walkint (&$item,$key) {
	$item = intval($item);
}
function walkobjint (&$item,$key,$field) {
	$item->{$field} = intval($item->{$field});
}
function walksplit (&$item,$key,$sep=",") {
	if ($item == "")
			$item = array();
	else	$item = explode($sep,$item);
}
function walkobjsplit (&$item,$key,$param) {
	list($field,$sep) = $param;
	$item->{$field} = explode($sep,$item->{$field});
}
function walkintsplit (&$item,$key,$sep=",") {
	$item = explode($sep,$item);
	array_walk($item,"walkint");
}
function walkobjintsplit (&$item,$key,$param) {
	list($field,$sep) = $param;
	$item->{$field} = explode($sep,$item->{$field});
	array_walk($item->{$field},"walkint");
}
function walkprefix (&$item,$key,$prefix="") { $item = $prefix.$item; }

function explode2 ($sep1,$sep2,$text) {
	if ($text == "") return array();
	$text = explode($sep1,$text);
	array_walk($text,"walksplit",$sep2);
	return $text;
}

function unhtmlentities( $string ){
   $trans_tbl = get_html_translation_table ( HTML_ENTITIES );
   $trans_tbl = array_flip( $trans_tbl );
   $ret = strtr( $string, $trans_tbl );
   return preg_replace( '/&#(\d+);/me' , "chr('\\1')" , $ret );
}

// nested output buffering
$gRob_ob_stack = array();
function rob_ob_start () {
	global $gRob_ob_stack;
	$stacksize = count($gRob_ob_stack);
	if ($stacksize > 0) {
		$gRob_ob_stack[$stacksize-1] .= ob_get_contents();
		ob_end_clean();
	}
	//warning("rob_ob_start $stacksize ");
	//echo("(rob_ob_start $stacksize)<br>");
	ob_start();
	$gRob_ob_stack[$stacksize] = "";
}
function rob_ob_end () {
	global $gRob_ob_stack;
	$stacksize = count($gRob_ob_stack);
	if ($stacksize == 0) { warning("gRob_ob_stack underflow");exit; }
	$res = $gRob_ob_stack[$stacksize-1].ob_get_contents();
	ob_end_clean();
	//warning("rob_ob_end $stacksize ");
	//echo("(rob_ob_end $stacksize)<br>");
	if ($stacksize-1 > 0) ob_start();
	array_pop($gRob_ob_stack);
	return $res;
}
/*
echo "_START_";
rob_ob_start();
echo "_1_";
rob_ob_start();
echo "_2_";
rob_ob_start();
echo "_3:BLA_";
$list1 = rob_ob_end();
$list2 = rob_ob_end();
$list3 = rob_ob_end();
echo "_END_<br>";
echo "L1".$list1."<br>";
echo "L2".$list2."<br>";
echo "L3".$list3."<br>";
// output : 
_START_(rob_ob_start 0)
(rob_ob_start 1)
(rob_ob_start 2)
(rob_ob_end 3)
(rob_ob_end 2)
(rob_ob_end 1)
_END_
L1_3:BLA_
L2_2_
L3_1_
*/


// Active assert and make it quiet
assert_options(ASSERT_ACTIVE, 1);
assert_options(ASSERT_WARNING, 0);
assert_options(ASSERT_QUIET_EVAL, 1);

// Create a handler function
function my_assert_handler($file, $line, $code)
{
  $trace = shorttrace();
   echo "<hr>Assertion Failed:
       File '$file'<br />
       Line '$line'<br />
       Code '$code'<br />
       Trace '$trace'<br />
       <hr />";
}

// Set up the callback
assert_options(ASSERT_CALLBACK, 'my_assert_handler');

if(!function_exists("hypot")){function hypot($x,$y){return sqrt($x*$x + $y*$y);}}

// global $gSID
// global $gUser
?>
