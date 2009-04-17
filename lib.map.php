<?php
require_once("lib.minimap.php");

function FindRandomStartplace () {
	global $gGlobal;
	//$o = sqlgetobject("SELECT MIN(`x`) as minx,MAX(`x`) as maxx,MIN(`y`) as miny,MAX(`y`) as maxy FROM `building`");
	$o->minx = $gGlobal["hq_min_x"];
	$o->maxx = $gGlobal["hq_max_x"];
	$o->miny = $gGlobal["hq_min_y"];
	$o->maxy = $gGlobal["hq_max_y"];
	for($i=0;$i<50;++$i){
		$x = rand($o->minx-20,$o->maxx+20);
		$y = rand($o->miny-20,$o->maxy+20);
		$d = 20;
		$count = sqlgetone("SELECT COUNT(`id`) FROM `building` WHERE (".($x-$d).")<=`x` AND `x`<=(".($x+$d).") AND (".($y-$d).")<=`y` AND `y`<=(".($y+$d).")");
		//sind hier schon gebäude
		if($count == 0){
			//nein, also mal sehen ob man hier ueberhaupt bauen kann
			$map = getMapAtPosition($x-1,$y-1,3,3);
			if($map->getTerrainTypeAt($x,$y) == kTerrain_Grass)break;
		}
	}
	return array($x,$y);
}


//checks if the position is in an buildable area
//will be used to limit new hq in an area in the middle of the world
//min max are stored in the globals table
// TODO : error message when attemting to set HQ outside !!!
function isPositionInBuildableRange($x,$y){
	global $gGlobal;
	$minx = $gGlobal["hq_min_x"];
	$maxx = $gGlobal["hq_max_x"];
	$miny = $gGlobal["hq_min_y"];
	$maxy = $gGlobal["hq_max_y"];
	return ($x>=$minx && $x<=$maxx) && ($y>=$miny && $y<=$maxy);
}

// TODO: OBSOLETE ???
function TypeCheck ($arr) {
	if (count($arr) == 0) return "1";
	return "`type` = ".implode(" OR `type` = ",$arr);
}

// TODO: DOOMED, OBSOLETE
function UpdateTerrainNWSE ($o) {
	global $gTerrainType;
	
	/*
	$types = $array();
	$btypes = array();
	
	$types[]=$o->type;
	
	//TODO: unhardcode this?!?
	
	//terrain <-> terrain coupling
	// Sea connects to ...
	if ($o->type==kTerrain_Sea)  $types[]=kTerrain_River;
	if ($o->type==kTerrain_Sea)  $types[]=kTerrain_Swamp;
	if ($o->type==kTerrain_Sea)  $types[]=kTerrain_DeepSea;
	// Swamp connects to ...
	if ($o->type==kTerrain_Swamp) $types[]=kTerrain_Sea;
	//SnowyMountain connects to ..
	if ($o->type==kTerrain_SnowyMountain) $types[]=kTerrain_Mountain;
	//Mountain connects to ..
	if ($o->type==kTerrain_Mountain)  $types[]=kTerrain_SnowyMountain;
	//Desert connects to ...
	if ($o->type==kTerrain_Desert)  $types[]=kTerrain_Oasis;
	
	//terrain <->building coupling
	if ($o->type=kTerrain_Sea) $btypes[] = kBuilding_Harbor;
	*/
	
	$qry = "SELECT 1 FROM `terrain` WHERE (".TypeCheck($gTerrainType[$o->type]->connectto_terrain).") AND ";
	/*
	if (count($btypes)<=0) {
		if (sqlgetone($qry."`x` = ".($o->x)." AND `y` = ".($o->y - 1))) $str .= "n";
		if (sqlgetone($qry."`x` = ".($o->x - 1)." AND `y` = ".($o->y))) $str .= "w";
		if (sqlgetone($qry."`x` = ".($o->x)." AND `y` = ".($o->y + 1))) $str .= "s";
		if (sqlgetone($qry."`x` = ".($o->x + 1)." AND `y` = ".($o->y))) $str .= "e";
	}else
	*/
	{
		$code = 0;
		$qry2 = "SELECT 1 FROM `building` WHERE (".TypeCheck($gTerrainType[$o->type]->connectto_building).") AND ";
		if (sqlgetone($qry."`x` = ".($o->x)." AND `y` = ".($o->y - 1))) $code |= kNWSE_N;
		if (sqlgetone($qry2."`x` = ".($o->x)." AND `y` = ".($o->y - 1))) $code |= kNWSE_N;
		if (sqlgetone($qry."`x` = ".($o->x - 1)." AND `y` = ".($o->y))) $code |= kNWSE_W;
		if (sqlgetone($qry2."`x` = ".($o->x - 1)." AND `y` = ".($o->y))) $code |= kNWSE_W;
		if (sqlgetone($qry."`x` = ".($o->x)." AND `y` = ".($o->y + 1))) $code |= kNWSE_S;
		if (sqlgetone($qry2."`x` = ".($o->x)." AND `y` = ".($o->y + 1))) $code |= kNWSE_S;
		if (sqlgetone($qry."`x` = ".($o->x + 1)." AND `y` = ".($o->y))) $code |= kNWSE_E;
		if (sqlgetone($qry2."`x` = ".($o->x + 1)." AND `y` = ".($o->y))) $code |= kNWSE_E;
	}

	sql("UPDATE `terrain` SET `nwse` = '".$code."' WHERE `id` = ".$o->id);
	
	return $code;
}

