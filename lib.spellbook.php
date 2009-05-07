<?php
require_once("constants.php");
require_once("lib.technology.php");
require_once("lib.spells.php");
require_once("lib.text.php");


// TODO : böse zauber : eintrag ins gildenlog

//********************[ Spell_Production ]*****************************************************
// Production Modifications
//*********************************************************************************************


// increase production
class Spell_Zauberwald extends Spell_Production { 
	function Spell_Zauberwald() { $this->res = "lumber"; $this->restext = "<img src='".g("res_lumber.gif")."'>"; }
}
class Spell_Steinreich extends Spell_Production {
	function Spell_Steinreich() { $this->res = "stone"; $this->restext = "<img src='".g("res_stone.gif")."'>";  }
}
class Spell_FruchtbaresLand extends Spell_Production {
	function Spell_FruchtbaresLand() { $this->res = "food"; $this->restext = "<img src='".g("res_food.gif")."'>"; }
}
class Spell_Erzbaron extends Spell_Production {
	function Spell_Erzbaron() { $this->res = "metal"; $this->restext = "<img src='".g("res_metal.gif")."'>"; }
}


//increases grow of population
class Spell_LoveAndJoy extends Spell_Production {
	function Birth ($success) {
		if (!parent::Birth($success)) return false;
		return true;
	}  
	function Spell_LoveAndJoy() { $this->res = "pop"; $this->restext = "<img src='".g("pop-r%R%.png")."'>"; }  
	function GetProduced ($dtime) { // override for non-res boosting
		if (empty($this->targetuser)) return 0;
		return ($dtime*40.0/3600.0+$this->level*0.005*$this->targetuser->pop*$dtime/3600.0)*$this->mod;
	}
}

// decreases food-production
class Spell_Duerre extends Spell_Production {
	function Spell_Duerre() { $this->res = "food"; $this->restext = "<img src='".g("res_food.gif")."'>"; }
	function Birth ($success) {
		if (!parent::Birth($success)) return false;
		$killed = sqlgettable("SELECT * FROM `spell` WHERE `type` = ".kSpellType_FruchtbaresLand." AND `target` = ".$this->target);
		foreach ($killed as $kill) BanSpell($kill,$this->owner);
		
		// send message to victim
		$spellreport = "Eine Dürre plagt das Land, die Nahrungsproduktion ist starkt gesunken.<br>\n";
		$owneruser = sqlgetobject("SELECT `id`,`name` FROM `user` WHERE `id` = ".intval($this->owner));
		if ($owneruser){
			$spellreport .= "Eure Magier konnten ".($owneruser->name)." als Verursacher identifizieren.<br>\n";
        } else $owneruser->id = 0;
        GuildLogMeShort($this->x,$this->y,$owneruser->id,$this->target,"Zauber",($this->spelltype->name)." wurde gezaubert");
		sendMessage($this->target,0,"Dürre",$spellreport,kMsgTypeReport,FALSE);
		
		return true;
	}
	function GetDifficulty ($spelltype,$mages,$userid) { return 6; }
	function GetProduced ($dtime) { return -parent::GetProduced($dtime)*($this->level); }
}


// decreases population
class Spell_Pest extends Spell_Production {
	function Spell_Pest() { $this->res = "pop"; $this->restext = "<img src='".g("pop-r%R%.png")."'>"; }
	function Birth ($success) {
		if (!parent::Birth($success)) return false;
		$killed = sqlgettable("SELECT * FROM `spell` WHERE `type` = ".kSpellType_LoveAndJoy." AND `target` = ".$this->target);
		foreach ($killed as $kill) BanSpell($kill,$this->owner);
		
		// send message to victim
		$spellreport = "Der schwarze Tod kriecht durch die Gassen,<br> grosse Teile der Bevölkerung sterben.<br>\n";
		$owneruser = sqlgetobject("SELECT `id`,`name` FROM `user` WHERE `id` = ".intval($this->owner));
		if ($owneruser){
			$spellreport .= "Eure Magier konnten ".($owneruser->name)." als Verursacher identifizieren.<br>\n";
        } else $owneruser->id = 0;
        GuildLogMeShort($this->x,$this->y,$owneruser->id,$this->target,"Zauber",($this->spelltype->name)." wurde gezaubert");
		sendMessage($this->target,0,"Pest",$spellreport,kMsgTypeReport,FALSE);
		
		return true;
	}
	function Cron($dtime) {
		parent::Cron($dtime);
		$killed = sqlgettable("SELECT * FROM `spell` WHERE `type` = ".kSpellType_LoveAndJoy." AND `target` = ".$this->target);
		foreach ($killed as $kill) BanSpell($kill,$this->owner);
	}
	function GetDifficulty ($spelltype,$mages,$userid) { return 8; }
	function GetProduced ($dtime) { 
		if (empty($this->targetuser)) return 0;
		return -($dtime*40.0/3600.0+$this->level*0.01*$this->targetuser->pop*$dtime/3600.0)*$this->mod; 
	}
}




//********************[ Multi-Use-Spells ]*****************************************************
// Mainly Elemental and Counterspells ?
//*********************************************************************************************




// protects against dürre
class Spell_Regen extends Spell_Cron {
	function Birth ($success) {
		if (!parent::Birth($success)) return false;

		//put out fires in the rain cast area
		FirePutOut($this->cast_x,$this->cast_y,kFireRegenLoeschRadius);
		
		$spellreport = "Es fängt an zu regnen.<br>";
		echo $spellreport;
		sendMessage($this->target,0,"Regen",$spellreport,kMsgTypeReport,FALSE);
		$this->Cron(60);
		return true;
	}
	function Cron($dtime) {
		parent::Cron($dtime);
		$killed = sqlgettable("SELECT * FROM `spell` WHERE `type` = ".kSpellType_Duerre." AND `target` = ".$this->target);
		foreach ($killed as $kill) BanSpell($kill,$this->owner);
	}
}


