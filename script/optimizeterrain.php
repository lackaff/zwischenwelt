<?php

require_once("../lib.main.php");
require_once("../lib.map.php");

echo "searching 64 segments ";
$t = sqlgettable("SELECT count(*) AS c, 64*64 AS `cmax`, t.type, floor(`x`/64) AS segx, floor(`y`/64) AS segy FROM `terrain` t GROUP BY segx, segy,type HAVING c=cmax");
foreach($t as $o)if($o->c==$o->cmax){
  $x = $o->segx;
  $y = $o->segy;
  $type = $o->type;
  sql("REPLACE `terrainsegment64` SET `x`=($x),`y`=($y),`type`=($type)");
  sql("DELETE FROM `terrain` WHERE floor(`x`/64)=($x) AND floor(`y`/64)=($y) AND `type`=$type");
  echo ".";
}
echo " done<br>\n";


echo "searching 4 segments ";
$t = sqlgettable("SELECT count(*) AS c, 4*4 AS `cmax`, t.type, floor(`x`/4) AS segx, floor(`y`/4) AS segy FROM `terrain` t GROUP BY segx, segy,type HAVING c=cmax");
foreach($t as $o)if($o->c==$o->cmax){
  $x = $o->segx;
  $y = $o->segy;
  $type = $o->type;
  sql("REPLACE `terrainsegment4` SET `x`=($x),`y`=($y),`type`=($type)");
  sql("DELETE FROM `terrain` WHERE floor(`x`/4)=($x) AND floor(`y`/4)=($y) AND `type`=$type");
  echo ".";
}
echo " done<br>\n";

?>
