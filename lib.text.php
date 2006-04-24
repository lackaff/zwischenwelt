<?php

define("kHelpIcon",g('help2.png'));

class cText {
	// platziert ein kleines fragezeichen, das auf eine wikiseite zeigt
	function Wiki ($topic,$typeid=0,$forceshow=false) {
		global $gUser,$gUnitType,$gBuildingType,$gTechnologyType,$gTerrainType,$gItemType;
		if($typeid>0)
		switch($topic){
			case "item":
				$topic.="#".$gItemType[$typeid]->name;
			break;
			case "unit":
				$topic.="#".$gUnitType[$typeid]->name;
			break;
			case "tech":
				$topic.="#".$gTechnologyType[$typeid]->name;
			break;
			case "terrain":
				$topic.="#".$gTerrainType[$typeid]->name;
			break;
			case "building":
				$topic.="#".$gBuildingType[$typeid]->name;
			break;
		}
		if(!$forceshow && intval($gUser->flags) & kUserFlags_DontShowWikiHelp) return "";
		else return " <a href='".kURL_Wiki.$topic."' target='_blank'><img src='".kHelpIcon."' border=0></a> ";
	}
	
	function UnitsList ($units,$userid,$header="",$drawcost=true,$armyidle=false) {
		global $gRes,$gUnitType,$gTerrainType,$gUser,$gMovableFlagMainTerrain;
		// $units ein array mit objekten, wie in lib.units.php, $o->cell = htmltext für die extra zelle
		// $userid is used for upraded a,v etc
		// $drawcost : costs only drawn if true
		// $armyidle is used for cooldown display for ranged weapons
		
		usort($units,"cmpUnit");
		
		$bgcolor = sqlgetone("SELECT `color` FROM `user` WHERE `id` = ".intval($userid));
		if (!$bgcolor) $bgcolor = "ffffff";
		
		$eff_img = array(
			"eff_sail"=>"<img src='".g("units/schiff-1.png")."' alt='Segeln' title='Segeln' border=0>",
			"eff_capture"=>"<img src='".g("units/entermatrose.png")."' alt='Entern' title='Entern' border=0>",
			"eff_fightondeck"=>"<img src='".g("units/marinematrose.png")."' alt='Seekampf' title='Seekampf' border=0>",
			"eff_siege"=>"<img src='".g("units/ramme.png")."' alt='Belagern' title='Belagern' border=0>",
		);
		
		$eff_show = array(); // ob die effizienz spalten überhaupt gezeigt werden
		foreach($eff_img as $field => $img)
				$eff_show[$field] = false;
		foreach($units as $o) if ($o->amount > 0)
			foreach($eff_img as $field => $img)
				if ($gUnitType[$o->type]->$field != 0)
					$eff_show[$field] = true;
		
		$display_f = false;
		//foreach($units as $o) if ($o->amount > 0 && $gUnitType[$o->type]->f > 0) $display_f = true;
		foreach($units as $o) if ($gUnitType[$o->type]->f > 0) $display_f = true;
		?>
			<table border=1 cellspacing=0>
			<tr>
				<th colspan=2>Einheit</th>
				<th>A</th>
				<th>V</th>
				<?php if ($display_f) {?>
				<th>Fernkampf</th>
				<?php } // endif?>
				<th>Speed</th>
				<th>Pl.</th>
				<th>Last</th>
				<th>Gew.</th>
				<th>Terrain</th>
				<?php foreach($eff_img as $field => $img) if ($eff_show[$field]) {?>
					<th><?=$img?></th>
				<?php } // endforeach?>
				<?php if ($drawcost) {?>
					<?php foreach($gRes as $n=>$f)echo '<th><img src="../gfx/res_'.$f.'.gif"></th>'; ?>
					<th><img src="<?=g("sanduhrklein.gif")?>"></th>
				<?php } // endif?>
				<th>&nbsp;</th>
				<th>hier</th>
				<th align="left"><?=$header?> <?=cText::Wiki("Werte",0,true)?></th>
			</tr>
			<?php foreach($units as $o) if ($o->amount >= 0) { $type = $gUnitType[$o->type]; ?>
				<tr>
					<?php $infourl = Query("?sid=?&x=?&y=?&infounittype=".$o->type);?>
					<td><a href="<?=$infourl?>"><img style="background-color:<?=$bgcolor?>" title="<?=strip_tags($type->descr)?>" alt="<?=strip_tags($type->descr)?>"  border="1" src="<?=g($type->gfx)?>"></a></td>
					<td nowrap>
						<?php if($gUser->admin){ ?>
							<a href="<?=query("adminunittype.php?id=".$o->type."&sid=?")?>"><img alt=Admin title=Admin src="<?=g("icon/admin.png")?>" border=0></a>
						<?php } ?>
						<?=cText::Wiki("unit",$o->type)?><a href="<?=$infourl?>"><?=$type->name?></a>
					</td>
					<td align=right><?=$type->a+cUnit::GetUnitBonus($o->type,$userid,"a")?></td>
					<td align=right><?=$type->v+cUnit::GetUnitBonus($o->type,$userid,"v")?></td>
					<?php if ($display_f) {?>
					<td align=right>
					<?php if ($type->f > 0) {?>
						<table border=0 cellspacing=0 cellpadding=0>
						<tr><td>Schaden</td>		<td align=right><?=$type->f+cUnit::GetUnitBonus($o->type,$userid,"f")?></td></tr>
						<tr><td>Reichweite</td>		<td align=right><?=$type->r?></td></tr>
						<tr><td>Ladezeit</td>		<td nowrap><?=round($type->cooldown/60,1)?> min</td></tr>
						<?php if ($armyidle !== false) {?>
						<tr><td>Nachladen</td><td align=right><?=(round(min(100,100*$armyidle/max(1,$type->cooldown)))."%")?></td></tr>
						<?php } // endif?>
						</table>
					<?php } else echo ""; ?>	
					</td>
					<?php } // endif?>
					<td align=right><?=$type->speed?></td>
					<td align=right><?=$type->pillage?></td>
					<td align=right><?=$type->last?></td>
					<td align=right><?=$type->weight?></td>
					<td align=left>
						<table><tr>
						<?php foreach ($gMovableFlagMainTerrain as $f => $ttid) if (intval($type->movable_flag) & $f) {?>
						<td><img src="<?=g($gTerrainType[$ttid]->gfx,"se")?>" alt="<?=$gTerrainType[$ttid]->name{0}?>" border=0></td>
						<?php } // endforeach?>
						</tr></table>
					</td>
					<?php foreach($eff_img as $field => $img) if ($eff_show[$field]) {?>
						<td align="right"><?=($type->$field!=0)?(($type->$field*100)."%"):""?></td>
					<?php } // endforeach?>
					<?php if ($drawcost) {?>
						<?php foreach($gRes as $n=>$f)echo '<td align=center>'.(($type->{"cost_".$f})?$type->{"cost_".$f}:"").'</td>'; ?>
						<td align=center><?=Duration2Text($type->buildtime)?></td>
					<?php } // endif?>
					<td><a href="<?=$infourl?>"><img style="background-color:<?=$bgcolor?>" title="<?=strip_tags($type->descr)?>" alt="<?=strip_tags($type->descr)?>"  border="1" src="<?=g($type->gfx)?>"></a></td>
					<td align=right><?=ktrenner(floor($o->amount))?></td>
					<td nowrap align="right"><?=isset($o->cell)?$o->cell:""?>
						<?php if ($o->spell && ($spell=sqlgetobject("SELECT * FROM `spell` WHERE `id` = ".intval($o->spell)))) {?>
						beschworen, noch <?=Duration2Text($spell->lasts-time())?>
						<?php } // endif?>
					</td>
				</tr>
			<?php }?>
			</table>
		<?php
	}
}

