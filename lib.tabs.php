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
	$anzahl_tabs = count($tabs);
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
		pane.style.display = "inline";
	}
	//-->
	</SCRIPT>
	<div class="<?=$cssclass?>">
	<div class="tabs">
		<div class="tabheader">
			<ul>
				<?php $i = 0; foreach ($tabs as $id => $pair) {?>
				<li name="tabhead<?=$gTabPaneNumber?>" id="tabhead<?=$gTabPaneNumber?>_<?=$i?>" 
					class="<?=$selected==$id?"activetab":"inactivetab"?>" 
					onClick="TabPane<?=$gTabPaneNumber?>Activate(<?=$i?>)"><span class="tabhead"><?=($pair[0])?></span>
				</li>
				<?php ++$i; } // endforeach?>
			</ul>
			<div class="corner"><?=$corner?></div>
		</div>
		<?php $i = 0;  foreach ($tabs as $id => $pair) {?>
		<div class="tabpane" name="tabpane<?=$gTabPaneNumber?>" id="tabpane<?=$gTabPaneNumber?>_<?=$i?>" <?=$selected==$id?"":"style=\"display:none;\""?>><?=($pair[1])?></div>
		<?php ++$i; } // endforeach?>
	</div>
	</div>
	<?php
	++$gTabPaneNumber;
}

?>