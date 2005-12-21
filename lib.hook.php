<?php

// the building $building gets an upgrade
function Hook_UpgradeBuilding($building){
	if($building->type == kBuilding_Farm && (($building->level%5)==0))
		growTerrainAroundPos($building->x,$building->y,1,kTerrain_Field,false,false,false);
}
// the building $building is created
function Hook_CreateBuilding($building){}
// the building $building is destroyed
function Hook_DestroyBuilding($building){}

?>