<?php 
require_once("../lib.main.php");
require_once("../lib.army.php");
require_once("../lib.transfer.php");
require_once("../lib.fight.php");

class cInfoArmy extends cInfoBase {
	function cancontroll ($user) { return cArmy::CanControllArmy($this,$user); }
	function mycommand () {
		foreach ($_REQUEST as $name=>$val) ${"f_".$name} = $val;
		global $gUser;
		global $gRes;
		global $gRes2ItemType;
		global $gUnitType;
		
		if (!isset($f_army) || !isset($f_do)) return;
	
		$army = sqlgetobject("SELECT * FROM `army` WHERE `id` = ".intval($f_army));
		if ($army) {
			$gc = getGuildCommander($gUser->guild);
			$cancontrollarmy = cArmy::CanControllArmy($army,$gUser);
			if ($cancontrollarmy) {
				sql("UPDATE `user` SET `lastusedarmy` = ".$army->id." WHERE `id` = ".$gUser->id);
				$gUser->lastusedarmy = $army->id;
			}
		}

		if ($army && ($cancontrollarmy || $gUser->admin))
		switch ($f_do) {
			case "admin_armystep" : if (!$gUser->admin) break;
				require_once("lib.armythink.php");
				ArmyThinkTimeShift($army->id,$f_minutes*60);
				echo "$f_minutes minutes have passed....<br>";
				$army = sqlgetobject("SELECT * FROM `army` WHERE `id` = ".intval($army->id));
				$oldpos = array($army->x,$army->y);
				ArmyThink($army,true);
				$army = sqlgetobject("SELECT * FROM `army` WHERE `id` = ".intval($army->id));
				if ($oldpos[0] != $army->x || $oldpos[1] != $army->y) {
					global $f_x,$f_y;
					$f_x = $army->x;
					$f_y = $army->y;
					?>
					<script language="javascript">
						parent.map.location.href = parent.map.location.href;
					</script>
					<?php
				}
			break;
			case "admin_fight_step" : if (!$gUser->admin) break;
				for ($i=0;$i<$f_steps;++$i) {
					$o = sqlgetobject("SELECT * FROM `fight` WHERE `id` = ".intval($f_id));
					if ($o) cFight::FightStep($o); else break;
				}
			break;
			case "admin_siege_step" : if (!$gUser->admin) break;
				for ($i=0;$i<$f_steps;++$i) {
					$o = sqlgetobject("SELECT * FROM `siege` WHERE `id` = ".intval($f_id));
					if ($o) cFight::SiegeStep($o,true); else break;
				}
			break;
			case "admin_pillage_step" : if (!$gUser->admin) break;
				for ($i=0;$i<$f_steps;++$i) {
					$o = sqlgetobject("SELECT * FROM `pillage` WHERE `id` = ".intval($f_id));
					if ($o) cFight::PillageStep($o,true); else break;
				}
			break;
			case "itemtrade_market":
				$trade = sqlgetobject("SELECT * FROM `itemtrade` WHERE `id` = ".intval($f_itemtrade));
				if (!$trade) break;
				$tradebuilding = sqlgetobject("SELECT * FROM `building` WHERE `id` = ".intval($trade->building));
				if (!cArmy::ArmyAtDiag($army,$tradebuilding->x,$tradebuilding->y)) break;
				// todo : trade between armies
				$price = explode2(",",":",$trade->price);
				$amount = max(0,min(cItem::GetMaxTrade($army,$price),intval($f_amount)));
				if ($amount <= 0) break;
				$success = true;
				foreach ($price as $comp) {
					$success = cItem::ArmyPayItem($army->id,$comp[0],$comp[1]*$amount);
					if (!$success) break;
				}
				if (!$success) break;
				$offer = explode2(",",":",$trade->offer);
				foreach ($offer as $comp)
					cItem::SpawnArmyItem($army,$comp[0],$comp[1]*$amount);
				// TODO : log and message for both users, if not null
			break;
			case "change_flags":
				if (!isset($f_flags)) $f_flags = array();
				if (!is_array($f_flags)) $f_flags = array(0=>$f_flags);
				$wannabe_sum = array_sum($f_flags);
				$mask = 0;
				$realsum = 0;
				global $gArmyType,$gArmyFlagNames;
				foreach ($gArmyFlagNames as $flag => $name) if (intval($gArmyType[$army->type]->ownerflags) & $flag) {
					$mask |= $flag;
					if ($wannabe_sum & $flag)
						$realsum |= $flag;
				}
				$army->flags = intval($army->flags);
				$army->flags_vorher = $army->flags;
				$army->flags &= (kArmyFlag_AllSet ^ intval($mask));
				$army->flags |= intval($realsum);
				sql("UPDATE `army` SET `flags` = ".$army->flags." WHERE `id` = ".$army->id);
				// check for gc change
				if ((kArmyFlag_GuildCommand & intval($army->flags_vorher)) != 
					(kArmyFlag_GuildCommand & intval($army->flags))) 
					ArmyChangeGC($army,($army->flags & kArmyFlag_GuildCommand) != 0);
			break;
			case "armydropres":
				foreach($gRes as $n=>$f) {
					$drop = isset(${"f_".$f})?intval(${"f_".$f}):0;
					$drop = max(0,min(floor($army->{$f}),$drop));
					if ($drop > 0) {
						sql("UPDATE `army` SET `$f` = GREATEST(0,`$f` - $drop) WHERE `$f` >= $drop AND `id` = ".$army->id);
						if (mysql_affected_rows() > 0)
							cItem::SpawnItem($army->x,$army->y,$gRes2ItemType[$f],$drop);
					}
				}
			break;
			
			case "flee":
				if (!sqlgetone("SELECT 1 FROM `fight` WHERE `attacker`=".$army->id." OR `defender`=".$army->id)) break;
				if (isset($f_escape_n_x))		{$x = $army->x;$y = $army->y-1;}
				else if (isset($f_escape_w_x))	{$x = $army->x-1;$y = $army->y;}
				else if (isset($f_escape_s_x))	{$x = $army->x;$y = $army->y+1;}
				else if (isset($f_escape_e_x))	{$x = $army->x+1;$y = $army->y;}
				else break;
				if (!cFight::Flee($army,$x,$y)) break;
				?>
				<script language="javascript">
					parent.map.location.href = parent.map.location.href;
				</script>
				<?php
			break;
			
			
			
			case "cancelaction":
				if (isset($f_cancelids)) {
					if (!is_array($f_cancelids)) $f_cancelids = array(0=>$f_cancelids);
					foreach ($f_cancelids as $id) 
						sql("DELETE FROM `armyaction` WHERE `id` = ".intval($id)." AND `army` = ".intval($army->id));
				}
			break;
			case "cancelpillage":
				$pillage = sqlgetobject("SELECT * FROM `pillage` WHERE `id` = ".intval($f_id));
				if (!$pillage || $pillage->army != $army->id) break;
				cFight::EndPillage($pillage,"Die Plünderung wurde abgebrochen.",true);
			break;
			case "cancelsiege":
				$siege = sqlgetobject("SELECT * FROM `siege` WHERE `id`=".intval($f_id));
				if (!$siege || $siege->army != $army->id) return;
				cFight::EndSiege($siege,"Die Belagerung wurde abgebrochen.",true);
			break;
			case "returnwaypoints":
				if (intval($army->flags) & kArmyFlag_SelfLock) break;
				$inverse = sqlgettable("SELECT * FROM `waypoint` WHERE `army`=".$army->id." ORDER BY `priority` DESC");
				$newwps = array();
				$i=0; foreach ($inverse as $o) if ($i++ > 0)
					$newwps[] = cArmy::ArmySetWaypoint($army->id,$o->x,$o->y);
				?>
				<script language="javascript">
					parent.map.location.href = parent.map.location.href;
				</script>
				<?php
			break;
			case "delallwaypoints":
				if (intval($army->flags) & kArmyFlag_SelfLock) break;
				sql("DELETE FROM `waypoint` WHERE `army`=".$army->id);
				?>
				<script language="javascript">
					parent.map.location.href = parent.map.location.href;
				</script>
				<?php
			break;
			case "setwaypoint":
				if (intval($army->flags) & kArmyFlag_SelfLock) break;
				//echo "want to calc route<br>";
				$f_x = intval($f_x);
				$f_y = intval($f_y);
				$army->units = cUnit::GetUnits($army->id);
				$lastwp = sqlgetobject("SELECT * FROM `waypoint` WHERE `army` = ".$army->id." ORDER BY `priority` DESC LIMIT 1");
				if (!$lastwp) { $lastwp->x = $army->x; $lastwp->y = $army->y; }
				if ($f_gfxbuttonmode == "wp") $f_button_wp = 1;
				if ($f_gfxbuttonmode == "route") $f_button_route = 1;
				if(isset($f_button_wp)) $newwps = array(cArmy::ArmySetWaypoint($army->id,$f_x,$f_y));
				else if(isset($f_button_route)){
					require_once("lib.path.php");
					profile_page_start("pathfinding");
					$newwps = cPath::ArmySetRouteTo($army->id,$f_x,$f_y);
					profile_page_end();
				} else $newwps = array();
				
				if (count($newwps) == 0) {
					echo "<h3><font color=red>Fehler bei der Wegfindung</font></h3>";
					echo "(vielleicht nicht erreichbar, oder zu komplex)<br><br>";
					break;
				} else if (!$newwps) {
					echo "<h3><font color=red>Entfernung zu gross für Wegfindung</font></h3>";
					echo "(maximal 30 Felder in jede Richtung, hier waren es ".max(abs($f_x-$lastwp->x),abs($f_y-$lastwp->y)).")<br><br>";
					break;
				}
				$newwplen = count($newwps);
				$path = array();
				$blocked = false;
				for ($i=0;$i<$newwplen;++$i) {
					$x2 = $newwps[$i][0];
					$y2 = $newwps[$i][1];
					if ($i>0) {
						$x1 = $newwps[$i-1][0];
						$y1 = $newwps[$i-1][1];
					} else {
						$x1 = $lastwp->x;
						$y1 = $lastwp->y;
					}
					for ($x=$x1,$y=$y1;$x!=$x2||$y!=$y2;) {
						list($x,$y) = GetNextStep($x,$y,$x1,$y1,$x2,$y2);
						if (($x!=$x1||$y!=$y1) && ($x!=$x2||$y!=$y2)) {
							$speed = cArmy::GetPosSpeed($x,$y,$army->user,$army->units,false);
							$path[] = array($x,$y,$speed);
							if ($speed == 0 && !$blocked)
								$blocked = array($x,$y);
						}
					}
				}
				$pathlen = count($path);
				?>
				<script language="javascript">
					parent.map.jsArmy(<?=cArmy::GetJavaScriptArmyData($army)?>);
					parent.map.JSActivateArmy(<?=$army->id?>,"<?=cArmy::GetJavaScriptWPs($army->id)?>");
				</script>
				<?php
				for ($i=0;$i<$newwplen;++$i) { 
					list($x,$y) = $newwps[$i]; 
					$speed = cArmy::GetPosSpeed($x,$y,$army->user,$army->units,false);
					if ($speed == 0 && !$blocked)
						$blocked = array($x,$y);
				}
				if ($blocked) {
					list($x,$y) = $blocked; 
					?>
					<h3><font color=red>WEG BLOCKIERT BEI <a href="<?=SessionLink("../".kMapScript."?x=".$x."&y=".$y."&army=".$army->id)?>" target="map">(<?="$x,$y"?>)</a></font></h3>
					<?php
				}
			break;
			case "setwaypointlist":
				if (intval($army->flags) & kArmyFlag_SelfLock) break;
				$points = explode(" ",$f_pointlist);
				if (count($points) > 0)
				foreach ($points as $point) {
					$coords = split("[/,]",$point);
					if (count($coords) == 2)
						cArmy::ArmySetWaypoint($army->id,$coords[0],$coords[1]);
				}
				?>
				<script language="javascript">
					parent.map.location.href = parent.map.location.href;
				</script>
				<?php
			break;
			case "attackarmy":
				// todo : function AddArmyAction(army,cmd,p1,p2,p3);
				$t = false;
				$t->cmd = ARMY_ACTION_ATTACK;
				$t->army = $f_army;
				$t->param1 = intval($f_target);
				$t->starttime = 0;
				$t->orderval = sqlgetone("SELECT MAX(`orderval`)+1 FROM `armyaction` WHERE `army`=".intval($f_army));
				sql("INSERT INTO `armyaction` SET ".obj2sql($t));
			break;
			case "rangeattackarmy":
				$t = false;
				$t->cmd = ARMY_ACTION_RANGEATTACK;
				$t->army = $f_army;
				$t->param1 = intval($f_target);
				$t->starttime = 0;
				$t->orderval = sqlgetone("SELECT MAX(`orderval`)+1 FROM `armyaction` WHERE `army`=".intval($f_army));
				sql("INSERT INTO `armyaction` SET ".obj2sql($t));
			break;
			case "lageraction":
				$t = false;
				if (isset($f_pillage))		$t->cmd = ARMY_ACTION_PILLAGE;
				else if (isset($f_deposit))	$t->cmd = ARMY_ACTION_DEPOSIT;
				else break;
				$building = sqlgetobject("SELECT * FROM `building` WHERE `id` = ".intval($f_target));
				if (!$building) break;
				$t->army = $f_army;
				$t->param1 = $building->x;
				$t->param2 = $building->y;
				$t->param3 = array_sum($f_restype);
				sql("DELETE FROM `armyaction` WHERE ".obj2sql($t," AND "));
				$t->orderval = sqlgetone("SELECT MAX(`orderval`)+1 FROM `armyaction` WHERE `army`=".intval($f_army));
				sql("INSERT INTO `armyaction` SET ".obj2sql($t));
			break;
			case "wpaction":
				$t = false;
				$t->cmd = ARMY_ACTION_WAIT;
				$t->army = intval($f_army);
				$t->param1 = intval($f_target);
				$t->param2 = intval($f_warten_dauer)*60;
				$t->starttime = 0;
				sql("DELETE FROM `armyaction` WHERE `cmd` = ".$t->cmd." AND `army` = ".$t->army." AND `param1` = ".$t->param1);
				if ($t->param2 <= 0) break;
				$t->orderval = sqlgetone("SELECT MAX(`orderval`)+1 FROM `armyaction` WHERE `army`=".$t->army);
				sql("INSERT INTO `armyaction` SET ".obj2sql($t));
			break;
			case "siege":
				$building = sqlgetobject("SELECT * FROM `building` WHERE `id` = ".intval($f_target));
				if (!$building) break;
				$t = false;
				$t->cmd = ARMY_ACTION_SIEGE;
				$t->army = $f_army;
				$t->param1 = $building->x;
				$t->param2 = $building->y;
				sql("DELETE FROM `armyaction` WHERE ".obj2sql($t," AND "));
				$t->orderval = sqlgetone("SELECT MAX(`orderval`)+1 FROM `armyaction` WHERE `army`=".intval($f_army));
				sql("INSERT INTO `armyaction` SET ".obj2sql($t));
			break;
			case "armycollect":
				$terrain = sqlgetobject("SELECT * FROM `terrain` WHERE `x` = ".$army->x." AND `y` = ".$army->y." LIMIT 1");
				$coltime = cArmy::GetArmyCollectTime($army,$terrain->type);
				echo "try armycollect : ".$terrain->type." coltime $coltime";
				if ($coltime > 0 && $army->idle >= $coltime)
					cArmy::ArmyCollect($army,$terrain);
			break;
			case "itempick":
				cItem::pickupItem(intval($f_item),$army->id);
			break;
			case "itempickall":
				cItem::pickupall($army);
			break;
			case "itemdrop":
				cItem::dropItem(intval($f_item),$army->id,-1);
			break;
			case "itemdropall":
				cItem::dropAll($army);
			break;
			case "multiitemdrop":
				TablesLock();
				if(isset($f_drop)){
					foreach ($f_dropamount as $itemid => $dropamount) if ($dropamount > 0)
						cItem::dropItem(intval($itemid),$army->id,intval($dropamount));
				} else if(isset($f_save)){
					if($f_useditem == 0)sql("UPDATE `army` SET `useditem`=0 WHERE `user`=".intval($gUser->id)." AND `id`=".intval($army->id));
					else {
						if(sqlgetone("SELECT `army` FROM `item` WHERE `id`=".intval($f_useditem))==$army->id)
							sql("UPDATE `army` SET `useditem`=".intval($f_useditem)." WHERE `user`=".intval($gUser->id)." AND `id`=".intval($army->id));
					}
				}
				TablesUnlock();
			break;
			case "itemuse":
				$result = cItem::useItem(intval($f_item),$army->id);
				if ($result) {
					list($x,$y) = $result;
					$url = Query("?sid=?&x=".$x."&y=".$y);
					?>
					<script language="javascript">
						//parent.map.location.href = parent.map.location.href;
						parent.navi.navabs(<?=intval($x)?>,<?=intval($y)?>,true);
						window.location="<?=$url?>";
					</script>
					<a href='<?=$url?>'>weiter: <?=$url?></a><br>
					<?php
					exit();
				} else {
					?>
					<h3><font color=red>Hat nicht funktioniert...</font></h3>
					<?php
				}
			break;
			
			default:
				echo "infocommand : unknown army command<br>";
			break;
		}
	}
	
	
	function mydisplay () {
		foreach ($_REQUEST as $name=>$val) ${"f_".$name} = $val;
		global $gArmyType; 
		global $gObject; 
		global $gUser;
		global $gRes;
		global $gGlobal;
		global $gRes2ItemType;
		global $gUnitType;
		global $gItemType;
		global $gArmyFlagNames;
		global $gBuildingType;
		profile_page_start("army.php");
				
		$gArmies = sqlgettable("SELECT * FROM `army` WHERE `user` = ".$gUser->id,"id");
		$gArmy = $gObject;
		if (!$gArmy) exit(error("nonexistant object"));
		$gArmy->units = cUnit::GetUnits($gArmy->id);
		$gCanControllArmy =	cArmy::CanControllArmy($gArmy,$gUser);
		$gArmyAction = sqlgettable("SELECT * FROM `armyaction` WHERE `army` = ".$gArmy->id." ORDER BY `id`");
		
		// activate army in map, show wps if own army
		if (cArmy::CanControllArmy($gArmy,$gUser)) {
			?>
			<script language="javascript">
				parent.map.jsArmy(<?=cArmy::GetJavaScriptArmyData($gArmy)?>);
				parent.map.JSActivateArmy(<?=$gArmy->id?>,"<?=cArmy::GetJavaScriptWPs($gArmy->id)?>");
			</script>
			<?php
		}
		?>
		
		
		
		
		<?php /* ##### ##### #####      #####      ##### ##### ##### */ ?>
		<?php /* ##### ##### ##### FIGHT,PILLAGE,SIEGE ##### ##### ##### */ ?>
		<?php /* ##### ##### #####      #####      ##### ##### ##### */ ?>
		
		
		<?php
		$gFights = sqlgettable("SELECT * FROM `fight` WHERE `attacker`=".$gArmy->id." OR `defender`=".$gArmy->id);
		foreach($gFights as $fight) {
			if($fight->attacker == $gArmy->id)$enemy = $fight->defender;
			else $enemy = $fight->attacker;
			$enemy = sqlgetobject("SELECT * FROM `army` WHERE `id`=".$enemy);
			?>
			<span style="color:red">befindet sich im Kampf mit <a href="<?=Query("?sid=?&x=".$enemy->x."&y=".$enemy->y)?>">'<?=$enemy->name?>'</a></span>
			<?php if($gUser->admin){ ?>
				<a href="<?=Query("?sid=?&x=?&y=?&army=$gObject->id&do=admin_fight_step&id=$fight->id&steps=1")?>">(s1)</a>
				<a href="<?=Query("?sid=?&x=?&y=?&army=$gObject->id&do=admin_fight_step&id=$fight->id&steps=10")?>">(s10)</a>
				<a href="<?=Query("?sid=?&x=?&y=?&army=$gObject->id&do=admin_fight_step&id=$fight->id&steps=60")?>">(s60)</a>
			<?php } ?>
			<br>
			<?php 
		}
		$gPillage = sqlgetobject("SELECT * FROM `pillage` WHERE `army`=".$gArmy->id);
		if ($gPillage) {
			$target = sqlgetobject("SELECT * FROM `building` WHERE `id`=".$gPillage->building);
			?>
			<span style="color:red">plündert gerade <?=cArmy::DrawPillageRes($gPillage->type)?> aus <a href="<?=Query("?sid=?&x=".$target->x."&y=".$target->y)?>">Lager (<?=$target->x?>|<?=$target->y?>)</a></span>
			<?php if($gCanControllArmy) {?><a href="<?=Query("?sid=?&x=?&y=?&army=".$gArmy->id."&do=cancelpillage&id=".$gPillage->id)?>"><img border=0 src="<?=g("del.png")?>"></a><?php } ?>
			<?php if($gUser->admin){ ?>
				<a href="<?=Query("?sid=?&x=?&y=?&army=$gObject->id&do=admin_pillage_step&id=$gPillage->id&steps=1")?>">(s1)</a>
				<a href="<?=Query("?sid=?&x=?&y=?&army=$gObject->id&do=admin_pillage_step&id=$gPillage->id&steps=10")?>">(s10)</a>
				<a href="<?=Query("?sid=?&x=?&y=?&army=$gObject->id&do=admin_pillage_step&id=$gPillage->id&steps=60")?>">(s60)</a>
				<a href="<?=Query("?sid=?&x=?&y=?&army=$gObject->id&do=admin_pillage_step&id=$gPillage->id&steps=".(24*60))?>">(s24*60)</a>
			<?php } ?>
			<br>
			<?php 
		}
		$gSiege = sqlgetobject("SELECT * FROM `siege` WHERE `army`=".$gArmy->id);
		if ($gSiege) {
			$target = sqlgetobject("SELECT * FROM `building` WHERE `id` = ".intval($gSiege->building));
			$btype = $gBuildingType[$target->type];
			?>
			<span style="color:red">belagert gerade <a href="<?=Query("?sid=?&x=".$target->x."&y=".$target->y)?>"><?=$btype->name?> (<?=$target->x?>|<?=$target->y?>)</a></span>
			<?php if($gCanControllArmy) {?><a href="<?=Query("?sid=?&x=?&y=?&army=".$gArmy->id."&do=cancelsiege&id=".$gSiege->id)?>"><img border=0 src="<?=g("del.png")?>"></a><?php } ?>
			<?php if($gUser->admin){ ?>
				<a href="<?=Query("?sid=?&x=?&y=?&army=$gObject->id&do=admin_siege_step&id=$gSiege->id&steps=1")?>">(s1)</a>
				<a href="<?=Query("?sid=?&x=?&y=?&army=$gObject->id&do=admin_siege_step&id=$gSiege->id&steps=10")?>">(s10)</a>
				<a href="<?=Query("?sid=?&x=?&y=?&army=$gObject->id&do=admin_siege_step&id=$gSiege->id&steps=60")?>">(s60)</a>
				<a href="<?=Query("?sid=?&x=?&y=?&army=$gObject->id&do=admin_siege_step&id=$gSiege->id&steps=".(24*60))?>">(s24*60)</a>
			<?php } ?>
			<br>
			<?php 
		}?>
		<br>

		
		<?php /* ##### ##### #####   #####   ##### ##### ##### */ ?>
		<?php /* ##### ##### ##### ARMY UNITS ##### ##### ##### */ ?>
		<?php /* ##### ##### #####   #####   ##### ##### ##### */ ?>



		<?php /* #### Armeegrösse #### */ ?>
		<?php 
			$max_army_weight = cUnit::GetMaxArmyWeight($gArmy->type);
			$army_unit_weight = cUnit::GetUnitsSum($gArmy->units,"weight");
			$fill = max(0.0,$army_unit_weight/$max_army_weight);
			
			$max_army_last = cUnit::GetUnitsSum($gArmy->units,"last");
			$cur_army_last = cArmy::GetArmyTotalWeight($gArmy);
			$fill2 = max(0.0,$cur_army_last/$max_army_last);
		?>
		<table width=60%><tr><td align=left nowrap><?=cText::Wiki("armysize")?> ArmeeLimit (<?=ktrenner(intval($army_unit_weight))?>/<?=ktrenner(intval($max_army_weight))?>):</td>
		<td align=right width="60%"><?php DrawBar(min(1.0,$fill),1);?></td>
		<td>&nbsp;<?=intval($fill*100.0)."%"?></td></tr>
		</table>
		
		<table width=60%><tr><td align=left nowrap><?=cText::Wiki("Auslastung")?> Auslastung (<?=ktrenner(intval($cur_army_last))?>/<?=ktrenner(intval($max_army_last))?>):</td>
		<td align=right width="60%"><?php DrawBar(min(1.0,$fill2),1);?></td>
		<td>&nbsp;<?=intval($fill2*100.0)."%"?></td></tr>
		</table>
		
		<?php /* #### Sailor #### */ ?>
		<?php
		if ($gArmy->type == kArmyType_Fleet && $gCanControllArmy) {
			$gArmy->transport = cUnit::GetUnits($gArmy->id,kUnitContainer_Transport);
			$gArmy->maxtransp = cUnit::GetUnitsSum($gArmy->units,"last");
			$sailors = cUnit::GetUnitsSailors($gArmy->transport);
			echo "Effektiv ".round(100*$sailors/$gArmy->maxtransp)."% der Maximalen Besatzung haben Seglerfähigkeiten<br>";
			$speed = cArmy::GetArmySpeed($gArmy);
			if ($speed > 0)
				echo "Damit ist die Reisegeschwindigkeit der Flotte ".Duration2Text(round($speed))."/Feld (ohne Terraineffekte)<br>";
			else	echo "Die Flotte kann so nicht in See stechen, es werden mehr Matrosen benötigt.<br>";
		} else $gArmy->transport = false;
		?>
		
		<?php /* #### Einheitenliste #### */ ?>
		<?php ImgBorderStart("s1","jpg","#ffffee","",32,33);?>
			<?php cText::UnitsList($gArmy->units,$gArmy->user,"",false,$gArmy->idle); ?>
		<?php ImgBorderEnd("s1","jpg","#ffffee",32,33);?>
		<br>
		
		
		
		<?php /* ##### ##### #####   #####   ##### ##### ##### */ ?>
		<?php /* ##### ##### ##### system-flags ##### ##### ##### */ ?>
		<?php /* ##### ##### #####   #####   ##### ##### ##### */ ?>
		
		
		
		<?php if (intval($gArmy->flags) & kArmyFlag_Captured) {?>
			<?=cText::Wiki("gekapert",0,true)?> Diese <?=$gArmyType[$gArmy->type]->name?> ist gekapert,
			Sie muss erst zu einem Hafen gebracht und aufgelöst werden, um die Schiffe den eigenen Flottenverbänden einzuverleiben,
			bis dahin kann sie nicht kämpfen (Angriff=0).
		<?php } // endif?>
		
		
		
		<?php /* ##### ##### #####   #####   ##### ##### ##### */ ?>
		<?php /* ##### ##### ##### ARMY Ressources ##### ##### ##### */ ?>
		<?php /* ##### ##### #####   #####   ##### ##### ##### */ ?>
		
		
		<table border=1 cellspacing=0>
		<tr>
			<?php foreach($gRes as $n=>$f)echo '<td align="center" width=40><img alt="'.$f.'" src="'.g("res_$f.gif").'"></td>'; ?>
			<th>Gesamt</th>
			<th>Max</th>
			<th colspan=2>Auslastung</th>
		</tr>
		<tr>
			<?php $maxlast = floor(cUnit::GetUnitsSum(cUnit::GetUnits($gArmy->id),"last"));?>
			<?php $load = cArmy::GetArmyTotalWeight($gArmy);?>
			<?php foreach($gRes as $n=>$f)echo '<td nowrap align="right">'.ktrenner(floor($gArmy->$f)).'</td>';?>
			<td nowrap align="right"><?php echo ktrenner(floor($load)); ?></td>
			<td align="right"><?=ktrenner($maxlast)?></td>
			<td width=100 height=10>
				<?php $fill = min(1.0,max(0.0,($maxlast > 0)?($load/$maxlast):0))?>
				<?php DrawBar($fill,1);?>
			</td>
			<td><?=intval($fill*100.0)?>%</td>
		</tr>
		<?php if ($gCanControllArmy) {?>
			<form method="post" action="<?=query("?sid=?&x=?&y=?")?>">
			<input type="hidden" name="army" value="<?=$gArmy->id?>">
			<input type="hidden" name="do" value="armydropres">
			<tr>
				<?php foreach($gRes as $n=>$f)echo '<td align="center"><input name="'.$f.'" type="text" size="8" value="'.floor($gArmy->$f).'"></td>'; ?>
				<td align="left" colspan="4"><input type="submit" value="ablegen"></td>
			</tr>
			</form>
		<?php } // endif?>
		</table>
		
		
		
		<?php /* ##### ##### #####   #####   ##### ##### ##### */ ?>
		<?php /* ##### ##### ##### Transfer ##### ##### ##### */ ?>
		<?php /* ##### ##### #####   #####   ##### ##### ##### */ ?>
		
		
		
		<?php if ($gArmy->user) cTransfer::display_armytransfer(0,$gArmy); ?>
		
		
		
		<?php /* ##### ##### #####   #####   ##### ##### ##### */ ?>
		<?php /* ##### ##### ##### ARMY ITEMS ##### ##### ##### */ ?>
		<?php /* ##### ##### #####   #####   ##### ##### ##### */ ?>
		
		
		
		<?php $gArmyItems = sqlgettable("SELECT * FROM `item` WHERE `army`=$gArmy->id ORDER BY `type`"); ?>
		<?php if(sizeof($gArmyItems)>0) {?>
		<br>
		<form method="post" action="<?=Query("?sid=?&x=?&y=?")?>">
			<input type="hidden" name="do" value="multiitemdrop">
			<input type="hidden" name="army" value="<?=$gArmy->id?>">
			<table border=1 cellspacing=0>
			<tr><td colspan=6><input type=radio name=useditem value=0 <?=$gArmy->useditem==0?"checked":""?>> kein Gegenstand aktiviert</td></tr>
			<?php foreach($gArmyItems as $i) {?>
				<tr>
				<?php if ($gCanControllArmy) { // doch cancontroll.. gc ist der erstatz für das account daten austauschen.?>
					<td><input type=radio name=useditem value=<?=$i->id?> <?=$gArmy->useditem==$i->id?"checked":""?>></td>
					<td>
						<?php if (cItem::canUseItem($i,$gArmy)) {?>
							<a href="<?=query("?sid=?&x=?&y=?&do=itemuse&item=".$i->id."&army=".$gArmy->id)?>">
							<img src="<?=g("res_mana.gif")?>" border=0 alt=benutzen title=benutzen></a>
						<?php }?>
					</td>
					<td nowrap>
						<input type="text" name="dropamount[<?=$i->id?>]" value="0" style="width:30px">/<?=floor($i->amount)?>
					</td>
				<?php } else {?>
					<td nowrap align="right"><?=ktrenner(floor($i->amount))?></td>
				<?php }?>
				<td><img title="<?=$gItemType[$i->type]->name?>" alt="<?=$gItemType[$i->type]->name?>" src="<?=g($gItemType[$i->type]->gfx)?>"></td>
				<td nowrap><?=cText::Wiki("item",$gItemType[$i->type]->id)?><?=$gItemType[$i->type]->name?></td>
				<td><?=$gItemType[$i->type]->descr?></td>
				</tr>
			<?php }?>
			</table>
			<?php if ($gUser->id == $gArmy->user) {?>
				<input type="submit" name="save" value="übernehmen">
				<input type="submit" name="drop" value="ablegen">
				<a href="<?=query("?sid=?&x=?&y=?&do=itemdropall&army=".$gArmy->id)?>">
					<img src="<?=g("drop.png")?>" border=0 alt="ablegen" title="ablegen">alles ablegen
				</a>
			<?php } // endif?>
		</form>
		<?php } // endif armyitems count > 0 ?>
		
		
		
		<?php /* ##### ##### #####   #####   ##### ##### ##### */ ?>
		<?php /* ##### ##### ##### ARMY COMMANDS ##### ##### ##### */ ?>
		<?php /* ##### ##### #####   #####   ##### ##### ##### */ ?>
		
		
		
		<?php if ($gCanControllArmy) {?>
		
			<?php /* ###### Flucht ###### */ ?>
			<?php if (count($gFights) > 0) {?>
				<h3>Flucht</h3>
				<?php 
				$escape_n = cArmy::GetPosSpeed($gArmy->x,$gArmy->y-1,$gArmy->user,$gArmy->units) > 0;
				$escape_w = cArmy::GetPosSpeed($gArmy->x-1,$gArmy->y,$gArmy->user,$gArmy->units) > 0;
				$escape_s = cArmy::GetPosSpeed($gArmy->x,$gArmy->y+1,$gArmy->user,$gArmy->units) > 0;
				$escape_e = cArmy::GetPosSpeed($gArmy->x+1,$gArmy->y,$gArmy->user,$gArmy->units) > 0;
				
				$gArmy->flucht_units = cUnit::GetUnitsAfterEscape($gArmy->units,$gArmy->user);
				$gArmy->verluste = cUnit::GetUnitsDiff($gArmy->units,$gArmy->flucht_units);
				?>
				<?php if ($escape_n || $escape_w || $escape_s || $escape_e) {?>
					Flucht möglich nach : 
					<form method="post" action="<?=Query("?sid=?&x=?&y=?")?>">
					<input type="hidden" name="army" value="<?=$gArmy->id?>">
					<input type="hidden" name="do" value="flee">
					<table class="mapnav" cellpadding="0" cellspacing="0">
						<tr>
						<td></td>
						<td><?php if ($escape_n) {?><input type="image" src="<?=g("scroll/n.png")?>" name="escape_n"><?php }?></td>
						<td></td>
						</tr><tr>
						<td><?php if ($escape_w) {?><input type="image" src="<?=g("scroll/w.png")?>" name="escape_w"><?php }?></td>
						<td></td>
						<td><?php if ($escape_e) {?><input type="image" src="<?=g("scroll/e.png")?>" name="escape_e"><?php }?></td>
						</tr><tr>
						<td></td>
						<td><?php if ($escape_s) {?><input type="image" src="<?=g("scroll/s.png")?>" name="escape_s"><?php }?></td>
						<td></td>
						</tr>
					</table>
					</form>
					Warnung, bei einer Flucht verliert man alle Gegenstände,<br>
					die Hälfte der Frags und die Hälfte der Truppenstärke :<br>
					<table border=1 cellspacing=0><tr>
						<?php foreach ($gArmy->verluste as $o) { $key = $o->type; $val = $o->amount;?>
							<th><img src="<?=g($gUnitType[$key]->gfx)?>" alt="<?=$gUnitType[$key]->name?>" title="<?=$gUnitType[$key]->name?>" class="picframe"></th>
						<?php }?>
					</tr><tr>
						<?php foreach ($gArmy->verluste as $o) { $key = $o->type; $val = $o->amount;?>
							<td align="right"><?=ceil($val)?></td>
						<?php }?>
					</tr>
					</table>
					Am besten schon vor der Flucht die nächsten Wegpunkte setzen, damit die Armee gleich losrennen kann.<br>
				<?php } else {?>
					Flucht nicht möglich
				<?php } // endif?>
			<?php } // endif not fighting?>
			
			
			<?php /* ###### Wegpunkte ###### */ ?>
			<h3><?=cText::Wiki("Wegpunkte")?>Wegpunkte</h3>
			<?php 
			$gWaypoints = sqlgettable("SELECT * FROM `waypoint` WHERE `army` = ".$gArmy->id." ORDER BY `priority`");
			$wplen = count($gWaypoints);
			$routelength = 0;
			$start->x = $gArmy->x;
			$start->y = $gArmy->y; 
			foreach($gWaypoints as $x) if ($x->priority > 0) {
				$routelength += abs($x->x - $start->x) + abs($x->y - $start->y);
				$start->x = $x->x;
				$start->y = $x->y;
			}
			?> 
			
			Routenlänge: <?=$routelength?> Felder , <?=max(0,$wplen-1)?> Wegpunkte<br>
			<?php foreach ($gWaypoints as $o) if ($o->priority > 0) {?>
			<a href="<?=SessionLink("../".kMapScript."?x=".$o->x."&y=".$o->y."&army=".$gArmy->id)?>" target="map"><?=$o->x?>,<?=$o->y?></a>
			<?php }?>
			<br>
		
			<FORM METHOD=POST ACTION="<?=Query("?sid=?&x=?&y=?")?>">
				<INPUT TYPE="hidden" NAME="do" VALUE="setwaypointlist">
				<INPUT TYPE="hidden" NAME="army" VALUE="<?=$gArmy->id?>">
				<INPUT TYPE="text" NAME="pointlist" style="width:200px">
				<INPUT TYPE="submit" VALUE="wegpunkte hinzufügen">(z.b. "-1,10 5,-10 5,20")
			</FORM>
			<?php if ($wplen > 0) {?>
			<a href="<?=Query("?sid=?&x=?&y=?&do=delallwaypoints&army=".$gArmy->id)?>">(alle Wegpunkte löschen)</a><br>
			<a href="<?=Query("?sid=?&x=?&y=?&calctraveltime=".$gArmy->id)?>">(Reisezeit Berechnen)</a><br>
			<a href="<?=Query("?sid=?&x=?&y=?&do=returnwaypoints&army=".$gArmy->id)?>">(Rückweg setzen)</a><br>
			<a href="javascript:WPMap(<?=$gArmy->id?>)">(WPMap)</a><br>
			<?php }?>
			Man kann Wegpunkte auch bequem in der Karte setzen, in dem man <img src='<?=g("tool_wp.png")?>'> für einzelne Wegpunkte und <img src='<?=g("tool_route.png")?>'> für die Wegfindung verwendet,
			funktioniert allerdings nur auf kurze Entfernungen.<br>
			
			<?php if (isset($f_calctraveltime) && intval($f_calctraveltime) == $gArmy->id) {
				$blocked = false;
				$traveltime = 0;
				for ($i=0;$i<$wplen-1&&!$blocked;++$i) {
					$x1 = $gWaypoints[$i]->x;
					$y1 = $gWaypoints[$i]->y;
					$x2 = $gWaypoints[$i+1]->x;
					$y2 = $gWaypoints[$i+1]->y;
					if ($i == 0) 
							{ $x=$gArmy->x; $y=$gArmy->y; }
					else 	{ $x=$x1; $y=$y1; }
					for (;$x!=$x2||$y!=$y2;) {
						list($x,$y) = GetNextStep($x,$y,$x1,$y1,$x2,$y2);
						$speed = min(cArmy::GetArmySpeed($gArmy),cArmy::GetPosSpeed($x,$y,$gUser->id,$gArmy->units));
						if ($speed == 0)  { $blocked = array($x,$y); break; }
						$traveltime += $speed;
					}
				}
				?>
				<h4>Reisezeit Berechnung</h4>
				<?php if ($blocked) { list($x,$y) = $blocked; ?>
					<h3><font color=red>WEG BLOCKIERT BEI <a href="<?=SessionLink("../".kMapScript."?x=".$x."&y=".$y."&army=".$gArmy->id)?>" target="map">(<?=$x?>,<?=$y?>)</a></font></h3>
				<?php }?>
				Die Reisezeit <?=$blocked?" <b>bis zum Hindernis</b>":""?> beträgt mindestens <?=Duration2Text($traveltime)?>,<br>
				Ankunft frühestens <?=date("d.m H:i",time()+$traveltime)?><br>
				Durch Kämpfe, Hindernisse, Befehle, Rohstoffsammlungen und diverse andere Ereignisse, kann die Armee natürlich aufgehalten werden.
				<?php
			} /* endif calctraveltime   */?>
			
			
			
			
			<?php /* ###### sonstige Aktionen ###### */ ?>
			<h3><?=cText::Wiki("ArmeeAktionen")?>sonstige Aktionen</h3>
		
			<?php $terrain = sqlgetobject("SELECT * FROM `terrain` WHERE `x` = ".$gArmy->x." AND `y` = ".$gArmy->y." LIMIT 1");?>
			<?php if (($coltime = cArmy::GetArmyCollectTime($gArmy,$terrain->type)) == 0) { ?>
				Auf diesem Gelände kann nichts geerntet werden.<br>
			<?php } else if ($gArmy->idle < $coltime) { ?>
				Noch <?=$rest = ceil(($coltime - $gArmy->idle)/60)?> <?=($rest > 1)?"Minuten":"Minute"?> bis das Ernten möglich ist.<br>
			<?php } else { ?>
				<form method=post action="<?=Query("?sid=?&x=?&y=?&army=".$gArmy->id."&do=armycollect")?>">
				<input type="submit" name="armycollect" value="einsammeln"></form>
			<?php } ?>
			<?php if($gArmy->nextactiontime - time() > 0) {?>
				Nächste Aktion in <?=$gArmy->nextactiontime - time()?> Sekunden<br>
			<?php } ?>
			<?php $sum = cUnit::GetUnitsSum($gArmy->units);?>
			<?php if($sum > kArmy_BigArmyGoSlowLimit) {?>
				Geschwindigkeitsfaktor wegen Armeegrösse (<?=floor($sum)?>) : <?=round(pow(kArmy_BigArmyGoSlowFactorPer1000Units,($sum - kArmy_BigArmyGoSlowLimit) / 1000),2)?><br>
			<?php } ?>
			Armee Geschwindigkeit (soviele Sekunden muss sie pro feld mindestens warten): <?=ceil(cArmy::getArmySpeed($gArmy))?><br>
			
			
			
			
			
			
			<?php /* ###### --Verhalten-- ###### */ ?>
			<h3><?=cText::Wiki("ArmeeVerhalten")?>Verhalten</h3>
			<FORM METHOD=POST ACTION="<?=Query("?sid=?&x=?&y=?")?>">
			<INPUT TYPE=HIDDEN NAME="army" VALUE='<?=$gArmy->id?>'>
			<INPUT TYPE=HIDDEN NAME="do" VALUE="change_flags">
			<table>
			<?php foreach ($gArmyFlagNames as $flag => $name) if (intval($gArmyType[$gArmy->type]->ownerflags) & $flag) {?>
			<tr>
				<td><input type="checkbox" name="flags[]" value="<?=$flag?>" <?=(intval($gArmy->flags) & $flag)?"checked":""?>></td>
				<td><?=$name?></td>
			</tr>
			<?php }?>
			</table>
			<INPUT TYPE="submit" VALUE="speichern">
			</FORM>	
			
			
			
			
			<?php /* ###### --Befehle-- ###### */ ?>
			<h3>Befehle</h3>
			<?php 
			$gCmds = sqlgettable("SELECT * FROM `armyaction` WHERE `army` = ".$gArmy->id." ORDER BY `orderval` ASC");
			if (count($gCmds) > 0) {?>
			
				<FORM METHOD=POST ACTION="<?=Query("?sid=?&x=?&y=?")?>">
				<INPUT TYPE="hidden" NAME="do" VALUE="cancelaction">
				<INPUT TYPE="hidden" NAME="army" VALUE="<?=$gArmy->id?>">
				<table border=1 cellspacing=0>
				<tr>
				<?php $i = 0;foreach($gCmds as $c) {?>
					<tr><td>
						<input type="checkbox" name="cancelids[]" value="<?=$c->id?>">
					</td><td>			
					<?php switch ($c->cmd) {
						case ARMY_ACTION_RANGEATTACK:
							$target = sqlgetobject("SELECT * FROM `army` WHERE `id`=".$c->param1);
							if($target) {?>
								Beschuss von <a href="<?=Query("?sid=?&x=".$target->x."&y=".$target->y)?>">
								<?=$target->name?>
								(<?=$target->x?>|<?=$target->y?>)</a>
							<?php } else sql("DELETE FROM `armyaction` WHERE `id`=".$c->id);
						break;
						case ARMY_ACTION_ATTACK:
							$target = sqlgetobject("SELECT * FROM `army` WHERE `id`=".$c->param1);
							if($target) {?>
								Angriff auf <a href="<?=Query("?sid=?&x=".$target->x."&y=".$target->y)?>">
								<?=$target->name?>
								(<?=$target->x?>|<?=$target->y?>)</a>
							<?php } else sql("DELETE FROM `armyaction` WHERE `id`=".$c->id);
						break;
						case ARMY_ACTION_PILLAGE:
							$target = sqlgetobject("SELECT * FROM `building` WHERE `x`=".$c->param1." AND `y`=".$c->param2);
							if ($target) {?>
								<?=cArmy::DrawPillageRes($c->param3)?> plündern aus <a href="<?=Query("?sid=?&x=".$target->x."&y=".$target->y)?>">
								<?=$gBuildingType[$target->type]->name?>
								(<?=$target->x?>|<?=$target->y?>)</a>
							<?php } else sql("DELETE FROM `armyaction` WHERE `id`=".$c->id);
						break;
						case ARMY_ACTION_DEPOSIT:
							$target = sqlgetobject("SELECT * FROM `building` WHERE `x`=".$c->param1." AND `y`=".$c->param2);
							if ($target) {?>
								<?=cArmy::DrawPillageRes($c->param3)?> einzahlen in <a href="<?=Query("?sid=?&x=".$target->x."&y=".$target->y)?>">
								<?=$gBuildingType[$target->type]->name?>
								(<?=$target->x?>|<?=$target->y?>)</a>
							<?php } else sql("DELETE FROM `armyaction` WHERE `id`=".$c->id);
						break;
						case ARMY_ACTION_SIEGE:
							$target = sqlgetobject("SELECT * FROM `building` WHERE `x`=".$c->param1." AND `y`=".$c->param2);
							if ($target) {?>
								<a href="<?=Query("?sid=?&x=".$target->x."&y=".$target->y)?>">
								<?=$gBuildingType[$target->type]->name?>
								(<?=$target->x?>|<?=$target->y?>)</a> belagern
							<?php } else sql("DELETE FROM `armyaction` WHERE `id`=".$c->id);
						break;
						case ARMY_ACTION_WAIT:
							$wp = sqlgetobject("SELECT * FROM `waypoint` WHERE `id` = ".$c->param1);
							echo "bei WegPunkt ".$wp->priority." ".pos2txt($wp->x,$wp->y)." ".$c->param2." Sekunden warten"; break;
					}?></td>
					</tr>
				<?php }?>
				</table>
				<INPUT TYPE="submit" VALUE="entfernen">
				</FORM>
			<?php } else {?>
				keine<br>
			<?php }?>
			<br>
		<?php } // endif can controll army?>
		
		<?php 
		profile_page_end(); 
	}
	
	
	// above mydisplay
	function display_header() {
		global $gObject;
		global $gUser;
		global $gUnitType;
		$units = cUnit::GetUnits($gObject->id);
		$gArmies = cArmy::getMyArmies(TRUE);
		?>
		
		<table border=0 cellspacing=0 cellpadding=0 width="100%">
		<tr><td nowrap valign="top" width=30>
				<img style="background-color:<?=sqlgetone("SELECT `color` FROM `user` WHERE `id`=".$gObject->user)?>" src="<?=g($gUnitType[cUnit::GetUnitsMaxType($units)]->gfx)?>" border=1>
			</td>
			<td nowrap valign="top" align="left">
				<table border=0 cellspacing=0 cellpadding=0>
				<tr>
					<td><b><?=$gObject->name?></b> (<?=floor($gObject->frags)?> Frags,<?=floor($gObject->idle/60)?>min idle) <?=cText::Wiki("army_frags_idle")?>
					<?php if($gUser->admin){ ?>
						<a href="<?=query("adminarmy.php?id=$gObject->id&sid=?")?>"><img alt=Army title=Army src="<?=g("icon/admin.png")?>" border=0></a>
						<a href="<?=query("adminunit.php?containerid=$gObject->id&containertype=".kUnitContainer_Army."&sid=?")?>"><img alt=Units title=Units src="<?=g("icon/admin.png")?>" border=0></a>
						<a href="<?=query("adminunit.php?containerid=$gObject->id&containertype=".kUnitContainer_Transport."&sid=?")?>"><img alt=Transport title=Transport src="<?=g("icon/admin.png")?>" border=0></a>
						<a href="<?=query("adminarmytype.php?id=$gObject->type&sid=?")?>"><img alt=ArmyType title=ArmyType src="<?=g("icon/admin.png")?>" border=0></a>
						<a href="<?=Query("?sid=?&x=?&y=?&army=$gObject->id&do=admin_armystep&minutes=5")?>">(think)</a><br>
						<?php $hellhole = $gObject->hellhole ? sqlgetobject("SELECT * FROM `hellhole` WHERE `id` = ".$gObject->hellhole) : false;?>
						<?php if ($hellhole) echo opos2txt($hellhole);?>
					<?php } ?>
					</td>
					<td nowrap rowspan=2 align="left">
						<?php if ($gUser->admin && $gObject->user != $gUser->id) {?>
							<FORM METHOD=POST ACTION="<?=Query("../symbiose.php?sid=?")?>" target="_parent">
							<INPUT TYPE="hidden" NAME="uid" VALUE="<?=$gObject->user?>">
							<INPUT TYPE="submit" VALUE="sym" style="width:30px">
							</FORM>
						<?php }?>
					</td>
				</tr>
				<tr>
					<td nowrap>
					<?php if ($gObject->user>0) {?>
						<?php $ownername = sqlgetone("SELECT `name` FROM `user` WHERE `id` = ".$gObject->user);?>
						<?php $ownerhq = sqlgetobject("SELECT * FROM `building` WHERE `type` = ".kBuilding_HQ." AND `user` = ".$gObject->user);?>
						von <a href="<?=query("?sid=?&x=".$ownerhq->x."&y=".$ownerhq->y)?>"><?=GetFOFtxt($gUser->id,$gObject->user,$ownername)?></a>
					<?php }?>
					</td>
				</tr>
				</table>
			</td>
			<td align="right">
				<?php $melee_list = array();
				foreach($gArmies as $o) if ($gObject->id != $o->id && cArmy::hasMeleeAttack($o->id)) $melee_list[] = $o; ?>
				<?php if (sizeof($melee_list) > 0) {?>
					<!--armee angreiffen-->
					<form method="post" action="<?=Query("?sid=?&x=?&y=?&do=attackarmy&target=".$gObject->id)?>">
					mit <SELECT NAME="army">
					<?php foreach($melee_list as $o) {?>
						<OPTION VALUE="<?=$o->id?>" <?=($o->id == $gUser->lastusedarmy)?"selected":""?>><?=$o->name?> <?=($o->user!=$gUser->id)?("(".$o->owner.")"):""?></OPTION>
					<?php }?>
					</SELECT>
					<input type="submit" value="angreifen">
					</form>
				<?php }?>
				<?php 
				$dist_list = array();
				foreach ($gArmies as $o) if ($gObject->id != $o->id && cArmy::hasDistantAttack($o)) $dist_list[] = $o; ?>
				<?php if (sizeof($dist_list) > 0) {?>
					<!--armee angreiffen-->
					<form method="post" action="<?=Query("?sid=?&x=?&y=?&do=rangeattackarmy&target=".$gObject->id)?>">
					mit <SELECT NAME="army">
					<?php foreach($dist_list as $o) {?>
						<OPTION VALUE="<?=$o->id?>" <?=($o->id == $gUser->lastusedarmy)?"selected":""?>><?=$o->name?> <?=($o->user!=$gUser->id)?("(".$o->owner.")"):""?></OPTION>
					<?php }?>
					</SELECT>
					<input type="submit" value="beschießen">
					</form>
				<?php }?>
			</td>
		</tr>
		</table>
		<?php
	}
}
?>