// TODO: DOOMED, OBSOLETE
function UpdateBuildingNWSE ($o) {
	$types = array();
	$code = "";
	if ($o->type == kBuilding_Gate) {
		// "ns" : wall from north to south , path from east to west
		$wall = "SELECT 1 FROM `building` WHERE `construction`=0 AND `type` = ".kBuilding_Wall." AND ";
		$path = "SELECT 1 FROM `building` WHERE `construction`=0 AND `type` = ".kBuilding_Path." AND ";
		$a1 = sqlgetone($path."`x` = ".($o->x - 1)." AND `y` = ".($o->y));
		$a2 = sqlgetone($path."`x` = ".($o->x + 1)." AND `y` = ".($o->y));
		$b1 = sqlgetone($wall."`x` = ".($o->x)." AND `y` = ".($o->y - 1));
		$b2 = sqlgetone($wall."`x` = ".($o->x)." AND `y` = ".($o->y + 1));

		if (($a1 && $a2) || ($b1 && $b2) || (($a1 || $a2) && ($b1 || $b2)))
			$code = kNWSE_N | kNWSE_S; // ns match 2 sides
		else if ($a1 || $a2 || $b1 || $b2) { // ns match 1 side
			// we does not match 2 sides
			$a1 = sqlgetone($wall."`x` = ".($o->x - 1)." AND `y` = ".($o->y));
			$a2 = sqlgetone($wall."`x` = ".($o->x + 1)." AND `y` = ".($o->y));
			$b1 = sqlgetone($path."`x` = ".($o->x)." AND `y` = ".($o->y - 1));
			$b2 = sqlgetone($path."`x` = ".($o->x)." AND `y` = ".($o->y + 1));
			if (($a1 && $a2) || ($b1 && $b2) || (($a1 || $a2) && ($b1 || $b2)))
					$code = kNWSE_W | kNWSE_E;
			else	$code = kNWSE_N | kNWSE_S; // ns match 1 side and we does not match 2 sides
		} else	$code = kNWSE_W | kNWSE_E;
	} else if ($o->type == kBuilding_Bridge || $o->type == kBuilding_GB) {
		// "ns" : path from north to south , river from east to west
		$river = "SELECT 1 FROM `terrain` WHERE `type` = ".kTerrain_River." AND ";
		$path = "SELECT 1 FROM `building` WHERE `construction`=0 AND `type` = ".kBuilding_Path." AND ";
		$a1 = sqlgetone($river."`x` = ".($o->x - 1)." AND `y` = ".($o->y));
		$a2 = sqlgetone($river."`x` = ".($o->x + 1)." AND `y` = ".($o->y));
		$b1 = sqlgetone($path."`x` = ".($o->x)." AND `y` = ".($o->y - 1));
		$b2 = sqlgetone($path."`x` = ".($o->x)." AND `y` = ".($o->y + 1));
		if (($a1 && $a2) || ($b1 && $b2) || (($a1 || $a2) && ($b1 || $b2)))
				$code = kNWSE_N | kNWSE_S;
		else	$code = kNWSE_W | kNWSE_E;
	} else if ($o->type == kBuilding_SeaGate) {
		$wall = "SELECT 1 FROM `building` WHERE `construction`=0 AND `type` = ".kBuilding_SeaWall." AND ";
		$b1 = sqlgetone($wall."`x` = ".($o->x)." AND `y` = ".($o->y - 1));
		$b2 = sqlgetone($wall."`x` = ".($o->x)." AND `y` = ".($o->y + 1));
		if ($b1) $code = kNWSE_N | kNWSE_S;
		if ($b1 && $b2)
			$code = kNWSE_N | kNWSE_S; // ns match 2 sides
		else	$code = kNWSE_W | kNWSE_E;
	} else if ($o->type == kBuilding_Harbor){ //TODO: verallgemeinern ... gebaeude sollten an gelaende orrientierbar sein
		$select="SELECT 1 FROM `terrain` WHERE `type` = ".kTerrain_Sea;
		$a=sqlgetone($select." AND (`x`=".($o->x+1)." AND `y`=".($o->y).")");
		$b=sqlgetone($select." AND (`x`=".($o->x-1)." AND `y`=".($o->y).")");
		$c=sqlgetone($select." AND (`x`=".($o->x)." AND `y`=".($o->y-1).")");
		$d=sqlgetone($select." AND (`x`=".($o->x)." AND `y`=".($o->y+1).")");
		if ($a) {
			$code = kNWSE_E;
		}else if ($b) {
			$code = kNWSE_W;
		}else if ($c) {
			$code = kNWSE_N;
		}else if ($d) {
			$code = kNWSE_S;
		}
	} else {
		//TODO: unhardcode this?
		$types[] = $o->type;
		if ($o->type == kBuilding_Path) $types[] = kBuilding_Bridge;
		if ($o->type == kBuilding_Path) $types[] = kBuilding_GB;
		if ($o->type == kBuilding_Path) $types[] = kBuilding_Gate;
		if ($o->type == kBuilding_Wall) $types[] = kBuilding_Gate;
		if ($o->type == kBuilding_SeaWall) $types[] = kBuilding_SeaGate;
		if ($o->type == kBuilding_SeaWall) $types[] = kBuilding_Wall;
		if ($o->type == kBuilding_Wall) $types[] = kBuilding_SeaWall;
		if ($o->type == kBuilding_Steg) $types[] = kBuilding_Harbor;
		
		$qry = "SELECT 1 FROM `building` WHERE (".TypeCheck($types).") AND `construction`=0 AND ";
		if (sqlgetone($qry."`x` = ".($o->x)." AND `y` = ".($o->y - 1))) $code |= kNWSE_N;
		if (sqlgetone($qry."`x` = ".($o->x - 1)." AND `y` = ".($o->y))) $code |= kNWSE_W;
		if (sqlgetone($qry."`x` = ".($o->x)." AND `y` = ".($o->y + 1))) $code |= kNWSE_S;
		if (sqlgetone($qry."`x` = ".($o->x + 1)." AND `y` = ".($o->y))) $code |= kNWSE_E;
	}
	sql("UPDATE `building` SET `nwse` = '".$code."' WHERE `id` = ".$o->id);
	return $code;
}

