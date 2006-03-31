<?php

require_once("lib.army.php");
require_once("lib.unit.php");

class cPath {
	function ArmySetRouteTo($armyid,$x,$y){
		$army = sqlgetobject("SELECT * FROM `army` WHERE `id`=".intval($armyid));
		if(empty($army))return array();
		
		$units = cUnit::GetUnits($army->id);
		
		$wp = sqlgetobject("SELECT `x`,`y` FROM `waypoint` WHERE `army` = ".$army->id." ORDER BY `priority` DESC LIMIT 1");
		if($wp){$sx = $wp->x;$sy = $wp->y;}
		else {$sx = $army->x;$sy = $army->y;}

		$path = cPath::FindPath($army->user,$units,$sx,$sy,$x,$y);
		
		$wps = array();
		foreach($path as $x) {
			if($army->x == $x->x && $army->y == $x->y)continue;
			$wp = cArmy::ArmySetWaypoint($army,$x->x,$x->y);
			if ($wp) $wps[] = $wp;
		}
		
		if($wps === false)return array();
		else return $wps;
	}
	 
	//cArmy::GetPosSpeed($x,$y,$userid,$units,$armyblock);
	 
	function GetHeuristic($srcx,$srcy,$dstx,$dsty){
		return 60*(abs($srcx-$dstx)+abs($srcy-$dsty));
	}
	 
	function GetNeighbours($userid,$units,$x,$y,$dstx,$dsty,$g){
		//echo "GetNeighbours($userid,$units,$x,$y,$dstx,$dsty,$g)\n";
		$list = array();
		$parent = $x."_".$y;
		
		$speed = cArmy::GetPosSpeed($x+1,$y,$userid,$units,false);
		if($speed > 0)$list[($x+1)."_".($y)] = array("p"=>$parent,"g"=>$speed+$g,"h"=>cPath::GetHeuristic($x+1,$y,$dstx,$dsty));
		
		$speed = cArmy::GetPosSpeed($x-1,$y,$userid,$units,false);
		if($speed > 0)$list[($x-1)."_".($y)] = array("p"=>$parent,"g"=>$speed+$g,"h"=>cPath::GetHeuristic($x-1,$y,$dstx,$dsty));

		$speed = cArmy::GetPosSpeed($x,$y+1,$userid,$units,false);
		if($speed > 0)$list[($x)."_".($y+1)] = array("p"=>$parent,"g"=>$speed+$g,"h"=>cPath::GetHeuristic($x,$y+1,$dstx,$dsty));

		$speed = cArmy::GetPosSpeed($x,$y-1,$userid,$units,false);
		if($speed > 0)$list[($x)."_".($y-1)] = array("p"=>$parent,"g"=>$speed+$g,"h"=>cPath::GetHeuristic($x,$y-1,$dstx,$dsty));

		return $list;
	}
	 
	function FindBest($list){
		$minfound = false;
		foreach($list as $id=>$a){
			$f = $a["g"]+$a["h"];
			if(!$minfound || $f<$minf){
				$minf = $f;
				$minid = $id;
				$minfound = true;
			}
		}
		
		return $minid;
	}
	
	function GetDirection($current,$parent){
		if(empty($parent))return 0;
		list($cx,$cy) = explode("_",$current);
		list($px,$py) = explode("_",$parent);
		if($cx != $px && $cy != $py)return 0;
		if($cx == $px){
			if($cy < $py)return 1;
			else return 3;
		} else {
			if($cx < $px)return 2;
			else return 4;
		}
	}
	
	function ReconstructPath($dstx,$dsty,$list){
		//echo "[list]";
		//print_r($list);
		$path = array();
		$id = $dstx."_".$dsty;
		$dir = 0;
		
		do {
			$current = $list[$id];
			$d = cPath::GetDirection($id,$current["p"]);
			//echo "[id=$id d=$d]\n";
			
			list($x,$y) = explode("_",$id);
			$o = null;
			$o->x = $x;
			$o->y = $y;
			$id = $current["p"];
			
			if($d != $dir){
				$path[] = $o;
				//echo "point $o->x,$o->y added\n";
			} //else "point $o->x,$o->y skipped\n";
			
			$dir = $d;
		} while(!empty($current["p"]));
		
		//echo "[path]";
		//print_r($path);
		return $path;
	}
	
