<html>
<head>
<!-- <meta http-equiv="refresh" content="60; URL=cron.php"> -->
<!-- ... andere Angaben im Dateikopf ... -->
</head>
<body>
<?php
//$gSQL_NOT_FATAL = true; 
$lock = FALSE;
if($lock){
	if(file_exists("/tmp/zw-cron.lock")){
		$fstat = stat("/tmp/zw-cron.lock");
		if(time() - $stat[9] > 600){
			shell_exec("/bin/rm -f /tmp/zw-cron.lock");
			shell_exec("echo lock > /tmp/zw-cron.lock");
		}else{
			exit(1);
		}
	}else{
		shell_exec("echo lock > /tmp/zw-cron.lock");
	}
}


error_reporting(E_ALL);

require_once("cronlib.php");
require_once("lib.quest.php");
require_once("lib.map.php");
require_once("lib.technology.php");
require_once("lib.spells.php");
require_once("lib.army.php"); // sql
require_once("lib.weather.php");
require_once("lib.hellholes.php");
require_once("lib.spells.php");
require_once("lib.score.php");
require_once("lib.hook.php");


// wichtige GLOBALS INITIALISIEREN!!! nix loeschen es sei denn ihr seid euch _WIRKLICH_ sicher
$gTechnologyLevelsOfAllUsers = sqlgetgrouptable("SELECT `user`,`type`,`level` FROM `technology`","user","type","level");
$gVerbose = false; // if false, echo only segments

$time = time();
$lasttick = $gGlobal["lasttick"];
$dtime = $time - $lasttick;
if($dtime < 0)$dtime = 0;
sql("UPDATE `global` SET `value`=$time WHERE `name`='lasttick'");

$gGlobal["ticks"]++;
if($gGlobal["ticks"] > 30000)$gGlobal["ticks"] = 0; // todo : unhardcode
sql("UPDATE `global` SET `value`=".intval($gGlobal["ticks"])." WHERE `name`='ticks'");

echo "dtime = $dtime<br><br>";


//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
profile_page_start("cron.php - fire",true);
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
if (true) {

	    
//fire spreading neighbours
$n = array();
$n[] = array("x"=>-1,	"y"=>0);
$n[] = array("x"=>+1,	"y"=>0);
$n[] = array("y"=>-1,	"x"=>0);
$n[] = array("y"=>+1,	"x"=>0);

//delete old fires, those fields count as totaly burned down
$t = sqlgettable("SELECT * FROM `fire` WHERE `created`<".time()."-".kFireLivetime);
foreach($t as $x)FirePutOutBurnedDown($x->x,$x->y);

//reads out the fire fields that cause damage and do it, hahahaha
$f = sqlgettable("SELECT * FROM `fire` WHERE `nextdamage`<".time());
foreach($f as $x){
		sql("UPDATE `fire` SET `nextdamage`=".(time()+kFireDamageTimeout)." WHERE `x`=$x->x AND `y`=$x->y");
		echo "fire at ($x->x,$x->y) cause damage<br>\n";
		sql("UPDATE `building` SET `hp`=`hp`-".kFireDamage." WHERE `x`=$x->x AND `y`=$x->y");
		if(mysql_affected_rows() > 0){
				echo "building gets ".kFireDamage." damage<br>\n";
				$b = sqlgetobject("SELECT * FROM `building` WHERE `x`=$x->x AND `y`=$x->y");
				if($b->hp <= 0){
						//destroy burned down buildings
						cBuilding::removeBuilding($b,$b->user);
						echo "building destroyed<br>\n";
						FirePutOutObj($x);
				}
		}
}
//fire spread  and putout handling
$f = sqlgettable("SELECT * FROM `fire` WHERE `nextspread`<".time());
foreach($f as $x){
		//random outout check
		echo "fire at ($x->x,$x->y) put out?<br>\n";
		$r = rand(0,100);
		if($r < $x->putoutprob){
				echo "***** oki this fire was put out<br>\n";
				FirePutOutObj($x);
				continue;
		}
		
		//random spread check
		echo "fire at ($x->x,$x->y) tries to spread<br>\n";
		shuffle($n);
		foreach($n as $y){
				$r = rand(0,100);
				if($r < kFireSpreadProbability){
						$r = rand(0,100);
						//oki this fire spreads
						$px = (int)($y["x"] + $x->x);
						$py = (int)($y["y"] + $x->y);
						
						$spread = false;
						//is there a burnable building?
						$b = sqlgetone("SELECT `type` FROM `building` WHERE `x`=$px AND `y`=$py LIMIT 1");
						if(empty($b)){
								//echo "check terrain<br>\n";
								//no building, so check the terrain
								$t = cMap::StaticGetTerrainAtPos($px,$py);
								if($gTerrainType[$t]->flags & kTerrainTypeFlag_CanBurn && $r < $gTerrainType[$t]->fire_prob)$spread = true;
						} else {
								//echo "check building<br>\n";
								//check the building burnable flag
								if($gBuildingType[$b]->flags & kBuildingTypeFlag_CanBurn && $r < $gBuildingType[$b]->fire_prob)$spread = true;
						}
						if($spread){
								echo "***** fire at ($x->x,$x->y) spreads to ($px,$py)<br>\n";
								FireSetOn($px,$py);
								//set time for next spread test
								sql("UPDATE `fire` SET `nextspread`=".(time()+kFireSpreadTimeout)." WHERE `x`=$x->x AND y=$x->y");
						}
				}
		}
}
}
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
profile_page_start("cron.php - bier",true);
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

$t = sqlgettable("SELECT * FROM `title` WHERE `title`='Brauereimeister'");
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

//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
profile_page_start("cron.php - gammel items",true);
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

