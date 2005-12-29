<?php
define("kMiniMapCacheTimeout",60*60*24);

require_once("lib.map.php");

function GetFOFminimap	($masteruser,$otheruser) { 
	$fof = GetFOF($masteruser->id,$otheruser->id);
	if ($masteruser->id == $otheruser->id || $fof == kFOF_Friend) return "minimap_friend.gif";
	if (IsInSameGuild($masteruser,$otheruser)) return "minimap_guild.gif";
	if ($fof == kFOF_Enemy) return "minimap_enemy.gif"; 
	return "minimap_neutral.gif"; 
}
function GetMiniMapFile ($mode,$time) {
	switch($mode) {
		case "guild":	return "tmp/pngmap-guild_$time.png";
		case "creep":	return "tmp/pngmapcreep_$time.png";
		case "wp":		return "tmp/pngmap_$time.png";
		case "wpout":	return "tmp/wpout_$time.png";
		case "test":		return "tmp/testminimap.png";
		default:		return "tmp/pngmap_$time.png";
	}
}
function GetMiniMapGlobal($mode) {
	switch($mode) {
		case "guild":	return "lastpngmap-guild";
		case "creep":	return "lastpngmapcreep";
		case "wp":		return "lastpngmap";
		case "test":		return "testminimap";
		default:		return "lastpngmap";
	}
}
function GetMiniMapLastTime($mode) {
	if ($mode == "test") return time()-25*3600;
	global $gGlobal;
	$global = GetMiniMapGlobal($mode);
	return isset($gGlobal[$global])?$gGlobal[$global]:0;
}

function hex2imgcolor($img,$hex) {
	switch($hex) {
		case "red":		return ImageColorAllocate($img,255,0,0);
		case "green":	return ImageColorAllocate($img,0,255,0);
		case "blue":	return ImageColorAllocate($img,0,0,255);
		case "black":	return ImageColorAllocate($img,0,0,0);
		case "white":	return ImageColorAllocate($img,255,255,255);
		case "yellow":	return ImageColorAllocate($img,255,255,0);
		case "gray":	return ImageColorAllocate($img,204,204,204);
		default:return ImageColorAllocate($img,hexdec(substr($hex,1,2)),hexdec(substr($hex,3,2)),hexdec(substr($hex,5,2)));
	}
}


function renderMinimap_walkgetbodenschatzpic (&$item,$key) {
	global $gBodenSchatzBuildings;
	if (in_array($item->id,$gBodenSchatzBuildings)) {
		$item->pic = imagecreatefrompng("gfx/".$item->gfx);
	}
}
function renderMinimap_walkgetmonsterpic (&$item,$key) {
	if (intval($item->flags) & kUnitFlag_Monster) {
		$item->pic = imagecreatefrompng("gfx/".$item->gfx);
	}
}
function renderMinimap_walkhex2imgcolor (&$item,$key,$im) {
	$item->imgcolor = hex2imgcolor($im,$item->color);
}

