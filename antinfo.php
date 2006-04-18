<?php
require_once("lib.main.php");
echo intval(sqlgetone("SELECT COUNT(*) FROM hellhole WHERE ai_type = 3"));
?>