// todo : army_units2txt fight2txt pillage2txt siege2txt
// plaintext, format not reliable, for debugging only, don't scan, might change at any time
function army2txt ($army) {
	// name[id,u=user,size=size](x,y)
	if (!$army) return "no_army";
	return $army->name."[".$army->id.(isset($army->size)?(",size=".floor($army->size)):"").",u=".user2txt($army->user)."]"."($army->x,$army->y)"; 
}
function building2txt ($building) { 
	if (!$building) return "no_building";
	// type->name[u=user](x,y)
	global $gBuildingType;
	return $gBuildingType[$building->type]->name."[u=".user2txt($building->user)."]"."($building->x,$building->y)";
}
function user2txt ($user) { 
	// name[id,pop=bevölkerung]
	global $gAllUsers;
	if (!is_object($user))
		if ($user == 0) return "no_user";
		else if (isset($gAllUsers)) 
				$user = $gAllUsers[$user];
		else	$user = sqlgetobject("SELECT * FROM `user` WHERE `id` = ".intval($user));
	if (!$user) return "<font color='red'>user_not_found</font>";
	return $user->name."[".$user->id.",pop=".floor($user->pop)."]";
}
function item2txt ($item) {
	if (!$item) return "no_item";
	if ($item->army) { $army = sqlgetobject("SELECT * FROM `army` WHERE `id` = ".$item->army); $item->x = $army->x; $item->y = $army->y; }
	// name[id](a=army)
	// name[id](x,y)
	global $gItemType;
	return $gItemType[$item->type]->name."[type=".$item->type.",amount=".$item->amount.",param=".$item->param."]".(($item->army!=0)?("(a=".army2txt($army).")"):"($item->x,$item->y)");
}
function pos2txt ($x,$y,$text=false,$nosession=false) {
	if ($nosession) return ($text?$text:"($x,$y)");
	return "<a target='map' href='".Query("../".kMapScript."?sid=?&x=".$x."&y=".$y)."'>".($text?$text:"($x,$y)")."</a>";
}
function opos2txt ($o,$text=false,$nosession=false) {
	return pos2txt($o->x,$o->y,$text,$nosession);
}
function posinfolink ($x,$y,$text=false) {
	$infourl = Query(BASEURL."/info/info.php?sid=?&x=".$x."&y=".$y);
	$mapurl = Query(BASEURL."/".kMapScript."?sid=?&x=".$x."&y=".$y);
	if (!$text) $text = "($x,$y)";
	return "<a target='map' href='".$mapurl."'>".$text."</a> <a href='".$infourl."'>(info)</a>";
}
function oposinfolink ($o,$text=false) {
	return posinfolink($o->x,$o->y,$text);
}
function quest2txt ($quest) {
	if (!$quest) return "no_quest";
	global $gQuestTypeNames;
	return $quest->name."[".$quest->id.",t=".$gQuestTypeNames[$quest->type]."]"."($quest->x,$quest->y)";
}
function usermsglink ($user) { // obj or id
	if (!$user) return "admin";
	global $gUser;
	if (!is_object($user)) $user = sqlgetobject("SELECT `id`,`name` FROM `user` WHERE `id` = ".intval($user));
	return "<a href='".query("../info/msg.php?sid=?&show=compose&to=".urlencode($user->name))."'>".GetFOFtxt($gUser->id,$user->id,$user->name)."</a>";
}


