<?php
require_once("../lib.main.php");
require_once("../lib.army.php");
require_once("../lib.building.php");
Lock();
$t = time();

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 transitional//EN"
   "http://www.w3.org/TR/html4/transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<link href="http://fonts.googleapis.com/css?family=Bree+Serif" rel="stylesheet" type="text/css">
<link href="http://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="<?=BASEURL?>css/zwstyle_new_temp.css">
<title>Zwischenwelt - Stats</title>

</head>
<body>

<?php
include("../menu.php");
include("../stats/header.php");
ImgBorderStart();

$gGuilds = sqlgettable("SELECT * FROM `guild`","id");

switch($f_what){
	
	case 'gom':
		$pts=sqlgettable("SELECT `guild`,SUM(`army_pts`) AS pts,COUNT(`id`) as `anzahl` FROM `user` WHERE `admin`=0 AND `guild`>0 GROUP BY `guild` ORDER BY pts DESC");
	break;
	
	case 'gnm':
		$pts=sqlgettable("SELECT `guild`,SUM(`general_pts`) AS pts,COUNT(`id`) as `anzahl` FROM `user` WHERE `admin`=0 AND `guild`>0  GROUP BY `guild` ORDER BY pts DESC");
	break;
	
	case 'g':
		$pts=sqlgettable("SELECT `guild`,SUM(`general_pts`+`army_pts`) AS pts,COUNT(`id`) as `anzahl` FROM `user` WHERE `admin`=0 AND `guild`>0 GROUP BY `guild` ORDER BY pts DESC","guild");
	break;
	
	default:
		$pts=sqlgettable("SELECT `guild`,SUM(`general_pts`+`army_pts`) AS pts,COUNT(`id`) as `anzahl` FROM `user` WHERE `admin`=0 AND `guild`>0 GROUP BY `guild` ORDER BY pts DESC");
	break;
}


echo "<table>";
echo "<tr><th>Rank</th><th>Name</th><th>Points</th><th>&nbsp;</th><th>Players</th><th>&nbsp;</th><th>Average</th></tr>";
$n=1;

foreach($pts as $p){
	if($p->guild>0)echo "<tr><td align=right>".$n++."</td><td><a href='".query("../info/viewguild.php?id=".$p->guild."&sid=?")."'>".$gGuilds[$p->guild]->name."</a></td><td align=right>".kplaintrenner($p->pts)."</td><td>&nbsp;</td><td align=right>".$p->anzahl."</td><td></td><td align=right>".kplaintrenner(round($p->pts/$p->anzahl))."</td></tr>";
}

echo "</table>";
 
ImgBorderEnd();
include("../stats/footer.php");
?>

</body>
</html>
