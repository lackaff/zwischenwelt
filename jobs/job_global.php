<?php

class Job_CalcPoints extends Job {
	protected function _run(){
	echo "generate points...<br>";
		$gAllUsers = sqlgettable("SELECT * FROM `user` ORDER BY `id`","id");
		$range=ceil(count($gAllUsers)/10);
		//echo "tick ".$gGlobal["ticks"]." / %10 ".($gGlobal["ticks"]%10)."<br>";
		
		$e = array_slice($gAllUsers,$range*($gGlobal["ticks"]%10),$range);
		echo "fetched $range users from \$gAllUsers<br>";
		$bpar = sqlgettable("SELECT `id`,`cost_stone`+`cost_food`+`cost_lumber`+`cost_metal`+`cost_runes` AS `costs` FROM `buildingtype` WHERE 1",'id');
		$upar = sqlgettable("SELECT `id`,`cost_stone`+`cost_food`+`cost_lumber`+`cost_metal`+`cost_runes` AS `costs` FROM `unittype` WHERE 1 ORDER BY `id`","id");
		$tpar = sqlgettable("SELECT `id`,`increment`,`basecost_stone`+`basecost_food`+`basecost_lumber`+`basecost_metal`+`basecost_runes` AS `costs` FROM `technologytype` WHERE 1 ORDER BY `id`","id");
		
		foreach ($e as $id=>$u){
			$gpts=getBuildingPts($u->id,$bpar);
			$mpts=getBasePts($u->id);
			$tpts=getTechPts($u->id,$tpar);
			$apts=getArmyPts($u->id,$upar);
			if ($gVerbose) echo "score uid ".$u->id." : buildingpoints=$gpts,miscpts=$mpts,techpts=$tpts,armypts=$apts<br>";
			$gpts+=$mpts;
			$gpts+=$tpts;
			sql("UPDATE `user` SET `general_pts`=".$gpts." , `army_pts`=".$apts." WHERE `id`=".$u->id);
			if($u->guildpoints<0)
				$gp=abs($u->guildpoints/intval($gGlobal['gp_pts_ratio']));
			else
				$gp=0;
			if($u->guild==kGuild_Weltbank && ($gpts+$apts+$gp)>intval($gGlobal['wb_max_gp']) && $u->id != kGuild_Weltbank_Founder){
				leaveGuild($u->id);
			}
		}
		
		$this->requeue(in_hours(time(),1));
	}
}

class Job_Weather extends Job {
	protected function _run(){
		if (!ExistGlobal("weather")){  // TODO : unhardcode
			profile_page_start("cron.php - weather",true);
			SetGlobal("weather",GetWeather($gWeatherUrl));
		}
		
		$this->requeue(in_mins(time(),30));
	}
}

class Job_Spells extends Job {
	protected function _run(){
		$spells = sqlgettable("SELECT * FROM `spell`");
		foreach($spells as $o) {
			$spell = GetSpellInstance($o->type,$o);
			$spell->Cron($dtime);
			unset($spell);
		}
		unset($spells);

		$this->requeue(in_mins(time(),1));
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