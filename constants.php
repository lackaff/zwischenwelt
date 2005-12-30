<?php

define("kURL_Nethack","http://zwischenwelt.org/telnet.html");
define("kURL_Chess","http://zwischenwelt.org/~hagish/occ/");
define("kURL_Wiki","http://zwischenwelt.milchkind.net/zwwiki/index.php/");

define("kMapNaviGotoCat_Pos",1);
define("kMapNaviGotoCat_Mark",2);
define("kMapNaviGotoCat_Own",3);
define("kMapNaviGotoCat_Guild",4);
define("kMapNaviGotoCat_Friends",5);
define("kMapNaviGotoCat_Enemies",6);
define("kMapNaviGotoCat_Search",7);
define("kMapNaviGotoCat_Random",8);
define("kMapNaviGotoCat_Hellhole",9);
$gMapNaviGotoCatNames = array(
	kMapNaviGotoCat_Po			=> "Pos",
	kMapNaviGotoCat_Mark		=> "MapMark",
	kMapNaviGotoCat_Own			=> "Eigene",
	kMapNaviGotoCat_Guild		=> "Gilde",
	kMapNaviGotoCat_Friends		=> "Freunde",
	kMapNaviGotoCat_Enemies		=> "Feinde",
	kMapNaviGotoCat_Search		=> "Suche",
	kMapNaviGotoCat_Random		=> "Zufall",
	kMapNaviGotoCat_Hellhole	=> "Hellhole",
	);
$gMapNaviGotoCat_AdminOnly = array(0=>kMapNaviGotoCat_Hellhole);

define("kJSMapBuildingFlag_Open",(1<<0));
define("kJSMapBuildingFlag_Tax",(1<<1)); // todo
define("kJSMapBuildingFlag_Locked",(1<<2)); // (portal cannot be used) todo 

define("kJSMapArmyFlag_Moving_N",(1<<0)); // todo
define("kJSMapArmyFlag_Moving_W",(1<<1)); // todo
define("kJSMapArmyFlag_Moving_S",(1<<2)); // todo
define("kJSMapArmyFlag_Moving_E",(1<<3)); // todo
define("kJSMapArmyFlag_Fighting_N",(1<<4)); // todo
define("kJSMapArmyFlag_Fighting_W",(1<<5)); // todo
define("kJSMapArmyFlag_Fighting_S",(1<<6)); // todo
define("kJSMapArmyFlag_Fighting_E",(1<<7)); // todo
define("kJSMapArmyFlag_Shooting",(1<<8)); // todo

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
$gMovableFlagMainTerrain = array(
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

define("kUserFlags_TerraFormer",1);
define("kUserFlags_DropDownMenu",2);
define("kUserFlags_BugOperator",4);
define("kUserFlags_ShowLogFrame",8);
define("kUserFlags_DontShowWikiHelp",16);
define("kUserFlags_NoMonsterFightReport",32);

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

define("MTARGET_SELF",1);
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
define("kArmyFlag_AllSet",					(1<<30)-1); // (at least one above the others)-1
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
	kArmyFlag_BuildingWait => 			"wait for wp bevore next auto-building action", // stops kArmyFlag_AutoPillage,kArmyFlag_AutoDeposit
	kArmyFlag_AutoGive_Own => 			"Eigene Armeen/Karawanen automatisch beladen",
	kArmyFlag_AutoGive_Guild => 		"Gilden-Armeen/Karawanen automatisch beladen",
	kArmyFlag_AutoGive_Friend => 		"Freundliche Armeen/Karawanen automatisch beladen",
	);
	
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
define("ARMY_ACTION_RANGEATTACK",7);

// units

define("kUnitContainer_Army","army");
define("kUnitContainer_Transport","transport");
define("kUnitContainer_Building","building");

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

define("kResItemType_lumber","44");
define("kResItemType_stone","45");
define("kResItemType_food","46");
define("kResItemType_metal","47");
define("kResItemType_runes","48");

