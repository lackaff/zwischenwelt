<?php
require_once("lib.main.php");
Lock();

$hq = sqlgetobject("SELECT * FROM `building` WHERE `user` = ".$gUser->id." AND `type` = ".kBuilding_HQ);
if ($hq) Redirect(Query("info/info.php?sid=?".($f_fc==1?"&fc=1&":"&")."x=".$hq->x."&y=".$hq->y));
else
{
?>
	Du hast noch kein Haupthaus, daher solltest Du Dir ein schönes Plätzchen suchen und dort eines bauen.<br>
	Unter der Karte im rechten Fenster sind Pfeile, damit kannst du dich umschauen.<br>
	Wenn du auf (MiniMap) clickst siehst du eine kleine Weltkarte.<br>
	Bedenke bei der Wahl deines Startplatzes das du Platz brauchst um dich auszubreiten, <br>
	also nicht unbedingt in einer kleinen Niesche zwischen den "älteren" Spielern anfangen ;)<br>
	Wenn du einen Platz gefunden hast,<br>
	wähle rechts unten das Bild für das Haupthaus <img src="<?=g($gBuildingType[kBuilding_HQ]->gfx)?>" alt="."><br>
	und clicke auf eine freie Stelle, um es zu Bauen<br>
	Viel Spass !<br>
	<br>
	<a target="map" href="<?=query(kMapScript."?sid=?")?>">Hier klicken um zu einem freien Platz zu springen</a><br>
	<a target="_blank" href="<?=query("minimap.php?random=1&sid=?")?>">Hier klicken um eine Weltkarte anzuschauen</a>
	
<?php
}

?>