	function FindPath($userid,$units,$srcx,$srcy,$dstx,$dsty,$maxsteps=512){
		$lOpen = array();
		$lClose = array();

		//init open list with fields around startingpoint
		$lOpen = cPath::GetNeighbours($userid,$units,$srcx,$srcy,$dstx,$dsty,0);
		$lClose[$srcx."_".$srcy] = array("p"=>"","h"=>cPath::GetHeuristic($srcx,$srcy,$dstx,$dsty),"g"=>0);
		//echo "[open]";
		//print_r($lOpen);
		
		$step = 0;
		
		while(sizeof($lOpen)>0){
			//get best open point
			$bestid = cPath::FindBest($lOpen);
			$best = $lOpen[$bestid];
			//echo "[bestid=$bestid]";
			list($x,$y) = explode("_",$bestid);
			
			//echo "[best]";
			//print_r($best);
			//echo "[open]";
			//print_r($lOpen);
			
			//add new valid points around it to the open list and calc the costs of the new ones and add parent link
			$n = cPath::GetNeighbours($userid,$units,$x,$y,$dstx,$dsty,$best["g"]);
			
			//echo "[n]";
			//print_r($n);
			
			foreach($n as $id=>$a)
				if(empty($lOpen[$id]) && empty($lClose[$id]))
					$lOpen[$id] = $a;
			
			//remove the point from the openlist an add it to the close list
			unset($lOpen[$bestid]);
			$lClose[$bestid] = $best;

			if($x == $dstx && $y == $dsty)break;
			++$step;
			if($step > $maxsteps)return array();
		}
		
		//echo "[close]";
		//print_r($lClose);
		
		//move back the list and create a new list with the path
		$path = cPath::ReconstructPath($dstx,$dsty,$lClose);
		//reverse path
		$path = array_reverse($path);
		//return path

		//echo "[path]";
		//print_r($path);
		
		return $path;
	}
	
	//insert wps before prio wp at army
	// used only by cPath::ArmyRecalcNextWP(), for the new-path-if-blocked behavior
	function ArmyInsertWPBeforePrio($army,$priority,$wps){
		//echo "ArmyInsertWPBeforePrio($army->id,$priority,".sizeof($wps).")<br>";
		$size = sizeof($wps);
		sql("UPDATE `waypoint` SET `priority`=`priority`+$size WHERE `army`=".intval($army->id)." AND `priority`>=".intval($priority));
		$o = null;
		$o->army = $army->id;
		
		foreach($wps as $wp){
			$o->priority = $priority;
			$o->x = $wp->x;
			$o->y = $wp->y;
			sql("INSERT INTO `waypoint` SET ".obj2sql($o));
			//vardump($o);
			++$priority;
		}
		
		echo "$size wps added to army $army->name [$army->id]<br>";
	}

	//try to find a route from army to the next wp
	// used only by cPath::ArmyRecalcNextWP(), for the new-path-if-blocked behavior
	function ArmyRecalcNextWP($army,&$wps){
		for($i=0;$i<sizeof($wps) && $wps[$i]->x == $army->x && $wps[$i]->y == $army->y;++$i);
		if($i<sizeof($wps) && ($wps[$i]->x != $army->x || $wps[$i]->y != $army->y)){
			if(empty($army))return array();
			
			$units = cUnit::GetUnits($army->id);
			$sx = $army->x;$sy = $army->y;
			$x = $wps[$i]->x;$y = $wps[$i]->y;
			$prio = $wps[$i]->priority;
			
			$path = cPath::FindPath($army->user,$units,$sx,$sy,$x,$y);
			
			cPath::ArmyInsertWPBeforePrio($army,$prio,$path);
			
		} else sql("UPDATE `army` SET `idle`=0 WHERE `id`=".intval($army->id));
	}
}

?>
