<?php
//requirements

// TODO : INCREMENT IMPLEMENTIEREN !

class cInfoReq {
	function PrintHeader () {  ImgBorderStart("s1","jpg","#ffffee","",32,33); }
	function PrintFooter () {  ImgBorderEnd("s1","jpg","#ffffee",32,33); echo "<hr>"; }
	
	// setzt eine mini-tabelle mit einem bild links und  (min/max über einer abs($level)) rechts   zusammen
	// rechnet aus ob das level erfüllt ist, und färbt dann grün bzw rot
	// entweder $ttype oder $btype oder keines von beiden gesetzt
	function LevelMinMaxTable ($pic,$level,$ismax,$ttype=false,$btype=false) {
		global $gUser;
		$level = abs($level);
		$curlevel = $ttype?GetTechnologyLevel($ttype,$gUser->id):($btype?GetMaxBuildingLevel($btype,$gUser->id):false);
		$col = ($ttype||$btype)?( (($ismax)?($curlevel <= $level):($curlevel >= $level)) ? "green" : "red" ):false;
		$colstart = $col?"<font color='$col'>":"";
		$colend = $col?"</font>":"";
		return "<table align='left' cellpadding=0 cellspacing=0><tr><td rowspan=2>$pic</td><td>".
			$colstart.(($ismax)?("max"):("min")).$colend."</td></tr><td>".
			$colstart.($level).$colend."</td></tr></table>";
	}
	