// TODO: DOOMED, OBSOLETE
function RegenSurroundingNWSE ($x,$y,$report_css_change=false) {
	$x = intval($x);
	$y = intval($y);
	return RegenAreaNWSE($x-1,$y-1,$x+1,$y+1,$report_css_change);
}

// TODO: DOOMED, OBSOLETE
function RegenAreaNWSE ($x1,$y1,$x2,$y2,$report_css_change=false) {
	return array(); // DEACTIVATED
	$xylimit = "`x` >= ".intval($x1)." AND `x` <= ".intval($x2)." AND 
				`y` >= ".intval($y1)." AND `y` <= ".intval($y2);
	
	global $gTerrainType;
	$cssclassarr = array();
	$gMapTerrain = sqlgettable("SELECT * FROM `terrain` WHERE ".$xylimit);
	foreach ($gMapTerrain as $o) {
		$o->nwse = UpdateTerrainNWSE($o);
		if ($report_css_change) {
			//$cssbase = $gTerrainType[$o->type]->cssclass;
			$cssbase = "t".($gTerrainType[$o->type]->id)."-%NWSE%";
			if ($o->type==kTerrain_River) foreach ($gMapTerrain as $x) if ($x->type == kTerrain_Sea) 
			if (($x->x == $o->x-1 && $x->y == $o->y && $o->nwse=="e") ||
				($x->x == $o->x+1 && $x->y == $o->y && $o->nwse=="w") ||
				($x->x == $o->x && $x->y == $o->y-1 && $o->nwse=="s") ||
				($x->x == $o->x && $x->y == $o->y+1 && $o->nwse=="n"))
				$cssbase = "fluss-see_%NWSE%";
			$cssclassarr[] = arr2obj(array("x"=>$o->x,"y"=>$o->y,"css"=>str_replace("%NWSE%",$o->nwse,$cssbase)));
		}
	}
	$gMapBuilding = sqlgettable("SELECT * FROM `building` WHERE `construction`=0 AND ".$xylimit);
	foreach ($gMapBuilding as $o) {
		$o->nwse = UpdateBuildingNWSE($o);
		if ($report_css_change)
			$cssclassarr[] = arr2obj(array("x"=>$o->x,"y"=>$o->y,"css"=>GetBuildingCSS($o,0)));
	}
	return $cssclassarr;
}

