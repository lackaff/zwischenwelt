// available map-params : gCX,gCY,gLeft,gTop,gSID,gThisUserID,gGFXBase,gBig,gXMid,gYMid,gMapMode,gScroll,gActiveArmyID
// available map-data : gTerrain,gBuildings,gArmies,gItems,gPlans,gOre??
// available globals : gTerrainTypes,gBuildingTypes...
// see also mapjs7_globals.js.php

// TODO : user anzeig : gilde, punktestand, rang...
// TODO : armee - anzeige : gilde,sprechblasen ?
// TODO : portal : maptip : schloss/lock grafik, wenn nicht benutzbar ?
// TODO : localusers : FOF auslesen -> namen einfärben in maptip
// TODO : gebäude hp-balken + farbe im maptip
// TODO : schild-text im tooltip ?
// TODO : brücken an fluss ausrichten !
// TODO : tor nwse bug : connect-to-building : self rausschmeissen
// TODO : tooltip : einheiten reihenfolge stimmt nicht (orderval ?)

// the order in which fields are filled from mapjs7.php
gUsers = new Array(); // filled with function, for string security
gArmies = new Array(); // filled with function, for string security
gBuildingsCache = false;
gTerrainPatchTypeMap = new Array(); // generated from gTerrainPatchType
gTerrainMap = false; // generated from CompileTerrain()
gTerrainMap_raw = false; // generated from CompileTerrain()
gWPMap = false; // generated from CompileWPs
gWPMapDirty = true;
var gXMid,gYMid;
gNWSEDebug = false; // shows typeid and connect-to infos in maptip
gPathDetected = 0;
gStaticCellInner = ""; // set to IE blind-gif (otpionally from gfxpack)
gWPAffectedCells = new Array();
gActiveArmyMarkerGfx = "";
gReportedWPMaxPrio = -1;


// compare version with kBaseJSMapVersion from mapjs7.php and kNaviJSMapVersion available with GetkNaviJSMapVersion
kMapTipName = "maptip";
kMapTip_xoff = 29;
kMapTip_yoff = 56;
kJSMapMode_Normal = 0;
kJSMapMode_Plan = 1;
kJSMapMode_Bauzeit = 2;
kJSMapMode_HP = 3;
gBigMapWindow = false;
gLastDebugTime = 0;
gProfileLastLine = "";
gMapConstructionCurY = -1;
gMapHTML = false;

// set border around map, used to indicate brushline modus
function SetBorder (colorcode) {
	document.getElementById("totalmapborder").style.borderColor = colorcode + " " + colorcode + " " + colorcode + " " + colorcode;
	if (!gBig && gBigMapWindow && !gBigMapWindow.closed) gBigMapWindow.SetBorder(colorcode);
}
function MapReport (text) {
	document.getElementById("mapdebug").innerHTML = text;
}

// a single location to secure all access
function GetNaviFrame () {
	if (gBig) {
		if (opener) if (opener.parent) if (opener.parent.navi) return opener.parent.navi;
		return false;
	} else {
		if (parent) if (parent.navi) return parent.navi;
		return false;
	}
}

// obsolete interface
function getmode() { return gMode;}
function getmode() { return gMode;}
function getleft() { return gLeft;}
function gettop() { return gTop;}
function getx() { return gLeft+gXMid;}
function gety() { return gTop+gYMid;}
function getcx() { return gCX;}
function getcy() { return gCY;}


// new interface
function JSGetActiveArmyID() { return gActiveArmyID; }
function JSInsertPlan (x,y,type,priority) {
	var res = new Object();
	res.x = x;
	res.y = y;
	res.type = type;
	res.priority = priority;
	gPlans[gPlans.length] = res;
	RefreshCell(x-gLeft,y-gTop);
	if (!gBig && gBigMapWindow && !gBigMapWindow.closed) gBigMapWindow.JSInsertPlan(x,y,type,priority);
}
function JSRemovePlan (x,y) {
	if (!gBig && gBigMapWindow && !gBigMapWindow.closed) gBigMapWindow.JSRemovePlan(x,y);
	var i; 
	for (i in gPlans) if (gPlans[i].x == x && gPlans[i].y == y) gPlans[i] = false;
	RefreshCell(x-gLeft,y-gTop);
}
function JSZap (x,y) { JSAdminClear(x,y); }
function JSRuin (x,y) { JSAdminClear(x,y); }
function JSRemoveItems (x,y) { JSAdminClear(x,y); }
function JSRemoveArmy (x,y) { JSAdminClear(x,y); }
function JSAdminClear (x,y) { JSClear(x,y); }
	
function JSClear (x,y) {
	if (!gBig && gBigMapWindow && !gBigMapWindow.closed) gBigMapWindow.JSClear(x,y);
	if (y-gTop+1 < 0 || y-gTop+1 >= gCY+2 || x-gLeft+1 < 0 || x-gLeft+1 >= gCX+2) return;
	var i;
	for (i in gArmies) if (gArmies[i].x == x && gArmies[i].y == y) gArmies[i] = false;
	for (i in gItems) if (gItems[i].x == x && gItems[i].y == y) gItems[i] = false; 
	gBuildingsCache[y-gTop+1][x-gLeft+1] = false;
}
function JSSetTerrain (x,y,type,brushrad) { 
	if (!gBig && gBigMapWindow && !gBigMapWindow.closed) gBigMapWindow.JSSetTerrain (x,y,type,brushrad);
	if (y-gTop+1 < 0 || y-gTop+1 >= gCY+2 || x-gLeft+1 < 0 || x-gLeft+1 >= gCX+2) return;
	// ./infoadmincmd.php:298:  ... more params : line,terraformer-limit..
	// TODO : implement brush/line, currently ignored
	x -= gLeft; y -= gTop;
	if (type == 0) type = kDefaultTerrainID;
	gTerrain[y+1][x+1] = type;
}

function JSRefreshCell (x,y) { 
	if (!gBig && gBigMapWindow && !gBigMapWindow.closed) gBigMapWindow.JSRefreshCell(x,y);
	if (y-gTop+1 < 0 || y-gTop+1 >= gCY+2 || x-gLeft+1 < 0 || x-gLeft+1 >= gCX+2) return;
	var relx = x - gLeft;
	var rely = y - gTop;
	var type = gTerrain[rely+1][relx+1];
	// patches have been ignored for speed here
	gTerrainMap[rely+1][relx+1] = g_nwse(gTerrainType[type].gfx,GetNWSE(gTerrainType[type],relx,rely));
	RefreshCell(x-gLeft,y-gTop);
}

function JSInsertItem (x,y,type,amount) {
	var res = new Object();
	res.x = x;
	res.y = y;
	res.type = type;
	res.amount = amount;
	gItems[gItems.length] = res;
}
function JSBuildingUpdate (x,y,type,user,level,hp,construction,jsflags,unitstxt,id) {
	if (!gBig && gBigMapWindow && !gBigMapWindow.closed) gBigMapWindow.JSBuildingUpdate(x,y,type,user,level,hp,construction,jsflags,unitstxt,id);
	if (y-gTop+1 < 0 || y-gTop+1 >= gCY+2 || x-gLeft+1 < 0 || x-gLeft+1 >= gCX+2) return;
	var res = new Object();
	res.x = x;
	res.y = y;
	res.type = type;
	res.user = user;
	res.level = level;
	res.hp = hp;
	res.construction = construction;
	res.jsflags = jsflags;
	res.units = ParseTypeAmountList(unitstxt);
	res.id = id;
	gBuildingsCache[y-gTop+1][x-gLeft+1] = res;
}
						
function JSArmyUpdate (	id,x,y,name,type,user,unitstxt,itemstxt,jsflags,wpstxt,lastwpx,lastwpy,wpmaxprio,fill_limit,fill_last) {
	jsArmy(id,x,y,name,type,user,unitstxt,itemstxt,jsflags,wpstxt,lastwpx,lastwpy,wpmaxprio,fill_limit,fill_last);
	gArmies[id].units = ParseTypeAmountList(gArmies[id].unitstxt);
	gArmies[id].items = ParseTypeAmountList(gArmies[id].itemstxt);
	gArmies[id].wps = ParseWPs(gArmies[id].wpstxt);
	
	if (id == gActiveArmyID) {
		gWPMapDirty = true; // regen needed TODO refresh map here ? or better afterwards ?
		gReportedWPMaxPrio = wpmaxprio;
	}
	
	// alert("JSArmyUpdate("+name+")");
	
	// JSArmyUpdate :: TODO : army.wps = parse(wpstxt)
	if (!gBig && gBigMapWindow && !gBigMapWindow.closed) 
		gBigMapWindow.JSArmyUpdate(id,x,y,name,type,user,unitstxt,itemstxt,jsflags,wpstxt,lastwpx,lastwpy,wpmaxprio,fill_limit,fill_last);
}

