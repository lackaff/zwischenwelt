// available map-params : gCX,gCY,gLeft,gTop,gSID,gThisUserID,gGFXBase,gBig,gXMid,gYMid,gMapMode,gScroll
// available map-data : gTerrain,gBuildings,gArmies,gItems,gPlans,gOre??
// available globals : gTerrainTypes,gBuildingTypes...
// see also mapnavi_globals.js.php

// TODO : tor-zu- -> tor-offen

// the order in which fields are filled from mapjs7.php
var gUsers = new Array(); // filled with function, for string security
var gArmies = new Array(); // filled with function, for string security
var gXMid,gYMid;

kMapTipName = "maptip";
kMapTip_xoff = 29;
kMapTip_yoff = 56;
kJSMapMode_Normal = 0;
kJSMapMode_Plan = 1;
kJSMapMode_Bauzeit = 2;
kJSMapMode_HP = 3;


// executed onLoad, parse data, CreateMap() when finished
function MapInit() {
	var i;
	gXMid=Math.floor(gCX/2);
	gYMid=Math.floor(gCY/2);
	
	// parse data
	gTerrain = gTerrain.split(";");							
	for (i in gTerrain) gTerrain[i] = gTerrain[i].split(",");
	jsParseBuildings();
	jsParseItems();
	jsParsePlans();
	
	// parse and summarize units in armies (summoned units are added to normal units)
	for (i in gArmies) {
		var units = gArmies[i].units.split("|");
		gArmies[i].units = new Array();
		for (j in units) {
			var type_amount_pair = units[j].split(":");
			if (gArmies[i].units[type_amount_pair[0]] == undefined)
				gArmies[i].units[type_amount_pair[0]] = 0;
			gArmies[i].units[type_amount_pair[0]] += parseInt(type_amount_pair[1]); 
		}
	}
	
	CreateMap();
}

