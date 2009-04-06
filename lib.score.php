<?php
// $costar kann aus globalen vars berechnet werden -> weniger querries , rentiert sich der umbau ?
function getBuildingPts($uid,$costar=0){
	if(!is_array($costar))
		$costar= sqlgettable("SELECT `id`,`cost_stone`+`cost_food`+`cost_lumber`+`cost_metal` AS `costs` FROM `buildingtype` WHERE 1",'id');
	$buildings= sqlgettable("SELECT COUNT(`id`) AS `anzahl`,`type`,SUM(`level`) AS `levelsum` FROM `building` WHERE `user`=".intval($uid)." AND `type`!=4 GROUP BY `type`"); // 4 : unhardcode me
	$points=0;
	foreach ($buildings as $building){
		// ugly hack to ignore broken tech entries
		if(!isset($costar[$building->type]))continue;
		
		$bp=$costar[$building->type]->costs/100;
		$points+=round($bp*$building->anzahl,0);
		$lschnitt=intval($building->levelsum/$building->anzahl);
		$l=0;
		for($i=1;$i<=$lschnitt;$i++)
			$l += cBuilding::calcUpgradeCostsMod($i);
		$points+=round($l*$building->anzahl*$bp/10,0);	
	}
	return $points;
}

function getArmyPts($uid,$costar=0){
	if(!is_array($costar))
		$costar = sqlgettable("SELECT `id`,`cost_stone`+`cost_food`+`cost_lumber`+`cost_metal`+`cost_runes` AS `costs` FROM `unittype` WHERE 1 ORDER BY `id`","id");
	$points=0;
	$uid=intval($uid);
	$unitsb= sqlgettable("SELECT SUM(m.`amount`) AS totala,m.`type` AS type FROM `unit` as m, `building` as b WHERE m.`building`<>0 AND b.`user`=".$uid." AND m.`building`=b.`id` GROUP BY m.`type`");
	foreach($unitsb as $group){
		// ugly hack to ignore broken tech entries
		if(!isset($costar[$group->type]))continue;
		
		$up=$costar[$group->type]->costs/200;
		$points+=round($group->totala/400 + $group->totala*$up,0);
	}
	$unitsb= sqlgettable("SELECT SUM(m.`amount`) AS totala,m.`type` AS type FROM `unit` as m, `army` as b WHERE m.`building`=0 AND b.`user`=".$uid." AND m.`army`=b.`id` GROUP BY m.`type`");
	foreach($unitsb as $group){
		// ugly hack to ignore broken tech entries
		if(!isset($costar[$group->type]))continue;
		
		$up=$costar[$group->type]->costs/200;
		$points+=round($group->totala/200 + $group->totala*$up,0);
	}
	$armies= sqlgettable("SELECT `frags` FROM `army` WHERE `user`=".$uid);
	foreach($armies as $army)
		$points+=round($army->frags/1000,0);
	return $points;
}

function getTechPts($uid,$costar=0){
	if(!is_array($costar))
		$costar = sqlgettable("SELECT `id`,`increment`,`basecost_stone`+`basecost_food`+`basecost_lumber`+`basecost_metal`+`basecost_runes` AS `costs` FROM `technologytype` WHERE 1 ORDER BY `id`","id");
	$techs= sqlgettable("SELECT `level`,`type` FROM `technology` WHERE `user`=".intval($uid)." AND `level`>0","type");
	$points=0;
	foreach($techs as $t){
		// ugly hack to ignore broken tech entries
		if(!isset($costar[$t->type]))continue;
		
		$tp=$costar[$t->type]->costs/20;
		$tl=0;
		$level=$t->level;
		for($i=1;$i<=$level;$i++)
			$tl+=$i;
		$l=$level+$tl*$costar[$t->type]->increment/2;
		$points+=round($tp*$l,0);
	}
	return $points;
}

function getBasePts($uid){
	return round(sqlgetone("SELECT `guildpoints`/1000+(`max_lumber`+`max_stone`+`max_food`+`max_metal`+`max_runes`)/50000+`maxpop`/100 FROM `user` WHERE `id`=".intval($uid)),0);
}
?>