function ParseWPs (wpstxt) {
	var i,arr;
	wpstxt = wpstxt.split(";");	
	wpstxt.length=wpstxt.length-1;
	for (i in wpstxt) if (wpstxt[i] == "") {
		wpstxt[i] = false;
	} else {
		arr = wpstxt[i].split(",");
		var res = new Object();
		res.x = arr[0];res.y = arr[1];wpstxt[i] = res;
	}
	return wpstxt;
}
	
function JSActivateArmy (armyid,refreshnavi) {
	if (!gBig && gBigMapWindow && !gBigMapWindow.closed) gBigMapWindow.JSActivateArmy(armyid,false);
	var army = gArmies[armyid] ? gArmies[armyid] : false;
	var newactiveid = (army && (army.jsflags & kJSMapArmyFlag_Controllable)) ? armyid : 0;
	if (gActiveArmyID == newactiveid && !gWPMapDirty) return; // nothing to do
	var armychanged = (gActiveArmyID != newactiveid);
	//alert("JSActivateArmy("+armyid+"),gActiveArmyID="+gActiveArmyID+",newactiveid="+newactiveid+",newactivename="+(newactiveid?gArmies[armyid].name:"")+",armychanged="+(armychanged?1:0));
	if (armychanged) {
		if (gActiveArmyID) SetArmyMarker(gActiveArmyID,'');
		if (newactiveid) SetArmyMarker(newactiveid,gActiveArmyMarkerGfx);
	}
	
	gActiveArmyID = newactiveid;
	RedrawWPs();
	JSUpdateNaviPos();
	if (refreshnavi && armychanged && !gBig) {
		var naviframe = GetNaviFrame();
		if (naviframe) naviframe.SelectArmy(gActiveArmyID);
	}
}

function CellIsWPAffected (relx,rely) {
	if (gWPAffectedCells[rely][relx]) return true;
	if (gActiveArmyID) {
		var army = GetActiveArmy();
		if (army) {
			var wp = SearchPos(gWPs,relx,rely);
			if (wp) return true;
		}
		if (gWPMap) if (gWPMap[relx][rely].length > 0) return true;
	}
	return false;
}

function RedrawWPs () {
	var i,relx,rely;
	CompileWPs();
	for (rely=0;rely<gCY;++rely)
	for (relx=0;relx<gCX;++relx) if (CellIsWPAffected(relx,rely)) {
		document.getElementById("wpzone_"+rely+"_"+relx).innerHTML = ApplyWPGfx(relx,rely,gStaticCellInner);
	}
}

function AddWP (absx,absy) {
	if (!gActiveArmyID) return;
	var activearmy = GetActiveArmy();
	if (!activearmy) return;
	
	var wps = activearmy.wps;
	var oldwp = new Object();
	var newwp = new Object();
	oldwp.x = activearmy.lastwpx;
	oldwp.y = activearmy.lastwpy;
	newwp.x = absx;
	newwp.y = absy;
	wps[wps.length] = false;
	wps[wps.length] = oldwp;
	wps[wps.length] = newwp;
	
	gReportedWPMaxPrio = activearmy.wpmaxprio;
	if (activearmy.wpmaxprio == -1)
			activearmy.wpmaxprio = 1;
	else	activearmy.wpmaxprio++;
	activearmy.lastwpx = absx;
	activearmy.lastwpy = absy;
	RedrawWPs();
}
	
function ApplyWPGfx (relx,rely,inner) {
	var i;
	var layers = new Array();
	
	// wps
	if (gActiveArmyID) {
		var army = GetActiveArmy();
		if (army) {
			var wp = SearchPos(gWPs,relx,rely);
			if (wp) {
				var movablemask = GetUnitsMovableMask(gArmies[gActiveArmyID].units);
				var blocked = (GetPosSpeed(relx,rely,movablemask,gActiveArmyID) == 0) ? "b" : ""; // appended to nwse
				layers[layers.length] = g("mapwp/dot"+blocked+".gif");
				if (army && army.user > 0 && gUsers[army.user]) backgroundcolor = gUsers[army.user].color;
			}
		}
		
		if (gWPMap) {
			var mywps = gWPMap[relx][rely];
			for (i in mywps) layers[layers.length] = mywps[i];
		}
	}
	
	var res = "";
	for (i=0;i<layers.length;++i) res += "<div style=\"background-image:url("+layers[i]+");\">";
	res += inner;
	for (i=0;i<layers.length;++i) res += "</div>";
	gWPAffectedCells[rely][relx] = layers.length > 0;
	return res;
}
	

function JSUpdateNaviPos () {
	if (gBig) return;
	var naviframe = GetNaviFrame();
	if (!naviframe) return;
	naviframe.updatepos(gLeft+gXMid,gTop+gYMid);
}

// speedup
function jsParseBuildings () {
	var i,res,arr;
	gBuildings = gBuildings.split(";");	
	gBuildings.length=gBuildings.length-1;
	for (i in gBuildings) {
		arr = gBuildings[i].split(",");
		res = new Object();
		res.x = arr[0];
		res.y = arr[1];
		res.type = arr[2];
		res.user = arr[3];
		res.level = arr[4];
		res.hp = arr[5];
		res.construction = arr[6];
		res.jsflags = arr[7];	
		res.units = ParseTypeAmountList(arr[8]);		
		res.id = arr[9];		
		res.burning_since = arr[10];		
		gBuildingsCache[res.y-gTop+1][res.x-gLeft+1] = res;
	}
}

function ParseArmyData () {
	// parse and summarize units in armies (summoned units are added to normal units)
	var i;
	for (i in gArmies) if (gArmies[i]) {
		gArmies[i].units = ParseTypeAmountList(gArmies[i].unitstxt);
		gArmies[i].items = ParseTypeAmountList(gArmies[i].itemstxt);
		gArmies[i].wps = ParseWPs(gArmies[i].wpstxt);
	}
}

function DirToNWSE1 (dx,dy) {
	if (dy < 0 && dx == 0) return "n";
	if (dx < 0 && dy == 0) return "w";
	if (dx > 0 && dy == 0) return "e";
	if (dy > 0 && dx == 0) return "s";
	return false;
}

function CompileWPs () {
	gWPMap = false;
	if (gActiveArmyID == 0) return;
	if (!gArmies[gActiveArmyID]) return;
	gWPs = gArmies[gActiveArmyID].wps;
	var movablemask = GetUnitsMovableMask(gArmies[gActiveArmyID].units);
	var i,x,y,dx1,dy1,dx2,dy2,relx,rely;
	var cur,last,step,foot,head,blocked;
	var wpmapcell;
	gWPMap = new Array(gCY); // generated from gWPs
	for (y=0;y<gCY;++y) {
		gWPMap[y] = new Array(gCX);
		for (x=0;x<gCX;++x) gWPMap[y][x] = new Array();
	}
	last = false;
	dx1 = 0; // last offset
	dy1 = 0;
	for (i in gWPs) {
		cur = gWPs[i];
		cur.x = parseInt(cur.x);
		cur.y = parseInt(cur.y);
		// wp-source : "1,1;200,1;;1,200;1,1;"
		// ;; means invisible path-parts have been cut out, results in one wp being not an object, but false (see parser)
		// here we process path-parts, (connections) consisting of two waypoints
		if (!last || !cur) { last = cur; continue; }
		for (x=last.x,y=last.y;x!=cur.x||y!=cur.y;) {
			GetNextStep(x,y,last.x,last.y,cur.x,cur.y);
			dx2 = gGetNextStepX - x; 
			dy2 = gGetNextStepY - y;
			// step[0,1] is the next pos,  x,y  is the current pos, write arrow for current pos (here)
			relx = x - gLeft;
			rely = y - gTop;
			//alert("last:"+last.x+","+last.y+"\ncur:"+cur.x+","+cur.y+"\nrel:"+relx+","+rely+"\nd:"+dx2+","+dy2);
			if (dx1 != 0 || dy1 != 0) if (relx >= 0 && rely >= 0 && relx < gCX && rely < gCY) {
				foot = DirToNWSE1(-dx1,-dy1); // direction coming here
				head = DirToNWSE1(dx2,dy2); // direction going from here to next
				// TODO : check if blocked -> red or green
				blocked = (GetPosSpeed(relx,rely,movablemask,gActiveArmyID) == 0) ? "b" : ""; // appended to nwse
				wpmapcell = gWPMap[relx][rely];
				if (foot) wpmapcell[wpmapcell.length] = (g("mapwp/foot_"+foot+blocked+".gif"));
				if (head) wpmapcell[wpmapcell.length] = (g("mapwp/head_"+head+blocked+".gif"));
			}
			if (x == gGetNextStepX && y == gGetNextStepY) { alert("endless!"); return; }
			x = gGetNextStepX;
			y = gGetNextStepY;
			dx1 = dx2;
			dy1 = dy2;
		}
		last = cur;
	}
	gWPMapDirty = false;
}


