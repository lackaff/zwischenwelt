<?php
$gClassName = "cInfoSchach";
class cInfoSchach extends cInfoBuilding {
	function mydisplay() {
		//redirect(kURL_Chess);
		?>
		<p><span style="padding:5px;background-color:#dddddd;border:solid black 1px;">[<a href="<?=kURL_Chess?>">hier</a>] gehts zum Schachplatz</span></p>
		<?php
	}
}?>
