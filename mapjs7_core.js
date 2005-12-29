// available map-params : gCX,gCY,gLeft,gTop,gSID,gThisUserID,gGFXBase,gBig,gXMid,gYMid,gMapMode,gScroll
// available map-data : gTerrain,gBuildings,gArmies,gItems,gPlans,gOre??
// available globals : gTerrainTypes,gBuildingTypes...
// see also mapnavi_globals.js.php

// TODO : tor-zu- -> tor-offen
// TODO : user anzeig : gilde, punktestand, rang...
// TODO : armee - anzeige : gilde,sprechblasen ?
// TODO : tore : auf/zu grafik + tip (für wen auf/zu/zoll...)
// TODO : portal : tip (verbunden mit für wen zoll/auf/zu)


// the order in which fields are filled from mapjs7.php
gUsers = new Array(); // filled with function, for string security
gArmies = new Array(); // filled with function, for string security
gTerrainPatchTypeMap = new Array(); // generated from gTerrainPatchType
gTerrainMap = false; // generated from MapInit
var gXMid,gYMid;
gNWSEDebug = true; // shows typeid and connect-to infos in maptip

kMapTipName = "maptip";
kMapTip_xoff = 29;
kMapTip_yoff = 56;
kJSMapMode_Normal = 0;
kJSMapMode_Plan = 1;
kJSMapMode_Bauzeit = 2;
kJSMapMode_HP = 3;
gLoading = true; // set to true when navi is clicked
gAllLoaded = false; // mouselistener protection

// executed onLoad, parse data, CreateMap() when finished
function MapInit() {
	var i,j,x,y;
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
		gArmies[i].units = ParseTypeAmountList(gArmies[i].units);
		gArmies[i].items = ParseTypeAmountList(gArmies[i].items);
	}
	
	for (i in gTerrainType) {
		gTerrainType[i].connectto_terrain = gTerrainType[i].connectto_terrain.split(",");
		gTerrainType[i].connectto_building = gTerrainType[i].connectto_building.split(",");
	}
	for (i in gBuildingType) {
		gBuildingType[i].connectto_terrain = gBuildingType[i].connectto_terrain.split(",");
		gBuildingType[i].connectto_building = gBuildingType[i].connectto_building.split(",");
	}
	HackCon(); // HACK : hardcoded connections, see mapjs7_globals.js.php
	
	for (i in gTerrainPatchType) {
		var x = gTerrainPatchType[i];
		if (gTerrainPatchTypeMap[x.here] == undefined)
			gTerrainPatchTypeMap[x.here] = new Array();
		gTerrainPatchTypeMap[x.here].push(x);	
	}
	
	// construct terrain map
	var termap_raw = new Array(gCY+2);
	var termap_nwse = new Array(gCY+2);
	
	// first pass, simple nwse (with connect to building/terrain...)
	for (y=-1;y<gCY+1;++y) {
		termap_raw[y+1] = new Array(gCX+2);
		termap_nwse[y+1] = new Array(gCX+2);
		for (x=-1;x<gCX+1;++x) {
			var terraintype = GetTerrainType(x,y);
			termap_raw[y+1][x+1] = gTerrainType[terraintype].gfx;
			termap_nwse[y+1][x+1] = GetNWSE(gTerrainType[terraintype],x,y);
		}
	}
	
	// second pass : terrain patches
	for (x=-1;x<gCX+1;++x) for (y=-1;y<gCY+1;++y) {
		var type = GetTerrainType(x,y);
		if (!KeyInArray(type,gTerrainPatchTypeMap)) continue;
		var patches = gTerrainPatchTypeMap[type];
		for (i in patches) {
			var o = patches[i];
			if(	(o.left	==0 || (o.left	>0 && GetTerrainType(x-1,y) == o.left)) &&
				(o.right==0 || (o.right	>0 && GetTerrainType(x+1,y) == o.right)) &&
				(o.up	==0 || (o.up	>0 && GetTerrainType(x,y-1) == o.up)) &&
				(o.down	==0 || (o.down	>0 && GetTerrainType(x,y+1) == o.down)) ) {
				termap_raw[y+1][x+1] = o.gfx;
				if (o.left	>0 && x >= 0)	termap_nwse[y+1][x+1-1] |= kNWSE_E;
				if (o.right	>0 && x < gCX)	termap_nwse[y+1][x+1+1] |= kNWSE_W;
				if (o.up	>0 && y >= 0)	termap_nwse[y+1-1][x+1] |= kNWSE_S;
				if (o.down	>0 && y < gCY)	termap_nwse[y+1+1][x+1] |= kNWSE_N;
			}
		}
	}
	
	// compile to terrainmap
	gTerrainMap = new Array(gCY+2);
	for (y=-1;y<gCY+1;++y) {
		gTerrainMap[y+1] = new Array(gCX+2);
		for (x=-1;x<gCX+1;++x)
			gTerrainMap[y+1][x+1] = g_nwse(termap_raw[y+1][x+1],termap_nwse[y+1][x+1]);
	}
	
	CreateMap();
	gLoading = false;
}