	// entweder $ttype oder $btype oder keines von beiden gesetzt
	function	PrintRequirements ($minlevel,$maxlevel,$req_geb,$req_tech,$ttype=0,$btype=0) {
		global $gRes;
		global $gBuildingType;
		global $gTechnologyType;
		global $gUnitType;
		global $gSpellType;
		global $gUser;
		$o = $ttype?$gTechnologyType[$ttype]:($btype?$gBuildingType[$btype]:false);
		$costpre = $ttype?"basecost_":($btype?"cost_":false);
		$mytypeid = $ttype?$ttype:($btype?$btype:0);
		$req_geb = ParseReq($req_geb);
		$req_tech = ParseReq($req_tech);
		// vardump2($req_tech);
		// calculate interdepance
		$where = array(); // wo kann diese forschung erforscht werden ?
		$mytechs = array(); // was kann hier erforscht werden ?
		$need = array(); // das wird benötigt
		$enables = array();
		$disables = array();
		$enables_techs = array();
		foreach ($gBuildingType as $x) if (!$x->special) {
			// Gebäude
			$reqs = ParseReq($ttype?$x->req_tech:$x->req_geb);
			$add = '<a href="'.Query("?sid=?&x=?&y=?&infobuildingtype=".$x->id).'"><img class="picframe" alt="." src="'.g($x->gfx,"ns",1,$gUser->race).'"></a>';
			if ($o) foreach ($reqs as $req) if ($req->type == $mytypeid) {
				if (!$req->ismax) {
					if (!isset($enables[$req->level])) $enables[$req->level] = array();
					$enables[$req->level][] = $add;
				} else {
					if (!isset($disables[$req->level+1])) $disables[$req->level+1] = array();
					$disables[$req->level+1][] = $add;
				}
			}
			foreach ($req_geb as $req) if ($req->type == $x->id)
				$need[] = cInfoReq::LevelMinMaxTable($add,$req->level,$req->ismax,0,$x->id);
			if ($ttype) if ($o->buildingtype == $x->id)
				$where[] = cInfoReq::LevelMinMaxTable($add,$o->buildinglevel,false,0,$x->id);
		}
		foreach ($gTechnologyType as $x) {
			// Technologien
			$reqs = ParseReq($ttype?$x->req_tech:$x->req_geb);
			$add = '<a href="'.Query("?sid=?&x=?&y=?&infotechtype=".$x->id).'"><img border=0 alt="." src="'.g($x->gfx).'"></a>';
			if ($o) foreach ($reqs as $req) if ($req->type == $mytypeid) {
				if (!$req->ismax) {
					if (!isset($enables[$req->level])) $enables[$req->level] = array();
					$enables[$req->level][] = $add;
					$enables_techs[] = $x->id;
				} else {
					if (!isset($disables[$req->level+1])) $disables[$req->level+1] = array();
					$disables[$req->level+1][] = $add;
				}
			}
			foreach ($req_tech as $req) if ($req->type == $x->id)
				$need[] = cInfoReq::LevelMinMaxTable($add,$req->level,$req->ismax,$x->id,0);
			if ($btype) if ($o->id == $x->buildingtype)
				$mytechs[] = cInfoReq::LevelMinMaxTable($add,$x->buildinglevel,false,0,$x->buildingtype);
		}
		if ($o) foreach ($gUnitType as $x) {
			// Einheiten
			if($x->flags & kUnitFlag_Elite) continue;
			$reqs = ParseReq($ttype?($x->req_tech_a.",".$x->req_tech_v):$x->req_geb);
			$add = '<a href="'.Query("?sid=?&x=?&y=?&infounittype=".$x->id).'"><img border=0 alt="." src="'.g($x->gfx).'"></a>';
			foreach ($reqs as $req) if ($req->type == $mytypeid) {
				if (!$req->ismax) {
					if (!isset($enables[$req->level])) $enables[$req->level] = array();
					$enables[$req->level][] = $add;
				} else {
					if (!isset($disables[$req->level+1])) $disables[$req->level+1] = array();
					$disables[$req->level+1][] = $add;
				}
			}
		}
		if ($o) foreach ($gSpellType as $x) {
			// Zauber
			$reqs = ParseReq($ttype?$x->req_tech:$x->req_building);
			$add = '<a href="'.Query("?sid=?&x=?&y=?&infospelltype=".$x->id).'"><img border=0 alt="." src="'.g($x->gfx).'"></a>';
			foreach ($reqs as $req) if ($req->type == $mytypeid) {
				if (!$req->ismax) {
					if (in_array($x->primetech,$enables_techs)) continue;
					if (!isset($enables[$req->level])) $enables[$req->level] = array();
					$enables[$req->level][] = $add;
				} else {
					if (!isset($disables[$req->level+1])) $disables[$req->level+1] = array();
					$disables[$req->level+1][] = $add;
				}
			}
		}
		ksort($enables);
		ksort($disables);
		?>
		<br>
		
		<?php /*Abhaengigkeiten*/?>
		<table border=0>
		<?php if (count($need) > 0) {?>
			<tr><th align="left" nowrap>Benötigt</th><td><?=implode("",$need)?></td></tr>
		<?php } // endforeach?>
		<?php if (count($where) > 0) {?>
			<tr><th align="left" nowrap>Erforschbar in</th><td><?=implode("",$where)?></td></tr>
		<?php } // endforeach?>
		<?php if (count($mytechs) > 0) {?>
			<tr><th align="left" nowrap>Forschungen</th><td><?=implode("",$mytechs)?></td></tr>
		<?php } // endforeach?>
		<?php foreach ($disables as $level => $arr) {?>
			<tr><th align="left" nowrap>Level <?=$level?> verhindert</th><td><?=implode("",$arr)?></td></tr>
		<?php } // endforeach?>
		<?php foreach ($enables as $level => $arr) {?>
			<tr><th align="left" nowrap>Level <?=$level?> ermöglicht</th><td><?=implode("",$arr)?></td></tr>
		<?php } // endforeach?>
		</table>
		
		<?php /*Kosten*/?>
		<table border=1 cellspacing=0>
		<tr>
			<?php if ($maxlevel > 0) {?> 	<th>Level</th> <?php } // endif?>
			<?php if ($o) foreach($gRes as $n=>$f) {?><th><img src="<?=g('res_'.$f.'.gif')?>"></th> <?php } // endforeach?>
			<?php if ($o) {?> <th align="center"><img src="<?=g("sanduhrklein.gif")?>"></th> <?php } // endif?>
		</tr>
		<?php for ($i=$minlevel;$i<=$maxlevel;++$i) {?>
		<tr>
			<?php 
				if ($btype) {
					$upmod = cBuilding::calcUpgradeCostsMod($i+1); 
					$time = cBuilding::calcUpgradeTime($o,$i+1);
				} else if ($ttype) {
					$upmod = ($i-1)*$o->increment + 1.0;
					$time = $upmod*$o->basetime;
				}
			?>
			<?php if ($maxlevel > 0) {?>	<td align=center><?=$i?></td> <?php } // endif?>
			<?php if ($o) foreach($gRes as $n=>$f) {?> <td align="right">&nbsp;<?=round($upmod * $o->{$costpre.$f},0)?></td> <?php } // endforeach?>
			<?php if ($o) {?> <td align="right">&nbsp;<?=Duration2Text($time)?></td> <?php } // endif?>
		</tr>
		<?php } // endfor?>
		</table>
		<?php
	}
	
