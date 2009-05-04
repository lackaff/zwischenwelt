<?php


function max4_fnc($zahl)
{
	if($zahl < 1)for(;$zahl < 1;$zahl+=4);
	else if($zahl > 4)for(;$zahl > 4;$zahl-=4);

	return $zahl;
}

function set_kooridinates_x($direction, $x)
{
	switch($direction)
	{
	case 1;
		$x++;
		break;
	case 3;
		$x--;
		break;
	}
	return $x;
}

function set_kooridinates_y($direction, $y)
{
	switch($direction)
	{
	case 2;
		$y--;
		break;
	case 4;
		$y++;
		break;
	}
	return $y;
}



function switch_direction_fnc($direction)
{
	switch($direction)
	{
		case 1;
			$i = 3;
			break;
		case 2;
			$i = 4;
			break;
		case 3;
			$i = 1;
			break;
		case 4;
			$i = 2;
			break;
	}
	return $i;
}

function get_new_dir_fnc($array)
{
	$bigrandom = rand(1,$MAX);
	$erste = $MAX / 100 * $array[1];
	$zweite = $MAX / 100 * $array[2];
	$dritte = $MAX / 100 * $array[3];
	$vierte = $MAX / 100 * $array[4];
	
	if($bigrandom <= $erste) $direction = 1;
	$zweite += $erste;
	
	if($bigrandom > $erste && $bigrandom <= $zweite) $direction = 2;
	$dritte += $zweite;
	if($bigrandom > $zweite && $bigrandom <= $dritte) $direction = 3;
	$vierte += $dritte;
	if($bigrandom > $dritte && $bigrandom <= $vierte) $direction = 4;
	$direction = max4_fnc($direction);
	
	return $direction;

}

function generateRiver($x,$y,$steps)
{
	//echo "[$x,$y,$steps]";
		
	$fix_x = $x;
	$fix_y = $y;
	
	$MAX = 20000;
	$MAIN_DIRECTION = 50;
	$SECOND_DIRECTION = 25;
	$THIRD_DIRECTION = 25;
	$LAST_DIRECTION = 0;
	
	$river["$fix_x,$fix_y"] = 1;
	
	$direction = rand(1,4);
	
	$array[1] = 0;
	$array[2] = 0;
	$array[3] = 0;
	$array[4] = 0;
	
	// Festlegen der wahrscheinlichkeiten 
	
	$array[$direction] += $MAIN_DIRECTION; //Hauptrichtung
	
	if(rand(1,2) == 1)
	{
		$array[max4_fnc($direction - 1)] += $SECOND_DIRECTION; // links zum hauptrichtung
		$array[max4_fnc($direction + 1)] += $THIRD_DIRECTION; // rechts zur hauptrichtung
	}
	else
	{
		$array[max4_fnc($direction  - 1)] += $THIRD_DIRECTION; // links zur hauptrichtung
		$array[max4_fnc($direction  + 1)] += $SECOND_DIRECTION; // rechts zur hauptrichtung
	}
	
	$array[max4_fnc($direction + 2)] += $LAST_DIRECTION; // entgegengesetzt der hauptrichtung
	
	$repeat = rand(2,4);
	
	for($counter = 0; $counter < $steps; $counter++)
	{
		$count = 0;
		do
		{
			$old_direction=switch_direction_fnc($direction);
			
			$count++;
			
			if($repeat == 0)
			{
				$direction = get_new_dir_fnc($array);
				$repeat=rand(2,4);
			}
			
			$x = set_kooridinates_x($direction, $fix_x);
			$y = set_kooridinates_y($direction, $fix_y);
			
			$repeat--;
			
			if($count == 40) 
			{
				print "SCHEISSE";
				break 2;
			}
	
				
		} while(( $old_direction == $direction || $river["$x,$y"] == 1) && $count < 40);
		
		$fix_x = $x;
		$fix_y = $y;
		
		$river["$fix_x,$fix_y"] = 1;
	}
	
	//print_r($river);
	
	foreach($river as $k => $v)
	{
		list($x,$y) = split(",",$k);
		$o = sqlgetobject("SELECT `id` FROM `terrain` WHERE `x`=($x) AND `y`=($y)");
		if($o)continue;
		$o = sqlgetobject("SELECT `id` FROM `building` WHERE `x`=($x) AND `y`=($y)");
		if($o)continue;
		$o = sqlgetobject("SELECT `id` FROM `army` WHERE `x`=($x) AND `y`=($y)");
		if($o)continue;
		sql("INSERT INTO `terrain` SET `x`=($x),`y`=($y),`type`=2");
		RegenSurroundingNWSE($x,$y);
		//echo "$x,$y - $v<br>";
	}
}


function setTerrain($x,$y,$type){
	$x = intval($x);
	$y = intval($y);
	$type = intval($type);
	
	$oldtype = cMap::StaticGetTerrainAtPos($x,$y);
	if($oldtype == $type)return;
	
	sql("UPDATE `terrain` SET `type`=$type WHERE `x`=($x) AND `y`=($y)");
	if (mysql_affected_rows() <= 0) {
		$o = new EmptyObject();
		$o->x = $x;$o->y = $y;$o->type = $type;
		sql("INSERT INTO `terrain` SET ".obj2sql($o));
	}
	
	RegenSurroundingNWSE($x,$y);
}

/**
* at a random position around the pos x/y there grows a terrainid terrain
* if overwriteall if false, only gras will be overwritten, otherwise every terrain
* if there is not space for grow, nothing happens
* if alsoincenter is true, then the x/y is also a possible growing point
* if underbuildings is false, spaces with buildings on it will be ignored
* radius is manhatten distance
*/
function growTerrainAroundPos($x,$y,$radius,$terrainid,$overwriteall=false,$alsoincenter=false,$underbuildings=false){
	$done = false;
	for($dx=-$radius;$dx<=$radius;++$dx)
		for($dy=-$radius;$dy<=$radius;++$dy)if(!$done){
			if($alsoincenter == false && $dx == 0 && $dy == 0)continue;
			$px = $x+$dx;
			$py = $y+$dy;
			
			if($underbuildings == false)$b = sqlgetobject("SELECT * FROM `building` WHERE `x`=(".intval($px).") AND `y`=(".intval($py).")");
			else $b = null;
			
			if($overwriteall == false)$t = sqlgetobject("SELECT * FROM `terrain` WHERE `x`=(".intval($px).") AND `y`=(".intval($py).")");
			else $t = null;
			
			if(empty($b) && (empty($t) || $t->type == kTerrain_Grass)){
				setTerrain($px,$py,$terrainid);
				//echo " -- grow $terrainid at $px,$py<br>\n";
				$done = true;
			}
		}
}

