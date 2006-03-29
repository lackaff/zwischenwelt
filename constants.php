<?php

define("kURL_Nethack","http://zwischenwelt.org/telnet.html");
define("kURL_Chess","http://zwischenwelt.org/~hagish/occ/");
define("kURL_Wiki","http://zwischenwelt.milchkind.net/zwwiki/index.php/");

define("kDefaultResFormat","HOR G2 RG BR AK / MK TAB");

define("kMapNaviTool_Look"			,0);
define("kMapNaviTool_Plan"			,1);
define("kMapNaviTool_SetTerrain"	,2); // admin and terraformer only
define("kMapNaviTool_WP"			,3);
define("kMapNaviTool_Route"			,4);
define("kMapNaviTool_Cancel"		,5);
define("kMapNaviTool_SetBuilding"	,6); // admin only (TODO : restricted for terraformer ?)
define("kMapNaviTool_SetArmy"		,7); // admin only
define("kMapNaviTool_SetItem"		,8); // admin only
define("kMapNaviTool_Pick"			,9);
define("kMapNaviTool_Center"		,10);
define("kMapNaviTool_Zap"			,11); // admin only
define("kMapNaviTool_Ruin"			,12); // admin only
define("kMapNaviTool_rmArmy"		,13); // admin only
define("kMapNaviTool_rmItem"		,14); // admin only
define("kMapNaviTool_Clear"			,15); // admin only
define("kMapNaviTool_QuickMagic"	,20);
define("kMapNaviTool_MultiTool"		,30);


define("kAdminCanAccessMysql",true);

define("kMapNaviGotoCat_Pos",1);
define("kMapNaviGotoCat_Mark",2);
define("kMapNaviGotoCat_Own",3);
define("kMapNaviGotoCat_Guild",4);
define("kMapNaviGotoCat_Friends",5);
define("kMapNaviGotoCat_Enemies",6);
define("kMapNaviGotoCat_Search",7);
define("kMapNaviGotoCat_Random",8);
define("kMapNaviGotoCat_Hellhole",10);
$gMapNaviGotoCatNames = array(
	kMapNaviGotoCat_Pos			=> "Pos",
	kMapNaviGotoCat_Mark		=> "MapMark",
	kMapNaviGotoCat_Own			=> "Einheiten",
	kMapNaviGotoCat_Guild		=> "Gilde",
	kMapNaviGotoCat_Friends		=> "Freunde",
	kMapNaviGotoCat_Enemies		=> "Feinde",
	kMapNaviGotoCat_Search		=> "Suche",
	kMapNaviGotoCat_Random		=> "Zufall",
	kMapNaviGotoCat_Hellhole	=> "Hellhole",
	);
$gMapNaviGotoCat_AdminOnly = array(0=>kMapNaviGotoCat_Hellhole);

define("kJSMapBuildingFlag_Open",			(1<<0));
define("kJSMapBuildingFlag_Tax",			(1<<1)); // todo
define("kJSMapBuildingFlag_Locked",			(1<<2)); // todo : (portal cannot be used, display a little lock icon..)  
define("kJSMapBuildingFlag_BeingSieged",	(1<<3));
define("kJSMapBuildingFlag_BeingPillaged",	(1<<4));
define("kJSMapBuildingFlag_Shooting",		(1<<5));
define("kJSMapBuildingFlag_BeingShot",		(1<<6));

define("kJSMapArmyFlag_Controllable",	(1<<0));
define("kJSMapArmyFlag_GC",				(1<<1));
define("kJSMapArmyFlag_Fighting",		(1<<2));
define("kJSMapArmyFlag_Sieging",		(1<<3));
define("kJSMapArmyFlag_Pillaging",		(1<<4));
define("kJSMapArmyFlag_Shooting",		(1<<5));
define("kJSMapArmyFlag_BeingShot",		(1<<6));
/*
define("kJSMapArmyFlag_Moving_N",(1<<0)); // todo
define("kJSMapArmyFlag_Moving_W",(1<<1)); // todo
define("kJSMapArmyFlag_Moving_S",(1<<2)); // todo
define("kJSMapArmyFlag_Moving_E",(1<<3)); // todo
define("kJSMapArmyFlag_Fighting_N",(1<<4)); // todo
define("kJSMapArmyFlag_Fighting_W",(1<<5)); // todo
define("kJSMapArmyFlag_Fighting_S",(1<<6)); // todo
define("kJSMapArmyFlag_Fighting_E",(1<<7)); // todo
*/
		
define("kNWSE_N",1);
define("kNWSE_W",2);
define("kNWSE_S",4);
define("kNWSE_E",8);
define("kNWSE_ALL",kNWSE_N|kNWSE_W|kNWSE_S|kNWSE_E);

define("kStats_UserInfo",1);
define("kStats_SysInfo_Misc",2);
define("kStats_SysInfo_Army",4);
define("kStats_SysInfo_Fight",3);
define("kStats_SysInfo_Trade",5);
define("kStats_SysInfo_Chat",6);
define("kStats_SysInfo_Magic",7);
define("kStats_SysInfo_Activity",8);

