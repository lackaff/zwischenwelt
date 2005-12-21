<?php

//user accepts a offer in the marketplace.
//if user=0 gUser used. user can be an complete userobject or an id
function MarketplaceAcceptTrade($offerid,$user=0){
	global $gUser,$gResTypeVars,$gResTypeNames;
	$gResTypeVarsFlipped = array_flip($gResTypeVars);
	
	if(empty($user))$user = $gUser;
	else if(is_numeric($user))$user = sqlgetobject("SELECT * FROM `user` WHERE `id`=".intval($user));
	else ;
	
	sql("LOCK TABLES `newlog` WRITE,`user` WRITE, 
		`sqlerror` WRITE, `marketplace` WRITE,`building` READ");
	$offerid = intval($offerid);
	$t = sqlgetobject("SELECT `marketplace`.*,`building`.`user` FROM `marketplace`,`building` WHERE `marketplace`.`building`=`building`.`id` AND `marketplace`.`id`=".$offerid);
	if($t)
	{
		$u = sqlgetobject("SELECT * FROM `user` WHERE `id`=".$user->id);
		$res_price = $gResTypeVars[$t->price_res];
		$res_offer = $gResTypeVars[$t->offer_res];
		if($u->{$res_price} >= $t->price_count)
		{
			sql("UPDATE `user` SET `$res_price`=`$res_price`-".$t->price_count.",`$res_offer`=`$res_offer`+".$t->offer_count." WHERE `id`=".$user->id);
			
			$uu = sqlgetobject("SELECT `user`.`id` FROM `user`,`building` WHERE `user`.`id`=`building`.`user` AND `building`.`id`=".$t->building);
			
			LogMe($uu->id,NEWLOG_TOPIC_TRADE,NEWLOG_TRADE,$t->price_count,0,0,$u->name,$gResTypeNames[$gResTypeVarsFlipped[$res_price]]);
			
			sql("UPDATE `user` SET `$res_price`=`$res_price`+".$t->price_count." WHERE `id`=".$uu->id);	
			sql("DELETE FROM `marketplace` WHERE `id`=".$t->id);
		}
	}
	sql("UNLOCK TABLES");
}

function MarketplaceCancelTrade($offerid,$user=0){
	global $gUser,$gResTypeVars;
	
	if(empty($user))$user = $gUser;
	else if(is_numeric($user))$user = sqlgetobject("SELECT * FROM `user` WHERE `id`=".intval($user));
	else ;

	sql("LOCK TABLES `user` WRITE, `sqlerror` WRITE, `guild` WRITE, `marketplace` WRITE,`building` READ");
	$offerid = intval($offerid);
	$t = sqlgetobject("SELECT `marketplace`.*,`building`.`user` FROM `marketplace`,`building` WHERE `marketplace`.`building`=`building`.`id` AND `marketplace`.`id`=".$offerid);
	if($t && $t->user == $user->id)
	{
		$spend = $t->offer_count * 0.1;
		$t->offer_count -= $spend;
		$res = $gResTypeVars[$t->offer_res];
		sql("UPDATE `user` SET `$res`=`$res`+".$t->offer_count." WHERE `id`=".$t->user);
		//weltbank spenden
		sql("UPDATE `guild` SET `$res`=`$res`+".$spend." WHERE `id`= ".kGuild_Weltbank);
		sql("DELETE FROM `marketplace` WHERE `id`=".$t->id);
	}
	sql("UNLOCK TABLES");
}

?>