// createmap is called as soon as loading is complete
function CreateMap() {
	var row,x,y,i,j;
	
	var maphtml = "<table class=\"map\" border=0 cellpadding=0 cellspacing=0 onMouseout=\"KillTip()\">\n";
	
	// maptiles
	for (y=-1;y<gCY+1;++y) {
		row = "";
		for (x=-1;x<gCX+1;++x) if (x >= 0 && x < gCX && y >= 0 && y < gCY) {
			// cell
			row += "<td class=\"mapcell\">"+GetCellHTML(x,y)+"</td>\n";
		} else {
			// border
			var myclass = "mapborder_" + (y<gYMid ? "n" : (y==gYMid?"":"s")) + (x<gXMid ? "w" : (x==gXMid?"":"e"));
			if ((x < 0 || x >= gCX) && (y < 0 || y >= gCY)) myclass += "_edge";
			var navx = x<0?-1:(x>=gCX?1:0);
			var navy = y<0?-1:(y>=gCY?1:0);
			var text = (x < 0 || x >= gCX) ? (y+gTop) : (x+gLeft);
			row += "<th class=\"mapborder\"><div class=\""+myclass+"\" onClick=\"nav("+navx+","+navy+")\"><span>"+text+"</span></div></th>\n";
		}
		maphtml += "\n<tr>"+row+"</tr>\n";
	}
	
	maphtml += "</table>";
	
	
	var tab_pre = "";
	tab_pre += "<div id=\"panel\">";
	tab_pre += "	<div id=\"header\">";
	tab_pre += "		<ul>";
	tab_pre += "			<li "+(gMapMode==kJSMapMode_Normal?	"id=\"current\"":"")+"><a href=\"javascript:SetMapMode(kJSMapMode_Normal)\">Normal</a></li>";
	tab_pre += "			<li "+(gMapMode==kJSMapMode_Plan?	"id=\"current\"":"")+"><a href=\"javascript:SetMapMode(kJSMapMode_Plan)\">Pl&auml;ne</a></li>";
	tab_pre += "			<li "+(gMapMode==kJSMapMode_Bauzeit?"id=\"current\"":"")+"><a href=\"javascript:SetMapMode(kJSMapMode_Bauzeit)\">Bauzeit</a></li>";
	tab_pre += "			<li "+(gMapMode==kJSMapMode_HP?		"id=\"current\"":"")+"><a href=\"javascript:SetMapMode(kJSMapMode_HP)\">HP</a></li>";
	tab_pre += "		</ul>";
	tab_pre += "		<div id=\"icons\">";
	tab_pre += "			<a href=\"#\"><img alt=\"bigmap\" title=\"bigmap\" border=0 src=\"gfx/icon/bigmap.png\"></a>";
	tab_pre += "			<a href=\"#\"><img alt=\"minimap2\" title=\"minimap2\" border=0 src=\"gfx/icon/minimap2.png\"></a>";
	tab_pre += "			<a href=\"#\"><img alt=\"minimap\" title=\"minimap\" border=0 src=\"gfx/icon/minimap.png\"></a>";
	tab_pre += "			<span>(2,33)</span>";
	tab_pre += "		</div>";
	tab_pre += "	</div>";
	tab_pre += "	<div id=\"map\">";
		
	var tab_post = "";
	tab_post += "	</div>";
	tab_post += "</div>";

	/*
	tab_pre += "<table class=\"tabs\" cellspacing=0 cellpadding=0><tr>\n";
	tab_pre += "<td "+(gMapMode==kJSMapMode_Normal?	"id=\"current\"":"")+"><a href=\"javascript:SetMapMode(kJSMapMode_Normal)\">Normal</a></td>\n";
	tab_pre += "<td class=\""+(gMapMode==kJSMapMode_Plan?	"":"in"	)+"activetab\"><a href=\"javascript:SetMapMode(kJSMapMode_Plan)\">Pläne</a></td>\n";
	tab_pre += "<td class=\""+(gMapMode==kJSMapMode_Bauzeit?"":"in"	)+"activetab\"><a href=\"javascript:SetMapMode(kJSMapMode_Bauzeit)\">Bauzeit</a></td>\n";
	tab_pre += "<td class=\""+(gMapMode==kJSMapMode_HP?"":"in"		)+"activetab\"><a href=\"javascript:SetMapMode(kJSMapMode_HP)\">HP</a></td>\n";
	tab_pre += "<td class=\"tabfillerright\" width=\"100%\" align=\"right\">"+tab_topright+"</td>\n";
	tab_pre += "</tr><tr>\n";
	tab_pre += "<td class=\"tabpane\" colspan=5><div>\n";
	var tab_post = "\n</div></td></tr></table>\n";
	*/
	
	var maptiphtml = "<span class=\"maptip\" onMouseover=\"KillTip()\" name=\""+kMapTipName+"\" style=\"position:absolute;top:0px;left:0px; visibility:hidden;\"></span>";
	
	
	document.getElementById("mapzone").innerHTML = tab_pre + maphtml + tab_post + maptiphtml;
	return true;
}

function SetMapMode (newmode) {
	if (gMapMode == newmode) return;
	gMapMode = newmode;
	CreateMap();
}


function GetTerrainPic (terraintype,relx,rely) {
	var nwsecode = 0;
	if (terraintype == GetTerrainType(relx,rely-1)) nwsecode += kNWSE_N;
	if (terraintype == GetTerrainType(relx-1,rely)) nwsecode += kNWSE_W;
	if (terraintype == GetTerrainType(relx,rely+1)) nwsecode += kNWSE_S;
	if (terraintype == GetTerrainType(relx+1,rely)) nwsecode += kNWSE_E;
	return g_nwse(gTerrainType[terraintype].gfx,nwsecode);
}
function GetBuildingPic (building,relx,rely) {
	var buildingtype = building.type;
	var level = building.level;
	if (level < 10) level = 0; else level = 1;
	var nwsecode = 0;
	if (buildingtype == GetBuildingType(relx,rely-1)) nwsecode += kNWSE_N;
	if (buildingtype == GetBuildingType(relx-1,rely)) nwsecode += kNWSE_W;
	if (buildingtype == GetBuildingType(relx,rely+1)) nwsecode += kNWSE_S;
	if (buildingtype == GetBuildingType(relx+1,rely)) nwsecode += kNWSE_E;
	return g3(gBuildingType[buildingtype].gfx,nwsecode,level);
}