// TODO: OBSOLETE ??
function	terraingen($x,$y,$type,$dur,$ang,$step,$line,$split=0) {
	$x = intval($x);
	$y = intval($y);
	$type = intval($type);
	$dur = intval($dur);
	$ang = intval($ang);
	$split = intval($split);
	$curang = rand(0,360);
	$minx = round($x);	$miny = round($y);
	$maxx = round($x);	$maxy = round($y);
	// x and y are used as floats
	
	for ($i=0;$i<$dur;$i++) {
		if ($split > 0 && ($i % $split) == $split-1)
			terraingen($x,$y,$type,$dur-$i-1,$ang,$step,$line,0);
		
		$oldx = $x;
		$oldy = $y;
		$x += $step*sin(deg2rad($curang));
		$y += $step*cos(deg2rad($curang));
		$curang += rand(-$ang,$ang);
		
		$myx = round($oldx);
		$myy = round($oldy);
		
		// connect last and current pos with a straight line, if $line is true
		do {
			if (!sqlgetone("SELECT 1 FROM `building` WHERE `x` = ".$myx." AND `y` = ".$myy." LIMIT 1")) {
				sql("DELETE FROM `terrain` WHERE `x` = ".$myx." AND `y` = ".$myy);
				sql("INSERT INTO `terrain` SET type = $type , `x` = ".$myx." , `y` = ".$myy);
				
				if ($minx > $myx) $minx = $myx;
				if ($miny > $myy) $miny = $myy;
				if ($maxx < $myx) $maxx = $myx;
				if ($maxy < $myy) $maxy = $myy;
			}
			
			list($myx,$myy) = GetNextStep($myx,$myy,round($oldx),round($oldy),round($x),round($y));
		} while ($line && ($myx != round($x) || $myy != round($y))) ;
		
	}
	
	$xylimit = "`x` >= ".intval($minx-1)." AND `x` <= ".intval($maxx+1)." AND 
				`y` >= ".intval($miny-1)." AND `y` <= ".intval($maxy+1);
	
	$gMapTerrain = sqlgettable("SELECT * FROM `terrain` WHERE ".$xylimit);
	foreach ($gMapTerrain as $o)
		UpdateTerrainNWSE($o);
}

/**
* a interface (cMap) to read and write map data for rect from (x,y) to (x+dx-1,y+dy-1) of the terrain
*/
function getMapAtPosition($x,$y,$dx,$dy,$onlyterrain=false){
	return new cMap($x,$y,$dx,$dy,$onlyterrain);
}