	function	PrintTechnology	($type) { 
		cInfoReq::PrintHeader();
		global $gUser,$gTechnologyType,$gTechnologyGroup,$gSpellType;
		$o = $gTechnologyType[$type];
		
		// override wikilink for spells
		$wikilink = cText::Wiki("tech",$o->id,true);
		foreach ($gSpellType as $spell) 
			if ($spell->primetech == $o->id) 
				$wikilink = cText::Wiki("spell",$spell->id,true);
				
		?><img src='<?=g($o->gfx)?>'> <?=$wikilink?> <?=$o->name?> 
			(Forschung, Level <?=GetTechnologyLevel($o->id,$gUser->id)?>/<?=$o->maxlevel?>)<?
		cInfoReq::PrintRequirements(1,$o->maxlevel,$o->req_geb,$o->req_tech,$o->id,0);
		cInfoReq::PrintFooter();
		foreach ($gSpellType as $spell) 
			if ($spell->primetech == $o->id) 
				cInfoReq::PrintSpell($spell->id,true);
	}
	function	PrintBuilding	($type) {
		cInfoReq::PrintHeader();
		global $gUser,$gBuildingType,$gUnitType;
		$o = $gBuildingType[$type];
		$mytopbuilding = sqlgetobject("SELECT * FROM `building` WHERE `type` = ".$type." AND `user` = ".$gUser->id." ORDER BY `level` DESC LIMIT 1");
		?>
		<?=$mytopbuilding?("<a href='".Query("info.php?sid=?&x=".$mytopbuilding->x."&y=".$mytopbuilding->y)."'>"):""?>
		<img border=0 class="picframe" src="<?=g($o->gfx,"ns",1,$gUser->race)?>"> <?=cText::Wiki("building",$o->id,true)?> <?=$o->name?> 
		<?=$mytopbuilding?("</a>"):""?>
			(GebäudeTyp, <?=($mytopbuilding->level>=0)?("Level $mytopbuilding->level"):"noch nicht gebaut"?>)<?
		cInfoReq::PrintRequirements(0,20,$o->req_geb,$o->req_tech,0,$o->id);
		echo "...";
		?>
		<table border=1>
			<tr><th>keines darf angrenzen</th><th>min. eines muß in der Nähe sein</th><th>min. eines muß angrenzen</th></tr>
			<tr>
				<td><?php foreach($gBuildingType[$type]->exclude_building as $x){ ?>
				<img src="<?=g($gBuildingType[$x]->gfx)?>" title="<?=$gBuildingType[$x]->name?>" alt="<?=$gBuildingType[$x]->name?>">
				<?php } ?></td>
				<td><?php foreach($gBuildingType[$type]->neednear_building as $x){ ?>
				<img src="<?=g($gBuildingType[$x]->gfx)?>" title="<?=$gBuildingType[$x]->name?>" alt="<?=$gBuildingType[$x]->name?>">
				<?php } ?></td>
				<td><?php foreach($gBuildingType[$type]->require_building as $x){ ?>
				<img src="<?=g($gBuildingType[$x]->gfx)?>" title="<?=$gBuildingType[$x]->name?>" alt="<?=$gBuildingType[$x]->name?>">
				<?php } ?></td>
			</tr>
		</table>
		<?php
		cInfoReq::PrintFooter();
	}
	function	PrintUnit	($type) { 
		cInfoReq::PrintHeader();
		global $gUser,$gUnitType;
		$o = $gUnitType[$type];
		?><img src="<?=g($gUnitType[$o->id]->gfx)?>"> <?=cText::Wiki("unit",$o->id,true)?> <?=$o->name?> (Einheit)<?php
		cInfoReq::PrintRequirements(0,0,$o->req_geb,$o->req_tech_a.",".$o->req_tech_v);
		cInfoReq::PrintFooter();
	}
	function	PrintSpell	($type,$noredirect=false) { 
		global $gUser,$gSpellType;
		$o = $gSpellType[$type];
		if (!$noredirect && $o->primetech) { cInfoReq::PrintTechnology($o->primetech); return;}
		cInfoReq::PrintHeader();
		?><img src="<?=g($o->gfx)?>"> <?=cText::Wiki("spell",$o->id,true)?> <?=$o->name?> (Zauber)<?php
		cInfoReq::PrintRequirements(0,0,$o->req_building,$o->req_tech);
		require_once("lib.spells.php");
		$spellobj = GetSpellInstance($o->id);
		/*Kosten*/
		global $gRes;
		?>
		<table border=1 cellspacing=0>
		<tr>
			<th>&nbsp;S&nbsp;</th>
			<th>&nbsp;L&nbsp;</th>
			<th><img src="<?=g("res_mana.gif")?>"></th>
			<th><img src="<?=g("sanduhrklein.gif")?>"></th>
			<?php foreach($gRes as $n=>$f) {?><th><img src="<?=g('res_'.$f.'.gif')?>"></th> <?php } ?>
		</tr>
		<tr>
			<th><?=$spellobj->GetDifficulty($o,1,$gUser->id)?></th>
			<th><?=$spellobj->GetLevel($gUser->id)?></th>
			<td align="right"><?=$o->cost_mana?></td>
			<td align="right" nowrap><?=Duration2Text($o->basetime)?></td>
			<?php foreach($gRes as $n=>$f) echo '<td align="right">'.(($o->{"cost_".$f} > 0)?ktrenner($o->{"cost_".$f}):"").'</td>'; ?>
		</tr>
		</table>
		<?php
		cInfoReq::PrintFooter();
	}
}
?>