function GetCellHTML (relx,rely) {
	if (relx < 0 || rely < 0 || relx >= gCX || rely >= gCY) return "x";
	var i;
	var layers = new Array();
	
	// terrain
	var backgroundcolor = false;
	var terraintype = GetTerrainType(relx,rely);
	layers[layers.length] = GetTerrainPic(terraintype,relx,rely);
	
	// building
	var building = SearchPos(gBuildings,relx,rely);
	if (building) {
		if (building.construction > 0) {
			layers[layers.length] = g(kConstructionPic);
		} else {
			layers[layers.length] = GetBuildingPic(building,relx,rely);
			if (building.user > 0) backgroundcolor = gUsers[building.user].color;
		}
		if (gMapMode==kJSMapMode_HP) {
			backgroundcolor = GradientRYG(GetFraction(building.hp,calcMaxBuildingHp(building.type,building.level)))
		}
	}
	
	// plan
	var plan = SearchPos(gPlans,relx,rely);
	if (plan) {
		if (gMapMode==kJSMapMode_Plan) {
			backgroundcolor = "red";
			layers[layers.length] = g3(gBuildingType[plan.type].gfx,0,0);
		} else {
			layers[layers.length] = g(kTransCP);
		}
	}
	
	// item
	var item = SearchPos(gItems,relx,rely);
	var items = SearchPosArr(gItems,relx,rely);
	for (i in items) layers[layers.length] = g(gItemType[items[i].type].gfx);
	
	// army
	var army = SearchPos(gArmies,relx,rely);
	if (army) {
		nwsecode = 0;
		var unittype = GetArmyUnitType(relx,rely);
		if (UnitTypeHasNWSE(unittype)) { // hyperblob hack
			if (unittype == GetArmyUnitType(relx,rely-1)) nwsecode += kNWSE_N;
			if (unittype == GetArmyUnitType(relx-1,rely)) nwsecode += kNWSE_W;
			if (unittype == GetArmyUnitType(relx,rely+1)) nwsecode += kNWSE_S;
			if (unittype == GetArmyUnitType(relx+1,rely)) nwsecode += kNWSE_E;
		}
		layers[layers.length] = g2(gUnitType[unittype].gfx,nwsecode);
		if (army.user > 0) backgroundcolor = gUsers[army.user].color;
	}
	
	
	var i,res = "";
	if (backgroundcolor) res += "<div style=\"\">";
	for (i in layers) {
		var bg = (i==0)?("background-color:"+backgroundcolor+";"):"";
		res += "<div style=\"background-image:url("+layers[i]+"); "+bg+"\">";
	}
	res += "<div id=\"cell_"+relx+"_"+rely+"\" onClick=\"mapclick("+relx+","+rely+")\" onMouseover=\"mapover("+relx+","+rely+")\">";
	if (relx == gXMid && rely == gYMid) res += "<img src='gfx/crosshair.png'>";
	res += '</div>';
	for (i in layers) res += '</div>';
	if (backgroundcolor) res += '</div>';
	
	//if (relx == 4 && rely == 5) alert(g3(gBuildingType[buildingtype].gfx,nwsecode,level));
	// background-color:$b
	// onClick="nav(-1,-1)"
	//res = "<div onClick='m("+relx+","+rely+")'>"+res+"</div>";
	//res = "<div onClick='m("+relx+","+rely+")'>"+res+"</div>";
	//if (relx == 0 && rely == 0) alert(res);
	return res;
}

function mapclick (relx,rely) {
	//debuglog("c"+relx+","+rely);
	KillTip();	
}

function KillTip () {
	document.getElementsByName(kMapTipName)[0].style.visibility = "hidden";
}