// locates nerby items
// Level 1 : 1 Rohstoff-Depots + 0 Gegenstände im Umkreis von 100 Feldern
// Level 2 : 2 Rohstoff-Depots + 1 Gegenstände im Umkreis von 100 Feldern
// Level 3 : 3 Rohstoff-Depots + 2 Gegenstände im Umkreis von 200 Feldern
class Spell_Schatzsuche extends Spell {
	// 3 zufällige grosse rohstoff-mengen die offen rumliegen zu entdecken
	function Birth ($success) {
		if (!parent::Birth($success)) return false;
		$c_res = floor($this->level * $this->mod);
		$c_item = floor(max(0,$this->level - 1) * $this->mod);
		$r = max(100,-100+100*$this->level);
		$x = $this->x;
		$y = $this->y;
		$cond = "`army` = 0 AND `amount` > 0 AND `building` = 0 ".
				"AND `x` >= (".($x-$r).") AND `y` >= (".($y-$r).")".
				"AND `x` <= (".($x+$r).") AND `y` <= (".($y+$r).")";
		$resitems = array(kResItemType_lumber,kResItemType_stone,kResItemType_food,kResItemType_metal,kResItemType_runes);
		$res = sqlgettable("SELECT * FROM `item` WHERE `type` IN (".implode(",",$resitems).") AND $cond ORDER BY RAND() LIMIT 20");
		$item = ($c_item==0)?array():sqlgettable("SELECT * FROM `item` WHERE `type` IN (".implode(",",$resitems).") = 0 AND $cond ORDER BY RAND() LIMIT 20");
		global $gItemType;
		for ($i=0;$i<$c_res;++$i) echo "<img src='".g($gItemType[$res[$i]->type]->gfx)."'> ".$res[$i]->amount." bei ".opos2txt($res[$i])."<br>";
		for ($i=0;$i<$c_item;++$i) echo "<img src='".g($gItemType[$item[$i]->type]->gfx)."'> ".$item[$i]->amount." bei ".opos2txt($item[$i])."<br>";
		return true;
	}
	function GetDifficulty ($spelltype,$mages,$userid) { return 5; }
}

// ameisenhügel + rohstoffe darauf + armeen drumrum
// Level 1 : im Umkreis von 100 Feldern
// Level 2 : im Umkreis von 200 Feldern
// Level 3 : im Umkreis von 300 Feldern
class Spell_Ameisensuche extends Spell {
	// 3 zufällige grosse rohstoff-mengen die offen rumliegen zu entdecken
	
    function cmp_nest($a, $b) {
        $a = $a->myrespoints;
        $b = $b->myrespoints;
		if ($a == $b) return 0;
		return ($a > $b) ? -1 : 1; // > : descending
    }
	
