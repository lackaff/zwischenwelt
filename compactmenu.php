<html>
  <head>
    <title>"compact menu" demo by Gor_de_Mar</title>
    <script src="js/compactmenu.js"></script>
    <script src="js/compactmenusitemap.js.php?sid=<?=$_REQUEST["sid"]?>"></script>
    <link rel="stylesheet" href="css/compactmenuscale.css" type="text/css">
    <link rel="stylesheet" href="css/brownbig.css" type="text/css">
    <link rel="stylesheet" href="css/cleanbig.css" type="text/css">
    <link rel="stylesheet" href="css/darkbig.css" type="text/css">
    <link rel="stylesheet" href="css/zw6big.css" type="text/css">
    <link rel="stylesheet" href="css/whitebig.css" type="text/css">
    <style type="text/css">
    <!--
      body {
        margin:2px;
        padding:0px;
      }
    -->
    </style>
  </head>
  <body>
    <table cellpadding="0" cellspacing="0" border="0" class="compactmenu">
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
      compactmenusetpage(1);
    </script>
  </body>
</html>
