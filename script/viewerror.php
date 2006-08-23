<table border=1>
<tr>
	<th>file</th>
	<th>line</th>
	<th>count</th>
	<th></th>
	<th></th>
</tr>
<?php

require_once("../lib.main.php");

if(isset($f_line) && isset($f_file)){
	sql("DELETE FROM `phperror` WHERE `scriptname`='".addslashes($f_file)."' AND `scriptlinenum`=".intval($f_line));
}

$t = sqlgettable("SELECT `scriptname`,`scriptlinenum`,COUNT(*) `errorcount` FROM `phperror` GROUP BY `scriptname`,`scriptlinenum` ORDER BY `errorcount` DESC,`scriptname`,`scriptlinenum`");

foreach($t as $x){
	?>
	<tr>
		<td><?=str_replace(BASEPATH,"",$x->scriptname)?></td>
		<td><?=$x->scriptlinenum?></td>
		<td><?=$x->errorcount?></td>
		<td>
		<?php
		
		$msgs = array();
		$es = sqlgettable("SELECT `errornum`,`errortype`,`errormsg`,`code` FROM `phperror` WHERE `scriptname`='".addslashes($x->scriptname)."' AND `scriptlinenum`='".addslashes($x->scriptlinenum)."'");
		foreach($es as $e){
			$hash = trim($e->errormsg."-".$e->code);
			if(in_array($hash,$msgs))continue;
			echo "$e->errornum $e->errortype: $e->errormsg<br>";
			if(!empty($e->code))echo "<pre>".htmlspecialchars($e->code)."</pre><br>";
			$msgs[] = $hash;
		}
		
		?>
		</td>
		<td>
			<form method=post action=?>
				<input type=hidden name=line value="<?=$x->scriptlinenum?>">
				<input type=hidden name=file value="<?=$x->scriptname?>">
				<input type=submit value="löschen">
			</form>
		</td>
	</tr>
	<?php
}

?>
</table>
