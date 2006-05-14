<?php
require_once("lib.main.php");
require_once("lib.army.php");
require_once("lib.technology.php");

// einwegzauber = wonder, gibt dem spieler $ownerid einen einwegzauber vom typ $spelltypeid
function GiveWonder		($spelltypeid,$userid=false) {
	global $gUser; if ($userid === false) $userid = $gUser->id;
	if (is_object($spelltypeid)) $spelltypeid = $spelltypeid->id;
	sql("INSERT INTO `wonder` SET ".arr2sql(array("user"=>$userid,"spelltype"=>$spelltypeid,"time"=>time())));
	DropOldWonders(CountWonders($userid)-GetWonderCapacity($userid),$userid);
}

function GetWonderCapacity ($userid=false) {
	global $gUser; if ($userid === false) $userid = $gUser->id;
	$tempellevels = intval(sqlgetone("SELECT SUM(`level`+1) FROM `building` WHERE `user` = ".intval($userid)." AND `type` = ".kBuilding_Temple));
	return 10 + floor($tempellevels / 10.0);
}
function CountWonders ($userid=false) {
	global $gUser; if ($userid === false) $userid = $gUser->id;
	return intval(sqlgetone("SELECT COUNT(*) FROM `wonder` WHERE `user` = ".intval($userid)));
}
function DropOldWonders ($num,$userid=false) {
	if ($num <= 0) return;
	$old_wonder_id_list = sqlgetonetable("SELECT `id` FROM `wonder` WHERE `user` = ".intval($userid)." ORDER BY `time` LIMIT ".intval($num));
	foreach ($old_wonder_id_list as $id) sql("DELETE FROM `wonder` WHERE `id` = ".$id);
}

// returns a twodimensional array with the spelltype-objects the user can cast, first-index : group, second-index : spelltype-id
function GetPossibleSpells ($userid=0,$groupbytarget=false) {
	global $gSpellType,$gTechnologyType,$gUser;
	if ($userid == 0) $userid = $gUser->id;
	$candospells = array();
	foreach ($gSpellType as $spelltype) {
		if (HasReq($spelltype->req_building,$spelltype->req_tech,$userid)) { // TODO : replace 0 by current spell-tech level ?
			if ($groupbytarget) 
					$group = $spelltype->target;
			else	$group = $spelltype->primetech ? $gTechnologyType[$spelltype->primetech]->group : 0;
			$candospells[$group][$spelltype->id] = $spelltype;
		}
	}
	return $candospells;
}


// constructs a spell instance, optionally with db object $o
function GetSpellInstance ($spelltype,$o=false) {
	global $gSpellType;
	if (!is_object($spelltype)) $spelltype = $gSpellType[intval($spelltype)];
	$class = "Spell_".strtr($spelltype->name,array("ä"=>"ae","ö"=>"oe","ü"=>"ue","-"=>"_","Ã¼"=>"ue","Ã¶"=>"oe"));
	if (!class_exists($class)) {
		?>
		FEHLER !
		Es gibt wohl ein Installationsproblem, es wurde versucht einen Zauberspruch names '<?=$class?>' zu laden, 
		der dazugehörige PHP Code wurde aber nicht gefunden.<br>
		Wenn in dem Namen merkwürdige Zeichen vorkommen, dann ist das wohl ein Problem mit Umlauten,Unicode,Collations oder sowas.<br>
		Wenn man sich damit nicht mit MySQL Konfiguration und Administration auskennt, am besten von Hand in der Datenbank
		in der Tabelle "spelltype" die Daten in der Spalte "name" korrigieren.
		<?php
		exit();
	}
	$spell = new $class();
	if ($o) $spell->SetObject($o);
	else { 
		$spell->spelltype = $spelltype;
		$spell->type = $spelltype->id;
	}
	return $spell;
}

