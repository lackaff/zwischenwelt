<?php
define("CONTENT_TYPE","text/css");

if ( extension_loaded('zlib') )ob_start('ob_gzhandler');

// see CONTENT_TYPE header('Content-Type: text/css');
$maxage = 60*60*24*7;
header('Last-Modified: '.date("r",floor((time()-$maxage)/$maxage)*$maxage));
header('Cache-Control: max-age='.$maxage.', must-revalidate');
require_once("lib.main.php");

if (isset($f_uid)) $gUser = sqlgetobject("SELECT * FROM `user` WHERE `id` = ".intval($f_uid));

//$gNWSECombos = array("","n","w","s","e","nwse","nws","nwe","nse","wse", "nw","ns","ne", "ws","we", "se");
$gNWSECombos = array("0","1","2","3","4","5","6","7","8","9","10","11","12","13","14","15");

define("kDiffLevel",2);

?>

<?php foreach ($gTerrainPatchType as $o) {?>
.p<?=$o->id?> { background-image:url(<?=g("$o->gfx")?>); }
<?php } ?>

<?php foreach ($gTerrainType as $o) foreach ($gNWSECombos as $nwse) {?>
.t<?=$o->id?>-<?=NWSEReplace("%NWSE%",$nwse)?> { background-image:url(<?=g($o->gfx,$nwse)?>); }
<?php } ?>

<?php foreach ($gTerrainSubType as $o) foreach ($gNWSECombos as $nwse) {?>
.<?=NWSEReplace("ts-$o->terraintype-$o->terrainconnecttype-%NWSE%",$nwse)?> { background-image:url(<?=g($o->gfx,$nwse)?>); }
<?php } ?>

<?php foreach (array("n","w","s","e") as $nwse) {?>
.<?=NWSEReplace("fluss-see_%NWSE%",$nwse)?> { background-image:url(<?=g("river/river-see-%NWSE%.png",$nwse)?>); }
 <?}?>
