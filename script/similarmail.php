<?php

// list users with similar email start (part before @)
/*
see also
levenshtein--      Calculate Levenshtein distance between two strings
similar_text--      Calculate the similarity between two strings
soundex--Calculate the soundex key of a string
metaphone--Calculate the metaphone key of a string
*/


require_once("../lib.main.php");

$users = sqlgettable("SELECT * FROM `user`","id");
$guilds = sqlgettable("SELECT * FROM `guild`","id");
$candidates = array();
foreach ($users as $o) { 
	list($o->mailstart,$o->mailend) = explode("@",$o->mail); 
	if (strlen($o->mailstart) <= 6) continue;
	if ($o->mailstart == "mail") continue;
	if ($o->mailstart == "info") continue;
	if ($o->mailstart == "andy") continue;
	if ($o->mailstart == "webmaster") continue;
	$candidates[] = $o; 
}

$pairs = array();
$o = false; // used to construct pair objects
foreach ($candidates as $a) {
	foreach ($candidates as $b) if ($b->id > $a->id) {
		$o->a = $a->id;
		$o->b = $b->id;
		$o->dist = levenshtein($a->mailstart,$b->mailstart);
		$pairs[] = $o;
	}
}



function cmp_pair($a, $b) {
    if ($a->dist == $b->dist) return strcasecmp($a->mailstart,$b->mailstart);
    return ($a->dist < $b->dist) ? -1 : 1;
}
usort($pairs, "cmp_pair");
?>
<table>
<tr>
	<th>dist</th>
	<th>id1</th>
	<th>id2</th>
	<th>mail1</th>
	<th>mail2</th>
	<th>name1</th>
	<th>name2</th>
	<th>guild1</th>
	<th>guild2</th>
</tr>
<?php $i=0; foreach ($pairs as $o) if (++$i >= 400) break; else {?>
<tr>
	<th><?=$o->dist?></th>
	<th><?=$o->a?></th>
	<th><?=$o->b?></th>
	<th><?=$users[$o->a]->mail?></th>
	<th><?=$users[$o->b]->mail?></th>
	<th><?=$users[$o->a]->name?></th>
	<th><?=$users[$o->b]->name?></th>
	<th><?=$users[$o->a]->guild ? $guilds[$users[$o->a]->guild]->name : ""?></th>
	<th><?=$users[$o->b]->guild ? $guilds[$users[$o->b]->guild]->name : ""?></th>
</tr>
<?php } // endforeach?>
</table>