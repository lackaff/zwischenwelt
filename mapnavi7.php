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

if ($gUser->admin && isset($f_regentypes)) {
	RegenTypeCache($f_newadder);
	require(kTypeCacheFile);
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
		if (gGotoCat == <?=kMapNaviGotoCat_Own?>)		{ HideList2(); Hide("armyshow"); }
		if (gGotoCat == <?=kMapNaviGotoCat_Guild?>)		{ HideList2(); Hide("armyshow"); }
		if (gGotoCat == <?=kMapNaviGotoCat_Friends?>)	{ HideList1(); }
		if (gGotoCat == <?=kMapNaviGotoCat_Enemies?>)	{ HideList1(); }
		if (gGotoCat == <?=kMapNaviGotoCat_Search?>)	{ HideList1(); Hide("search"); }
		if (gGotoCat == <?=kMapNaviGotoCat_Random?>) 	{ HideList1(); }
		if (gGotoCat == <?=kMapNaviGotoCat_Hellhole?>)	{ HideList1(); }
		
		gGotoCat = GetName("gotocat").value;
		
		// show new cat
		if (gGotoCat == <?=kMapNaviGotoCat_Pos?>) 		{ Show("pos"); }
		if (gGotoCat == <?=kMapNaviGotoCat_Mark?>)		{ ShowList1(); }
		if (gGotoCat == <?=kMapNaviGotoCat_Own?>)		{ ShowList2(); Show("armyshow"); }
		if (gGotoCat == <?=kMapNaviGotoCat_Guild?>)		{ ShowList2(); Show("armyshow"); }
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
	
	var gBrush = 0;
	var gBrushLineOn = false;
	var gBrushLastX = 0;
	var gBrushLastY = 0;
	var gSID = "<?=$gSID?>";
	var curtool = 0;
	var curtoolparam = 0;
	var bigmap = null;
	var gDummyFrameCycler = 0;
	kDummyFrames = <?=kDummyFrames?>;
	kNaviJSMapVersion = <?=intval(kJSMapVersion)+intval($gGlobal["typecache_version_adder"])?>;
	function GetkNaviJSMapVersion () {
		return kNaviJSMapVersion;
	}
	function navi_navrel (x,y) {
		parent.map.navrel(x,y);
	}
	function updatepos (x,y) {
		document.getElementsByName("pos")[0].value = x+","+y;
		SetBrushBorder();
	}
	function settool (tool,param,gfx) {
		curtool = tool;
		curtoolparam = param;
		document.getElementsByName("curtoolpic")[0].src = gfx;
		StopBrushLine();
	}
	function resettool () {
		//if (curtool == 3 || curtool == 4)
		//if (curtool != 10)
		settool(0,0,'<?=g("tool_look.png")?>');
	}
	function addpatch (text) {
		//if (document.getElementsByName("patchcheck")[0].checked)
		//if (document.getElementsByName("grassonly")[0].checked)
		//	document.getElementsByName("patch")[0].value += text;
	}
	function mapclicktool_hasoverlay () {
		if (curtool == 1) return true;
		if (curtool == 2) return true;
		if (curtool == 3) return true;
		if (curtool == 4) return true;
		if (curtool == 5) return true;
		if (curtool >= 6) return true;
		return false;
	}
	function mapclicktool (x,y,activearmyid) {
		updatepos(x,y);
		if (curtool == 9) {
			document.getElementsByName("notizblock")[0].value += "  "+x+","+y;
			return;
		}	
		if (curtool == 10) {
			parent.map.extnavabs(x,y,false);
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
				urladd = "&do=adminsetterrain&terrain="+curtoolparam;
			break;
			case 3:
				urladd = "&do=setwaypoint&army="+army+"&button_wp=1";
			break;
			case 4:
				urladd = "&do=setwaypoint&army="+army+"&button_route=1";
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
					"&anzahl="+document.getElementsByName("sellevel")[0].value+
					"&user="+document.getElementsByName("seluser")[0].value+
					"&quest="+document.getElementsByName("selquest")[0].value;
			break;
			case 8:
				urladd = "&do=adminsetitem&type="+curtoolparam+
					"&anzahl="+document.getElementsByName("sellevel")[0].value+
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
			case 20:
				urladd = "&do=quickmagic&spellid="+curtoolparam;
			break;
		}
		
		// add brush info to query
		urladd += "&brush="+gBrush+"&brushline="+(gBrushLineOn?1:0)+"&brushlastx="+gBrushLastX+"&brushlasty="+gBrushLastY;
		if (document.getElementsByName("brushrad").length > 0)
				urladd += "&brushrad="+document.getElementsByName("brushrad")[0].value;
		else	urladd += "&brushrad=0"; // only terraformer and admins
		if (document.getElementsByName("brushdensity").length > 0)
				urladd += "&brushdensity="+document.getElementsByName("brushdensity")[0].value;
		else	urladd += "&brushdensity=100"; // only terraformer and admins
		if (document.getElementsByName("grassonly").length > 0)
				urladd += "&brushgrassonly="+(document.getElementsByName("grassonly")[0].checked?"1":"0");
		else	urladd += "&brushgrassonly=0"; // only terraformer and admins
		
		//parent.info.location.href = "info/info.php?blind=1&x="+x+"&y="+y+urladd+"&sid=<?=$gSID?>";
		// ohne baseurl kommt hier der merkwürdige fehler, dass sich mehrere info/ ansammeln...
		// vermutlich weil die funktion ueber das map frame aufgerufen wird -> browserbug ?
		var url = "<?=BASEURL?>info/info.php?x="+x+"&y="+y+urladd+"&sid=<?=$gSID?>";
		//alert(url);
		if (curtool == 0 || curtool == 20) 
				parent.info.location.href = url;
		else {
			<?php for ($i=0;$i<kDummyFrames;++$i) {?>
			if (gDummyFrameCycler == <?=$i?>) parent.dummy<?=$i?>.location.href = url + "&blind=1";
			<?php } // endforeach?>
		}
		
		// update the brush tool
		if (curtool == 1 || curtool == 2 || curtool == 5 || (curtool >= 6 && curtool <= 15)) {
			if (gBrushLineOn) {
				if (gBrush == 1 || gBrush == 3) StopBrushLine();
			} else {
				if (gBrush != 0) StartBrushLine();
			}
		}
		gBrushLastX = x;
		gBrushLastY = y;
		
		gDummyFrameCycler++;
		if (gDummyFrameCycler >= kDummyFrames) gDummyFrameCycler = 0;
	}
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
	gBigMapWin = false; // bigmap is opened from navi, so it can be called even after normal map scroll
	function OpenBigMap(a,b) { gBigMapWin = window.open(a,b); }
	function GetBigMap() { return gBigMapWin; }
	function BrushAdd (add) {
		// brushsize = brushrad
		document.getElementsByName('brushrad')[0].value = 
			Math.max(0,parseInt(document.getElementsByName('brushrad')[0].value) + add);
	}
	function SetBrushBorder () {
		parent.map.SetBorder(gBrushLineOn?"red":"white");
	}
	function StartBrushLine () {
		if (gBrushLineOn) return;
		gBrushLineOn = true;
		SetBrushBorder();
	}
	function StopBrushLine () {
		if (!gBrushLineOn) return;
		gBrushLineOn = false;
		SetBrushBorder();
	}
	function ChangeBrush (newbrushnum) {
		StopBrushLine();
		gBrush = newbrushnum;
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
<INPUT TYPE="submit" NAME="armyshow" VALUE="wp" style="display:none;">
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
	foreach ($gBuildingTypeGroups as $name => $buildingtypeids) {
		$head = "<img src=\"".g($gBuildingTypeGroupsPics[$name])."\" alt=\"".$name."\" title=\"".$name."\">";
		$content = "<div class=\"mapnavitool_buildings\">";
		foreach ($buildingtypeids as $id) if ($id != -1 && !$gBuildingType[$id]->special) {
			if ($gBuildingType[$id]->race != 0 && $gUser->race != $gBuildingType[$id]->race) continue;
			if ($id == kBuilding_HQ) continue;
			if (!isset($gBuildingType[$id])) continue;
			$canbuild = HasReq($gBuildingType[$id]->req_geb,$gBuildingType[$id]->req_tech,$gUser->id);
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


<?php 
$brushtabs = array();
rob_ob_start(); ?>
<img border=0 src="<?=g("brush/brush_normal.png")?>" alt="<?=$tip="Jeder Click betrifft nur ein Feld"?>" title="<?=$tip?>">
<?php $brushtabs[] = array(rob_ob_end(),""); rob_ob_start();?>
<img border=0 src="<?=g("brush/brush_lines.png")?>" alt="<?=$tip="Jeder ZWEITE Click erzeugt eine Linie zum letzten"?>" title="<?=$tip?>">
<?php $brushtabs[] = array(rob_ob_end(),""); rob_ob_start();?>
<img border=0 src="<?=g("brush/brush_linestrip.png")?>" alt="<?=$tip="Jeder Click erzeugt eine Linie zum letzten"?>" title="<?=$tip?>">
<?php $brushtabs[] = array(rob_ob_end(),""); rob_ob_start();?>
<img border=0 src="<?=g("brush/brush_rects.png")?>" alt="<?=$tip="Jeder zweite Click füllt ein Rechteck zum letzten"?>" title="<?=$tip?>">
<?php $brushtabs[] = array(rob_ob_end(),""); rob_ob_start();?>
<?php if ($gUser->admin || intval($gUser->flags) & kUserFlags_TerraFormer) {?>
	<table border=0 cellspacing=0 cellpadding=0>
	<tr>
	<td><img border=0 src="<?=g("brush/brush_size.png")?>" alt="<?=$tip="Pinselgrösse (Nur für Landschaft)"?>" title="<?=$tip?>"></td>
	<td><INPUT TYPE="text" NAME="brushrad" VALUE="0" style="width:30px"></td>
	<td>
		<table border=0 cellspacing=2 cellpadding=2>
		<tr>
			<td><a href="javascript:BrushAdd(+1)"><img border=0 src="<?=g("plus.png")?>" alt="" title=""></a></td>
		</tr><tr>
			<td><a href="javascript:BrushAdd(-1)"><img border=0 src="<?=g("minus.png")?>" alt="" title=""></a></td>
		</tr>
		</table>
	</td>
	<td><img border=0 src="<?=g("brush/brush_density.png")?>" alt="<?=$tip="Pinseldichte (Nur für Landschaft)"?>" title="<?=$tip?>"></td>
	<td>
	<select name="brushdensity">
		<?php for ($i=100;$i>0;$i-=10) {?>
		<option value="<?=$i?>"><?=$i?>%</option>
		<?php } // endforeach?>
	</select>
	</td><td>
	<input type="checkbox" name="grassonly" value="1" checked>
	<img border=0 src="<?=g($gTerrainType[kTerrain_Grass]->gfx)?>" alt="<?=$tip="nur auf Grass bearbeiten"?>" title="<?=$tip?>">
	</td>
	</tr>
	</table>
<?php } // endif?>
<?php $corner = trim(rob_ob_end());
echo GenerateTabsMultiRow("brushtabs",$brushtabs,8,0,$corner,"ChangeBrush");
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
	<br>
	<?php 	$n = "typecache_version_adder"; $newadder = intval(isset($gGlobal[$n])?$gGlobal[$n]:0)+1;?>
	<a href="<?=Query("?regentypes=1&sid=?&newadder=".$newadder)?>">(clear type cache)</a>
	<?php if (0) {?><a href="<?=Query("?createbodenschatz=1&sid=?")?>">(bodenschatz)</a><?php }?>
	
<?php }?> 
<a href="javascript:myreload()"><img border=0 src="<?=g("icon/reload.png")?>" alt="reload" title="reload"></a>



</body>
</html>
