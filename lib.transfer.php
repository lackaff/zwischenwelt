<?php

// TODO : 30,kUnitType_TransportShip : alle 30 minuten kommt die lastsumme der transporter zu den ausladbaren einheiten dazu...

class cTransfer {

	// ##### ##### ##### ##### ##### ##### ##### #####
	// ##### ##### #####   ArmyTransfer   ##### ##### ####
	// ##### ##### ##### ##### ##### ##### ##### #####
	
	// used in adminarmytype and adminarmytransfer
	function GetArmyTransferName ($id) {
		global $gArmyTransfer,$gBuildingType,$gArmyType,$gUnitType;
		$t = $gArmyTransfer[$id];
		$sourcename = $t->sourcebuildingtype?$gBuildingType[$t->sourcebuildingtype]->name:$gArmyType[$t->sourcearmytype]->name;
		$sourcename .= $t->sourcetransport?"(t)":"";
		$targetname = $gArmyType[$t->targetarmytype]->name;
		$targetname .= $t->transportarmytype?("(t:".$gArmyType[$t->transportarmytype]->name.")"):"";
		$targetname .= $t->unitsbuildingtype?("(t:".$gBuildingType[$t->unitsbuildingtype]->name.")"):"";
		$mod = $t->idlemod ? ($t->idlemod . ($t->transportertype?("(".$gUnitType[$t->transportertype]->name.")"):"")) : "";
		if ($mod != "") $mod = "_(idle:$mod)";
		return $t->name."_".$sourcename."_".$targetname.$mod;
	}
	
	// $sourcebuilding XOR $sourcearmy , exactly one of them is not false
	function display_armytransfer($sourcebuilding,$sourcearmy) {
		global $gArmyTransfer;
		foreach ($gArmyTransfer as $transfer) 
			cTransfer::ArmyTransferBox($transfer,$sourcebuilding,$sourcearmy);
	}
	function has_armytransfer($sourcebuilding,$sourcearmy) {
		global $gArmyTransfer,$gUser;
		foreach ($gArmyTransfer as $transfer) 
			if (cTransfer::ArmyTransferVisible($transfer,$sourcebuilding,$sourcearmy,$gUser)) return true;
		return false;
	}
	
	// check if displayed , $user must be object !
	function ArmyTransferVisible ($transfer,$sourcebuilding,$sourcearmy,$user) {
		global $gArmyTransfer;
		if (!is_object($transfer)) $transfer = $gArmyTransfer[$transfer];
		if (!$transfer) return false;
		if ($sourcebuilding && !$transfer->sourcebuildingtype) return false;
		if ($sourcebuilding && !is_object($sourcebuilding)) $sourcebuilding = sqlgetobject("SELECT * FROM `building` WHERE `id` = ".intval($sourcebuilding));
		if ($sourcearmy && !is_object($sourcearmy)) $sourcearmy = sqlgetobject("SELECT * FROM `army` WHERE `id` = ".intval($sourcearmy));
		if (!$sourcebuilding && !$sourcearmy) return false;
		if ($sourcebuilding && $sourcearmy) return false;
		if ($sourcebuilding && !cBuilding::CanControllBuilding($sourcebuilding,$user)) return false;
		if ($sourcearmy && !cArmy::CanControllArmy($sourcearmy,$user)) return false;
		if ($sourcebuilding && $transfer->sourcebuildingtype != $sourcebuilding->type) return false;
		if ($sourcearmy && $transfer->sourcearmytype != $sourcearmy->type) return false;
		return true;
	}
	
