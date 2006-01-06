<?php
require_once("lib.main.php");
require_once("lib.army.php");
require_once("lib.guild.php");
require_once("lib.construction.php");
require_once("lib.tabs.php");
Lock();

// todo : not-yet-buildable  buildings shown as tools, but marked as unavailable... 
// todo : prices as toolstips for buildings and spells

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
<link rel="stylesheet" type="text/css" href="<?=GetZWStylePath()?>"></link>
<SCRIPT LANGUAGE="JavaScript">
<!--
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
				if ($a->user != $gUser->id && cArmy::CanControllArmy($a,$gUser))
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
		kMapNaviGotoCat_Search		=> array("Spieler","Gilde","Armee","Monster","Bodenschatz"),
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
	// TODO : fuer admins die hellhole liste wieder aktivieren (orkdorf,hyperblob)
	//$hellholes = $gUser->admin?sqlgettable("SELECT * FROM `hellhole` WHERE `ai_type` > 0 ORDER BY `id`"):array(); // todo : unhardcode
	//$hellholetypename = array(1=>"orkdorf",2=>"megablob"); // todo : unhardcode
	// $hellholetypename[$o->ai_type]
	?>
	gGotoCat = <?=kMapNaviGotoCat_Pos?>;
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
		if (gGotoCat == <?=kMapNaviGotoCat_Search?>)	{ HideList1(); Hide("search"); }
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
		if (gGotoCat == <?=kMapNaviGotoCat_Search?>)	{ ShowList1(); Show("search"); }
		if (gGotoCat == <?=kMapNaviGotoCat_Random?>)	{ ShowList1(); }
		if (gGotoCat == <?=kMapNaviGotoCat_Hellhole?>)	{ ShowList1(); }
		ChangeGotoCat2();
	}
	function ChangeGotoCat2 () {
		GetName("searchcounter").value = -1; // incremented before send
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
	
	var gSID = "<?=$gSID?>";
	var curtool = 0;
	var curtoolparam = 0;
	var bigmap = null;
	var lineinit = false;
	var linex = 0;
	var liney = 0;
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
		//if (curtool == 3 || curtool == 4)
		//if (curtool != 10)
		settool(0,0,'<?=g("tool_look.png")?>');
	}
	function addpatch (text) {
		//if (document.getElementsByName("patchcheck")[0].checked)
		//	document.getElementsByName("patch")[0].value += text;
	}
	function mapclicktool (x,y,activearmyid) {
		updatepos(x,y);
		if (curtool == 9) {
			document.getElementsByName("notizblock")[0].value += "  "+x+","+y;
			return;
		}	
		if (curtool == 10) {
			navabs(x,y,false);
			return;
		}	
		var urladd = "";
		//var army = parent.map.JSGetActiveArmyID();
		var army = activearmyid;
		if (army == 0 && document.getElementsByName("gotocat3")[0] != null)
			army = document.getElementsByName("gotocat3")[0].value;
		
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
			case 20:
				urladd = "&do=quickmagic&spellid="+curtoolparam;
			break;
			case 5:
				urladd = "&do=cancel&cancel_wp_armyid="+army;
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
		// ohne baseurl kommt hier der merkwürdige fehler, dass sich mehrere info/ ansammeln...
		// vermutlich weil die funktion ueber das map frame aufgerufen wird -> browserbug ?
		var url = "<?=BASEURL?>info/info.php?x="+x+"&y="+y+urladd+"&sid=<?=$gSID?>";
		//alert(url);
		if (curtool == 0 || curtool == 20) 
				parent.info.location.href = url;
		else	parent.dummy.location.href = url + "&blind=1";
	}
	function clearline () { lineinit = false; }
	function myreload () { 
		parent.map.location.href = parent.map.location.href;
		parent.navi.location.href = parent.navi.location.href;
	}
	function ToolTabChange (tabnum) {
		if (tabnum == 0) resettool();
	}
	function MyOnLoad () {
		ChangeGotoCat();
		//if (parent.map != null && parent.map.JSUpdateNaviPos != null)
		//	parent.map.JSUpdateNaviPos();
	}
	function SelectArmy (armyid) {
		if (armyid == 0) return;
		// called from map when an army is activated for waypoint-setting, select it from the dropdown
		// might be under own gGotoCats[3] or under guild gGotoCats[4], but not gGotoCats[4].Mitglieder
		var i,j,k,id;
		for (i=3;i<=4;++i) {
			k = 0;
			for (j in gGotoCats[i]) {
				if (j != "Mitglieder") for (id in gGotoCats[i][j]) if (id == armyid) {
					GetName("gotocat").value = i;
					ChangeGotoCat();
					GetName("gotocat2").value = k;
					ChangeGotoCat2();
					GetName("gotocat3").value = armyid;
					return;
				}
				++k;
			}
		}
	}
	function submitgoto() {
		resettool();
		GetName("searchcounter").value++; // starts at -1
	}