if (0) {
	sql("LOCK TABLES	
						`sqlerror` WRITE, `phperror` WRITE,
						`item` WRITE,
						`itemtype` READ");
	$gammelitems = sqlgettable("SELECT `item`.`id` FROM `item`,`itemtype` WHERE `itemtype`.`id` = `item`.`type` AND
		 `itemtype`.`gammeltime` > 0 AND `item`.`param` > 0 AND `item`.`param` <= $time");
	foreach ($gammelitems as $o) {
		echo "item bei $o->x,$o->y vergammelt<br>";
		/*
		if ($gItemType[$o->type]->gammeltype == 0)
				sql("DELETE FROM `item` WHERE `id` = ".$o->id." LIMIT 1");
		else	sql("UPDATE `item` SET `type` = ".$gItemType[$o->type]->gammeltype." WHERE `id` = ".$o->id." LIMIT 1");
		*/
	}
	sql("UNLOCK TABLES");
	unset($gammelitems);
}

//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
profile_page_start("cron.php - build buildings",true);
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++


// lock around build building, upgrade building and user, for UserPay optimization
TablesLock();
$res = Array("lumber","food","stone","metal","runes");
foreach($res as $r) sql("UPDATE `user` SET `$r`=0 WHERE `$r`<0");
$gAllUsers = sqlgettable("SELECT * FROM `user` ORDER BY `id`","id");
$gPayCache_Users =& $gAllUsers;
// now it is save calculate paying on this buffer, UserPay() uses it automatically
// TODO : all paying and res production within this lock must now operate on this array (via reference)
// WARNING : all changes to user ressources within this lock that are not operating on this cache, are overwritten at the end

$cons = sqlgettable("SELECT * FROM `building` WHERE `construction` > 0","user");
foreach($gAllUsers as $x) {
	$o = isset($cons[$x->id])?$cons[$x->id]:false;
	if($o) { 
		if($time > $o->construction || kZWTestMode) {
			if ($gVerbose) echo "fertiggestellt : ".$gBuildingType[$o->type]->name."(".$o->x.",".$o->y.")<br>";
			
			$now = microtime_float();
			CompleteBuild($o,($x->flags & kUserFlags_AutomaticUpgradeBuildingTo)>0);
			echo "Profile CompleteBuild : ".sprintf("%0.3f",microtime_float()-$now)."<br>\n";
		}
	} else {
		$mycon = sqlgetobject("SELECT * FROM `construction` WHERE `user`=".$x->id." ORDER BY `priority` LIMIT 1");
		if ($mycon) {
			$now = microtime_float();
			if (startBuild($mycon))
				if ($gVerbose) echo "gestartet : ".$gBuildingType[$mycon->type]->name."(".$mycon->x.",".$mycon->y.")<br>";
			echo "Profile startBuild : ".sprintf("%0.3f",microtime_float()-$now)."<br>\n";
		}
	}
}
unset($cons);


//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
profile_page_start("cron.php - think buildings",true);
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

if (true) {
$time = time();
if (!isset($gGlobal["nextbuildingthink"]) || $time >= $gGlobal["nextbuildingthink"]) {
	SetGlobal("nextbuildingthink",$time + 60*11); // next think in 5 minutes
	echo "step<br>";
	$typelist = array_merge($gFlaggedBuildingTypes[kBuildingTypeFlag_CanShootArmy],$gFlaggedBuildingTypes[kBuildingTypeFlag_CanShootBuilding]);
	if (count($typelist) > 0) {
		$buildings = sqlgettable("SELECT * FROM `building` WHERE `type` IN (".implode(",",$typelist).")");
		foreach ($buildings as $o) {
			cBuilding::Think($o);
		}
	}
}
}

//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
profile_page_start("cron.php - upgrade buildings",true);
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

$time = time();
$hqlevels = sqlgettable("SELECT `user`,`level` FROM `building` WHERE `type`=".kBuilding_HQ,"user");
$sieges = sqlgettable("SELECT `building` FROM `siege`","building");

$count_start = 0;
$count_end = 0;
//$buildings = sqlgettable("SELECT * FROM `building` WHERE `upgrades` > 0 ORDER BY `level`");  OLD
$mysqlresult = sql("SELECT * FROM `building` WHERE `upgrades` > 0");
echo "testing ".mysql_num_rows($mysqlresult)." buildings for upgrades<br>\n";
while ($o = mysql_fetch_object($mysqlresult)) {
	if ($o->upgradetime > 0 && ($o->upgradetime < $time || kZWTestMode)) {
		$count_end++;
		// upgrade finished
		if (isset($sieges[$o->id])) continue;
		$maxhp = cBuilding::calcMaxBuildingHp($o->type,$o->level+1);
		$up = $maxhp - cBuilding::calcMaxBuildingHp($o->type,$o->level);
		$heal = $maxhp/100*2.0;
		
		sql("UPDATE `building` SET
			`level` = `level` + 1 ,
			`upgrades` = GREATEST(0,`upgrades` - 1) ,
			`hp` = LEAST(`hp`+".($up+$heal)." , $maxhp),
			`upgradetime` = 0 WHERE `id` = ".$o->id." LIMIT 1");
		// echo "upgrade auf ".($o->level+1)." fertig : ".$gBuildingType[$o->type]->name."(".$o->x."|".$o->y."), hpup=".$up.", hpheal=".$heal."<br>\n";
		LogMe($o->user,NEWLOG_TOPIC_BUILD,NEWLOG_UPGRADE_FINISHED,$o->x,$o->y,$o->level+1,$gBuildingType[$o->type]->name,"",false);
		//$o = sqlgetobject("SELECT * FROM `building` WHERE `id`=".$o->id." LIMIT 1");
		$o->level++;
		$o->upgrades = max(0,$o->upgrades-1);
		$o->hp = min($o->hp+$up+$heal,$maxhp);
		$o->upgradetime = 0;
		Hook_UpgradeBuilding($o);
	} else if ($o->upgradetime == 0) {
		$count_start++;
		// test if upgrade can be started
		if (!isset($hqlevels[$o->user])) continue;
		if (isset($sieges[$o->id])) continue;
		$hqlevel = $hqlevels[$o->user]->level;
		$level = $o->level + 1;
		if($level <= (3*($hqlevel+1))) { // TODO : unhardcode
			$mod = cBuilding::calcUpgradeCostsMod($level);
			if (UserPay($o->user,
				$mod * $gBuildingType[$o->type]->cost_lumber,
				$mod * $gBuildingType[$o->type]->cost_stone,
				$mod * $gBuildingType[$o->type]->cost_food,
				$mod * $gBuildingType[$o->type]->cost_metal,
				$mod * $gBuildingType[$o->type]->cost_runes)) {
				// echo "upgrade auf $level gestartet : ".$gBuildingType[$o->type]->name."(".$o->x."|".$o->y.")<br>\n";
				$finishtime = $time + cBuilding::calcUpgradeTime($o->type,$level);
				sql("UPDATE `building` SET `upgradetime` = ".$finishtime." WHERE `id` = ".intval($o->id)." LIMIT 1");
			}
		}
	}
}
echo "<br>\ns=".($count_start++);
echo "<br>\ne=".($count_end++)."<br>\n";
mysql_free_result($mysqlresult);
unset($sieges);
unset($hqlevels);

//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
profile_page_start("cron.php - user",true);
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++




$zeroset_btypes = array($gGlobal["building_hq"],$gGlobal["building_house"],$gGlobal["building_store"],$gGlobal["building_runes"]);

foreach($gAllUsers as $u) {
	$b = sqlgettable("SELECT count( `id` ) AS `count` , `type` AS `type` , sum( `level` ) AS `level` , sum(`supportslots`) as `supportslots` FROM `building` WHERE `construction`=0 AND `user`=".$u->id." GROUP BY `type`","type");
		
	foreach ($zeroset_btypes as $key) if (!isset($b[$key])) {
		$b[$key]->count = 0;
		$b[$key]->level = 0;
		$b[$key]->supportslots = 0;
	}
	
	// calc max pop
	$maxpop =	$gGlobal["pop_slots_hq"]	* ($b[$gGlobal["building_hq"]]->count + $b[$gGlobal["building_hq"]]->level) +
				$gGlobal["pop_slots_house"]	* ($b[$gGlobal["building_house"]]->count + $b[$gGlobal["building_house"]]->level);
	//echo "user=".$u->id." ".$u->name.": maxpop=$maxpop/".$u->maxpop."<br>";
	$usersets = "`maxpop`=$maxpop";
	
	if($b[$gGlobal["building_hq"]]->count == 0) {
		// no haupthaus -> new player start ressources
		$sr = $gGlobal["store"];
		switch($u->race){
			case kRace_Gnome:
				$usersets .= ",	`max_lumber`=$sr,`lumber`=$sr,
								`max_stone`=$sr,`stone`=$sr,
								`max_food`=$sr,`food`=$sr,
								`max_metal`=$sr,`metal`=$sr,
								`max_runes`=$sr, `runes`=$sr ";
			break;
			default:
				$usersets .= ",	`max_lumber`=$sr,`lumber`=$sr,
								`max_stone`=$sr,`stone`=$sr,
								`max_food`=$sr,`food`=$sr,
								`max_metal`=$sr,`metal`=$sr,
								`max_runes`=0, `runes`=0 ";
			break;
		}
	} else {
		// haupthaus -> normale berechnung // TODO : unhardcode
		$store =	$gGlobal["store"] * ($b[$gGlobal["building_hq"]]->count + $b[$gGlobal["building_hq"]]->level) + 
					$gGlobal["store"] * ($b[$gGlobal["building_store"]]->count + $b[$gGlobal["building_store"]]->level);
		$rstore = 	2500			  * ($b[$gGlobal["building_runes"]]->count + $b[$gGlobal["building_runes"]]->level);
		
		// WARNING : all changes to user ressources within this lock that are not operating on $gPayCache_Users or $gAllUsers, are overwritten here
		switch($u->race){
			case kRace_Gnome:
				$usersets .= ",	`max_lumber`=$store, `lumber`=".$u->lumber.",
								`max_stone`=$store, `stone`=".$u->stone.",
								`max_food`=$store, `food`=".$u->food.",
								`max_metal`=$store, `metal`=".$u->metal.",
								`max_runes`=$store, `runes`=".$u->runes;
			break;
			default:
				$usersets .= ",	`max_lumber`=$store, `lumber`=".$u->lumber.",
								`max_stone`=$store, `stone`=".$u->stone.",
								`max_food`=$store, `food`=".$u->food.",
								`max_metal`=$store, `metal`=".$u->metal.",
								`max_runes`=$rstore, `runes`=".$u->runes;
			break;
		}
		$prodfaktoren = GetProductionFaktoren($u->id);
		$gAllUsers[$u->id]->prodfaktoren = $prodfaktoren; // for later use
		$slots = GetProductionSlots($u->id,$b);
		foreach($gRes as $resname=>$resfield) {
			$btype = $gGlobal["building_".$resfield];
			$prod_factor = $prodfaktoren[$resfield];
			$w = $u->pop * $u->{"worker_".$resfield} / 100; // anzahl zugewiesene arbeiter
			$s = $slots[$resfield]; // anzahl slots
			$p = (min($w,$s) + max(($w - $s),0) * $gGlobal["prod_faktor_slotless"]) * ($gGlobal["prod_faktor"]) * $prod_factor; // produktion
			
			// Grundproduktion
			if (isset($gGrundproduktion[$u->race]) && isset($gGrundproduktion[$u->race][$resfield]))
				$p += $gGrundproduktion[$u->race][$resfield];
				
			$usersets .= ",`prod_$resfield`=$p";
		}
	}
	sql("UPDATE `user` SET ".$usersets." WHERE `id`=".$u->id);
	
	//check if there is enough food for the population
	$foodneed = calcFoodNeed($u->pop,$dtime);
	$food = $gAllUsers[$u->id]->food;
	$food -= $foodneed;
	if($food <= 0){
		//people die
		$gAllUsers[$u->id]->pop = max(0,$gAllUsers[$u->id]->pop-$foodneed);
		echo "$foodneed units food in $dtime needed by ".$u->name."\n<br>";
	}
	$gAllUsers[$u->id]->food = max(0,$food);
	sql("UPDATE `user` SET `food`=".($gAllUsers[$u->id]->food).",`pop`=".($gAllUsers[$u->id]->pop)." WHERE `id`=".$u->id);
	
	unset($b);
}


// lock around build building, upgrade building and user, for UserPay optimization
unset($gPayCache_Users);
TablesUnlock();

//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
if (($gGlobal["ticks"] % 30) == 0 || empty($gGlobal["weather"])){  // TODO : unhardcode
	profile_page_start("cron.php - weather",true);
	SetGlobal("weather",GetWeather($gWeatherUrl));
}

//remove zero items
if (($gGlobal["ticks"] % 30) == 0)sql("DELETE FROM `item` WHERE `amount` = 0");

$gSupportslotsFrequency = 5*60; // TODO : unhardcode  5 hours makes it less probable to fall together with 6h backup
 
//update support slots (braucht recht viel rechenzeit, und produziert tonnen von querries (2 pro produktionsgebauede))
// muss nur sehr selten berechnet werden
// todo : wenn das spaeter mal kritisch wird, update bei erzeugen der felder statt hier
if($gGlobal["ticks"] % $gSupportslotsFrequency == 0) {
	profile_page_start("cron.php - supportslots",true);
	$supportslotbuildings = sqlgetgrouptable("SELECT * FROM `building` WHERE (
	`type`=".$gGlobal["building_lumber"]." OR 
	`type`=".$gGlobal["building_stone"]." OR 
	`type`=".$gGlobal["building_food"]." OR 
	`type`=".$gGlobal["building_runes"]." OR 
	`type`=".$gGlobal["building_metal"].")","user");
	foreach($gAllUsers as $u) {
		if(!isset($supportslotbuildings[$u->id]))continue;
		$t = $supportslotbuildings[$u->id];
		foreach($t as $x) getSlotAddonFromSupportFields($x);
	}
}




//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
profile_page_start("cron.php - rarestuff",true);
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++



if(($gGlobal["ticks"] % 60) == 0){  // TODO : unhardcode
	echo "remove old log<br>";
	sql("DELETE FROM `newlog` WHERE $time-`time`>60*60*24*7");  // TODO : unhardcode
}

//if(($gGlobal["ticks"] % (60*6)) == 0)$gGlobal["ticks"] % 60 == 0){

echo "generate points...<br>";
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



//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
profile_page_start("cron.php - magic",true);
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

$spells = sqlgettable("SELECT * FROM `spell`");
foreach($spells as $o) {
	$spell = GetSpellInstance($o->type,$o);
	$spell->Cron($dtime);
	unset($spell);
}
unset($spells);

//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
profile_page_start("cron.php - production and population",true);
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

echo "calc res,pop mana... ".($dtime/3600)."<br>";

//sql("UPDATE `user` SET `pop`=`maxpop` WHERE `pop`>`maxpop`");

sql("UPDATE `user` SET	`pop`=LEAST(`maxpop`,`pop`+".($dtime/300).") ,
						`lumber`=`lumber`+`prod_lumber`*".($dtime/3600)." , 
						`stone`=`stone`+`prod_stone`*".($dtime/3600)." ,
						`food`=`food`+`prod_food`*".($dtime/3600)." ,
						`metal`=`metal`+`prod_metal`*".($dtime/3600));

//gnome:
sql("UPDATE `user` SET `runes`=`runes`+`prod_runes`*".($dtime/3600)." WHERE `race`=".kRace_Gnome); // TODO : unhardcode

// todo : optimize by select max with group by guild ??
//calc guild max resources
echo "calc guild max res...<br>";
$gGuilds = sqlgettable("SELECT * FROM `guild`");
foreach($gGuilds as $x){
	$s = "";
	foreach($gResFields as $r)$s .= ", sum(`max_$r`) as `max_$r`";
	$s{0} = ' ';
	$s = "SELECT".$s;
	$s .= " FROM `user` WHERE `guild`=".$x->id;
	$o = sqlgetobject($s);
	sql("UPDATE `guild` SET ".obj2sql($o)." WHERE `id`=".$x->id);
	if ($gVerbose) echo "Guild ".$x->id." res max set to ".implode("|",obj2array($o))."<br>";
}
echo "enforcing guild max res ....<br>";
$set="";
// todo : single query
foreach($gRes as $f=>$r)
	sql("UPDATE `guild` SET `$r`=`max_$r` WHERE `$r`>`max_$r");
echo "done<br><br>";

//repairs broken buildings if user want this
echo "calc guild max res...<br>";

echo "repair buildings...<br>";
//MAXHP: ceil($maxhp + $maxhp/100*1.5*$level);  // NOTE: see also lib.building.php calcMaxBuildingHp

profile_page_start("cron.php - repairing",true);

TablesLock();
$t = sqlgettable("SELECT `user`.`id` as `id`, COUNT( * ) as `broken`,`user`.`pop` as `pop`,`user`.`worker_repair` as `worker_repair`
FROM `user`, `building`, `buildingtype`
WHERE 
	`building`.`construction`=0 AND `buildingtype`.`id` = `building`.`type` AND `building`.`user` = `user`.`id` AND `user`.`worker_repair`>0 AND 
	`building`.`hp`<CEIL(`buildingtype`.`maxhp`+`buildingtype`.`maxhp`/100*1.5*`building`.`level`)
GROUP BY `user`.`id`");
foreach($t as $x){
	//one worker should be able to repair one hp in one day and consume 100 wood and 100 stone for this
	if($x->broken == 0)continue;
	$worker = $x->pop * $x->worker_repair/100;
	$broken = $x->broken;
	$all = $worker*$dtime/(24*60*60);
	$plus = $all / $broken;
	$wood = $all * 100;
	$stone = $all * 100;
	
	echo "$worker worker repair $all, $plus hp in $broken buildings of user $x->id consuming $wood wood and $stone stone\n<br>";
	if(!UserPay($x->id,$wood,$stone,0,0,0))continue;
	sql("UPDATE `building`, `buildingtype` SET `building`.`hp` = LEAST(
		`building`.`hp`+($plus),
		CEIL(`buildingtype`.`maxhp`+`buildingtype`.`maxhp`/100*1.5*`building`.`level`)
		) WHERE 
		`building`.`construction`=0 AND `building`.`user`=".intval($x->id)." AND `building`.`type`=`buildingtype`.`id` AND
		`building`.`hp`<CEIL(`buildingtype`.`maxhp`+`buildingtype`.`maxhp`/100*1.5*`building`.`level`)");
	echo mysql_affected_rows()." buildings updated\n<br>";
}
sql("UPDATE `building`, `buildingtype` SET `building`.`hp` = CEIL(`buildingtype`.`maxhp`+`buildingtype`.`maxhp`/100*1.5*`building`.`level`) WHERE 
	`building`.`construction`=0 AND `building`.`type`=`buildingtype`.`id` AND
	`building`.`hp`>CEIL(`buildingtype`.`maxhp`+`buildingtype`.`maxhp`/100*1.5*`building`.`level`)");
echo mysql_affected_rows()." buildings had to much hp and were reduced to maxhp\n<br>";
TablesUnlock();

profile_page_start("cron.php - mana generation",true);

$basemana=$gBuildingType[$gGlobal['building_runes']]->basemana;
// TODO : unhardcode
sql("UPDATE `building` SET `mana`=LEAST((`level`+1)*$basemana,`mana`+($basemana*(`level`+1)/(10+`level`/20)*".($dtime/3600).")) WHERE `type`=".$gGlobal['building_runes']);

profile_page_start("cron.php - runes production",true);

foreach($gAllUsers as $u){
	switch($u->race){
		default:
			$rpfs = (0.8 + 
				(isset($gTechnologyLevelsOfAllUsers[$u->id][kTech_MagieMeisterschaft])?$gTechnologyLevelsOfAllUsers[$u->id][kTech_MagieMeisterschaft]:0)*0.6)/2;
		   if($u->worker_runes>0){
				if(($u->lumber+$u->prod_lumber*($dtime/3600)) >= ($rpfs*($u->worker_runes*$u->pop/100*$gGlobal['lc_prod_runes'])*($dtime/3600))){
					$l=$rpfs*1;
				}else{
					$l=$rpfs*($u->lumber+$u->prod_lumber*($dtime/3600))/($u->worker_runes*$u->pop/100*$gGlobal['lc_prod_runes']*($dtime/3600));
				}
				if(($u->metal+$u->prod_metal*($dtime/3600)) >= ($rpfs*($u->worker_runes*$u->pop/100*$gGlobal['mc_prod_runes'])*($dtime/3600))){
					$m=$rpfs*1;
				}else{
					$m=$rpfs*($u->metal+$u->prod_metal*($dtime/3600))/($u->worker_runes*$u->pop/100*$gGlobal['mc_prod_runes']*($dtime/3600));
				}
				if(($u->stone+$u->prod_stone*($dtime/3600)) >= ($rpfs*($u->worker_runes*$u->pop/100*$gGlobal['sc_prod_runes']*($dtime/3600)))){
					$s=$rpfs*1;
				}else{
					$s=$rpfs*($u->stone+$u->prod_stone*($dtime/3600))/($u->worker_runes*$u->pop/100*$gGlobal['sc_prod_runes']*($dtime/3600));
				}
				if(($u->food+$u->prod_food*($dtime/3600)) >= ($rpfs*($u->worker_runes*$u->pop/100*$gGlobal['fc_prod_runes']*($dtime/3600)))){
					$f=$rpfs*1;
				}else{
					$f=$rpfs*($u->food+$u->prod_food*($dtime/3600))/($u->worker_runes*$u->pop/100*$gGlobal['fc_prod_runes']*($dtime/3600));
				}
				$factor=round(min($l,$m,$s,$f),3);
				sql("UPDATE `user` SET `runes`=`runes`+`prod_runes`*".($dtime/3600)."*".$factor." WHERE `id`=".$u->id);
				UserPay($u->id,	$u->worker_runes*$u->pop/100*$gGlobal['lc_prod_runes']*$factor*($dtime/3600),
								$u->worker_runes*$u->pop/100*$gGlobal['sc_prod_runes']*$factor*($dtime/3600),
								$u->worker_runes*$u->pop/100*$gGlobal['fc_prod_runes']*$factor*($dtime/3600),
								$u->worker_runes*$u->pop/100*$gGlobal['mc_prod_runes']*$factor*($dtime/3600));
			}
		break;
		case kRace_Gnome:
		break;
	}
}

profile_page_start("cron.php - flush res to guild",true);

echo "flush user res to guild... <br>";
TablesLock();
foreach($gResFields as $r){
	$t = sqlgettable("SELECT `id`,`$r`,`max_$r`,`guild` FROM `user` WHERE `guild`>0 AND `$r`>`max_$r`");
	foreach($t as $x) {
		$radd = ($x->{$r}) - ($x->{"max_$r"});
		sql("UPDATE `guild` SET `$r`=`$r`+($radd) WHERE `id`=".$x->guild);
		sql("UPDATE `user` SET `guildpoints`=`guildpoints`+($radd) WHERE `id`=".$x->id);
		//echo "add user ".$x->id." res to guild ".$x->guild." [$r] $radd<br>";
	}
	unset($t);
	sql("UPDATE `user` SET `$r`=`max_$r` WHERE `$r`>`max_$r`");
}
TablesUnlock();


profile_page_start("cron.php - weltbank",true);
// weltbank

//TODO .. dies produziert zu viele sql querys 

foreach ($gAllUsers as $id=>$u){
	$id=$u->id;
	if(($u->general_pts+$u->army_pts)<intval($gGlobal['wb_paybacklimit']))continue;
	$w=floatval(sqlgetone("SELECT `value` FROM `guild_pref` WHERE `var`='schulden_".$u->id."'"));
	if($w==0)continue;
	foreach ($gResFields as $r){
		$prod="prod_$r";
		if($u->{$prod}>0){
			$radd=intval($gGlobal['wb_payback_perc'])*$u->{$prod}/100/3600*$dtime;
		}
		sql("UPDATE `guild` SET `$r`=`$r`+($radd) WHERE `id`=".kGuild_Weltbank);
		sql("UPDATE `user` SET `guildpoints`=`guildpoints`+($radd) WHERE `id`=".$u->id);
		if ($gVerbose)  echo "user ".$u->name." (".$u->id.") payes res to guild ".kGuild_Weltbank." [$r] $radd (ressources left to pay: $w)<br>";
		$w-=$radd;
	}
	if($w<1)
		sql("DELETE FROM `guild_pref` WHERE `guild`=".kGuild_Weltbank." AND `var`='schulden_$id' OR `var`='schulden_$id'");
	else
		sql("UPDATE `guild_pref` SET `value`='$w' WHERE `var`='schulden_$id'");
}

//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
profile_page_start("cron.php - army_move",true);
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

$old_time = $time;
$old_dtime = $dtime;
$gMiniCronFromCron = true;
include("minicron.php");
$time = $old_time;
$dtime = $old_dtime;

//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
profile_page_start("cron.php - hellholes",true);
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

$hellholes = sqlgettable("SELECT * FROM `hellhole`");
foreach($hellholes as $o) {
	$hellhole = GetHellholeInstance($o);
	$hellhole->Cron($dtime);
}
unset($hellholes);

//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
profile_page_start("cron.php - QuestStep",true);
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++


// called after army_move and hellhole monstermove, so the army cache is usable
QuestTrigger_CronStep(); // todo : call at start, so triggers can use quest-cache ??


//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
profile_page_start("cron.php - oldshooting",true);
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

$oldshootings = sqlgettable("SELECT * FROM `shooting` WHERE `autocancel` = 1 AND 
	`start`		< ".($time-kShootingAlarmTimeout)." AND 
	`lastshot`	< ".($time-kShootingAlarmTimeout));
foreach ($oldshootings as $o) {
	cFight::EndShooting($o,"Es wurde lange nicht mehr geschossen");
} 
unset($oldshootings);

//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
profile_page_start("cron.php - army_fight",true);
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++


$fights = sqlgettable("SELECT * FROM `fight`");
if (count($fights) > 0) {
	TablesLock();
	foreach ($fights as $fight) cFight::FightStep($fight);
	TablesUnlock();
}
unset($fights);



//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
profile_page_start("cron.php - actions part1",true);
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++



$i_bsteps = kZWTestMode ? kZWTestMode_BuildingActionSteps : 1;
//for ($kbstep=0;$kbstep<$i_bsteps;$kbstep++) {

	// process running actions
	$running_actions = sqlgettable("SELECT * FROM `action` WHERE `starttime` > 0 GROUP BY `building`");
	foreach ($running_actions as $action) {
		$unittype = $gUnitType[$action->param1];
		
		// has one action cycle completed ?
		if ($time >= ($action->starttime + $unittype->buildtime) || kZWTestMode) {						
			if ($action->param2 > 0) {
				// unit complete
				switch ($action->cmd) {
					case kActionCmd_Build:
						$curtargetid = GetBParam($action->building,"target",0);
						// TODO : der check hier ob das gebaeude existiert geht vermutlich auf die performance, bessere bei gebaeude tot alle stationierungen auf das gebaeude canceln
						if (!sqlgetone("SELECT 1 FROM `building` WHERE `id` = ".intval($curtargetid))) $curtargetid = 0;
						if ($curtargetid == 0) $curtargetid = $action->building;
						
						// pay pop
						$actionuserid = intval(sqlgetone("SELECT `user` FROM `building` WHERE `id` = ".intval($action->building)));
						sql("UPDATE `user` SET `pop`=`pop`-1 WHERE `id`=$actionuserid");
						
						cUnit::AddUnits($curtargetid,$action->param1,1,kUnitContainer_Building,$actionuserid);
					break;
				}
				
				//echo "action ".$action->id." : in building ".$action->building." produced one ".$unittype->name." (".($action->param2-1)." left)<br>";

				if ($action->param2-1 > 0)
						sql("UPDATE `action` SET `starttime` = 0 , `param2` = `param2` - 1 WHERE `id` = ".$action->id);
				else	sql("DELETE FROM `action` WHERE `id` = ".$action->id);
			} else sql("DELETE FROM `action` WHERE `id` = ".$action->id);
		}
	}
	unset($running_actions);

profile_page_start("cron.php - actions part2",true);
$gAvailableUnitTypesByUser = array();

	// start action where building has nothing to do
	$waiting_actions = sqlgettable("SELECT *,MAX(`starttime`) as `maxstarttime` FROM `action` GROUP BY `building`");
	foreach ($waiting_actions as $action) if ($action->maxstarttime == 0) {
		$unittype = $gUnitType[$action->param1];
		$actionuserid = intval(sqlgetone("SELECT `user` FROM `building` WHERE `id` = ".intval($action->building)));
		
		$availableUnitTypes = false;
		if (isset($gAvailableUnitTypesByUser[$actionuserid])) {
			$availableUnitTypes = $gAvailableUnitTypesByUser[$actionuserid];
		} else {
			$availableUnitTypes = array();
			$gAvailableUnitTypesByUser[$actionuserid] = $availableUnitTypes;
		}
		
		$available = false;
		if (isset($availableUnitTypes[$unittype->id])) {
			$available = $availableUnitTypes[$unittype->id];
		} else {
			$available = HasReq($unittype->req_geb,$unittype->req_tech_a.",".$unittype->req_tech_v,$actionuserid);
			$gAvailableUnitTypesByUser[$actionuserid][$unittype->id] = $available;
		}
		
		// only build if the technological requirements are met
		if (!$available) {
			sql("DELETE FROM `action` WHERE `id` = ".$action->id);
			continue;
		}
		
		// building weight-limit, used to block ramme
		$max_weight_left_source = cUnit::GetMaxBuildingWeight($gUnitType[$action->param1]->buildingtype);
		if ($max_weight_left_source >= 0) {
			$curtargetid = GetBParam($action->building,"target",0);
			if ($curtargetid == 0) $curtargetid = $action->building;
			$max_weight_left_source -= cUnit::GetUnitsSum(cUnit::GetUnits($curtargetid,kUnitContainer_Building),"weight");
			if ($max_weight_left_source < $gUnitType[$action->param1]->weight) continue;
		}
		
		if (sqlgetone("SELECT `pop` FROM `user` WHERE `id` = ".intval($actionuserid)) <= 0 || 
			!UserPay($actionuserid,$unittype->cost_lumber,$unittype->cost_stone,
									$unittype->cost_food,$unittype->cost_metal,$unittype->cost_runes))
		{
			//echo "action ".$action->id." : in building ".$action->building." (".$action->param2." ".$unittype->name.") is waiting for ressources<br>";
			continue;
		}
								
		sql("UPDATE `action` SET `starttime` = ".$time." WHERE `id` = ".$action->id);
		//echo "action ".$action->id." in building ".$action->building." started<br>";
	}
	unset($waiting_actions);
//}




//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
profile_page_start("cron.php - tech",true);
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++



//$gTechnologyTypes = sqlgettable("SELECT * FROM `technologytype`","id");
sql("LOCK TABLES `user` WRITE,`technology` WRITE,`building` READ,`phperror` WRITE,
						`sqlerror` WRITE, `newlog` WRITE");
$technologies = sqlgettable("SELECT * FROM `technology` WHERE `upgrades` > 0 ORDER BY `level`");
$time = time();
foreach ($technologies as $o) {
	if ($o->upgradetime > 0 && ($o->upgradetime < $time || kZWTestMode) ){
		// upgrade finished

		//only complete the tech if requirenments meet
		//echo "<br>\nHasReq(".($gTechnologyType[$o->type]->req_geb).",".($gTechnologyType[$o->type]->req_tech).",".($o->user).",".($o->level+1).")<br>\n";
		if(HasReq($gTechnologyType[$o->type]->req_geb,$gTechnologyType[$o->type]->req_tech,$o->user,$o->level+1)){
			sql("UPDATE `technology` SET
				`level` = `level` + 1 ,
				`upgrades` = `upgrades` - 1 ,
				`upgradetime` = 0 WHERE `id` = ".$o->id." LIMIT 1");
				
			$gTechnologyLevelsOfAllUsers[$o->user][$o->type] = $o->level + 1;
			
			$text = $gTechnologyType[$o->type]->name." von user ".$o->user." ist nun Level ".($o->level+1);
			echo $text."<br>\n";
			
			// TODO : neue log meldung machen !
			LogMe($o->user,NEWLOG_TOPIC_BUILD,NEWLOG_UPGRADE_FINISHED,0,0,$o->level+1,$gBuildingType[$o->type]->name,"",false);
		} else {
			sql("UPDATE `technology` SET
				`upgrades` = 0 ,
				`upgradetime` = 0 WHERE `id` = ".$o->id." LIMIT 1");
				
			$text = $gTechnologyType[$o->type]->name." von user ".$o->user." wurde abgebrochen, da die anforderungen nicht erfüllt wurden";
			echo $text."<br>\n";
		}
	} else if ($o->upgradetime == 0) {
		// test if upgrade can be started
		
		// only one upgrade per building at once
		$other = sqlgetone("SELECT 1 FROM `technology` WHERE 
			`upgradetime` > 0 AND `upgradebuilding` = ".$o->upgradebuilding." AND `id` <> ".$o->id);
		if (!$other) {
			$techtype = $gTechnologyType[$o->type];
		
			// only upgrade if the technological requirements are met
			if (!HasReq($techtype->req_geb,$techtype->req_tech,$o->user,GetTechnologyLevel($o->type,$o->user)+1)) {
				sql("UPDATE `technology` SET `upgrades` = 0 WHERE `id` = ".$o->id." LIMIT 1");
				continue;
			}
		
			$upmod = cTechnology::GetUpgradeMod($o->type,$o->level);
			if (UserPay($o->user,
				$upmod * $techtype->basecost_lumber,
				$upmod * $techtype->basecost_stone,
				$upmod * $techtype->basecost_food,
				$upmod * $techtype->basecost_metal,
				$upmod * $techtype->basecost_runes)) {
				if ($gVerbose) echo $techtype->name." von user ".$o->user." upgrade gestartet<br>\n";
				$finishtime = $time + cTechnology::GetUpgradeDuration($o->type,$o->level);
				sql("UPDATE `technology` SET `upgradetime` = ".$finishtime." WHERE `id` = ".$o->id." LIMIT 1");
			}
		}
	}
}
sql("UNLOCK TABLES");



//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
profile_page_start("cron.php - pillage",true);
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

$pillages = sqlgettable("SELECT * FROM `pillage`");
if (count($pillages) > 0) {
	TablesLock();
	foreach ($pillages as $pillage) if (!in_array($pillage->army,$gAllFights)) cFight::PillageStep($pillage); 
	TablesUnlock();
}
unset($pillages);

//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
profile_page_start("cron.php - siege",true);
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

$sieges = sqlgettable("SELECT * FROM `siege`");
if (count($sieges) > 0) {
	TablesLock();
	foreach ($sieges as $siege) if (!in_array($siege->army,$gAllFights)) cFight::SiegeStep($siege); 
	TablesUnlock();
}
unset($sieges);



//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
profile_page_start("cron.php - young forest",true);
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

define("kStumpToYoung",1.0/(60*24));
define("kYoungToForest",1.0/(60*24));
// array(from,to,probability)
$growarr = array(0=>array(kTerrain_TreeStumps,kTerrain_YoungForest,kStumpToYoung),
					array(kTerrain_YoungForest,kTerrain_Forest,kYoungToForest));
foreach ($growarr as $arr) {
	$c = 0;
	$r = sql("SELECT * FROM `terrainsegment4` WHERE `type` = ".$arr[0]);
	while ($seg = mysql_fetch_object($r)) for ($y=0;$y<4;++$y) for ($x=0;$x<4;++$x) if (rand() <  $arr[2]*getrandmax()) {
		if (sqlgetone("SELECT 1 FROM `terrain` WHERE `x` = ".($seg->x*4 + $x)." AND `y` = ".($seg->y*4 + $y))) continue;
		sql("REPLACE INTO `terrain` SET `type` = ".$arr[1]." , `x` = ".($seg->x*4 + $x)." , `y` = ".($seg->y*4 + $y));
		++$c;
	}
	mysql_free_result($r);
	sql("UPDATE `terrain` SET `type` = ".$arr[1]." WHERE `type` = ".$arr[0]." AND RAND() < ".$arr[2]);
	echo (mysql_affected_rows()+$c)." units of ".$gTerrainType[$arr[0]]->name." turned to ".$gTerrainType[$arr[1]]->name."<br>";
}
/*
$growwood = sqlgettable("SELECT * FROM `terrain` WHERE `type` = ".kTerrain_YoungForest." AND RAND() < ".kYoungToForest);
echo count($growwood)." units of YoungForest turned to Forest<br>";
foreach ($growwood as $o) {
	sql("UPDATE `terrain` SET `type` = ".kTerrain_Forest." WHERE `id` = ".$o->id." LIMIT 1");
	RegenSurroundingNWSE($o->x,$o->y);
}
unset($growwood);
*/
/*
//if map to old (1 day) or there is not map then generate
if(($gGlobal["ticks"] % 60*24) == 0 || !file_exists(GetMiniMapFile("user",GetMiniMapLastTime("user")))){
	//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	profile_page_start("cron.php - minimap",true);
	//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	
	// generate new minimaps for each mode
	$o = sqlgetobject("SELECT MIN(`x`) as minx,MAX(`x`) as maxx,MIN(`y`) as miny,MAX(`y`) as maxy FROM `building`");
	$left = $o->minx - 10;
	$right = $o->maxx + 10;
	$top = $o->miny - 10;
	$bottom = $o->maxy + 10;
	SetGlobal("minimap_left",$left);
	SetGlobal("minimap_right",$right);
	SetGlobal("minimap_top",$top);
	SetGlobal("minimap_bottom",$bottom);
	
	$modes = array("user","creep","guild");
	foreach($modes as $mode){
		$global = GetMiniMapGlobal($mode);
		$filename = GetMiniMapFile($mode,$time);
		echo "rendering $mode minimap to file $filename ...<br>\n";
		renderMinimap($top,$left,$bottom,$right,$filename,$mode);
		SetGlobal($global,$time);
	}
}
*/

//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
profile_page_start("cron.php - misc",true);
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++


echo "remove guild requests if user has a guild...<br>";
$t = sqlgettable("SELECT r.`id`,u.`name` FROM `guild_request` r,`user` u WHERE r.`user`=u.`id` AND u.`guild`>0");
foreach($t as $x){
	echo "remove request from $x->name<br>";
	sql("DELETE FROM `guild_request` WHERE `id`=".intval($x->id));
}
echo "done<br>";

/*
echo "grow cornfields ...<br>";
$farm = sqlgetobject("SELECT * FROM `building` WHERE `type`=".$gGlobal["building_food"]." ORDER BY RAND() LIMIT 1");
if($farm && (rand()%10==0)){ // TODO : unhardcode
	echo "grow!!! ";
	$radius = 1;
	$done = false;
	for($x=-$radius;$x<=$radius;++$x)
		for($y=-$radius;$y<=$radius;++$y)if(!$done){
			$b = sqlgetobject("SELECT * FROM `building` WHERE `x`=(".($x+$farm->x).") AND `y`=(".($y+$farm->y).")");
			$t = sqlgetobject("SELECT * FROM `terrain` WHERE `x`=(".($x+$farm->x).") AND `y`=(".($y+$farm->y).")");
			if(!$b && (!$t || $t->type == kTerrain_Grass)){
				sql("DELETE FROM `terrain` WHERE `x`=(".($x+$farm->x).") AND `y`=(".($y+$farm->y).")");
				$o = null;
				$o->x = $x+$farm->x;
				$o->y = $y+$farm->y;
				$o->type = kTerrain_Field;
				sql("INSERT INTO `terrain` SET ".obj2sql($o));
				echo " field crow at (".$o->x."|".$o->y.")<br>";
				$done = true;
			}
		}
}
echo "done<br><br>";
*/

//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

/*
echo "grow wood ...<br>";
TablesLock();
$wood = sqlgetobject("SELECT `x`,`y` FROM `terrain` WHERE `type`=".kTerrain_Forest." ORDER BY RAND() LIMIT 1");
if ($wood){
	echo "grow!!! ";
	$radius = 2; // TODO : unhardcode
	$done = false;
	$x = rand(-$radius,$radius)+$wood->x;
	$y = rand(-$radius,$radius)+$wood->y;

	$b = sqlgetobject("SELECT `id` FROM `building` WHERE `x`=(".($x).") AND `y`=(".($y).")");
	$t = sqlgetobject("SELECT `id`,`type` FROM `terrain` WHERE `x`=(".($x).") AND `y`=(".($y).")");
	if(empty($b) && (empty($t) || $t->type == kTerrain_Grass)) {
		echo " wood grow at ($x|$y)<br>";
		setTerrain($x,$y,kTerrain_YoungForest);
		$done = true;
	}
}
echo "done<br>";
TablesUnlock();
*/



//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
profile_page_end();
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++



sql("UPDATE `global` SET `value`='".((time() - $time + 2 * (float)($gGlobal["crontime"])) / 3)."' WHERE `name`='crontime'");

// generating stats for cute little diagrams
$dt = $gGlobal["stats_nexttime"] - time();
if($dt <= 0)
{
	profile_page_start("cron.php - stats",true);
	echo "collection stats ...";
	include("stats.php");
	sql("UPDATE `global` SET `value`=".(time()+kStats_dtime)." WHERE `name`='stats_nexttime' LIMIT 1");
	echo "done\n\n";
	profile_page_end();
	
	
	//TODO remove me cause i am a ugly quickfix
	if (1) {
		profile_page_start("cron.php - firecount workaround",true);
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
	}
}
else echo $dt."sec left to net stats collection.\n\n";
?>
</body>
</html>
<?php
	if($lock)
		shell_exec("rm -f /tmp/zw-cron.lock");

echo "<br>\n... cron finished";
?>
