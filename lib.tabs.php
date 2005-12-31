<?php
require_once("lib.main.php");

$gTabPaneNumber = 0;

// $cssclass : class for div-container created around the whole thing
// $tabs : array of $pair : $pair[0] = header-image/text/html, $pair[1] = content
// $corner : html for the right-top corner (cover in spans !)
// selected is compared to the keys of the $tabs array, can be numeric or associative
// $jschangecallback : if not false, then this is javascript function is called at tabchange jschangecallback(tabnum)
function PrintTabs ($cssclass,$tabs,$corner="",$jschangecallback=false,$selected=0) {
	// internally for the javascript new numbers are used ($i)
	global $gTabPaneNumber;
	?>
	<SCRIPT LANGUAGE="JavaScript" type="text/javascript"><!--
	function TabPane<?=$gTabPaneNumber?>Activate (tabnum) {
		var i;
		var heads = document.getElementsByName("tabhead<?=$gTabPaneNumber?>");
		var panes = document.getElementsByName("tabpane<?=$gTabPaneNumber?>");
		for (i in heads) if (heads[i].className == "activetab") {
			if (i == tabnum) return; // nothing to activate
			heads[i].className = "inactivetab";
			panes[i].style.display = "none";
		}
		heads[tabnum].className = "activetab";
		panes[tabnum].style.display = "inline";
		<?php if ($jschangecallback) {?> <?=$jschangecallback?>(tabnum); <?php }?>
	}
	//-->
	</SCRIPT>
	<div class="<?=$cssclass?>">
	<div class="tabs">
		<div class="tabheader">
			<ul>
				<?php $i = 0; foreach ($tabs as $id => $pair) {?>
				<li name="tabhead<?=$gTabPaneNumber?>" 
					class="<?=$selected==$id?"activetab":"inactivetab"?>" 
					onClick="TabPane<?=$gTabPaneNumber?>Activate(<?=$i?>)"><span class="tabhead"><?=($pair[0])?></span></li>
				<?php ++$i; } // endforeach?>
			</ul>
			<div class="corner"><?=$corner?></div>
		</div>
		<?php $i = 0;  foreach ($tabs as $id => $pair) {?>
		<div class="tabpane" name="tabpane<?=$gTabPaneNumber?>" <?=$selected==$id?"":"style=\"display:none;\""?>><?=($pair[1])?></div>
		<?php ++$i; } // endforeach?>
	</div>
	</div>
	<?php
	++$gTabPaneNumber;
}

?>