//renders a part of the minimap from top to almost bottom
//segment*segment terrainfields a read in one step
function renderMinimap($top,$left,$bottom,$right,$filename,$mode="normal",$segment=128){
	//echo "renderMinimap($top,$left,$bottom,$right,$filename,$mode,$segment)<br>";

	global $gTerrainType,$gBuildingType,$gAllGuilds,$gAllUsers,$gBodenSchatzBuildings,$gUnitType;
	$dx = abs($right-$left);
	$dy = abs($bottom-$top);
	$sx = ceil($dx / $segment);
	$sy = ceil($dy / $segment);
	
	$im = imagecreatetruecolor($dx, $dy) or die("Cannot Initialize new GD image stream");
	
	$gAllUsers = sqlgettable("SELECT `id`,`color`,`guild` FROM `user`","id");
	$gAllGuilds = sqlgettable("SELECT `id`,`color` FROM `guild`","id");
	
	array_walk($gTerrainType,"renderMinimap_walkhex2imgcolor",$im);
	array_walk($gBuildingType,"renderMinimap_walkhex2imgcolor",$im);
	array_walk($gAllGuilds,"renderMinimap_walkhex2imgcolor",$im);
	array_walk($gAllUsers,"renderMinimap_walkhex2imgcolor",$im);
	
	$background_color = $gTerrainType[kTerrain_Grass]->imgcolor;
	$color_nonplayer = hex2imgcolor($im,"#888888");
	$color_no_guild = hex2imgcolor($im,"#00FF00");
	$color_red = hex2imgcolor($im,"#FF0000");
	$color_hellhole = hex2imgcolor($im,"#FFFF00");
	$color_portal = hex2imgcolor($im,"#88FFFF");
	$color_portal2 = hex2imgcolor($im,"#0088FF");
	$color_portal3 = hex2imgcolor($im,"#000088");
	$color_bodenschatz = hex2imgcolor($im,"#000000");
	$color_bodenschatz2 = hex2imgcolor($im,"#FFFFFF");
	imagefill($im,0,0,$background_color);
	
	//echo "draw terrain<br>\n";
	//draw terrain
	for($xx=0;$xx<$sx;++$xx)for($yy=0;$yy<$sy;++$yy){
		$sleft = $left + $xx*$segment;
		$stop = $top + $yy*$segment;
		$sright = $sleft + $segment;
		$sbottom = $stop + $segment;
		
		$map = getMapAtPosition($sleft,$stop,$segment,$segment);
		for($xxx=$sleft;$xxx<$sleft+$segment;++$xxx)for($yyy=$stop;$yyy<$stop+$segment;++$yyy){
			$type = $map->getTerrainTypeAt($xxx,$yyy);
			if($type != kTerrain_Grass){
				imagesetpixel($im, $xxx-$left,$yyy-$top,$gTerrainType[$type]->imgcolor);
			}
		}
	/*		
		$l = sqlgettable("SELECT `x`,`y`,`type` FROM `terrain` WHERE `x`>=$sleft AND `x`<$sright AND `y`>=$stop AND `y`<$sbottom");
		foreach($l as $x)if ($x->type != kTerrain_Grass && $gTerrainType[$x->type]->speed > 0) {
			imagesetpixel($im, $x->x-$left,$x->y-$top,$gTerrainType[$x->type]->imgcolor);
		}*/
	}
	//unset($l);
	
	if ($mode == "creep") {
		//echo "draw creep<br>\n";
		array_walk($gUnitType,"renderMinimap_walkgetmonsterpic");
		$gHellholes = sqlgettable("SELECT `x`,`y`,`type`,`radius` FROM `hellhole`");
		foreach ($gHellholes as $o) {
			imagefilledrectangle($im,$o->x-$left-$o->radius,$o->y-$top-$o->radius,$o->x-$left+$o->radius,$o->y-$top+$o->radius,$color_hellhole);
		}
		foreach ($gHellholes as $o) {
			if ($o->type > 0 && isset($gUnitType[$o->type]->pic))
				imagecopyresized($im,$gUnitType[$o->type]->pic,$o->x-$left-$o->radius,$o->y-$top-$o->radius,0,0,$o->radius*2,$o->radius*2,23,23);
		}
	}
	
	//$l = sqlgettable("SELECT `x`,`y`,`user`,`type` FROM `building` WHERE `x`>=$left AND `x`<=$right AND `y`>=$top AND `y`<=$bottom");
	
	if (0 && $mode != "creep") {
		//echo "draw ressources<br>\n";
		array_walk($gBuildingType,"renderMinimap_walkgetbodenschatzpic");
		$bs_size = 23;
		foreach($l as $o) if ($o->user == 0 && in_array($o->type,$gBodenSchatzBuildings)) {
			if (!isset($gBuildingType[$o->type]->pic)) continue;
			imagecopy($im,$gBuildingType[$o->type]->pic,$o->x-$left-$bs_size/2,$o->y-$top-$bs_size/2,0,0,$bs_size,$bs_size);
		}
	}

	//echo "draw buildings<br>\n";
	foreach($map->building as $x) {
		if ($x->user == 0 && in_array($x->type,$gBodenSchatzBuildings)) {
			imagesetpixel($im, $x->x-$left,		$x->y-$top,$color_bodenschatz);
			imagesetpixel($im, $x->x-$left-1,		$x->y-$top-1,$color_bodenschatz);
			imagesetpixel($im, $x->x-$left-1,		$x->y-$top,$color_bodenschatz2);
			imagesetpixel($im, $x->x-$left,		$x->y-$top-1,$color_bodenschatz2);
		} else {
			switch($x->type) {
				case 3:
				case 5:
					$color = $gBuildingType[$x->type]->imgcolor;
				break;
				default:
					if ($x->user == 0 || !isset($gAllUsers[$x->user])) 
						$color = $color_nonplayer;
					else {
						$myuser = $gAllUsers[$x->user];
						if (isset($f_mode) && $f_mode == "guild") {
							if ($myuser->guild > 0 && isset($gAllGuilds[$myuser->guild])) 
									$color = $gAllGuilds[$myuser->guild]->imgcolor;
							else	$color = $color_no_guild;
						} else {
							$color = $myuser->imgcolor;
						}
					}
				break;
			}
			imagesetpixel($im,$x->x-$left,$x->y-$top,$color);
		}
	}
	//unset($l);
	
	// armeen
	//$l = sqlgettable("SELECT `x`,`y` FROM `army` WHERE $left<=`x` AND `x`<=($right) AND $top<=`y` AND `y`<=($bottom)");
	//echo "draw armies<br>\n";
	foreach($map->army as $x) {
		imagesetpixel($im, $x->x-$left,$x->y-$top,$color_red);
		imagesetpixel($im, $x->x+1-$left,$x->y-$top,$color_red);
		imagesetpixel($im, $x->x-$left,$x->y+1-$top,$color_red);
		imagesetpixel($im, $x->x+1-$left,$x->y+1-$top,$color_red);
	}
	
	// portale
	//echo "draw portals<br>\n";
	$l = sqlgettable("SELECT `x`,`y` FROM `building` WHERE `type` = 23 AND $left<=`x` AND `x`<=($right) AND $top<=`y` AND `y`<=($bottom)");
	foreach($l as $x) {
		imagefilledrectangle($im,$x->x-$left-2,$x->y-$top-2,$x->x-$left+2,$x->y-$top+2,$color_portal3);
		imagefilledrectangle($im,$x->x-$left-1,$x->y-$top-1,$x->x-$left+1,$x->y-$top+1,$color_portal2);
		imagesetpixel($im, $x->x-$left,		$x->y-$top,$color_portal);
	}
	unset($l);
	
	imagepng($im,$filename);
}

function generateCompleteMinimap($mode,$filename,$left,$right,$top,$bottom){
	renderMinimap($top,$left,$bottom,$right,$filename,$mode);
	
}

?>