function mapover (relx,rely) {
	// todo : if (GetTool() != lupe) { KillTip(); return; }

	// generate tip text
	var i;
	var tiptext = "<table>";

	// terrain
	var terraintype = GetTerrainType(relx,rely);
	tiptext += "<tr><td nowrap><img src=\""+GetTerrainPic(terraintype,relx,rely)+"\"></td><td nowrap colspan=2>";
	tiptext += "<span>"+(relx+","+rely)+"</span><br>";
	tiptext += "<span>"+gTerrainType[terraintype].name+"</span>";
	tiptext += "</td></tr>";
	
	// building
	var building = SearchPos(gBuildings,relx,rely);
	if (building) {
		tiptext += "<tr><td nowrap><img src=\""+GetBuildingPic(building,relx,rely)+"\"></td><td nowrap colspan=2>";
		tiptext += "<span>"+gBuildingType[building.type].name + " Stufe "+building.level + "</span><br>";
		tiptext += "<span>"+"HP : "+building.hp+"/"+calcMaxBuildingHp(building.type,building.level) + "</span><br>";
		if (building.user > 0) tiptext += "<span>"+gUsers[building.user].name + "</span>";
		// backgroundcolor = GradientRYG(GetFraction(hp,calcMaxBuildingHp(type,level)))
		tiptext += "</td></tr>";
	}
	
	// plan
	var plan = SearchPos(gPlans,relx,rely);
	if (plan) {
		tiptext += "<tr><td nowrap>";
		tiptext += "<img src=\""+g(kTransCP)+"\"></td><td nowrap>";
		tiptext += "<img src=\""+g3(gBuildingType[plan.type].gfx,0,0)+"\"></td><td nowrap>";
		tiptext += "<span>Bauplan</span><br>";
		tiptext += "<span>"+gBuildingType[plan.type].name+"</span>";
		tiptext += "</td></tr>";
	}
	
	// item
	var items = SearchPosArr(gItems,relx,rely);
	for (i in items) {
		var item = items[i];
		tiptext += "<tr><td nowrap>";
		tiptext += "<img class=\"maptippic\" src=\""+g(gItemType[item.type].gfx)+"\"></td><td nowrap colspan=2>";
		tiptext += "<span>"+gItemType[item.type].name+":"+item.amount+"</span>";
		tiptext += "</td></tr>";
	}
	
	// army
	var army = SearchPos(gArmies,relx,rely);
	if (army) {
		tiptext += "<tr><td nowrap colspan=3>";
		tiptext += "<span>"+army.name+"</span><br>";
		if (army.user > 0) tiptext += "<span>"+gUsers[army.user].name+"</span><br>";
		tiptext += "<span>";
		for (i in army.units) tiptext += "<img src=\""+g(gUnitType[i].gfx)+"\">"+army.units[i];
		tiptext += "</span>";
		tiptext += "</td></tr>";
	}
	tiptext += "</table>";
	
	// find a suitable position
	var x,y;
	x = kMapTip_xoff + kJSMapTileSize*relx;
	y = kMapTip_yoff + kJSMapTileSize*rely;
	// spawn tip
	var maptipnode = document.getElementsByName(kMapTipName)[0];
	maptipnode.innerHTML = tiptext;
	maptipnode.style.visibility = "visible";
	maptipnode.style.position = "absolute";
	if (gBig) {
		maptipnode.style.left = (kMapTip_xoff + kJSMapTileSize*(relx+1))+"px";
		maptipnode.style.top = (kMapTip_yoff + kJSMapTileSize*(rely+1))+"px";
	} else {
		if (relx >= gXMid)
				maptipnode.style.left = (kMapTip_xoff)+"px";
		else	maptipnode.style.left = (kMapTip_xoff+gXMid*kJSMapTileSize)+"px";
		if (rely >= gYMid)
				maptipnode.style.top = (kMapTip_yoff)+"px";
		else	maptipnode.style.top = (kMapTip_yoff+gYMid*kJSMapTileSize)+"px";
	}
}

function debuglog (text) {
	document.getElementsByName("mapdebug")[0].innerHTML = text+"<br>"+document.getElementsByName("mapdebug")[0].innerHTML;
}

	
function GetFraction (cur,max) { return (cur <= 0.0 || max == 0)?0.0:((cur >= max)?1.0:(cur / max)); }
function GradientRYG (factor) { // red-yellow-green
	var dist = Math.abs(factor - 0.5)*2.0;
	factor = 0.5 + 0.5*((factor>0.5)?1.0:(-1.0))*dist*dist;
	var r = 255.0*Math.min(1.0,2.0-factor*2.0);
	var g = 255.0*Math.min(1.0,factor*2.0);
	r = r.toString(16); if (r.length == 1) r = "0"+r; if (r.length > 2) r = "ff";
	g = g.toString(16); if (g.length == 1) g = "0"+g; if (g.length > 2) g = "ff";
	return "#"+r+g+"00";
}
function calcMaxBuildingHp(type,level) {
	var maxhp = gBuildingType[type].maxhp;
	return Math.ceil(maxhp + maxhp/100*1.5*level);
}

