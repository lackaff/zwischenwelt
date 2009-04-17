<?php
include("lib.php");
include("header.php");
?>
<span id=code><h1>Code</h1></span>
<a href="code/">download code</a><br>
<a href="http://zwischenwelt.org/trac/zw/browser">SVN</a>
(svn://zwischenwelt.org/zw/zw/trunk)<br>
[ <a href="https://sourceforge.net/cvs/?group_id=138787">CVS</a> 
(cvs -z3 -d:pserver:anonymous@cvs.sourceforge.net:/cvsroot/zwischenwelt co -P zw) eine sehr sehr alte version ]<br>

<br>

<span id=changelog><h1>ChangeLog</h1></span>
<div class="changelog">
<?php
	$cl = file("ChangeLog");
	$l = array();
	$hd = $cl[0];
	for($i=1;$i<sizeof($cl);++$i){
		$line = trim($cl[$i]);
		if(empty($line)){
			$hd = trim($cl[$i+1]);
			++$i;
		} else $l[$hd] .= $line."<br>";
	}
	$i = 0;
	foreach($l as $d=>$t){++$i; ?>
	<div class="entry">
		<span class="date"><?=$d?></span>
		<span class="text"><?=$t?></span>
	</div>
	<?php if($i>5)break;} ?>
</div>
<?php include("footer.php"); ?>
