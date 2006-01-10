<?php
require_once("../lib.main.php");

// included only if user is admin or terraformer, saves a lot of parsing for normal users

if (!isset($f_building) && !isset($f_army) && isset($f_do)) switch ($f_do) {
		case "admin_maptemplate": 
			if (!$gUser->admin) break;
			if (isset($f_del)) sql("DELETE FROM `maptemplate` WHERE `id` = ".intval($f_maptemplate));
			if (isset($f_del)) echo("DELETE FROM `maptemplate` WHERE `id` = ".intval($f_maptemplate));
			if (isset($f_use)) {
				$template = sqlgetobject("SELECT * FROM `maptemplate` WHERE `id` = ".intval($f_maptemplate));
				$x = intval($f_x) - 5;
				$y = intval($f_y) - 5;
				$template->terrain = explode2("#","|",$template->terrain);
				foreach ($template->terrain as $o) 
					sql("REPLACE INTO `terrain` SET ".arr2sql(array("x"=>$o[0]+$x,"y"=>$o[1]+$y,"type"=>$o[2])));
				$template->building = explode2("#","|",$template->building);
				foreach ($template->building as $o) {
					sql("DELETE FROM `building` WHERE `x` = ".intval($o[0]+$x)." AND `y` = ".intval($o[1]+$y)." LIMIT 1");
					sql("INSERT INTO `building` SET ".arr2sql(array("x"=>$o[0]+$x,"y"=>$o[1]+$y,"type"=>$o[2],"level"=>$o[3],"hp"=>$o[4],"mana"=>$o[5])));
					$id = mysql_insert_id();
					if ($o[6] != "") SetBParam($id,"text",$o[6]);
				}
				$template->army = explode2("#","|",$template->army);
				foreach ($template->army as $o) 
					cArmy::SpawnArmy($o[0]+$x,$o[1]+$y,cUnit::Simple($o[2],$o[3]),$o[4],-1,0,0,0,true,$o[5]);
				$template->item = explode2("#","|",$template->item);
				foreach ($template->item as $o) 
					cItem::SpawnItem($o[0]+$x,$o[1]+$y,$o[2],$o[3]);
				$template->hellhole = explode2("#","|",$template->hellhole);
				foreach ($template->hellhole as $o) 
					sql("REPLACE INTO `hellhole` SET ".arr2sql(array("x"=>$o[0]+$x,"y"=>$o[1]+$y,"type"=>$o[2],"type2"=>$o[3],
						"ai_type"=>$o[4],"armysize"=>$o[5],"armysize2"=>$o[6],"num"=>$o[7],"spawndelay"=>$o[8],"radius"=>$o[9])));
				
				require_once("lib.map.php");
				RegenAreaNWSE($x-1,$y-1,$x+2+$template->cx,$y+2+$template->cy,false);
				$gJSCommands[] = "parent.map.location.href = parent.map.location.href;";
			}
			if (isset($f_new)) {
				$x1 = intval($f_x1);$y1 = intval($f_y1);
				$x2 = intval($f_x2);$y2 = intval($f_y2);
				$template = false;
				$template->name = $f_name;
				$template->cx = $x2 - $x1 + 1;
				$template->cy = $y2 - $y1 + 1;
				$cond = "`x` >= $x1 AND `x` <= $x2 AND `y` >= $y1 AND `y` <= $y2";
				
				$template->terrain = array();
				$terrain = sqlgettable("SELECT * FROM `terrain` WHERE $cond");
				foreach ($terrain as $o) if ($o->type != kTerrain_Grass) $template->terrain[] = implode("|",array($o->x-$x1,$o->y-$y1,$o->type));
				$template->terrain = implode("#",$template->terrain);
				
				$template->building = array();
				$buildings = sqlgettable("SELECT * FROM `building` WHERE $cond");
				foreach ($buildings as $o) {
					$text = strtr(GetBParam($o->id,"text"),array("#"=>"_","|"=>"_")); // sign text
					$template->building[] = implode("|",array($o->x-$x1,$o->y-$y1,$o->type,$o->level,$o->hp,$o->mana,$text));
				}
				$template->building = implode("#",$template->building);
				
				$template->army = array();
				$armies = sqlgettable("SELECT * FROM `army` WHERE $cond AND `user` = 0");
				foreach ($armies as $o) {
					$units = cUnit::GetUnits($o->id);
					$unittype = cUnit::GetUnitsMaxType($units);
					$amount = cUnit::GetUnitsSum(cUnit::FilterUnitsType($units,$unittype));
					$template->army[] = implode("|",array($o->x-$x1,$o->y-$y1,$unittype,$amount,strtr($o->name,array("#"=>"_","|"=>"_")),$o->flags));
				}
				$template->army = implode("#",$template->army);
				
				$template->item = array();
				$items = sqlgettable("SELECT * FROM `item` WHERE $cond AND `army` = 0 AND `building` = 0");
				foreach ($items as $o) $template->item[] = implode("|",array($o->x-$x1,$o->y-$y1,$o->type,$o->amount));
				$template->item = implode("#",$template->item);
				
				$template->hellhole = array();
				$hellholes = sqlgettable("SELECT * FROM `hellhole` WHERE $cond");
				foreach ($hellholes as $o) $template->hellhole[] = implode("|",array($o->x-$x1,$o->y-$y1,$o->type,$o->type2,$o->ai_type,$o->armysize,$o->armysize2,$o->num,$o->spawndelay,$o->radius));
				$template->hellhole = implode("#",$template->hellhole);
				
				sql("INSERT INTO `maptemplate` SET ".obj2sql($template));
			}
		break;
		case "completeconstruction": // finish construction
			if (!$gUser->admin)break;
			sql("UPDATE `building` SET `construction`=".time()." WHERE `id`=".intval($f_id));
			JSRefreshCell($f_x,$f_y,true);
		break;
		case "adminsql":// admin command
			if (!kAdminCanAccessMysql) break;
			if (!$gUser->admin)break;
			$gAdminSQLResult = sqlgettable($f_sqlcommand); 
		break;
		case "genriver":// admin command
			if (!$gUser->admin)break;
			require_once("../lib.map.php");
			generateRiver(intval($f_x),intval($f_y),intval($f_steps));
			$gJSCommands[] = "parent.map.location.href = parent.map.location.href;";
		break;
		case "terraingen":// admin command
			if (!$gUser->admin)break;
			require_once("../lib.map.php");
			terraingen($f_x,$f_y,$f_type,$f_dur,$f_ang,1.0,($f_type==kTerrain_River)?true:false,$f_split);
			$gJSCommands[] = "parent.map.location.href = parent.map.location.href;";
		break;
		case "gotobuildingid":// admin command
			if (!$gUser->admin)break;
			$building = sqlgetobject("SELECT * FROM `building` WHERE `id` = ".intval($f_id));
			if ($building) {
				$f_x = $building->x;
				$f_y = $building->y;
			}
		break;
		case "gotoarmyid":// admin command
			if (!$gUser->admin)break;
			$army = sqlgetobject("SELECT * FROM `army` WHERE `id` = ".intval($f_id));
			if ($army) {
				$f_x = $army->x;
				$f_y = $army->y;
			}
		break;
		case "adminsetitem":// admin command
			if (!$gUser->admin) break;
			if (!empty($f_x) && !empty($f_y) && !empty($f_type))
				cItem::SpawnItem($f_x,$f_y,$f_type,$f_anzahl,isset($f_quest)?$f_quest:0);
			JSRefreshCell($f_x,$f_y);
		break;
		case "adminsetarmy":// admin command
			if (!$gUser->admin)break;
			require_once("../lib.army.php");
			$units = cUnit::Simple($f_unit,$f_anzahl);
			$newarmy = cArmy::SpawnArmy($f_x,$f_y,$units,false,-1,$f_user,$f_quest,0,-1);
			JSRefreshArmy($newarmy);
		break;
		case "adminzap":// admin command
			if (!$gUser->admin)break;
			require_once("../lib.broid.php");
			$cssclassarr = zap($f_x,$f_y);
			JSRefreshCell($f_x,$f_y,true);
		break;
		case "adminruin":// admin command (geb&auml;ude in ruine verwandeln)
			if (!$gUser->admin)break;
			$building = sqlgetobject("SELECT * FROM `building` WHERE `x`=".intval($f_x)." AND `y`=".intval($f_y));
			if ($building) { 
				echo "adminruin ".$building->type." -> ".$gBuildingType[$building->type]->ruinbtype;
				if ($gBuildingType[$building->type]->ruinbtype > 0) {
					echo " ruine";
					// ruine
					$ruin = false;
					$ruin->type = $gBuildingType[$building->type]->ruinbtype;
					$ruin->level = $building->level;
					$ruin->x = $building->x;
					$ruin->y = $building->y;
					$ruin->hp = 1;
					sql("DELETE FROM `building` WHERE `id` = ".$building->id." LIMIT 1");
					sql("INSERT INTO `building` SET ".obj2sql($ruin));
				} else {
					echo " schutt $f_x $f_y";
					// schutt
					$schutt = false;
					$schutt->type = kTerrain_Rubble;
					$schutt->x = intval($f_x);
					$schutt->y = intval($f_y);
					sql("DELETE FROM `building` WHERE `id` = ".$building->id." LIMIT 1");
					sql("DELETE FROM `terrain` WHERE `x` = ".$schutt->x." AND `y` = ".$schutt->y);
					sql("INSERT INTO `terrain` SET ".obj2sql($schutt));
				}
				$cssclassarr = RegenSurroundingNWSE($f_x,$f_y,true);
				JSRefreshCell($f_x,$f_y,true);
			}
		break;
		case "adminremoveitems":// admin command
			if (!$gUser->admin)break;
			sql("DELETE FROM `item` WHERE `x`=".intval($f_x)." AND `y`=".intval($f_y));
			JSRefreshCell($f_x,$f_y);
		break;
		case "adminclear":// admin command
			if (!$gUser->admin) break;
			require_once("../lib.broid.php");
			zap($f_x,$f_y,true);
			sql("DELETE FROM `terrain` WHERE `x`=".intval($f_x)." AND `y`=".intval($f_y));
			$cssclassarr = RegenSurroundingNWSE($f_x,$f_y,true);
			JSRefreshCell($f_x,$f_y,true);
		break;
		case "adminremovearmy":// admin command
			if (!$gUser->admin) break;
			$army = sqlgetobject("SELECT * FROM `army` WHERE `x`=".intval($f_x)." AND `y`=".intval($f_y));
			cArmy::DeleteArmy($army);
			JSRefreshCell($f_x,$f_y);
		break;
		case "adminteleportarmy":// admin command (geb&auml;ude in ruine verwandeln)
			if (!$gUser->admin)break;
			$tarmies = sqlgettable("SELECT * FROM `army` WHERE `x`=".intval($f_x)." AND `y`=".intval($f_y));
			sql("UPDATE `army` SET `x` = ".intval($f_dx)." , `y` = ".intval($f_dy)." WHERE `x`=".intval($f_x)." AND `y`=".intval($f_y));
			foreach ($tarmies as $tarmy) {
				$tarmy->x = intval($f_dx);
				$tarmy->y = intval($f_dy);
				QuestTrigger_TeleportArmy($tarmy,false,$f_dx,$f_dy);
			}
			JSRefreshCell($f_x,$f_y);
			JSRefreshCell($f_dx,$f_dy);
		break;
		case "admineditbuilding":// admin command (geb&auml;ude in ruine verwandeln)
			if (!$gUser->admin)break;
			if (isset($f_adminuserfullres)) {
				$arr = array();
				foreach ($gRes as $f=>$n) $arr[] = "`$n` = `max_$n`";
				$arr[] = "`pop` = `maxpop`";
				$userid = sqlgetone("SELECT `user` FROM `building` WHERE `id` = ".intval($f_id));
				sql("UPDATE `user` SET ".implode(" , ",$arr)." WHERE `id` = ".intval($userid));
				$gUser = sqlgetobject("SELECT * FROM `user` WHERE `id` = ".$gUser->id);
			} else {
				$o = sqlglobals();
				sql("UPDATE `building` SET ".obj2sql($o)." WHERE `id` = ".intval($f_id));
			}
		break;
		case "adminsetterrain":// admin command
			if (!($gUser->admin || (intval($gUser->flags) & kUserFlags_TerraFormer)))break;
			if ($f_terrain != 0) {
				require_once("../lib.map.php");
				$brushrad = min(20,max(0,intval($f_brushrad)));
				echo "brushrad ".$brushrad;
				$myterrain = false;
				$myterrain->type = intval($f_terrain);
				
				$f_x = intval($f_x);$f_y = intval($f_y);
				$minx = ($f_x);$miny = ($f_y);
				$maxx = ($f_x);$maxy = ($f_y);
				$endx = isset($f_linex)?intval($f_linex):intval($f_x);
				$endy = isset($f_liney)?intval($f_liney):intval($f_y);
				$startx = $f_x;
				$starty = $f_y;
				
				$patch = array();
				do {
					$minx = min($minx,$f_x-$brushrad-1);$miny = min($miny,$f_y-$brushrad-1);
					$maxx = max($maxx,$f_x+$brushrad+1);$maxy = max($maxy,$f_y+$brushrad+1);
					if(!$gUser->admin){
						//nur terraformer kein admin, daher darf er nur un unbesiedeltem gebiet bauen
						$countb = sqlgetone("SELECT COUNT(*) FROM `building` WHERE 
							`x`>=(".($minx-kTerraFormer_SicherheitsAbstand).") AND 
							`x`<=(".($maxx+kTerraFormer_SicherheitsAbstand).") AND
							`y`>=(".($miny-kTerraFormer_SicherheitsAbstand).") AND
							`y`<=(".($maxy+kTerraFormer_SicherheitsAbstand).") AND `user`>0");
						$counta = sqlgetone("SELECT COUNT(*) FROM `army` WHERE 
							`x`>=(".($minx-kTerraFormer_SicherheitsAbstand).") AND 
							`x`<=(".($maxx+kTerraFormer_SicherheitsAbstand).") AND
							`y`>=(".($miny-kTerraFormer_SicherheitsAbstand).") AND
							`y`<=(".($maxy+kTerraFormer_SicherheitsAbstand).") AND `user`>0");
						//echo "[b=$countb a=$counta]";
						 if($countb>0 || $counta>0)break;
					}
					for ($x=-$brushrad;$x<=$brushrad;$x++)
					for ($y=-$brushrad;$y<=$brushrad;$y++) {
						if (sqrt($x*$x+$y*$y) > $brushrad + 0.5) continue;
						$myterrain->x = intval($f_x)+$x;
						$myterrain->y = intval($f_y)+$y;
						$myterrain->creator = $gUser->id;
						sql("DELETE FROM `terrain` WHERE `x` = ".$myterrain->x." AND `y` = ".$myterrain->y);
						//sql("DELETE FROM `building` WHERE `x` = ".$myterrain->x." AND `y` = ".$myterrain->y);
						if (sqlgetone("SELECT 1 FROM `building` WHERE `x` = ".$myterrain->x." AND `y` = ".$myterrain->y." LIMIT 1"))
							continue;
						sql("INSERT INTO `terrain` SET ".obj2sql($myterrain));
						$patch[] = "t,".$myterrain->x.",".$myterrain->y.",".$myterrain->type;
						JSRefreshCell($myterrain->x,$myterrain->y,true); // TODO : replace by js brush
					}
					if ($f_x == $endx && $f_y == $endy) break;
					else list($f_x,$f_y) = GetNextStep($f_x,$f_y,$startx,$starty,$endx,$endy);
				} while (true) ;
				
				$cssclassarr = RegenAreaNWSE($minx,$miny,$maxx,$maxy,true);
				// TODO : implement brush,lines...
				// parent.map.JSTerrainBrush(intval($f_x),intval($f_y),intval($f_terrain),$brushrad);
			}
		break;
		case "adminsetbuilding":// admin command
			if (!$gUser->admin)break;
			require_once("../lib.map.php");
			sql("DELETE FROM `building` WHERE `x` = ".intval($f_x)." AND `y` = ".intval($f_y));
			if ($f_btype != 0)
			{
				$btype = $gBuildingType[intval($f_btype)];
				$mybuilding = false;
				$mybuilding->user = intval($f_buser);
				$mybuilding->type = intval($f_btype);
				$mybuilding->level = intval($f_blevel);
				$mybuilding->hp = cBuilding::calcMaxBuildingHp($mybuilding->type,$mybuilding->level);
				if($mybuilding->type==kBuilding_Bridge || $mybuilding->type==kBuilding_GB)
					$mybuilding->param=getBridgeParam($mybuilding->x,$mybuilding->y);
					
				$f_x = intval($f_x);$f_y = intval($f_y);
				$minx = ($f_x);$miny = ($f_y);
				$maxx = ($f_x);$maxy = ($f_y);
				$endx = isset($f_linex)?intval($f_linex):intval($f_x);
				$endy = isset($f_liney)?intval($f_liney):intval($f_y);
				$startx = $f_x;
				$starty = $f_y;
				
				$patch = array();
				do {
					$minx = min($minx,$f_x-1);$miny = min($miny,$f_y-1);
					$maxx = max($maxx,$f_x+1);$maxy = max($maxy,$f_y+1);
					$mybuilding->x = intval($f_x);
					$mybuilding->y = intval($f_y);
					if ($mybuilding->type == kBuilding_GB || $mybuilding->type == kBuilding_Bridge)
					{
						sql("DELETE FROM `terrain` WHERE `x` = ".$mybuilding->x." AND `y` = ".$mybuilding->y);
						sql("INSERT INTO `terrain` SET `type` = ".kTerrain_River.", `x` = ".$mybuilding->x.", `y` = ".$mybuilding->y);
						// TODO : unhardcode me !!!!
					}
					sql("DELETE FROM `building` WHERE `x` = ".$mybuilding->x." AND `y` = ".$mybuilding->y);
					sql("INSERT INTO `building` SET ".obj2sql($mybuilding));
					$patch[] = "b,".$mybuilding->x.",".$mybuilding->y.",".$mybuilding->user.",".$mybuilding->type.",".$mybuilding->level;
					JSRefreshCell($f_x,$f_y,true); // TODO : replace by js brush/line ?
					if ($f_x == $endx && $f_y == $endy) break;
					else list($f_x,$f_y) = GetNextStep($f_x,$f_y,$startx,$starty,$endx,$endy);
				} while (true) ;
				
				$cssclassarr = RegenAreaNWSE($minx,$miny,$maxx,$maxy,true);
				// parent.navi.addpatch("implode("|",$patch)|");
				// parent.map.JSSetBuilding(intval($f_x),intval($f_y),intval($f_btype),$brushrad);
			}
		break;
		case "hellhole_admin_think": if (!$gUser->admin) break;
			require_once("lib.hellholes.php");
			$o = sqlgetobject("SELECT * FROM `hellhole` WHERE `id` = ".intval($f_hellhole));
			$hellhole = GetHellholeInstance($o);
			$hellhole->Think();
			global $f_x,$f_y;
			$f_x = $hellhole->x;
			$f_y = $hellhole->y;
			require_once("lib.armythink.php");
			$f_minutes = 60;
			echo "$f_minutes minutes have passed....<br>";
			$monsters = sqlgettable("SELECT * FROM `army` WHERE `hellhole` = ".intval($f_hellhole));
			foreach ($monsters as $army) {
				ArmyThinkTimeShift($army->id,$f_minutes*60);
				$army = sqlgetobject("SELECT * FROM `army` WHERE `id` = ".intval($army->id));
				ArmyThink($army,true);
				$f_steps = $f_minutes;
				for ($i=0;$i<$f_steps;++$i) {
					$fights = sqlgettable("SELECT * FROM `fight` WHERE `attacker` = ".$army->id." OR `defender` = ".$army->id);
					$sieges = sqlgettable("SELECT * FROM `siege` WHERE `army` = ".$army->id);
					$pillages = sqlgettable("SELECT * FROM `pillage` WHERE `army` = ".$army->id);
					foreach ($fights as $o) cFight::FightStep($o);
					foreach ($sieges as $o) cFight::SiegeStep($o,true);
					foreach ($pillages as $o) cFight::PillageStep($o,true);
				}
				echo "<hr>";
			}
			$gJSCommands[] = "parent.map.location.href = parent.map.location.href;";
		break;
		case "hellhole_admin_create": if (!$gUser->admin) break;
			$hellhole->x = intval($f_x);
			$hellhole->y = intval($f_y);
			$hellhole->type = 0;
			$hellhole->num = 5; // no more than 5 monsters
			$hellhole->armysize = 50; // how many monsters per army
			$hellhole->spawndelay = 60*60*2; // 1 monster every 2 hours
			$hellhole->spawntime = time()+120; 
			$hellhole->lastupgrade = time(); 
			$hellhole->totalspawns = 0; 
			$hellhole->radius = 8; // monsters move within this radius
			sql("INSERT INTO `hellhole` SET ".obj2sql($hellhole));
		break;
		default :
			echo "infocommand : unknown non-building command $f_do<br>";
		break;
}