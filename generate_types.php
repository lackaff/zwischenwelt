<?php
// generates "tmp/types.php"

function val_out($val){
	if(is_array($val)) {
		$imploded = implode(",",$val);
		if ($imploded == "") 
				return "array()";
		else	return 'array(0=>'.$imploded.')';
	}
	else return '"'.addslashes($val).'"';
}

function objarr_out ($arr) {
	$res = array();
	foreach ($arr as $key=>$val)
		$res[] = '"'.addslashes($key).'"=>'.val_out($val);
	return implode(",",$res);
}
function array_out_numkey ($arr) {
	$res = array();
	foreach ($arr as $key=>$val){
		$res[] = "\n".intval($key).'=>arr2obj(array('.objarr_out(obj2arr($val))."))";
	}
	return implode(",",$res);
}

function array_out_numkey2 ($arr) {
	$res = array();
	foreach ($arr as $key=>$val){
		$res[] = intval($key).'=>arr2obj(array('.array_out_numkey($val)."))";
	}
	return implode(",",$res);
}

function GenerateTypesPHP () {
	$fp = fopen(kTypeCacheFile,"w");
	if (!$fp) return;
	fwrite($fp,"<?php\n");

	$l = array(
		"gArmyType"=>"armytype",
		"gArmyTransfer"=>"armytransfer",
		"gTechnologyType"=>"technologytype",
		"gTechnologyGroup"=>"technologygroup",
		"gItemType"=>"itemtype",
		"gSpellType"=>"spelltype",
		"gRace"=>"race",
		"gTerrainSubType"=>"terrainsubtype",
		"gTerrainPatchType"=>"terrainpatchtype",
	);
	foreach($l as $g=>$t){
		$tbl = sqlgettable("SELECT * FROM `$t` ORDER BY `id`","id");
		fwrite($fp,'$'.$g.' = array('.array_out_numkey($tbl).");\n");
		$tbl = null;
	}
	
	$gUnitType = sqlgettable("SELECT * FROM `unittype` ORDER BY `orderval` ASC","id");
	fwrite($fp,'$gUnitType = array('.array_out_numkey($gUnitType).");\n");
	
	
	$gTerrainPatchType = sqlgettable("SELECT * FROM `terrainpatchtype`","id");
	$gTerrainPatchTypeMap = array();
	foreach($gTerrainPatchType as $x)$gTerrainPatchTypeMap[$x->here][] = $x;
	fwrite($fp,'$gTerrainPatchTypeMap = array('.array_out_numkey2($gTerrainPatchTypeMap).");\n");
	
	$gBuildingType = sqlgettable("SELECT * FROM `buildingtype` ORDER BY `orderval` ASC","id");
	foreach($gBuildingType as $id=>$o){
		$gBuildingType[$id]->cssclass = "b".$o->id."-%R%-%L%-%NWSE%";
		$gBuildingType[$id]->connectto_terrain = explode(",",trim($gBuildingType[$id]->connectto_terrain,","));
		$gBuildingType[$id]->connectto_building = explode(",",trim($id.",".$gBuildingType[$id]->connectto_building,","));
		$gBuildingType[$id]->neednear_building = explode(",",trim($gBuildingType[$id]->neednear_building,","));
		$gBuildingType[$id]->require_building = explode(",",trim($gBuildingType[$id]->require_building,","));
		$gBuildingType[$id]->exclude_building = explode(",",trim($gBuildingType[$id]->exclude_building,","));
	}	
	fwrite($fp,'$gBuildingType = array('.array_out_numkey($gBuildingType).");\n");
	
	$gTerrainType = sqlgettable("SELECT * FROM `terraintype` ORDER BY `id` ASC","id");
	foreach($gTerrainType as $id=>$o){
		$gTerrainType[$id]->connectto_terrain = explode(",",trim($id.",".$gTerrainType[$id]->connectto_terrain,","));
		$gTerrainType[$id]->connectto_building = explode(",",trim($gTerrainType[$id]->connectto_building,","));
	}	
	fwrite($fp,'$gTerrainType = array('.array_out_numkey($gTerrainType).");\n");

	fwrite($fp,'?>');
	fclose($fp);
}
GenerateTypesPHP();
//echo "<hr><hr>GenerateTypesPHP<hr><hr>";
?>