function AbsolutePathCheck () {
	// to detect path mismatch early
	// a mismatching absolute path can cause permission violations on some browser versions
	var base1 = kBaseUrl+"/"+kMapScript;
	var base2 = kBaseUrl+kMapScript;
	var expected1 = location.protocol+"//"+location.host+location.pathname;
	var expected2 = location.protocol+"//"+location.hostname+location.pathname;
	gPathDetected = 0;
	if (base1 == expected1) gPathDetected += 1;
	if (base2 == expected1) gPathDetected += 2;
	if (base1 == expected2) gPathDetected += 4;
	if (base2 == expected2) gPathDetected += 8;
	if (gPathDetected > 0) return true; // valid path
	var mytext = "";
	mytext += "Fehler in der ZW-Config :\n";
	mytext += "der Absolute Pfad stimmt nicht\n";
	mytext += "Bitte folgende Werte den Admins melden :\n";
	mytext += "absolutepath="+base1+"\n";
	mytext += "protocol.host.pathname="+expected1+"\n";
	mytext += "hostname="+location.hostname+"\n";
	mytext += "siehe defines.mysql.php : BASEURL\n";
	MapReport(mytext.split("\n").join("<br>"));
	//alert(mytext);
	return false;
}

function VersionCheckBase () {
	if (!AbsolutePathCheck()) return false;
	//AbsolutePathCheck();
	if (kCoreJSMapVersion == kBaseJSMapVersion) return true;
	alert("Update der JavaScriptKarte (Core von v"+kCoreJSMapVersion+" auf v"+kBaseJSMapVersion+"), bitte neu einloggen...");
	var naviframe = GetNaviFrame();
	if (naviframe) naviframe.location.reload();
	location.reload();
	return false;
}

function VersionCheckNavi () {
	var naviframe = GetNaviFrame();
	if (!naviframe) return true;
	if (!naviframe.GetkNaviJSMapVersion) return true;
	var naviversion = naviframe.GetkNaviJSMapVersion();
	if (kCoreJSMapVersion == naviversion) return true;
	alert("Update der JavaScriptKarte (Navi von v"+naviversion+" auf v"+kCoreJSMapVersion+"), bitte neu einloggen...");
	naviframe.location.reload();
	location.reload();
	return false;
}

// executed onLoad, parse data, CreateMap() when finished
function MapInit() {
	if (!gBig) if (!VersionCheckBase()) return;

	gStaticCellInner = "<img src=\""+g("1px.gif")+"\" width="+kJSForceIESpaceCX+" height="+kJSForceIESpaceCY+">";
	//gActiveArmyMarkerGfx = g("minimap/minimapcross.gif");
	gActiveArmyMarkerGfx = g("crosshair.png");
	profiling("starting init");
	
	var i,j,x,y;
	gXMid=Math.floor(gCX/2);
	gYMid=Math.floor(gCY/2);
	for (y=0;y<gCY;++y) {
		gWPAffectedCells[y] = new Array(gCX);
		for (x=0;x<gCX;++x) gWPAffectedCells[y][x] = false;
	}
	
	profiling("parse terrain");
	// parse data
	gTerrain = gTerrain.split(";");							
	for (i in gTerrain) gTerrain[i] = gTerrain[i].split(",");
	
	profiling("parse buildings");
	// special for speed
	gBuildingsCache = new Array(gCY+2);
	for (y=0;y<gCY+2;++y) {
		gBuildingsCache[y] = new Array(gCX+2);
		for (x=0;x<gCX+2;++x) gBuildingsCache[y][x] = false;
	}
	jsParseBuildings();
	
	
	profiling("parse items");
	jsParseItems();
	profiling("parse plans");
	jsParsePlans();
	profiling("parse BuildSources");
	jsParseBuildSources();
	
	profiling("parse ParseArmyData");
	ParseArmyData();
	profiling("compile Waypoints");
	CompileWPs();
	
	profiling("parse gTerrainType");
	for (i in gTerrainType) {
		gTerrainType[i].connectto_terrain = gTerrainType[i].connectto_terrain.split(",");
		gTerrainType[i].connectto_building = gTerrainType[i].connectto_building.split(",");
	}
	
	profiling("parse gBuildingType");
	for (i in gBuildingType) {
		gBuildingType[i].connectto_terrain = gBuildingType[i].connectto_terrain.split(",");
		gBuildingType[i].connectto_building = gBuildingType[i].connectto_building.split(",");
	}
	HackCon(); // HACK : hardcoded connections, see mapjs7_globals.js.php
	
	profiling("parse gTerrainPatchType");
	for (i in gTerrainPatchType) {
		var x = gTerrainPatchType[i];
		if (!gTerrainPatchTypeMap[x.here]) // EX UNDEFINED
			gTerrainPatchTypeMap[x.here] = new Array();
		gTerrainPatchTypeMap[x.here][gTerrainPatchTypeMap[x.here].length] = x;	
	}
	
	gTerrainMap = new Array(gCY+2);
	gTerrainMap_raw = new Array(gCY+2);
	for (y=-1;y<gCY+1;++y) {
		gTerrainMap[y+1] = new Array(gCX+2);
		gTerrainMap_raw[y+1] = new Array(gCX+2);
	}
	CompileTerrain();
	
	profiling("constructing map");
	CreateMap();
	profiling("done");profiling(""); // double call to finish output
	
	JSUpdateNaviPos();
	
	var naviframe = GetNaviFrame();
	if (naviframe) gBigMapWindow = naviframe.GetBigMap();
}

var gRandSeed =  555.0; // zahl zwischen 500 und 50 000 (nicht zwingend Ganzzahl)
function PosRandPath (path,x,y,randmax) {
	if (randmax <= 1) return path.split("%RND%").join("0");
	x += gLeft;
	y += gTop;
	if (x < 0) x = 444-x;
	if (y < 0) y = 333-y;
	// positional pseudo random number generator by ishka
	// liefert fuer gleiche x,y coordinaten immer denselben wert (also kein richtiger zufall)
	// koennte man auch als Gfx-Variator bezeichen ;)
	var t = Math.sqrt(Math.sqrt(x+0.2)+Math.sqrt(y+0.3))*gRandSeed;
	t = t - Math.floor(t); // (also der Nachkommateil)
	return path.split("%RND%").join(Math.floor(t*16777216) % randmax);
}

