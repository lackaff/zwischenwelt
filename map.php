<?php require_once("lib.php"); ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<title>Zwischenwelt</title>

<link rel="stylesheet" type="text/css" href="mapdemo.css"></link>

<script type="text/javascript">
<!--

function InitAjaxObject() {
	var A;
	
	var msxmlhttp = new Array(
		'Msxml2.XMLHTTP.5.0',
		'Msxml2.XMLHTTP.4.0',
		'Msxml2.XMLHTTP.3.0',
		'Msxml2.XMLHTTP',
		'Microsoft.XMLHTTP');
	for (var i = 0; i < msxmlhttp.length; i++) {
		try {
			A = new ActiveXObject(msxmlhttp[i]);
		} catch (e) {
			A = null;
		}
	}
	
	if(!A && typeof XMLHttpRequest != "undefined")
		A = new XMLHttpRequest();

	return A;
}

function MakeRequest(url,callback) {
	var http_request = InitAjaxObject();
	
	if (!http_request)return false;
	
	http_request.onreadystatechange = function() {
		if (http_request.readyState == 4) {
			if (http_request.status == 200) {
				callback(http_request.responseText);
			}
		}
	};
	http_request.open('GET', url, true);
	http_request.send(null);
}

//PrepareMap(400,300,200,200,map,27,10,0,0);
function PrepareMap(width, height, top, left, map, tileSize, segmentSize, segx, segy) {
	for(var child = map.firstChild; child; child = child.nextSibling) {
		if(child.className == 'pane') {
			map.pane = child;
			child.map = map;
		} else if(child.className == 'mouse') {
			map.mouse = child;
			child.map = map;
		} else if(child.className == 'status') {
			map.status = child;
			child.map = map;
		}
	}
	map.dimensions = {
		'width': width, 'height': height,
		'top': top, 'left': left,
		'segw': segmentSize, 'segh': segmentSize,
		'tilew': tileSize, 'tileh': tileSize,
		'segx': segx, 'segy': segy,
		'rows': Math.round(height / (segmentSize*tileSize)) + 2,
		'cols': Math.round(width / (segmentSize*tileSize)) + 2
	};

	map.pane.style.width = width+'px';
	map.pane.style.height = height+'px';
	map.pane.style.position = 'absolute';
	map.pane.style.left = left+'px';
	map.pane.style.top = top+'px';

	map.mouse.style.width = width+'px';
	map.mouse.style.height = height+'px';
	map.mouse.style.position = 'absolute';
	map.mouse.style.left = left+'px';
	map.mouse.style.top = top+'px';

	map.status.style.position = 'absolute';
	map.status.style.left = left+'px';
	map.status.style.top = (top+height)+'px';

	map.mouse.onmousedown = PressMouse;
	map.mouse.onmouseup = ReleaseMouse;

	AddSegments(map, -width/2, -height/2);
}

function GetSegment(map, segx, segy) {
	for(var i = 0; i < map.segments.length; ++i) {
		var segment = map.segments[i];
		if(segment.segx == segx && segment.segy == segy)return segment;
	}
	//alert("segment not found");
	return null;
}

function AddSegments(map, dx, dy) {
	var mouse = map.mouse;
	var pane = map.pane;
	var dim = map.dimensions;

	map.segments = [];

	var rows = dim.rows;
	var cols = dim.cols;
	
	var segwpx = dim.segw*dim.tilew;
	var seghpx = dim.segh*dim.tileh;
	
	PrintStatus(map, 'size: '+segwpx+' '+seghpx+' rows: '+rows+', cols: '+cols);
	//alert("rows="+rows+" cols="+cols);
	for(var c = 0; c < cols; c += 1) {
		for(var r = 0; r < rows; r += 1) {

			var segment = {'dx': (c*segwpx)+dx, 'dy': (r*seghpx)+dy, 'segx': dim.segx+c, 'segy': dim.segy+r, 'div': document.createElement('div'), 'map': map};
			segment.div.className = 'segment';
			segment.div.style.width = (segwpx)+'px';
			segment.div.style.height = (seghpx)+'px';
			segment.div.style.position = 'absolute';
			segment.div.style.top = (segment.dy)+'px';
			segment.div.style.left = (segment.dx)+'px';
			segment.div.style.border = 'dotted black 0px';

			//alert(c+" "+r+" "+segment.dx+" "+segment.dy+" "+segment.div.style.left+" "+segment.div.style.top+" "+dim.left+" "+dim.top);
			
			segment.div.innerHTML = "";
			
			pane.appendChild(segment.div);
			map.segments.push(segment);

			ReloadSegment(map,segment);
		}
	}
}

function ReloadSegment(map,segment){
	var dim = map.dimensions;
	var segwpx = dim.segw*dim.tilew;
	var seghpx = dim.segh*dim.tileh;
	segment.div.innerHTML = '<img width='+segwpx+' height='+seghpx+' src="gfx/map_loading.png">';
	segment.loading = true;
	LoadSegment(map,segment.segx,segment.segy);
}