	// $transfer must be object, $unitarr = array(typeid => amount) 
	function FilterUnitArr($transfer,$unitarr) {
		global $gUnitType;
		$unitsarmytype = $transfer->transportarmytype ? $transfer->transportarmytype : $transfer->targetarmytype;
		$res = array();
		//foreach ($unitarr as $typeid => $amount)  echo "armytype $typeid:".$gUnitType[$typeid]->armytype."/".$unitsarmytype."<br>";
		//foreach ($unitarr as $typeid => $amount)  echo "unitsbuildingtype $typeid:".$gUnitType[$typeid]->buildingtype."/".$transfer->unitsbuildingtype."<br>";
		foreach ($unitarr as $typeid => $amount) 
			if ($gUnitType[$typeid]->armytype == $unitsarmytype &&
				(!$transfer->unitsbuildingtype || $transfer->unitsbuildingtype == $gUnitType[$typeid]->buildingtype)) 
					$res[$typeid] = $amount;
		//else if (!($gUnitType[$typeid]->armytype == $unitsarmytype)) echo "$typeid : wrong atype<br>";
		//else if (!(!$transfer->unitsbuildingtype || $transfer->unitsbuildingtype == $gUnitType[$typeid]->buildingtype)) echo "$typeid : wrong btype<br>";
		//vardump2($unitarr);
		return $res;
	}
		
