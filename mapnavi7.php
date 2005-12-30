<?php
require_once("lib.main.php");
require_once("lib.army.php");
require_once("lib.guild.php");
require_once("lib.construction.php");
Lock();

// TODO : gAllUsers is only used for admin
$gAllUsers = sqlgettable("SELECT `id`,`name` FROM `user` ORDER BY `name`","id");

if (isset($f_regenmini)) {  
	@unlink("tmp/pngmap-guild.png");
	@unlink("tmp/pngmapcreep.png");
	@unlink("tmp/pngmap.png");
}
if (isset($f_regentypes)) {  
	//@unlink(kTypeCacheFile);
	//@unlink("info/".kTypeCacheFile);
	//@unlink("stats/".kTypeCacheFile);
	require_once("generate_types.php");
	require_once(kTypeCacheFile);
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/transitional.dtd">
<html>
<head>
<link rel="stylesheet" type="text/css" href="styles.css"></link>
<SCRIPT LANGUAGE="JavaScript">
<!--
	var gSID = "<?=$gSID?>";
	var curtool = 0;
	var curtoolparam = 0;
	var bigmap = null;
	function nav (x,y) {
		parent.map.nav(x,y);
	}
	function navabs (x,y,cancelmode) {
		parent.map.navabs(x,y,cancelmode);
	}
	function updatepos (x,y) {
		document.getElementsByName("pos")[0].value = x+","+y;
	}
	function settool (tool,param,gfx) {
		curtool = tool;
		curtoolparam = param;
		document.getElementsByName("curtoolpic")[0].src = gfx;
	}
	function resettool () {
		if (curtool == 3 || curtool == 4)
		if (curtool != 10)
			settool(0,0,'<?=g("tool_look.png")?>');
	}
	var lineinit = false;
	var linex = 0;
	var liney = 0;
	function addpatch (text) {
		//if (document.getElementsByName("patchcheck")[0].checked)
		//	document.getElementsByName("patch")[0].value += text;
	}
	function map (x,y) {
		if (curtool == 9) {
			document.getElementsByName("notizblock")[0].value += "  "+x+","+y;
			return;
		}	
		if (curtool == 10) {
			navabs(x,y,false);
			return;
		}	
		var urladd = "";
		var army = 0;
		if (document.getElementsByName("army")[0] != null)
			army = document.getElementsByName("army")[0].value;
		
		switch(curtool){
			case 1:
				urladd = "&do=build&build["+curtoolparam+"]=bauen";
			break;
			case 2:
				urladd = "&do=adminsetterrain&terrain="+curtoolparam+"&brushrad="+document.getElementsByName("brushrad")[0].value;
			break;
			case 3:
				urladd = "&do=setwaypoint&army="+army+"&button_wp=1";
			break;
			case 4:
				urladd = "&do=setwaypoint&army="+army+"&button_route=1";
			break;
			case 5:
				urladd = "&do=cancel";
			break;
			case 6:
				urladd = "&do=adminsetbuilding&btype="+curtoolparam+
				"&blevel="+document.getElementsByName("sellevel")[0].value+
				"&buser="+document.getElementsByName("seluser")[0].value+
				"&quest="+document.getElementsByName("selquest")[0].value;
			break;
			case 7:
				urladd = "&do=adminsetarmy&unit="+curtoolparam+
					"&brushrad="+document.getElementsByName("brushrad")[0].value+
					"&anzahl="+document.getElementsByName("sellevel")[0].value+
					"&user="+document.getElementsByName("seluser")[0].value+
					"&quest="+document.getElementsByName("selquest")[0].value;
			break;
			case 8:
				urladd = "&do=adminsetitem&type="+curtoolparam+
					"&anzahl="+document.getElementsByName("sellevel")[0].value+
					"&brushrad="+document.getElementsByName("brushrad")[0].value+
					"&quest="+document.getElementsByName("selquest")[0].value;
			break;
			case 11:
				urladd = "&do=adminzap";
			break;
			case 12:
				urladd = "&do=adminruin";
			break;
			case 13:
				urladd = "&do=adminremovearmy";
			break;
			case 14:
				urladd = "&do=adminremoveitems";
			break;
			case 15:
				urladd = "&do=adminclear";
			break;
		}
		if (document.getElementsByName("line")[0] != null && document.getElementsByName("line")[0].checked) {
			if (lineinit)
				urladd += "&linex="+linex+"&liney="+liney;
			if (lineinit && !document.getElementsByName("line2")[0].checked)
					lineinit = false;
			else	lineinit = true;
			linex = x;
			liney = y;
		} else lineinit = false;
		
		//parent.info.location.href = "info/info.php?blind=1&x="+x+"&y="+y+urladd+"&sid=<?=$gSID?>";
		if (curtool == 0) parent.info.location.href = "info/info.php?x="+x+"&y="+y+urladd+"&sid=<?=$gSID?>";
		else parent.dummy.location.href = "info/info.php?blind=1&x="+x+"&y="+y+urladd+"&sid=<?=$gSID?>";
	}
	function clearline () { lineinit = false; }
	function myreload () { 
		parent.map.location.href = parent.map.location.href;
		parent.navi.location.href = parent.navi.location.href;
	}
	<?php
	function GetUserStuffList ($user) {
		$user = intval($user);
		global $gArmyType;
		// armies by armytype
		$res = array();
		foreach ($gArmyType as $o) {
			$arr = sqlgettable("SELECT * FROM `army` WHERE `user` = ".$user." AND `type` = ".$o->id." ORDER BY `name`","id","name");
			if (count($arr) > 0) $res[$o->name] = $arr;
		}
		return $res;
	}
	function GetGuildStuffList ($guild) {
		$guild = intval($guild);
		global $gArmyType,$gUser;
		if ($guild == 0 || $guild == kGuild_Weltbank) return false;
		$res = array();
		$res["Mitglieder"] = sqlgettable("SELECT * FROM `user` WHERE `guild` = ".$guild." ORDER BY `name`","id","name");
		foreach ($gArmyType as $o) {
			$gildenarmeen = sqlgettable("SELECT `army`.* FROM `army`,`user` WHERE `army`.`user` = `user`.`id` AND `user`.`guild` = ".$guild." AND `army`.`type` = ".$o->id." ORDER BY `name`","id");
			$controllable = array();
			foreach ($gildenarmeen as $a)
				if (cArmy::CanControllArmy($a,$gUser))
					$controllable[$a->id] = $a->name;
			if (count($controllable) > 0) $res[$o->name] = $controllable;
		}
		return $res;
	}
	
	$gGotoCats = array(
		kMapNaviGotoCat_Pos			=> 0,
		kMapNaviGotoCat_Mark		=> sqlgettable("SELECT * FROM `mapmark` WHERE `user` = ".$gUser->id." ORDER BY `name`","id","name"),
		kMapNaviGotoCat_Own			=> GetUserStuffList($gUser->id),
		kMapNaviGotoCat_Guild		=> GetGuildStuffList($gUser->guild),
		kMapNaviGotoCat_Friends		=> sqlgettable("SELECT `user`.* FROM `fof_user`,`user` WHERE `class` = ".kFOF_Friend." AND `master` = ".$gUser->id." AND `other` = `user`.id ORDER BY `name`","id","name"),
		kMapNaviGotoCat_Enemies		=> sqlgettable("SELECT `user`.* FROM `fof_user`,`user` WHERE `class` = ".kFOF_Enemy." AND `master` = ".$gUser->id." AND `other` = `user`.id ORDER BY `name`","id","name"),
		kMapNaviGotoCat_Search		=> 0,
		kMapNaviGotoCat_Random		=> array("Gebäude","Landschaft","Position"),
		kMapNaviGotoCat_Hellhole	=> 0,
	);
	echo "gGotoCats = new Array();\n";
	foreach ($gGotoCats as $key => $val) if ($gUser->admin || !in_array($key,$gMapNaviGotoCat_AdminOnly)) {
		if (is_array($val)) {
			echo "gGotoCats[".$key."] = new Array();\n";
			foreach ($val as $key2 => $val2) {
				$key2_call = is_numeric($key2) ? ("[".$key2."]") : (".".$key2);
				if (is_array($val2)) {
					echo "gGotoCats[".$key."]".$key2_call." = new Array();\n";
					foreach ($val2 as $key3 => $val3) {
						if (is_numeric($val3)) 
								echo "gGotoCats[".$key."]".$key2_call."[".$key3."] = ".($val3).";\n";
						else	echo "gGotoCats[".$key."]".$key2_call."[".$key3."] = \"".addslashes($val3)."\";\n";
					}
				} else if (is_numeric($val2)) 
						echo "gGotoCats[".$key."]".$key2_call." = ".($val2).";\n";
				else	echo "gGotoCats[".$key."]".$key2_call." = \"".addslashes($val2)."\";\n";
			}
		} else if (is_numeric($val)) 
				echo "gGotoCats[".$key."] = ".($val).";\n";
		else	echo "gGotoCats[".$key."] = \"".addslashes($val)."\";\n";
	}
	//$hellholes = $gUser->admin?sqlgettable("SELECT * FROM `hellhole` WHERE `ai_type` > 0 ORDER BY `id`"):array(); // todo : unhardcode
	//$hellholetypename = array(1=>"orkdorf",2=>"megablob"); // todo : unhardcode
	// $hellholetypename[$o->ai_type]
	?>
	gGotoCat = <?=kMapNaviGotoCat_Pos?>;
	gMarks = new Array( "a", "v", "b", "s");
	gOwnCats2 = new Array( "Armee","Karawane","Arbeiter","Maschiene","Schiff" );
	gOwnCats3 = new Array( "" );
	function GetName (name) { return document.getElementsByName(name)[0]; }
	function Hide (name) { GetName(name).style.display = "none"; }
	function Show (name) { GetName(name).style.display = "inline"; }
	function ShowList (name,list) {
		Show(name); /*set options from array*/
		var options = GetName(name);
		while (options.length > 0) options[options.length-1] = null;
		if (list) {
			var i;
			for (i in list) {
				var NeuerEintrag = new Option(list[i],i);
				options[options.length] = NeuerEintrag;
			}
		}
	}
	function HideList1 () { Hide("gotocat2"); }
	function HideList2 () { Hide("gotocat2"); Hide("gotocat3"); }
	function ShowList1 () { ShowList("gotocat2",gGotoCats[gGotoCat]); }
	function ShowList2 () {
		var i = 0,field;
		var sublist = new Array();
		for (field in gGotoCats[gGotoCat]) {
			sublist[i++] = field;
		}
		ShowList("gotocat2",sublist);
	}
	
	function ChangeGotoCat () {
		// hide old cat
		if (gGotoCat == <?=kMapNaviGotoCat_Pos?>)		{ Hide("pos"); }
		if (gGotoCat == <?=kMapNaviGotoCat_Mark?>)		{ HideList1(); }
		if (gGotoCat == <?=kMapNaviGotoCat_Own?>)		{ HideList2(); }
		if (gGotoCat == <?=kMapNaviGotoCat_Guild?>)		{ HideList2(); }
		if (gGotoCat == <?=kMapNaviGotoCat_Friends?>)	{ HideList1(); }
		if (gGotoCat == <?=kMapNaviGotoCat_Enemies?>)	{ HideList1(); }
		if (gGotoCat == <?=kMapNaviGotoCat_Search?>)	{ Hide("search"); }
		if (gGotoCat == <?=kMapNaviGotoCat_Random?>) 	{ HideList1(); }
		if (gGotoCat == <?=kMapNaviGotoCat_Hellhole?>)	{ HideList1(); }
		
		gGotoCat = GetName("gotocat").value;
		
		// show new cat
		if (gGotoCat == <?=kMapNaviGotoCat_Pos?>) 		{ Show("pos"); }
		if (gGotoCat == <?=kMapNaviGotoCat_Mark?>)		{ ShowList1(); }
		if (gGotoCat == <?=kMapNaviGotoCat_Own?>)		{ ShowList2(); }
		if (gGotoCat == <?=kMapNaviGotoCat_Guild?>)		{ ShowList2(); }
		if (gGotoCat == <?=kMapNaviGotoCat_Friends?>)	{ ShowList1(); }
		if (gGotoCat == <?=kMapNaviGotoCat_Enemies?>)	{ ShowList1(); }
		if (gGotoCat == <?=kMapNaviGotoCat_Search?>)	{ Show("search"); }
		if (gGotoCat == <?=kMapNaviGotoCat_Random?>)	{ ShowList1(); }
		if (gGotoCat == <?=kMapNaviGotoCat_Hellhole?>)	{ ShowList1(); }
		ChangeGotoCat2();
	}
	function ChangeGotoCat2 () {
		if (gGotoCat != <?=kMapNaviGotoCat_Own?> &&
			gGotoCat != <?=kMapNaviGotoCat_Guild?>) return;
		var cat2 = GetName("gotocat2").value;
		var i = 0,field;
		var sublist = new Array();
		for (field in gGotoCats[gGotoCat]) if (i++ == cat2) {
			sublist = gGotoCats[gGotoCat][field];
		}
		ShowList("gotocat3",sublist);
	}
//-->
</SCRIPT>
</head><body onLoad="ChangeGotoCat()">

<!--mapcontrols-->
<table>
</td><td valign=top>
		<?php 
		$gArmy = cArmy::getMyArmies(TRUE,$gUser);
		$gMapMarks = sqlgettable("SELECT * FROM `mapmark` WHERE `user` = ".$gUser->id." ORDER BY `name`","id");
		?>
		<?php if (count($gArmy) > 0 || count($gMapMarks) > 0 || count($hellholes) > 0) {?>
		<FORM METHOD=GET ACTION="<?=Query(kMapScript."?sid=?&big=?&cx=$gCX&cy=$gCY")?>" target="map">
		<INPUT TYPE="hidden" NAME="sid" VALUE="<?=$gSID?>">
		<SELECT NAME="gotocat" onChange="ChangeGotoCat()">
			<?php foreach($gMapNaviGotoCatNames as $id => $name) 
				if ($gUser->admin || !in_array($id,$gMapNaviGotoCat_AdminOnly)) {?>
				<OPTION VALUE=<?=$id?>><?=$name?></OPTION>
			<?php }?>
		</SELECT>
		<INPUT TYPE="text" NAME="pos" VALUE="" style="width:90px;display:none;">
		<INPUT TYPE="text" NAME="search" VALUE="" style="width:90px;display:none;" >
		<SELECT NAME="gotocat2" onChange="ChangeGotoCat2()" style="display:none;"></SELECT>
		<SELECT NAME="gotocat3" style="display:none;"></SELECT>
		<INPUT TYPE="submit" NAME="armygoto" VALUE="&gt;">
		</FORM>
		<?php }?>
</td>
</tr>
</table>
<!-- tools -->
<table><tr><td valign="middle" align="center" bgcolor="green" width="40">
	<img name="curtoolpic" class="picframe" src="<?=isset($f_curtoolgfx)?g($f_curtoolgfx):g("tool_look.png")?>">
</td><td>
	<a href="javascript:settool(0,0,'<?=g("tool_look.png")?>')"><img class="picframe" src="<?=g("tool_look.png")?>"></a>
	<a href="javascript:settool(5,0,'<?=g("tool_cancel.png")?>')"><img alt="Bauplan abbrechen" title="Bauplan abbrechen" class="picframe" src="<?=g("tool_cancel.png")?>"></a>
	<a href="javascript:settool(3,0,'<?=g("tool_wp.png")?>')"><img alt="Wegpunkt setzen" title="Wegpunkt setzen" class="picframe" src="<?=g("tool_wp.png")?>"></a>
	<a href="javascript:settool(4,0,'<?=g("tool_route.png")?>')"><img alt="Route berechnen" title="Route berechnen" class="picframe" src="<?=g("tool_route.png")?>"></a>
	<a href="javascript:settool(9,0,'<?=g("pick.png")?>')"><img alt="Koordinate aufschreiben" title="Koordinate aufschreiben" class="picframe" src="<?=g("pick.png")?>"></a>
	<?php if ($gUser->admin) {?> 
	<a href="javascript:settool(11,0,'<?=g("del.png")?>')"><img alt="Zap" title="Zap" border=0 src="<?=g("del.png")?>"></a>
	<a href="javascript:settool(12,0,'<?=g("del.png")?>')"><img alt="ruin" title="ruin" border=0 src="<?=g("del.png")?>"></a>
	<a href="javascript:settool(13,0,'<?=g("del.png")?>')"><img alt="rm_army" title="rm_army" border=0 src="<?=g("del.png")?>"></a>
	<a href="javascript:settool(14,0,'<?=g("del.png")?>')"><img alt="rm_items" title="rm_items" border=0 src="<?=g("del.png")?>"></a>
	<a href="javascript:settool(15,0,'<?=g("del.png")?>')"><img alt="clear" title="clear" border=0 src="<?=g("del.png")?>"></a>
	<?php }?>
	<a href="javascript:settool(10,0,'<?=g("crosshair.png")?>')"><img alt="Zentrieren" title="Zentrieren" border=0 src="<?=g("crosshair.png")?>"></a>
	<?php 
		if (!UserHasBuilding($gUser->id,kBuilding_HQ))
				$buildable = array(kBuilding_HQ);
		else	$buildable = GetBuildlist(0,0,TRUE,FALSE,FALSE,TRUE);
	?>
	<?php foreach ($buildable as $o) {
		$name = $gBuildingType[$o]->name;?>
		<a href="javascript:settool(1,<?=$o?>,'<?=GetBuildingPic($o,0,"ns")?>')"><img alt="<?=$name?>" title="<?=$name?>" class="picframe" src="<?=g($gBuildingType[$o]->gfx,"ns",0,$gUser->race)?>"></a>
	<?php }?>
	<a href="javascript:myreload()">(reload)</a><br>
</td></tr></table>	
<?php if ($gUser->admin || intval($gUser->flags) & kUserFlags_TerraFormer) {?> 
	brushrad:<INPUT TYPE="text" NAME="brushrad" VALUE="0" style="width:30px"><br>
	<?php foreach($gTerrainType as $o) {?>
	<a href="javascript:settool(2,<?=$o->id?>,'<?=g($o->gfx,"ns")?>')"><img alt="<?=$o->name?>" title="<?=$o->name?>" class="picframe" src="<?=g($o->gfx,"ns")?>"></a>
	<?php } }?>
<?php if ($gUser->admin) {?> 
	<?php foreach($gBuildingType as $o) {?>
	<a href="javascript:settool(6,<?=$o->id?>,'<?=GetBuildingPic($o->id,1,"ns")?>')"><img alt="<?=$o->name?>" title="<?=$o->name?>" class="picframe" src="<?=GetBuildingPic($o->id,1,"ns")?>"></a>
	<?php }?>
	<?php foreach($gUnitType as $o) if ($o->gfx) {?>
	<a href="javascript:settool(7,<?=$o->id?>,'<?=g($o->gfx)?>')"><img alt="<?=$o->name?>" title="<?=$o->name?>" class="picframe" src="<?=g("$o->gfx")?>?>"></a>
	<?php }?>
	<?php foreach($gItemType as $o) if ($o->gfx) {?>
	<a href="javascript:settool(8,<?=$o->id?>,'<?=g($o->gfx)?>')"><img alt="<?=$o->name?>" title="<?=$o->name?>" class="picframe" src="<?=g("$o->gfx")?>?>"></a>
	<?php }?>
	<br>
	<?php $quests = sqlgettable("SELECT * FROM `quest` ORDER BY `start`");?>
	<SELECT NAME="selquest">
		<OPTION VALUE=0>-no_quest-</OPTION>
		<?php PrintObjOptionsId($quests,"id","name")?>
	</SELECT>
	level/num:<INPUT TYPE="text" NAME="sellevel" VALUE="10" style="width:30px">
	<SELECT NAME="seluser">
		<OPTION VALUE=0>-no_owner-</OPTION>
		<?php PrintObjOptions($gAllUsers,"id","name")?>
	</SELECT>
	line:<INPUT TYPE="checkbox" NAME="line" onChange="clearline()" VALUE="1">
	<INPUT TYPE="checkbox" NAME="line2" onChange="clearline()" VALUE="1">
	<a href="<?=Query("?regencss=1&sid=?")?>">(css)</a>
	<a href="<?=Query("?regennwse=1&sid=?")?>">(nwse)</a>
	<a href="<?=Query("?regentypes=1&sid=?")?>">(types)</a>
	<?php if (0) {?><a href="<?=Query("?createbodenschatz=1&sid=?")?>">(bodenschatz)</a><?php }?>
<?php } // endif admin?>
	<textarea name="notizblock" rows=2 cols=40></textarea><br>
	<?php if (0) {?>
	patch<a href="javascript:void(document.getElementsByName('patch')[0].value = '')">#</a>:
	<INPUT TYPE="checkbox" NAME="patchcheck" VALUE="1">
	<textarea name="patch" rows=2 cols=30></textarea>
	<?php }?>
	<?php 
		// same as in mapstyle.php
		if($gUser && isset($gUser->usegfxpath) && $gUser->usegfxpath){
			$gfxpath = $gUser->gfxpath;
			if(!empty($gfxpath))if($gfxpath{strlen($gfxpath)-1} != '/')$gfxpath .= "/";
		}
		else $gfxpath = "";
	?>
</body>
</html>