	function Birth ($success) {
		if (!parent::Birth($success)) return false;
		$r = 100*$this->level;
		$x = $this->x;
		$y = $this->y;
		$xylimit =	"`x` >= (".($x-$r).") AND `y` >= (".($y-$r).") AND".
					"`x` <= (".($x+$r).") AND `y` <= (".($y+$r).")";
		$nests_pre = sqlgettable("SELECT * FROM `hellhole` WHERE `ai_type` = ".kHellholeType_AntNest." AND ".$xylimit);
		$nests = array();
		global $gRes2ItemType,$gUnitType;
		$c_total = 0;
		$c_queen = 0;
		$spreadresmax = 0;
		foreach ($nests_pre as $o) {
			$x = $o->x;
			$y = $o->y;
			$o->building = sqlgetobject("SELECT * FROM `building` WHERE `x` = ".$o->x." AND `y` = ".$o->y);
			
			if (count($gRes2ItemType) > 0)
					$myresitems = sqlgettable("SELECT * FROM `item` WHERE `x` = ".$o->x." AND `y` = ".$o->y." AND 
									`type` IN (".implode(",",$gRes2ItemType).")");
			else	$myresitems = array();
			$myrespoints = 0;
			foreach ($myresitems as $i) $myrespoints += $i->amount;
			$o->myresitems = $myresitems;
			$o->myrespoints = $myrespoints;
			$o->a = $gUnitType[$o->type]->last;
			$o->b = $gUnitType[$o->type2]->last;
			$o->spread_min_respoints = max(
				$gUnitType[$o->type]->last * $o->armysize * kHellHoleParam_Ant_MinRunsTillSpread,
				$gUnitType[$o->type2]->last * $o->armysize2 * kHellHoleParam_Ant_KingSizeMult
				);
			$spreadresmax = max($spreadresmax,$o->spread_min_respoints);
			$o->hasqueens = $o->myrespoints > $o->spread_min_respoints;
			
			++$c_total;
			if ($o->hasqueens) ++$c_queen;
		
			$r = 1;
			$xylimit =	"`x` >= (".($x-$r).") AND `y` >= (".($y-$r).") AND".
						"`x` <= (".($x+$r).") AND `y` <= (".($y+$r).")";
			$o->armies = sqlgettable("SELECT * FROM `army` WHERE `user` > 0 AND ".$xylimit);
			$nests[] = $o;
		}
		usort($nests, array("Spell_Ameisensuche", "cmp_nest"));
		?>
		AmeisenHügel gesamt : <?=$c_total?>, davon <?=$c_queen?> mit Königinnen (min <?=kplaintrenner($spreadresmax)?> Res)
		<table border=1 cellspacing=0 cellpadding=0>
		<tr>
			<th>AmeisenHügel</th>
			<th>HP</th>
			<th>Ressourcen</th>
			<th>Königinnen</th>
			<th>danebenstehende Armeen</th>
		</tr>
		<?php foreach ($nests as $o) {?>
			<tr>
				<td><?=opos2txt($o)?></td>
				<td align="right"><?=intval($o->building->hp)?></td>
				<td align="right"><?=kplaintrenner($o->myrespoints)?></td>
				<td><?=($o->hasqueens)?"JA":"-"?></td>
				<td nowrap align="right">
					<?php foreach ($o->armies as $a) {?>
					<a href="<?=Query("info.php?sid=?&x=".$a->x."&y=".$a->y)?>"><?=$a->name?></a> von <?=GetUserLink($a->user)?><br>
					<?php } // endforeach?>
				</td>
			</tr>
		<?php } // endforeach?>
		</table>
		<?php
		//vardump2($nests);
		//for ($i=0;$i<$c_res;++$i) echo "<img src='".g($gItemType[$res[$i]->type]->gfx)."'> ".$res[$i]->amount." bei ".opos2txt($res[$i])."<br>";
		//for ($i=0;$i<$c_item;++$i) echo "<img src='".g($gItemType[$item[$i]->type]->gfx)."'> ".$item[$i]->amount." bei ".opos2txt($item[$i])."<br>";
		return true;
	}
	function GetDifficulty ($spelltype,$mages,$userid) { return 5; }
}

// Armee für eine bestimmte zeit festhalten
class Spell_Spinnennetz extends Spell_Cron {
	function Birth ($success) {
		if (!parent::Birth($success)) return false;
		$army = sqlgetobject("SELECT * FROM `army` WHERE `type` = ".kArmyType_Normal." AND `x` = ".$this->x." AND `y` = ".$this->y);
		if (empty($army)) {
			$this->Expire();
			echo "Kein Opfer in Sicht, das Spinnennetz sinkt auf den Boden.<br>";
			return false;
		}
		$victim_user = sqlgetobject("SELECT * FROM `user` WHERE `id` = ".intval($army->user));
		if ($victim_user) {
			echo "Die Armee $army->name von $victim_user->name wurde gefangen<br>";
			echo "Vorraussichtliche Dauer der Bewegungsunfähigkeit : ".Duration2Text($this->lasts-time())."<br>\n";

			// send message to victim
			$spellreport = "Unsere Armee $army->name sind bei ($army->x,$army->y) in ein riesiges Spinnennetz geraten.<br>\n";
			$spellreport .= "Vorraussichtliche Dauer der Bewegungsunfähigkeit : ".Duration2Text($this->lasts-time())."<br>\n";
			$owneruser = sqlgetobject("SELECT `id`,`name` FROM `user` WHERE `id` = ".intval($this->owner));
			if ($owneruser){
				$spellreport .= "Eure Magier konnten ".($owneruser->name)." als Verursacher identifizieren.<br>\n";
			} else $owneruser->id = 0;
            GuildLogMeShort($this->x,$this->y,$owneruser->id,$victim_user->id,"Zauber",($this->spelltype->name)." wurde gezaubert");
            sendMessage($victim_user->id,0,"Spinnennetz bei ($army->x,$army->y)",$spellreport,kMsgTypeReport,FALSE);
		} else {
			echo "Die Armee $army->name wurde gefangen<br>";
			echo "Vorraussichtliche Dauer der Bewegungsunfähigkeit : ".Duration2Text($this->lasts-time())."<br>\n";
		}
		$this->Cron(60);
		return true;
	}
	