function GetUserLink ($user,$guildinfo=true,$msglink=true,$specialcolor=false) {
	if ($user && !is_object($user)) $user = sqlgetobject("SELECT * FROM `user` WHERE `id` = ".intval($user)); 
	if (!$user) return false; // no fallback in here
	global $gUser;
	$hq = sqlgetobject("SELECT * FROM `building` WHERE `type` = ".kBuilding_HQ." AND `user` = ".$user->id);
	$guild = ($guildinfo && $user->guild)?sqlgetobject("SELECT * FROM `guild` WHERE `id` = ".$user->guild):false;
	$res = "";
	if ($msglink) $res .= '<a href="'.query("../info/msg.php?show=compose&to=".urlencode($user->name)."&sid=?").'"><img border=0 src="'.g("icon/guild-send.png").'"></a> ';
	if ($specialcolor)
			$res .= '<a style="color:'.$specialcolor.'" href="'.query("../info/info.php?sid=?&x=".$hq->x."&y=".$hq->y).'">'.($user->name).'</a> ';
	else	$res .= '<a href="'.query("../info/info.php?sid=?&x=".$hq->x."&y=".$hq->y).'">'.GetFOFtxt($gUser->id,$user->id,$user->name).'</a> ';
	if ($guild) $res .= '<a href="'.query("../info/viewguild.php?sid=?&id=".$guild->id).'">['.$guild->name.']</a> ';
	return $res;
}