function CompileTerrain () {
	// construct terrain map
	
	profiling("construct terrain, pass1");
	// first pass, simple nwse (with connect to building/terrain...)
	var randnum;
	var tile;
	var nwse_allset = kNWSE_N+kNWSE_W+kNWSE_S+kNWSE_E;
	var gCXPlusOne = gCX+1;
	var gCYPlusOne = gCY+1;
	var cb,ct;
	for (y=-1;y<gCYPlusOne;++y) for (x=-1;x<gCXPlusOne;++x) {
		tile = new Object();
		// tile.terraintype = GetTerrainType(x,y);
		// high-performance zone, function call hardcoded
			tile.terraintype = gTerrain[y+1][x+1];
			if (tile.terraintype == 0) tile.terraintype = kDefaultTerrainID;
		
		tile.terraintypeobj = gTerrainType[tile.terraintype];
		tile.gfx = tile.terraintypeobj.gfx;
		tile.nwse = 0;
		
		gTerrainMap_raw[y+1][x+1] = tile;
	}
			
		
	// second pass : nwse and terrain patches
	profiling("construct terrain, pass2");
	var patches;
	var o;	
	var tl,tr,tu,td;	
	for (x=-1;x<gCXPlusOne;++x) for (y=-1;y<gCYPlusOne;++y) {
		tile = gTerrainMap_raw[y+1][x+1];
		
		// reuse for performance
		tl = GetTerrainType(x-1,y);
		tr = GetTerrainType(x+1,y);
		tu = GetTerrainType(x,y-1);
		td = GetTerrainType(x,y+1);
		
		// calc nwse
		// tile.nwse = GetNWSE(tile.terraintypeobj,x,y);
		// high-performance zone, function call hardcoded
			//tile.nwse = 0;
			if (x > -1 && y > -1 && x < gCX && y < gCY) {
				ct = tile.terraintypeobj.connectto_terrain;
				cb = tile.terraintypeobj.connectto_building;
				if (InArray(tu,ct) || InArray(GetBuildingTypeFast(x,y-1),cb)) tile.nwse |= kNWSE_N;
				if (InArray(tl,ct) || InArray(GetBuildingTypeFast(x-1,y),cb)) tile.nwse |= kNWSE_W;
				if (InArray(td,ct) || InArray(GetBuildingTypeFast(x,y+1),cb)) tile.nwse |= kNWSE_S;
				if (InArray(tr,ct) || InArray(GetBuildingTypeFast(x+1,y),cb)) tile.nwse |= kNWSE_E;
			}
			
		// calc randmax
		if (tile.nwse == nwse_allset)
				tile.randmax = tile.terraintypeobj.maxrandcenter;
		else	tile.randmax = tile.terraintypeobj.maxrandborder;
		
		// apply patches
		if (KeyInArray(tile.terraintype,gTerrainPatchTypeMap)) {
			patches = gTerrainPatchTypeMap[tile.terraintype];
			for (i in patches) {
				o = patches[i];
				if(	(o.left	==0 || (o.left	>0 && tl == o.left)) &&
					(o.right==0 || (o.right	>0 && tr == o.right)) &&
					(o.up	==0 || (o.up	>0 && tu == o.up)) &&
					(o.down	==0 || (o.down	>0 && td == o.down)) ) {
					tile.randmax = 0;
					tile.gfx = o.gfx;
					if (o.left	>0 && x >= 0)	gTerrainMap_raw[y+1][x+1-1].nwse |= kNWSE_E;
					if (o.right	>0 && x < gCX)	gTerrainMap_raw[y+1][x+1+1].nwse |= kNWSE_W;
					if (o.up	>0 && y >= 0)	gTerrainMap_raw[y+1-1][x+1].nwse |= kNWSE_S;
					if (o.down	>0 && y < gCY)	gTerrainMap_raw[y+1+1][x+1].nwse |= kNWSE_N;
				}
			}
		}
		
		gTerrainMap_raw[y+1][x+1] = tile;
	}
	
	profiling("construct terrain, compile");
	// compile to terrainmap
	for (y=-1;y<gCYPlusOne;++y)  for (x=-1;x<gCXPlusOne;++x) {
		tile = gTerrainMap_raw[y+1][x+1];
		//HACK: a small cornfield cropcricle hack (terraintype id = 8)
		if(tile.terraintype == 8 && (gLeft+x)%11==0 && (gTop+y)%11==0)
			gTerrainMap[y+1][x+1] = g_nwse(PosRandPath("landschaft/cornfield-circle.png",x,y,tile.randmax),tile.nwse);
		else 
			gTerrainMap[y+1][x+1] = g_nwse(PosRandPath(tile.gfx,x,y,tile.randmax),tile.nwse);
	}
}

function ParseTypeAmountList (list) {
	var arr = list.split("|");
	arr.length=arr.length-1;
	var i,type_amount_pair;
	var res = new Array();
	for (i in arr) {
		type_amount_pair = arr[i].split(":");
		if (!res[type_amount_pair[0]]) // EX UNDEFINED
			res[type_amount_pair[0]] = 0;
		res[type_amount_pair[0]] += parseInt(type_amount_pair[1]);
	}
	return res;
}

function RefreshCell (relx,rely) {
	if (relx < 0 || rely < 0 || relx >= gCX || rely >= gCY) return;
	document.getElementById("cell_"+relx+"_"+rely).innerHTML = GetCellHTML(relx,rely);
}

function CreateMapLoopPart () {
	while (!CreateMapStep()) ; // one step triggers the next
}

gMapWarnTipp = ". Falls beim Laden der Karte Warnungen kommen, ob man das Script abbrechen will, kann man unter Einstellungen 'Karte langsam aufbauen' aktivieren.";

// construction process divided into steps to improve browser responsibility and prevent script hang check
function CreateMapStep () {
	var row,x,y,i,j,myclass,step;
	var smallstep = Math.floor(gXMid/2),bigstep = gCX-1,normalstep = gXMid;
	var navx,navy,text;
	var rowhtml;
	y = gMapConstructionCurY;
	
	// maptiles
	if (y<gCY+1) {
		gMapHTML += '<tr>';
		for (x=-1;x<gCX+1;++x) {
			if (x >= 0 && x < gCX && y >= 0 && y < gCY) {
				gMapHTML += "<td nowrap class=\"mapcell\" id=\"cell_"+x+"_"+y+"\">"+GetCellHTML(x,y)+"</td>\n";
			} else {
				// border
				myclass = "mapborder_" + (y<gYMid ? "n" : (y==gYMid?"":"s")) + (x<gXMid ? "w" : (x==gXMid?"":"e"));
				if ((x < 0 || x >= gCX) && (y < 0 || y >= gCY)) myclass += "_edge";
				
				// arrows for the big and small steps
				step = normalstep;
				
					 if(x+1 == gXMid && y<gYMid){step = smallstep;	myclass = "mapborder_n_small";}
				else if(x-1 == gXMid && y<gYMid){step = bigstep;	myclass = "mapborder_n_big";}
				else if(y+1 == gYMid && x<gXMid){step = smallstep;	myclass = "mapborder_w_small";}
				else if(y-1 == gYMid && x<gXMid){step = bigstep;	myclass = "mapborder_w_big";}
				else if(x+1 == gXMid && y>gYMid){step = smallstep;	myclass = "mapborder_s_small";}
				else if(x-1 == gXMid && y>gYMid){step = bigstep;	myclass = "mapborder_s_big";}
				else if(y+1 == gYMid && x>gXMid){step = smallstep;	myclass = "mapborder_e_small";}
				else if(y-1 == gYMid && x>gXMid){step = bigstep;	myclass = "mapborder_e_big";}
				
				navx = x<0?-1:(x>=gCX?1:0);
				navy = y<0?-1:(y>=gCY?1:0);
				text = (x < 0 || x >= gCX) ? (y+gTop) : (x+gLeft);
				//var blindcx = (y<0)?kJSForceIESpace:1;
				//var blindcy = (x<0)?kJSForceIESpace:1;
				//var blindgif = (x<0&&y<0)?"":("<img src=\""+g("edit.png")+"\" width="+blindcx+" height="+blindcy+">");
				gMapHTML += "<th nowrap class=\"mapborder\"><div class=\""+myclass+"\" onClick=\"navrel("+navx+","+navy+","+step+")\"><span>"+text+"</span></div></th>\n";
			}
		}
		gMapHTML += '</tr>';
		
		//if(y < 1)alert(y+": "+rowhtml);
		//document.getElementById("row"+(y+1)).innerHTML = rowhtml;
		//if(y < 1)alert(y+": "+document.getElementById("row"+(y+1)).innerHTML);
		
		++gMapConstructionCurY;
		if (gBig) {
			if (y > 0 && ((y % 3) == 0 || gCX > 70)) {
				MapReport("Erzeuge Kartenzeile "+y+"/"+(gCY)+gMapWarnTipp);
				window.setTimeout("CreateMapLoopPart()",gSlowMap?800:200);
				return true;
			}
			return false;
		}
		return false; // loop in createmap, false -> not done
	} else {
		//profiling("sending html to browser");
		MapReport("");
		
		gMapHTML += '</table>';
		document.getElementById("mapzone").innerHTML = gMapHTML;
		
		VersionCheckNavi();
	
		// done constructing map
		return true;
	}
}