	function Cron($dtime) {
		parent::Cron($dtime);
		sql("UPDATE `army` SET `nextactiontime` = ".$this->lasts." , `idle` = 0
			WHERE `type` = ".kArmyType_Normal." AND `x` = ".$this->x." AND `y` = ".$this->y);
	}
	function GetDifficulty ($spelltype,$mages,$userid) { return 8; }
	function Effect () {
		return "<font color='#dd6600'>hält eine Armee gefangen</font>";
	}
}


// not yet implementet, too mighty
// kampf abbrechen, todo : cron : kampf verhindern ?
class Spell_Friedenstaube extends Spell {
	function Birth ($success) {
		if (!parent::Birth($success)) return false;
		return true;
	}
	function GetDifficulty ($spelltype,$mages,$userid) { return 5; }
}


// information about hellhole or player
// level 1 : hellhole analysieren
// level 2 : lager-inhalt ausspionieren
// level 3 : armee-positonen des gegners aufdecken
// level 4 : wegpunkte ?
class Spell_Hoellenauge extends Spell {
	function Birth ($success) {
		if (!parent::Birth($success)) return false;
		
		$hellhole = sqlgetobject("SELECT * FROM `hellhole` WHERE `x` = ".intval($this->x)." AND `y` = ".intval($this->y));
		$building = sqlgetobject("SELECT * FROM `building` WHERE `x` = ".intval($this->x)." AND `y` = ".intval($this->y));
		$army = sqlgetobject("SELECT * FROM `army` WHERE `x` = ".intval($this->x)." AND `y` = ".intval($this->y));
		if (empty($building) && empty($army)) { echo "hier gibts nichts zu sehen<br>"; return false; }
		
		$targetuser = false;
		if ($building) $targetuser = sqlgetobject("SELECT * FROM `user` WHERE `id` = ".intval($building->user));
		if ($army && $army->user) $targetuser = sqlgetobject("SELECT * FROM `user` WHERE `id` = ".intval($army->user));
		
		rob_ob_start();
			
		global $gUnitType,$gItemType;
		if ($hellhole) {
			echo "Höllenauge auf (".$hellhole->x.",".$hellhole->y.")<br>";
			if ($hellhole->type > 0) {
				$unittype = $gUnitType[$hellhole->type];
				$units = cUnit::Simple($hellhole->type,$hellhole->armysize);
				$total_treasure = array();
				for ($i=0;$i<100;++$i) {
					$treasure = cUnit::GetUnitsTreasure($units);
					foreach ($treasure as $k => $v)
						$total_treasure[$k] = (isset($total_treasure[$k])?$total_treasure[$k]:0) + $v;
				}
				$faktor = 100*$hellhole->spawndelay/3600.0;
				foreach ($total_treasure as $k => $v) $total_treasure[$k] = floor($v/$faktor)."<img src='".g($gItemType[$k]->gfx)."'>";
				echo "Truppenstärke : ".$hellhole->armysize." <img src='".g($unittype->gfx)."'>".$unittype->name."<br>";
				echo "Ressourcenproduktion pro Stunde : ".implode(",",$total_treasure)."<br>";
			} else echo "Truppenstärke : ".$hellhole->armysize." ???<br>";
			echo "Maxmimal ".$hellhole->num." Truppen gleichzeitig, alle ".Duration2Text($hellhole->spawndelay)." ein neuer Trupp<br>";
			echo "Bewegung im Radius ".$hellhole->radius."<br>";
		}
		
		global $gRes;
		if ($targetuser) {
			echo "Höllenauge auf ".$targetuser->name."<br>";
		}
		if ($this->level >= 2 && $targetuser) {
			echo "<b>Lagerinhalt</b> : ";
			foreach ($gRes as $n => $f) echo "<img src='".g("res_$f.gif")."'>".sprintf("%0.0f",$targetuser->$f)." ";
			echo "<br>";
		}
		if ($this->level < 2 && $targetuser) echo "Lagerinhalt erst ab Stufe 2<br>";
		
		if ($this->level >= 3 && $targetuser) {
			echo "<b>Magiet&uuml;rme</b>:<br>";
			$target_towers = sqlgettable("SELECT *,COUNT(*) as c FROM `building` WHERE `type` = ".kBuilding_MagicTower." AND `user` = ".$targetuser->id." GROUP BY `level` ORDER BY `level` DESC");
			?>
			<?php foreach ($target_towers as $o) {?>
			<?=$o->c?> mal Stufe <?=$o->level?><br>
			<?php } // endforeach?>
			<?php 
			echo "<b>Armeen</b>:<hr>";
			$armies = sqlgettable("SELECT * FROM `army` WHERE `user` = ".$targetuser->id." ORDER BY `type`,`name`");
			global $gUnitType,$gItemType,$gRes;
			foreach ($armies as $army) { 
				echo $army->name.opos2txt($army,false,true)." : ";
				$units = cUnit::GetUnits($army->id);
				$items = sqlgettable("SELECT * FROM `item` WHERE `army` = ".$army->id." OR (`army` = 0 AND `building` = 0 AND `x` = ".$army->x." AND `y` = ".$army->y.")ORDER BY `type`");
				?>
				<?php foreach ($units as $unit) {?>
					<?=ktrenner(floor(abs($unit->amount)))?><img src='<?=g($gUnitType[$unit->type]->gfx)?>'>
				<?php }?>
				<br>
				<?php foreach ($items as $o) {?>
				<img border=0 title="<?=$gItemType[$o->type]->name?>" alt="<?=$gItemType[$o->type]->name?>" src="<?=g($gItemType[$o->type]->gfx)?>"><?=ktrenner($o->amount)?>
				<?php } // endforeach?>
				<?php foreach ($gRes as $n=>$f) if ($army->{$f} > 0) {?>
				<img border=0 alt="<?=$f?>" src="<?=g("res_$f.gif")?>"><?=ktrenner(floor($army->{$f}))?>
				<?php } // endforeach?>
				<hr>
				<?php
			}
		}
		if ($this->level < 3 && $targetuser) echo "Armee-Positionen erst ab Stufe 3<br>";
	
		$report = rob_ob_end();
		echo magictext($report);
		
		if ($targetuser) {
			sendMessage($this->owner,0,"Höllenauge auf ".$targetuser->name,$report,kMsgTypeReport,FALSE);
		}
		if ($hellhole) {
			sendMessage($this->owner,0,"Höllenauge auf (".$hellhole->x.",".$hellhole->y.")",$report,kMsgTypeReport,FALSE);
		}
		
		return true;
	}
	