	// $sourcebuilding XOR $sourcearmy
	// $transfer,$sourcebuilding,$sourcearmy must be objects
	function ArmyTransferBox ($transfer,$sourcebuilding,$sourcearmy) {
		global $gUnitType,$gArmyType,$gUser;
		assert(is_object($sourcearmy) || is_object($sourcebuilding));
		if (!cTransfer::ArmyTransferVisible($transfer,$sourcebuilding,$sourcearmy,$gUser)) return;
		
		// get information about source
		$sourceuserid = $sourcebuilding ? $sourcebuilding->user : $sourcearmy->user;
		$bgcolor = sqlgetone("SELECT `color` FROM `user` WHERE `id` = ".intval($sourceuserid));
		$armylist = sqlgettable("SELECT * FROM `army` WHERE `user` = ".$sourceuserid." AND ".($sourcearmy?("`id` <> ".$sourcearmy->id):1)." AND `type` = ".$transfer->targetarmytype);
		$x = $sourcebuilding ? $sourcebuilding->x : $sourcearmy->x;
		$y = $sourcebuilding ? $sourcebuilding->y : $sourcearmy->y;
		$can_create_new_army = ($transfer->transportarmytype==0) && cArmy::CanCreateNewArmy($sourceuserid,$transfer->targetarmytype);
		if (count($armylist) == 0 && !$can_create_new_army) return;
		
		// get units
		if ($sourcebuilding)
				$units_here = cUnit::GetUnits($sourcebuilding->id,kUnitContainer_Building);
		else	$units_here = cUnit::GetUnits($sourcearmy->id,$transfer->sourcetransport?kUnitContainer_Transport:kUnitContainer_Army);
		$units_here = cUnit::GroupUnits($units_here);
		$unittypes = array();
		foreach ($units_here as $o) if ($o->amount > 0) 
			$unittypes[$o->type] = (isset($unittypes[$o->type])?$unittypes[$o->type]:0)+$o->amount;
		foreach ($armylist as $k => $o) {
			$units = $transfer->transportarmytype ? cUnit::GetUnits($o->id,kUnitContainer_Transport) : cUnit::GetUnits($o->id);
			$armylist[$k]->units = $units; // use "units" even for transports, makes it much simpler
			foreach ($units as $u) if (!isset($unittypes[$u->type])) $unittypes[$u->type] = 0;
		}
		//vardump2($unittypes);
		$unittypes = cTransfer::FilterUnitArr($transfer,$unittypes);
		//echo cTransfer::GetArmyTransferName($transfer->id)."<br>";
		
		if (count($unittypes) == 0) return;
		
		$idlewait = ceil($transfer->idlemod-$sourcearmy->idle/60);
		
		if ($transfer->targetarmytype == kArmyType_Fleet && $sourcebuilding)
				$pos = cArmy::FindPierExit($x,$y,$sourceuserid,$units_here);
		else	$pos = cArmy::FindExit($x,$y,$sourceuserid,$units_here);
		$new_army_no_space = !$pos;
		
		ImgBorderStart("p2","jpg","#f2e7d5","",32,33);
		if ($idlewait > 0) {
			?> <?=$transfer->name?> erst in <?=$idlewait?> Minuten möglich <?php
		} else {
			?>
			<FORM METHOD=POST ACTION="<?=Query("?sid=?&x=?&y=?")?>">
			<INPUT TYPE="hidden" NAME="do" VALUE="armytransfer">
			<INPUT TYPE="hidden" NAME="transfer" VALUE="<?=$transfer->id?>">
			<INPUT TYPE="hidden" NAME="sourcebuilding" VALUE="<?=$sourcebuilding->id?>">
			<INPUT TYPE="hidden" NAME="sourcearmy" VALUE="<?=$sourcearmy->id?>">
			<b><?=$transfer->name?></b>
			<table border=1 cellspacing=0>
			<tr>
				<th>armee</th>
				<th>pos</th>
				<?php foreach($unittypes as $typeid => $here) {?>
					<th><img border="1" style="background-color:<?=$bgcolor?>" src="<?=g($gUnitType[$typeid]->gfx)?>"></th>
				<?php }?>
			</tr>
			
			<?php /* #### list armies #### */ ?>
			<?php foreach($armylist as $targetarmy) { ?>
				<?php 
				$error = cTransfer::CheckArmyTransfer($transfer,$sourcearmy,$sourcebuilding,$targetarmy,$gUser);
				?>
				<tr>
				<td><INPUT TYPE="text" NAME="armyname[<?=$targetarmy->id?>]" VALUE="<?=$targetarmy->name?>" style="width:120px"></td>
				<td align=right><?=opos2txt($targetarmy)?></td>
				<?php foreach ($unittypes as $typeid => $here) {?>
					<td align=right>
						<?php $amount = cUnit::GetUnitsSum(cUnit::FilterUnitsType($targetarmy->units,$typeid)); ?>
						<?php if (!$error) {?>
							<INPUT TYPE="text" NAME="armyunits[<?=$targetarmy->id?>][<?=$typeid?>]" VALUE="<?=floor($amount)?>" style="width:40px">
						<?php } else {?>
							<?=ktrenner(floor($amount))?>
						<?php }?>
					</td>
				<?php }?>
				<?php if ($error) {?>
					<td align=right nowrap><?=$error?></td>
				<?php } // endif?>
				</tr>
			<?php }?>
		
			<?php /* #### create new army #### */ ?>
			<?php if ($can_create_new_army) {?>
				<tr>
				<?php $error = cTransfer::CheckArmyTransfer($transfer,$sourcearmy,$sourcebuilding,false,$gUser);?>
				<?php if ($error) {?>
					<td align=right colspan=<?=count($unittypes)+2?>>
						neue <?=$gArmyType[$transfer->targetarmytype]->name?> : <?=$error?>
					</td>
				<?php } else { // ?>
					<th><INPUT TYPE="text" NAME="armyname[0]" VALUE="neue <?=$gArmyType[$transfer->targetarmytype]->name?>" style="width:120px"></th>
					<td align=right></td>
					<?php foreach ($unittypes as $typeid => $here) {?>
						<td align=right><INPUT TYPE="text" NAME="armyunits[0][<?=$typeid?>]" VALUE="0" style="width:40px"></td>
					<?php }?>
				<?php }?>
				</tr>
			<?php }?>
			
			<?php /* #### units in source #### */ ?>
			<tr>
				<th colspan=2>hier</th>
				<?php foreach ($unittypes as $typeid => $here) {?>
					<td align=right><?=ktrenner(floor($here))?></td>
				<?php }?>
			</tr>
			
			</table>
			<INPUT TYPE="submit" VALUE="transfer">
			</FORM>
			<?php
		}
		ImgBorderEnd("p2","jpg","#f2e7d5",32,33);
	}
	
	
	// check if building-army or army-army transfer is allowed, called from info/info.php
	// see below : ArmyTransfer()
	function TryArmyTransfer ($transfer,$sourcebuilding,$sourcearmy,$names,$unitarrs,$user) {
		global $gArmyTransfer;
		if ($sourcebuilding && !is_object($sourcebuilding)) $sourcebuilding = sqlgetobject("SELECT * FROM `building` WHERE `id` = ".intval($sourcebuilding));
		if ($sourcearmy && !is_object($sourcearmy)) $sourcearmy = sqlgetobject("SELECT * FROM `army` WHERE `id` = ".intval($sourcearmy));
		if (!is_object($user)) $user = sqlgetobject("SELECT * FROM `user` WHERE `id` = ".intval($user));
		if (!$user) return false;
		$transfer = $gArmyTransfer[intval($transfer)];
		if (!cTransfer::ArmyTransferVisible($transfer,$sourcebuilding,$sourcearmy,$user)) return false;
		if ($sourcearmy && ceil($transfer->idlemod-$sourcearmy->idle/60) > 0) return false;
		
		
		// name armies
		foreach ($names as $id => $newname)
			if ($id) sql("UPDATE `army` SET `name` = '".addslashes(cArmy::escapearmyname($newname))."' WHERE `id` = ".intval($id));
			
		// process armies
		foreach ($unitarrs as $id => $desiredunits) {
			$targetarmy = $id ? sqlgetobject("SELECT * FROM `army` WHERE `id` = ".intval($id)) : false;
			$error = cTransfer::CheckArmyTransfer($transfer,$sourcearmy,$sourcebuilding,$targetarmy,$user);
			if ($error) { echo $error."<br>"; continue; }
			$myidlewait = ceil($transfer->idlemod-$targetarmy->idle/60);
			$desiredunits = cTransfer::FilterUnitArr($transfer,$desiredunits);
			// todo : idlemod transportertype
			cTransfer::ArmyTransfer($transfer,$sourcebuilding,$sourcearmy,$targetarmy,$desiredunits,$names[$id]);
		}
		
		// always refresh map&navi frames : names change, max-unit-type may change -> different army picture
		?>
		<script language="javascript">
			parent.map.location.href = parent.map.location.href;
			parent.navi.location.href = parent.navi.location.href;
		</script>
		<?php
		return true;
	}
	