// createmap is called as soon as loading is complete
function CreateMap() {
	gMapConstructionCurY = -1;
	gMapHTML = "";
	
	gMapHTML += "<div class=\"tabcorner\">";
	gMapHTML += 	"<span><img src=\""+gWeatherGfx+"\" name=\"Wetter\" title=\"Wetter\" border=0 width=17 height=17></span>";
	gMapHTML += 	"<span>"+gMapModiHelp+"</span>";
	//gMapHTML += "<a href=\"javascript:void(alert('Map-Version="+kCoreJSMapVersion+",PathCode="+gPathDetected+"'))\">v</a>";
	gMapHTML += "<a href=\"javascript:navrel(0,0,1)\"><img alt=\"reload\" title=\"reload\" border=0 src=\""+g("icon/reload.png")+"\"></a>";
	if (!gBig)	gMapHTML += "<a href=\"javascript:OpenMap(1)\"><img alt=\"bigmap\" title=\"bigmap\" border=0 src=\""+g("icon/bigmap.png")+"\"></a>";
	if (!gBig)	gMapHTML += "<a href=\"javascript:OpenMap(2)\"><img alt=\"minimap2\" title=\"minimap2\" border=0 src=\""+g("icon/minimap2.png")+"\"></a>";
	if (!gBig)	gMapHTML += "<a href=\"javascript:OpenMap(3)\"><img alt=\"minimap\" title=\"minimap\" border=0 src=\""+g("icon/minimap.png")+"\"></a>";
	if (!gBig)	gMapHTML += "<a href=\"javascript:OpenMap(4)\"><img alt=\"creepmap\" title=\"creepmap\" border=0 src=\""+g("icon/creepmap.png")+"\"></a>";
	if (!gBig)	gMapHTML += "<a href=\"javascript:OpenMap(5)\"><img alt=\"diplomap\" title=\"diplomap\" border=0 src=\""+g("icon/diplomap.png")+"\"></a>";
	gMapHTML += "</div>";
	gMapHTML += "<ul>";
	gMapHTML += 	"<li class=\""+(gMapMode==kJSMapMode_Normal?	"activetab":"inactivetab")+"\"><span class=\"tabhead\"><img border=0 src=\"gfx/1px.gif\" width=1 height=18><a href=\"javascript:SetMapMode(kJSMapMode_Normal)\">Normal</a></span></li>";
	gMapHTML += 	"<li class=\""+(gMapMode==kJSMapMode_Plan?	"activetab":"inactivetab")+"\"><span class=\"tabhead\"><img border=0 src=\"gfx/1px.gif\" width=1 height=18><a href=\"javascript:SetMapMode(kJSMapMode_Plan)\">Pl&auml;ne</a></span></li>";
	gMapHTML += 	"<li class=\""+(gMapMode==kJSMapMode_Bauzeit?"activetab":"inactivetab")+"\"><span class=\"tabhead\"><img border=0 src=\"gfx/1px.gif\" width=1 height=18><a href=\"javascript:SetMapMode(kJSMapMode_Bauzeit)\">Bauzeit</a></span></li>";
	gMapHTML +=		"<li class=\""+(gMapMode==kJSMapMode_HP?		"activetab":"inactivetab")+"\"><span class=\"tabhead\"><img border=0 src=\"gfx/1px.gif\" width=1 height=18><a href=\"javascript:SetMapMode(kJSMapMode_HP)\">HP</a></span></li>";
	gMapHTML += "</ul>";
	
	document.getElementById("mapheaderzone").innerHTML = gMapHTML;
	
	
	var x,y,i,j,myclass,step;
	var smallstep = Math.floor(gXMid/2),bigstep = gCX-1,normalstep = gXMid;
	var navx,navy,text;
	
	gMapHTML = '<table class="map" onMouseout="AbortTip()" border=0 cellpadding=0 cellspacing=0>';
	
	
	document.getElementById("maptipzone").innerHTML = "<span class=\"maptip\" onClick=\"KillTip()\" id=\""+kMapTipName+"\" style=\"position:absolute;top:0px;left:0px; visibility:hidden;\">&nbsp;</span>";
	if (gBig && gSlowMap)
			window.setTimeout("CreateMapLoopPart()",800);
	else	CreateMapLoopPart();
}

function OpenMap (type) {
	var x = gLeft+gXMid;
	var y = gTop+gYMid;
	// TODO ??? : kBASEURL+"/"+kMapScript+"?sid=
	if (type == 1) { // bigmap
		var naviframe = GetNaviFrame();
		if (!naviframe) return;
		//gBigMapWindow = window.open("mapjs7.php?sid="+gSID+"&cx=50&cy=50&big=1&army="+gActiveArmyID+"&mode="+gMapMode+"&x="+x+"&y="+y,"BigMap");
		naviframe.OpenBigMap("mapjs7.php?sid="+gSID+"&cx=50&cy=50&big=1&army="+gActiveArmyID+"&mode="+gMapMode+"&x="+x+"&y="+y,"BigMap");
		gBigMapWindow = naviframe.GetBigMap();
	} else if (type == 2) { //minimap2
		window.open("minimap2.php?sid="+gSID+"&crossx="+x+"&crossy="+y,"MiniMap","location=no,menubar=no,toolbar=no,status=no,resizable=yes,scrollbars=yes");
	} else if (type == 3) { //minimap
		window.open("minimap.php?sid="+gSID+"&cx="+x+"&cy="+y,"MiniMap","location=no,menubar=no,toolbar=no,status=no,resizable=yes,scrollbars=yes");
	} else if (type == 4) { //creepmap
		window.open("minimap.php?mode=creep&sid="+gSID+"&cx="+x+"&cy="+y,"CreepMap","location=no,menubar=no,toolbar=no,status=no,resizable=yes,scrollbars=yes");
	} else if (type == 5) { //diplomap
		window.open("minimap.php?mode=guild&diplomap=1&sid="+gSID+"&cx="+x+"&cy="+y,"DiploMap","location=no,menubar=no,toolbar=no,status=no,resizable=yes,scrollbars=yes");
	} else if (type == 100) { //hugemap
		if (!confirm("Sicher ? Die HugeMap ist riesig und hat 200*200 felder, die BigMap 50*50...")) return;
		window.open("<?=kMapScript?>?sid="+gSID+"&cx=200&cy=200&big=1&x="+x+"&y="+y,"HugeMap");
	}
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
	layers[layers.length] = GetTerrainPic(relx,rely);
	var backgroundcolor = HackBackgroundColor(relx,rely);
	
	// building
	var building = GetBuilding(relx,rely);
	if (building) {
		if (building.construction > 0 || !gBuildingType[building.type]) {
			layers[layers.length] = g(kConstructionPic);
		} else {
			
			//handly %BUSY% replacement
			var busy,busy_rnd;
			if(building.user == kUserID){
				if(gBusy[building.type])busy = gBusy[building.type];
				else busy = 0;
				busy_rnd = Math.round(Math.random()*100);
				if(busy_rnd < busy)busy = 1;
				else busy = 0;
			} else busy = 0;
			
			layers[layers.length] = GetBuildingPic(building,relx,rely,busy);

			if (building.user > 0 && gUsers[building.user] && gBuildingType[building.type].border && gMapMode!=kJSMapMode_Plan && gMapMode!=kJSMapMode_Bauzeit) 
				backgroundcolor = gUsers[building.user].color;
			if (gBuildingType[building.type] && (gBuildingType[building.type].flags & kBuildingTypeFlag_DrawMaxTypeOnTop)) {
				// draw units on top (cannon tower)
				var unittype = GetMaxUnitType(building.units);
				if (gUnitType[unittype]) layers[layers.length] = g2(gUnitType[unittype].gfx,0);
			}
		}
		if (gMapMode==kJSMapMode_HP) {
			backgroundcolor = GradientRYG(GetFraction(building.hp,calcMaxBuildingHp(building.type,building.level)));
		}
	}
	
	// plan
	var plan = SearchPos(gPlans,relx,rely);
	if (plan) {
		if (gMapMode==kJSMapMode_Plan && gBuildingType[plan.type]) {
			backgroundcolor = "red";
			layers[layers.length] = g3(gBuildingType[plan.type].gfx,10,0); // nwse : we
		} else {
			layers[layers.length] = g(kTransCP);
		}
	}
	
	// bauzeit farbe + text
	if (gMapMode==kJSMapMode_Bauzeit && !building) {
		var builddistfactor = GetBuildDistFactor(GetBuildDist(relx,rely));
		//celltext = builddistfactor.toPrecision(2);
		backgroundcolor = GradientRYG(1.0-GetFraction(builddistfactor-1.0,1.0));
	}
	
	// item
	var item = SearchPos(gItems,relx,rely);
	var items = SearchPosArr(gItems,relx,rely);
	for (i in items) if (gItemType[items[i].type])
		layers[layers.length] = g(gItemType[items[i].type].gfx);
	
	// army
	var army = SearchPos(gArmies,relx,rely);
	if (army) {
		nwsecode = 0;
		var unittype = GetMaxUnitType(army.units);
		if (UnitTypeHasNWSE(unittype)) { // hyperblob hack
			if (unittype == GetArmyUnitType(relx,rely-1)) nwsecode += kNWSE_N;
			if (unittype == GetArmyUnitType(relx-1,rely)) nwsecode += kNWSE_W;
			if (unittype == GetArmyUnitType(relx,rely+1)) nwsecode += kNWSE_S;
			if (unittype == GetArmyUnitType(relx+1,rely)) nwsecode += kNWSE_E;
		}
		if (gUnitType[unittype])
			layers[layers.length] = g2(gUnitType[unittype].gfx,nwsecode);
		if (army.user > 0 && gUsers[army.user]) backgroundcolor = gUsers[army.user].color;
	}
	
	//set overlays
	var k = ""+relx+"-"+rely;
	if(gOverlay[k])layers[layers.length] = g(gOverlayGfx[gOverlay[k]]);
	
	var i,bg,res = "";
	for (i in layers) {
		// if (i == 0) alert(backgroundcolor);
		bg = (i==0 && backgroundcolor && backgroundcolor != "false")?("background-color:"+backgroundcolor+";"):"";
		res += "<div style=\"background-image:url("+layers[i]+"); "+bg+"\">";
	}
	res += "<div id=\"mouselistener_"+rely+"_"+relx+"\" ><div onClick=\"mapclick("+relx+","+rely+")\" onMouseover=\"mapover("+relx+","+rely+")\">";
	//if (relx == gXMid && rely == gYMid) 
	//		res += "<img src='gfx/crosshair.png' onMouseover=\"mapover("+relx+","+rely+")\">"; 
	//else
	if (army) res += "<div id=\"armymarker_"+army.id+"\" "+((army.id==gActiveArmyID)?("style=\"background-image:url("+gActiveArmyMarkerGfx+");\""):"")+">";
	res += "<div id=\"wpzone_"+rely+"_"+relx+"\" >";
	res += ApplyWPGfx(relx,rely,gStaticCellInner);
	if (army) res += '</div>';
	res += '</div></div></div>';
	for (i in layers) res += '</div>';
	
	//if (relx == 4 && rely == 5) alert(g3(gBuildingType[buildingtype].gfx,nwsecode,level));
	// background-color:$b
	// onClick="navrel(-1,-1)"
	//res = "<div onClick='m("+relx+","+rely+")'>"+res+"</div>";
	//res = "<div onClick='m("+relx+","+rely+")'>"+res+"</div>";
	//if (relx == 0 && rely == 0) alert(res);
	return res;
}