function BanSpell ($spell_db_obj,$banner_userid=false) {
	global $gSpellType;
	if (!$spell_db_obj) return;
	if (!is_object($spell_db_obj)) $spell_db_obj = sqlgetobject("SELECT * FROM `spell` WHERE `id` = ".intval($spell_db_obj));
	$spell = GetSpellInstance($spell_db_obj->type,$spell_db_obj);
	$spell->Expire();
	$spelltype = $gSpellType[$spell->type];
	
	// send message to victim
	$owneruser = $spell->owner?sqlgetobject("SELECT `id`,`name` FROM `user` WHERE `id` = ".intval($spell->owner)):false;
	echo $spelltype->name.($owneruser?" von $owneruser->name":"")." gebannt !<br>";
	$spellreport = "Unser Zauber ".$spelltype->name." auf ($spell->x,$spell->y) wurde gebannt !<br>\n";
	$banner_user = $banner_userid?sqlgetobject("SELECT `id`,`name` FROM `user` WHERE `id` = ".intval($banner_userid)):false;
	if ($banner_user)
		$spellreport .= "Eure Magier konnten ".($banner_user->name)." als Verursacher identifizieren.<br>\n";
	sendMessage($spell->owner,0,"Bann (".$spelltype->name.",$spell->x,$spell->y)",$spellreport,kMsgTypeReport,FALSE);
}