define("kFOF_Neutral",0);
define("kFOF_Friend",1);
define("kFOF_Enemy",2);

define("kTerrain_Grass",1);
define("kTerrain_River",2);
define("kTerrain_Mountain",3);
define("kTerrain_Hole",5);
define("kTerrain_Sea",6);
define("kTerrain_Forest",4);
define("kTerrain_Field",8);
define("kTerrain_Flowers",10);
define("kTerrain_Rubble",11);
define("kTerrain_TreeStumps",12);
define("kTerrain_YoungForest",13);
define("kTerrain_SnowyMountain",15);
define("kTerrain_Swamp",16);
define("kTerrain_Oasis",9);
define("kTerrain_Desert",7);
define("kTerrain_DeepSea",18);

define("kHarvestAmount",500); // gain so much res by harvesting one field

define("kTerrain_Flag_Moveable_Land",		1<<0);
define("kTerrain_Flag_Moveable_Wood",		1<<1);
define("kTerrain_Flag_Moveable_Mountain",	1<<2);
define("kTerrain_Flag_Moveable_River",		1<<3);
define("kTerrain_Flag_Moveable_Sea",		1<<4);
define("kTerrain_Flag_Moveable_DeepSea",	1<<5);
define("kTerrain_Flag_Moveable_Mask_All",	(1<<16)-1);
$gMovableFlagMainTerrain = array( // for graphic representation
	kTerrain_Flag_Moveable_Land => kTerrain_Grass,
	kTerrain_Flag_Moveable_Wood => kTerrain_Forest,
	kTerrain_Flag_Moveable_Mountain => kTerrain_Mountain,
	kTerrain_Flag_Moveable_River => kTerrain_River,
	kTerrain_Flag_Moveable_Sea => kTerrain_Sea,
	kTerrain_Flag_Moveable_DeepSea => kTerrain_DeepSea,
);

define("kTerrain_Flag_Moveable_Mask_OnLand",
	kTerrain_Flag_Moveable_Land | 
	kTerrain_Flag_Moveable_Wood);
define("kTerrain_Flag_Moveable_Mask_OnWater",
	kTerrain_Flag_Moveable_Sea | 
	kTerrain_Flag_Moveable_River | 
	kTerrain_Flag_Moveable_DeepSea);
	
define("kTerrain_Mask_Moveable_Default",kTerrain_Flag_Moveable_Mask_OnLand);

define("kArmy_ExpBonus",$gGlobal["kArmy_ExpBonus"]); //10);
define("kArmy_AW_for_one_exp",$gGlobal["kArmy_AW_for_one_exp"]); //800.0); 
define("kPortalTaxUnitNum",$gGlobal["kPortalTaxUnitNum"]); //1000);
define("kBaracksDestMaxDist",$gGlobal["kBaracksDestMaxDist"]); //30);
define("kArmy_BigArmyGoSlowLimit",$gGlobal["kArmy_BigArmyGoSlowLimit"]); //10000);
define("kArmy_BigArmyGoSlowFactorPer1000Units",$gGlobal["kArmy_BigArmyGoSlowFactorPer1000Units"]); //1.01);

define("kTech_Architecture",34);

define("kTech_Erdbeben",31);
define("kTech_Strike",35);
define("kTech_EffRunen",25);
define("kTech_Hammer",21);
define("kTech_Axt",22);
define("kTech_Spitzhacke",24);
define("kTech_Sense",23);
define("kTech_SchichtArbeit",26);
define("kTech_Bier",71);
define("kTech_MagieMeisterschaft",58);

define("kTerraFormer_SicherheitsAbstand",$gGlobal["kTerraFormer_SicherheitsAbstand"]); //25);

define("kUserFlags_TerraFormer",				(1<<0)); // set by admin
define("kUserFlags_DropDownMenu",				(1<<1)); // obsolete
define("kUserFlags_BugOperator",				(1<<2)); // set by admin
define("kUserFlags_ShowLogFrame",				(1<<3));
define("kUserFlags_DontShowWikiHelp",			(1<<4));
define("kUserFlags_NoMonsterFightReport",		(1<<5));
define("kUserFlags_AutomaticUpgradeBuildingTo",	(1<<6));
define("kUserFlags_DontShowNoobTip",			(1<<7));
define("kUserFlags_ShowMaxRes",					(1<<8));
define("kUserFlags_NoTabs",						(1<<9));
define("kUserFlags_SlowMap",					(1<<10));
$gUserFlagNames = array(
	kUserFlags_TerraFormer					=> "TerraFormer",
	kUserFlags_BugOperator					=> "BugOperator",
	kUserFlags_DropDownMenu					=> "DropDownMenu benutzen (geht nur im Firefox!)?", // obsolete
	
	kUserFlags_ShowLogFrame					=> "LogFrame anzeigen?",
	kUserFlags_DontShowWikiHelp				=> "WikiHilfe ausblenden",
	kUserFlags_NoMonsterFightReport			=> "Monsterkampfberichte entfernen",
	kUserFlags_AutomaticUpgradeBuildingTo	=> "bei neuen Gebäuden automatisch Upgrades planen",
	kUserFlags_DontShowNoobTip				=> "NoobTips ausblenden",
	kUserFlags_ShowMaxRes					=> "Lagerkapazität anzeigen (obsolete, da man nun das komplette Format einstellen kann)",
	kUserFlags_NoTabs						=> "Tabs Deaktivieren (bei Abstürzen im IE)",
	kUserFlags_SlowMap						=> "Karte langsam aufbauen (hilft gegen 'Script-abbrechen?'-Warnungen)",
);
$gUserFlag_AdminSet = array( kUserFlags_TerraFormer, kUserFlags_BugOperator, kUserFlags_DropDownMenu);


