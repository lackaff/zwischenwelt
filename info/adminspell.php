<?php

require_once("../lib.main.php");
require_once("../lib.building.php");
require_once("../lib.army.php");
require_once("../lib.map.php");
require_once("../lib.tableedit.php");

AdminLock();


$techs = array(0=>"keine");
$techtab = sqlgettable("SELECT * FROM `technologytype` WHERE 1 ORDER BY `buildingtype`,`group`,`id`");
foreach ($techtab as $o) 
	$techs[$o->id] = "<img src='".g($o->gfx)."'>".addslashes($o->name)."[$o->id]";

$targets = array(0=>"kein",MTARGET_PLAYER=>"PLAYER",MTARGET_AREA=>"AREA",MTARGET_ARMY=>"ARMY");
	
$form = 
new cTableEditForm("?sid=?&id=$f_id","SpellType $f_id editieren",
	new cTableEditCols(array(
		new cTableEditRows(array(
			new cTableEditTextField("spelltype","id",$f_id,"Name","name"),
			new cTableEditTextArea("spelltype","id",$f_id,"Beschreibung","desc"),
			
			new cTableEditTextField("spelltype","id",$f_id,"Kosten: Holz","cost_lumber"),
			new cTableEditTextField("spelltype","id",$f_id,"Kosten: Stein","cost_stone"),
			new cTableEditTextField("spelltype","id",$f_id,"Kosten: Nahrung","cost_food"),
			new cTableEditTextField("spelltype","id",$f_id,"Kosten: Metall","cost_metal"),
			new cTableEditTextField("spelltype","id",$f_id,"Kosten: Runen","cost_runes"),
			new cTableEditTextField("spelltype","id",$f_id,"Kosten: Mana","cost_mana"),
			
			new cTableEditTimeField("spelltype","id",$f_id,"BaseTime","basetime"),
			new cTableEditTextField("spelltype","id",$f_id,"BaseRange","baserange"),
			new cTableEditTextField("spelltype","id",$f_id,"BaseEffect","baseeffect"),
			new cTableEditTextField("spelltype","id",$f_id,"BaseMod","basemod"),
			new cTableEditTextField("spelltype","id",$f_id,"OrderVal","orderval"),
			
			new cTableEditTextField("spelltype","id",$f_id,"Req: Geb","req_building"),
			new cTableEditTextField("spelltype","id",$f_id,"Req: Tech","req_tech"),
			
			new cTableEditIMGUrl("spelltype","id",$f_id,"Bild","gfx")
		)),
		new cTableEditRows(array(
			new cTableEditRadioField("spelltype","id",$f_id,"Target","target",$targets),
			new cTableEditRadioField("spelltype","id",$f_id,"Prim.Tech","primetech",$techs)
		))
	))
);


$form->HandleInput();
// regenerate typecache
require_once("../generate_types.php");
require(kTypeCacheFile);

require_once("header.php"); 
$form->Show();
echo '<td>	SYNTAX : type&gt;minlevel+inc  OR  type&lt;maxlevel+inc   inc can be float : "4&gt;5+0.5"  for id 4 at least level 5<br>
			</td>';

require_once("footer.php"); 

?>