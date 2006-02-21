<?php
require_once("lib.spells.php");
require_once("lib.main.php");
require_once("lib.army.php");
require_once("lib.technology.php");
require_once("lib.spells.php");


/*
$f_tower = sqlgetone("SELECT `id` FROM `building` WHERE `user` = ".intval($gUser->id)." ORDER BY `mana` DESC");
//print_r($candospells);
*/

function MagicListSortedTowers ($userid=0,$firstonly=false) {
	global $gUser;
	if ($userid == 0) $userid = $gUser->id;
	$r = sqlgettable("SELECT `building`.*,`unit`.`amount` as `mages` FROM `building`,`unit` WHERE 
		`building`.`type` = ".kBuilding_MagicTower." AND
		`building`.`user` = ".intval($userid)." AND
		`unit`.`type` = ".kUnitType_TowerMage." AND
		`building`.`id` = `unit`.`building`
		ORDER BY FLOOR(`mana` / 50) DESC, FLOOR(`unit`.`amount` / 20) DESC, `mana` DESC
		".($firstonly?" LIMIT 1":""));
	return $r;
}


function GetMagicCastingBox ($x,$y) {
	global $gRes,$gUser,$gBuildingType,$gTechnologyGroup,$gBuildingType,$gGlobal;
	global $f_tower,$f_full_tower_list;
	if (!isset($f_full_tower_list)) $f_full_tower_list = false;
	
	$towers = MagicListSortedTowers($gUser->id,$f_full_tower_list?false:true);
	if (count($towers) == 0) return false;
	
	$candospells = GetPossibleSpells();
	$basemana = $gBuildingType[$gGlobal['building_runes']]->basemana;
	$columns = 4;
	
	rob_ob_start();
	?>
	<?php ImgBorderStart("s1","jpg","#ffffee","",32,33); ?>
	<h4>Zauber auf <?="(".$x."/".$y.")"?></h4>
	<table border=1 cellspacing=0>
		<FORM name="spellform" METHOD="POST" ACTION="<?=Query("?sid=?&x=?&y=?")?>">
			<INPUT TYPE="hidden" NAME="do" VALUE="cast_spell">
		<tr>
			<td colspan=14>
				<table>
				<tr>
					<?php for ($i=0;$i<min($columns,count($towers));++$i) {?>
					<th></th><th>Mana</th><th colspan=2 align="left">Magier</th>
					<?php } // endif?>
				</tr>
				<tr>
					<?php
					$i=0;
					$maxmages = 0;
					foreach ($towers as $tower) {
						$mages = $tower->mages;
						$maxmages = max($maxmages,$mages);
						?>
						<td><INPUT TYPE="radio" NAME="tower" VALUE="<?=$tower->id?>" <?=(++$i==1)?"checked":""?>></td>
						<td><?=floor($tower->mana)."/".($basemana*($tower->level+1))?></td>
						<td><?=$mages?></td>
						<td><?=opos2txt($tower,'<img border=0 src="'.g($gBuildingType[kBuilding_MagicTower]->gfx).'">')?></td>
						<?php if (($i % $columns) == 0) {?>
						</tr><tr>
						<?php } // endif?>
					<?}?>
				</tr>
				</table>
				
				<?php if (!$f_full_tower_list) {?>
				<a href="<?=Query("?sid=?&x=?&y=?&full_tower_list=1")?>">(alle Türme auflisten)</a><br>
				<?php } // endif?>
				
			</td>
		</tr>
		<SCRIPT LANGUAGE="JavaScript" type="text/javascript">
		<!--
			function spelladd(num,name) {
				var input = document.getElementsByName("count"+name)[0];
				if (num == 0)
						input.value = 0;
				else	input.value = parseInt(input.value) + num;
			}
		//-->
		</SCRIPT>
		<?php
		foreach ($candospells as $key => $group){?>
			<tr>
				<th><?=cText::Wiki("MagieAnwendung",0,true)?></th>
				<th>&nbsp;S&nbsp;</th>
				<th>&nbsp;L&nbsp;</th>
				<th><img src="<?=g("res_mana.gif")?>" alt="mana" title="mana"></th>
				<th><img src="<?=g("sanduhrklein.gif")?>"></th>
				<th></th>
				<th></th>
				<th>Name</th>
				<?php foreach($gRes as $n=>$f)echo '<th><img src="'.g('res_'.$f.'.gif').'"></th>'; ?>
			</tr>
			<?
			foreach ($group as $spelltype){
				$spellobj = GetSpellInstance($spelltype->id);
			?>
				<?php $infourl = Query("?sid=?&x=?&y=?&infospelltype=".$spelltype->id);?>
				<tr>
					<td nowrap><input type=text size=3 name="count[<?=$spelltype->id?>]" value="0">
						<a href="javascript:spelladd(0,'[<?=$spelltype->id?>]')">0</a>
						<a href="javascript:spelladd(1,'[<?=$spelltype->id?>]')">+1</a>
						<a href="javascript:spelladd(5,'[<?=$spelltype->id?>]')">+5</a>
					</td>
					<th><?=$spellobj->GetDifficulty($spelltype,$maxmages,$gUser->id)?></th>
					<th><?=$spellobj->GetLevel($gUser->id)?></th>
					<td align="right"><?=$spelltype->cost_mana?></td>
					<td align="right" nowrap><?=Duration2Text($spelltype->basetime)?> *</td>
					<td><img src="<?=isset($gTechnologyGroup[$key])?g($gTechnologyGroup[$key]->gfx):g("res_mana.gif")?>"></td>
					<td><a href="<?=$infourl?>"><img border=0 src="<?=g($spelltype->gfx)?>"></a></td>
					<td nowrap><?=cText::Wiki("spell",$spelltype->id)?><a href="<?=$infourl?>"><?=$spelltype->name?></a>
						<?php if ($gUser->admin) {?>
						<a href="<?=query("adminspell.php?sid=?&id=".$spelltype->id)?>"><img alt=Admin title=Admin src="<?=g("icon/admin.png")?>" border=0></a>
						<?php } // endif?>	
					</td>
					<?php foreach($gRes as $n=>$f) echo '<td align="right">'.(($spelltype->{"cost_".$f} > 0)?ktrenner($spelltype->{"cost_".$f}):"").'</td>'; ?>
				</tr>
			<?}
		}?>
		<tr><td colspan=14 align=left><INPUT TYPE="submit" NAME="cast" VALUE="Cast"></td></tr>
		</FORM>
	</table>
	<?php ImgBorderEnd("s1","jpg","#ffffee",32,33); ?>
	<?php
	PrintMagicHelp();
	return rob_ob_end();
}

function PrintMagicHelp () {
	?>
	<?php ImgBorderStart("p2","jpg","#f2e7d5","",32,33); ?>
	*) die Zeitangabe ist die Basiszeit, die tatsaechliche Zeit haengt vom Level der Technologie und Zufallsfaktoren ab und kann bis doppelt so Lang sein.<br>
	<br>
	Je nach Erfolg bzw Fehlschlag können die Kosten für den Zauber höher oder niedriger ausfallen.<br>
	<br>
	"L" steht für das erforschte Level des Zaubers.<br>
	"S" steht für die Schwierigkeit des Zaubers, je höher, desto schwerer.<br>
	20 Turmzauberer senken die Schwierigkeit um eine Stufe,<br>
	0-10 Stufen kommen beim Zaubern durch Zufall noch drauf,<br>
	wenn die Schwierigkeit danach unter 9 ist, schafft man den Zauber.<br>
	wenn man den Zauber nicht schafft, besteht eine 10%tige Chance für einen Patzer, 
	ein solcher kann, je nach Zauber, neben einer Kostenerhöhung noch weitere böse Effekte mit sich bringen.<br>
	<table border=1 cellspacing=0>
	<tr><th>Ergebnis</th><th>Kosten</th><th>Mod</th></tr>
	<tr><td>&gt;9 : Patzer! (10%)</td>		<td>+120%</td>	<td>0.0</td></tr>
	<tr><td>&gt;9 : versagt</td>		<td>+70%</td>	<td>0.0</td></tr>
	<tr><td>&gt;5 : knapp geschafft</td><td>+30%</td>	<td>0.9</td></tr>
	<tr><td>&gt;3 : geschafft</td>		<td>+0%</td>	<td>1.0</td></tr>
	<tr><td>rest : gut geschafft</td>	<td>-20%</td>	<td>1.5</td></tr>
	</table>
	<?php ImgBorderEnd("p2","jpg","#f2e7d5",32,33); ?>
	<?php
}
?>