define("kDiplo_BreakFriendOnAttack","diplo_breakfriendonattack");

define("kGuild_Weltbank",8);
define("kGuild_Weltbank_Founder",249);

define("kRace_Mensch",1);
define("kRace_Gnome",2);

define("LOG_MISC",0);
define("LOG_FIGHT",1);
define("LOG_BUILD",2);
define("LOG_SYSTEM",3);
define("LOG_TRADE",4);

define("P_OPEN",1);
define("P_CLOSED",0);
define("P_GOPEN",2);
define("P_TAX",3);
define("P_GOPEN_TAX",4);

define("NEWLOG_TOPIC_MISC",0);
define("NEWLOG_TOPIC_FIGHT",1);
define("NEWLOG_TOPIC_BUILD",2);
define("NEWLOG_TOPIC_SYSTEM",3);
define("NEWLOG_TOPIC_TRADE",4);
define("NEWLOG_TOPIC_GUILD",5);
define("NEWLOG_TOPIC_MAGIC",6);

define("NEWLOG_PILLAGE_ATTACKER_START",1);
define("NEWLOG_PILLAGE_ATTACKER_STOP",2);
define("NEWLOG_PILLAGE_DEFENDER_START",3);
define("NEWLOG_PILLAGE_DEFENDER_STOP",4);

define("NEWLOG_FIGHT_START",5);
define("NEWLOG_FIGHT_STOP",6);

define("NEWLOG_TRADE",9);

define("NEWLOG_UPGRADE_FINISHED",10);
define("NEWLOG_BUILD_FINISHED",11);

define("NEWLOG_RAMPAGE_ATTACKER_START",12);
define("NEWLOG_RAMPAGE_ATTACKER_CANCEL",13);
define("NEWLOG_RAMPAGE_DEFENDER_START",14);
define("NEWLOG_RAMPAGE_DEFENDER_CANCEL",15);
define("NEWLOG_RAMPAGE_ATTACKER_DESTROY",16);
define("NEWLOG_RAMPAGE_DEFENDER_DESTROY",17);

define("NEWLOG_PILLAGE_ATTACKER_CANCEL",18);
define("NEWLOG_PILLAGE_DEFENDER_CANCEL",19);

define("NEWLOG_ARMY_RES_PUTDOWN",20);
define("NEWLOG_ARMY_RES_GETOUT",21);

define("NEWLOG_GUILD_TRANSFER_ERROR",22);

define("NEWLOG_MAGIC_CAST_FAIL",23);
define("NEWLOG_MAGIC_CAST_SUCCESS",24);
define("NEWLOG_MAGIC_DAMAGE_TARGET",25);
define("NEWLOG_MAGIC_HELP_TARGET",26);

// magic

define("MTARGET_PLAYER",2);
define("MTARGET_AREA",3);
define("MTARGET_ARMY",4);

define("kSpellType_FruchtbaresLand",1);
define("kSpellType_Erzbaron",2);
define("kSpellType_Zauberwald",3);
define("kSpellType_Steinreich",4);
define("kSpellType_ArmeeDerToten",6);
define("kSpellType_LoveAndJoy",8);
define("kSpellType_Regen",9);
define("kSpellType_Duerre",10);
define("kSpellType_Pest",11);

// army