	// returns false if possible, or a readable error message if not
	// parameters must be objects
	function CheckArmyTransfer ($transfer,$sourcearmy,$sourcebuilding,$targetarmy,$user) {
		if ($targetarmy && !$targetarmy->counttolimit) return "Geschenkarmee";
		if ($sourcearmy && !$sourcearmy->counttolimit) return "Geschenkarmee";
		$sourceuserid = $sourcearmy ? $sourcearmy->user : $sourcebuilding->user;
		if ($sourcearmy && $sourcearmy->id == $targetarmy->id) return "Quelle=Ziel";
		if (!$targetarmy && !cArmy::CanCreateNewArmy($sourceuserid,$transfer->targetarmytype)) return "ArmeeLimit";
		if (!$targetarmy && $transfer->transportarmytype) return "Gründung durch Besatzung allein nicht möglich"; 
		if ($targetarmy && !cArmy::CanControllArmy($targetarmy,$user)) return "nicht steuerbar"; 
		if ($targetarmy && $targetarmy->type != $transfer->targetarmytype) return "typ passt nicht"; 
		if ($targetarmy) {
			$sourceobj = $sourcearmy ? $sourcearmy : $sourcebuilding;
			if ($targetarmy->type == kArmyType_Fleet && $sourcebuilding)
					$mynear = cArmy::ArmyAtPier($targetarmy,$sourceobj->x,$sourceobj->y,$sourceuserid);
			else	$mynear = cArmy::ArmyAtDiag($targetarmy,$sourceobj->x,$sourceobj->y);
			if (!$mynear) return "nicht hier";
		}
		if ($targetarmy) {
			$myidlewait = ceil($transfer->idlemod-$targetarmy->idle/60);
			if ($myidlewait > 0) return "erst in $myidlewait Minuten";
		}
		if ($targetarmy) {
			if (sqlgetone("SELECT 1 FROM `fight` WHERE `attacker` = ".$targetarmy->id)) return "kämpft gerade";
			if (sqlgetone("SELECT 1 FROM `fight` WHERE `defender` = ".$targetarmy->id)) return "kämpft gerade";
			if (sqlgetone("SELECT 1 FROM `pillage` WHERE `army` = ".$targetarmy->id)) return "plündert gerade";
			if (sqlgetone("SELECT 1 FROM `siege` WHERE `army` = ".$targetarmy->id)) return "belagert gerade";
		}
		if ($sourcearmy) {
			if (sqlgetone("SELECT 1 FROM `fight` WHERE `attacker` = ".$sourcearmy->id)) return "kämpft gerade";
			if (sqlgetone("SELECT 1 FROM `fight` WHERE `defender` = ".$sourcearmy->id)) return "kämpft gerade";
			if (sqlgetone("SELECT 1 FROM `pillage` WHERE `army` = ".$sourcearmy->id)) return "plündert gerade";
			if (sqlgetone("SELECT 1 FROM `siege` WHERE `army` = ".$sourcearmy->id)) return "belagert gerade";
		}
		return false;
	}
	

	
	// building-army or army-army transfer
	// $sourcebuilding XOR $sourcearmy
	// $transfer,$sourcebuilding,$sourcearmy must be objects
	// raceconditions minimized by using "`amount` = `amount` + $add WHERE `amount` + $add > 0" in cUnit::AddUnits()
	function ArmyTransfer ($transfer,$sourcebuilding,$sourcearmy,$targetarmy,$desiredunits,$newname) {
		// TODO : LOCK TABLES...
		global $gUnitType,$gArmyType;
		if (!$targetarmy && array_sum($desiredunits) <= 0) return true; // do not create empty army
		$debug = false;
		
		// container/id info
		$sourcecontainer = $sourcearmy ? ( $transfer->sourcetransport ? kUnitContainer_Transport : kUnitContainer_Army ) : kUnitContainer_Building;
		$targetcontainer = $transfer->transportarmytype ? kUnitContainer_Transport : kUnitContainer_Army;
		$sourceid = $sourcearmy ? $sourcearmy->id : $sourcebuilding->id ;
		$targetid = $targetarmy ? $targetarmy->id : 0;
		
		// get available troups and load/weight
		$targetunits = $targetid ? cUnit::GetUnits($targetid,$targetcontainer) : array();
		$sourceunits = cUnit::GetUnits($sourceid,$sourcecontainer);
		if ($sourcearmy)
				$max_weight_left_source = $transfer->sourcetransport ? cUnit::GetUnitsSum(cUnit::GetUnits($sourceid),"last") : cUnit::GetMaxArmyWeight($sourcearmy->type);
		else	$max_weight_left_source = cUnit::GetMaxBuildingWeight($sourcebuilding->type);
		$max_weight_left_target = $transfer->transportarmytype ? cUnit::GetUnitsSum(cUnit::GetUnits($targetid),"last") : cUnit::GetMaxArmyWeight($transfer->targetarmytype);
		
		// calculate troops to transfer from building to army, negative for reverse direction
		$mytrans = array();
		$empty_target = true;
		$empty_source = true;
		$no_troops_changed = true;
		
		 // two rounds for better weight limit handling, which limits added units, not total units
		for ($i=0;$i<2;++$i) {
			foreach ($desiredunits as $typeid => $desired_amount) {
				$type = $gUnitType[$typeid];
				$old_source = cUnit::GetUnitsSum(cUnit::FilterUnitsType($sourceunits,$typeid));
				$old_target = cUnit::GetUnitsSum(cUnit::FilterUnitsType($targetunits,$typeid));
				$new_target = max(0,min($old_source+$old_target,$desired_amount));
				$target_add = $new_target - $old_target;
				$new_source = $old_source - $target_add;
				
				if ($i == 0) {
					// weight-limit
					$max_weight_left_target -= min($old_target,$new_target) * $type->weight;
					if ($max_weight_left_source >= 0) // -1 means no limit
						$max_weight_left_source = max(0,$max_weight_left_source - min($old_source,$new_source) * $type->weight);
				} else {
					// weight-limit, second round (weight += added_weight)
					if ($type->weight > 0) {
						if ($target_add > 0) {
							// weight-limit target
							$target_add = min($target_add,max(0,floor($max_weight_left_target/$type->weight)));
							$max_weight_left_target -= $target_add * $type->weight;
						} else if ($target_add < 0) {
							// weight-limit source
							// note : $source_added = -$target_add
							if ($max_weight_left_source >= 0) { // -1 means no limit
								$target_add = -min(-$target_add,max(0,floor($max_weight_left_source/$type->weight)));
								$max_weight_left_source -= (-$target_add) * $type->weight;
							}
						}
					}
					if ($target_add > 0) $target_add = floor($target_add);
					//$target_add = ($target_add > 0) ? floor($target_add) : ceil($target_add);
					$new_target = $old_target + $target_add;
					$new_source = $old_source - $target_add;
					
					if ($target_add != 0) $no_troops_changed = false;
					if ($new_target >= 1) $empty_target = false;
					if ($new_source >= 1) $empty_source = false;
					$mytrans[$typeid] = $target_add;
					
					if ($debug && $target_add != 0) echo "$type->name [$type->id] : desired=$desired_amount total=".($old_source+$old_target)." t_add=$target_add t_new=$new_target s_new=$new_source<br>";
				}
			}
		}
		
		// renaming
		if ($targetid)
			sql("UPDATE `army` SET `name` = '".addslashes(cArmy::escapearmyname($newname))."' WHERE `id` = ".intval($targetid));
		
		// no change made to troop arrangement
		if (!$targetid && $empty_target) return false; // do not create empty army
		
		// execute transfer
		foreach ($mytrans as $typeid=>$target_add) if ($target_add != 0) {
			$failed = false;
			if ($debug) echo "transfer : $target_add ".$gUnitType[$typeid]->name."<br>";
			
			if ($target_add > 0) {
				if ($debug) echo "from source to target<br>";
				// from source to target
				foreach ($sourceunits as $o) if ($o->type == $typeid && !$failed) {
					$totarget = min($target_add,$o->amount);
					if (!cUnit::AddUnits($sourceid,$o->type,-$totarget,$sourcecontainer,$o->user,$o->spell)) {$failed = true;break;}
					if ($targetid) {
						cUnit::AddUnits($targetid,$o->type,+$totarget,$targetcontainer,$o->user,$o->spell);
					} else {
						// create army
						$sourceobj = $sourcearmy ? $sourcearmy : $sourcebuilding;
						$spawnunits = arr2obj(array("amount"=>$totarget,"type"=>$o->type,"user"=>$o->user,"spell"=>$o->spell));
						$newarmyflags = $sourcearmy ? $sourcearmy->flags : 0;
						$targetarmy = cArmy::SpawnArmy($sourceobj->x,$sourceobj->y,array(0=>$spawnunits),$newname,$transfer->targetarmytype,$sourceobj->user,0,0,false,$newarmyflags);
						if (!$targetarmy) {
							// create failed, return units to source
							cUnit::AddUnits($sourceid,$o->type,$totarget,$sourcecontainer,$o->user,$o->spell);
							echo $gArmyType[$transfer->targetarmytype]->name." konnte nicht gegründet werden, kein Ausgang gefunden !";
							return false;
						}
						echo $gArmyType[$transfer->targetarmytype]->name." '".addslashes(cArmy::escapearmyname($newname))."' gegründet<br>";
						$targetid = $targetarmy->id;
					}
					$target_add -= $totarget;
					if ($target_add <= 0) break; // done
				}
			} else {
				if ($debug) echo "from target to source<br>";
				// from target to source, $target_add < 0
				foreach ($targetunits as $o) if ($o->type == $typeid && !$failed) {
					$tosource = min(-$target_add,$o->amount);
					if (!cUnit::AddUnits($targetid,$o->type,-$tosource,$targetcontainer,$o->user,$o->spell)) 
						{$failed = true; if ($debug) echo "-$target_add,$o->amount .. cUnit::AddUnits($targetid,$o->type,-$tosource,$targetcontainer,$o->user,$o->spell) failed<br>"; break;}
						 cUnit::AddUnits($sourceid,$o->type,+$tosource,$sourcecontainer,$o->user,$o->spell);
					$target_add += $tosource;
					if ($target_add >= 0) break; // done
				}
			}
			if ($failed) {
				echo "Truppentransfer fehlgeschlagen!<br>";
				return false;
			}
		}
		
		// target is disbanded or reduced, evacuate transported units to source-TRANSPORT or source-BUILDING
		if (!$transfer->transportarmytype) {
			$evacuated_units = cUnit::GetUnits($targetid,kUnitContainer_Transport);
			$max_weight = cUnit::GetUnitsSum(cUnit::GetUnits($targetid),"last");
			$cur_weight = cUnit::GetUnitsSum($evacuated_units,"weight");
			$overweight = $cur_weight - $max_weight;
			
			if ($empty_target || $overweight > 0) foreach ($evacuated_units as $o) {
				$evac_amount = $empty_target ? $o->amount : ($gUnitType[$o->type]->weight?min($o->amount,$overweight/$gUnitType[$o->type]->weight):0);
				if ($evac_amount <= 0) continue;
				
				if ($debug) echo "evacuate : $evac_amount ".$gUnitType[$o->type]->name."<br>";
				if (!cUnit::AddUnits($targetid,$o->type,-$evac_amount,kUnitContainer_Transport,$o->user,$o->spell)) {
					echo "Evakuierung der Transportierten fehlgeschlagen!<br>";
					return false;
				}
				if ($sourcearmy)
						cUnit::AddUnits($sourceid,$o->type,+$evac_amount,kUnitContainer_Transport,$o->user,$o->spell);
				else	cUnit::AddUnits($sourceid,$o->type,+$evac_amount,kUnitContainer_Building,$o->user,$o->spell);
			}
		}
		
		// source ARMY is disbanded or reduced, evacuate transported units to target-TRANSPORT
		if (!$transfer->sourcetransport && $sourcearmy) {
			$evacuated_units = cUnit::GetUnits($sourceid,kUnitContainer_Transport);
			$max_weight = cUnit::GetUnitsSum(cUnit::GetUnits($sourceid),"last");
			$cur_weight = cUnit::GetUnitsSum($evacuated_units,"weight");
			$overweight = $cur_weight - $max_weight;
			
			if ($empty_source || $overweight > 0) foreach ($evacuated_units as $o) {
				$evac_amount = $empty_source ? $o->amount : ($gUnitType[$o->type]->weight?min($o->amount,$overweight/$gUnitType[$o->type]->weight):0);
				if ($evac_amount <= 0) continue;
				
				if ($debug) echo "evacuate : $evac_amount ".$gUnitType[$o->type]->name."<br>";
				if (!cUnit::AddUnits($sourceid,$o->type,-$evac_amount,kUnitContainer_Transport,$o->user,$o->spell)) {
					echo "Evakuierung der Transportierten fehlgeschlagen!<br>";
					return false;
				}
				cUnit::AddUnits($targetid,$o->type,+$evac_amount,kUnitContainer_Transport,$o->user,$o->spell);
			}
		}
		sql("DELETE FROM `unit` WHERE `amount` < 1.0");
			
		// drop excess cargo, delete empty armies, transfer frags
		if ($sourcearmy) {
			if (!$transfer->sourcetransport && $empty_source && cUnit::GetUnitsSum(cUnit::GetUnits($sourcearmy->id)) < 1) {
				cArmy::DropExcessCargo($sourcearmy,$targetarmy,0);
				cArmy::DeleteArmy($sourcearmy,true,"aufgelöst");
				echo $gArmyType[$sourcearmy->type]->name." '".addslashes($sourcearmy->name)."' aufgelöst<br>";
				sql("UPDATE `army` SET `frags` = `frags` + ".intval($sourcearmy->frags)." WHERE `id` = ".intval($targetarmy->id));
			} else cArmy::DropExcessCargo($sourcearmy,$targetarmy);
		}
		if (!$transfer->transportarmytype && $empty_target && cUnit::GetUnitsSum(cUnit::GetUnits($targetid)) < 1) {
			cArmy::DropExcessCargo($targetarmy,$sourcearmy,0);
			cArmy::DeleteArmy($targetid,true,"aufgelöst");
			echo $gArmyType[$transfer->targetarmytype]->name." '".addslashes($targetarmy->name)."' aufgelöst<br>";
			sql("UPDATE `army` SET `frags` = `frags` + ".intval($targetarmy->frags)." WHERE `id` = ".intval($sourcearmy->id));
		} else cArmy::DropExcessCargo($targetarmy,$sourcearmy);
		
		return true;
	}
}
?>