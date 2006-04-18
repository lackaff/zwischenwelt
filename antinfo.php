<?php
require_once("lib.main.php");
?>
<xml>
  <antinfo>
    <timestamp format="Y-m-d H:i:s">2006-04-18 16:51:12</timestamp>
    <units type="AmeisenTruppenAnzahl"><?=intval(sqlgetone("SELECT COUNT(*) FROM `unit` WHERE `army` > 0 AND `type` = 55"))?></units>
    <units type="AmeisenKöniginnenTruppenAnzahl"><?=intval(sqlgetone("SELECT COUNT(*) FROM `unit` WHERE `army` > 0 AND  `type` = 54"))?></units>
    <units type="AmeisenEinheitenAnzahl"><?=intval(sqlgetone("SELECT SUM(`amount`) FROM `unit` WHERE `type` = 55"))?></units>
    <units type="AmeisenKöniginnenEinheitenAnzahl"><?=intval(sqlgetone("SELECT SUM(`amount`) FROM `unit` WHERE `type` = 54"))?></units>
    <units type="AmeisenHügel"><?=intval(sqlgetone("SELECT COUNT(*) FROM hellhole WHERE ai_type = 3"))?></units>
  </antinfo>
</xml>