	function GetDifficulty ($spelltype,$mages,$userid) { return 8; }
}


// mana vom zielturm vernichten
// Zauber an dieser Position bannen
// level 1 : 50% des manas
// level 2 : 100% des manas
class Spell_Bann extends Spell {
	function Birth ($success) {
		if (!parent::Birth($success)) return false;
		
		$killed = sqlgettable("SELECT * FROM `spell` WHERE `x` = ".$this->x." AND `y` = ".$this->y);
		foreach ($killed as $kill) BanSpell($kill,$this->owner);
		
		
		if (count($killed) == 0) {
			$building = sqlgetobject("SELECT * FROM `building` WHERE `x` = ".intval($this->x)." AND `y` = ".intval($this->y));
			if ($building && $building->type == kBuilding_MagicTower && $building->mana > 0) {
				$dmg = floor($this->level * 0.5 * $building->mana * $this->mod);
				sql("UPDATE `building` SET `mana` = `mana` - $dmg WHERE `id` = ".$building->id);
				echo $dmg." Mana gebannt<br>";
		
				// send message to victim
				$spellreport = "In unserem Turm bei ($building->x,$building->y) wurde $dmg Mana gebannt !<br>\n";
				$owneruser = sqlgetobject("SELECT `id`,`name` FROM `user` WHERE `id` = ".intval($this->owner));
				if ($owneruser){
					$spellreport .= "Eure Magier konnten ".($owneruser->name)." als Verursacher identifizieren.<br>\n";
                } else $owneruser->id = 0;
                GuildLogMeShort($this->x,$this->y,$owneruser->id,$this->target,"Zauber",($this->spelltype->name)." wurde gezaubert");
				sendMessage($this->target,0,"Bann (Turm,$building->x,$building->y)",$spellreport,kMsgTypeReport,FALSE);
			} else if (count($killed) == 0) {
				echo "hier ist kein Mana, und auch kein Zauber, den man bannen könnte<br>";
				return false;
			}
		}
		
		return true;
	}
	function GetDifficulty ($spelltype,$mages,$userid) { return 9; } // todo : unhardcode
}

// todo : spell feuer : wirkt auf ein feld, mach cron schaden, 
// ist auf der map sichtbar, genau wie netz, killbar durch regen (area)
// nicht zauberbar bei wetter : regen
// brand mehr area : gebäudeschaden ? konter durch regen (spieler)




//********************[ Spell_Instant_Damage ]*****************************************************
// Army-Damaging Spells
//*********************************************************************************************


// hinterlässt schutt
// schaden gegen armee
// level 1 : 40 000 schadenspunkte (ca 400 Ritter)
// level 2 : 80 000 schadenspunkte (ca 800 Ritter)
class Spell_Steinschlag extends Spell_Instant_Damage {
	function Spell_Steinschlag () { 
		$this->basedmg = 400 * 100;
		$this->report_topic = "Steinschlag";
		$this->report_dead_why = "Die Armee _ARMYNAME_ wurde von Felsbrocken erschlagen.";
		$this->terrain_change = kTerrain_Rubble;
	}
	function Effect () { return "Es regnet Felsbrocken bei ($this->x,$this->y)"; }
	function GetDifficulty ($spelltype,$mages,$userid) { return 9; }  // todo : unhardcode
}


// hinterlässt Krater
// schaden gegen armee
// level 1 : 4 000 000 schadenspunkte (ca 4000 Ritter)
// level 2 : 8 000 000 schadenspunkte (ca 8000 Ritter)
class Spell_Komet extends Spell_Instant_Damage {
	function Spell_Komet () { 
		$this->basedmg = 4000 * 100;
		$this->report_topic = "Komet";
		$this->report_dead_why = "Die Armee _ARMYNAME_ wurde von einem Kometen erschlagen.";
		$this->terrain_change = kTerrain_Hole;
	}
	function Effect () { return "Ein Komet ist bei ($this->x,$this->y) eingeschlagen"; }
	function GetDifficulty ($spelltype,$mages,$userid) { return 12; }
}


//********************[ Synthesis-Spells ]*****************************************************
// Create things
//*********************************************************************************************


// create portalstein
class Spell_Portalstein extends Spell {
	function Birth ($success) {
		if (!parent::Birth($success)) return false;
		$army = sqlgetone("SELECT * FROM `army` WHERE `x` = ".intval($this->x)." AND `y` = ".intval($this->y));
		if (!$army && cArmy::GetPosSpeed($this->x,$this->y,$this->owner) <= 0) {
			echo "feld ist nicht betretbar<br>";
			return false;
		}
		$itemtypeids = array(
			kItem_Portalstein_Blau,
			kItem_Portalstein_Gruen,
			kItem_Portalstein_Schwarz,
			kItem_Portalstein_Rot);
		$itemtypeid = $itemtypeids[array_rand($itemtypeids)];
		if ($army)
			cItem::SpawnArmyItem($army,$itemtypeid);
		else	cItem::SpawnItem($this->x,$this->y,$itemtypeid);
		global $gItemType;
		echo "<img src='".g($gItemType[$itemtypeid]->gfx)."'>erzeugt<br>";
		return true;
	}
	function GetDifficulty ($spelltype,$mages,$userid) { return 5; }
}


// increase army speed
class Spell_7_Meilen_Stiefel extends Spell_Cron {
	function Cron($dtime) {
		parent::Cron($dtime);
		sql("UPDATE `army` SET `nextactiontime` = 0 , `idle` = `idle` + 600 WHERE `type` = ".kArmyType_Normal." AND `user` = ".$this->target);
	}
	function GetDifficulty ($spelltype,$mages,$userid) { return 8; }
	function Effect () {
		return "<font color='green'>maximale Armee Geschwindigkeit</font>";
	}
}


//********************[ Spell_Erdbeben ]*******************************************************
//a dangerous earthquake
//*********************************************************************************************


class Spell_Erdbeben extends Spell_Once_Per_User {
	function Birth ($success) {
		// $success < 0 -> patzer , $success == 0 -> normal failure
		if (!parent::Birth($success)) return false;
		
		// determine victims
		$aff_buildings = $this->GetAffectedBuildings($this->level);
		$aff_users = array();
		foreach ($aff_buildings as $o) {
			if (isset($aff_users[$o->user]))
					$aff_users[$o->user]++;
			else	$aff_users[$o->user] = 1;
		}
		
		// try counter spells
		foreach ($aff_users as $uid => $num) {
			if ($this->TryCounter($uid)) {
				echo "Zauber wurde gekontert !<br>";
				return false;
			}
		}
		
		$spellreport = "Schaden in der Mitte : ".$this->GetDamage($this->level)." pro Stunde<br>\n";
		$spellreport .= "Vorraussichtliche Dauer : ".Duration2Text($this->lasts-time())."<br>\n";
		$spellreport .= "Radius : ".$this->GetRadius($this->level)."<br>\n";
		echo $spellreport;
		
		// send message to victims
		$topic = "Erdbeben bei ($this->x,$this->y)";
		if ($this->accumulated)
				$msg = "Ein weiteres Erdbeben";
		else	$msg = "Ein Erdbeben";
		$msg .= " beschädigt Ihre Gebäude bei ($this->x,$this->y).<br>\n";
		$msg .= $spellreport;
		
		$owneruser = sqlgetobject("SELECT `id`,`name` FROM `user` WHERE `id` = ".intval($this->owner));
		if ($owneruser)
			$msg .= "Eure Magier konnten ".($owneruser->name)." als Verursacher identifizieren.<br>\n";
		else $owneruser->id = 0;
        
		foreach ($aff_users as $uid => $num) {
			$mymsg = $msg;
			$mymsg .= "Von unseren Gebäuden befinden sich $num im Wirkungsbereich.<br>\n";
			sendMessage($uid,0,$topic,$mymsg,kMsgTypeReport,FALSE);
			LogMe($uid,NEWLOG_TOPIC_MAGIC,NEWLOG_MAGIC_DAMAGE_TARGET,$this->x,$this->y,$this->target,$this->spelltype->name,$this->owner);
            GuildLogMeShort($this->x,$this->y,$owneruser->id,$uid,"Zauber",($this->spelltype->name)." wurde gezaubert");
   		}
		
		if (count($aff_buildings) == 0) {
			echo "Kein Gebäude beschädigt<br>";
		} else {
			echo count($aff_buildings)." Gebäude im Wirkungsbereich<br>";
			arsort($aff_users);
			foreach ($aff_users as $uid => $num)
				echo "$num von ".nick($uid)."<br>";
		}
		
		return true;
	}
	