class cMap {
		//checks if there is no building at the position and the terrain is the default (id=1) terrain (mostly grass)
		function StaticIsFieldEmpty($x,$y){
				$x = (int)$x;$y = (int)$y;
				$b = sqlgetone("SELECT COUNT(*) FROM `building` WHERE `x`=$x AND `y`=$y");
				$t = cMap::StaticGetTerrainAtPos($x,$y);
				$e = ($t == kTerrain_Grass) && ($b == 0);
				//if($e)$ee = "true"; else $ee = "false";
				//echo "[x=$x y=$y t=$t b=$b e=$ee]";
				return $e;
		}
	
	function StaticGetTerrainAtPos ($x,$y) {
		$x = intval($x); $y = intval($y);
		$type = sqlgetone("SELECT `type` FROM `terrain` WHERE `x` = ".$x." AND `y` = ".$y." LIMIT 1");
		if ($type) return $type;
		$type = sqlgetone("SELECT `type` FROM `terrainsegment4` WHERE `x` = ".floor($x/4)." AND `y` = ".floor($y/4)." LIMIT 1");
		if ($type) return $type;
		$type = sqlgetone("SELECT `type` FROM `terrainsegment64` WHERE `x` = ".floor($x/64)." AND `y` = ".floor($y/64)." LIMIT 1");
		if ($type) return $type;
		return kTerrain_Grass;
	}

