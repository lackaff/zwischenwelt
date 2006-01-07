<?php 
require_once("../lib.main.php");
require_once("../lib.army.php");



$gClassName = "cInfoLager";
class cInfoLager extends cInfoBuilding {
	function mycommand () {
		foreach ($_REQUEST as $name=>$val) ${"f_".$name} = $val;
		global $gObject;
		global $gUser;
		global $gRes;
		global $gRes2ItemType;
	
		if ($gObject->type != kBuilding_Silo) return;
		switch ($f_do) {
			case "transfer":
				require_once("../lib.army.php");
				foreach ($f_transfer as $armyid => $val)
				{
					//if (isset($val[0])) echo "abheben<br>";
					//if (isset($val[1])) echo "einzahlen<br>";
					if (isset($val[0]) && $gUser->id != $gObject->user) continue;
					if (isset($val[2]) && $gUser->id != $gObject->user) continue;
					
					$army = sqlgetobject("SELECT * FROM `army` WHERE `id`=".intval($armyid));
					foreach($gRes as $n=>$f)${$f} = abs(intval(${"f_".$f}[$army->id]));
					
					if (isset($val[2])) {
						// alles abheben
						foreach($gRes as $n=>$f) $$f = floor($gUser->{$f});
					}
					if (isset($val[3])) {
						// alles einzahlen
						foreach($gRes as $n=>$f) $$f = floor($gUser->{"max_".$f} - $gUser->{$f});
					}
					
					if(isset($f_get))$faktor = 1;
					else $faktor = -1;
					
					if (cArmy::CanControllArmy($army,$gUser) && cArmy::ArmyAtDiag($army->id,$gObject->x,$gObject->y)) {
						$sig = (isset($val[0]) || isset($val[2])) ? 1 : -1;
						cArmy::ArmyGetRes($army->id,$gObject->user,$sig*$lumber,$sig*$stone,$sig*$food,$sig*$metal,$sig*$runes);
					}
				}
				$gUser = sqlgetobject("SELECT * FROM `user` WHERE `id` = ".$gUser->id);
			break;
		}
	}
	
	
	function mygenerate_tabs() {
		if ($this->construction > 0) return;
		foreach ($_REQUEST as $name=>$val) ${"f_".$name} = $val;
		global $gUser;
		global $gObject;
		global $gItemType;
		global $gSpellType;
		global $gRes;
		global $gResTypeNames;
		global $gResTypeVars;
		global $gTechnologyType;
		global $gTechnologyGroup;
		
		$gc=getGuildCommander();
		profile_page_start("lager.php");
		$gArmies = cArmy::getMyArmies(false,$gUser);
		rob_ob_start();
		?>
		
		<?php if (count($gArmies) > 0) {?>
			<?php /* ***** PILLAGE ***** */ ?>
			<form method="post" action="<?=Query("?sid=?&x=?&y=?&do=lageraction&target=".$gObject->id)?>">
			<?php $b = 1;foreach($gRes as $n=>$f){ echo'<INPUT TYPE="checkbox" NAME="restype[]" VALUE="'.$b.'" checked><img alt="'.$f.'" src="'.g("res_$f.gif").'">'; $b = $b << 1; } ?>
			mit <SELECT NAME="army">
			<?php foreach($gArmies as $o) if(cArmy::hasPillageAttack($o->id)) {?>
				<OPTION VALUE="<?=$o->id?>" <?=($o->id == $gUser->lastusedarmy)?"selected":""?>><?=$o->name?> (<?=$o->owner?>)</OPTION>
			<?php }?>
			</SELECT>
			<input type="submit" name="pillage" value="plündern">
			<input type="submit" name="deposit" value="einzahlen">
			</form>
			
			<?php /* ***** armyactions ***** */ ?>
			<?php foreach ($gArmies as $o) if (sqlgetone("SELECT 1 FROM `armyaction` WHERE `cmd` = ".ARMY_ACTION_PILLAGE." AND `army` = ".$o->id." AND `param1` = ".$gObject->x." AND `param2` = ".$gObject->y)) {?>
				<font color="red">soll mit <?=pos2txt($o->x,$o->y,$o->name)?> geplündert werden</font><br>
			<?php }?>
			<?php foreach ($gArmies as $o) if (sqlgetone("SELECT 1 FROM `armyaction` WHERE `cmd` = ".ARMY_ACTION_DEPOSIT." AND `army` = ".$o->id." AND `param1` = ".$gObject->x." AND `param2` = ".$gObject->y)) {?>
				<font color="green">hier soll mit <?=pos2txt($o->x,$o->y,$o->name)?> eingezahlt werden</font><br>
			<?php }?>
		<?php }?>
		
		<?php 
		$pillages = sqlgettable("SELECT * FROM `pillage` WHERE `building`= ".$gObject->id);
		if (count($pillages) > 0)
		{?>
			<br>
			<?php foreach($pillages as $pillage) {?>
			<?php $enemy = sqlgetobject("SELECT * FROM `army` WHERE `id`=".$pillage->army);?>
			<span style="color:red">wird gerade von
				<?php $ownername = sqlgetone("SELECT `name` FROM `user` WHERE `id` = ".$enemy->user);?>
				<a href="<?=query("msg.php?sid=?&show=compose&to=".urlencode($ownername))?>"><?=$ownername?></a>
				mit
				<a href="<?=Query("?sid=?&x=".$enemy->x."&y=".$enemy->y)?>">'<?=$enemy->name?>'</a> geplündert
			</span><br>
			<?php }?>
		<?php }?>
		
		
		<?php
			$gDockedArmies = array();
			foreach ($gArmies as $army)
				if (cArmy::ArmyAtDiag($army,$gObject->x,$gObject->y))
					$gDockedArmies[] = $army;
		?>
		<?php if (count($gDockedArmies) > 0) {?>
		<form method="post" action="<?=query("?sid=?&x=?&y=?")?>">
		<INPUT TYPE="hidden" NAME="building" VALUE="lager">
		<INPUT TYPE="hidden" NAME="id" VALUE="<?=$gObject->id?>">
		<INPUT TYPE="hidden" NAME="do" VALUE="transfer">
		<table>
			<tr>
				<?php foreach($gRes as $n=>$f)echo '<td align="center"><img alt="'.$f.'" src="'.g("res_$f.gif").'"></td>'; ?>
				<td align="left"><img alt="army" src="<?=g("army.png")?>"></td>
			</tr>
			<?php foreach ($gDockedArmies as $army) {?>
			<tr>
				<?php foreach($gRes as $n=>$f)echo '<td><input value="'.floor($army->$f).'" type="text" style="width:40px" name="'.$f.'['.$army->id.']"></td>'; ?>
				<td align="left"><?=$army->name?></td>
				<?php if (cArmy::CanControllArmy($army,$gUser)) {?>
				<td><input type="submit" name="transfer[<?=$army->id?>][0]" value="abheben"></td>
				<td><input type="submit" name="transfer[<?=$army->id?>][2]" value="alles abheben"></td>
				<?php }?>
				<td><input type="submit" name="transfer[<?=$army->id?>][1]" value="einzahlen"></td>
				<td><input type="submit" name="transfer[<?=$army->id?>][3]" value="lager füllen"></td>
			</tr>
			<?php }?>
		</table>
		</form>
		<?php }?>
		<?php profile_page_end(); 
		
		RegisterInfoTab("Waren",rob_ob_end(),10);
	}
}?>