function UpdateMap(map, dx, dy) {
	var dim = map.dimensions;
	var segwpx = dim.segw*dim.tilew;
	var seghpx = dim.segh*dim.tileh;
	var tresholdw = segwpx*0.66;
	var tresholdh = seghpx*0.66;
	
	for(var i = 0; i < map.segments.length; ++i) {
		var segment = map.segments[i];
		
		segment.dx += dx;
		segment.dy += dy;
		
		if(!segment.loading){
			if(segment.dx < -segwpx-tresholdw){
				segment.segx += dim.cols;
				segment.dx += dim.cols*segwpx;
				ReloadSegment(map,segment);
			} else if(segment.dy < -seghpx-tresholdh){
				segment.segy += dim.rows;
				segment.dy += dim.rows*seghpx;
				ReloadSegment(map,segment);
			} else if(segment.dx > dim.width+tresholdw){
				segment.segx -= dim.cols;
				segment.dx -= dim.cols*segwpx;
				ReloadSegment(map,segment);
			} else if(segment.dy > dim.height+tresholdh){
				segment.segy -= dim.rows;
				segment.dy -= dim.rows*seghpx;
				ReloadSegment(map,segment);
			}
		}

		segment.div.style.left = (segment.dx)+'px';
		segment.div.style.top = (segment.dy)+'px';
	}
}

function MoveMouse(event)
{
	var map = this.map;
	var ev = GetEvent(event);
	var x = ev.clientX;
	var y = ev.clientY;
	
	var dx = x - map.mouseMove.x;
	var dy = y - map.mouseMove.y;

	map.mouseMove = {'x': x, 'y': y};
	
	UpdateMap(map, dx, dy);
	
	PrintStatus(map, 'mouse at: '+x+', '+y+' ('+dx+'|'+dy+')');
}

function PressMouse(event)
{
	var map = this.map;
	var dim = map.dimensions;
	var ev = GetEvent(event);

	map.pane.style.cursor = map.mouse.style.cursor = 'move';
	this.onmousemove = MoveMouse;

	var x = ev.clientX;
	var y = ev.clientY;
	
	map.mouseStart = {'x': x, 'y': y};
	map.mouseMove = {'x': x, 'y': y};
	map.mousePressed = true;
	
	PrintStatus(map, 'mouse pressed at '+x+','+y);
}

function ReleaseMouse(event)
{
	var ev = GetEvent(event);

	var map = this.map;
	map.mouse.onmousemove = null;
	map.pane.style.cursor = map.mouse.style.cursor = 'default';
	map.mousePressed = false;

	var x = ev.clientX;
	var y = ev.clientY;

	PrintStatus(map, 'mouse dragged from '+map.mouseStart.x+', '+map.mouseStart.y+' to '+x+','+y);
}

function PrintStatus(map, message) {
	map.status.innerHTML = message;
}

function GetEvent(event)
{
	if(event == undefined) {
		return window.event;
	}

	return event;
}

function LoadSegment(map, x, y) {
	var w = map.dimensions.segw;
	var h = map.dimensions.segh;
	var url = 'ajax_map.php?x='+x+'&y='+y+'&w='+w+'&h='+h;
	var segment = GetSegment(map,x,y);
	
	//segment.div.innerHTML = "<img src=\""+url+"\">";
	//segment.loading = false;
	//return;
	
	MakeRequest(url,function(response) {
		var segment = GetSegment(map,x,y);
		segment.div.innerHTML = response;
		segment.loading = false;
		UpdateMap(map,0,0);
	});
}

//-->
</script>

<style>
body, html {
	padding:0px;
	margin:0px;
	overflow:hidden;
}

div {
	border:0px;
	margin:0px;
	padding:0px;
	z-index:0;
}
</style>

</head>
<body>
        <div id="map" class="map">
		<div class="pane"></div>
		<div class="mouse" style="border:dotted red 0px"></div>
		<p class="status"></p>
        </div>
<!--
	<div style="position:absolute;left:800px;top:0px;width:800px;height:600px;background-color:gray;z-index:1;"></div>
	<div style="position:absolute;left:0px;top:600px;width:2000px;height:1000px;background-color:gray;z-index:1;"></div>
-->
</body>

<?php
if(isset($f_x))$segx = round(((int)$f_x)/10); else $segx = 0;
if(isset($f_y))$segy = round(((int)$f_y)/10); else $segy = 0;
?>
<script type="text/javascript">
<!--
var w = window.innerWidth;
var h = window.innerHeight;
var map = document.getElementById("map");
PrepareMap(w,h,0,0,map,27,10,<?=$segx?>,<?=$segy?>);
//window.setTimeout("loadSegment(map,10,10)", 1000);
//-->
</script>
</html>