define("kArmyFlag_GuildCommand",			(1<<0));
define("kArmyFlag_Patrol",					(1<<1));
define("kArmyFlag_AutoAttack",				(1<<2));
define("kArmyFlag_WillingToAbortFight",		(1<<3));
define("kArmyFlag_AttackBlockingArmy",		(1<<4));
define("kArmyFlag_SiegeBlockingBuilding",	(1<<5));
define("kArmyFlag_AlwaysCollectItems",		(1<<6));
define("kArmyFlag_RecalcBlockedRoute",		(1<<7));
define("kArmyFlag_AutoAttackRangeMonster",	(1<<8));
define("kArmyFlag_CaptureShips", 			(1<<9));
define("kArmyFlag_Wander", 					(1<<10)); // monsters : random movement
define("kArmyFlag_RunToEnemy",				(1<<11)); // monsters : attack
define("kArmyFlag_HarvestForest",			(1<<12));
define("kArmyFlag_HarvestRubble",			(1<<13));
define("kArmyFlag_HarvestField",			(1<<14));
define("kArmyFlag_Captured",				(1<<15)); // system flag, captured fleets
define("kArmyFlag_SelfLock",				(1<<16)); 
define("kArmyFlag_LastWaypointArrived",		(1<<17)); // system flag, limits RecalcBlockedRoute
define("kArmyFlag_AutoSiege",				(1<<18));
define("kArmyFlag_AutoDeposit",				(1<<19));
define("kArmyFlag_AutoPillage",				(1<<20));
define("kArmyFlag_BuildingWait",			(1<<21)); // systemflag
define("kArmyFlag_AutoGive_Own",			(1<<22)); // worker
define("kArmyFlag_AutoGive_Guild",			(1<<23)); // worker
define("kArmyFlag_AutoGive_Friend",			(1<<24)); // worker
define("kArmyFlag_AutoPillageOff",			(1<<25)); // systemflag
define("kArmyFlag_HoldFire",				(1<<26)); // dont'shoot, so we can move
define("kArmyFlag_AutoShoot_Enemy",			(1<<27));
define("kArmyFlag_AutoShoot_Strangers",		(1<<28));
define("kArmyFlag_SiegePillage",			(1<<29)); // army steals ressources while pillaging (used for ants)
define("kArmyFlag_StopSiegeWhenFull",		(1<<30));
$gArmyFlagNames = array(
	kArmyFlag_GuildCommand=>			"unter Gildenkommando",
	kArmyFlag_SelfLock=>				"Armee nicht selbst steuern", // blockiert wegpunkt setzen
	kArmyFlag_RecalcBlockedRoute=>		"ausweichen, wenn blockiert",
	kArmyFlag_WillingToAbortFight=>		"bereit, den Kampf abzubrechen",
	kArmyFlag_Patrol=>					"Patrouillenmodus (WP wiederholen)",
	kArmyFlag_AutoAttack=>				"feindliche Armeen automatisch angreifen",
	kArmyFlag_AutoAttackRangeMonster =>	"automatisch auf Monster schießen",
	kArmyFlag_AttackBlockingArmy=>		"blockierende Armeen automatisch angreifen",
	kArmyFlag_SiegeBlockingBuilding=>	"blockierende Gebäude automatisch belagern",
	kArmyFlag_CaptureShips => 			"Die Flotte versucht Schiffe zu übernehmen",
	kArmyFlag_RunToEnemy => 			"Auf Feinde zulaufen",
	kArmyFlag_Wander => 				"Ziellos umherwandern, wenn kein WP gesetzt ist",
	kArmyFlag_AlwaysCollectItems=>		"Alle Gegenstände einsammeln",
	kArmyFlag_HarvestForest => 			"Wald abernten",
	kArmyFlag_HarvestRubble => 			"Geröll abernten",
	kArmyFlag_HarvestField => 			"Felder abernten",
	kArmyFlag_Captured => 				"in Gefangenschaft(systemflag)",
	kArmyFlag_LastWaypointArrived => 	"letzter wegpunkt wurde erreicht(systemflag)",
	kArmyFlag_AutoSiege => 				"Feinde automatisch belagern",
	kArmyFlag_AutoPillage => 			"Feinde automatisch plündern",
	kArmyFlag_AutoDeposit => 			"automatisch in freundliche Lager einzahlen",
	kArmyFlag_BuildingWait => 			"wait for wp bevore next auto-building action", // stops kArmyFlag_AutoDeposit
	kArmyFlag_AutoPillageOff => 		"wait for move bevore next auto-pillage action", // stops kArmyFlag_AutoPillage
	kArmyFlag_AutoGive_Own => 			"Eigene Armeen/Karawanen automatisch beladen",
	kArmyFlag_AutoGive_Guild => 		"Gilden-Armeen/Karawanen automatisch beladen",
	kArmyFlag_AutoGive_Friend => 		"Freundliche Armeen/Karawanen automatisch beladen",
	kArmyFlag_AutoShoot_Enemy => 		"Automatisch auf Feinde schiessen",
	kArmyFlag_AutoShoot_Strangers => 	"Automatisch auf Fremde schiessen",
	kArmyFlag_HoldFire => 				"Feuer einstellen (während dem Schiessen ist man unbeweglich)",
	kArmyFlag_SiegePillage => 			"Armee erbeutet beim belagern Baumaterial (Ameisen)",
	kArmyFlag_StopSiegeWhenFull =>		"Armee hört auf zu belagern, wenn sie vollgeladen ist (Ameisen)",
	);
	
	
/*
ALTER TABLE `armytype` ADD `flags` INT UNSIGNED NOT NULL AFTER `ownerflags` ;
define("kArmyTypeFlag_CanShootArmy",		1<<0); // used for cannon towers, building can shoot
define("kArmyTypeFlag_CanShootBuilding",	1<<1); // used for cannon towers, building can shoot
$gArmyTypeFlagNames = array(
	kArmyTypeFlag_CanShootArmy => 		"kann Armeen schiessen",
	kArmyTypeFlag_CanShootBuilding => 	"kann Gebäude schiessen",
);
*/


