<?php

require_once("../lib.main.php");
require_once("../lib.map.php");

echo "searching 64 segments<br>\n";
$t = sqlgettable("SELECT count(*) AS c, 64*64 AS `cmax`, t.type, floor(`x`/64) AS segx, floor(`y`/64) AS segy FROM `terrain` t GROUP BY segx, segy,type HAVING c=cmax");
$size = sizeof($t);$count = 0;
foreach($t as $o)if($o->c==$o->cmax){
  $x = $o->segx;
  $y = $o->segy;
  $type = $o->type;
  sql("REPLACE `terrainsegment64` SET `x`=($x),`y`=($y),`type`=($type)");
  sql("DELETE FROM `terrain` WHERE `x`>=(($x)*64) AND `x`<(($x+1)*64) AND `y`>=(($y)*64) AND `y`<(($y+1)*64) AND `type`=$type");
  ++$count;echo "$count / $size <br>\n";
}
echo "done<br>\n";


echo "searching 4 segments<br>\n";
$t = sqlgettable("SELECT count(*) AS c, 4*4 AS `cmax`, t.type, floor(`x`/4) AS segx, floor(`y`/4) AS segy FROM `terrain` t GROUP BY segx, segy,type HAVING c=cmax");
$size = sizeof($t);$count = 0;
foreach($t as $o)if($o->c==$o->cmax){
  $x = $o->segx;
  $y = $o->segy;
  $type = $o->type;
  sql("REPLACE `terrainsegment4` SET `x`=($x),`y`=($y),`type`=($type)");
  sql("DELETE FROM `terrain` WHERE `x`>=(($x)*4) AND `x`<(($x+1)*4) AND `y`>=(($y)*4) AND `y`<(($y+1)*4) AND `type`=$type");
  ++$count;echo "$count / $size <br>\n";
}
echo "done<br>\n";

echo "merging 16x16 4er to 1x1 64er segments<br>\n";
$t = sqlgettable("SELECT count(*) AS c, 16*16 AS `cmax`, t.type, floor(`x`/16) AS segx, floor(`y`/16) AS segy FROM `terrainsegment4` t GROUP BY segx, segy,type HAVING c=cmax");
$size = sizeof($t);$count = 0;
foreach($t as $o)if($o->c==$o->cmax){
  $x = $o->segx;
  $y = $o->segy;
  $type = $o->type;
  sql("REPLACE `terrainsegment64` SET `x`=($x),`y`=($y),`type`=($type)");
  sql("DELETE FROM `terrainsegment4` WHERE `x`>=(($x)*16) AND `x`<(($x+1)*16) AND `y`>=(($y)*16) AND `y`<(($y+1)*16) AND `type`=$type");
  ++$count;echo "$count / $size <br>\n";
}
echo "done<br>\n";


?>
