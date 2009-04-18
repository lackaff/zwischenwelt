<?php
// hooks for all quest-triggers
//require_once("lib.quest.php");
require_once("lib.main.php");
require_once("lib.item.php");

/*
todo : item add param ?? , army add quest ??
*/

// monster jagt : 20 pogopuschel in region x töten
// rettung funktioniert wie transport



$gQuestTypeNames = array(0=>"Monster-Jagt",1=>"Rettung",2=>"Transport",3=>"Sammeln",4=>"Stehlen");
$gQuestTypeAlias = array(2=>1,3=>1,4=>1); // 1,2,3,4 funktionieren identisch

$gQuestItems = false; // singleton
function &GetQuestItems() { 
	global $gQuestItems;
	if (empty($gQuestItems))
		$gQuestItems = sqlgetgrouptable("SELECT * FROM `item` WHERE `quest` > 0","army","id");
	return $gQuestItems; 
}

$gRunningQuests = false; // singleton
function &GetRunningQuests() { 
	global $gRunningQuests;
	if (empty($gRunningQuests)) {
		$gRunningQuests = sqlgettable("SELECT * FROM `quest` WHERE `running` = 1","id");
		array_walk($gRunningQuests,"QuestSplit");
	}
	return $gRunningQuests; 
}

function realquesttype ($type) { 
	global $gQuestTypeAlias;
	return isset($gQuestTypeAlias[$type])?$gQuestTypeAlias[$type]:$type;
}

function QuestSplit (&$quest,$key) {
	$quest->params = explode("#",$quest->params);
	$quest->realtype = realquesttype($quest->type);
	switch ($quest->realtype) {
		case 1: // Rettung : itemtypes#startpos#endpos#itemsetmode
			$quest->params[0] = explode("|",$quest->params[0]);
			$quest->params[1] = explode("|",$quest->params[1]);
			$quest->params[2] = explode("|",$quest->params[2]);
			$quest->params[3] = intval($quest->params[3]);
			array_walk($quest->params[1],"walkintsplit",",");
			array_walk($quest->params[2],"walkintsplit",",");
		break;
	}
}




// questing functions

function Quest_Start ($quest) {
	echo "Quest_Start(".quest2txt($quest).")<br>";
	switch ($quest->realtype) {
		case 1: // Rettung : itemtypes#startpos#endpos#itemsetmode
			if ($quest->params[3] == 1) {
				// non random itemtypes : all items spawned simultaneously, and itemtype is for a specific startpos, all items must be delivered together
				for ($i=0;$i<count($quest->params[0]);$i++) {
					$itemtype = $quest->params[0][$i];
					list($x,$y) = $quest->params[1][$i];
					cItem::SpawnItem($x,$y,$itemtype,1.0,$quest->id);
				}
			} else {
				$itemtype = $quest->params[0][array_rand($quest->params[0],1)];
				shuffle($quest->params[1]);
				echo "quest typ 1 gestartet : ".$quest->name." , versuche itemtyp:$itemtype zu setzen<br>";
				foreach ($quest->params[1] as $pos) {
					list($x,$y) = $pos;
					if (sqlgetone("SELECT 1 FROM `item` WHERE `army` = 0 AND `x` = $x AND `y` = $y LIMIT 1")) continue;
					cItem::SpawnItem($x,$y,$itemtype,1.0,$quest->id);
					break;
				}
			}
		break;
	}
	
	sql("INSERT INTO `triggerlog` SET ".arr2sql(array("time"=>time(),"trigger"=>"Quest_Start","id1"=>$quest->id,"x"=>$quest->x,"y"=>$quest->y,
		"what"=>quest2txt($quest))));
}