// returns all spell-instances with type in typelist, that cover the given pos
// $typelist is array(spelltypeid,spelltypeid,...) 
function GetSpellsInArea ($x,$y,$typelist) {
	$x = intval($x);
	$y = intval($y);
	$typelist = intarray($typelist);
	$res = array();
	$list = sqlgettable("SELECT * FROM `spell` WHERE `type` IN (".implode(",",$typelist).")
		AND `x` >= ('$x') - `radius` AND `y` >= ('$y') - `radius`
		AND `x` <= ('$x') + `radius` AND `y` <= ('$y') + `radius`
		ORDER BY `id`");
	foreach ($list as $o) 
		if (($o->x-$x)*($o->x-$x) + ($o->y-$y)*($o->y-$y) <= $o->radius*$o->radius) 
			$res[] = GetSpellInstance($o->type,$o);
	return $res;
}

//********************[ Spell ]****************************************************************
//basic spell class, handles casting-dice-roll
//*********************************************************************************************

class Spell {
	// transfer vars from sql-object $o to $this
	function SetObject ($o) {
		assert($o);
		$o = get_object_vars($o);
		foreach($o as $name=>$value) $this->$name = $value;
		$this->GetHelpers();
	}
	
	// sets up helper vars
	function GetHelpers() {
		global $gSpellType;
		$this->spelltype = $gSpellType[$this->type];
		$building = sqlgetobject("SELECT * FROM `building` WHERE `x` = ".intval($this->x)." AND `y` = ".intval($this->y));
		if ($building) $this->targetuser = sqlgetobject("SELECT * FROM `user` WHERE `id` = ".intval($building->user));
		else $this->targetuser = false;
		
		//if ($this->targettype == MTARGET_PLAYER)
		//	$this->targetuser = sqlgetobject("SELECT * FROM `user` WHERE `id` = ".intval($this->target));
		$this->level = $this->GetLevel();
		$this->radius = $this->GetRadius();
	}
	
	// GetPrimeTechnologyLevel
	function GetLevel ($owner=false,$techtypeid=false) {
		if (!$owner) $owner = $this->owner;
		if (!$techtypeid) $techtypeid = $this->spelltype->primetech;
		return intval(sqlgetone("SELECT `level` FROM `technology` WHERE `type`=".intval($techtypeid)." AND `user`=".intval($owner)));
	}
	
	// high difficulty is bad, override me =) (algorithm pattern?)
	function GetDifficulty ($spelltype,$mages,$userid) {
		$req = ParseReqForATechLevel($spelltype->req_tech);
		$f = 1;
		foreach($req as $r) 
			$f = max($f,$r->level+2);
		return $f;
	}
	
	// returns array($success,$penalty,$msg,$color), first two floats, third is message, penalty is used for extra cost
	function GetRandomSuccess ($spelltype,$mages,$userid) {
		// high entropy is bad
		$entropy = 0;
		$entropy += 10.0*$this->GetDifficulty($spelltype,$mages,$userid);
		$entropy -= $mages/2.0; // 20 magier gleichen die schwierigkeit einer tech-stufe aus
		$baseentropy = $entropy;
		$entropy += rand(0,100);
		// 10 zu 1 chance bei versagen einen patzer zu machen
		
		 // todo : document in wiki
			 if ($entropy > 90.0)	$res = (rand(0,10)==0)?array(-1.0,2.2,"Patzer!","red"):array( 0.0,1.7,"versagt","red");
		else if ($entropy > 50.0)	$res = array( 0.9,1.3,"knapp geschafft","#228800");
		else if ($entropy > 30.0)	$res = array( 1.0,1.0,"geschafft","#00AA00");
		else						$res = array( 1.5,0.8,"gut geschafft","#00CC00");
		
		$costmodtxt = (($res[1]>=1.0)?"+":"").($res[1]*100 - 100)."%";
		
		echo "<font color='".$res[3]."'>".
			"<u><b>".$res[2]."</b></u>, Schwierigkeit ".sprintf("%0.1f",$baseentropy/10)." + Zufall = ".sprintf("%0.1f",$entropy/10).", ".
			"Modifikator = ".sprintf("%0.1f",max(0,$res[0])).", Kosten : ".$costmodtxt."</font><br>";
		return $res;
	}
	
	// pay ressources and mana
	function PaySpell ($spelltype,$towerid=0,$penalty=1,$userid=0) { // $userid=-1 means no res-cost
		global $gUser,$gRes;
		if ($userid == 0) $userid = $gUser->id;
		if ($towerid == 0 && $userid == -1) return; // no cost at all
		
		$cost = array();
		$rescost = array();
		foreach ($gRes as $f) {
			$cost[] = $spelltype->{"cost_$f"}*$penalty;
			$rescost[] = "`$f` = `$f` - ".($spelltype->{"cost_$f"}*$penalty);
		}

		TablesLock();
		if ($userid > 0) {
			if ($userid == $gUser->id) {
				echo cost2txt($cost,$gUser);
				if ($towerid > 0) echo " <img src='".g("res_mana.gif")."'><font color='black'>".($spelltype->cost_mana*$penalty)."</font>";
				echo "<br>";
			}
			sql("UPDATE `user` SET ".implode(" , ",$rescost)." WHERE `id`=".intval($userid)." LIMIT 1");
			if ($userid == $gUser->id)
				$gUser = sqlgetobject("SELECT * FROM `user` WHERE `id` = ".$gUser->id);
		}
		if ($towerid > 0) {
			sql("UPDATE `building` SET `mana`=`mana`-".($spelltype->cost_mana*$penalty)." WHERE `id`=".intval($towerid)." LIMIT 1");
		}
		TablesUnlock();
	}
	
	// no need to override this method in the actual spells, use Birth($success) instead
	// $owner=-1 means no res-cost
	// $successOverride=true means spell is forced to a normal success
	function Cast ($spelltype,$x,$y,$owner=0,$towerid=0,$successOverride=false,$nocost=false) {
		global $gUser,$gRes;
		if (is_object($owner)) $owner = $owner->id;
		if ($owner == 0) $owner = $gUser->id;
		
		//LogMe($spelltype->target,NEWLOG_TOPIC_MAGIC,NEWLOG_MAGIC_HELP_TARGET,$x,$y,$spelltype->target,$gSpellType[$type]->name,$owner);
		
		// check user
		if ($owner > 0) {
			$user = sqlgetobject("SELECT * FROM `user` WHERE `id` = ".intval($owner));
			
			if (!$nocost) {
				// check tech
				// todo : replace last 0 by current spell-tech level ?
				if (!HasReq($spelltype->req_building,$spelltype->req_tech,$owner)) {
					echo "Technologie nicht erreicht<br>";
					return false;
				}
				
				// check res
				foreach($gRes as $n => $f) if ($user->$f < $spelltype->{"cost_$f"}) {
					echo "nicht genug $n<br>";
					return false;
				}
			}
		}
		
		// check tower
		if ($towerid > 0) {
			// check mana
			if (sqlgetone("SELECT `mana` FROM `building` WHERE `id`=".intval($towerid)) < $spelltype->cost_mana) {
				echo "nicht genug Mana<br>";
				return false;
			}
		
			// check mages
			if (($mages = intval(sqlgetone("SELECT SUM(`amount`) FROM `unit` WHERE `type` = ".kUnitType_TowerMage." AND `building`=".intval($towerid)))) < 1) {
				echo "nicht genug Magier<br>";
				return false;
			}
		} else $mages = 0;
		
		
		// check target
		$this->target = 0;
		if ($spelltype->target == MTARGET_PLAYER || $spelltype->target == MTARGET_ARMY) {
			$this->target = $this->GetTargetID($spelltype->target,$x,$y,$owner);
			if (!$this->target) {
				echo "kein Ziel gefunden<br>";
				return false;
			}
		}
		
		if ($successOverride) {
			// force a normal success
			list($success,$penalty,$msg) = array( 1.0,1.0,"geschafft","#00AA00");
		} else {
			// throw the dice...
			list($success,$penalty,$msg) = $this->GetRandomSuccess($spelltype,$mages,$owner);
		}
		
		// res and mana cost
		if (!$nocost) {
			$this->PaySpell($spelltype,$towerid,$penalty,$owner);
		}
		
		// start the spell
		$this->towerid = $towerid; // only available in birth()
		$this->mages = $mages; // only available in birth()
		$this->x = $x;
		$this->y = $y;
		$this->type = $spelltype->id;
		$this->targettype = $spelltype->target;
		$this->owner = $owner;
		$this->GetHelpers();
		$this->mod = $success;
		$this->lasts = time() + $this->spelltype->basetime*$success;
		
		// versuche zauber-konter
		if ($spelltype->target == MTARGET_PLAYER) {
			if ($this->TryCounter($this->target)) {
				echo "Zauber wurde gekontert !<br>";
				return false;
			}
		}
		
		// attempt to correct player position to hq (needed to be able to ban evil spells like "pest" and "duerre" by casting ban on hq)
		if ($spelltype->target == MTARGET_PLAYER && $this->targetuser) {
			$hq = sqlgetobject("SELECT * FROM `building` WHERE `type` = ".kBuilding_HQ." AND `user` = ".intval($this->targetuser->id));
			if ($hq) {
				$this->x = $hq->x;
				$this->y = $hq->y;
			}
		}
		return $this->Birth($success);
	}
	
	// get target object
	function GetTargetID ($targettype,$x,$y,$owner) {
		if (is_object($owner)) $owner = $owner->id;
		switch($targettype) {
			case MTARGET_PLAYER: 
				$r = sqlgetone("SELECT `user` FROM `building` WHERE `x`=".intval($x)." AND `y`=".intval($y));
				if ($r) return $r;
				return sqlgetone("SELECT `user` FROM `army` WHERE `x`=".intval($x)." AND `y`=".intval($y));
			case MTARGET_AREA:
				return false;
			case MTARGET_ARMY:
				return sqlgetone("SELECT `id` FROM `army` WHERE `x`=".intval($x)." AND `y`=".intval($y));
			default:
				return false;
		}
	}
	
	// will be called once upon casting the spell, $success < 0 indicates a bad failure, which could cause negative effects
	// $success < 0 -> patzer  , $success == 0 -> normal failure
	function Birth ($success) { return $success > 0; } // be sure to call parent::Birth($dtime); !
	
	//use this if the spell lasts longer, $dt is the time in sec since last cron
	function Cron ($dtime) { assert($dtime>0); } // be sure to call parent::Cron($dtime); !

	// ends the spell, called only once, and only for Spell_Cron and derivates
	function Expire () {} // be sure to call parent::Expire(); !
	
	//tells the user in less words the effekt of the spell
	function Effect () {}
	
	// determine spell radius
	function GetRadius () { return 0; }
	
	// returns true if the target-user was able to counter the spell (NOT YET IMPLEMENTED)
	function TryCounter ($userid) { return false; }
}

//********************[ CronSpell ]****************************************************************
// class for longer-lasting spells, manages db-object
// standard accumulation : per (type,target) , mod = min(2,max(old,new) + 5%) , last = min(2*basetime,max(old,new) + a little bit)
//*********************************************************************************************

class Spell_Cron extends Spell {
	function Birth ($success) {
		// $success < 0 -> patzer , $success == 0 -> normal failure
		// $success <= 0 : failed => no storage
		if (!parent::Birth($success)) return false;
		
		$this->id = 0;
		$this->accumulated = $this->AccumulationCheck(); // only available in birth !
		if (!$this->accumulated) {
			// only produce new object if accumulation into an old object was not possible
			$newspell = false; // object to be stored database
			$newspell->x = $this->x;
			$newspell->y = $this->y;
			$newspell->type = $this->type;
			$newspell->target = $this->target;
			$newspell->targettype = $this->targettype;
			$newspell->owner = $this->owner;
			$newspell->mod = $this->mod;
			$newspell->lasts = $this->lasts;
			$newspell->radius = $this->radius;
			sql("INSERT INTO `spell` SET ".obj2sql($newspell));
			$this->id = mysql_insert_id();
		}
		
		return true;
	}
	
	//use this if the spell lasts longer, $dt is the time in sec since last cron
	function Cron ($dtime) {
		parent::Cron($dtime);
		if (time() > $this->lasts) $this->Expire();
	}
	
	// return true if an older spell object was strengthened, override me =)
	function AccumulationCheck () {
		$this->id = 0;
		TablesLock();
		$o = sqlgetobject("SELECT * FROM `spell` WHERE 
			`type`=".intval($this->type)." AND 
			`target`=".intval($this->target));
		if ($o) {
			$this->id = $o->id;
			$o->x = $this->x;
			$o->y = $this->y;
			$o->mod = min(2,max($this->mod,$o->mod) + ($o->mod+$this->mod)/20);
			$o->lasts = min(time()+$this->spelltype->basetime*2,max($this->lasts,$o->lasts) + abs(($o->lasts+$this->lasts)/2 - time())/10);
			sql("UPDATE `spell` SET ".obj2sql($o)." WHERE `id`=".$o->id);
			$this->SetObject($o); // take over variables from the old object
		}
		TablesUnlock();
		return $this->id;
	}
	
	//removes the spell from db, this is the last method called
	function Expire () {
		parent::Expire();
		assert($this->id);
		$this->id = intval($this->id);
		if (!$this->id) return;
		sql("DELETE FROM `spell` WHERE `id`=".$this->id);
		$affected_army_ids = sqlgetonetable("SELECT `army` FROM `unit` WHERE `spell` = ".$this->id." GROUP BY `army`");
		sql("DELETE FROM `unit` WHERE `spell`=".$this->id);
		sql("DELETE FROM `item` WHERE `spell`=".$this->id);
		foreach ($affected_army_ids as $armyid) {
			if (sqlgetone("SELECT 1 FROM `unit` WHERE `army` = ".$armyid." AND `amount` >= 1")) continue;
			// otherwise the army vanishes...
			cArmy::DeleteArmy($armyid);
			// TODO : message
		}
	}
}

//********************[ Spell_Once_Per_User ]********************************************************
//a spell that can have only one active instance per user, accumulates differently
// accumulation : per (type,owner) , mod = max(old,new) , last = max(old,new)
//*********************************************************************************************

class Spell_Once_Per_User extends Spell_Cron {	
	// return true if an older spell object was strengthened, override me =)
	function AccumulationCheck () {
		$this->id = 0;
		TablesLock();
		$o = sqlgetobject("SELECT * FROM `spell` WHERE 
			`type`=".intval($this->type)." AND 
			`owner`=".intval($this->owner));
		if ($o) {
			$this->id = $o->id;
			$o->x = $this->x;
			$o->y = $this->y;
			$o->mod = max($this->mod,$o->mod);
			$o->lasts = max($this->lasts,$o->lasts);
			sql("UPDATE `spell` SET ".obj2sql($o)." WHERE `id`=".$o->id);
			$this->SetObject($o); // take over variables from the old object
		}
		TablesUnlock();
		return $this->id;
	}
}

//********************[ Spell_Production ]********************************************************
//a spell that changes a production, set $this->res in constructor !
//*********************************************************************************************
class Spell_Production extends Spell_Cron {	
	function GetProduced ($dtime) { // override me for non-res (pop,maxpop,maxres,...)
		if (!$this->targetuser) return 0;
		return ( ((float)$this->targetuser->{"worker_".$this->res}) * 0.01 *
				 ((float)$this->targetuser->pop)  *
				 ((float)$this->spelltype->baseeffect) *
				 ((float)$this->mod) * ($dtime/3600.0));
	}
	
	function Cron($dtime) {
		parent::Cron($dtime);
		if (!$this->targetuser) return;
		assert($dtime>0);
		$produced = $this->GetProduced($dtime);
		global $gVerbose;
		if (!isset($gVerbose) || $gVerbose) echo "produced $produced extra ".$this->res." for userid ".$this->targetuser->id." (".($produced*60.0)."/h)<br>";
		sql("UPDATE `user` SET `".$this->res."` = `".$this->res."`+($produced) WHERE 
			`".$this->res."`+($produced) >= 0 AND `id`=".intval($this->targetuser->id));
	}

	function Effect() {
		$produced = round($this->GetProduced(3600),1);
		return $this->restext.": <font color='".($produced>0?"green":"red")."'>$produced pro Stunde</font>";
	}
}



//********************[ Spell_Instant_Damage ]********************************************************
// instantly damages army, set $this->basedmg,report_topic,terrain_change in constructor !
//*********************************************************************************************
class Spell_Instant_Damage extends Spell {
	function Birth ($success) {
		if (!parent::Birth($success)) return false;
		$cond = "`x` = ".$this->x." AND `y` = ".$this->y;
	
		// create terrain
		if ($this->terrain_change) {
			$changeable_terrain = array(kTerrain_Swamp,kTerrain_YoungForest,kTerrain_TreeStumps,kTerrain_Forest,kTerrain_Grass,kTerrain_Field,kTerrain_Flowers);
			$terraintype = cMap::StaticGetTerrainAtPos($this->x,$this->y);
			if (in_array($terraintype,$changeable_terrain)) {
				sql("REPLACE INTO `terrain` SET ".arr2sql(array("type"=>$this->terrain_change,"x"=>$this->x,"y"=>$this->y)));
			}
		}
		
		$dmg = floor($this->basedmg * $this->level * $this->mod);
		
		global $gUnitType;
		$spellreport = $this->Effect()."<br>";
		$spellreport .= "Schaden : $dmg<br>";
		$army = sqlgetobject("SELECT * FROM `army` WHERE `type` = ".kArmyType_Normal." AND `x` = ".$this->x." AND `y` = ".$this->y);
		if ($army) {
			$victim_user = sqlgetobject("SELECT * FROM `user` WHERE `id` = ".intval($army->user));
			$spellreport .= "Die Armee $army->name von $victim_user->name wurde getroffen, Verluste : <br>\n";
			
			// todo : der ganze lock block dient nur dem armee-beschädigen, die teile kommen aus dem cron fight, KAPSEL MICH !
			TablesLock();
			$army->units = cUnit::GetUnits($army->id);
			$army->vorher_units = $army->units;
			$army->units = cUnit::GetUnitsAfterDamage($army->units,$dmg,$army->user);
			$army->lost_units = cUnit::GetUnitsDiff($army->vorher_units,$army->units);
			foreach ($army->lost_units as $o)
				$spellreport .= "<img src='".g($gUnitType[$o->type]->gfx)."'>".floor($o->amount)."<br>\n";
			// TODO : terrainkills stimmt hier nicht so richtig, z.b. wenn kein terrain da ist, TODO : einheitliche damage funktion
			sql("UPDATE `terrain` SET `kills`=`kills`+".round(abs(cUnit::GetUnitsSum($army->lost_units)))." WHERE `x`=".$army->x." AND `y`=".$army->y);
			$army->size = cUnit::GetUnitsSum($army->units);
			if ($army->size >= 1.0) 
					cUnit::SetUnits($army->units,$army->id);
			else { 
				cArmy::DeleteArmy($army,false,$this->report_dead_why);
				$spellreport .= "Die Armee wurde VERNICHTET !<br>\n";
			}
			TablesUnlock();
			
			if ($victim_user) {
				// send message to victim
				$myspellreport = $spellreport;
				$owneruser = sqlgetobject("SELECT `id`,`name` FROM `user` WHERE `id` = ".intval($this->owner));
				if ($owneruser)
					$myspellreport .= "Eure Magier konnten ".($owneruser->name)." als Verursacher identifizieren.<br>\n";
				sendMessage($victim_user->id,0,$this->report_topic." bei ($army->x,$army->y)",$myspellreport,kMsgTypeReport,FALSE);
			}
		}
		
		echo $spellreport;
		return true;
	}
}


require_once("lib.spellbook.php");
?>
