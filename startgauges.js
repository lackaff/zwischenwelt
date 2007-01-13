function startGauges()
{
	if (!document.getElementsByTagName) return false;
	if (!document.getElementById) return false;
	if (!document.body.style) return false;
	var divs=document.getElementsByTagName('div');
	var format=/(\d+)x(\d+):([\d]+)of([\d]+)need([\d]+):(\-?[\d]+)/;
		// breite höhe pos max hot pixelinterval
	for (var i in divs)
	{
		if (divs[i].className=='gauge')
		{
			var div_done=document.createElement('span');
			var div=divs[i];
			if (!format.test(div.title))
			{
				alert('div '+div.id+' hat falsch formatierten title: '+div.title);
				continue;
			}
			var data=format.exec(div.title);
			var w=parseInt(data[1]);
			var h=parseInt(data[2]);
			var pos=parseInt(data[3]);
			var max=parseInt(data[4]);
			var hot=parseInt(data[5]);
			var pix=parseInt(data[6]);
			if (data.length==0) continue; // eigendlich überflüssig
			div_done.id=div.id+'_done';
			div.appendChild(div_done);

			var sleep = 1;
			if (navigator.userAgent.indexOf('MSIE')>=0)
			{
				//var sleep=max/w*pix;
				runGauge(div_done.id,pos,max,sleep,w+2,hot);
				div_done.style.width='0px';
				div.style.height=(h+0).toString()+'px';
				div.style.width=w.toString()+'px';
				div_done.style.height=(h-3).toString()+'px';
				div_done.style.marginBottom='1px';
				div_done.style.top='-1px';
				div_done.style.left='-1px';
			}
			else
			{
				//var sleep=max/(w-5)*pix;
				runGauge(div_done.id,pos,max,sleep,w-5,hot);
				div_done.style.width='0px';
				div.style.height=(h-2).toString()+'px';
				div.style.width=(w-2).toString()+'px';
				div_done.style.height=(h-6).toString()+'px';
			}

			div.title="Spanne: "+max+"sec. Die ersten "+hot+"sec läuft der CRON. Refresh: "+(Math.floor(sleep*10)/10).toString()+"sec.";
		} // each div.gauge
	} // each div
} // startGauge()

function runGauge(div_done_id,pos,max,step,width,hot)
{
	var div_done=document.getElementById(div_done_id);
	pos+=step;
	while (pos<0) pos+=max;
	while (pos>=max) pos-=max;
	var x=Math.floor((0.0 + width)*((0.0 + pos)/(0.0 + max)));
	var h=div_done.style.height;
	if (pos<hot) 
	{
		div_done.className='gaugeondone gaugeondonehot';
		div_done.parentNode.className='gaugeon gaugeonbusy';
	}
	else
	{
		div_done.className='gaugeondone';
		div_done.parentNode.className='gaugeon';
	}
	div_done.style.width=x.toString()+'px';
	div_done.style.height=h;
	window.setTimeout("runGauge('"+div_done_id+"',"+pos+","+max+","+step+","+width+","+hot+")",Math.floor(step*1000));
} // runGauge()