	function GetAffectedBuildings () {
		$x = $this->x;
		$y = $this->y;
		$r = $this->GetRadius($this->level);
		// kreis : x + y <= r
		$arr = sqlgettable("SELECT * FROM `building` WHERE
			`x` >= (".($x-$r).") AND `x` <= (".($x+$r).") AND
			`y` >= (".($y-$r).") AND `y` <= (".($y+$r).")");
		$res = array();
		foreach ($arr as $o) 
			if (($o->x-$x)*($o->x-$x) + ($o->y-$y)*($o->y-$y) <= $r*$r)
				$res[] = $o;
		return $res;
	}
	
	function GetRadius () {
		return $this->spelltype->baserange + $this->level;
	}
	
	function GetDamage() {
		return $this->spelltype->baseeffect + $this->level*$this->spelltype->basemod;
	}	

	function Cron($dtime) {
		parent::Cron($dtime);
		
		TablesLock();
		$damage = $this->GetDamage() * $dtime/3600;
		$range = $this->GetRadius();
		$faktor = -$damage/($range*$range);
		$x = $this->x;
		$y = $this->y;
		echo "make $damage dmg at with center ($x,$y)<br>";
		$aff_buildings = $this->GetAffectedBuildings();
		foreach($aff_buildings as $building) {
				$dx = abs($x-$building->x);
				$dy = abs($y-$building->y);
				$dist = sqrt($dx*$dx+$dy*$dy);
				if ($dist > $range) continue;
				$local_damage = max(0,$damage - ((float)$damage)*($dist*$dist)/((float)($range*$range)));
				if ($building->type==kBuilding_HQ)
						$hpmin = 100+5*$building->level;
				else	$hpmin = 1+$building->level;
				// stop damage at minhp
				$newhp = max($building->hp-$local_damage,$hpmin);
				// don't heal buildings already below minhp
				$local_damage = max(0,$building->hp-$newhp);
				
				// don't heal buildings already below minhp
				if ($local_damage > 0) {
					echo "do $local_damage damage at range $dist ($building->x,$building->y)<br>";
					sql("UPDATE `building` SET `hp`=`hp`-".$local_damage." WHERE `id`=".intval($building->id));
				}
		}
		TablesUnlock();
	}

	function Effect() {
		return $this->GetDamage()." Schaden im Zentrum ($this->x,$this->y) pro Stunde. Radius ".$this->GetRadius();
	}
}


//********************[ Spell_Strike ]************************************************************
//damages a building. verly less damage, but can destroy it
//*********************************************************************************************


class Spell_Strike extends Spell {
	function Birth ($success) { // $success < 0 -> patzer , $success == 0 -> normal failure
		// if ($success < 0) damage($this->towerid); // böser zauberpatzer ??
		if (!parent::Birth($success)) return false;
		$result = false;
		TablesLock();
		$o = sqlgetobject("SELECT * FROM `building` WHERE `x`=".intval($this->x)." AND `y`=".intval($this->y));
		if ($o && $o->type != kBuilding_HQ) {
			$dmg = floatval($this->spelltype->baseeffect * $this->level * $this->mod);
			$spellreport = "Schaden : $dmg, HP vorher = ".round($o->hp,1).", HP nachher = ".max(0,round($o->hp - $dmg,1))."<br>";
			if ($o->hp - $dmg <= 0) {
				cBuilding::removeBuilding($o,$o->user);
				$spellreport .= "Gebäude VERNICHTET !<br>";
			} else sql("UPDATE `building` SET `hp`=`hp`-".$dmg." WHERE `id`=".$o->id);
			echo $spellreport;
			
			
			// send message to victim
			if ($o->user) {
				$topic = "Strike auf ($this->x,$this->y)";
				$msg = "Ein Strike hat Ihr Gebäude bei ($this->x,$this->y) beschädigt.<br>\n";
				$msg .= $spellreport;
				$owneruser = sqlgetobject("SELECT `id`,`name` FROM `user` WHERE `id` = ".intval($this->owner));
				if ($owneruser){
					$msg .= "Eure Magier konnten ".($owneruser->name)." als Verursacher identifizieren.<br>\n";
                } else $owneruser->id = 0;
                GuildLogMeShort($this->x,$this->y,$owneruser->id,$o->user,"Zauber",($this->spelltype->name)." wurde gezaubert");
                
				sendMessage($o->user,0,$topic,$msg,kMsgTypeReport,FALSE);
				LogMe($o->user,NEWLOG_TOPIC_MAGIC,NEWLOG_MAGIC_DAMAGE_TARGET,$this->x,$this->y,$this->target,$this->spelltype->name,$this->owner);
			}
			
			$result = true;
		} else if (empty($o)) echo "Kein Gebäude beschädigt<br>";
		else if ($o->type != kBuilding_HQ) echo "Strike ist wirkungslos gegen Haupthäuser !<br>";
		TablesUnlock();
		return $result;
	}
}


//********************[ Spell_Strike ]************************************************************
//damages a building. verly less damage, but can destroy it
//*********************************************************************************************


class Spell_Brandrodung extends Spell {
	function Birth ($success) { // $success < 0 -> patzer , $success == 0 -> normal failure
		// if ($success < 0) damage($this->towerid); // böser zauberpatzer ??
		if (!parent::Birth($success)) return false;
		$ter = cMap::StaticGetTerrainAtPos($this->x,$this->y);
		if (in_array($ter,array(kTerrain_YoungForest,kTerrain_TreeStumps,kTerrain_Forest,kTerrain_Flowers,kTerrain_Field,kTerrain_Swamp))) {
			sql("REPLACE INTO `terrain` SET `type` = ".kTerrain_Grass." , `x` = ".intval($this->x).", `y` = ".intval($this->y));
			require_once("lib.map.php");
			RegenSurroundingNWSE($this->x,$this->y);
		}
	}
}

//********************[ Spell_WaldAnpflanzen ]*************************************************
//Create a forest; If it fails, creates a swamp;
//If succeeds superior, creates a fully grown forest
//*********************************************************************************************

$gWaldAnpflanzReplaceableTerrain = array(kTerrain_Grass, kTerrain_Flowers, kTerrain_TreeStumps, kTerrain_Field, kTerrain_Rubble);

class Spell_WaldAnpflanzen extends Spell {
	function Birth ($success) { // $success < 0 -> patzer, $success == 0 -> normal failure
		global $gWaldAnpflanzReplaceableTerrain;
		$o = sqlgetobject("SELECT * FROM `building` WHERE `x`=".intval($this->x)." AND `y`=".intval($this->y));
		if ($o) { echo "kann nicht auf Gebäude gesprochen werden"; return false; }
		$ter = cMap::StaticGetTerrainAtPos($this->x,$this->y);
		if (!in_array($ter, $gWaldAnpflanzReplaceableTerrain)) { echo "kann nicht auf diesem Terrain gesprochen werden"; return false; }
		
		if ($success < 0) {
			//Create a swamp
			sql("REPLACE INTO `terrain` SET `type` = ".kTerrain_Swamp." , `x` = ".intval($this->x).", `y` = ".intval($this->y));
		}
		if (!parent::Birth($success)) return false;
		if ($success > 1)
				sql("REPLACE INTO `terrain` SET `type` = ".kTerrain_Forest." 	  , `x` = ".intval($this->x).", `y` = ".intval($this->y));
		else 	sql("REPLACE INTO `terrain` SET `type` = ".kTerrain_YoungForest." , `x` = ".intval($this->x).", `y` = ".intval($this->y));
	}
	// high difficulty is bad (less than 11 does not create patzer
	function GetDifficulty ($spelltype,$mages,$userid) {
		return 12;
	}
}


//********************[ Spell_Brandbaender ]*************************************************
//sets a fire on the target field, on failure your magic tower will burn
//*********************************************************************************************

class Spell_Brandbaender extends Spell {
	function Birth ($success) { // $success < 0 -> patzer, $success == 0 -> normal failure
		if ($success < 0) {
			//ups, your tower burns
			$o = sqlgetobject("SELECT * FROM `building` WHERE `id`=".intval($this->towerid));
			FireSetOn($o->x,$o->y);
            echo "Ups, da ist was schief gelaufen. Ihr Turm bei ($o->x,$o->y) steht nun in Flammen.";
		}
		
		if (!parent::Birth($success)) return false;
		
        $o = sqlgetobject("SELECT * FROM `building` WHERE `x`=".intval($this->x)." AND `y`=".intval($this->y));
        // send message to victim
        if ($o && $o->user) {
            $topic = "Brandbaender auf ($this->x,$this->y)";
            $msg = "Brandbaender hat Ihr Gebäude bei ($this->x,$this->y) in Flammen aufgehen lassen.<br>\n";
            $owneruser = sqlgetobject("SELECT `id`,`name` FROM `user` WHERE `id` = ".intval($this->owner));
            if ($owneruser){
                $msg .= "Eure Magier konnten ".($owneruser->name)." als Verursacher identifizieren.<br>\n";
            } else $owneruser->id = 0;

            GuildLogMeShort($this->x,$this->y,$owneruser->id,$o->user,"Zauber",($this->spelltype->name)." wurde gezaubert");
            sendMessage($o->user,0,$topic,$msg,kMsgTypeReport,FALSE);
            LogMe($o->user,NEWLOG_TOPIC_MAGIC,NEWLOG_MAGIC_DAMAGE_TARGET,$this->x,$this->y,$this->target,$this->spelltype->name,$this->owner);
        }

        if($success > 0){
            FireSetOn($this->x,$this->y);
            echo "($this->x,$this->y) steht nun in Flammen.";
        }
	}
	// high difficulty is bad (less than 11 does not create patzer
	function GetDifficulty ($spelltype,$mages,$userid) {
		return 12;
	}
}

//********************[ Spell_ArmeeDerToten ]****************************************************
//a dangerous spell to summon evil creatures from beneath
//*********************************************************************************************


// Level 1 : 30% of fallen units raised
// Level 2 : 50% of fallen units raised
// Level 3 : 70% of fallen units raised
class Spell_ArmeeDerToten extends Spell_Once_Per_User {
	function Spell_ArmeeDerToten () { $this->undeadtype = kUnitType_GhostKnight; }
	
