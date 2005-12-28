// available map-params : gCX,gCY,gLeft,gTop,gSID,gThisUserID,gGFXBase,gBig,gXMid,gYMid,gMapMode
// available map-data : gTerrain,gBuildings,gArmies,gItems,gPlans,gOre??
// available globals : gTerrainTypes,gBuildingTypes...
// see also mapnavi_globals.js.php

// the order in which fields are filled from mapjs7.php
function lu (guild,color,name) {
	var res = new Object();
	res.guild = guild;res.color = color;res.name = name;
	return res;
}
gLocalUsers = new Array(); // filled with function, for string security

var i = 0;
kJSField_x = i++; // convenient for general search
kJSField_y = i++;
i = 0;
kJSBuildingField_x = i++;
kJSBuildingField_y = i++;
kJSBuildingField_type = i++;
kJSBuildingField_user = i++;
kJSBuildingField_level = i++;
kJSBuildingField_hp = i++;
kJSBuildingField_construction = i++;
i = 0;
kJSArmyField_x = i++;
kJSArmyField_y = i++;
kJSArmyField_type = i++;
kJSArmyField_user = i++;
kJSArmyField_units = i++;
kJSArmyField_items = i++;
kJSArmyField_flags = i++;
i = 0;
kJSItemField_x = i++;
kJSItemField_y = i++;
kJSItemField_type = i++;
kJSItemField_amount = i++;
i = 0;
kJSPlanField_x = i++;
kJSPlanField_y = i++;
kJSPlanField_type = i++;
kJSPlanField_priority = i++;

// createmap is called as soon as loading is complete
var gXMid,gYMid;

function MapInit() {
	gXMid=Math.floor(gCX/2);
	gYMid=Math.floor(gCY/2);
	
	// parse data
	gTerrain = gTerrain.split(";");							
	for (i in gTerrain) gTerrain[i] = gTerrain[i].split(",");
	
	gBuildings = gBuildings.split(";");
	gBuildings.pop();
	for (i in gBuildings) gBuildings[i] = gBuildings[i].split(",");
	
	gItems = gItems.split(";");
	gItems.pop();
	for (i in gItems) gItems[i] = gItems[i].split(",");
	
	gPlans = gPlans.split(";");
	gPlans.pop();
	for (i in gPlans) gPlans[i] = gPlans[i].split(",");
	
	gArmies = gArmies.split(";"); 
	gArmies.pop(); 				
	for (i in gArmies) {
		gArmies[i] = gArmies[i].split(",");
		var units = gArmies[i][kJSArmyField_units].split("|");
		units.pop();
		gArmies[i][kJSArmyField_units] = new Array();
		for (j in units) {
			var type_amount_pair = units[j].split(":");
			if (gArmies[i][kJSArmyField_units][type_amount_pair[0]] == undefined)
				gArmies[i][kJSArmyField_units][type_amount_pair[0]] = 0;
			gArmies[i][kJSArmyField_units][type_amount_pair[0]] += parseInt(type_amount_pair[1]); 
		}
	}
	
	CreateMap();
}

