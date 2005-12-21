<?php
require_once("lib.army.php");
require_once("lib.unit.php");


function PathFindCallback($x,$y,$userid=0,$army=0,$armyblock=true){
	$units = $army ? (isset($army->units)?$army->units:cUnit::GetUnits($army->id)) : false;
	if (!$userid && $army) $userid = $army->user;
	// todo : if user == 0 use current user ?!? nope, need this for monster...
	return cArmy::GetPosSpeed($x,$y,$userid,$units,$armyblock);
}


class cPath {
	function CalcRouteBordered($t,$sx,$sy,$dx,$dy,$border,$map)
	{
		//echo "CalcRouteBordered($t,$sx,$sy,$dx,$dy,$border,$map)<br>";
		$run = true;
		while(sizeof($border) > 0 && $run)
		{
			//echo "border size = ".sizeof($border)."<br>";
			foreach($border as $b)
			{
				//echo "#border: ".$b[0].",".$b[1]."<br>";
				$dnext = Array(Array(-1,0),Array(1,0),Array(0,-1),Array(0,1));
				foreach($dnext as $next)
				{
					$x = $b[0]+$next[0];
					$y = $b[1]+$next[1];
					if(isset($t[$x][$y]))
					{
						if($t[$x][$y] > 0 && !isset($map[$x][$y]))
						{
							$map[$x][$y] = Array($b[0],$b[1]);
							$newborder[] = Array($x,$y);
							//echo "add border: $x,$y<br>";
							if($x == $dx && $y == $dy)$run = false;
						}
					}
					//else echo "!not in table: $x,$y<br>";
				}
			}
			$border = $newborder;
			$newborder = null;
		}
		//echo "...finished!<br>";
		return $map;
	}
	
	//generate astar node
	function AStarNode($army,$x,$y,$g,$h,&$p,$getspeedfunc="PathFindCallback"){
		$o = null;
		$o->x = $x; //x pos
		$o->y = $y; //y pos
		$o->g = $g; //g cost
		$o->h = $h; //h cost heuristic
		$o->s = $getspeedfunc($x,$y,0,$army,false); //speed
		$o->c = $g+$h; //complete cost
		$o->p =& $p; //parent node
		//echo "AStarNode($x,$y,$g,$h,[".$p->x.",".$p->y."])<br>";
		return $o;
	}
	
	//generate astar node
	function AStarNodeParentless($army,$x,$y,$g,$h,$getspeedfunc="PathFindCallback"){
		$o = null;
		$o->x = $x; //x pos
		$o->y = $y; //y pos
		$o->g = $g; //g cost
		$o->h = $h; //h cost heuristic
		$o->s = $getspeedfunc($x,$y,0,$army,false); //speed
		$o->c = $g+$h; //complete cost
		$o->p = null;
		return $o;
	}
	
	//heuristic distance between s? and d?
	//fielcost, cost of moving through one field
	function AStarH($sx,$sy,$dx,$dy,$fieldcost){
		return (abs($sx-$dx)+abs($sy-$dy))*$fieldcost;
	}
	
	//finds the node with the lowest costs
	function &AStarGetLowestCostNode(&$list){
		$n = null;
		$min = 0;
		$found = false;
		foreach($list as $i=>$value)
			if($found == false || $list[$i]->c < $min){
				$n =& $list[$i];
				$min = $n->c;
				$found = true;
			}
		return $n;
	}
	
	//pops the node with the lowest costs from the list
	function &AStarPopLowestCostNode(&$list){
		$n = null;
		$mini = 0;
		$min = 0;
		$found = false;
		foreach($list as $i=>$value)
			if($found == false || $list[$i]->c < $min){
				$n =& $list[$i];
				$mini = $i;
				$min = $n->c;
				$found = true;
			}
		unset($list[$mini]);
		//echo "poped node[x=$n->x,y=$n->y,g=$n->g,h=$n->h,c=$n->c,parent=$n->p[".$n->p->x.",".$n->p->y."]]<br>";
		return $n;
	}
	
	//is node with pos in list?
	function AStarIsInList($x,$y,&$list){
		foreach($list as $i=>$value)
			if($list[$i]->x == $x && $list[$i]->y == $y)return true;
		return false;
	}
	
	//return a reference to the node with x,y or null
	function AStarGetFromList($x,$y,&$list){
		foreach($list as $i=>$value)
			if($list[$i]->x == $x && $list[$i]->y == $y)return $list[$i];
		return null;
	}
	