	function cMap($x,$y,$dx,$dy,$onlyterrain){
		$x = intval($x)-1;$y = intval($y)-1;
		$dx = intval($dx)+2;$dy = intval($dy)+2;
		$x64 = floor($x/64);$y64 = floor($y/64);
		$dx64 = ceil($dx/64)+1;$dy64 = ceil($dy/64)+1;
		$x4 = floor($x/4);$y4 = floor($y/4);
		$dx4 = ceil($dx/4)+1;$dy4 = ceil($dy/4)+1;
		$this->x = $x;$this->y = $y;
		$this->dx = $dx;$this->dy = $dy;
		
		$l = sqlgettable("SELECT * FROM `terrain` WHERE `x`>=($x) AND `x`<($x+$dx) AND `y`>=($y) AND `y`<($y+$dy) AND `type`!=".kTerrain_Grass);
		$this->seg1 = array();
		foreach($l as $o)$this->seg1[$o->x][$o->y] = $o;
		unset($l);

		$lseg64 = sqlgettable("SELECT * FROM `terrainsegment64` 
			WHERE `x` >= $x64 AND `x` < ".($x64+$dx64)." AND `y` >= $y64 AND `y` < ".($y64+$dy64));
		$this->seg64 = array();
		foreach($lseg64 as $o)$this->seg64[$o->x][$o->y] = $o;
		unset($lseg64);

		$lseg4  = sqlgettable("SELECT * FROM `terrainsegment4`  
			WHERE `x` >= $x4 AND `x` < ".($x4+$dx4)." AND `y` >= $y4  AND `y` < ".($y4+$dy4));
		$this->seg4 = array();
		foreach($lseg4 as $o)$this->seg4[$o->x][$o->y] = $o;
		unset($lseg4);
		
		if(!$onlyterrain){
			$this->army = sqlgettable("SELECT * FROM `army` WHERE `x` >= $x AND `x` < ".($x+$dx)." AND `y` >= $y AND `y` < ".($y+$dy));
			$this->item = sqlgettable("SELECT * FROM `item` WHERE `x` >= $x AND `x` < ".($x+$dx)." AND `y` >= $y AND `y`< ".($y+$dy));
			$this->building = sqlgettable("SELECT * FROM `building` WHERE `x` >= $x AND `x` < ".($x+$dx)." AND `y` >= $y AND `y` < ".($y+$dy));
			$this->construction = sqlgettable("SELECT * FROM `construction` WHERE `x` >= $x AND `x` < ".($x+$dx)." AND `y` >= $y AND `y` < ".($y+$dy));
		}
		
		//echo "[1:($x,$y,$dx,$dy,".sizeof($this->seg1).") 64:($x64,$y64,$dx64,$dy64,".sizeof($this->seg64).") 4:($x4,$y4,$dx4,$dy4,".sizeof($this->seg4).")]";
		//print_r($this);
	}
	
	function getTerrainTypeAt($x,$y){
		assert('$x>=$this->x && $y>=$this->y && $x<($this->x+$this->dx) && $y<($this->y+$this->dy)');
		$x64 = floor($x/64);$y64 = floor($y/64);
		$x4 = floor($x/4);$y4 = floor($y/4);

		$type = kTerrain_Grass;
		
		if(!empty($this->seg64[$x64][$y64]))$type = $this->seg64[$x64][$y64]->type;
		if(!empty($this->seg4[$x4][$y4]))$type = $this->seg4[$x4][$y4]->type;
		if(!empty($this->seg1[$x][$y]))$type = $this->seg1[$x][$y]->type;
		
		//if($type != kTerrain_Grass)echo "[$x,$y,".($this->seg1[$x][$y]->type)."]";
		
		return $type;
	}

	function getBuildingAt($x,$y){
		if(empty($this->building))return null;
		foreach($this->building as $o)if($o->x == $x && $o->y == $y)return $o;
		return null;
	}
	
	function getBuildingTypeAt($x,$y){
		$o = $this->getBuildingAt($x,$y);
		if($o == null)return 0;
		else return $o->type;
	}

	function getTerrainNwseAt($x,$y){
		global $gTerrainType;
		$x64 = floor($x/64);$y64 = floor($y/64);
		$x4 = floor($x/4);$y4 = floor($y/4);

		$nwse = 0;
		//if(!empty($this->seg64[$x64][$y64]))$type = $this->seg64[$x64][$y64]->type;
		//if(empty($this->seg1[$x][$y]))return kNWSE_ALL;
		
		//todo: use the nwse field in the db to save cpu power (cache)
		$type = $this->getTerrainTypeAt($x,$y);
		$connectto_terrain = $gTerrainType[$type]->connectto_terrain;
		$connectto_building = $gTerrainType[$type]->connectto_building;
		if(in_array($this->getTerrainTypeAt($x,$y-1),$connectto_terrain)) $nwse |= kNWSE_N;
		if(in_array($this->getBuildingTypeAt($x,$y-1),$connectto_building)) $nwse |= kNWSE_N;
		if(in_array($this->getTerrainTypeAt($x-1,$y),$connectto_terrain)) $nwse |= kNWSE_W;
		if(in_array($this->getBuildingTypeAt($x-1,$y),$connectto_building)) $nwse |= kNWSE_W;
		if(in_array($this->getTerrainTypeAt($x,$y+1),$connectto_terrain)) $nwse |= kNWSE_S;
		if(in_array($this->getBuildingTypeAt($x,$y+1),$connectto_building)) $nwse |= kNWSE_S;
		if(in_array($this->getTerrainTypeAt($x+1,$y),$connectto_terrain)) $nwse |= kNWSE_E;
		if(in_array($this->getBuildingTypeAt($x+1,$y),$connectto_building)) $nwse |= kNWSE_E;

		return $nwse;
	}
}


//moves the complete base of the player with id (all buildings) to
//a new position (old hq -> new hq)
//only if there is only grass at the new position
//true if the move was successfull
function MovePlayerBase($id,$x,$y){
		$x = (int)$x;$y = (int)$y;$id = (int)$id;
		
		$user = sqlgetobject("SELECT * FROM `user` WHERE `id`=$id LIMIT 1");
		$hq = sqlgetobject("SELECT * FROM `building` WHERE `user`=$id AND `type`=".kBuilding_HQ." LIMIT 1");
		if(empty($user) || empty($hq))return false;
		
		//position delta vector
		$dx = $x - $hq->x;
		$dy = $y - $hq->y;
		echo "[dx=$dx dy=$dy]";
		
		$ok = true;
		
		//is the new space empty?
		$lb = sqlgettable("SELECT * FROM `building` WHERE `user`=$id");
		foreach($lb as $b){
				$nx = $dx + $b->x;
				$ny = $dy + $b->y;
				if(!cMap::StaticIsFieldEmpty($nx,$ny)){
						$ok = false;
						echo "[ ($nx,$ny) is not empty]";
				}
		}
		
		//oki the move all buildings
		if($ok){
				sql("UPDATE `building` SET `x`=`x`+($dx),`y`=`y`+($dy) WHERE `user`=$id");
				return true;
		} else return false;
}


?>
