<?php

class Job_SeamonsterSpawn extends Job {
	protected function _run(){
		$players = sqlgetone("SELECT COUNT(*) FROM `user` LIMIT 1");
		// TODO unhardcode me
		$seemonsterids = "(59,60,61,62,63)";
		$watertypeids = "(2,6,18)";
		$seemonsterunits_per_player = 1000;
		
		$seemonsteramount = sqlgetone("SELECT SUM(`amount`) FROM `unit` WHERE `type` IN $seemonsterids LIMIT 1");
		if(empty($seemonsteramount))$seemonsteramount = 0;
		
		$spawnpos = sqlgetobject("SELECT `x`,`y` FROM `terrain` WHERE `type` IN $watertypeids ORDER BY RAND() LIMIT 1");
		echo "[players=$players seemonsteramount=$seemonsteramount x=$spawnpos->x y=$spawnpos->y]<br>\n";
		// should i spawn monsters, master?
		if($seemonsteramount < ($players * $seemonsterunits_per_player)){
			echo "from the deep they shall come!<br>\n";
			
			// randomly select type
			$spawntype = 59 + rand(0,4);
			// and amount
			$spawncount = rand($seemonsterunits_per_player / 2, $seemonsterunits_per_player);
			
			$flags = kArmyFlag_Wander | kArmyFlag_RunToEnemy | kArmyFlag_AutoAttack;
			
			$newmonster = cArmy::SpawnArmy($spawnpos->x,$spawnpos->y,cUnit::Simple($spawntype,$spawncount),
				false,kArmyType_Normal,0,0,0,true,$flags);
			if ($newmonster) echo "Spawned $spawncount ".$gUnitType[$spawntype]->name." at $newmonster->x,$newmonster->y <br>";
			else echo "spawn of $spawncount ".$gUnitType[$spawntype]->name." failed<br>";
		}
		
		$this->requeue(in_mins(time(),5));
	}
}

?>
