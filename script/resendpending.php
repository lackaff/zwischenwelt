<?php
include("../lib.main.php");
$t = sqlgettable("SELECT * FROM `pending`");
foreach($t as $x){
	echo "sende mail an $x->mail <br>\n";
	mail($x->mail, "Zwischenwelt Registratur", "Um den Account freizuschalten bitte folgenden Link aufrufen: ".BASEURL."anmelden.php?key=$x->key","From: ".ZW_MAIL_SENDER."\r\nReply-To: ".ZW_MAIL_SENDER."\r\nX-Mailer: PHP/" . phpversion()); 
}
?>