// quests

define("kQuestFlag_Delete_QuestItems_On_Finish",	(1<<0));
define("kQuestFlag_Delete_QuestArmy_On_Finish",		(1<<1));
define("kQuestFlag_Permanent",						(1<<2));
define("kQuestFlag_RepeatOnFinish",					(1<<3));

// buildings

define("kBuildingRequirenment_NearRadius",2);
define("kHQ_Upgrade_BaseTime",43200);

define("kBuilding_HQ",1);
define("kBuilding_MagicTower",2);
define("kBuilding_Path",3);
define("kBuilding_BROID",4);
define("kBuilding_Wall",5);
define("kBuilding_Silo",7); // lager
define("kBuilding_Baracks",8);
define("kBuilding_Farm",9);
define("kBuilding_Hospital",10);
define("kBuilding_Garage",11); // werkstatt
define("kBuilding_Smith",12);
define("kBuilding_Market",16);
define("kBuilding_Gate",17);
define("kBuilding_Bridge",18);
define("kBuilding_Sign",19);
define("kBuilding_Temple",20);
define("kBuilding_Hellhole",21);
define("kBuilding_Chessboard",22);
define("kBuilding_Portal",23);
define("kBuilding_GB",24); // gatebridge
define("kBuilding_Tavern",51);
define("kBuilding_SeaWall",49);
define("kBuilding_Werft",46);
define("kBuilding_Harbor",47);
define("kBuilding_SeaGate",50);
define("kBuilding_Steg",48);

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
define("kBuildingFlag_AllSet",			(1<<9)-1); // (at least one above the others)-1
define("kBuildingFlag_OpenMask",	kBuildingFlag_Open_Stranger|
									kBuildingFlag_Open_Guild|
									kBuildingFlag_Open_Friend|
									kBuildingFlag_Open_Enemy);
define("kBuildingFlag_TaxMask",		kBuildingFlag_Tax_Stranger|
									kBuildingFlag_Tax_Guild|
									kBuildingFlag_Tax_Friend|
									kBuildingFlag_Tax_Enemy);
define("kBuildingFlag_OpenTaxMask",	kBuildingFlag_OpenMask|kBuildingFlag_TaxMask);


define("kBodenSchatzIdealWorkers",10000);
define("kBodenSchatz_Weizen",55);
define("kBodenSchatz_Kristalle",56);
define("kBodenSchatz_Erz",57);
define("kBodenSchatz_Fisch",58);
define("kBodenSchatz_Fruechte",59);
define("kBodenSchatz_EichenHolz",60);
define("kBodenSchatz_Marmor",61);
define("kBodenSchatz_Granit",62);
define("kBodenSchatz_Wild",63);
$gBodenSchatzBuildings = array(55,56,57,58,59,60,61,62,63); // TODO : unhardcode..

$gOpenableBuildingTypes = array(0=>kBuilding_Portal,kBuilding_GB,kBuilding_Gate,kBuilding_SeaGate);
$gTaxableBuildingTypes = array(0=>kBuilding_Portal);

// owner can set these flags, all others are system flags
$gOwnerBuildingFlags = array(kBuildingFlag_Open_Stranger	=> $gOpenableBuildingTypes, 
								kBuildingFlag_Open_Guild	=> $gOpenableBuildingTypes,
								kBuildingFlag_Open_Friend	=> $gOpenableBuildingTypes,
								kBuildingFlag_Open_Enemy	=> $gOpenableBuildingTypes,
								kBuildingFlag_Tax_Stranger	=> $gTaxableBuildingTypes,
								kBuildingFlag_Tax_Guild		=> $gTaxableBuildingTypes,
								kBuildingFlag_Tax_Friend	=> $gTaxableBuildingTypes,
								kBuildingFlag_Tax_Enemy		=> $gTaxableBuildingTypes);
								
$gBuildingFlagNames = array(	); // for further flags

?>
