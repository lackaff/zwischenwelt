<?php
$gClassName = "cInfoSpielhalle";
class cInfoSpielhalle extends cInfoBuilding {
	function mydisplay() {
		global $gUser;
		
		//redirect(kURL_Chess);
		$url = query("http://zwischenwelt.org/casino/what_ever.swf?user=".intval($gUser->id)."&sid=?");
		?>
		<p><span style="padding:5px;background-color:#dddddd;border:solid black 1px;">[<a href="#" onClick="javascript:window.open('<?=$url?>','Minispiel','height=515,width=300')">hier</a>] gehts zum ersten Minispiel</span></p>
		<?php
	}
}?>
