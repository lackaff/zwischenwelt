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
<link rel="stylesheet" type="text/css" href="<?=GetZWStylePath()?>">
<title>Zwischenwelt - Statistiken</title>

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
echo "<tr><th>Rang</th><th>Name</th><th>Punkte</th><th>&nbsp;</th><th>Spieler</th><th>&nbsp;</th><th>Durchschnitt</th></tr>";
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
