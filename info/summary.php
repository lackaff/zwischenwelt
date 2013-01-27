<?php
require_once("../lib.main.php");
require_once("../lib.building.php");
require_once("../lib.army.php");
require_once("../lib.map.php");
require_once("../lib.spells.php");
Lock();
profile_page_start("summary.php");


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


<?php
	// spell testing
	$spelltestimg = "<img border=0 src='".g("icon/admin.png")."' alt='spelltest' title='spelltest'>";
	if ($gUser->admin && isset($f_spelltest)) {
		ImgBorderStart("s1","jpg","#ffffee","",32,33);
		$o = sqlgetobject("SELECT * FROM `spell` WHERE `id` = ".intval($f_spelltest));
		echo "<h3>Spelltest : ".$f_spelltestrepeat." Minuten , ".$gSpellType[$o->type]->name." [$o->id] </h3>";
		vardump2($o);
		for ($i=0;$i<intval($f_spelltestrepeat);++$i) {
			$o = sqlgetobject("SELECT * FROM `spell` WHERE `id` = ".intval($f_spelltest));
			if ($o) {
				$spell = GetSpellInstance($o->type,$o);
				$spell->Cron(60);
			}
		}
		ImgBorderEnd("s1","jpg","#ffffee",32,33);
	}
	if ($gUser->admin && isset($f_spellexpire)) {
		ImgBorderStart("s1","jpg","#ffffee","",32,33);
		$o = sqlgetobject("SELECT * FROM `spell` WHERE `id` = ".intval($f_spellexpire));
		echo "<h3>Spell-Expire , ".$gSpellType[$o->type]->name." [$o->id] </h3>";
		$spell = GetSpellInstance($o->type,$o);
		$spell->Expire();
		ImgBorderEnd("s1","jpg","#ffffee",32,33);
	}
	
	$myspells = sqlgettable("SELECT * FROM `spell` WHERE `owner`=".$gUser->id." ORDER BY `target`,`type`");
	$onme = sqlgettable("SELECT * FROM `spell` WHERE `target`=".$gUser->id." ORDER BY `type`");
?>


	<?php if (count($myspells) > 0 || count($onme) > 0) {?>	
			<h4>Zauber</h4>
			<table id='summary'>
				<tr><th colspan=5>Meine Zauber</th></tr>
			<?php foreach ($myspells as $spell){
				$spellobj = GetSpellInstance($spell->type,$spell);
				$effect = $spellobj->Effect();
				?>
				<tr>
					<td nowrap><?=cText::Wiki("spell",$spell->type)?><a href="<?=Query("?sid=?&x=?&y=?&infospelltype=".$spell->type)?>"><?=$gSpellType[$spell->type]->name?></a></td>
					<td nowrap>auf <?=($spell->targettype==MTARGET_AREA)?opos2txt($spell):nick($spell->target,"Server",true)?></td>
					<td nowrap>noch <?=Duration2Text($spell->lasts-time())?></td>
					<td nowrap><?=$effect?></td>
					<?php if ($gUser->admin) {?>
						<td nowrap>
						<a href="<?=query("adminspell.php?sid=?&id=".$spell->type)?>"><img alt=Admin title=Admin src="<?=g("icon/admin.png")?>" border=0></a>
						<a href="<?=Query("?sid=?&spelltest=".$spell->id."&spelltestrepeat=1")?>">1</a>
						<a href="<?=Query("?sid=?&spelltest=".$spell->id."&spelltestrepeat=5")?>">5</a>
						<a href="<?=Query("?sid=?&spelltest=".$spell->id."&spelltestrepeat=60")?>">60</a>
						<a href="<?=Query("?sid=?&spellexpire=".$spell->id)?>">expire<?=$spelltestimg?></a>
						</td>
					<?php } // endif?>
				</tr>
				<?php
			}?>
			<tr><th colspan=5><img src="<?=g("1px.gif")?>"></th></tr>
			<tr><th colspan=5>Zauber auf mich</th></tr>
			<?php foreach ($onme as $spell){
				$spellobj = GetSpellInstance($spell->type,$spell);
				$effect = $spellobj->Effect();
				?>
				<tr>
					<td nowrap><?=cText::Wiki("spell",$spell->type)?> <a href="<?=Query("?sid=?&x=?&y=?&infospelltype=".$spell->type)?>"><?=$gSpellType[$spell->type]->name?></a></td>
					<td nowrap>von <?=nick($spell->owner,"Server",true)?></td>
					<td nowrap>noch <?=Duration2Text($spell->lasts-time())?></td>
					<td nowrap><?=$effect?></td>
					<?php if ($gUser->admin) {?>
						<td nowrap>
						<a href="<?=query("adminspell.php?sid=?&id=".$spell->type)?>"><img alt=Admin title=Admin src="<?=g("icon/admin.png")?>" border=0></a>
						<a href="<?=Query("?sid=?&spelltest=".$spell->id."&spelltestrepeat=1")?>">1</a>
						<a href="<?=Query("?sid=?&spelltest=".$spell->id."&spelltestrepeat=5")?>">5</a>
						<a href="<?=Query("?sid=?&spelltest=".$spell->id."&spelltestrepeat=60")?>">60</a>
						<a href="<?=Query("?sid=?&spellexpire=".$spell->id)?>">expire<?=$spelltestimg?></a>
						</td>
					<?php } // endif?>
				</tr>
				<?php
			}?>
		</table>
	<?php } // endif?>

</body>
</html>
<?php profile_page_end(); ?>
