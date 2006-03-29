<?php
require_once("../lib.main.php");
require_once("../lib.score.php");
Lock();

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 transitional//EN"
   "http://www.w3.org/TR/html4/transitional.dtd">
<html>
<head>
<link rel="stylesheet" type="text/css" href="<?=GetZWStylePath()?>">
<title>Zwischenwelt - Ressourcenanzeige</title>
</head>
<body>
<?php

if(isset($f_fmt))
	SetUserValue($gUser->id,"resformat",$f_fmt);

include("../menu.php");

$fmts = array(
'VERT G4 TAB RG TAB ACT / MAX TAB (PROZ)',
'HOR G2 RG BR AK / MK TAB',
'HOR RT BR AK / MK',
'HOR RG (PROZ) BR AK',
'VERT RG RN G4 (PROZ) ACT / MAX',
'HOR G2 RG AK TAB',
);


/*
  for ($i=0;$i<=100;$i++)
  {
    $c=getacolor($i,1.5,-60);
    echo '<div style="background-color:'.$c.'"><tt>'.$i.' '.$c.'</tt></div>';
  }
*/
  
?>

<h1>Info</h1>
Hier kann man die Darstellung der Ressourcen unter dem Menu verändern. Das ganze
wird über einen auf den 1. Blick recht kryptischen Code gemacht. Wenn man nicht damit klar kommt,
kein Problem, in dem DropDownMenu sind ein paar gute Beispiele, die man verwenden kann.
Mehr Infos hierzu sind auch <a href="http://zwischenwelt.milchkind.net/zwwiki/index.php/Setup-FAQ#Lagerf.C3.BCllstandsanzeige_individualisieren" target="_blank">hier</a> im Wiki.
<h1>Einstellungen</h1>
<form method=post action="<?=sessionLink("showressource.php")?>">
Format: <input size=64 type=text id=fmt name=fmt value="<?=GetUserValue($gUser->id,"resformat",kDefaultResFormat)?>">
<br>
Vorschläge/Beispiele: <select onchange="document.getElementById('fmt').value=this.options[this.selectedIndex].value">
<?php foreach($fmts as $fmt)echo '<option>'.$fmt.'</option>'; ?>
</select>
<input type=submit name=ok value=ok>
</form>

<h1>Aktuelles Format mit gleichmäßigen Füllungen:</h1>
Code: <?=GetUserValue($gUser->id,"resformat",kDefaultResFormat)?><br>
<?php drawRessources($gUser,GetUserValue($gUser->id,"resformat",kDefaultResFormat),false); ?>

<h1>Referenz</h1>
<pre>
HOR   Horizontal
VERT  Vertikal (Alias VER)
BR    Umbruch
TAB   Tabulator, Ausrichten
RN    Name der Ressource als Text (Alias: RT, falls ihr euch das besser merken könnt)
RG    Grafik der Ressource (Alias: RI für Ressource Image)
AX    Aktuell Exakt (Alias: ACT)
AK    Aktuell auf 1000 gerundet - also 1k statt 1,000
AS    Aktuell gerundet - kurze Anzeige
MX    Maximal Exakt (Alias: MAX)
MK    Maximal auf 1000 gerundet - also 1k statt 1,000
MS    Maximal gerundet - kurze Anzeige
T1    Text mit % Zeichen  (Alias: PROZ)
T2    Text mit % Zeichen mit Textfarbe (Alias: TCOL für Text Colored)
T3    Text mit % Zeichen mit Hintergrundfarbe (Alias TB Text Background)
G1    Füllicon, breit, knalliger Farbverlauf
G2    Füllicon, halbbreit, knalliger Farbverlauf
G3    Füllicon, halbbreit, dezenter Farbverlauf
G4    Füllicon, halbbreit, grauer Balken
G5    Füllicon, breit, knalliger Farbverlauf
G6    Füllblase
G7    Füllblase (tech)
HR    Horizontaler Trennstrich (richtet auch aus wie TAB)
SEP   Horizontaler Trennstrich etwas breiter aber blasser (richtet auch aus wie TAB)
AXC   Aktuell Exakt Textfarbe
AKC   Aktuell Kilo Textfarbe
ASC   Aktuell Short Textfarbe
AXB   Aktuell Exakt Hintergrundfarbe
AKB   Aktuell Kilo Hintergrundfarbe
ASB   Aktuell Short Hintergrundfarbe
MXC   Maximal Exakt Textfarbe
MKC   Maximal Kilo Textfarbe
MSC   Maximal Short Textfarbe
MXB   Maximal Exakt Hintergrundfarbe
MKB   Maximal Kilo Hintergrundfarbe
MSB   Maximal Short Hintergrundfarbe

Beispiel: HOR RG RT BR G4 T1 BR XK/ZK
</pre>

</body>
</html>
<?php profile_page_end(); ?>
