<?php
include("lib.php");
include("lib.main.php");


include("header.php"); ?>
<span id=passwortvergessen><h1>Passwort vergessen</h1></span>
<?php

if (isset($f_name)) {?>
	<?php 
		$u = sqlgetobject("SELECT * FROM `user` WHERE
			`name` = '".addslashes($f_name)."' AND
			`mail` = '".addslashes($f_email)."'");
		if ($u)
		{
			$newpass = GeneratePassword();
			sql("UPDATE `user` SET `pass` = PASSWORD('".addslashes($newpass)."') WHERE `id` = ".$u->id);
			mail($u->mail, "Zwischenwelt Passwort",
				"Sie haben ein neues Passwort fuer Zwischenwelt angefordert.\n".
				"username : ".$u->name."\n".
				"passwort : ".$newpass."\n".
				BASEURL." \n".
				"Ihr Zwischenwelt team","From: ".ZW_MAIL_SENDER."\r\nReply-To: ".ZW_MAIL_SENDER."\r\nX-Mailer: PHP/" . phpversion()); 
			?>
			Sie haben Post ! Ihnen wurde ein neues Passwort zugeschickt.<br>
			<a href="index.php">zur Startseite</a>
			<?php
		} else {
			?>
			Kein User gefunden auf der auf die angegebenen Daten passt.<br>
			Vielleicht war es ja die falsche EMail adresse....<br>
			<FORM METHOD=POST ACTION="<?=Query("?sid=?")?>">
			Name:<INPUT TYPE="text" NAME="name" VALUE=""> (Ihr Benutzername)<br>
			EMail:<INPUT TYPE="text" NAME="email" VALUE=""> (die bei der Registrierung angegebene EMail-Adresse)<br>
			<INPUT TYPE="submit" VALUE="neues Passwort zuschicken">
			</FORM>
			<?php
		}
	?>
<?php } else {?>
	<FORM METHOD=POST ACTION="<?=Query("?sid=?")?>">
	<table>
		<tr><th>Name:</th><td><INPUT TYPE="text" NAME="name" VALUE=""> (Ihr Benutzername)</td></tr>
		<tr><th>EMail:</th><td><INPUT TYPE="text" NAME="email" VALUE=""> (die bei der Registrierung angegebene EMail-Adresse)</td></tr>
		<tr><td colspan=2 align=right><INPUT TYPE="submit" VALUE="neues Passwort zuschicken"></td></tr>
	</table>
	</FORM>
<?php }

include("footer.php");
?>
