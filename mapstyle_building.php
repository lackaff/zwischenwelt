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

<? foreach($gRace as $race)for($l=0;$l<=kDiffLevel-1;$l++){ 
	$r=$race->id;
	?>
.building_<?=$l?> { background-image:url(<?=g("gebaeude/house-$l.png")?>); border:1px solid yellow; }
.gate_5_open_<?=$l?> { background-image:url(<?=g("gate/tor-offen-ns-$l.png")?>); }
.gate_10_open_<?=$l?> { background-image:url(<?=g("gate/tor-offen-we-$l.png")?>); }

.gb_5_open_<?=$l?> { background-image:url(<?=g("gate/gb-offen-ns-$l.png")?>); }
.gb_10_open_<?=$l?> { background-image:url(<?=g("gate/gb-offen-we-$l.png")?>); }

.portal_open_<?=$l?>{ background-image:url(<?=g("gate/portal-offen-$l.png")?>); }

.seagate_5_open_<?=$l?> { background-image:url(<?=g("hafen/seagate-offen-ns-$l.png")?>); }
.seagate_10_open_<?=$l?> { background-image:url(<?=g("hafen/seagate-offen-we-$l.png")?>); }
	<?php 
	
	foreach ($gBuildingType as $o){
		$cssclass = str_replace("%R%",$r,$o->cssclass);
		$cssclass = str_replace("%L%",$l,$cssclass);
		//if (strstr($cssclass,"%NWSE%")) 
		foreach ($gNWSECombos as $nwse) {?>
.<?=NWSEReplace($cssclass,$nwse)?> { background-image:url(<?=g($o->gfx,$nwse,$l,$r)?>); }
		<?php }
	}?>

<?}?>

