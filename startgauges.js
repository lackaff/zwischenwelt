function startGauges()
{
	if (!document.getElementsByTagName) return false;
	if (!document.getElementById) return false;
	if (!document.body.style) return false;
	var divs=document.getElementsByTagName('div');
	var format=/(\d+)x(\d+):([\d]+)of([\d]+):(\-?[\d]+)/;
	for (var i in divs)
	{
		if (divs[i].className=='gauge')
		{
			var div_done=document.createElement('span');
			var div=divs[i];
			div.className='gaugeon';
			div_done.className='gaugeondone';
			if (!format.test(div.title))
			{
				alert('div '+div.id+' hat falsch formatierten title: '+div.title);
                        continue;
			}
			var data=format.exec(div.title);
			if (data.length==0) continue; // eigendlich überflüssig
                    div.style.width=data[1]+'px';
                    div.style.height=data[2]+'px';
                    div_done.style.height=(data[2]-2)+'px';
                    div_done.style.width='0px';
                    div_done.id=div.id+'_done';
                    div.appendChild(div_done);
                    runGauge(div_done.id,data[3],data[4],data[5],data[1]-1);
		} // each div.gauge
	} // each div
} // startGauge()

function runGauge(div_done_id,pos,max,step,width)
{
	pos+=step;
	while (pos<0) pos+=max;
	while (pos>=max) pos-=max;
	var x=Math.floor(width/max*pos);
	document.getElementById(div_done_id).style.width=x.toString()+'px';
	window.setTimeout("runGauge('"+div_done_id+"',"+pos+","+max+","+step+","+width+")",Math.abs(step)*1000);
} // runGauge()
