<?php 

include("lib.main.php");

if(isset($f_setpage))$page = intval($f_setpage);
else $page = 1;      

if(isset($f_style) && !empty($gMenuStyles[$f_style])){
  SetUserValue($gUser,"menustyle",$f_style);
  //echo "[".GetUserValue($gUser,"menustyle")."]";
  header("Location: ?setpage=$page&sid=".$f_sid);
  exit;
}

$css = $gMenuStyles[GetUserValue($gUser->id,"menustyle")];
if(empty($css))$css = $gMenuStyles["default"];


?>
<html>
  <head>
    <title>"compact menu" demo by Gor_de_Mar</title>
    <script src="js/compactmenu.js"></script>
    <script src="js/compactmenusitemap.js.php?sid=<?=$_REQUEST["sid"]?>"></script>
    <link rel="stylesheet" href="css/compactmenuscale.css" type="text/css">
    <link rel="stylesheet" href="<?=$css?>" type="text/css">
  </head>
  <body>
    <table cellpadding="0" cellspacing="0" border="0" class="compactmenu" id="compactmenutable">
      <tr class="t">
        <td class="l"></td>
        <td class="c" id="compactmenutop">&nbsp;</td>
        <td class="r"></td>
      </tr>
      <tr class="m">
        <td class="l"></td>
        <td class="c" id="compactmenupath">&nbsp;</td>
        <td class="r"></td>
      </tr>
      <tr class="b">
        <td class="l"></td>
        <td class="c" id="compactmenubottom">&nbsp;</td>
        <td class="r"></td>
      </tr>
    </table>
    <script>
      compactmenuparse(compactmenusitemap);
      compactmenusetpage(<?=$page?>);
    </script>
  </body>
</html>