// user false means no coloring
function cost2txt ($costarr,$user=false) {
	global $gRes;
	$out = "";
	$i=0;
	foreach ($gRes as $n=>$f) {
		$cost = $costarr?$costarr[$i++]:0;
		if ($cost <= 0) continue;
		$color = (!$user)?"black":(($user->{$f} >= $cost)?"green":"red");
		$out .= "<img src='".g("res_$f.gif")."' alt='$n' title='$n'><font color='$color'>".ktrenner($cost)."</font>";
	}
	if (empty($out)) $out = "0";
	return $out;
}
function BuildingAction2txt ($action) {
	global $gUnitType;
	switch ($action->cmd) {
		case kActionCmd_Build:
			return '<img class="picframe" src="'.g($gUnitType[$action->param1]->gfx).'">'.$action->param2;
		break;
	}
}
// text=false is replaced by building picture
function GetBuildingTypeLink ($type,$x,$y,$text=false,$user=false,$level=false,$url=true) {
	global $gObject,$gBuildingType;
	if ($level === false) $level = 10;
	if (!is_object($type)) $type = $gBuildingType[$type];
	if (!$text) $text = "<img border=0 src=\"".GetBuildingPic($type,$user,$level)."\" alt=\"".$type->name."\" title=\"".$type->name."\">";
	if ($url === false) return $text;
	$url = Query("info.php?sid=?&x=".$x."&y=".$x."&infobuildingtype=".$type->id);
	return "<a href=\"$url\">$text</a>";
}
// text=false is replaced by unit picture
function GetUnitTypeLink ($type,$x,$y,$text=false,$user=false) {
	global $gObject,$gUnitType;
	if (!is_object($type)) $type = $gUnitType[$type];
	if (!$text) $text = "<img border=0 src=\"".g($type->gfx)."\" alt=\"".$type->name."\" title=\"".$type->name."\">";
	$url = Query("info.php?sid=?&x=".$x."&y=".$x."&infounittype=".$type->id);
	return "<a href=\"$url\">$text</a>";
}
// text=false is replaced by terrain picture
function GetTerrainTypeLink ($type,$x,$y,$text=false,$user=false) {
	global $gObject,$gTerrainType;
	if (!is_object($type)) $type = $gTerrainType[$type];
	if (!$text) $text = "<img border=0 src=\"".g($type->gfx)."\" alt=\"".$type->name."\" title=\"".$type->name."\">";
	return "$text";
}
// text=false is replaced by item picture
function GetItemTypeLink ($type,$x,$y,$text=false,$user=false) {
	global $gObject,$gItemType;
	if (!is_object($type)) $type = $gItemType[$type];
	if (!$text) $text = "<img border=0 src=\"".g($type->gfx)."\" alt=\"".$type->name."\" title=\"".$type->name."\">";
	return "$text";
}

//replaces plain text with sweet html sweeties
//can replace (kUNITID) with the kill of the gUser
function magictext($text) {
	// positions
	$text = ereg_replace("\\(([-+0-9]+),([-+0-9]+)\\)","<a target='map' href='".Query("../".kMapScript."?sid=?")."&x=\\1&y=\\2'>(\\1,\\2)</a>",$text);
	// items
	global $gItemType;
	foreach ($gItemType as $o)
		$text = str_replace("(i".$o->id.")",'<img src="'.g($o->gfx).'" alt=".">',$text);
	// units
	global $gUnitType;
	foreach ($gUnitType as $o)
		$text = str_replace("(u".$o->id.")",'<img src="'.g($o->gfx).'" alt=".">',$text);
	// kills
	global $gUser;
	if($gUser->id>0){
		$kills = sqlgettable("SELECT * FROM `userkills` WHERE `user`=".intval($gUser->id));
		foreach ($kills as $o)
			$text = str_replace("(k".$o->unittype.")",floor($o->kills),$text);
	}
	return $text;
}

function speedformat($speed)
{
  if ($speed==0) $speed=1; // division by zero
  $fph=round(3600/$speed);
  echo '<span title="entspricht '.$fph.' Feldern pro Stunde">';
  echo '<img src="gfx/sanduhrklein.gif">&nbsp;'.$fph.'</span>';
} // speedformat()

?>
