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
		//var scroll = document.getElementsByName("myscroll")[0].value;
		//navabs(x*scroll+parent.map.getx(),y*scroll+parent.map.gety(),x==0&&y==0);
	}
	function navabs (x,y,cancelmode) {
		parent.map.navabs(x,y);
		//resettool();
		/*
		var army = 0;
		if (document.getElementsByName("army")[0] != null)
			army = document.getElementsByName("army")[0].value;
		document.getElementsByName("x")[0].value = x;
		document.getElementsByName("y")[0].value = y;
		document.getElementsByName("pos")[0].value = x+","+y;
		var mode = cancelmode?0:parent.map.getmode();//kMapScript
		parent.map.location.href = parent.map.location.pathname+"<?=Query("?sid=?&big=?&naviset=1&cx=$gCX&cy=$gCY")?>&mode="+mode+"&x="+x+"&y="+y+"&army="+army;
		*/
		// todo : hilightplayer
	}
	
	function updatepos (x,y) {
		resettool();
		/*
		if (document.getElementsByName("x")[0] != null)
			document.getElementsByName("x")[0].value = x;
		if (document.getElementsByName("y")[0] != null)
			document.getElementsByName("y")[0].value = y;
		if (document.getElementsByName("pos")[0] != null)
			document.getElementsByName("pos")[0].value = x+","+y;
		*/
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
	function IsVisible (x,y) {
		x -= parent.map.getleft();
		y -= parent.map.gettop();
		if (x < 0 || y < 0 || x >= parent.map.getcx() || y >= parent.map.getcy()) return false;
		return true;
	}
	function SetCellClass (x,y,classname) {
		// ignored so far to prevent error... need more arguments
		/*
		if (bigmap && !bigmap.closed) {
			var x2 = x - bigmap.getleft();
			var y2 = y - bigmap.gettop();
			if (x2 >= 0 && y2 >= 0 && x2 < bigmap.getcx() && y2 < bigmap.getcy())  
				bigmap.document.getElementsByTagName("div")[bigmap.getcx()*y2+x2].className = classname;
		}
		x -= parent.map.getleft();
		y -= parent.map.gettop();
		if (x < 0 || y < 0 || x >= parent.map.getcx() || y >= parent.map.getcy()) return;  
		//alert("SetCellClass ("+x+","+y+","+classname+")");
		//document.getElementsByTagName("td")[<?=$gCX?>*y+x].attributes['class'].nodeValue = classname;
		parent.map.document.getElementsByTagName("div")[<?=$gCX?>*y+x].className = classname;
		*/
	}
	//SetCellClass(54,32,"cp");
	//SetCellClass(55,33,"cp");
//-->
</SCRIPT>

<!--mapcontrols-->
<table>
</td><td valign=top>
		
		<FORM METHOD=GET ACTION="<?=Query(kMapScript."?sid=?&big=?&cx=$gCX&cy=$gCY")?>" target="map" onSubmit="resettool()">
		<INPUT TYPE="hidden" NAME="sid" VALUE="<?=$gSID?>">
		<INPUT TYPE="hidden" NAME="x" VALUE="0" style="width:30px">
		<INPUT TYPE="hidden" NAME="y" VALUE="0" style="width:30px">
		<INPUT TYPE="text" NAME="pos" VALUE="0" style="width:60px">
		<INPUT TYPE="submit" VALUE="Goto">
		</FORM>
		
		<?php 
		$gArmy = cArmy::getMyArmies(TRUE,$gUser);
		$gMapMarks = sqlgettable("SELECT * FROM `mapmark` WHERE `user` = ".$gUser->id." ORDER BY `name`","id");
		$hellholes = $gUser->admin?sqlgettable("SELECT * FROM `hellhole` WHERE `ai_type` > 0 ORDER BY `id`"):array(); // todo : unhardcode
		$hellholetypename = array(1=>"orkdorf",2=>"megablob"); // todo : unhardcode
		?>
		<?php if (count($gArmy) > 0 || count($gMapMarks) > 0 || count($hellholes) > 0) {?>
		<FORM METHOD=GET ACTION="<?=Query(kMapScript."?sid=?&big=?&cx=$gCX&cy=$gCY")?>" target="map">
		<INPUT TYPE="hidden" NAME="sid" VALUE="<?=$gSID?>">
		<SELECT NAME="army"><?php /*  onChange="nav(0,0)" */ ?>
			<OPTION VALUE=0>--Armee wählen--</OPTION>
			<?php foreach($gArmy as $o) {?>
				<OPTION VALUE=<?=$o->id?>><?=$o->name?> (<?=$o->owner?>)</OPTION>
			<?php }?>
			<?php foreach($gMapMarks as $o) {?>
				<OPTION VALUE=<?=-$o->id?>><?=$o->name?>(<?=$o->x?>,<?=$o->y?>)</OPTION>
			<?php }?>
			<?php foreach($hellholes as $o) {?>
				<OPTION VALUE=<?="h".$o->id?>><?=$hellholetypename[$o->ai_type]."($o->x,$o->y)"?></OPTION>
			<?php }?>
		</SELECT>
		<INPUT TYPE="submit" NAME="armygoto" VALUE="Goto">
		</FORM>
		<?php }?>
		
		<?php if (0) {?><a href="javascript:HugeMap()">HugeMap</a><?php }?>
		<a href="javascript:BigMap()">BigMap</a>
		<a href="javascript:MiniMap()">MiniMap</a>
		<a href="javascript:MiniMap2()">MiniMap2</a>
		<a href="javascript:DiploMap()">DiploMap</a>
		<a href="javascript:CreepMap()">CreepMap</a>
		<?=cText::Wiki("MapModi")?>
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