function Quest_Finish ($quest,$army,$item=false) {
	
	// finished by trigger, e.g. player fullfilled the mission
	// prepare repetition, global news, etc
	echo "Quest_Finish($quest->id,$army->id)<br>";
	
	// give reward
	$rewardtimes = 1;
	if ($item && $item->amount > 1)
		$rewardtimes = $item->amount;
	sql("UPDATE `user` SET 
		`lumber` = `lumber`+".$quest->lumber*$rewardtimes." , 
		`stone` = `stone`+".$quest->stone*$rewardtimes." , 
		`food` = `food`+".$quest->food*$rewardtimes." , 
		`metal` = `metal`+".$quest->metal*$rewardtimes." , 
		`runes` = `runes`+".$quest->runes*$rewardtimes." WHERE `id` = ".$army->user);
	if ($quest->rewarditemtype > 0) {
		cItem::SpawnArmyItem($army,$quest->rewarditemtype,$quest->rewarditemamount*$rewardtimes);
	}
	// TODO : message to user
	
	Quest_Cleanup($quest);
	
	sql("INSERT INTO `triggerlog` SET ".arr2sql(array("time"=>time(),"trigger"=>"Quest_Finish","id1"=>$quest->id,"id2"=>$army->id,"x"=>$army->x,"y"=>$army->y,
		"what"=>quest2txt($quest)."#".army2txt($army)."#".$rewardtimes)));
		
	if (intval($quest->flags) & kQuestFlag_RepeatOnFinish)
		Quest_Start($quest);
}


function Quest_Timeout ($quest) {
	echo "Quest_Timeout(".quest2txt($quest).")<br>";
	sql("INSERT INTO `triggerlog` SET ".arr2sql(array("time"=>time(),"trigger"=>"Quest_Timeout","id1"=>$quest->id,"x"=>$quest->x,"y"=>$quest->y,
		"what"=>quest2txt($quest))));
		
	Quest_Cleanup($quest);
}


function Quest_Cleanup ($quest) { // called from both Quest_Finish and Quest_Timeout
	if ($quest->id == 0) return;
	
	// restart or remove
	if ($quest->repeat > 0) 
			sql("UPDATE `quest` SET	`running` = 0 , `start` = `start`+`repeat` WHERE `id` = ".$quest->id." LIMIT 1");
	else	sql("UPDATE `quest` SET	`running` = 0 WHERE `id` = ".$quest->id." LIMIT 1");
	
	// flags
	if ((intval($quest->flags) & kQuestFlag_Delete_QuestItems_On_Finish) != 0)
		sql("DELETE FROM `item` WHERE `quest` = ".$quest->id);
	if ((intval($quest->flags) & kQuestFlag_Delete_QuestArmy_On_Finish) != 0)
		sql("DELETE FROM `army` WHERE `quest` = ".$quest->id);
}



// ***** ***** ***** ***** ***** triggers called from cron



function QuestTrigger_CronStep() { // called after army_move and hellhole monstermove, so the army cache is usable
	global $time;
	echo "QuestTrigger_CronStep $time<br>";
	
	// end quests via timeout
	$ending = sqlgettable("SELECT * FROM `quest` WHERE `running` = 1 AND `dur` > 0 AND `start`+`dur` <= ".$time);
	if (count($ending) > 0) {
		array_walk($ending,"QuestSplit");
		foreach ($ending as $o) Quest_Timeout($o);
		//sql("UPDATE `quest` SET	`running` = 0 , `start` = `start`+`repeat` WHERE `repeat` > 0 AND `dur` > 0 AND `start`+`dur` <= ".$time);
		//sql("UPDATE `quest` SET	`running` = 0 WHERE `dur` > 0 AND `start`+`dur` <= ".$time);
	}
	
	// start quests
	$starting = sqlgettable("SELECT * FROM `quest` WHERE `running` = 0 AND (`start`<=".$time." OR `flags` & ".kQuestFlag_Permanent.")");
	if (count($starting) > 0) {
		array_walk($starting,"QuestSplit");
		foreach ($starting as $o) Quest_Start($o);
		sql("UPDATE `quest` SET `running` = 1 WHERE `start`<=".$time." OR `flags` & ".kQuestFlag_Permanent);
	}
}

function QuestTrigger_ArmyMove(&$army,$x,$y) { // called very often, non-monster army , also called from QuestTrigger_TeleportArmy
	$gQuestItems =& GetQuestItems();
	
	// kArmyFlag_AlwaysCollectItems main function
	//echo "QuestTrigger_ArmyMove<br>";
	if (intval($army->flags) & kArmyFlag_AlwaysCollectItems) {
		//cItem::pickupall($army);
		$picked_items = sqlgettable("SELECT * FROM `item` WHERE `army` = 0 AND `building` = 0 AND `x` = ".$army->x." AND `y` = ".$army->y);
		//echo "QuestTrigger_ArmyMove : kArmyFlag_AlwaysCollectItems : ".count($picked_items)." items<br>";
		foreach ($picked_items as $item) {
			if (cItem::pickupItem($item,$army) && $item->quest > 0) {
				$item->army = $army->id;
				if (!isset($gQuestItems[$army->id])) 
					$gQuestItems[$army->id] = array();
				$gQuestItems[$army->id][] = $item;
			}
			$army = sqlgetobject("SELECT * FROM `army` WHERE `id`=".$army->id);
		}
	}
	
	if (!isset($army->id) || !isset($gQuestItems[$army->id])) return;
	$items =& $gQuestItems[$army->id];
	if (empty($items)) return;
	// execute only when this army has quest items
	
	$gRunningQuests =& GetRunningQuests();
	foreach ($items as $item) {
		$quest =& $gRunningQuests[$item->quest];
		if (empty($quest)) continue;
		switch ($quest->realtype) {
			case 1: // Rettung : itemtypes#startpos#endpos#itemsetmode
				foreach ($quest->params[2] as $pos) {
					//if (count($pos) < 2) vardump2($quest);
					if (count($pos) < 2) continue;
					list($qx,$qy) = $pos;
					if (intval($qx) != $x || intval($qy) != $y) continue;
					
					if ($quest->params[3] == 1) {
						// must have all items
						$itypes_in_army = AF($items,"type");
						$haveall = true;
						foreach ($quest->params[0] as $itype) {
							if (in_array($itype,$itypes_in_army)) continue;
							$haveall = false;
							break;
						}
						if ($haveall) {
							// wenn nicht alle quest items automatisch geloescht werden, dann zumindest die abgegebenen
							if (!(intval($quest->flags) & kQuestFlag_Delete_QuestItems_On_Finish))
								sql("DELETE FROM `item` WHERE `army` = ".$army->id." AND `quest` = ".$quest->id);
							Quest_Finish($quest,$army);
						} else {
							// TODO : message to user, that he must bring all items at once
						}
						return;
					} else {
						sql("DELETE FROM `item` WHERE `id` = ".$item->id);
						Quest_Finish($quest,$army,$item);
					}
					break;
				}
			break;
		}
	}
} 



// ***** ***** ***** ***** ***** triggers called outside cron (spells,useractions,etc)




function QuestTrigger_TeleportArmy($army,$building,$x,$y) { // calls QuestTrigger_ArmyMove
	if (!is_object($army)) $army = sqlgetobject("SELECT * FROM `army` WHERE `id` = ".intval($army));
	sql("DELETE FROM `waypoint` WHERE `army` = ".$army->id);
	QuestTrigger_ArmyMove($army,$x,$y);
} 

function QuestTrigger_EscapeArmy($army,$x,$y) { // calls QuestTrigger_ArmyMove
	if (!is_object($army)) $army = sqlgetobject("SELECT * FROM `army` WHERE `id` = ".intval($army));
	QuestTrigger_ArmyMove($army,$x,$y);
} 

?>