define("kArmyRecalcBlockedRoute_Timeout",$gGlobal["kArmyRecalcBlockedRoute_Timeout"]); //5*60);
define("kArmyAutoAttackRangeMonster_Timeout",$gGlobal["kArmyAutoAttackRangeMonster_Timeout"]); //5*60);

define("kArmyType_Siege",1);
define("kArmyType_Fleet",3);
define("kArmyType_Normal",4);
define("kArmyType_Karawane",5);
define("kArmyType_Arbeiter",6);

define("ARMY_ACTION_ATTACK",1);
define("ARMY_ACTION_PILLAGE",2);
define("ARMY_ACTION_SIEGE",3);
define("ARMY_ACTION_ALWAYSCOLLECT",4);
define("ARMY_ACTION_DEPOSIT",5);
define("ARMY_ACTION_WAIT",6);
define("ARMY_ACTION_RANGEATTACK",7); // OBSOLUTE, use table "shooting" instead of "action"

// units

define("kUnitContainer_Army","army");
define("kUnitContainer_Transport","transport");
define("kUnitContainer_Building","building");
$gNumber2ContainerType = array(kUnitContainer_Army,kUnitContainer_Transport,kUnitContainer_Building);
$gContainerType2Number = array_flip($gNumber2ContainerType);

define("kUnitType_Miliz",1);
define("kUnitType_Kaempfer",2);
define("kUnitType_SchwertKrieger",3);
define("kUnitType_LanzenTraeger",4);
define("kUnitType_Berserker",5);
define("kUnitType_Ritter",6);
define("kUnitType_Ramme",10);
define("kUnitType_Baummonster",11);
define("kUnitType_EisenGolem",12);
define("kUnitType_SteinGolem",13);
define("kUnitType_Trollkoenig",14);
define("kUnitType_Schatzkiste",15);
define("kUnitType_TowerMage",16);
define("kUnitType_GhostKnight",17);
define("kUnitType_Bug",18);
define("kUnitType_Blob",19);
define("kUnitType_Puschel",20);
define("kUnitType_Ork",21);
define("kUnitType_Huhn",22);
define("kUnitType_Schlange",23);
define("kUnitType_Squid",24);
define("kUnitType_Schwertmeister",25);
define("kUnitType_Elitekaempfer",26);
define("kUnitType_Lanzentraegerveteran",27);
define("kUnitType_Berserkerhaeuptling",28);
define("kUnitType_Rittermeister",29);
define("kUnitType_Zentaur",30);
define("kUnitType_Hellhound",31);
define("kUnitType_Gurke",32);
define("kUnitType_HyperBlob",33);
define("kUnitType_Einmaster",39);
define("kUnitType_TransportShip",4000); // todo : einheit einbauen + id anpassen
define("kUnitType_Zombie",49);
define("kUnitType_Ghost",48);
define("kUnitType_Kamel",50);
define("kUnitType_Worker",51);
define("kUnitType_Kanone",53);
define("kUnitType_Ameise",55);


define("kMonster_HyperblobID",kUnitType_HyperBlob);
define("kMonster_HyperblobCSS","hb_%NWSE%");
define("kMonster_HyperblobGFX","hyperblob/blob-%NWSE%.png");

$gRandomSpawnTypes = explode(",",$gGlobal["randomspawnmonsters"]);
// array(Baummonster,EisenGolem,SteinGolem,Trollkoenig,Bug,Blob);

define("kUnitFlag_Elite",		(1<<0));
define("kUnitFlag_Monster",		(1<<1));
define("kUnitFlag_Undead",		(1<<2));
define("kUnitFlag_AllSet", 		(1<<16)-1); // (at least one above the others)-1
$gUnitFlagName = array(
	kUnitFlag_Elite=>"Elite",
	kUnitFlag_Monster=>"Monster",
	kUnitFlag_Undead=>"Untot",
	);
			
			
			
			
// items

define("kItem_Portalstein_Blau",30);
define("kItem_Portalstein_Gruen",31);
define("kItem_Portalstein_Schwarz",32);
define("kItem_Portalstein_Rot",33);
define("kItem_FaulesEi",100);
define("kItem_Osterei0",72);
define("kItem_Amboss",101);
define("kItem_Stiefel",102);
define("kItem_Spam",103);


$gResFields = array("lumber","stone","food","metal","runes");
$gResNames = array("Holz","Stein","Nahrung","Metall","Runen");
$gResTypeVars = Array(1 => "lumber",2 => "stone", 3 => "food", 4 => "metal", 5 => "runes");
$gResTypeNames = Array(1 => "Holz",2 => "Stein", 3 => "Nahrung", 4 => "Metall", 5 => "Runen");

//resource list
$gRes = my_array_combine($gResNames,$gResFields);
//list of things that can be done by people, worker adjustment
$gAdjust = array_merge($gRes,array("Reparieren"=>"repair"));

