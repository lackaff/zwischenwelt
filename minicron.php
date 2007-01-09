<?php
//exit(0);
if (true) {
if (!isset($gMiniCronFromCron) || !$gMiniCronFromCron) exit(0);

require_once("cronlib.php");
require_once("lib.quest.php");
require_once("lib.map.php");
require_once("lib.technology.php");
require_once("lib.spells.php");
require_once("lib.army.php"); // sql
require_once("lib.weather.php");
require_once("lib.fight.php");

$mlock = FALSE;
if(!isset ($lock) && $mlock)
	if(file_exists("/tmp/zw-cron.lock"))
		exit(1);
	else
		shell_exec("echo lock > /tmp/zw-cron.lock");


$time = time();
$lastminitick = $gGlobal["lastminitick"];
if(empty($lastminitick))$lastminitick = $gGlobal["lasttick"];
SetGlobal("lastminitick",$time);

$dtime = min($time - $gGlobal["lasttick"],$time - $lastminitick);
if($dtime < 0)$dtime = 0;

echo "dtime = $dtime<br><br>";

if (1) {
// TODO : tables lock needed ? hunger/starvation-damage, shootings..

//sql("DELETE FROM `unit` WHERE `amount` < 1.0"); 
sql("UPDATE `army` SET `idle`=`idle`+$dtime"); // call before including lib.armythink.php
require_once("lib.armythink.php"); // warning ! generates big globals, called here, so idletime add is in $gAllArmys

if (kProfileArmyLoop) LoopProfiler_flush();


$c = 0;
$r = sql("SELECT * FROM `army`");
if ($r !== true && $r !== false) { while ($army = mysql_fetch_object($r)) { echo "army ".(++$c)."<br>";
	//if ($c++ > 100) break;
	if (kProfileArmyLoop) LoopProfiler("armyloop:init");
	if (!isset($gAllArmyUnits[$army->id])) warning("Army $army->id ($army->x,$army->y) has no units ??<br>");
	$army->units = isset($gAllArmyUnits[$army->id])?$gAllArmyUnits[$army->id]:array(); // constructed in lib.armythink.php
	$army->size = cUnit::GetUnitsSum($army->units);
	$army->useditemobj = $army->useditem ? sqlgetobject("SELECT * FROM `item` WHERE `id` = ".$army->useditem) : false;
	//if ($army->size < 1.0) { cArmy::DeleteArmy($army->id); continue; }
	if ($army->type == kArmyType_Fleet) // todo : $army->transport = $gAllArmyTransport[$army->id];
		$army->transport = cUnit::GetUnits($army->id,kUnitContainer_Transport);
	
	// eating : monsters and siege-armies do not eat
	if ($army->user != 0 && $army->type != kArmyType_Siege) {
		if (kProfileArmyLoop) LoopProfiler("armyloop:eat");
		if ($army->type == kArmyType_Fleet)
				$verbrauch = $dtime * cUnit::GetUnitsEatSum($army->transport) / 3600.0;
		else	$verbrauch = $dtime * cUnit::GetUnitsEatSum($army->units) / 3600.0;
		
		if ($army->useditemobj && $army->useditemobj->type == kItem_Spam) $verbrauch *= 0.5;
		
		if ($verbrauch > 0) {
			$hungerschaden = max(0,$verbrauch - $gAllUsers[$army->user]->food);
			sql("UPDATE `user` SET `food`=GREATEST(0,`food`-$verbrauch) WHERE `id`=".$army->user);
			if ($hungerschaden > 0) {
				if (kProfileArmyLoop) LoopProfiler("armyloop:hungerschaden");
				if($army->type == kArmyType_Fleet) {
					$army->transport = cUnit::GetUnitsAfterDamage($army->transport,$hungerschaden,$army->user);
					cUnit::SetUnits($army->transport,$army->id,kUnitContainer_Transport);
				} else {
					$army->units = cUnit::GetUnitsAfterDamage($army->units,$hungerschaden,$army->user);
					cUnit::SetUnits($army->units,$army->id);
					$army->size = cUnit::GetUnitsSum($army->units);
					if ($army->size <= 0.0) { 
						// armee ist verhungert
						cArmy::DeleteArmy($army->id); continue; 
					}
				} 
			}
		}
	}

	if ($army->nextactiontime > $time) continue;
	
	ArmyThink($army);
}
mysql_free_result($r);
}



if (kProfileArmyLoop) LoopProfiler_flush(true); // report profiling

} // perfomance tes} // perfomance testt
?>

<?php
	if(!isset($lock) && $mlock)
		if(file_exists("/tmp/zw-cron.lock"))
			shell_exec("rm -f /tmp/zw-cron.lock");

}
?>
