<?php
if ( extension_loaded('zlib') )ob_start('ob_gzhandler');

header('Content-Type: text/css');
$maxage = 60*60*24*7;
header('Last-Modified: '.date("r",floor((time()-$maxage)/$maxage)*$maxage));
header('Cache-Control: max-age='.$maxage.', must-revalidate');
require_once("lib.main.php");

if (isset($f_uid)) $gUser = sqlgetobject("SELECT * FROM `user` WHERE `id` = ".intval($f_uid));

//$gNWSECombos = array("","n","w","s","e","nwse","nws","nwe","nse","wse", "nw","ns","ne", "ws","we", "se");
$gNWSECombos = array("0","1","2","3","4","5","6","7","8","9","10","11","12","13","14","15");

define("kDiffLevel",2);


/*
if($gUser && $gUser->usegfxpath){
	$gfxpath = $gUser->gfxpath;
	if(!empty($gfxpath))if($gfxpath{strlen($gfxpath)-1} != '/')$gfxpath .= "/";
}
else $gfxpath = "";
*/

?>

.map th { width:<?=kMapTileSize?>px; height:<?=kMapTileSize?>px; }
.map td { width:<?=kMapTileSize?>px; height:<?=kMapTileSize?>px; background-position:1px 1px; background-repeat:no-repeat;}
.map div { width:100%; height:100%; background-position:1px 1px; background-repeat:no-repeat;}
.ramme { background-image:url(<?=g("ramme.png")?>); }
.wp { background-color:#00FF00; }
.pathb { background-color:#FF8888; }
.path { background-color:#88FF88; }
.cp { background-image:url(<?=g(kConstructionPlanPic)?>); }
.tcp { background-image:url(<?=g(kTransCP)?>); }
.con { background-image:url(<?=g(kConstructionPic)?>); }
.gr { background-image:url(<?=g("grass.png")?>); }

<?php
foreach($gItemType as $it)echo ".item_$it->id { background-image:url(\"".g($it->gfx)."\"); }\n";
?>

<?php foreach ($gUnitType as $o) {?>
.unit_<?=$o->id?> { background-image:url(<?=g("$o->gfx")?>); }
<?php } ?>

<?php foreach ($gNWSECombos as $nwse) {?>
.<?=NWSEReplace(kMonster_HyperblobCSS,$nwse)?> { background-image:url(<?=g(kMonster_HyperblobGFX,$nwse)?>); }
<?php } ?>
