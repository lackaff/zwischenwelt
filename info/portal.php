<?php
require_once("../lib.main.php");
require_once("../lib.army.php");


$gPortalConCost = false;

$gClassName = "cInfoPortal";
class cInfoPortal extends cInfoBuilding {
	function mycommand () {
		foreach ($_REQUEST as $name=>$val) ${"f_".$name} = $val;
		global $gObject;
		global $gGlobal;
		global $gUser;
		global $gRes;
		global $gRes2ItemType;
		global $gPortalConCost;
		
		if ($gObject->type != kBuilding_Portal) return;
		switch ($f_do) {
			case "fetcharmies":
				if (!cBuilding::BuildingOpenForUser($gObject,$gUser->id)) break;
				$cost = cBuilding::getPortalFetchArmyCost($gObject);
				foreach ($f_sel as $id) {
					$army = sqlgetobject("SELECT * FROM `army` WHERE `id` = ".intval($id));
					if (!$army) continue;
					if (!cArmy::CanControllArmy($army,$gUser)) continue;
					if (!cArmy::CanFetchArmyToPortal($gObject,$army)) continue;
					$army->units = cUnit::GetUnits($army->id); 
					
					$target = $gObject;
					$exit = cArmy::FindExit($target->x,$target->y,$army->user,$army->units);
					
					if (!$exit) {
						echo "<h3><font color='red'>Ausgang Blockiert !</font></h3>";
					} else if (userPay($gUser->id,$cost[0],$cost[1],$cost[2],$cost[3],$cost[4]) && cItem::ArmyPayItem($army->id,kItem_Portalstein_Blau,1)) {
					
						// teleportation
						sql("UPDATE `army` SET `x`=".$exit[0].",`y`=".$exit[1]." WHERE `id`=".$army->id);
						QuestTrigger_TeleportArmy($army,$gObject,$exit[0],$exit[1]);
						echo "<b><font color='green'>teleportation nach ".pos2txt($exit[0],$exit[1])." geglückt !</font></b><br>";
						$gUser = sqlgetobject("SELECT * FROM `user` WHERE `id` = ".$gUser->id);
					} else echo "<h3><font color='red'>ZU TEUER !</font></h3>";
				}
			break;
			case "openconnection":
				global $f_showdest; unset($f_showdest);
				if (!cBuilding::BuildingOpenForUser($gObject,$gUser->id)) break;
				$possibleTargets = cBuilding::listAllPortalTargets($gObject,$gUser);
				foreach ($possibleTargets as $o) if(isset(${"f_con_".($o->id)})) {
					if (!cBuilding::BuildingOpenForUser($o,$gUser->id)) break;
					$concost = cBuilding::getPortalConCost($gObject,$o);
					$gPortalConCost = "Verbindungskosten ".cost2txt($concost,$gUser)."<br>";
					if (userPay($gUser->id,$concost[0],$concost[1],$concost[2],$concost[3],$concost[4])) {
						// close old connections
						$source_target = GetBParam($gObject->id,"target");
						if ($source_target) {
							ClearBParam($source_target,"transportcount");
							ClearBParam($source_target,"target");
						}
						$dest_target = GetBParam($o->id,"target");
						if ($dest_target) {
							ClearBParam($dest_target,"transportcount");
							ClearBParam($dest_target,"target");
						}
						// open new connection
						$transportcount = ceil($gObject->level/3+1);
						SetBParam($gObject->id,"target",$o->id);
						SetBParam($o->id,"target",$gObject->id);
						SetBParam($gObject->id,"transportcount",$transportcount);
						SetBParam($o->id,"transportcount",$transportcount);
						$gUser = sqlgetobject("SELECT * FROM `user` WHERE `id` = ".$gUser->id);
					} else $gPortalConCost = "<h3><font color='red'>ZU TEUER !</font></h3>";
					break;
				}
			break;
			case "transport":
				global $f_showdest; unset($f_showdest);
				if (!cBuilding::BuildingOpenForUser($gObject,$gUser->id)) break;
				
				$target = intval(GetBParam($gObject->id,"target"));
				$target = $target?sqlgetobject("SELECT * FROM `building` WHERE `id` = ".intval($target)):false;
				if (!$target) {
					echo "<h3><font color='red'>Keine Verbindung !</font></h3>";
					break;
				}
				
				if (!cBuilding::BuildingOpenForUser($target,$gUser->id)) {
					echo "<h3><font color='red'>Das Zielportal ist verschlossen !</font></h3>";
					break;
				}
				
				$transportcount_my = intval(GetBParam($gObject->id,"transportcount"));
				$transportcount_target = intval(GetBParam($target->id,"transportcount"));
				if ($transportcount_my <= 0) {
					echo "<h3><font color='red'>Von dieser Seite kann nichts mehr verschickt werden !</font></h3>";
					break;
				}
				
				$armies = cArmy::getMyArmies();
				$tax = cBuilding::getPortalConTax($gObject,$target,$gUser->id);
				
				foreach ($armies as $army) if (cArmy::ArmyAtDiag($army,$gObject->x,$gObject->y)) if(isset(${"f_tarmy_".($army->id)})) {
					if (sqlgetone("SELECT 1 FROM `fight` WHERE `attacker` = ".$army->id." OR `defender` = ".$army->id)) continue; // no teleport during fight
					
					$army->units = cUnit::GetUnits($army->id); 
					$army->size = cUnit::GetUnitsSum($army->units);
					$taxrate = $army->size / (float)kPortalTaxUnitNum;
					$cost = array();
					for ($i=0;$i<count($gRes);$i++) $cost[$i] = $tax?ceil($taxrate*$tax[$i]):0;
					echo "Transportkosten ".cost2txt($cost,$gUser)."<br>";
					
					$exit = cArmy::FindExit($target->x,$target->y,$army->user,$army->units);
					
					if (!$exit) {
						echo "<h3><font color='red'>Ausgang Blockiert !</font></h3>";
					} else if (userPay($gUser->id,$cost[0],$cost[1],$cost[2],$cost[3],$cost[4])) {
					
						// tax an user oder weltbank zahlen.
						$outtax = cBuilding::BuildingTaxForUser($gObject,$gUser->id)?GetBParam($gObject->id,"tax"):false;
						$intax = cBuilding::BuildingTaxForUser($target,$gUser->id)?GetBParam($target->id,"tax"):false;
						if ($outtax) $outtax = explode(",",$outtax);
						if ($intax) $intax = explode(",",$intax);
						global $gResFields;
						for ($i=0;$i<count($gRes);$i++) {
							if ($outtax) {
								// steuer beim quell portal
								if ($gObject->user == 0)
										sql("UPDATE `guild` SET `".$gResFields[$i]."`=`".$gResFields[$i]."`+".$outtax[$i]." WHERE `id`= ".kGuild_Weltbank);
								else	sql("UPDATE `user`  SET `".$gResFields[$i]."`=`".$gResFields[$i]."`+".$outtax[$i]." WHERE `id`= ".$gObject->user);
							}
							if ($intax) {
								// steuer beim ziel portal
								if ($target->user == 0)
										sql("UPDATE `guild` SET `".$gResFields[$i]."`=`".$gResFields[$i]."`+".$intax[$i]." WHERE `id`= ".kGuild_Weltbank);
								else	sql("UPDATE `user`  SET `".$gResFields[$i]."`=`".$gResFields[$i]."`+".$intax[$i]." WHERE `id`= ".$target->user);
							}
						}
						
						// teleportation
						sql("UPDATE `army` SET `x`=".$exit[0].",`y`=".$exit[1]." WHERE `id`=".$army->id);
						QuestTrigger_TeleportArmy($army,$gObject,$exit[0],$exit[1]);
						echo "<b><font color='green'>teleportation nach ".pos2txt($exit[0],$exit[1])." geglückt !</font></b><br>";
						
						if ($transportcount_my-1 <= 0) {
							// verbindung beenden
							ClearBParam($gObject->id,"transportcount");
							ClearBParam($gObject->id,"target");
							ClearBParam($target->id,"transportcount");
							ClearBParam($target->id,"target");
							echo "Die Verbindung ist nach dem Transport zusammengebrochen<br>";
						} else {
							SetBParam($gObject->id,"transportcount",max(0,$transportcount_my-1));
							SetBParam($target->id,"transportcount",max(0,$transportcount_target-1));
						}
						$gUser = sqlgetobject("SELECT * FROM `user` WHERE `id` = ".$gUser->id);
					} else echo "<h3><font color='red'>ZU TEUER !</font></h3>";
					break;
				} // endforeach MyArmies
			break;
			default:
				if (!($gObject->user==$gUser->id)) break;
			break;
		}
	}
	
	
	function mygenerate_tabs() {
		if ($this->construction > 0) return;
		foreach ($_REQUEST as $name=>$val) ${"f_".$name} = $val;
		global $gUser;
		global $gObject;
		global $gRes;
		global $gGlobal;
		global $gPortalConCost;
		profile_page_start("portal.php");
		rob_ob_start();
		
		if ($gPortalConCost) echo $gPortalConCost;
		
		$transportcount = intval(GetBParam($gObject->id,"transportcount"));
		$target = GetBParam($gObject->id,"target");
		$target = $target?sqlgetobject("SELECT * FROM `building` WHERE `id` = ".intval($target)):false;
		?>
		
		<br>
		<br>
		<?php if ($target) {?>
			<?php
			$mark = sqlgetobject("SELECT * FROM `mapmark` WHERE `user` = ".$gUser->id." AND `x` = ".$target->x." AND `y` = ".$target->y." ORDER BY `name`");
			?>
			Das Portal ist verbunden mit <?=$mark?$mark->name:"dem"?> bei <?=opos2txt($target)?>  <?=$target->user?("von ".usermsglink($target->user)):"(öffentlich)"?><br>
			Es sind noch <?=$transportcount?> Transporte möglich<br>
			<br>
			
			<?php if (cBuilding::BuildingOpenForUser($gObject,$gUser->id) && cBuilding::BuildingOpenForUser($target,$gUser->id) && $transportcount > 0) {?>
				<?php $armies = cArmy::getMyArmies();?>
				<?php $count = 0; foreach ($armies as $army) if (cArmy::ArmyAtDiag($army,$gObject->x,$gObject->y)) ++$count;?>
				<?php if ($count > 0) {?>
					<?php $tax = cBuilding::getPortalConTax($gObject,$target,$gUser->id);?>
					Welche Armee soll transportiert werden?<br>
					<FORM method="post" action="<?=Query("?sid=?&x=?&y=?")?>">
					<INPUT TYPE="hidden" NAME="building" VALUE="portal">
					<INPUT TYPE="hidden" NAME="id" VALUE="<?=$gObject->id?>">
					<INPUT TYPE="hidden" NAME="do" VALUE="transport">
					<table>
						<tr>
							<th>Name</th>
							<th>Besitzer</th>
							<th>Größe</th>
							<?php $i=0; foreach($gRes as $n=>$f) {?>
							<th><img src="<?=g("res_$f.gif")?>"></th>
							<?php } // endforeach res?>
							<th></th>
						</tr>
						<?php foreach ($armies as $army) if (cArmy::ArmyAtDiag($army,$gObject->x,$gObject->y)) {?>
							<?php	
									if (sqlgetone("SELECT 1 FROM `fight` WHERE `attacker` = ".$army->id." OR `defender` = ".$army->id)) continue; // no teleport during fight
								
									$army->units = cUnit::GetUnits($army->id); 
									$army->size = floor(cUnit::GetUnitsSum($army->units));
									$taxrate = $army->size / (float)kPortalTaxUnitNum;
							?>
							<tr>
								<td><?=$army->name?></td>
								<td><?=$army->owner?></td>
								<td><?=$army->size?></td>
								<?php $i=0; foreach($gRes as $n=>$f) {?>
								<td align="right"><?=$tax?ceil($taxrate*$tax[$i++]):0?></td>
								<?php }?>
								<td><input type="submit" name="tarmy_<?=$army->id?>" value="Send"></td>
							</tr>
						<?php } // endforeach MyArmies?>
					</table>
					</FORM>
				<?php } // endif armies?>
			<?php } // endif can use?>
		<?php } else { // else notconnected?>
			Das Portal ist zur Zeit nicht in Betrieb<br>
		<?php } // endif connected?>
		
		<br><hr>
		<?php if (cBuilding::BuildingOpenForUser($gObject,$gUser->id)) {?>
			Kosten für das Herholen einer Armee : (<?=GetItemTypeLink(kItem_Portalstein_Blau,$gObject->x,$gObject->y)?>in der Armee) + <?=cost2txt(cBuilding::getPortalFetchArmyCost($gObject),$gUser)?><br>
			<?php 
				$armylist = cArmy::ListControllableArmies(); 
				$fetchlist = array();
				foreach ($armylist as $o) if (cArmy::CanFetchArmyToPortal($gObject,$o)) 
					if (!cArmy::ArmyAtDiag($o,$gObject->x,$gObject->y)) $fetchlist[] = $o;
			?>
			<?php if (count($fetchlist) > 0) {?>
				<FORM method="post" action="<?=Query("?sid=?&x=?&y=?")?>">
				<INPUT TYPE="hidden" NAME="building" VALUE="portal">
				<INPUT TYPE="hidden" NAME="id" VALUE="<?=$gObject->id?>">
				<INPUT TYPE="hidden" NAME="do" VALUE="fetcharmies">
				<table>
				<?php foreach ($fetchlist as $o) {?>
					<tr>
					<td><input type="checkbox" name="sel[]" value="<?=$o->id?>"></td>
					<td><?=$o->name?></td>
					<td><?=GetUserLink($o->user,false)?></td>
					<td align="center"><?=opos2txt($o)?></td>
					<td>hat im Moment</td>
					<td><?=GetItemTypeLink(kItem_Portalstein_Blau,$gObject->x,$gObject->y)?></td>
					<td><?=cItem::CountArmyItem($o,kItem_Portalstein_Blau)?></td>
					</tr>
				<?php } // endforeach?>
				</table>
				<input type="submit" name="fetcharmies" value="Herholen">
				</FORM>
			<?php }?>
			
		<br><hr>
			Kosten für eine neue Verbindung : <?=cost2txt(cBuilding::getPortalConCost($gObject),$gUser)?>
			<?php $possibleTargets = cBuilding::listAllPortalTargets($gObject,$gUser);?>
			<FORM method="post" action="<?=Query("?sid=?&x=?&y=?")?>">
			<INPUT TYPE="hidden" NAME="building" VALUE="portal">
			<INPUT TYPE="hidden" NAME="id" VALUE="<?=$gObject->id?>">
			<INPUT TYPE="hidden" NAME="do" VALUE="openconnection">
			<table>
			<tr>
				<th colspan=2>Ziel</th>
				<th>Entf.</th>
				<th>Besitzer</th>
				<th>Zoll pro <?=kPortalTaxUnitNum?> Mann</th>
			</tr>
			<?php foreach ($possibleTargets as $o) {?>
				<?php $mark = sqlgetobject("SELECT * FROM `mapmark` WHERE `user` = ".$gUser->id." AND `x` = ".$o->x." AND `y` = ".$o->y." ORDER BY `name`");?>
				<?php if (!isset($f_showdest) && !$mark) continue;?>
				<?php $tax = cBuilding::getPortalConTax($gObject,$o,$gUser->id);?>
				<tr>
					<td align="left"><?=$mark?$mark->name:""?></td>
					<td align="center"><?=pos2txt($o->x,$o->y)?></td>
					<td align="right"><?=ceil($o->dist)?></td>
					<td align="left">&nbsp;<?=$o->user?usermsglink($o->user):"öffentlich"?></td>
					<td align="left"><?=cost2txt($tax)?></td>
					<td><input type="submit" name="con_<?=$o->id?>" value="Walk the Abyss"></td>
				</tr>
			<?php } // endforeach?>
			</table>
			</FORM>
			<?php if (!isset($f_showdest)) { // ?>
				<a href="<?=Query("?sid=?&x=?&y=?&showdest=1")?>">(alle möglichen Ziel-Portale anzeigen)</a><br>
			<?php } // endif?>
		<?php } else {// endif canuse?>
			<font color="red">Portal-Zugang verweigert</font><br>
		<?php } // endif?>
		
		<?php profile_page_end();
		RegisterInfoTab("Reisen",rob_ob_end(),10);
	}
}