	function IsArmyAffected ($army) {
		//if ($this->level >= 3 && $army->user != $this->owner) return false; // should be able to cast on friends
		return $army->type == kArmyType_Normal;
		//return $army->type == kArmyType_Normal && $army->user > 0; // no monsters
	}
	
	// callback from cron return array($units,$lost_units)
	// modifyes units and lost units in fight (raisedead,heal,moreelite,doubledamage,inverted-damage-order(flankenangriff),...)
	function ModUnits ($army,$units,$lost_units) {
		global $gUnitType;
		if (!$this->IsArmyAffected($army)) return array($units,$lost_units);
		$lost_units = cUnit::GroupUnits($lost_units);
		$units = cUnit::GroupUnits($units);
		
		//echo "verluste ".$army->name." :";vardump2($lost_units);
		
		$raised = 0;
		$lost_corpses = array();
		foreach ($lost_units as $o) {
			if ($o->type != $this->undeadtype && $o->amount > 0) {
				// don't raise undeads again
				$raise = $this->GetRaisedFraction()*floatval($o->amount);
				$raised += $raise;
				$o->amount = -$raise; // remove raised corpses...
				$lost_corpses[] = $o;
			}
		}
		$units[] = arr2obj(array("type"=>$this->undeadtype,"amount"=>$raised,"user"=>$army->user,"spell"=>$this->id));
		//vardump2($units);
		foreach ($lost_corpses as $o) $lost_units[]  = $o;
		$lost_units = cUnit::GroupUnits($lost_units);
		$units = cUnit::GroupUnits($units);
		echo round($raised,1)." ".$gUnitType[$this->undeadtype]->name." sind fuer ".$army->name." auferstanden<br>";
		return array($units,$lost_units);
	}
	