	//berechnet einen weg, userid ist die id des users der rennt
	//s? der anfang und d? das ziel
	function AStarRoute($army,$sx,$sy,$dx,$dy,$getspeedfunc="PathFindCallback"){
		$debug=false;
		if($sx == $dx && $sy == $dy)return array();
		if($debug){
			echo "AStarRoute($army,$sx,$sy,$dx,$dy)<br>";
			echo "getspeedfunc = $getspeedfunc<br>";
		}
		$open = Array(cPath::AStarNodeParentless($army,$sx,$sy,0,0,$getspeedfunc));
		$close = Array();
		$maxsteps = 200;
		$fieldcost = 60;
		
		for($step=0;$step<$maxsteps;++$step){
			if($debug){
				echo "################### step...<br>";
				foreach($open as $nn)echo " * open node[x=$nn->x,y=$nn->y,g=$nn->g,h=$nn->h,c=$nn->c,parent=$nn->p[".$nn->p->x.",".$nn->p->y."]]<br>";
				foreach($close as $nn)echo " * closed node[x=$nn->x,y=$nn->y,g=$nn->g,h=$nn->h,c=$nn->c,parent=$nn->p[".$nn->p->x.",".$nn->p->y."]]<br>";
			}
			
			# Look for the lowest F cost square on the open list. We refer to this as the current square.
			$n =& cPath::AStarPopLowestCostNode($open);
			//PathFindCallback($x,$y,$userid=0,$army=0,$armyblock=true){
			$speed = $getspeedfunc($n->x,$n->y,0,$army,false);
			if($debug){
				echo "current node[x=$n->x,y=$n->y,g=$n->g,h=$n->h,c=$n->c,parent=$n->p[".$n->p->x.",".$n->p->y."],speed=$speed]<br>";
				if($speed == 0)echo "!!!!!!!!!!!!!!!!!!!!!! field is blocked<br>";
			}
				
			# Switch it to the closed list.
			$close[] = $n;
			
			# For each of the 8 squares adjacent to this current square
			$nx = Array($n->x+1,$n->x-1,$n->x,$n->x);
			$ny = Array($n->y,$n->y,$n->y-1,$n->y+1);
			
			for($i=0;$i<4;++$i){
				# If it is not walkable or if it is on the closed list, ignore it. 
				$speed = $getspeedfunc($nx[$i],$ny[$i],0,$army,false);
				if($debug)echo " + try pos ($nx[$i]|$ny[$i]) speed=$speed<br>";
				if($speed == 0 || cPath::AStarIsInList($nx[$i],$ny[$i],$close));
				# Otherwise do the following.
				else {
					# If it isn't on the open list, add it to the open list. Make the current square the parent of this square. Record the F, G, and H costs of the square.
					$nn = cPath::AStarGetFromList($nx[$i],$ny[$i],$open);
					if($nn === null){
						$nn = cPath::AStarNode($army,$nx[$i],$ny[$i],$n->g+$speed,cPath::AStarH($nx[$i],$ny[$i],$dx,$dy,$fieldcost,$getspeedfunc),$n);
						$open[] = $nn;
					}
					# If it is on the open list already, check to see if this path to that square is better, 
					# using G cost as the measure. A lower G cost means that this is a better path. If so, 
					# change the parent of the square to the current square, and recalculate the G and F scores of the square. 
					# If you are keeping your open list sorted by F score, you may need to resort the list to account for the change. 
					else {
						if($n->g+$speed < $nn->g){
							//found better path
							if($debug)echo "found better path<br>";
							$nn->p =& $n;
							$nn->g = $n->g+$speed;
							$nn->c = $nn->g + $nn->h;
						}
					}
					
					if($debug)echo " + next node[x=$nn->x,y=$nn->y,g=$nn->g,h=$nn->h,c=$nn->c,parent=$nn->p[".$nn->p->x.",".$nn->p->y."]]<br>";
			
					# Stop when you
					
					# Add the target square to the open list, in which case the path has been found, or
					if($nx[$i] == $dx && $ny[$i] == $dy){
						//echo "A*: path found in $step steps<br>";
						# Save the path. Working backwards from the target square, 
						# go from each square to its parent square until you reach the starting square. That is your path.
						$route = array();
	
						$node =& $nn;
						while($node != null && $step>=0){
							if($debug)echo " --- node[x=$node->x,y=$node->y,g=$node->g,h=$node->h,c=$node->c,parent=$node->p[".$node->p->x.",".$node->p->y."],speed=$speed]<br>";
							--$step;
							$route[] = array($node->x,$node->y);
							$node =& $node->p;
						}
						return array_reverse($route);
					}
				}
			}
			# Fail to find the target square, and the open list is empty. In this case, there is no path. 
			if(sizeof($open) == 0){
				//echo "A*: open empty after $step steps, no path found<br>";
				return array();
			}
		}
		
		if($debug)echo "A*: no path found, $step steps done<br>"; 
		return array();
	}
	
