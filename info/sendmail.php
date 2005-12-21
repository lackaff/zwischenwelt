<?php
/*zwei parameter übergeben: sendmail.php?TO=emailaddress@domain.tld&cont=CONTENTSTRING */
/*evtl noch sessions reinbacken */

ini_set(sendmail_from, "opcfx@leenox.de");


$connect = fsockopen ("localhost", "25", $errno, $errstr, 30) or die("Could not talk to the smtp server!");
	$rcv = fgets($connect, 1024); 
fputs($connect, "HELO leenox.de\r\n");
fputs($connect, "MAIL FROM: opcfx-no-reply@leenox.de\r\n");
foreach($tos as $to){
	fputs($connect, "RCPT TO: $TO\r\n");
	$rcv=fgets($connect,1024);
}
fputs($connect, "DATA\r\n");
fputs($connect,stripslashes($cont)."\r\n");
fputs($connect,".\r\n");
fputs($connect, "QUIT\r\n");
fclose($connect);

?>