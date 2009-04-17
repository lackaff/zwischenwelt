<?php
$q = "title=".$_REQUEST["title"]."&x=".$_REQUEST["x"]."&y=".$_REQUEST["y"];
$md5 = md5($q);

if(file_exists("tmp/plot-$md5.png")){
	header("Location: tmp/plot-$md5.png");
	exit();
}

define("CONTENT_TYPE","image/png");
// see CONTENT_TYPE header('Content-type: image/png');

$x = explode(",",$_REQUEST["x"]);
$y = explode(",",$_REQUEST["y"]);

$l = min(sizeof($x),sizeof($y));

$tmp = "tmp/plot-".$md5;

$dat = "# x y\n";
for($i = 0;$i<$l;++$i)
	//if(is_float($x[$i]) && is_float($y[$i]))
		$dat .= $x[$i]." ".$y[$i]."\n";

$f = fopen($tmp.".dat","w");
fputs($f,$dat);
fclose($f);
$dat = null;

$plot = "
set terminal postscript eps noenhanced color solid defaultplex \"Verdana\" 16
set output \"| convert - $tmp.png\"
set grid
set size 1,0.7
set xdata time
set timefmt \"%Hh_%d.%m.%Y\"
plot \"$tmp.dat\" using 1:2 smooth csplines with lines title '".$_REQUEST["title"]."'";
//smooth csplines";

$f = fopen($tmp.".plot","w");
fputs($f,$plot);
fclose($f);
$plot = null;

//$s = 'echo \'set terminal postscript eps noenhanced color solid defaultplex "Verdana" 36\'"\n"\'set output "| convert - '.$tmp.'"\'"\nplot sin(x)" | gnuplot';
$s = "gnuplot < $tmp.plot";
exec($s);
passthru("rm $tmp.dat $tmp.plot && cat $tmp.png");

?>