define("kSiegePillageEfficiency",2.0); // get a multitude of the damage as ressources, so ants don't have to wait that long...

define("kResItemType_lumber","44");
define("kResItemType_stone","45");
define("kResItemType_food","46");
define("kResItemType_metal","47");
define("kResItemType_runes","48");

$gGrundproduktion = array(
	kRace_Mensch => array(
		"lumber"=>100,
		"stone"=>100,
		"food"=>50,
		),
	kRace_Gnome => array(
		"lumber"=>50,
		"stone"=>50,
		"food"=>50,
		"metal"=>50,
		"runes"=>50,
		),
	);

$gRes2ItemType = array(	"lumber"=>	kResItemType_lumber,
						"stone"=>	kResItemType_stone,
						"food"=>	kResItemType_food,
						"metal"=>	kResItemType_metal,
						"runes"=>	kResItemType_runes);
$gItemType2Res = array_flip($gRes2ItemType);

define("kItemFlag_NoPickup",		(1<<0)); // TODO : corpses/gibs
define("kItemFlag_Invis",			(1<<1)); // TODO : implement me
define("kItemFlag_GammelOnPickup",	(1<<2)); // TODO : test/use me
define("kItemFlag_Ware",			(1<<3)); // warenuebersicht, kann von spio geklaut werden.
//define("kItemFlag_XXX",			(1<<4)); // xxxx
define("kItemFlag_UseOnPick",		(1<<5)); // call use function on pickup
define("kItemFlag_UseGivesCost",	(1<<6)); // the cost times the amount is added to the army on use
define("kItemFlag_AllSet",			(1<<8)-1); // (at least one above the others)-1

$gItemFlagNames = array(kItemFlag_NoPickup=>		"NoPickup",
						kItemFlag_Invis=>			"Invis", 
						kItemFlag_GammelOnPickup=>	"GammelOnPickup",
						kItemFlag_Ware=>			"Ware",
						kItemFlag_UseOnPick=>		"UseOnPick",
						kItemFlag_UseGivesCost=>	"UseGivesCost");




// quests

define("kQuestFlag_Delete_QuestItems_On_Finish",	(1<<0));
define("kQuestFlag_Delete_QuestArmy_On_Finish",		(1<<1));
define("kQuestFlag_Permanent",						(1<<2));
define("kQuestFlag_RepeatOnFinish",					(1<<3));

// buildings

define("kBuildingRequirenment_CrossRadius",2); // max dist to any owned building
define("kBuildingRequirenment_NearRadius",2);
define("kBuildingRequirenment_NextToRadius",0); // 0 means adjacted
define("kBuildingRequirenment_ExcludeRadius",0); // 0 means adjacted
define("kHQ_Upgrade_BaseTime",43200);

define("kBuilding_HQ",1);
define("kBuilding_MagicTower",2);
define("kBuilding_Path",3);
define("kBuilding_BROID",4);
define("kBuilding_Wall",5);
define("kBuilding_House",6);
define("kBuilding_Silo",7); // lager
define("kBuilding_Baracks",8);
define("kBuilding_Farm",9);
define("kBuilding_Hospital",10);
define("kBuilding_Garage",11); // werkstatt
define("kBuilding_Smith",12);
define("kBuilding_Lumberjack",13);
define("kBuilding_StoneProd",14);
define("kBuilding_IronMine",15);
define("kBuilding_Market",16);
define("kBuilding_Gate",17);
define("kBuilding_Bridge",18);
define("kBuilding_Sign",19);
define("kBuilding_Temple",20);
define("kBuilding_Hellhole",21);
define("kBuilding_Chessboard",22);
define("kBuilding_Portal",23);
define("kBuilding_GB",24); // gatebridge
define("kBuilding_Brunnen",25); 
define("kBuilding_Teehaus",43);
define("kBuilding_Gaertner",44);
define("kBuilding_Observatorium",45);
define("kBuilding_Werft",46);
define("kBuilding_Harbor",47);
define("kBuilding_Steg",48);
define("kBuilding_SeaWall",49);
define("kBuilding_SeaGate",50);
define("kBuilding_Tavern",51);
define("kBuilding_Galgen",52);
define("kBuilding_Spielhalle",64);
define("kBuilding_Platz",65);
define("kBuilding_Leuchtturm",66);
define("kBuilding_Verteidigungsturm",73);