function ParseTypeAmountList (list) {
	var arr = list.split("|");
	arr.pop();
	var i,res = new Array();
	for (i in arr) {
		var type_amount_pair = arr[i].split(":");
		if (res[type_amount_pair[0]] == undefined)
			res[type_amount_pair[0]] = 0;
		res[type_amount_pair[0]] += parseInt(type_amount_pair[1]);
	}
	return res;
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
	
	var tab_corner = "";
	tab_corner += "<span class=\"mapscroll\">";
	tab_corner += "<span class=\"mapscroll_minus\"><a href=\"javascript:mapscroll_minus()\">-</a></span>";
	tab_corner += "<span class=\"mapscroll_plus\"><a href=\"javascript:mapscroll_plus()\">+</a></span>";
	tab_corner += "<input type=\"text\" name=\"mapscroll\" value=\""+gScroll+"\">";
	tab_corner += "</span>";
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
	tab_pre += "			<a href=\"javascript:OpenMap(1)\"><img alt=\"bigmap\" title=\"bigmap\" border=0 src=\"gfx/icon/bigmap.png\"></a>";
	tab_pre += "			<a href=\"javascript:OpenMap(2)\"><img alt=\"minimap2\" title=\"minimap2\" border=0 src=\"gfx/icon/minimap2.png\"></a>";
	tab_pre += "			<a href=\"javascript:OpenMap(3)\"><img alt=\"minimap\" title=\"minimap\" border=0 src=\"gfx/icon/minimap.png\"></a>";
	tab_pre += "			<a href=\"javascript:OpenMap(4)\"><img alt=\"diplomap\" title=\"diplomap\" border=0 src=\"gfx/icon/minimap.png\"></a>";
	tab_pre += "			<a href=\"javascript:OpenMap(5)\"><img alt=\"creepmap\" title=\"creepmap\" border=0 src=\"gfx/icon/minimap.png\"></a>";
	tab_pre += "			<span>"+tab_corner+"</span>";
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

function OpenMap (type) {
	alert("todo:open map now");
}

function mapscroll_plus() {
	document.getElementsByName('mapscroll')[0].value *= 2;
}
function mapscroll_minus() {
	document.getElementsByName('mapscroll')[0].value = Math.floor(document.getElementsByName('mapscroll')[0].value / 2);
}
	
function SetMapMode (newmode) {
	if (gMapMode == newmode) return;
	gMapMode = newmode;
	CreateMap();
}

function GetCellHTML (relx,rely) {
	if (relx < 0 || rely < 0 || relx >= gCX || rely >= gCY) return "x";
	var i;
	var layers = new Array();
	
	// terrain
	var backgroundcolor = false;
	layers[layers.length] = GetTerrainPic(relx,rely);
	
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
	res += "<div name=\"mouselistener\" id=\"cell_"+relx+"_"+rely+"\" onClick=\"mapclick("+relx+","+rely+")\" onMouseover=\"if (gAllLoaded) if (!gLoading) mapover("+relx+","+rely+")\">";
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
	if (gLoading) return;
	//debuglog("c"+relx+","+rely);
	KillTip();
	if (gBig) {
		//opener.parent.info.location.href = "info/info.php?x="+(x+gLeft)+"&y="+(y+gTop)+"&sid="+gSID;
		opener.parent.navi.map(x+gLeft,y+gTop);
	} else {
		parent.navi.map(x+gLeft,y+gTop);
	}
}

function KillTip () {
	if (gLoading) return;
	document.getElementsByName(kMapTipName)[0].style.visibility = "hidden";
}

function mapover (relx,rely) {
	if (gLoading) return;
	// todo : if (GetTool() != lupe) { KillTip(); return; }

	// generate tip text
	var i;
	var tiptext = "<table>";

	// terrain
	var terraintype = GetTerrainType(relx,rely);
	tiptext += "<tr><td nowrap align=\"left\"><img src=\""+GetTerrainPic(relx,rely)+"\"></td><td nowrap colspan=2>";
	tiptext += "<span>"+(relx+","+rely)+"</span><br>";
	tiptext += "<span>"+gTerrainType[terraintype].name+"</span>";
	if (gNWSEDebug) tiptext += "<br><span>type="+terraintype+"</span>";
	if (gNWSEDebug) tiptext += "<br><span>tc="+gTerrainType[terraintype].connectto_terrain.join(",")+"</span>";
	if (gNWSEDebug) tiptext += "<br><span>bc="+gTerrainType[terraintype].connectto_building.join(",")+"</span>";
	tiptext += "</td></tr>";
	
	// building
	var building = SearchPos(gBuildings,relx,rely);
	if (building) {
		tiptext += "<tr><td nowrap><img src=\""+GetBuildingPic(building,relx,rely)+"\"></td><td nowrap colspan=2 align=\"left\">";
		tiptext += "<span>"+gBuildingType[building.type].name + " Stufe "+building.level + "</span><br>";
		tiptext += "<span>"+"HP : "+building.hp+"/"+calcMaxBuildingHp(building.type,building.level) + "</span><br>";
		if (building.user > 0) tiptext += "<span>"+gUsers[building.user].name + "</span>";
		if (gNWSEDebug) tiptext += "<br><span>type="+building.type+"flags="+building.jsflags+"</span>";
		if (gNWSEDebug) tiptext += "<br><span>tc="+gBuildingType[building.type].connectto_terrain.join(",")+"</span>";
		if (gNWSEDebug) tiptext += "<br><span>bc="+gBuildingType[building.type].connectto_building.join(",")+"</span>";
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
		tiptext += "<span>"+gItemType[item.type].name+":"+TausenderTrenner(item.amount)+"</span>";
		tiptext += "</td></tr>";
	}
	
	// army
	var army = SearchPos(gArmies,relx,rely);
	if (army) {
		tiptext += "<tr><td nowrap colspan=3>";
		tiptext += "<span>"+army.name+"</span><br>";
		if (army.user > 0) tiptext += "<span>"+gUsers[army.user].name+"</span><br>";
		tiptext += "<span>";
		if (army.units.length > 0) for (i in army.units)
			tiptext += "<img src=\""+g(gUnitType[i].gfx)+"\">"+TausenderTrenner(army.units[i]);
		tiptext += "</span><br>";
		tiptext += "<span>";
		if (army.items.length > 0) for (i in army.items)
			tiptext += "<img src=\""+g(gItemType[i].gfx)+"\">"+TausenderTrenner(army.items[i]);
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

// utilities

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
function NWSECodeToStr (code) {
	var out = "";
	if(code & kNWSE_N) out += "n";
	if(code & kNWSE_W) out += "w";
	if(code & kNWSE_S) out += "s";
	if(code & kNWSE_E) out += "e";
	return out;
}
function InArray(needle,haystack) {
	// != ""  :   javascript : "".split(",") liefert ein array mit EINEM ELEMENT : dem leeren string  -> rausfiltern
	var i;for (i in haystack) if (haystack[i] == needle && haystack[i] != "") return true;
	return false;
}
function KeyInArray(needle,haystack) {
	var i;for (i in haystack) if (i == needle) return true;
	return false;
}
function TausenderTrenner (nummertext) {
	nummertext = ""+nummertext;
	var blocks = Math.floor((nummertext.length+2)/3);
	var i,j,res = "";
	for (i=0;i<blocks;++i) {
		if (3*i+0 < nummertext.length) res = nummertext[nummertext.length-1 - (3*i+0)]+res;
		if (3*i+1 < nummertext.length) res = nummertext[nummertext.length-1 - (3*i+1)]+res;
		if (3*i+2 < nummertext.length) res = nummertext[nummertext.length-1 - (3*i+2)]+res;
		if (3*i+3 < nummertext.length) res = "."+res;
	}
	return res;
}

// type at position

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

// terrain/building pic + nwse

function GetNWSE (typeobj,relx,rely) {
	var nwsecode = 0;
	var ct = typeobj.connectto_terrain;
	var cb = typeobj.connectto_building;
	if (InArray(GetTerrainType(relx,rely-1),ct) || InArray(GetBuildingType(relx,rely-1),cb)) nwsecode += kNWSE_N;
	if (InArray(GetTerrainType(relx-1,rely),ct) || InArray(GetBuildingType(relx-1,rely),cb)) nwsecode += kNWSE_W;
	if (InArray(GetTerrainType(relx,rely+1),ct) || InArray(GetBuildingType(relx,rely+1),cb)) nwsecode += kNWSE_S;
	if (InArray(GetTerrainType(relx+1,rely),ct) || InArray(GetBuildingType(relx+1,rely),cb)) nwsecode += kNWSE_E;
	return nwsecode;
}
function GetTerrainPic (relx,rely) {
	if (gTerrainMap) return gTerrainMap[rely+1][relx+1];
	var terraintype = GetTerrainType(relx,rely);
	var nwsecode = GetNWSE(gTerrainType[terraintype],relx,rely);
	return g_nwse(gTerrainType[terraintype].gfx,nwsecode);
}
function GetBuildingPic (building,relx,rely) {
	var type = building.type;
	var level = building.level;
	if (level < 10) level = 0; else level = 1;
	var nwsecode = GetNWSE(gBuildingType[type],relx,rely);
	var gfx = gBuildingType[type].gfx;
	
	// TODO: FIXME: HACK: (gates&portal)  also in mapstyle_buildings.php and GetBuildingCSS()
	if (building.jsflags & kJSMapBuildingFlag_Open) 
		gfx = gfx.split("-zu-").join("-offen-"); 
		
	// HACK: special nwse for path,gates,bridge...  also in UpdateBuildingNWSE()
	nwsecode = HackNWSE(type,nwsecode,relx,rely); // see mapjs7_globals.js.php
		
	return g3(gfx,nwsecode,level);
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

// interaction

function nav(x,y) {
	gLoading = true;
	gAllLoaded = false;
	// alle elemente mit javascript-mouseover deaktivieren, um javascript fehler beim laden zu verhindern
	var i,mouselistener = document.getElementsByName("mouselistener");
	for (i in mouselistener) mouselistener[i].onMouseover = "";
	gScroll = parseInt(document.getElementsByName("mapscroll")[0].value);
	if (x < 0) x = -1; else if (x > 0) x = 1;
	if (y < 0) y = -1; else if (y > 0) y = 1;
	x = gLeft + gXMid + x * gScroll;
	y = gTop + gYMid + y * gScroll;
	location.href = "?sid="+gSID+"&x="+x+"&y="+y+"&mode="+gMapMode+"&scroll="+gScroll;
}

function debuglog (text) {
	document.getElementsByName("mapdebug")[0].innerHTML = text+"<br>"+document.getElementsByName("mapdebug")[0].innerHTML;
}

gAllLoaded = true; 
// scheiss race conditions beim navigieren im mouseover, krieg ich sogar hiermit nicht ganz weg...