function CreateMap() {
	var row,x,y,i,j;
	
	var mapstyle = "style=\"width:'+((2+gCX)*gTilesize)+'px;\"";
	var maphtml = "<table class=\"map\" border=0 cellpadding=0 cellspacing=0 "+mapstyle+" onMouseout=\"KillTip()\">\n";
	
	// top row
	row = "";
	for (x=-1;x<gCX+1;++x) {
		var myclass = "n"+ (x<gXMid ? "w" : (x==gXMid?"":"e"));
		if (x < 0 || x >= gCX) myclass += "_edge";
		row += "<th class=\"mapborder_"+myclass+"\" onClick=\"nav("+(x<0?-1:(x>=gCX?1:0))+",-1)\">"+(x+gLeft)+"</th>\n";
	}
	maphtml += "\n<tr>"+row+"</tr>\n";
	
	// maptiles
	for (y=0;y<gCY;++y) {
		var myclass = (y<gYMid ? "n" : (y==gYMid?"":"S"));
		row = "";
		row += "<th class=\"mapborder_"+myclass+"w\" onClick=\"nav(-1,0)\">"+(y+gTop)+"</th>\n";
		for (x=0;x<gCX;++x) row += "<td class=\"mapcell\">"+GetCellHTML(x,y)+"</td>\n";
		row += "<th class=\"mapborder_"+myclass+"e\" onClick=\"nav(1,0)\">"+(y+gTop)+"</th>\n";
		maphtml += "\n<tr>"+row+"</tr>\n";
	}
	
	// bottom row
	row = "";
	for (x=-1;x<gCX+1;++x) {
		var myclass = "s"+ (x<gXMid ? "w" : (x==gXMid?"":"e"));
		if (x < 0 || x >= gCX) myclass += "_edge";
		row += "<th class=\"mapborder_"+myclass+"\" onClick=\"nav("+(x<0?-1:(x>=gCX?1:0))+",1)\">"+(x+gLeft)+"</th>\n";
	}
	maphtml += "\n<tr>"+row+"</tr>\n";
	maphtml += "</table>";
	maphtml += "<span class=\"maptip\" onMouseover=\"KillTip()\" name=\""+kMapTipName+"\" style=\"position:absolute;top:0px;left:0px; visibility:hidden;\"></span>";
	
	var tab_topright = "bla";
	
	var tab_pre = "";
	tab_pre += "<div id=\"panel\">";
	tab_pre += "	<div id=\"header\">";
	tab_pre += "		<ul>";
	tab_pre += "			<li id=\"current\"><a href=\"#\">Normal</a></li>";
	tab_pre += "			<li><a href=\"#\">Pl&auml;ne</a></li>";
	tab_pre += "			<li><a href=\"#\">Bauzeit</a></li>";
	tab_pre += "			<li><a href=\"#\">HP</a></li>";
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
	tab_pre += "<td class=\""+(gMapMode==kJSMapMode_Normal?"":"in"	)+"activetab\"><a href=\"javascript:SetMapMode(kJSMapMode_Normal)\">Normal</a></td>\n";
	tab_pre += "<td class=\""+(gMapMode==kJSMapMode_Plan?"":"in"	)+"activetab\"><a href=\"javascript:SetMapMode(kJSMapMode_Plan)\">Pläne</a></td>\n";
	tab_pre += "<td class=\""+(gMapMode==kJSMapMode_Bauzeit?"":"in"	)+"activetab\"><a href=\"javascript:SetMapMode(kJSMapMode_Bauzeit)\">Bauzeit</a></td>\n";
	tab_pre += "<td class=\""+(gMapMode==kJSMapMode_HP?"":"in"		)+"activetab\"><a href=\"javascript:SetMapMode(kJSMapMode_HP)\">HP</a></td>\n";
	tab_pre += "<td class=\"tabfillerright\" width=\"100%\" align=\"right\">"+tab_topright+"</td>\n";
	tab_pre += "</tr><tr>\n";
	tab_pre += "<td class=\"tabpane\" colspan=5><div>\n";
	var tab_post = "\n</div></td></tr></table>\n";
	*/
	document.getElementById("mapzone").innerHTML = tab_pre + maphtml + tab_post;
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
	var buildingtype = building[kJSBuildingField_type];
	var level = building[kJSBuildingField_level];
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
	var layers = new Array();
	
	// terrain
	var backgroundcolor = false;
	var terraintype = GetTerrainType(relx,rely);
	layers[layers.length] = GetTerrainPic(terraintype,relx,rely);
	
	// building
	var building = SearchPos(gBuildings,relx,rely);
	if (building) {
		if (building[kJSBuildingField_construction] > 0) {
			layers[layers.length] = g(kConstructionPic);
		} else {
			layers[layers.length] = GetBuildingPic(building,relx,rely);
			if (building[kJSBuildingField_user] > 0) backgroundcolor = gLocalUsers[building[kJSBuildingField_user]].color;
		}
		if (gMapMode==kJSMapMode_HP) {
			var hp = building[kJSBuildingField_hp];
			var type = building[kJSBuildingField_type];
			var level = building[kJSBuildingField_level];
			backgroundcolor = GradientRYG(GetFraction(hp,calcMaxBuildingHp(type,level)))
		}
	}
	
	// plan
	var plan = SearchPos(gPlans,relx,rely);
	if (plan) {
		if (gMapMode==kJSMapMode_Plan) {
			backgroundcolor = "red";
			layers[layers.length] = g3(gBuildingType[plan[kJSPlanField_type]].gfx,0,0);
		} else {
			layers[layers.length] = g(kTransCP);
		}
	}
	
	// item
	var item = SearchPos(gItems,relx,rely);
	if (item) layers[layers.length] = g(gItemType[item[kJSItemField_type]].gfx);
	
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
		if (army[kJSArmyField_user] > 0) backgroundcolor = gLocalUsers[army[kJSArmyField_user]].color;
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

function mapover (relx,rely) {
	// generate tip text
	var i;
	var tiptext = relx+","+rely+"<br>";

	// terrain
	var terraintype = GetTerrainType(relx,rely);
	tiptext += "<img src=\""+GetTerrainPic(terraintype,relx,rely)+"\">"+gTerrainType[terraintype].name+"<br>";
	
	// building
	var building = SearchPos(gBuildings,relx,rely);
	if (building) {
		var buildingtype = building[kJSBuildingField_type];
		var hp = building[kJSBuildingField_hp];
		var level = building[kJSBuildingField_level];
		var user = building[kJSBuildingField_user];
		tiptext += "<img src=\""+GetBuildingPic(building,relx,rely)+"\">";
		tiptext += gBuildingType[buildingtype].name + " Stufe "+level;
		if (user > 0) tiptext += " von "+gLocalUsers[user].name;
		tiptext += " HP : "+hp+"/"+gBuildingType[buildingtype].maxhp;
		tiptext += "<br>";
		// backgroundcolor = GradientRYG(GetFraction(hp,calcMaxBuildingHp(type,level)))
	}
	
	// plan
	var plan = SearchPos(gPlans,relx,rely);
	if (plan) {
		tiptext += "<img src=\""+g(kTransCP)+"\">";
		tiptext += "<img src=\""+g3(gBuildingType[plan[kJSPlanField_type]].gfx,0,0)+"\">";
		tiptext += "<br>";
	}
	
	// item
	var item = SearchPos(gItems,relx,rely);
	if (item) {
		tiptext += "<img src=\""+g(gItemType[item[kJSItemField_type]].gfx)+"\">";
		tiptext += gItemType[item[kJSItemField_type]].name+":"+item[kJSItemField_amount];
		tiptext += "<br>";
	}
	
	// army
	var army = SearchPos(gArmies,relx,rely);
	if (army) {
		var user = army[kJSArmyField_user];
		for (i in army[kJSArmyField_units]) tiptext += "<img src=\""+g(gUnitType[i].gfx)+"\">"+army[kJSArmyField_units][i];
		if (user > 0) tiptext += " von "+gLocalUsers[user].name;
		tiptext += "<br>";
	}
	
	// find a suitable position
	var x,y;
	x = kMapTip_xoff + kJSMapTileSize*(relx+1);
	y = kMapTip_yoff + kJSMapTileSize*(rely+1);
	// spawn tip
	document.getElementsByName(kMapTipName)[0].style.visibility = "visible";
	document.getElementsByName(kMapTipName)[0].style.position = "absolute";
	document.getElementsByName(kMapTipName)[0].style.left = x+"px";
	document.getElementsByName(kMapTipName)[0].style.top = y+"px";
	document.getElementsByName(kMapTipName)[0].innerHTML = tiptext;
}

function KillTip () {
	document.getElementsByName(kMapTipName)[0].style.visibility = "hidden";
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
		if (arr[i][kJSField_x] == relx + gLeft && 
			arr[i][kJSField_y] == rely + gTop) return arr[i];
	}
	return false;
}

function GetBuildingType (relx,rely) {
	var building = SearchPos(gBuildings,relx,rely);
	if (building == false) return 0;
	return building[kJSBuildingField_type];
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
	for (i in army[kJSArmyField_units])
		if (maxamount < army[kJSArmyField_units][i]) {
			maxamount < army[kJSArmyField_units][i];
			maxtype = i;
		}
	return maxtype;
	 //army[kJSArmyField_units];
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
	var scroll = 5;
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