function SearchPos (arr,relx,rely) {
	var i; for (i in arr) {
		if (arr[i].x == relx + gLeft && 
			arr[i].y == rely + gTop) return arr[i];
	}
	return false;
}

function SearchPosArr (arr,relx,rely) {
	var res = new Array();
	var i; for (i in arr) {
		if (arr[i].x == relx + gLeft && 
			arr[i].y == rely + gTop) res.push(arr[i]);
	}
	return res;
}

function GetBuildingType (relx,rely) {
	var building = SearchPos(gBuildings,relx,rely);
	if (building == false) return 0;
	return building.type;
}

function GetTerrainType (relx,rely) {
	if (relx < -1 || rely < -1 || relx >= gCX+1 || rely >= gCY+1) return kDefaultTerrainID;
	var terraintype = gTerrain[rely+1][relx+1];
	if (terraintype == 0) return kDefaultTerrainID;
	return terraintype;
}

function GetArmyUnitType (relx,rely) {
	var army = SearchPos(gArmies,relx,rely);
	if (!army) return 0;
	var i,maxtype=0,maxamount=0;
	for (i in army.units)
		if (maxamount < army.units[i]) {
			maxamount < army.units[i];
			maxtype = i;
		}
	return maxtype;
}

// simple,fast versions of g
function g (path) { return gGFXBase+path; }
function g_nwse (path,nwse_code) { return gGFXBase+path.split("%NWSE%").join(NWSECodeToStr(nwse_code)); }

// the original g function reimplemented (SLOW!!!)
function g1 (path) { return g2(path,"ns"); }
function g2 (path,nwse) { return g3(path,nwse,0); }
function g3 (path,nwse,level) { return g4(path,nwse,level,0); }
function g4 (path,nwse,level,race) { return g5(path,nwse,level,race,100); }
function g5 (path,nwse,level,race,moral) {
	moral = Math.max(0,Math.min(200,moral));
	//moral range from 0 - 4
	moral = Math.round(moral/200*4);
	if (race == 0) race = 1;
	return gGFXBase+path.split("%M%").join(moral).split("%R%").join(race).split("%NWSE%").join(NWSECodeToStr(nwse)).split("%L%").join(level);
	// return str_replace("%M%",$moral,str_replace("%R%",$race,str_replace("%NWSE%",$nwse,str_replace("%L%",$level,$base.$path))));
}

function NWSECodeToStr (code) {
	var out = "";
	if(code & kNWSE_N) out += "n";
	if(code & kNWSE_W) out += "w";
	if(code & kNWSE_S) out += "s";
	if(code & kNWSE_E) out += "e";
	return out;
}


function nav(x,y) {
	if (x < 0) x = -1; else if (x > 0) x = 1;
	if (y < 0) y = -1; else if (y > 0) y = 1;
	//var scroll = document.getElementsByName("myscroll")[0].value; TODO : restore
	var scroll = gScroll;
	x = gLeft + gXMid + x * scroll;
	y = gTop + gYMid + y * scroll;
	location.href = "?sid="+gSID+"&x="+x+"&y="+y+"&mode="+gMapMode;
}


