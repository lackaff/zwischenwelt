<?php
require_once("../lib.main.php");

define("kMarketplace_MinResOffer",1500);

function PrintItemTrade ($text) {
	global $gItemType;
	if (!is_array($text)) $text = explode2(",",":",$text);
	$res = array();
	foreach ($text as $arr) $res[] = '<img border=0 alt="'.$gItemType[$arr[0]]->name.'" title="'.$gItemType[$arr[0]]->name.'" src="'.g($gItemType[$arr[0]]->gfx).'">'.$arr[1];
	return implode("",$res);
}

function GetMaxTradeInArmyList ($armies,$tradetext) {
	if (!is_array($tradetext)) $tradetext = explode2(",",":",$tradetext);
	$max = 0;
	$maxarmy = false;
	foreach ($armies as $army) {
		$mymax = cItem::GetMaxTrade($army,$tradetext);
		if ($max < $mymax) {
			$max = $mymax;
			$maxarmy = $army;
		}
	}
	return array($max,$army);
}
	
$gClassName = "cInfoMarket";
class cInfoMarket extends cInfoBuilding {
	function mycommand () {
		foreach ($_REQUEST as $name=>$val) ${"f_".$name} = $val;
		global $gObject;
		global $gUser;
		global $gRes;
		global $gRes2ItemType;
		global $gResTypeVars;
		
		if ($gObject->type != kBuilding_Market) return;
		switch ($f_do) {
			case "do trade":
				MarketplaceAcceptTrade($f_offerid);
				Redirect(Query("?sid=?&x=?&y=?"));
			break;
			case "cancel trade":
				MarketplaceCancelTrade($f_offerid);
				Redirect(Query("?sid=?&x=?&y=?"));
			break;
			case "add trade":
				$gUser->max_res=0;
				foreach ($gRes as $f=>$n){
					$res="max_".$n;
					$gUser->max_res+=$gUser->{$res};
				}
				$gUser->max_res/50;
				sql("LOCK TABLES `phperror` WRITE , `user` WRITE, `sqlerror` WRITE, `marketplace` WRITE");
				$f_price_count = intval($f_price_count);
				$f_offer_count = intval($f_offer_count);
				$f_price_res = intval($f_price_res);
				$f_offer_res = intval($f_offer_res);
				if(($f_offer_count / $f_price_count) < 0.33) {
					Redirect(Query("?sid=?&x=?&y=?"));
					sql("UNLOCK TABLES");
				}else {
					if(intval($f_price_res)!=intval($f_offer_res) 
						&& intval($f_count)>0 
						&& intval($f_count)<=10 
						&& intval($f_price_count) >= min($gUser->max_res,kMarketplace_MinResOffer)
						&& intval($f_offer_count) >= min($gUser->max_res,kMarketplace_MinResOffer))
					foreach($gResTypeVars as $k=>$v)
						if($k == $f_offer_res)for($i=0;$i<intval($f_count);++$i){
							$u = sqlgetobject("SELECT * FROM `user` WHERE `id`=".$gUser->id);
							if($u->{$v} >= $f_offer_count)
							{
								sql("UPDATE `user` SET `$v`=`$v`-$f_offer_count WHERE `id`=".$gUser->id);
								sql("INSERT INTO `marketplace` SET `building`=$f_id,
								`offer_res`=$f_offer_res,
								`offer_count`=$f_offer_count,
								`price_res`=$f_price_res,
								`price_count`=$f_price_count,
								`starttime`=".time());
							}
						}
					$gUser = sqlgetobject("SELECT * FROM `user` WHERE `id` = ".$gUser->id);
					sql("UNLOCK TABLES");
					Redirect(Query("?sid=?&x=?&y=?"));
				}
			break;
		}
	}
	
	
	function mydisplay() {
		foreach ($_REQUEST as $name=>$val) ${"f_".$name} = $val;
		global $gUser;
		global $gObject;
		global $gItemType;
		global $gRes;
		global $gResTypeNames;
		global $gResTypeVars;
				
		profile_page_start("marketplace.php");
		
		if ($gObject->user == $gUser->id) {
			
			$t_other = sqlgettable("SELECT m.*,b.`user`,u.`name`,(m.`offer_count` / m.`price_count`) as `fak` FROM `marketplace` m,`building` b,`user` u WHERE u.`id`=b.`user` AND b.`user`<>".$gUser->id." AND m.`building`=b.`id` ORDER BY `offer_res`,`fak` DESC");
			$t_own = sqlgettable("SELECT m.*,b.`user`,u.`name` FROM `marketplace` m,`building` b,`user` u WHERE u.`id`=b.`user` AND b.`user`=".$gUser->id." AND m.`building`=b.`id`");
				
			?>
			<form method="post" action="<?=Query("?sid=?&x=?&y=?")?>">
			<input type="hidden" name="building" value="marketplace">
			<input type="hidden" name="id" value="<?=$gObject->id?>">
			<input type="hidden" name="do" value="add trade">
			<table border=0>
				<tr><th colspan="5" align="left">Angebot aufgeben</th></tr>
					<tr><td>&nbsp;</td>
					<td>Angebot:</td>
					<td><select name="offer_res" size="1">
					<?php foreach($gResTypeNames as $k=>$v)echo '<option value="'.$k.'">'.$v.'</option>'; ?>
					</select></td>
					<td><input type="text" name="offer_count" size="8"></td><td>Angebot <input type=text name=count value=1 size=2> mal (höchstens 10)</td></tr>
					<tr><td>&nbsp;</td>
					<td>Preis:</td>
					<td><select name="price_res" size="1">
					<?php foreach($gResTypeNames as $k=>$v)echo '<option value="'.$k.'">'.$v.'</option>'; ?>
					</select></td>
					<td><input type="text" name="price_count" size="8"></td><td><input type="submit" name="" value="aufgeben"> (jeweils mindestens <?=kMarketplace_MinResOffer?> erforderlich)</td>
				<tr><td colspan=5 align=left>beim Zurücknehmen eines Handels werden 10% in die Weltbank überwiesen!</tr>
			</table>
			</form>
			<br>
			<table border=0>
				<tr><th colspan="7" align="left">fremde Angebote</th></tr>
				<tr>
					<th colspan=3></th>
					<th colspan=3>Angebot</th>
					<th colspan=3>Preis</th>
					<th colspan=1>Händler</th>
					<th colspan=1></th>
					</tr>
				<?php foreach($t_other as $x) {?>
					<form method="post" action="<?=Query("?sid=?&x=?&y=?")?>">
						<input type="hidden" name="building" value="marketplace">
						<input type="hidden" name="id" value="<?=$gObject->id?>">
						<input type="hidden" name="do" value="do trade">
						<input type="hidden" name="offerid" value="<?=$x->id?>">
						<tr>
							<td>&nbsp;</td>
							<td>[<?=round($x->fak,2)?>]</td>
							<td>&nbsp;</td>
							<td><img src="<?=g("res_".$gResTypeVars[$x->offer_res].".gif")?>"></td>
							<td><?=$x->offer_count?></td>
							<td>&nbsp;für</td>
							<td><img src="<?=g("res_".$gResTypeVars[$x->price_res].".gif")?>"></td>
							<td><?=$x->price_count?></td>
							<td>von</td>
							<td nowrap><?=$x->name?></td>
							<td nowrap>
								<?php $preisdiff = $x->price_count - $gUser->{$gResTypeVars[$x->price_res]};?>
								<?php if ($preisdiff <= 0) {?>
								<input type="submit" value="handeln">
								<?php } else {?>
								<font color="red"><b>zu teuer</b>, es fehlen <?=floor($preisdiff)?> <?=$gResTypeNames[$x->price_res]?></font>
								<?php }?>
							</td>
						</tr>
						<tr><td></td><td colspan=9><hr style="height:1px;margin:0px;padding:0px;"></td></tr>
					</form>
				<?php }?>
			</table>
			<br>
			<table>
				<tr><th colspan="5" align="left">eigene Angebote</th></tr>
				<?php foreach($t_own as $x) {?>
					<form method="post" action="<?=Query("?sid=?&x=?&y=?")?>">
						<input type="hidden" name="building" value="marketplace">
						<input type="hidden" name="id" value="<?=$gObject->id?>">
						<input type="hidden" name="do" value="cancel trade">
						<input type="hidden" name="offerid" value="<?=$x->id?>">
						<tr>
							<td>&nbsp;</td>
							<td><?=$x->offer_count?> <?=$gResTypeNames[$x->offer_res]?> <img style="vertical-align:middle" src="<?=g("res_".$gResTypeVars[$x->offer_res].".gif")?>"></td>
							<td>für</td>
							<td><?=$x->price_count?> <?=$gResTypeNames[$x->price_res]?> <img style="vertical-align:middle" src="<?=g("res_".$gResTypeVars[$x->price_res].".gif")?>"></td>
							<td><input type="submit" value="zurücknehmen"></td>
						</tr>
						<tr><td></td><td colspan=6><hr style="height:1px;margin:0px;padding:0px;"></td></tr>
					</form>
				<?php }?>
			</table>
			<br>
		<?php }?>
		
		<?php if($gUser->admin) {
			if (isset($f_new)) { 
				sql("INSERT INTO `itemtrade` SET `amount` = -1 , `starttime` = '".time()."' , `user` = 0, `building` = ".$gObject->id); 
			}
			if (isset($f_save)) { ISaveAll("itemtrade"); }
			if (isset($f_del)) { IDel("itemtrade",$f_sel); }
		} // endif?>
		
		<?php $itemtrades = sqlgettable("SELECT * FROM `itemtrade` WHERE `building` = ".$gObject->id." ORDER BY `starttime`");?>
		
		
		<?php 
		$gArmies = cArmy::getMyArmies(false,$gUser);
		$gDockedArmies = array();
		foreach ($gArmies as $army)
			if (cArmy::ArmyAtDiag($army,$gObject->x,$gObject->y))
				$gDockedArmies[] = $army;
		?>
		
		<?php if (count($itemtrades) > 0) {?>
			<table>
			<tr>
				<th>Anzahl</th>
				<th>Angebot</th>
				<th>Preis</th>
				<th></th>
			</tr>
			<?php foreach ($itemtrades as $o) if ($o->user == 0) { /*&infin;*/?>
			<tr>			
				<td align="right"><?=($o->amount > 0)?$o->amount:"unbegrenzt"?></td>
				<td nowrap><?=PrintItemTrade($o->offer)?>&nbsp;&nbsp;</td>
				<td nowrap><?=PrintItemTrade($o->price)?></td>
				<td nowrap>
					<?php if (count($gDockedArmies) > 0) {?>
					<?php list($max,$army) = GetMaxTradeInArmyList($gDockedArmies,$o->price); ?>
					<?php if ($max > 0) {?>
						<form method="post" action="<?=Query("?sid=?&x=?&y=?")?>">
							<input type="hidden" name="army" value="<?=$army->id?>">
							<input type="hidden" name="itemtrade" value="<?=$o->id?>">
							<input type="hidden" name="do" value="itemtrade_market">
							<input type="text" name="amount" value="<?=$max?>" style="width:30px">/<?=$max?>(<?=$army->name?>)
							<input type="submit" value="tauschen">
						</form>
					<?php } else { // ?>
						zu teuer
					<?php } // endif?>
					<?php }?>
				</td>
			</tr>
			<?php }?>
			</table>
		<?php } // endif?>
		
		
		
		
		<?php if($gUser->admin) {?>
			<br><br><br><hr>
			<form method="post" action="<?=Query("?sid=?&x=?&y=?")?>">
				<table>
				<tr>
					<th></th>
					<th>Anzahl</th>
					<th>Angebot</th>
					<th>Preis</th>
					<th>Angebot</th>
					<th>Preis</th>
				</tr>
				<?php foreach ($itemtrades as $o) if ($o->user == 0) {?>
				<tr>			
					<td><input type="checkbox" name="sel[]" value="<?=$o->id?>"></td>
					<td><?=IText($o,"amount","width:30px","","i_",$o->id)?></td>
					<td><?=IText($o,"offer","width:60px","","i_",$o->id)?></td>
					<td><?=IText($o,"price","width:60px","","i_",$o->id)?></td>
					<td nowrap><?=PrintItemTrade($o->offer)?>&nbsp;&nbsp;</td>
					<td nowrap><?=PrintItemTrade($o->price)?></td>
				</tr>
				<?php }?>
				</table>
				<input type="submit" name="del" value="löschen">
				<input type="submit" name="save" value="speichern">
				<input type="submit" name="new" value="neu">
			</form>
			
			<?php foreach($gItemType as $o) if ($o->gfx) {?>
			<img alt="<?=$o->name?>" title="<?=$o->name?>" class="picframe" src="<?=g($o->gfx)?>"><?=$o->id?>
			<?php }?>	
		<?php }?>
		
		<?php profile_page_end();
	}
}?>