function SetArmyMarker (armyid,path) { // gActiveArmyMarkerGfx or ''
	var markerdiv = document.getElementById("armymarker_"+armyid);
	if (markerdiv) markerdiv.style.backgroundImage = "url("+path+")";
}

function SetOverlayGraphic (relx,rely,path) {
	if (relx < 0 || rely < 0 || relx >= gCX || rely >= gCY) return;
	document.getElementById("mouselistener_"+rely+"_"+relx).style.backgroundImage = "url("+path+")";
}
function SetTempOverlayGraphic (relx,rely,path) {
	SetOverlayGraphic(relx,rely,path);
	window.setTimeout("SetOverlayGraphic("+relx+","+rely+",'')",1000);	
}

function mapclick (relx,rely) {
	if (!VersionCheckNavi()) return;
	var naviframe = GetNaviFrame();
	if (naviframe) {
		if (naviframe.mapclicktool_hasoverlay()) SetTempOverlayGraphic(relx,rely,g1("sanduhrklein.gif"));
		LocalMapTool(relx,rely,naviframe.GetCurTool());
		ExecuteTool(relx+gLeft,rely+gTop,-1);
	}
	KillTip();
}

function ExecuteTool	(absx,absy,tool) {
	var naviframe = GetNaviFrame();
	if (!naviframe) return;
	var activearmy = GetActiveArmy();
	var wpmaxprio = activearmy?gReportedWPMaxPrio:-1;
	// if (((tool==-1)?naviframe.GetCurTool():tool) == kMapNaviTool_WP) alert("Adding WP with prio "+wpmaxprio);
	naviframe.mapclicktool(absx,absy,gActiveArmyID,wpmaxprio,tool);
}

function GetToolActionInfo (relx,rely) {
	var naviframe = GetNaviFrame();
	if (!naviframe) return false;
	var curtool = naviframe.GetCurTool();
	if (curtool == kMapNaviTool_WP) {
		var activearmy = GetActiveArmy();
		if (activearmy) 
			return "WP für "+activearmy.name+" setzten";
	}
	if (curtool == kMapNaviTool_MultiTool) {
		// flostre multitool
		var army = SearchPos(gArmies,relx,rely);
		var building = GetBuilding(relx,rely);
		var wp = SearchPos(gWPs,relx,rely);
		if (army) {
			if (army.jsflags & kJSMapArmyFlag_Controllable)
					return ""+army.name+" auswählen";
			else	return "Info für "+army.name+" abrufen";
		} else if (building && GetArmyPosSpeed(relx,rely,gArmies[gActiveArmyID]) == 0) {
			return "Info für Gebäude abrufen";
		} else if (wp) {
			return "WP löschen";
		} else {
			var activearmy = GetActiveArmy();
			return "WP für "+activearmy.name+" setzten";
		}
	}
	return false;
}

function LocalMapTool (relx,rely,curtool) {
	// called from mapclick for tools that have local effects for the map
	if (curtool == kMapNaviTool_Look) {
		// activate army
		var army = SearchPos(gArmies,relx,rely);
		if (army) JSActivateArmy(army.id,true);
	}
	if (curtool == kMapNaviTool_WP) AddWP(relx+gLeft,rely+gTop);
	if (curtool == kMapNaviTool_MultiTool) {
		// flostre multitool
		var army = SearchPos(gArmies,relx,rely);
		var building = GetBuilding(relx,rely);
		var wp = SearchPos(gWPs,relx,rely);
		if (army) {
			if (army.jsflags & kJSMapArmyFlag_Controllable)
					JSActivateArmy(army.id,true);
			else	ExecuteTool(relx+gLeft,rely+gTop,kMapNaviTool_Look);
		} else if (building && GetArmyPosSpeed(relx,rely,gArmies[gActiveArmyID]) == 0) {
			ExecuteTool(relx+gLeft,rely+gTop,kMapNaviTool_Look);
		} else if (wp) {
			ExecuteTool(relx+gLeft,rely+gTop,kMapNaviTool_Cancel);
		} else {
			LocalMapTool(relx,rely,kMapNaviTool_WP);
			ExecuteTool(relx+gLeft,rely+gTop,kMapNaviTool_WP);
		}
	}
}

var gLastTipX = -1;
var gLastTipY = -1;
var gMapTipCountDown = false;
function KillTip () {
	var maptipnode = document.getElementById(kMapTipName);
	maptipnode.style.visibility = "hidden";
	AbortTip();
	//alert();
	//document.getElementsByName(kMapTipName)[0].style.visibility = "hidden";
}

// stops the countdown for popping up
function AbortTip () {
	if (gMapTipCountDown) {
		// clear interval
		window.clearTimeout(gMapTipCountDown);
		gMapTipCountDown = false;
	}
}

function mapover (relx,rely) {
	if (relx == gLastTipX && rely == gLastTipY) return;
	gLastTipX = relx;
	gLastTipY = rely;
	KillTip();
	// set interval
	gMapTipCountDown = window.setTimeout("ShowMapTip("+relx+","+rely+")",300);
}