	function CalcRoute($t,$sx,$sy,$dx,$dy)
	{
		//echo "CalcRoute($t,$sx,$sy,$dx,$dy)<br>";
		$map = cPath::CalcRouteBordered($t,$sx,$sy,$dx,$dy,Array(Array($sx,$sy)),Array());
		$x = $dx;$y = $dy;
		$route[] = array($x,$y);
		//echo "generate route<br>";
		while($x != $sx || $y != $sy)
		{
			$p = $map[$x][$y];
			//echo " #$x,$y: (".$p[0]."|".$p[1].")<br>";
			if(empty($p))return Array();
			$route[] = $p;
			$x = $p[0];$y = $p[1];
		}
		//echo "...finished!<br>";
		return array_reverse($route);
	}
	
	function SimplyfyRouteIsLine(&$r,&$a,&$b)
	{
		if($b-$a != 2)return false;
		//echo "SimplyfyRouteIsLine($r,$a,$b)<br>";
		//print_r($r[$a]);
		//print_r($r[$b]);
		return ($r[$a][0] == $r[$a+1][0] && $r[$a+1][0] == $r[$a+2][0]) || ($r[$a][1] == $r[$a+1][1] && $r[$a+1][1] == $r[$a+2][1]);
	}
	
	function SimplyfyRoute($r)
	{
		//echo "SimplyfyRoute($r)<br>";
		if(sizeof($r) < 3)return $r;
		$a = 0;
		$remove = Array();
		for($b=2;$b<sizeof($r);++$b)
		{
			//echo "$a,$b#a(".$r[$a][0]."|".$r[$a][1].") #b(".$r[$b][0]."|".$r[$b][1].")<br>";
			if(cPath::SimplyfyRouteIsLine($r,$a,$b))
			{
				//echo "line between $a and $b, size=".sizeof($r)."<br>";
				$remove[] = $b-1;
				++$a;
			}
			else ++$a;
		}
		foreach($remove as $i)unset($r[$i]);
		//echo "...finished!<br>";
		return $r;
	}
	
	function ArmySetRouteTo ($armyid,$x,$y)
	{
		echo "ArmySetRouteTo ($armyid,$x,$y)<br>";
		$max = 30;
		$x = intval($x);$y = intval($y);
		$army = sqlgetobject("SELECT * FROM `army` WHERE `id`=".intval($armyid));
		if($army)
		{//army exists
			$wp = sqlgetobject("SELECT `x`,`y` FROM `waypoint` WHERE `army` = ".$army->id." ORDER BY `priority` DESC LIMIT 1");
			
			if($wp){$sx = $wp->x;$sy = $wp->y;}
			else {$sx = $army->x;$sy = $army->y;}
	
			//echo "x=$x,y=$y,sx=$sx,sy=$sy<br>";
	
			//if($sx == $x && $sy == $y) echo "error:already there";
			if($sx == $x && $sy == $y)return false;
			else
			{//and must move to get to the target
				if(max(abs($sx - $x),abs($sy - $y))<$max)
				{//oki, area is small enough to calc route
					/*
					for($xx=$sx-$max;$xx<$sx+$max;++$xx)
						for($yy=$sy-$max;$yy<$sy+$max;++$yy)
						{
							$t[$xx][$yy] = cArmy::GetPosSpeed($xx,$yy,$army->user,$army->units);
							//echo "add to table: $xx,$yy,".$t[$xx][$yy]."<br>";
						}
					$r = SimplyfyRoute(CalcRoute($t,$sx,$sy,$x,$y));
					*/
					//echo "find a way...<br>";
					$r = cPath::SimplyfyRoute(cPath::AStarRoute($army,$sx,$sy,$x,$y));
					$newwparr = array();
					foreach($r as $x) {
						//echo "#(".$x[0]."|".$x[1].")<br>";
						$wp = cArmy::ArmySetWaypoint($army,$x[0],$x[1]);
						if ($wp) $newwparr[] = $wp;
					}
					return $newwparr;
				} //else echo "error:too far";
			}
		} 
		//echo "error:army not set";
		//echo "...finished!<br>";
		return false;
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
			$o->x = $wp[0];
			$o->y = $wp[1];
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
			$r = cPath::SimplyfyRoute(cPath::AStarRoute($army,$army->x,$army->y,$wps[$i]->x,$wps[$i]->y));
			cPath::ArmyInsertWPBeforePrio($army,$wps[$i]->priority,$r);
		} else $r = array();
		sql("UPDATE `army` SET `idle`=0 WHERE `id`=".intval($army->id));
	}
}
?>
