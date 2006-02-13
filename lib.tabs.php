<?php
require_once("lib.main.php");

$gTabPaneNumber = 0;


// $cssclass : class for div-container created around the whole thing
// $tabs : array of $tupel : $tupel[0] = header-image/text/html, $tupel[1] = content, $tupel[2] : url, if set, tabchange not by javascript, but by call
// $corner : html for the right-top corner (cover in spans !)
// selected is compared to the keys of the $tabs array, can be numeric or associative
// $jschangecallback : if not false, then this is javascript function is called at tabchange jschangecallback(tabnum)
function GenerateTabs ($cssclass,$tabs,$corner="",$jschangecallback=false,$selected=0) {
	// internally for the javascript new numbers are used ($i)
	global $gTabPaneNumber;
	$anzahl_tabs = count($tabs);
	rob_ob_start();
	?>
	<SCRIPT LANGUAGE="JavaScript" type="text/javascript"><!--
	function TabPane<?=$gTabPaneNumber?>Activate (tabnum) {
		var i,head,pane,nochange = false;
		for (i=0;i<<?=$anzahl_tabs?>;++i) {
			head = document.getElementById("tabhead<?=$gTabPaneNumber?>_"+i);
			pane = document.getElementById("tabpane<?=$gTabPaneNumber?>_"+i);
			if (!pane) return;
			if (head.className == "activetab") {
				if (i == tabnum) nochange = true; // nothing to activate
				if (!nochange) {
					head.className = "inactivetab";
					pane.style.display = "none";
				}
			}
		}
		if (!nochange) {
			head = document.getElementById("tabhead<?=$gTabPaneNumber?>_"+tabnum);
			pane = document.getElementById("tabpane<?=$gTabPaneNumber?>_"+tabnum);
			if (!pane) return;
			head.className = "activetab";
			pane.style.display = "inline";
		}
		<?php if ($jschangecallback) {?> <?=$jschangecallback?>(tabnum); <?php }?>
	}
	//-->
	</SCRIPT>
	<div class="<?=$cssclass?>">
	<div class="tabs">
		<div class="tabheader">
			<div class="tabcorner"><?=$corner?></div>
			<ul>
				<?php $i = 0; foreach ($tabs as $id => $tupel) {?>
				<?php $hasurl = isset($tupel[2]) && $tupel[2];?>
				<?php if ($hasurl && 0) {?><a href="<?=$tupel[2]?>"><?php } // endif?>
				<li name="tabhead<?=$gTabPaneNumber?>" id="tabhead<?=$gTabPaneNumber?>_<?=$i?>" 
					class="<?=$selected==$id?"activetab":"inactivetab"?>">
					<span class="tabhead">
						<a href="<?=$hasurl?($tupel[2]):("javascript:TabPane".$gTabPaneNumber."Activate(".$i.")")?>"><?=($tupel[0])?></a>
					</span>
				</li>
				<?php if ($hasurl && 0) {?></a><?php } // endif?>
				<?php ++$i; } // endforeach?>
			</ul>
		</div>
		<?php $i = 0;  foreach ($tabs as $id => $tupel) {?>
		<div class="tabpane" name="tabpane<?=$gTabPaneNumber?>" id="tabpane<?=$gTabPaneNumber?>_<?=$i?>" <?=$selected==$id?"":"style=\"display:none;\""?>><?=($tupel[1])?></div>
		<?php ++$i; } // endforeach?>
	</div>
	</div>
	<div class="tabsend"></div>
	<?php
	++$gTabPaneNumber;
	return rob_ob_end();
}



// for large quantities of tabs
function GenerateTabsMultiRow ($cssclass,$tabs,$max_per_row,$selected=0,$corner="",$jschangecallback=false) {
	global $gTabPaneNumber;
	$anzahl_tabs = count($tabs);
	rob_ob_start();
	?>
	<SCRIPT LANGUAGE="JavaScript" type="text/javascript"><!--
	function MultiTabPane<?=$gTabPaneNumber?>Activate (tabnum) {
		var i,head,pane,nochange = false;
		for (i=0;i<<?=$anzahl_tabs?>;++i) {
			head = document.getElementById("tabhead<?=$gTabPaneNumber?>_"+i);
			pane = document.getElementById("tabpane<?=$gTabPaneNumber?>_"+i);
			if (!pane) return;
			if (head.className == "activemultitab") {
				if (i == tabnum) nochange = true; // nothing to activate
				if (!nochange) {
					head.className = "inactivemultitab";
					pane.style.display = "none";
				}
			}
		}
		if (!nochange) {
			head = document.getElementById("tabhead<?=$gTabPaneNumber?>_"+tabnum);
			pane = document.getElementById("tabpane<?=$gTabPaneNumber?>_"+tabnum);
			if (!pane) return;
			head.className = "activemultitab";
			pane.style.display = "block";
		}
		<?php if ($jschangecallback) {?> <?=$jschangecallback?>(tabnum); <?php }?>
	}
	//-->
	</SCRIPT>
	<div class="<?=$cssclass?>">
	<div class="multitabs">
		<div class="multitabheader">
		<table border=0>
		<tr>
			<?php $i = 0; foreach ($tabs as $id => $tupel) {?>
				<th name="tabhead<?=$gTabPaneNumber?>" id="tabhead<?=$gTabPaneNumber?>_<?=$i?>"
					class="<?=$selected==$id?"activemultitab":"inactivemultitab"?>" 
					onClick="MultiTabPane<?=$gTabPaneNumber?>Activate(<?=$i?>)"><?=$tupel[0]?></th>
				<?php if ($i<$anzahl_tabs-1 && ($i%$max_per_row) == $max_per_row-1) { /*last one in row*/?>
					</tr><tr>
				<?php } // endif?>
			<?php ++$i; } // endforeach?>
			<?php if (!empty($corner)) {?>
			<th nowrap><?=$corner?></th>
			<?php } // endif?>
		</tr>
		</table>
		</div>
		<?php $i = 0;  foreach ($tabs as $id => $tupel) {?>
		<div class="multitabpane" name="tabpane<?=$gTabPaneNumber?>" id="tabpane<?=$gTabPaneNumber?>_<?=$i?>" <?=$selected==$id?"":"style=\"display:none;\""?>><?=($tupel[1])?></div>
		<?php ++$i; } // endforeach?>
	</div>
	</div>
	<?php
	++$gTabPaneNumber;
	return rob_ob_end();
}


?>