function ShowMapTip(relx,rely) {
	gMapTipCountDown = false; // count has finished, don't try to kill it now..
	// todo : if (GetTool() != lupe) { KillTip(); return; }

	// generate tip text
	var i;
	var tiptext = "<table border=0 bgcolor=\"white\" cellpadding=0 cellspacing=0>";


	// terrain
	var terraintype = GetTerrainType(relx,rely);
	//tiptext += "<tr><td nowrap align=\"left\"><img src=\""+GetTerrainPic(relx,rely)+"\"></td><td nowrap colspan=2>";
	tiptext += "<tr><td align=\"left\" colspan=2>";
	tiptext += "<span>"+((relx+gLeft)+","+(rely+gTop))+" : "+gTerrainType[terraintype].name+"</span>";
	var builddistfactor = GetBuildDistFactor(GetBuildDist(relx,rely));
	//tiptext += "<span>BuildDist "+GetBuildDist(relx,rely).toPrecision(2)+"</span>";
	if (builddistfactor < 4.0) tiptext += "<br><span>Bauzeit * "+builddistfactor.toPrecision(3)+"</span>";
	if (gNWSEDebug) tiptext += "<br><span>type="+terraintype+"</span>";
	if (gNWSEDebug) tiptext += "<br><span>tc="+gTerrainType[terraintype].connectto_terrain.join(",")+"</span>";
	if (gNWSEDebug) tiptext += "<br><span>bc="+gTerrainType[terraintype].connectto_building.join(",")+"</span>";
	tiptext += "</td></tr>";
	
	// building
	var building = GetBuilding(relx,rely);
	if (building) {
		if (gBuildingType[building.type]) {
			tiptext += "<tr><td nowrap><img src=\""+GetBuildingPic(building,relx,rely,0)+"\"></td><td nowrap colspan=2 align=\"left\">";
			tiptext += "<span>"+gBuildingType[building.type].name + " Stufe "+building.level + "</span><br>";
			tiptext += "<span>"+"HP : "+building.hp+"/"+calcMaxBuildingHp(building.type,building.level) + "</span><br>";
			if (building.user > 0 && gUsers[building.user]) tiptext += "<span>"+gUsers[building.user].name + "</span><br>";
			
			if (building.jsflags & kJSMapBuildingFlag_BeingSieged) 
				tiptext += "<span><b><font color=red>wird belagert</font></b></span><br>";
			if (building.jsflags & kJSMapBuildingFlag_BeingPillaged) 
				tiptext += "<span><b><font color=red>wird geplündert</font></b></span><br>";
			if (building.jsflags & kJSMapBuildingFlag_Shooting) 
				tiptext += "<span><b><font color=red>schiesst gerade</font></b></span><br>";
			if (building.jsflags & kJSMapBuildingFlag_BeingShot) 
				tiptext += "<span><b><font color=red>wird beschossen</font></b></span><br>";
				
			if (gNWSEDebug) tiptext += "<br><span>type="+building.type+"flags="+building.jsflags+"</span>";
			if (gNWSEDebug) tiptext += "<br><span>tc="+gBuildingType[building.type].connectto_terrain.join(",")+"</span>";
			if (gNWSEDebug) tiptext += "<br><span>bc="+gBuildingType[building.type].connectto_building.join(",")+"</span>";
			// backgroundcolor = GradientRYG(GetFraction(hp,calcMaxBuildingHp(type,level)))
			if (building.units.length > 0) {
				tiptext += "<span>"; 
				for (i in building.units) if (gUnitType[i])
					tiptext += "<img src=\""+g(gUnitType[i].gfx)+"\">"+TausenderTrenner(building.units[i]);
				tiptext += "</span><br>";
			}
			if (gBuildingData[building.id]) {
				tiptext += "<span>";
				tiptext += gBuildingData[building.id];
				tiptext += "</span><br>";
			}
			tiptext += "</td></tr>";
		} else {
			tiptext += "<tr><td nowrap colspan=3 align=\"left\">";
			tiptext += "<span> Unbekannter GebäudeTyp "+ building.type + "</span><br>";
			tiptext += "</td></tr>";
		}
	}
	
	// plan
	var plan = SearchPos(gPlans,relx,rely);
	if (plan && gBuildingType[plan.type]) {
		tiptext += "<tr><td nowrap>";
		tiptext += "<img src=\""+g(kTransCP)+"\"></td><td nowrap>";
		tiptext += "<img src=\""+g3(gBuildingType[plan.type].gfx,HackNWSE(plan.type,10,relx,rely),0)+"\"></td><td nowrap>";
		tiptext += "<span>Bauplan</span><br>";
		tiptext += "<span>"+gBuildingType[plan.type].name+"</span>";
		tiptext += "</td></tr>";
	}
	
	// item
	var items = SearchPosArr(gItems,relx,rely);
	for (i in items) if (gItemType[items[i].type]) {
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
		tiptext += "<span>"+army.name+"("+army.fill_limit+"%,Last:"+army.fill_last+"%)</span><br>";
		if (army.user > 0 && gUsers[army.user]) tiptext += "<span>"+gUsers[army.user].name+"</span><br>";
		
		//tiptext += "<span>Flags:"+army.jsflags+"</span><br>";
		if (army.jsflags & kJSMapArmyFlag_Controllable) 
			tiptext += "<span><b><font color=green>untersteht eurem Befehl</font></b></span><br>";
		if (army.jsflags & kJSMapArmyFlag_GC) 
			tiptext += "<span><b><font color=orange>steht unter GC</font></b></span><br>";
		if (army.jsflags & kJSMapArmyFlag_Fighting) 
			tiptext += "<span><b><font color=red>kämpft gerade</font></b></span><br>";
		if (army.jsflags & kJSMapArmyFlag_Sieging) 
			tiptext += "<span><b><font color=red>belagert gerade</font></b></span><br>";
		if (army.jsflags & kJSMapArmyFlag_Pillaging) 
			tiptext += "<span><b><font color=red>plündert gerade</font></b></span><br>";
		if (army.jsflags & kJSMapArmyFlag_Shooting) 
			tiptext += "<span><b><font color=red>schiesst gerade</font></b></span><br>";
		if (army.jsflags & kJSMapArmyFlag_BeingShot) 
			tiptext += "<span><b><font color=red>wird beschossen</font></b></span><br>";
				
		tiptext += "<span>";
		if (army.units.length > 0) for (i in army.units) if (gUnitType[i])
			tiptext += "<img src=\""+g(gUnitType[i].gfx)+"\">"+TausenderTrenner(army.units[i]);
		tiptext += "</span><br>";
		tiptext += "<span>";
		if (army.items.length > 0) for (i in army.items) if (gItemType[i])
			tiptext += "<img src=\""+g(gItemType[i].gfx)+"\">"+TausenderTrenner(army.items[i]);
		tiptext += "</span>";
		
		tiptext += "</td></tr>";
	}
	
	// active army, wps
	if (gActiveArmyID) {
		var wp = SearchPos(gWPs,relx,rely);
		var army = GetActiveArmy();
		var user = (army && army.user > 0 && gUsers[army.user])?gUsers[army.user]:false;
		if (army) { 
			// distance to last wp
			tiptext += "<tr><td nowrap colspan=2>";
			tiptext += "<span>Entf.zum letzten WP:"+(army.lastwpx-relx-gLeft)+","+(army.lastwpy-rely-gTop)+":"+army.wpmaxprio+"</span><br>";
			tiptext += "</td></tr>";
		}
		if (wp || (gWPMap && gWPMap[relx][rely].length > 1)) {
			tiptext += "<tr><td nowrap>";
			if (!wp) {
				tiptext += "<div style=\"background-image:url("+gWPMap[relx][rely][gWPMap[relx][rely].length-2]+");\">";
				tiptext += "<img src=\""+gWPMap[relx][rely][gWPMap[relx][rely].length-1]+"\">";
				tiptext += "</div>";
			} else tiptext += "<img src=\""+g("mapwp/dot.gif")+"\">";
			tiptext += "</td><td nowrap>";
			if (wp) 
					tiptext += "<span>Wegpunkt</span><br>";
			else 	tiptext += "<span>geplanter weg</span><br>";
			if (army) tiptext += "<span>"+army.name+"</span><br>";
			if (user) tiptext += "<span>"+user.name+"</span><br>";
			tiptext += "</td></tr>";
		}
	}
	
	// ausgewaehltes tool anzeigen:
	var toolactioninfo = GetToolActionInfo(relx,rely);
	if (gBig) {
		var curtoolgfx = false;
		var naviframe = GetNaviFrame();
		if (naviframe) curtoolgfx = naviframe.GetLastToolGFX();
		if (curtoolgfx) {
			tiptext += "<tr><td nowrap colspan=2>";
			tiptext += "<span><img src=\""+curtoolgfx+"\">";
			if (toolactioninfo) tiptext += ":"+toolactioninfo;
			tiptext += "</span><br>";
			tiptext += "</td></tr>";
		}
	} else {
		if (toolactioninfo) {
			tiptext += "<tr><td nowrap colspan=2>";
			tiptext += "<span>"+toolactioninfo+"</span><br>";
			tiptext += "</td></tr>";
		}
	}
	
	tiptext += "</table>";
	
	// find a suitable position
	var x,y;
	x = kMapTip_xoff + kJSMapTileSize*relx;
	y = kMapTip_yoff + kJSMapTileSize*rely;
	// spawn tip
	var maptipnode = document.getElementById(kMapTipName);
	//alert("maptipnode"+maptipnode+","+kMapTipName+","+document.getElementsByName(kMapTipName));
	//alert("1"+maptipnode);
	//for (i in maptipnode) alert("2:"+i+"="+maptipnode[i]);
	maptipnode.innerHTML = tiptext;
	maptipnode.style.visibility = "visible";
	//alert("3"+maptipnode);
	maptipnode.style.position = "absolute";
	if (gBig) {
		var xdiff = (relx >= gCX-8)?-10:0;
		var ydiff = (rely >= gCY-8)?-10:0;
		maptipnode.style.left = (kMapTip_xoff + kJSMapTileSize*(relx+1+xdiff))+"px";
		maptipnode.style.top = (kMapTip_yoff + kJSMapTileSize*(rely+1+ydiff))+"px";
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
gOutPutOnce = false;

function GetFraction (cur,max) { return (cur <= 0.0 || max == 0)?0.0:((cur >= max)?1.0:(cur / max)); }
function GradientRYG (factor) { // red-yellow-green
	factor = Math.min(1.0,Math.max(0.0,factor));
	var dist = Math.abs(factor - 0.5)*2.0;
	factor = 0.5 + 0.5*((factor>0.5)?1.0:(-1.0))*dist*dist;
	var r = Math.round(255.0*Math.min(1.0,2.0-factor*2.0));
	var g = Math.round(255.0*Math.min(1.0,factor*2.0));
	// if (!gOutPutOnce) { gOutPutOnce = true; alert(factor+"\n"+r+"\n"+g); }
	r = r.toString(16); if (r.length == 0) r = "00"; if (r.length == 1) r = "0"+r; if (r.length > 2) r = "ff";
	g = g.toString(16); if (g.length == 0) g = "00"; if (g.length == 1) g = "0"+g; if (g.length > 2) g = "ff";
	return "#"+(""+r)+(""+g)+"00";
}
function calcMaxBuildingHp(type,level) {
	if (!gBuildingType[type]) return 0;
	var maxhp = gBuildingType[type].maxhp;
	return Math.ceil(maxhp + maxhp/100*1.5*level);
}
function GetArmyUnitType (relx,rely) {
	var army = SearchPos(gArmies,relx,rely);
	if (!army) return 0;
	return GetMaxUnitType(army.units);
}
function GetMaxUnitType (units) {
	if (!units) return 0;
	var i,maxtype=0,maxamount=0;
	for (i in units)
		if (maxamount < units[i]) {
			maxamount = units[i];
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
	//return nummertext+",last="+nummertext.substr(nummertext.length-1,1);
	var i,j,res = "";
	for (i=0;i<blocks;++i) {
		if (3*i+0 < nummertext.length) res = nummertext.substr(nummertext.length-1 - (3*i+0),1)+res;
		if (3*i+1 < nummertext.length) res = nummertext.substr(nummertext.length-1 - (3*i+1),1)+res;
		if (3*i+2 < nummertext.length) res = nummertext.substr(nummertext.length-1 - (3*i+2),1)+res;
		if (3*i+3 < nummertext.length) res = "."+res;
	}
	return res;
}

// type at position

function SearchPos (arr,relx,rely) {
	var i; for (i in arr) if (arr[i]) {
		if (arr[i].x == relx + gLeft && 
			arr[i].y == rely + gTop) return arr[i];
	}
	return false;
}
function SearchPosArr (arr,relx,rely) {
	var res = new Array();
	var i; for (i in arr) if (arr[i]) {
		if (arr[i].x == relx + gLeft && 
			arr[i].y == rely + gTop) res[res.length] = arr[i];
	}
	return res;
}
function GetBuildingType (relx,rely) {
	var building = GetBuilding(relx,rely);
	if (building == false) return 0;
	return building.type;
}

function GetBuildingTypeFast (relx,rely) { // no check
	var building = gBuildingsCache[rely+1][relx+1];
	if (building == false) return 0;
	return building.type;
}
function GetTerrainType (relx,rely) {
	if (relx < -1 || rely < -1 || relx >= gCX+1 || rely >= gCY+1) return kDefaultTerrainID;
	var terraintype = gTerrain[rely+1][relx+1];
	if (terraintype == 0) return kDefaultTerrainID;
	return terraintype;
}
function GetTerrainTypeFast (relx,rely) { // no bounds check
	var terraintype = gTerrain[rely+1][relx+1];
	if (terraintype == 0) return kDefaultTerrainID;
	return terraintype;
}
function GetBuilding (relx,rely) {
	if (relx < -1 || rely < -1 || relx >= gCX+1 || rely >= gCY+1) return false;
	return gBuildingsCache[rely+1][relx+1];
}
function GetBuildDist (relx,rely) {
	var mindist = -1;
	var curdist,dx,dy;
	for (i in gBuildSources) {
		dx = gBuildSources[i].x - (relx+gLeft);
		dy = gBuildSources[i].y - (rely+gTop);
		curdist = dx*dx + dy*dy;
		if (mindist == -1 || mindist > curdist) mindist = curdist;
	}
	if (mindist < 0) return 0;
	return Math.sqrt(mindist);
}
function GetActiveArmy () { 
	for (i in gArmies) 
		if (gArmies[i] && gArmies[i].id == gActiveArmyID) 
			return gArmies[i];
	return false;
}
function ArmyGetMovableMask (army) {
	return 0;
}


// terrain/building pic + nwse

function GetNWSE (typeobj,relx,rely) {
	var nwsecode = 0;
	if (typeobj) {
		var ct = typeobj.connectto_terrain;
		var cb = typeobj.connectto_building;
		if (InArray(GetTerrainType(relx,rely-1),ct) || InArray(GetBuildingType(relx,rely-1),cb)) nwsecode += kNWSE_N;
		if (InArray(GetTerrainType(relx-1,rely),ct) || InArray(GetBuildingType(relx-1,rely),cb)) nwsecode += kNWSE_W;
		if (InArray(GetTerrainType(relx,rely+1),ct) || InArray(GetBuildingType(relx,rely+1),cb)) nwsecode += kNWSE_S;
		if (InArray(GetTerrainType(relx+1,rely),ct) || InArray(GetBuildingType(relx+1,rely),cb)) nwsecode += kNWSE_E;
	}
	return nwsecode;
}
function GetTerrainPic (relx,rely) {
	if (gTerrainMap) return gTerrainMap[rely+1][relx+1];
	var terraintype = GetTerrainType(relx,rely);
	var nwsecode = GetNWSE(gTerrainType[terraintype],relx,rely);
	return g_nwse(gTerrainType[terraintype].gfx,nwsecode);
}

// similar to the php function GetBuildingPic in lib.main.php
function GetBuildingPic (building,relx,rely,busy) {
	var type = building.type;
	if (!gBuildingType[type]) return ""; // broken types
	var level = building.level;
	var gfxlevel = 0;
	var user = building.user;
	var race = (user > 0 && gUsers[user]) ? gUsers[user].race : 1;
	var moral = (user > 0 && gUsers[user]) ? gUsers[user].moral : 100;
	if (level < 10) gfxlevel = 0; 
	else if (level < 50) gfxlevel = 1;
	else if (level < 100) gfxlevel = 2;
	else if (level < 200) gfxlevel = 3;
	else gfxlevel = 4; // pic level
	
	var maxgfxlevel = gBuildingType[type].maxgfxlevel;
	if (gfxlevel > maxgfxlevel) gfxlevel = maxgfxlevel;
	
	var nwsecode = GetNWSE(gBuildingType[type],relx,rely);
	var gfx = gBuildingType[type].gfx;
	
	// TODO: FIXME: HACK: (gates&portal)  also in mapstyle_buildings.php and GetBuildingCSS()
	if (building.jsflags & kJSMapBuildingFlag_Open) 
		gfx = gfx.split("-zu-").join("-offen-"); 
	
	//replace %BUSY%
	gfx = gfx.split("%BUSY%").join(busy); 
		
	// HACK: special nwse for path,gates,bridge...  also in UpdateBuildingNWSE()
	nwsecode = HackNWSE(type,nwsecode,relx,rely); // see mapjs7_globals.js.php
	
	return g5(gfx,nwsecode,gfxlevel,race,moral);
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

function navrel(x,y,scroll) {
	if (x < 0) x = -1; else if (x > 0) x = 1;
	if (y < 0) y = -1; else if (y > 0) y = 1;
	x = gLeft + gXMid + x * scroll;
	y = gTop + gYMid + y * scroll;
	navabs(x,y,0);
}

// call this from external pages...
function extnavabs (x,y,cancelmode) {
	navabs(x,y,cancelmode);
}
function navabs (x,y,cancelmode) {
	// alle elemente mit javascript-mouseover deaktivieren, um javascript fehler beim laden zu verhindern
	
	if (gBig) {
		document.getElementById("mapzone").innerHTML = "";
	} else {
		var i,ix,iy,mouselistener;
		for (iy=0;iy<gCY;++iy)
		for (ix=0;ix<gCX;++ix) {
			mouselistener = document.getElementById("mouselistener_"+iy+"_"+ix);
			mouselistener.innerHTML = "";
		}
	}
	var mode = cancelmode?kJSMapMode_Normal:gMapMode;
	//location.href = location.pathname + "?sid="+gSID+"&x="+x+"&big="+gBig+"&y="+y+"&cx="+gCX+"&cy="+gCY+"&mode="+mode+"&scroll="+gScroll+"&army="+gActiveArmyID;
	//location.href = kBASEURL + "/mapjs7.php?sid="+gSID+"&x="+x+"&big="+gBig+"&y="+y+"&cx="+gCX+"&cy="+gCY+"&mode="+mode+"&scroll="+gScroll+"&army="+gActiveArmyID;
	location.href = kBASEURL+"/"+kMapScript+"?sid="+gSID+"&x="+x+"&big="+gBig+"&y="+y+"&cx="+gCX+"&cy="+gCY+"&mode="+mode+"&scroll="+gScroll+"&army="+gActiveArmyID;
}


function profiling (text) {
	return; // no more profiling needed, building-speedup did a fine job !
	var curdate = new Date();
	var curtime = curdate.getTime();
	var timediff = (gLastDebugTime==0)?0:(curtime - gLastDebugTime);
	gLastDebugTime = curtime;
	if (timediff > 0)
		gProfileLastLine += "...took "+(Math.ceil(timediff)/1000.0)+" seconds.<br>";
	debuglog(gProfileLastLine);
	gProfileLastLine = text;
}

function debuglog (text) {
	document.getElementsByName("mapdebug")[0].innerHTML = text+document.getElementsByName("mapdebug")[0].innerHTML;
}