/*

.wp { background-color:#00FF00; }
.pathb { background-color:#FF8888; }
.path { background-color:#88FF88; }
.cp { background-image:url(<?=g(kConstructionPlanPic)?>); }
.tcp { background-image:url(<?=g(kTransCP)?>); }
.con { background-image:url(<?=g(kConstructionPic)?>); }
.gr { background-image:url(<?=g("grass.png")?>); }



	define("kMapColor_Hilight","#FFFFFF");
	define("kMapColor_Neutral_User","#66AA55");

	document.getElementById("age").innerHTML = gAge + " =)";
	
	gHalloWeltInterval = window.setInterval("HalloWeltStep()",50);
	window.clearInterval(gHalloWeltInterval);
	
	parseInt(..);

	<td valign="top" align="left">
		<!--Navigation-->
		<table class="mapnav" cellpadding="0" cellspacing="0"><?php
		?><tr><?php
		?><td><img src="<?=g("scroll/nw.png")?>" onClick="nav(-1,-1)"></td><?php
		?><td><img src="<?=g("scroll/n.png")?>" onClick="nav(0,-1)"></td><?php
		?><td><img src="<?=g("scroll/ne.png")?>" onClick="nav(1,-1)"></td><?php
		?></tr><tr><?php
		?><td><img src="<?=g("scroll/w.png")?>" onClick="nav(-1,0)"></td><?php
		?><td><img src="<?=g("scroll/r.png")?>" onClick="nav(0,0)"></td><?php
		?><td><img src="<?=g("scroll/e.png")?>" onClick="nav(1,0)"></td><?php
		?></tr><tr><?php
		?><td><img src="<?=g("scroll/sw.png")?>" onClick="nav(-1,1)"></td><?php
		?><td><img src="<?=g("scroll/s.png")?>" onClick="nav(0,1)"></td><?php
		?><td><img src="<?=g("scroll/se.png")?>" onClick="nav(1,1)"></td><?php
		?></tr><?php
		?></table>
		<FORM METHOD="POST" ACTION="<?=Query(kMapScript."?sid=?&big=?&cx=$gCX&cy=$gCY")?>">
		<INPUT TYPE="hidden" NAME="sid" VALUE="<?=$gSID?>">
		<INPUT TYPE="hidden" NAME="x" VALUE="0" style="width:30px">
		<INPUT TYPE="hidden" NAME="y" VALUE="0" style="width:30px">
		<INPUT TYPE="text" NAME="pos" VALUE="0" style="width:60px">
		<INPUT TYPE="submit" VALUE="Goto">
		scroll:
		<a href="javascript:void(document.getElementsByName('myscroll')[0].value = Math.floor(document.getElementsByName('myscroll')[0].value / 2))">-</a>
		<a href="javascript:void(document.getElementsByName('myscroll')[0].value *= 2)">+</a>
		<INPUT TYPE="text" NAME="myscroll" VALUE="<?=$gScroll?>" style="width:30px">
		</FORM>
	</td>
	


function getWindowWidth()
{
	if (window.innerWidth)return window.innerWidth;
	else if (document.documentElement && document.documentElement.clientWidth != 0)return document.documentElement.clientWidth;
	else if (document.body)return document.body.clientWidth;
	return 0;
}

var mapwidth = Math.floor((getWindowWidth()-2*40)/<?=$gTilesize?>);
//alert(mapwidth+" "+getWindowWidth());

<?php if (isset($f_big)) { // navi ?>
function nav(x,y) {
	var scroll = document.getElementsByName("myscroll")[0].value;
	x = <?=intval($f_x)?> + x * scroll;
	y = <?=intval($f_y)?> + y * scroll;
	location.href = "<?=Query("?sid=?&big=?&army=?&mode=?&cx=?&cy=?&")?>x="+(x)+"&y="+(y); 
}
<?php } // endif?>
function getmode() { return "<?=$f_mode?>";}
function getleft() { return <?=$gLeft?>;}
function gettop() { return <?=$gTop?>;}
function getx() { return <?=$gX?>;}
function gety() { return <?=$gY?>;}
function getcx() { return <?=$gCX?>;}
function getcy() { return <?=$gCY?>;}
function m(x,y) {
	<?php if (isset($f_big)) {?>
	//opener.parent.info.location.href = "info/info.php?x="+(x+<?=$gLeft?>)+"&y="+(y+<?=$gTop?>)+"&sid=<?=$gSID?>";
	opener.parent.navi.map(x+<?=$gLeft?>,y+<?=$gTop?>);
	<?php } else {?>
	parent.navi.map(x+<?=$gLeft?>,y+<?=$gTop?>);
	<?php }?>
}
<?php if (!isset($f_naviset)) {?>
if (parent.navi != null && parent.navi.updatepos != null)
	parent.navi.updatepos(<?=$gX?>,<?=$gY?>);
<?php }?>
<?php if ($f_mode == "bauplan" && $concount == 0 && 0) {?> 
alert("Der Knopf Pläne zeigt Baupläne als fertige Gebäude an,\n damit man eine übersicht hat, was man wo geplant hat.");
<?php }?>

*/