//-->
</SCRIPT>
</head><body onLoad="MyOnLoad()">

<!--mapcontrols-->
<div class="mapnavigoto">
<FORM METHOD=GET ACTION="<?=Query(kMapScript."?sid=?&big=?&cx=$gCX&cy=$gCY")?>" target="map" onSubmit="submitgoto()">
<INPUT TYPE="hidden" NAME="sid" VALUE="<?=$gSID?>">
<INPUT TYPE="hidden" NAME="searchcounter" VALUE="-1">
<SELECT NAME="gotocat" onChange="ChangeGotoCat()">
	<?php foreach($gMapNaviGotoCatNames as $id => $name) 
		if ($gUser->admin || !in_array($id,$gMapNaviGotoCat_AdminOnly)) {?>
		<OPTION VALUE=<?=$id?>><?=$name?></OPTION>
	<?php }?>
</SELECT>
<SELECT NAME="gotocat2" onChange="ChangeGotoCat2()" style="display:none;"></SELECT>
<SELECT NAME="gotocat3" style="display:none;"></SELECT>
<INPUT TYPE="text" NAME="pos" VALUE="" style="width:90px;display:none;">
<INPUT TYPE="text" NAME="search" VALUE="" style="width:90px;display:none;" >
<INPUT TYPE="submit" NAME="armygoto" VALUE="&gt;">
</FORM>
</div>


<?php 

// prepare the $gBuildingTypeGroups array, replace -1 by all not in list..
$listed = array();
foreach ($gBuildingTypeGroups as $buildingtypeids) 
	foreach ($buildingtypeids as $id)
		if ($id > 0) $listed[] = $id;
foreach ($gBuildingTypeGroups as $name => $buildingtypeids) {
	foreach ($buildingtypeids as $key => $id) {
		if ($id == -1) {
			foreach ($gBuildingType as $o) if (!in_array($o->id,$listed))
				$buildingtypeids[] = $o->id;
			$gBuildingTypeGroups[$name] = array_unique($buildingtypeids);
			break;
		}
	}
}

// construct tabs
$gNaviToolTabs = array();
function NaviTool ($pic,$param1,$param2,$tooltip="",$css="") {
	$res = "";
	$res .= "<span class=\"".$css."\">";
	$res .= "<a href=\"javascript:settool(".$param1.",".$param2.",'".$pic."')\">";
	$res .= "<img border=1 src=\"".$pic."\" alt=\"".addslashes($tooltip)."\" title=\"".addslashes($tooltip)."\">";
	$res .= "</a>";
	$res .= "</span>\n";
	return $res;
}

// general tools tab
$head = "<img src=\"".g("tool_look.png")."\">";
$content = "";
$content .= "<div class=\"mapnavitool_general\">\n";
$content .= NaviTool(g("tool_look.png"),0,0,"anschauen","navtoolicon");
$content .= NaviTool(g("tool_cancel.png"),5,0,"Bauplan/Wegpunkt löschen","navtoolicon");
$content .= NaviTool(g("tool_wp.png"),3,0,"Wegpunkt setzen","navtoolicon");
$content .= NaviTool(g("tool_route.png"),4,0,"Route berechnen","navtoolicon");
$content .= NaviTool(g("pick.png"),9,0,"Koordinate aufschreiben","navtoolicon");
if ($gUser->admin) {
	$content .= NaviTool(g("del.png"),11,0,"Zap","navtoolicon");
	$content .= NaviTool(g("del.png"),12,0,"ruin","navtoolicon");
	$content .= NaviTool(g("del.png"),13,0,"rm_army","navtoolicon");
	$content .= NaviTool(g("del.png"),14,0,"rm_items","navtoolicon");
	$content .= NaviTool(g("del.png"),15,0,"clear","navtoolicon");
}
$content .= NaviTool(g("tool_crosshair.png"),10,0,"Zentrieren");
$content .= "<br>";
$content .= "<textarea class=\"notizblock\" name=\"notizblock\" rows=2 cols=40></textarea>";
$content .= "</div>\n";

$gNaviToolTabs[] = array($head,$content);