define("kBuildingTypeFlag_BuildDistSource",		1<<0); // 1 not yet used, set for hq,silo(lager),harbor, affects build-distance
define("kBuildingTypeFlag_Speedy",				1<<1); // 2 not yet used, affected by newbee factor
define("kBuildingTypeFlag_Openable",			1<<2); // 4 not yet used, ->$gOpenableBuildingTypes
define("kBuildingTypeFlag_Taxable",				1<<3); // 8 not yet used, ->$gTaxableBuildingTypes
define("kBuildingTypeFlag_CanShootArmy",		1<<4); // 16 used for cannon towers, building can shoot armies
define("kBuildingTypeFlag_CanShootBuilding",	1<<5); // 16 used for cannon towers, building can shoot buildings
define("kBuildingTypeFlag_OthersCanSeeUnits",	1<<6); // 32 used for cannon towers, other players can see units inside
define("kBuildingTypeFlag_DrawMaxTypeOnTop",	1<<7); // 64 used for cannon towers, draw maximal unit type on top
define("kBuildingTypeFlag_Bodenschatz",			1<<8); // 128 not yet used -> $gBodenSchatzBuildings
define("kBuildingTypeFlag_IsInQuickJump",			1<<9); // apears in the quickjump bar
$gBuildingTypeFlagNames = array(
	kBuildingTypeFlag_BuildDistSource => 	"BuildDistSource (Abstand zu diesen typen bestimmt den bauzeit faktor)",
	kBuildingTypeFlag_Speedy => 			"Speedy (newbee Faktor betrifft diese gebäude)",
	kBuildingTypeFlag_Openable => 			"Openable (absperren : Tor,Portal)",
	kBuildingTypeFlag_Taxable => 			"Taxable (besteuern : Portal)",
	kBuildingTypeFlag_CanShootArmy => 		"kann auf Armeen schiessen (Turm)",
	kBuildingTypeFlag_CanShootBuilding => 	"kann auf Gebäude schiessen (Turm)",
	kBuildingTypeFlag_OthersCanSeeUnits => 	"fremde Spieler können Einheiten im Gebäude sehen (Turm)",
	kBuildingTypeFlag_DrawMaxTypeOnTop => 	"Haupt-EinheitenTyp wird über das Gebäudebild gezeichnet (Kanonen-Turm)",
	kBuildingTypeFlag_Bodenschatz => 		"Bodenschatz",
	kBuildingTypeFlag_IsInQuickJump => 		"im Schnellsprung?",
);

 // TODO : unhardcode : set flags in db and empty these lists
$gFlaggedBuildingTypes = array();
$gFlaggedBuildingTypes[kBuildingTypeFlag_BuildDistSource] = array(0=>kBuilding_HQ,kBuilding_Silo,kBuilding_Harbor);
$gFlaggedBuildingTypes[kBuildingTypeFlag_Speedy] = array(0=>6,7,8,9,11,12,13,14,15,16,20,22,23);
$gFlaggedBuildingTypes[kBuildingTypeFlag_Openable] = array(0=>kBuilding_Portal,kBuilding_GB,kBuilding_Gate,kBuilding_SeaGate);
$gFlaggedBuildingTypes[kBuildingTypeFlag_Taxable] = array(0=>kBuilding_Portal);
$gFlaggedBuildingTypes[kBuildingTypeFlag_Bodenschatz] = array(55,56,57,58,59,60,61,62,63);
foreach ($gFlaggedBuildingTypes as $flag => $arr) foreach ($arr as $typeid) $gBuildingType[$typeid]->flags = intval($gBuildingType[$typeid]->flags) | $flag;

// a list of buildingtype ids with certain flags set
foreach ($gBuildingTypeFlagNames as $flag => $name) {
	if (!isset($gFlaggedBuildingTypes[$flag])) $gFlaggedBuildingTypes[$flag] = array();
	foreach ($gBuildingType as $o)
		if (intval($o->flags) & intval($flag))
			$gFlaggedBuildingTypes[$flag][] = $o->id;
}

// todo : replaceme by $gFlaggedBuildingTypes
$gBuildDistanceSources = $gFlaggedBuildingTypes[kBuildingTypeFlag_BuildDistSource];
$gSpeedyBuildingTypes = $gFlaggedBuildingTypes[kBuildingTypeFlag_Speedy];
$gOpenableBuildingTypes = $gFlaggedBuildingTypes[kBuildingTypeFlag_Openable];
$gTaxableBuildingTypes = $gFlaggedBuildingTypes[kBuildingTypeFlag_Taxable];
$gBodenSchatzBuildings = $gFlaggedBuildingTypes[kBuildingTypeFlag_Bodenschatz];

define("kBodenSchatz_Weizen",55); // todo : unhardcode us : kBuildingTypeFlag_Bodenschatz
define("kBodenSchatz_Kristalle",56);
define("kBodenSchatz_Erz",57);
define("kBodenSchatz_Fisch",58);
define("kBodenSchatz_Fruechte",59);
define("kBodenSchatz_EichenHolz",60);
define("kBodenSchatz_Marmor",61);
define("kBodenSchatz_Granit",62);
define("kBodenSchatz_Wild",63);

define("kBodenSchatzIdealWorkers",10000); // maximum workers that are of use when harvesting "minerals"
define("kShootingAlarmTimeout",2*3600); // send a new igm when fire is resumed after a longer pause
define("kSpeedyBuildingsLimit",121*2); // 11*11 = 1 map full

