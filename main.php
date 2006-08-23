<?php
include("lib.main.php");
Lock();
$dummyframesetcols = array();
for($i=0;$i<kDummyFrames;++$i) $dummyframesetcols[] = "0";
$dummyframesetcols = implode(",",$dummyframesetcols);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">
<html>
<head>
<title>Zwischenwelt</title>
<link REL="shortcut icon" HREF="favicon.ico" TYPE="image/png">
</head>
<frameset cols="*,360" noresize>
	<?php if((intval($gUser->flags) & kUserFlags_ShowLogFrame) == 0){ ?>
	<frameset rows="85,*,0" noresize>
		<frame src="<?=SessionLink("compactmenu.php".((isset($f_fc) && $f_fc==1)?"?fc=1":""));?>" scrolling=no name="menu" frameborder="1">
		<frame src="<?=SessionLink("info.php".((isset($f_fc) && $f_fc==1)?"?fc=1":""));?>" name="info" frameborder="0">
		<frameset cols="<?=$dummyframesetcols?>" noresize>
			<?php for($i=0;$i<kDummyFrames;++$i) {?>
			<frame src="about:blank" name="dummy<?=$i?>" noresize frameborder="0">
			<?php } // endforeach?>
		</frameset>
	</frameset>
	<?php } else { ?>
	<frameset rows="85,*,85,0" noresize>
		<frame src="<?=SessionLink("compactmenu.php".((isset($f_fc) && $f_fc==1)?"?fc=1":""));?>" scrolling=no name="menu" frameborder="1">
		<frame src="<?=SessionLink("info.php".((isset($f_fc) && $f_fc==1)?"?fc=1":""));?>" name="info" frameborder="1">
		<frame src="<?=SessionLink("log.php");?>" name="log" frameborder="0">
		<frameset cols="<?=$dummyframesetcols?>" noresize>
			<?php for($i=0;$i<kDummyFrames;++$i) {?>
			<frame src="about:blank" name="dummy<?=$i?>" noresize frameborder="0">
			<?php } // endforeach?>
		</frameset>
	</frameset>
	<?php } ?>
	<?php if (kMapScript == "mapjs5.php") {?>
	<frame src="<?=SessionLink(kMapScript);?>" name="map" id="map" noresize frameborder="1">
	<?php } else {?>
	<frameset rows="383,*" noresize>
		<frame src="<?=SessionLink(kMapScript);?>" name="map" scrolling=no frameborder="1">
		<frame src="<?=SessionLink(kMapNaviScript);?>" name="navi" scrolling=yes frameborder="1">
	</frameset>
	<?php }?>
</frameset>
</html>
