<?php
require_once("lib.main.php");
//if ( extension_loaded('zlib') )ob_start('ob_gzhandler');
header('Content-Type: text/javascript');
//header('Last-Modified: '.date("r",time()-60*60*24*7));
//header('Cache-Control: max-age='.(60*60*24*7).', must-revalidate');

?>
kDefaultTerrainID = <?=kTerrain_Grass?>;
kNWSE_N = <?=kNWSE_N?>;
kNWSE_W = <?=kNWSE_W?>;
kNWSE_S = <?=kNWSE_S?>;
kNWSE_E = <?=kNWSE_E?>;
kConstructionPlanPic = "<?=kConstructionPlanPic?>";
kConstructionPic = "<?=kConstructionPic?>";
kTransCP = "<?=kTransCP?>"; // transparent construction plan


//kJSMapTileSize = <?=kMapTileSize?>;
kJSMapTileSize = 27;

function UnitTypeHasNWSE (unittype) {
	return unittype == <?=kUnitType_HyperBlob?>;
}

<?php

// prints a javascript function to create a js-object from its parameters
// if global_arr_name is false, the object is returned, otherwise it is added to the end of the array (global js var)
// if indexfield is false, then the object is added to the end of the array
// used for exporting object with string fields to javascript (users,armies,type-arrays)
function php2js_objectfunction ($function_name,$fields,$global_arr_name=false,$index_field=false) {
	if (!is_array($fields)) $fields = explode(",",$fields);
	?>
	function <?=$function_name?> (<?=implode(",",$fields)?>) {
		var res = new Object();
		<?php foreach ($fields as $field) echo "res.".$field." = ".$field.";";?>
		<?php if ($global_arr_name) {?>
		<?=$global_arr_name?>[<?=$index_field?$index_field:($global_arr_name.".length")?>] = res;
		<?php } else {?>
		return res;
		<?php }?>
	}
	<?php 
}

// prints a javascript function
// for parsing arrays of objects, that have been passed to javascript as comma seperated list
function php2js_parser ($function_name,$fields,$globalarr,$sep_obj=";",$sep_val=",") {
	if (!is_array($fields)) $fields = explode(",",$fields);
	?>
	function <?=$function_name?> () {
		var i;
		<?=$globalarr?> = <?=$globalarr?>.split("<?=addslashes($sep_obj)?>");	
		<?=$globalarr?>.pop();
		for (i in <?=$globalarr?>) {
			<?=$globalarr?>[i] = <?=$globalarr?>[i].split("<?=addslashes($sep_val)?>");
			var res = new Object();
			<?php $i = 0; foreach ($fields as $field) echo "res.".$field." = ".$globalarr."[i][".($i++)."];";?>
			<?=$globalarr?>[i] = res;
		}
	}
	<?php 
}

// produces array full of objects, can be accessed using "gTerrainType[12].gfx"
// for exporting type-arrays to javascript
function php2js_objarray ($name,$arr,$fields) {
	if (!$fields) return;
	if (!is_array($fields)) $fields = explode(",",$fields);
	php2js_objectfunction("entry_".$name,$fields);
	echo $name." = new Array();\n";
	foreach ($arr as $key => $o) {
		$objfields = obj2arr($o);
		$values = array();
		foreach ($fields as $fieldname) {
			$value = $o->{$fieldname};
			if (is_array($value)) $values[] = "'".addslashes(implode(",",$value))."'"; // warning, not suitable for fancy values
			else if (is_numeric($value)) 
					$values[] = $value;
			else	$values[] = "'".addslashes($value)."'";
		}
		echo $name."[".$key."] = entry_".$name."(".implode(",",$values).");\n";
	}
}

php2js_objarray("gTerrainType",$gTerrainType,"name,speed,buildable,gfx,mod_a,mod_v,mod_f,movable_flag,connectto_terrain,connectto_building");
php2js_objarray("gBuildingType",$gBuildingType,"name,maxhp,speed,gfx,mod_a,mod_v,mod_f,connectto_terrain,connectto_building,neednear_building,require_building,exclude_building,border,movable_flag,movable_override_terrain");
php2js_objarray("gUnitType",$gUnitType,"name,orderval,a,v,f,r,speed,gfx");
php2js_objarray("gItemType",$gItemType,"name,gfx");
// bodenschaetze (ressources,perks,specials,deposit...)

php2js_objectfunction("jsUser","id,guild,color,name","gUsers","id");
php2js_objectfunction("jsArmy","id,x,y,name,type,user,units,items,jsflags","gArmies","id");
php2js_parser("jsParseBuildings","x,y,type,user,level,hp,construction,jsflags","gBuildings");
php2js_parser("jsParseItems","x,y,type,amount","gItems");
php2js_parser("jsParsePlans","x,y,type,priority","gPlans");


/* // css notes

 ***** OLD-TABS ***** 
BACKGROUND-COLOR : #efd39c;
BACKGROUND-COLOR : #deb67b;

display:Wert;
Für Wert einen der folgenden Werte notieren.
block = Erzwingt einen Block - das Element erzeugt eine neue Zeile.
inline = Erzwingt die Anzeige im Text - das Element wird im laufenden Textfluss angezeigt.
list-item = wie block, jedoch mit einem Aufzählungszeichen (Bullet) davor.
marker = deklariert automatisch generierten Text für das Element.
run-in und compact = bewirken, dass das Element kontext-abhängig als Block-Element oder als Inline-Element dargestellt wird.
none = Element wird nicht angezeigt und es wird auch kein Platzhalter freigelassen.

visibility:hidden;
visibility:visible;
Für Wert einen der folgenden Werte notieren:
hidden = Der Inhalt des Element wird zunächst versteckt (nicht angezeigt).
visible = Der Inhalt des Element wird zunächst angezeigt (Normaleinstellung).

line-height:0pt; font-size:0pt; font-weight:0;
clip:rect(Wert1 Wert2 Wert3 Wert4);

cursor:Wert;
Zugeordnetes Element erhält beim Überfahren mit der Maus einen anderen Cursor. Für Wert einen der folgenden Werte notieren:
auto = automatischer Cursor (Normaleinstellung).
default = Plattformunabhängiger Standard-Cursor.
crosshair = Cursor in Form eines einfachen Fadenkreuzes.
pointer = Cursor in Form eines Zeigers.
move = Cursor in Form eines Kreuzes, das die Fähigkeit zum Bewegen des Elements signalisiert.
n-resize = Cursor in Form Pfeils, der nach oben zeigt (n = Norden).
ne-resize = Cursor in Form Pfeils, der nach rechts oben zeigt (ne = Nordost).
e-resize = Cursor in Form Pfeils, der nach rechts zeigt (e = Osten).
se-resize = Cursor in Form Pfeils, der nach rechts unten zeigt (se = Südost).
s-resize = Cursor in Form Pfeils, der nach unten zeigt (s = Süden).
sw-resize = Cursor in Form Pfeils, der nach links unten zeigt (sw = Südwest).
w-resize = Cursor in Form Pfeils, der nach links zeigt (w = Westen).
nw-resize = Cursor in Form Pfeils, der nach links oben zeigt (nw = Nordwest).
text = Cursor in einer Form, die normalen Text symbolisiert.
wait = Cursor in Form eines Symbols, das einen Wartezustand signalisiert.
help = Cursor in Form Symbols, das Hilfe zu dem Element signalisiert.
url([URI]) = Beliebiger Cursor, URI sollte eine GIF- oder JPG-Grafik sein.

.tabs A:link, 
.tabs A:visited,
.tabs A:active, 
.tabs A:hover
*/
?>