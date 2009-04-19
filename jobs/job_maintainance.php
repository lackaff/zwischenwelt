<?php

class Job_Stats extends Job {
	protected function _run(){
		profile_page_start("cron.php - stats");
		echo "collection stats ...";
		include("stats.php");
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
			`starttime`>0 AND 
			`endtime`>0 AND 
			`locked`>0 AND 
			`time`<".time());

		$this->requeue(in_hours(time(),1));
	}
}

class Job_Bier extends Job {
	protected function _run(){
		$t = sqlgetone("SELECT 1 FROM `title` WHERE `title`='Brauereimeister'");
		if(empty($t)){
			$o = null;
			$o->title = "Brauereimeister";
			$o->time = time();
			$o->image = "title/title-bier.png";
			$o->text = "Der König der Biere";
			sql("INSERT INTO `title` SET ".obj2sql($o));
		}
		$u = sqlgetone("SELECT t.`user` FROM `technology` t,`user` u WHERE u.`id`=t.`user` AND u.`admin`=0 AND t.`level`>0 AND `type`=".kTech_Bier." ORDER BY `level` DESC LIMIT 1");
		if($u>0)sql("UPDATE `title` SET `user`=".intval($u)." WHERE `title`='Brauereimeister'");

		$this->requeue(in_hours(time(),1));
	}
}

?>