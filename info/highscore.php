<?php
require_once("../lib.main.php");
require_once("../lib.army.php");
Lock();
profile_page_start("highscore.php");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 transitional//EN"
   "http://www.w3.org/TR/html4/transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<link href="http://fonts.googleapis.com/css?family=Bree+Serif" rel="stylesheet" type="text/css">
<link href="http://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="<?=BASEURL?>css/zwstyle_new_temp.css">
<title>Zwischenwelt - Overview</title>
</head>
<body>
<?php include(BASEPATH."/menu.php"); ?>
<iframe src="../stats/gen_pts.php" width=100% height=370 frameborder=0 marginwidth=0 marginheight=0></iframe>
</body>
</html>
<?php profile_page_end(); ?>