// building-tabs
$user_has_hq = UserHasBuilding($gUser->id,kBuilding_HQ);
if ($user_has_hq) {
	// normal build menu
	// obsolete : $buildable = GetBuildlist(0,0,TRUE,FALSE,FALSE,TRUE);
	foreach ($gBuildingTypeGroups as $name => $buildingtypeids) {
		$head = "<img src=\"".g($gBuildingTypeGroupsPics[$name])."\" alt=\"".$name."\" title=\"".$name."\">";
		$content = "<div class=\"mapnavitool_buildings\">";
		foreach ($buildingtypeids as $id) if ($id != -1 && !$gBuildingType[$id]->special) {
			if ($gBuildingType[$id]->race != 0 && $gUser->race != $gBuildingType[$id]->race) continue;
			if ($id == kBuilding_HQ) continue;
			$canbuild = HasReq($gBuildingType[$id]->req_geb,$gBuildingType[$id]->req_tech,$gUser->id);
			// HasReq($o->req_geb,$o->req_tech,$gUser->id)
			$content .= NaviTool(g($gBuildingType[$id]->gfx,"we",1),1,$id,$gBuildingType[$id]->name.($canbuild?"":"(noch nicht baubar)"),$canbuild?"buildable":"unbuildable");
		}
		$content .= "</div>\n";
		$gNaviToolTabs[] = array($head,$content);
	}
} else {
	// no HQ yet...
	$id = kBuilding_HQ;
	$head = "<img src=\"".g($gBuildingType[$id]->gfx,"we",1)."\">";
	$content = "";
	$content .= NaviTool(g($gBuildingType[$id]->gfx,"we",1),1,$id,$gBuildingType[$id]->name,"navtoolicon");
	$gNaviToolTabs[] = array($head,$content);
}


// magic tabs
$candospells = array();
$spelltypes = sqlgettable("SELECT * FROM `spelltype` ORDER BY `orderval` ASC");
foreach ($spelltypes as $spelltype) {
	if(HasReq($spelltype->req_building,$spelltype->req_tech,$gUser->id,0)){ // TODO : replace 0 by current spell-tech level ?
		$group = $spelltype->primetech ? $gTechnologyType[$spelltype->primetech]->group : 0;
		$candospells[$spelltype->target][$spelltype->id] = $spelltype;
	}
}
// (isset($gTechnologyGroup[$groupkey])?g($gTechnologyGroup[$groupkey]->gfx):g("res_mana.gif"))
foreach ($candospells as $group => $arr) if (count($arr) > 0) {
	$head = "<img src=\"".g("tool_mana.png")."\">";
	$content = "<div class=\"mapnavitool_magic\">\n";
	foreach ($arr as $spelltype)
		$content .= NaviTool(g($spelltype->gfx),20,$spelltype->id,$spelltype->name,"navtoolicon");
	$content .= "</div>\n";
	$gNaviToolTabs[] = array($head,$content);
}

// terraforming tab
if ($gUser->admin || intval($gUser->flags) & kUserFlags_TerraFormer) {
	$head = "<img src=\"".g("icon/admin.png")."\">";
	$content = "<div class=\"mapnavitool_terraform\">\n";
	$content .= "Landschaftsgestaltung: Pinselgrösse:<INPUT TYPE=\"text\" NAME=\"brushrad\" VALUE=\"0\" style=\"width:30px\"><br>\n";
	foreach($gTerrainType as $o)
		$content .= NaviTool(g($o->gfx,"ns"),2,$o->id,$o->name,"navtoolicon");
	$content .= "</div>\n";
	$gNaviToolTabs[] = array($head,$content);
}


// admin tabs
if ($gUser->admin) {
	$head = "<img src=\"".g("icon/admin.png")."\">";
	
	// buildings
	$content = "<div class=\"mapnavitool_admin\">\n";
	foreach($gBuildingType as $o)
		$content .= NaviTool(GetBuildingPic($o->id),6,$o->id,$o->name,"navtoolicon");
	$content .= "</div>\n";
	$gNaviToolTabs[] = array($head,$content);
	
	// units
	$content = "<div class=\"mapnavitool_admin\">\n";
	foreach($gUnitType as $o)
		$content .= NaviTool(g($gUnitType[$o->id]->gfx),7,$o->id,$o->name,"navtoolicon");
	$content .= "</div>\n";
	$gNaviToolTabs[] = array($head,$content);
	
	// items
	$content = "<div class=\"mapnavitool_admin\">\n";
	foreach($gItemType as $o)
		$content .= NaviTool(g($gItemType[$o->id]->gfx),8,$o->id,$o->name,"navtoolicon");
	$content .= "</div>\n";
	$gNaviToolTabs[] = array($head,$content);
}

$tabcorner = "<div class=\"mapnavi_curtool\"><img width=23 height=23 name=\"curtoolpic\" src=\"".g("tool_look.png")."\"></div>";

echo "<table width=\"100%\" border=0 cellspacing=0 cellpadding=0><tr><td>\n"; // cage
echo GenerateTabs("mapnavitools",$gNaviToolTabs,$tabcorner,"ToolTabChange");
echo "</td></tr></table>\n"; // cage
?>

<?php if ($gUser->admin) {?> 
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
	<br>
	<a href="<?=Query("?regencss=1&sid=?")?>">(css)</a>
	<a href="<?=Query("?regennwse=1&sid=?")?>">(nwse)</a>
	<a href="<?=Query("?regentypes=1&sid=?")?>">(types)</a>
	<?php if (0) {?><a href="<?=Query("?createbodenschatz=1&sid=?")?>">(bodenschatz)</a><?php }?>
	
<?php }?> 
<a href="javascript:myreload()"><img border=0 src="<?=g("icon/reload.png")?>" alt="reload" title="reload"></a>

</body>
</html>
