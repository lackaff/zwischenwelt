<?php
require_once("lib.main.php");
require_once("lib.minimap.php");
if (isset($f_mode) && $f_mode != "test") Lock();
profile_page_start("minimap.php");

define("kCrossHairOffset",102);
define("kSegmentSize",128);
define("kPinOffset",4);
$im = false;


			
if (!isset($f_mode)) $f_mode = "user";
$global = GetMiniMapGlobal($f_mode);
$lastpngmap = GetMiniMapLastTime($f_mode);
$filename = GetMiniMapFile($f_mode,$lastpngmap);


// random startplace
if (isset($f_random)) {
	require_once("lib.map.php");
	list($f_cx,$f_cy) = FindRandomStartplace();
}

if (!isset($f_x) && !isset($f_cross_x) && !isset($f_u)) {
	// use cache or generate new ?
	//if ((abs($lastpngmap - time()) < kMiniMapCacheTimeout && file_exists($filename))) {
		$left = $gGlobal["minimap_left"];
		$right = $gGlobal["minimap_right"];
		$top = $gGlobal["minimap_top"];
		$bottom = $gGlobal["minimap_bottom"];
		$dx = abs($right-$left);
		$dy = abs($bottom-$top);
	/*
	} else {
		// generate new map
		$o = sqlgetobject("SELECT MIN(`x`) as minx,MAX(`x`) as maxx,MIN(`y`) as miny,MAX(`y`) as maxy FROM `building`");
		$left = $o->minx - 10;
		$right = $o->maxx + 10;
		$top = $o->miny - 10;
		$bottom = $o->maxy + 10;
		$dx = abs($right-$left);
		$dy = abs($bottom-$top);
		$sx = ceil($dx / kSegmentSize);
		$sy = ceil($dy / kSegmentSize);
		SetGlobal("minimap_left",$left);
		SetGlobal("minimap_right",$right);
		SetGlobal("minimap_top",$top);
		SetGlobal("minimap_bottom",$bottom);
		
		$im = imagecreatetruecolor($dx, $dy) or die("Cannot Initialize new GD image stream");
		$time = time();
		SetGlobal($global,$time);
		$filename = GetMiniMapFile($f_mode,$time);
		
		$gAllUsers = sqlgettable("SELECT `id`,`color`,`guild` FROM `user`","id");
		$gAllGuilds = sqlgettable("SELECT `id`,`color` FROM `guild`","id");
		
		function walkhex2imgcolor (&$item,$key,$im) {
			$item->imgcolor = hex2imgcolor($im,$item->color);
		}
		array_walk($gTerrainType,"walkhex2imgcolor",$im);
		array_walk($gBuildingType,"walkhex2imgcolor",$im);
		array_walk($gAllGuilds,"walkhex2imgcolor",$im);
		array_walk($gAllUsers,"walkhex2imgcolor",$im);
		
		$background_color = $gTerrainType[1]->imgcolor;
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
		
		//draw terrain
		for($xx=0;$xx<$sx;++$xx)for($yy=0;$yy<$sy;++$yy){
			$sleft = $left + $xx*kSegmentSize;
			$stop = $top + $yy*kSegmentSize;
			$sright = $sleft + kSegmentSize;
			$sbottom = $stop + kSegmentSize;
			
			$lt = sqlgettable("SELECT `x`,`y`,`type` FROM `terrain` WHERE `x`>=$sleft AND `x`<$sright AND `y`>=$stop AND `y`<$sbottom");
			foreach($lt as $x)if ($x->type != kTerrain_Grass && $gTerrainType[$x->type]->speed > 0) {
				imagesetpixel($im, $x->x-$left,$x->y-$top,$gTerrainType[$x->type]->imgcolor);
			}
		}
		
		
		
		if ($f_mode == "creep") {
			function walkgetmonsterpic (&$item,$key) {
				if (intval($item->flags) & kUnitFlag_Monster) {
					$item->pic = imagecreatefrompng("gfx/".$item->gfx);
				}
			}
			array_walk($gUnitType,"walkgetmonsterpic");
			$gHellholes = sqlgettable("SELECT `x`,`y`,`type`,`radius` FROM `hellhole`");
			foreach ($gHellholes as $o) {
				imagefilledrectangle($im,$o->x-$left-$o->radius,$o->y-$top-$o->radius,$o->x-$left+$o->radius,$o->y-$top+$o->radius,$color_hellhole);
			}
			foreach ($gHellholes as $o) {
				if ($o->type > 0 && isset($gUnitType[$o->type]->pic))
					imagecopyresized($im,$gUnitType[$o->type]->pic,$o->x-$left-$o->radius,$o->y-$top-$o->radius,0,0,$o->radius*2,$o->radius*2,23,23);
			}
		}
		
		$lb = sqlgettable("SELECT `x`,`y`,`user`,`type` FROM `building`");
		//$lb = sqlgettable("SELECT `x`,`y`,`user`,`type` FROM `building` WHERE `x`>=$left AND `x`<=$right AND `y`>=$top AND `y`<=$bottom");
		
		if (0 && $f_mode != "creep") {
			function walkgetbodenschatzpic (&$item,$key) {
				global $gBodenSchatzBuildings;
				if (in_array($item->id,$gBodenSchatzBuildings)) {
					$item->pic = imagecreatefrompng("gfx/".$item->gfx);
				}
			}
			array_walk($gBuildingType,"walkgetbodenschatzpic");
			$bs_size = 23;
			foreach($lb as $o) if ($o->user == 0 && in_array($o->type,$gBodenSchatzBuildings)) {
				if (!isset($gBuildingType[$o->type]->pic)) continue;
				imagecopy($im,$gBuildingType[$o->type]->pic,$o->x-$left-$bs_size/2,$o->y-$top-$bs_size/2,0,0,$bs_size,$bs_size);
			}
		}
	
		foreach($lb as $x) {
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
							if ($f_mode == "guild") {
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
		unset($lb);
		
		// armeen
		$la = sqlgettable("SELECT `x`,`y` FROM `army` WHERE $left<=`x` AND `x`<=($right) AND $top<=`y` AND `y`<=($bottom)");
		foreach($la as $x) {
			imagesetpixel($im, $x->x-$left,$x->y-$top,$color_red);
			imagesetpixel($im, $x->x+1-$left,$x->y-$top,$color_red);
			imagesetpixel($im, $x->x-$left,$x->y+1-$top,$color_red);
			imagesetpixel($im, $x->x+1-$left,$x->y+1-$top,$color_red);
		}
		
		// portale
		$la = sqlgettable("SELECT `x`,`y` FROM `building` WHERE `type` = 23 AND $left<=`x` AND `x`<=($right) AND $top<=`y` AND `y`<=($bottom)");
		foreach($la as $x) {
			imagefilledrectangle($im,$x->x-$left-2,$x->y-$top-2,$x->x-$left+2,$x->y-$top+2,$color_portal3);
			imagefilledrectangle($im,$x->x-$left-1,$x->y-$top-1,$x->x-$left+1,$x->y-$top+1,$color_portal2);
			imagesetpixel($im, $x->x-$left,		$x->y-$top,$color_portal);
		}
		unset($la);
		
		imagepng($im,$filename);
	} // endif generate or load
	*/

	// header("Content-type: image/png");
	
	
	// resize window for waypoint map
	$jsresize = false;
	if ($f_mode == "wp" && isset($gUser) && (sqlgetone("SELECT `user` FROM `army` WHERE `id` = ".intval($f_army)) == $gUser->id)) {
		$o = sqlgetobject("SELECT MIN(`x`) as minx,MAX(`x`) as maxx,MIN(`y`) as miny,MAX(`y`) as maxy FROM `waypoint` WHERE `army` = ".intval($f_army));
		$left2 = $o->minx - 10;
		$right2 = $o->maxx + 10;
		$top2 = $o->miny - 10;
		$bottom2 = $o->maxy + 10;
		$dx2 = abs($right2-$left2);
		$dy2 = abs($bottom2-$top2);
		$jsresize = "window.resizeTo(".($dx2+60).",".($dy2+90).");";
	
		//fixme: $im is false
		$im2 = imagecreatetruecolor($dx2, $dy2) or die("Cannot Initialize new GD image stream");
		//vardump($im2);
		//echo "[left2=$left2 right2=$right2 top2=$top2 dx2=$dx2 dy2=$dy2]";
		
		$im = imagecreatefrompng($filename);
		
		imagecopy($im2,$im,0,0,$left2-$left,$top2-$top,$dx2,$dy2);
		
		$color = hex2imgcolor($im2,"#00FF00");
		$wps = sqlgettable("SELECT * FROM `waypoint` WHERE `army` = ".intval($f_army)." ORDER BY `priority`");
		$wplen = count($wps);
		for ($i=0;$i<$wplen-1;++$i)
			imageline($im2,$wps[$i]->x-$left2,$wps[$i]->y-$top2,$wps[$i+1]->x-$left2,$wps[$i+1]->y-$top2,$color);
		
		$filename = GetMiniMapFile("wpout",time());
		
		imagepng($im2,$filename);
		imagedestroy($im2);
	} // endif resize window for waypoint map
	
	// release memory
	if ($im) imagedestroy($im);
} // endif x or crossx set
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
<TITLE>MiniMap</TITLE>
<?php
if (isset($f_clickimgmap_x)) {
	$f_x = $f_clickimgmap_x;
	$f_y = $f_clickimgmap_y;
}
if (isset($f_x) || isset($f_cross_x) || isset($f_u)) {
	if (isset($f_mode) && $f_mode == "wp") {
		$o = sqlgetobject("SELECT MIN(`x`) as minx,MIN(`y`) as miny FROM `waypoint` WHERE `army` = ".intval($f_army));
		$left = $o->minx - 10;
		$top = $o->miny - 10;
	} else {
		$left = $gGlobal["minimap_left"];
		$top = $gGlobal["minimap_top"];
		if (isset($f_u)) {
			foreach ($f_u as $key => $val) {
				$hq = sqlgetobject("SELECT * FROM `building` WHERE `type` = ".kBuilding_HQ." AND `user` = ".intval($key)." LIMIT 1");
				$f_x = $hq->x-$left;
				$f_y = $hq->y-$top;
				break;
			}
		} else if (isset($f_cross_x)) {
			$f_x = intval($f_cross_x) + intval($f_cx) - $left - kCrossHairOffset;
			$f_y = intval($f_cross_y) + intval($f_cy) - $top - kCrossHairOffset;
		}
	}
	echo ($f_x+$left).",".($f_y+$top);
	?>
	<SCRIPT LANGUAGE="JavaScript">
	<!--
		opener.parent.map.location.href = "<?=kMapScript?>?x="+<?=$f_x+$left?>+"&y="+<?=$f_y+$top?>+"&sid=<?=$gSID?>";
		<?php if(UserHasBuilding($gUser->id,kBuilding_HQ)) {?>
		opener.parent.info.location.href = "info/info.php?x="+<?=$f_x+$left?>+"&y="+<?=$f_y+$top?>+"&sid=<?=$gSID?>";
		<?php }?>
		window.close();
	//-->
	</SCRIPT>
	<?php
		$die = true;
} else	$die = false;
?>
</HEAD>
<BODY>
	<?php if (!$die) {?>
		<?php if ($jsresize) {?>
		<SCRIPT LANGUAGE="JavaScript">
		<!--
		<?=$jsresize?>
		//-->
		</SCRIPT>
		<?php }?>
		<FORM METHOD=POST ACTION="<?=Query("?mode=?&army=?&sid=?&cx=?&cy=?")?>">
		<input type="image" src="<?=$filename?>" name="clickimgmap" style="position:absolute;left:0px;top:0px;">
		<?php if (!isset($f_mode) || $f_mode != "wp") {?>
			<input type="image" name="cross" src="gfx/minimapcross.gif" style="position:absolute;left:<?=intval($f_cx)-$left-kCrossHairOffset?>px;top:<?=intval($f_cy)-$top-kCrossHairOffset?>px;<?php if (0) echo "z-index:2;";?>">
		<?php } // endif?>
		<?php if (isset($f_diplomap)) {?>
			<?php $allusers = sqlgettable("SELECT `user`.*,`building`.`x`,`building`.`y` FROM `user`,`building` WHERE `building`.`type` = ".kBuilding_HQ." AND `building`.`user` = `user`.`id`");?>
			<?php foreach ($allusers as $o) if ($o->id == $gUser->id || $o->general_pts+$o->army_pts >= 20) {?>
			<input type="image" name="<?="u[".$o->id."]"?>" title="<?=$o->name?>" src="gfx/<?=GetFOFminimap($gUser,$o)?>" style="position:absolute;left:<?=$o->x - $left - kPinOffset?>px;top:<?=$o->y - $top- kPinOffset?>px;">
			<?php } // endforeach?>
		<?php }?>
		</FORM>
	<?php }?>
</BODY>
</HTML>
<?php profile_page_end();?>
