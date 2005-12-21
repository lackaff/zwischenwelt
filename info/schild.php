<?php 
$gClassName = "cInfoSchild";
class cInfoSchild extends cInfoBuilding {
	function mydisplay() {
		foreach ($_REQUEST as $name=>$val) ${"f_".$name} = $val;
		global $gUser;
		global $gObject;
		profile_page_start("schild.php"); ?>
		<hr>
		<?php 
			if(($gUser->id == $gObject->user || $gUser->admin) && isset($f_text)) {
				$text = $f_text;
				SetBParam($gObject->id,"text",$text);
			} 
			$text = GetBParam($gObject->id,"text");
		?>
		<div><?php ImgBorderStart();?><pre><?=magictext(htmlspecialchars($text))?></pre><?php ImgBorderEnd();?></div>
		<hr>
		<?php
		if($gUser->id == $gObject->user || $gUser->admin) {?>
			<form method="post" action="<?=query("info.php?sid=?&x=?&y=?")?>">
			<textarea name="text" rows="5" cols="60"><?=htmlspecialchars($text)?></textarea><br>
			<input type="submit" name="buttom_schild" value="&uuml;bernehmen">
			</form>
		<?php } ?>
		<?php profile_page_end();
	}
}