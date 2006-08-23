<?php
require_once("../lib.main.php");
Lock();
profile_page_start("note.php");

if(isset($f_save))SetUserValue($gUser,"note",$f_note);

?>
<form method=post action="<?=query("note.php?sid=?")?>">
<input style="width:100%" type=submit name=save value=speichern><br>
<textarea name=note style="margin-top:5px;width:100%;height:90%;border:solid black 1px;background-color:#efefef;" rows=20 cols=20><?=htmlspecialchars(GetUserValue($gUser,"note","hier kann man eine Notiz hinterlassen"))?></textarea>
</form>
<?php
profile_page_end();
?>
