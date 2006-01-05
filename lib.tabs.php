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
		<?php if ($jschangecallback) {?> <?=$jschangecallback?>(tabnum); <?php }?>
		var i,head,pane;
		//var heads = document.getElementsByName("tabhead<?=$gTabPaneNumber?>");
		//var panes = document.getElementsByName("tabpane<?=$gTabPaneNumber?>");
		//var heads = document.getElementsByName("tabhead<?=$gTabPaneNumber?>");
		//var panes = document.getElementsByName("tabpane<?=$gTabPaneNumber?>");
		//var gumbas = document.getElementsByName("gumba");
		//var gumba = gumbas[0];
		//alert("len="+gumbas.length+",gumbas[0] = "+gumba);
		//for(i in heads)alert(i+" = "+);
		//alert(tabnum+" / "+heads+" / "+heads[tabnum]);
		for (i=0;i<<?=$anzahl_tabs?>;++i) {
			head = document.getElementById("tabhead<?=$gTabPaneNumber?>_"+i);
			pane = document.getElementById("tabpane<?=$gTabPaneNumber?>_"+i);
			if (head.className == "activetab") {
				if (i == tabnum) return; // nothing to activate
				head.className = "inactivetab";
				pane.style.display = "none";
			}
		}
		head = document.getElementById("tabhead<?=$gTabPaneNumber?>_"+tabnum);
		pane = document.getElementById("tabpane<?=$gTabPaneNumber?>_"+tabnum);
		head.className = "activetab";
		pane.style.display = "inline"; // TODO : is this the evil tabs bug ??
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
					class="<?=$selected==$id?"activetab":"inactivetab"?>" 
					<?=$hasurl?("onClick=\"location.href='".$tupel[2]."'\""):("onClick=\"TabPane".$gTabPaneNumber."Activate(".$i.")\"")?> 
					><span class="tabhead"><?=($tupel[0])?></span></li>
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
function GenerateTabsMultiRow ($cssclass,$tabs,$max_per_row,$selected=0) {
	global $gTabPaneNumber;
	$anzahl_tabs = count($tabs);
	rob_ob_start();
	?>
	<SCRIPT LANGUAGE="JavaScript" type="text/javascript"><!--
	function MultiTabPane<?=$gTabPaneNumber?>Activate (tabnum) {
		<?php if ($jschangecallback) {?> <?=$jschangecallback?>(tabnum); <?php }?>
		var i,head,pane;
		for (i=0;i<<?=$anzahl_tabs?>;++i) {
			head = document.getElementById("tabhead<?=$gTabPaneNumber?>_"+i);
			pane = document.getElementById("tabpane<?=$gTabPaneNumber?>_"+i);
			if (head.className == "activetab") {
				if (i == tabnum) return; // nothing to activate
				head.className = "inactivetab";
				pane.style.display = "none";
			}
		}
		head = document.getElementById("tabhead<?=$gTabPaneNumber?>_"+tabnum);
		pane = document.getElementById("tabpane<?=$gTabPaneNumber?>_"+tabnum);
		head.className = "activetab";
		pane.style.display = "block";
	}
	//-->
	</SCRIPT>
	<div class="<?=$cssclass?>">
	<div class="multitabs">
		<div class="tabheader">
		<table>
		<tr>
			<?php $i = 0; foreach ($tabs as $id => $tupel) {?>
				<th name="tabhead<?=$gTabPaneNumber?>" id="tabhead<?=$gTabPaneNumber?>_<?=$i?>"
					class="<?=$selected==$id?"activetab":"inactivetab"?>" 
					onClick="MultiTabPane<?=$gTabPaneNumber?>Activate(<?=$i?>)"><?=$tupel[0]?></th>
				<?php if ($i<$anzahl_tabs-1 && ($i%$max_per_row) == $max_per_row-1) { /*last one in row*/?>
					</tr><tr>
				<?php } // endif?>
			<?php ++$i; } // endforeach?>
		</tr>
		</table>
		</div>
		<?php $i = 0;  foreach ($tabs as $id => $tupel) {?>
		<div class="tabpane" name="tabpane<?=$gTabPaneNumber?>" id="tabpane<?=$gTabPaneNumber?>_<?=$i?>" <?=$selected==$id?"":"style=\"display:none;\""?>><?=($tupel[1])?></div>
		<?php ++$i; } // endforeach?>
	</div>
	</div>
	<?php
	++$gTabPaneNumber;
	return rob_ob_end();
}


?>