$gBuildingTypeGroupsPics = array("Gebäude"=>"tool_house.png","Infrastruktur"=>"tool_street.png","Deko"=>"tool_brunnen.png"); 
$gBuildingTypeGroups = array( // used by for mapnavi tabs
	"Gebäude" => array(0=>kBuilding_House,-1), // -1 is replaced by the whole rest
	/*"Produktion" => array(0=>	kBuilding_Farm,
								kBuilding_Lumberjack,
								kBuilding_StoneProd,
								kBuilding_IronMine),*/
	"Infrastruktur" => array(0=>kBuilding_Path,
								kBuilding_Wall,
								kBuilding_Gate,
								kBuilding_Bridge,
								kBuilding_GB,
								kBuilding_Portal,
								kBuilding_Platz,
								kBuilding_Sign,
								kBuilding_SeaWall,
								kBuilding_SeaGate,
								kBuilding_Steg),
	"Deko" => array(0=>			kBuilding_Brunnen,
								kBuilding_Chessboard,
								kBuilding_Tavern,
								kBuilding_Teehaus,
								kBuilding_Gaertner,
								kBuilding_Spielhalle,
								kBuilding_Leuchtturm,
								kBuilding_Galgen,
								kBuilding_Observatorium),
);

define("kRuinStartHp",1);
define("kActionCmd_Build",1); // einheitenproduktion

define("kBuildingFlag_Open_Stranger",	(1<<0));
define("kBuildingFlag_Open_Guild",		(1<<1));
define("kBuildingFlag_Open_Friend",		(1<<2));
define("kBuildingFlag_Open_Enemy",		(1<<3));
define("kBuildingFlag_Tax_Stranger",	(1<<4));
define("kBuildingFlag_Tax_Guild",		(1<<5));
define("kBuildingFlag_Tax_Friend",		(1<<6));
define("kBuildingFlag_Tax_Enemy",		(1<<7));
define("kBuildingFlag_AutoShoot_Enemy",		(1<<8));
define("kBuildingFlag_AutoShoot_Strangers",	(1<<9));
define("kBuildingFlag_AllSet",			(1<<11)-1); // (at least one above the others)-1
define("kBuildingFlag_OpenMask",	kBuildingFlag_Open_Stranger|
									kBuildingFlag_Open_Guild|
									kBuildingFlag_Open_Friend|
									kBuildingFlag_Open_Enemy);
define("kBuildingFlag_TaxMask",		kBuildingFlag_Tax_Stranger|
									kBuildingFlag_Tax_Guild|
									kBuildingFlag_Tax_Friend|
									kBuildingFlag_Tax_Enemy);
define("kBuildingFlag_OpenTaxMask",	kBuildingFlag_OpenMask|kBuildingFlag_TaxMask);
define("kBuildingFlag_ShootMask",	kBuildingFlag_AutoShoot_Enemy|kBuildingFlag_AutoShoot_Strangers);



// owner can set these flags, all others are system flags
$shootertypelist = array_merge($gFlaggedBuildingTypes[kBuildingTypeFlag_CanShootArmy],$gFlaggedBuildingTypes[kBuildingTypeFlag_CanShootBuilding]);
$gOwnerBuildingFlags = array(	kBuildingFlag_Open_Stranger			=> $gFlaggedBuildingTypes[kBuildingTypeFlag_Openable], 
								kBuildingFlag_Open_Guild			=> $gFlaggedBuildingTypes[kBuildingTypeFlag_Openable],
								kBuildingFlag_Open_Friend			=> $gFlaggedBuildingTypes[kBuildingTypeFlag_Openable],
								kBuildingFlag_Open_Enemy			=> $gFlaggedBuildingTypes[kBuildingTypeFlag_Openable],
								kBuildingFlag_Tax_Stranger			=> $gFlaggedBuildingTypes[kBuildingTypeFlag_Taxable],
								kBuildingFlag_Tax_Guild				=> $gFlaggedBuildingTypes[kBuildingTypeFlag_Taxable],
								kBuildingFlag_Tax_Friend			=> $gFlaggedBuildingTypes[kBuildingTypeFlag_Taxable],
								kBuildingFlag_Tax_Enemy				=> $gFlaggedBuildingTypes[kBuildingTypeFlag_Taxable],
								kBuildingFlag_AutoShoot_Enemy		=> $shootertypelist,
								kBuildingFlag_AutoShoot_Strangers	=> $shootertypelist,
								);
								
$gBuildingFlagNames = array(	kBuildingFlag_AutoShoot_Enemy => "Automatisch auf Feinde schiessen",
								kBuildingFlag_AutoShoot_Strangers => "Automatisch auf Fremde schiessen",
								);


$gMenuStyles = array(
"classicbig" => "css/classicbig.css",
"paperbig" => "css/paperbig.css",
"darkbig" => "css/darkbig.css",
"cleanbig" => "css/cleanbig.css",
"brownbig" => "css/brownbig.css",
"whitebig" => "css/whitebig.css",
"zw6big" => "css/zw6big.css",
"default" => "css/classicbig.css",
);

?>
