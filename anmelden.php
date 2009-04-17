<?php
include("lib.php");
include("lib.main.php");
require_once("lib.message.php");
if($f_a+$f_b==$f_aplusb)$spam = false;
else $spam = true;




$time = time() - 60*60*24*7;
sql("DELETE FROM `pending` WHERE `time`<$time");

include("header.php");
?>
<span id=anmeldung><h1>Anmeldung</h1></span>
<?php
$errstr = '';
if(isset($f_selfcall))
//if(isset($f_name) && isset($f_mail) && strlen($f_pass1) >= 6 && $f_pass1 == $f_pass2)
{
	if(isset($f_name)) {
		$f_name = addslashes(trim($f_name));
		if(ereg('^[a-zA-Z][a-zA-Z0-9 \.-]+$',$f_name) === FALSE)$errstr .= "Der Name enth&auml;lt ung&uumlml;ltige Zeichen. Namen m&uuml;ssen mit Buchstaben beginnen und bestehen nahezu nur aus Buchstaben.<br>";
	} else $errstr .= 'Bitte einen Loginnamen angeben.<br>';
	
	if(strpos($f_mail,'@') && strpos($f_mail,'.')) $f_mail = addslashes($f_mail);
	else $errstr .= 'Bitte eine g&uuml;tige E-Mailadresse angeben.<br>';
	
	$f_from = addslashes($f_from);
	$f_text = addslashes($f_text);
	
	// TODO : unhardcode passlen
	if((strlen($f_pass1) >= 6) && ($f_pass1 == $f_pass2)) $f_pass = $f_pass1;
	else $errstr .= 'Das Passwort muss mindestens 6 Zeichen lang sein und im Best&auml;tigungsfeld wiederholt werden.<br>';
	
	if ($errstr == '' && $spam == false)
	{
		$r = sql("SELECT `id` FROM `user` WHERE `name`='$f_name' OR `mail`='$f_mail'");
		if(mysql_num_rows($r))
		{
			echo "<hr>Sorry, den Usernamen oder die Mailadresse gibt es leider schon.<hr>";
		}
		else
		{
			$r = sql("SELECT `name` FROM `pending` WHERE `name`='$f_name' OR `mail`='$f_mail'");
			if(mysql_num_rows($r))
			{
				echo "<hr>Sorry, unter dem Usernamen oder der Mailadresse hat sich schon jemand angemeldet.<hr>";
			}
			else
			{
				$time = time();
				$ip = $_SERVER["REMOTE_ADDR"];
				$key = md5("INSERT INTO `pending` SET `time`=$time,`ip`='$ip',`mail`='$f_mail',`name`='$f_name',`pass`='$f_pass',`key`='$key',`from`='$f_from',`text`='$f_text'");
				$newpending = false;
				$newpending->ip = $ip;
				$newpending->key = $key;
				$newpending->time = $time;
				$newpending->mail = $f_mail;
				$newpending->name = $f_name;
				$newpending->from = $f_from;
				$newpending->text = $f_text;
				sql("INSERT INTO `pending` SET ".obj2sql($newpending)." , `pass`=PASSWORD('".addslashes($f_pass)."')");
				mail($f_mail, "Zwischenwelt Registratur", "Um den Account freizuschalten bitte folgenden Link aufrufen: ".BASEURL."anmelden.php?key=$key","From: ".ZW_MAIL_SENDER."\r\nReply-To: ".ZW_MAIL_SENDER."\r\nX-Mailer: PHP/" . phpversion()); 
				if (ZW_NEWREGISTRATION_NOTIFY) mail(ZW_NEWREGISTRATION_NOTIFY,"neue Anmeldung","name=$f_name\nmail=$f_mail\nfrom=$f_from\ntext=$f_text","From: ".ZW_MAIL_SENDER."\r\nReply-To: ".ZW_MAIL_SENDER."\r\nX-Mailer: PHP/" . phpversion());
				echo '<hr>Es hat geklappt, sie haben Post :).<hr><b style="color:red">Manchmal kann es mit dem Mails etwas dauern, also keine Panik.</b><hr>';
				if(ZW_NEWREGISTRATION_SHOWLINK){
					echo '<hr>Um den Account freizuschalten, <a href="anmelden.php?key='.$key.'">hier</a> klicken.<hr>';
				}
				include("footer.php");
				exit;
			}
		}
	} else echo '<hr><p style="color:red">Es sind Fehler aufgetreten:<br>'.$errstr.'</p><hr>';
}
else if(isset($f_key))
{
	$f_key = addslashes($f_key);
	$r = sql("SELECT * FROM `pending` WHERE `key`='$f_key' LIMIT 1");
	if($row = mysql_fetch_array($r))
	{
		$sr = $gGlobal["store"];

		$newuser = false;
		$newuser->max_lumber = $sr;	$newuser->lumber = $sr;
		$newuser->max_stone = $sr;	$newuser->stone = $sr;
		$newuser->max_food = $sr;	$newuser->food = $sr;
		$newuser->max_metal = $sr;	$newuser->metal = $sr;
		$newuser->color = sprintf("#%02X%02X%02X",rand(0,255),rand(0,255),rand(0,255));
		$newuser->name = $row["name"];
		$newuser->pass = $row["pass"]; // already encrypted in pending
		$newuser->mail = $row["mail"];
		$newuser->registered = time();
		$newuser->guild = kGuild_Weltbank; // weltbankgild
		//fud forum user import hack

		$u = $newuser;
        /*	$fu = sqlgetobject("SELECT * FROM `fudforum`.`fud26_users` WHERE `login` like '".addslashes($u->name)."' OR `email` like '".addslashes($u->mail)."'");
	        if(empty($fu)){
			$fu = null;
			$fu->login = $u->name;
			$fu->alias = $fu->login;
			$fu->email = $u->mail;
			$fu->join_date = ($u->registered>0?$u->registered:time());
			$fu->time_zone = "Europe/Berlin";
			$fu->passwd = $u->pass;
			$fu->name = $fu->login;
			$fu->users_opt = 4357111; // todo : unhardcode
			sql("INSERT INTO `fudforum`.`fud26_users` SET ".obj2sql($fu));
		}*/
		
		$newuser->guildstatus = sqlgetone("SELECT `stdstatus` FROM `guild` WHERE `id`=".intval(kGuild_Weltbank));
		sql("INSERT INTO `user` SET ".obj2sql($newuser));
		$newuserid = mysql_insert_id();

		//set a hq at a random position
		list($x,$y) = FindRandomStartplace();
		$o = null;
		$o->user = $newuserid;
		$o->type = 1;
		$o->hp = 5000;
		$o->x = $x;
		$o->y = $y;
		sql("INSERT INTO `building` SET ".obj2sql($o));
		
		sql("DELETE FROM `pending` WHERE `key`='$f_key'");
		echo "<hr>Alles ok, der Account ist nun freigeschaltet. Sie k&ouml;nnen sich nun einloggen.<hr>";
		include("footer.php");
		exit;
		/*
		addDirectory($newuserid,"root",0);
		$id = sqlgetone("SELECT id from message where `subject` = 'root' AND `unread` = '-1' AND `from` = '".$newuserid."' ");
		addDirectory($newuserid,"SEND",$id);
		*/
		
		createFolder("Eingang",0,$newuserid,kFolderTypeRoot);
		createFolder("Ausgang",0,$newuserid,kFolderTypeSent);
		createFolder("Berichte",0,$newuserid,kFolderTypeExtra);
		/*$text="So meine lieben Kinder,<br>
<br>
Ihr seid nun alle in dieser grossen Gilde gelandet, da ihr alle noch recht klein seid. <br>
Diese Gilde, die Weltbank hat den Zweck euch bei eurem Start zu helfen. Bedient euch an den Rohstoffen, <br>
um eure Gebaude, Updates und Forschungen zu finanzieren und schnell zu wachsen.<br>
Habt ihr 20.000 Punkte erreicht, musst ihr die Gilde verlassen und eure Schulden zuruckzahlen <br>
(1% eurer Rohstoffproduktion wird eingezogen bis ihr alles abgezahlt habt oder zwei Wochen vergangen sind). <br>
Naturlich konnt ihr auch die Gilde verlassen und euch allein versuchen (langwierig) oder einer anderen Gilde beitreten <br>
(empfehlenswert; Highscore - Gilde - Gilde anklicken und Bewerbungstext abschicken).<br>
Aber bedenkt, dass ihr, wenn ihr gro?er seid, mehr Rohstoffe produziert und somit die Schulden schnell zuruckzahlen<br>
konnt und einen Vorteil durch die Mitgliedschaft in einer Gilde habt.<br>
<br>
Wenn Ihr Fragen habt, konnt ihr die gerne hier in der Gilde stellen.<br>
<br>
Die Weltenmacher<br>
<br>
Schaut euch doch auch mal die FAQ an:<br>
http://zwischenwelt.net-play.de/faq/<br>
<br>
und lest ab und zu mal im Forum<br>
http://zwischenwelt.net-play.de/forum/phpBB2/index.php<br>
	<br>
";
	sendMessage($newuserid,0,"Herzlich Willkommen in der Weltbankgilde und bei zwischenwelt.org",$text);*/
	}
	else
	{
		echo "<hr>Irgendwas ist schiefgelaufen. der Key ist falsch.<hr>";
		include("footer.php");
		exit;
	}
}
?>
<FORM style='text-align:center' METHOD=POST ACTION="anmelden.php">
	<input type="hidden" name="selfcall" value="1">
	<table>
		<tr><th align="left">Name:</th><td><INPUT TYPE="text" NAME="name" value="<?if(isset($f_name))echo $f_name?>"></td></tr>
		<tr><th align="left">Password:</th><td><INPUT TYPE="password" NAME="pass1"></td></tr>
		<tr><th align="left">(nochmal):</th><td><INPUT TYPE="password" NAME="pass2"></td></tr>
		<tr><th align="left">eMail:</th><td><INPUT TYPE="text" NAME="mail" value="<?if(isset($f_mail))echo $f_mail?>"></td></tr>
		<tr><th align="left" colspan="2">woher kennst du dieses Spiel?</th></tr>
		<tr><td align="left" colspan="2">
			<select name="from" size="1">
				<option>sonstiges</option>
				<option>ich entwickel an dem Spiel mit</option>
				<option>Freunde</option>
				<option>habe darüber gelesen</option>
				<option>Linksammlung</option>
				<option>ein Geistesblitz</option>
			</select>
		</td></tr>
		<tr><td align="left" colspan="2"><textarea name="text" rows="3" cols="25">hier das sonstige hinschreiben</textarea></td></tr>
		<tr>
			<td>spamschutz</td>
			<td>
			<INPUT TYPE="text" NAME="a" ID="antispam_a" style="width:50px" value="<?=rand(1,100)?>">+
			<INPUT TYPE="text" NAME="b" ID="antispam_b" style="width:50px" value="<?=rand(1,100)?>">=
			<INPUT TYPE="text" NAME="aplusb" ID="antispam_aplusb" style="width:50px" value="???">
			(einfach ignorieren)
			</td>
		</tr>
		<SCRIPT LANGUAGE="JavaScript" type="text/javascript">
		<!--
			document.getElementById('antispam_aplusb').value = parseInt(document.getElementById('antispam_a').value) + parseInt(document.getElementById('antispam_b').value)
		//-->
		</SCRIPT>				
		<tr><td align="left"></td><td align="right"><INPUT TYPE="submit" VALUE="anmelden"></td></tr>
	</table>
</FORM>
<br>
<b>Mit der Anmeldung stimmt man den <a href="http://zwischenwelt.milchkind.net/zwwiki/index.php/Regeln">Regeln</a> zu!!!</b>
<br>
<br>
Wenn bei der Anmeldung etwas nicht geklappt hat, keine Panik, sondern einfach eine eMail an "hagish (&auml;t) schattenkind.net"
<?php include("footer.php"); ?>
