<?php

require_once(BASEPATH."/lib.score.php");
require_once(BASEPATH."/lib.weather.php");

class Job_CalcPoints extends Job {
	protected function _run(){
	echo "generate points...<br>";
		$users = sqlgettable("SELECT * FROM `user` ORDER BY `id`","id");
		$range=ceil(count($users)/10);
		//echo "tick ".GetGlobal("ticks")." / %10 ".(GetGlobal("ticks")%10)."<br>";
		
		$e = array_slice($users,$range*(GetGlobal("ticks")%10),$range);
		echo "fetched $range users from \$$users<br>";
		$bpar = sqlgettable("SELECT `id`,`cost_stone`+`cost_food`+`cost_lumber`+`cost_metal`+`cost_runes` AS `costs` FROM `buildingtype` WHERE 1",'id');
		$upar = sqlgettable("SELECT `id`,`cost_stone`+`cost_food`+`cost_lumber`+`cost_metal`+`cost_runes` AS `costs` FROM `unittype` WHERE 1 ORDER BY `id`","id");
		$tpar = sqlgettable("SELECT `id`,`increment`,`basecost_stone`+`basecost_food`+`basecost_lumber`+`basecost_metal`+`basecost_runes` AS `costs` FROM `technologytype` WHERE 1 ORDER BY `id`","id");
		
		foreach ($e as $id=>$u){
			$gpts=getBuildingPts($u->id,$bpar);
			$mpts=getBasePts($u->id);
			$tpts=getTechPts($u->id,$tpar);
			$apts=getArmyPts($u->id,$upar);
			echo "score uid ".$u->id." : buildingpoints=$gpts,miscpts=$mpts,techpts=$tpts,armypts=$apts<br>";
			$gpts+=$mpts;
			$gpts+=$tpts;
			sql("UPDATE `user` SET `general_pts`=".$gpts." , `army_pts`=".$apts." WHERE `id`=".$u->id);
			if($u->guildpoints<0)
				$gp=abs($u->guildpoints/intval(GetGlobal('gp_pts_ratio')));
			else
				$gp=0;
			if($u->guild==kGuild_Weltbank && ($gpts+$apts+$gp)>intval(GetGlobal('wb_max_gp')) && $u->id != kGuild_Weltbank_Founder){
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
		if(!ExistGlobal("last_spell_calc")){
			SetGlobal("last_spell_calc",T);
		}
		
		$last = GetGlobal("last_spell_calc");
		
		if(T - $last > 0){
			$dtime = (T - $last);
			echo "DT: $dtime\n";

			$spells = sqlgettable("SELECT * FROM `spell`");
			foreach($spells as $o) {
				$spell = GetSpellInstance($o->type,$o);
				$spell->Cron($dtime);
				unset($spell);
			}
			unset($spells);

			SetGlobal("last_spell_calc",T);
		}
		
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

class Job_Weltbank extends Job {
	protected function _run(){
		global $gResFields;
		
		if(!ExistGlobal("last_weltbank")){
			SetGlobal("last_weltbank",T);
		}
		
		$last = GetGlobal("last_weltbank");
		
		if(T - $last > 0){
			$dtime = (T - $last);
			echo "DT: $dtime\n";
			
			// weltbank
		
			//TODO .. dies produziert zu viele sql querys 
			
			$users = sqlgettable("SELECT * FROM `user` ORDER BY `id`","id");
			foreach ($users as $id=>$u){
				$id=$u->id;
				if(($u->general_pts+$u->army_pts)<intval(GetGlobal('wb_paybacklimit')))continue;
				$w=floatval(sqlgetone("SELECT `value` FROM `guild_pref` WHERE `var`='schulden_".$u->id."'"));
				if($w==0)continue;
				foreach ($gResFields as $r){
					$prod="prod_$r";
					if($u->{$prod}>0){
						$radd=intval(GetGlobal('wb_payback_perc'))*$u->{$prod}/100/3600*$dtime;
					}
					sql("UPDATE `guild` SET `$r`=`$r`+($radd) WHERE `id`=".kGuild_Weltbank);
					sql("UPDATE `user` SET `guildpoints`=`guildpoints`+($radd) WHERE `id`=".$u->id);
					echo "user ".$u->name." (".$u->id.") payes res to guild ".kGuild_Weltbank." [$r] $radd (ressources left to pay: $w)<br>\n";
					$w-=$radd;
				}
				if($w<1)
					sql("DELETE FROM `guild_pref` WHERE `guild`=".kGuild_Weltbank." AND `var`='schulden_$id' OR `var`='schulden_$id'");
				else
					sql("UPDATE `guild_pref` SET `value`='$w' WHERE `var`='schulden_$id'");
			}
			
			SetGlobal("last_weltbank",T);
		}
				
		$this->requeue(in_mins(time(),5));
	}
}

class Job_Quest extends Job {
	protected function _run(){
		QuestTrigger_CronStep();
				
		$this->requeue(in_mins(time(),1));
	}
}

class Job_GroupItems extends Job {
	protected function _run(){
		$t = sqlgettable("SELECT `x`,`y`,`type`,sum(`amount`) as `amount`,
			count(`id`) as `ids`,
			`id` FROM `item` WHERE quest=0 GROUP BY x,y,type HAVING ids > 1 LIMIT 50");
		
		foreach($t as $x){
			TablesLock();
			var_dump($x);
			sql("DELETE FROM `item` WHERE 
				`x`=".$x->x." AND 
				`y`=".$x->y." AND 
				`quest`=0 AND
				`type`=".$x->type." AND `id`!=".$x->id);
			sql("UPDATE `item` SET `amount`=".$x->amount." WHERE `id`=".$x->id);
			TablesUnlock();
		}
		
		$this->requeue(in_mins(time(),15));
	}
}
?>