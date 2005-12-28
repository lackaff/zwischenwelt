<?php
require_once("lib.main.php");
//if ( extension_loaded('zlib') )ob_start('ob_gzhandler');
header('Content-Type: text/javascript');
//header('Last-Modified: '.date("r",time()-60*60*24*7));
//header('Cache-Control: max-age='.(60*60*24*7).', must-revalidate');

?>
kDefaultTerrainID = <?=kTerrain_Grass?>;
kNWSE_N = <?=kNWSE_N?>;
kNWSE_W = <?=kNWSE_W?>;
kNWSE_S = <?=kNWSE_S?>;
kNWSE_E = <?=kNWSE_E?>;
kConstructionPlanPic = "<?=kConstructionPlanPic?>";
kConstructionPic = "<?=kConstructionPic?>";
kTransCP = "<?=kTransCP?>"; // transparent construction plan

kJSMapMode_Normal = 0;
kJSMapMode_Plan = 1;
kJSMapMode_Bauzeit = 2;
kJSMapMode_HP = 3;

kJSMapTileSize = <?=kMapTileSize?>;
kMapTip_xoff = 50;
kMapTip_yoff = 60;
kMapTipName = "maptip";

function UnitTypeHasNWSE (unittype) {
	return unittype == <?=kUnitType_HyperBlob?>;
}

<?php
// produces array full of objects, can be accessed using "gTerrainType[12].gfx"
function php2js_objarray ($name,$arr,$fields) {
	if (!$fields) return;
	$fields = explode(",",$fields);
	?>
	function <?="entry_".$name?> (<?=implode(",",$fields)?>) {
		var res = new Object();
		<?php foreach ($fields as $fieldname) echo "res.".$fieldname." = ".$fieldname.";"; ?>
		return res;
	}
	<?php
	echo $name." = new Array();\n";
	foreach ($arr as $key => $o) {
		$objfields = obj2arr($o);
		$values = array();
		foreach ($fields as $fieldname) {
			$value = $o->{$fieldname};
			if (is_array($value)) $values[] = "'".addslashes(implode(",",$value))."'"; // warning, not suitable for fancy values
			else if (is_numeric($value)) 
					$values[] = $value;
			else	$values[] = "'".addslashes($value)."'";
		}
		echo $name."[".$key."] = entry_".$name."(".implode(",",$values).");\n";
	}
}
php2js_objarray("gTerrainType",$gTerrainType,"name,speed,buildable,gfx,mod_a,mod_v,mod_f,movable_flag,connectto_terrain,connectto_building");
php2js_objarray("gBuildingType",$gBuildingType,"name,maxhp,speed,gfx,mod_a,mod_v,mod_f,connectto_terrain,connectto_building,neednear_building,require_building,exclude_building,border,movable_flag,movable_override_terrain");
php2js_objarray("gUnitType",$gUnitType,"name,orderval,a,v,f,r,speed,gfx");
php2js_objarray("gItemType",$gItemType,"name,gfx");
// bodenschaetze (ressources,perks,specials,deposit...)
?>