	function GetRadius () {
		return $this->spelltype->baserange + $this->level;
	}
	
	function GetRaisedFraction () {
		return 0.10 + $this->level * 0.20;
	}
	
	function Birth ($success) { // $success < 0 -> patzer , $success == 0 -> normal failure
		if (!parent::Birth($success)) return false;
		$r = $this->radius;
		// echo "Beschworene Kreaturen pro Stunde : ".round($this->GetSummonable(3600),1)."<br>";
		// echo "Verfügbare Leichen in der Umgebung : ".$this->GetCorpsesLeft()."<br>";
		echo $this->Effect()."<br>";
		
		// send message to victims
		$r += 5; // alarm a few armies within sight
		$x = intval($this->x);
		$y = intval($this->y);
		$area_cond = "	`x`>(".($x-$r).") AND `x`<(".($x+$r).") AND 
						`y`>(".($y-$r).") AND `y`<(".($y+$r).")";
		$affected_userids = sqlgetonetable("SELECT `user` FROM `army` WHERE `user` > 0 AND $area_cond GROUP BY `user`");
		
		$topic = "Untote bei ($this->x,$this->y)";
		$msg = "Eure Krieger haben Untote Kreaturen nahe ($this->x,$this->y) gesichtet.<br>\n";
		
		foreach ($affected_userids as $uid) {
			sendMessage($uid,0,$topic,$msg,kMsgTypeReport,FALSE);
		}
		
		return true;
	}
	
	function Cron ($dtime) {
		parent::Cron($dtime);
	}
	
	function Effect() {
		global $gUnitType;
		$raised = floor(100.0*$this->GetRaisedFraction())."%";
		return "$raised der gefallenen werden zu <img src='".g($gUnitType[$this->undeadtype]->gfx)."'>, Radius $this->radius";
	}
	
	/*

	
	function GetSummonable ($dtime) {
		$basesummon=2;
		return ($basesummon+($this->level-1)*2)*$this->mod*$dtime/3600;
	}
	
	function GetCorpsesLeft () {
		$r = $this->GetRadius();
		$x = intval($this->x);
		$y = intval($this->y);
		$area_cond = "	`x`>(".($x-$r).") AND `x`<(".($x+$r).") AND 
						`y`>(".($y-$r).") AND `y`<(".($y+$r).")";
		return intval(sqlgetone("SELECT SUM(`kills`) FROM `terrain` WHERE $area_cond"));
	}
	
	function Cron ($dtime){
		parent::Cron($dtime);
		assert($dtime>0);
		
		TablesLock();
		$summonable = $this->GetSummonable($dtime);
		$x = intval($this->x);
		$y = intval($this->y);
		$r = $this->GetRadius();
		echo "summon evil creatures with center ($x,$y) r = $r<br>";
		$area_cond = "	`x`>(".($x-$r).") AND `x`<(".($x+$r).") AND 
						`y`>(".($y-$r).") AND `y`<(".($y+$r).")";
		$affected = sqlgetobject("SELECT * FROM `terrain` WHERE `kills`>0 AND $area_cond ORDER BY `kills` DESC");
		if ($affected) {
			$sum = min($affected->kills,$summonable);
			$army = sqlgetobject("SELECT *,((`x`-($x))*(`x`-($x))+(`y`-($y))*(`y`-($y))) as `dist` FROM `army` WHERE 
				`type`<>".kArmyType_Siege." AND $area_cond ORDER BY `dist` ASC LIMIT 1");
			if ($army && $sum>0) {
				$u = cArmy::GetArmyUnitCount($army->id,kUnitType_GhostKnight);
				cArmy::SetArmyUnitCount($army->id,kUnitType_GhostKnight,$u+$sum);
				sql("UPDATE `terrain` SET `kills`=`kills`-($sum) WHERE `id`=".intval($affected->id));
				echo "$sum units summoned and put in army $army->id , summonable $summonable<br>";
			}
		}
		TablesUnlock();
	}

	function Effect(){
		$summonable = $this->GetSummonable(3600);
		$corpses = $this->GetCorpsesLeft();
		return "beschwärt GhostKnights in der Gegend um ($this->x,$this->y)<br>".
			"bis zu $summonable Kreaturen pro Stunde, noch $corpses Leichen in der Umgebung.";
	}
	*/
}

?>
