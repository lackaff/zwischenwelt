<?php

class Job_Stats extends Job {
	protected function _run(){
		profile_page_start("cron.php - stats");
		echo "collection stats ...";
		include(BASEPATH."/stats.php");
		echo "done\n\n";
		profile_page_end();
		
		$this->requeue(in_hours(time(),12));
	}
}

//TODO remove me cause i am a ugly quickfix
class Job_Firefix extends Job {
	protected function _run(){
		profile_page_start("cron.php - firecount workaround");
		//recalc fire count
		$t_fires = sqlgettable("SELECT COUNT( * ) AS `count` , b.`user`
		FROM `fire` f, `building` b
		WHERE f.`x` = b.`x`
		AND f.`y` = b.`y`
		GROUP BY b.`user`");
	
		sql("UPDATE `user` SET `buildings_on_fire`=0");
		foreach($t_fires as $x){
				echo "player $x->user has $x->count fires<br>\n";
			sql("UPDATE `user` SET `buildings_on_fire`=$x->count WHERE `id`=$x->user");
		}
		unset($t_fires);
		profile_page_end();
		
		$this->requeue(in_hours(time(),1));
	}
}

class Job_RemoveGuildRequests extends Job {
	protected function _run(){
		echo "remove guild requests if user has a guild...<br>";
		$t = sqlgettable("SELECT r.`id`,u.`name` FROM `guild_request` r,`user` u WHERE r.`user`=u.`id` AND u.`guild`>0");
		foreach($t as $x){
			echo "remove request from $x->name<br>";
			sql("DELETE FROM `guild_request` WHERE `id`=".intval($x->id));
		}
		echo "done<br>";

		$this->requeue(in_hours(time(),1));
	}
}

class Job_PurgeOldJobs extends Job {
	protected function _run(){
		sql("DELETE FROM `job` WHERE 
			`locked`=2 AND 
			`time`<".time());

		$this->requeue(in_hours(time(),1));
	}
}

class Job_PurgeOldLogs extends Job {
	protected function _run(){
		echo "remove old log<br>";
		$time = time();
		sql("DELETE FROM `newlog` WHERE $time-`time`>60*60*24");  // TODO : unhardcode
		
		$this->requeue(in_hours(time(),1));
	}
}

class Job_UpgradesFix extends Job {
	protected function _run(){
		sql("UPDATE `building` SET `upgrades`=255 WHERE `upgrades`>255");
		
		$this->requeue(in_mins(time(),10));
	}
}

class Job_RemoveZeroItems extends Job {
	protected function _run(){
		//remove zero items
		sql("DELETE FROM `item` WHERE `amount` = 0");
		
		$this->requeue(in_mins(time(),30));
	}
}

?>