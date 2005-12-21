<?php
require_once("../lib.main.php");
require_once("../lib.building.php");
require_once("../lib.army.php");
require_once("../lib.map.php");

function createz($zombie,$type){
	global $hq,$userid;
	if($zombie > 50000) $num = $zombie/50000;
	else $num = 1;
	for($i=0;$i<$num;$i++){
		$z = min($zombie,50000);
		$zombie-=$z;
		$n=0;
		if($z<1) continue;
		$units=cUnit::Simple($type,$z);
		for(;;){
			$n++;
			$x=$hq->x+((rand()%10)+(5+round($n/25)));
			$y=$hq->y+((rand()%10)+(5+round($n/25)));
			if((sqlgetone("SELECT COUNT(a.`id`)+COUNT(b.`id`) FROM `army` a,`building` b WHERE a.`x`=$x AND a.`y`=$y AND b.`x`=$x  AND b.`y`=$y")<1 )
			&& cArmy::GetPosSpeed($x,$y,$userid,$units,true) >0)break;
			if($n>1000000) break;
		}
		if($n<100000)cArmy::SpawnArmy($x,$y,$units,false,-1,0,0,0,-1);
	}
}

if(isset($f_delid))
	foreach($f_delid as $id=>$v){
		$user = sqlgetobject("SELECT * FROM `user` where `id`=".intval($id));
		$hq = sqlgetobject("SELECT * FROM `building` WHERE `user`=".$user->id." AND `type`=".kBuilding_HQ);
		if($hq){
		cBuilding::removeBuilding($hq,$user->id,false,false);
		echo "the user had $user->population ppl ... they will return as ghosts or zombies by chance ...";
		$undead = round((max($user->population,10000)/2)/max(0.1,(rand()%100)/100));
		echo "we are going to create $undead undead<br>";
		$zombie = $undead/(max(0.1,(rand()%100)/100));
		createz($zombie,49);
		$undead-=$zombie;
		createz($undead,48);
	}
	}
		
echo "<form method=post action=?do=del>";
echo "scanning for players without buildings ...<br>";
$t = sqlgettable("SELECT `id`,`name`,`lastlogin` FROM `user` WHERE `admin`=0 AND ".time()."-`lastlogin`>(7*7*24*60*60)");
foreach($t as $u)
{
	if($f_unchecked==1)$checked="";
	else if($u->lastlogin==0) $checked="";
	else $checked = "checked";
	
	echo "<input value=1 type=checkbox name=\"delid[$u->id]\" $checked> user ".$u->id." '".$u->name."' lastlogin was ".date($u->lastlogin)."<br>";
}
echo "done<br><br>";
echo "<input type=submit value=delete></form>";
?>