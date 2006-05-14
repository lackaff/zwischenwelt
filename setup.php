<?php

require("lib.setup.php");

CheckStart();

CheckRequired("FileExists","Configfile 'defines.mysql.php' exists",array("name"=>"defines.mysql.php"));
include("defines.mysql.php");
CheckRequired("FileExists","BASEPATH '".BASEPATH."' exists",array("name"=>BASEPATH));
CheckRequired("FileExists","with an tmp directory",array("name"=>BASEPATH."/tmp"));
CheckRequired("Writeable","that is writeable",array("name"=>BASEPATH."/tmp"));
CheckRequired("EregMatch","BASEPATH with trailing /",array("subject"=>BASEPATH,"pattern"=>'^.+(/|\\\\)$'));
CheckRequired("EregNotMatch","BASEPATH is not an url",array("subject"=>BASEPATH,"pattern"=>CHECK_PATTERN_HTTP));
CheckRequired("EregMatch","kGfxServerPath is an url",array("subject"=>kGfxServerPath,"pattern"=>CHECK_PATTERN_HTTP));
CheckRequired("EregMatch","kStyleServerPath is an url",array("subject"=>kStyleServerPath,"pattern"=>CHECK_PATTERN_HTTP));
CheckRequired("EregMatch","BASEURL is an url",array("subject"=>BASEURL,"pattern"=>'^http://.+$'));
CheckOptional("FileExists","sql error logfile exists",array("name"=>BASEPATH."/sqlerror.log"));
CheckOptional("Writeable","and is writeable",array("name"=>BASEPATH."/sqlerror.log"));
CheckRequired("MysqlAccess","mysql access",array("host"=>MYSQL_HOST,"user"=>MYSQL_USER,"pass"=>MYSQL_PASS));
CheckRequired("MysqlExistsDB","mysql db '".MYSQL_DB."' exists",array("host"=>MYSQL_HOST,"user"=>MYSQL_USER,"pass"=>MYSQL_PASS,"db"=>MYSQL_DB));
CheckRequired("MysqlValidQuery","mysql table 'user' exists",array("host"=>MYSQL_HOST,"user"=>MYSQL_USER,"pass"=>MYSQL_PASS,"db"=>MYSQL_DB,"query"=>"SELECT * FROM `user` LIMIT 1"));
CheckRequired("FunctionExists","function existance check for testing purpose",array("name"=>"system"));
CheckOptional("CmdExists","gnuplot",array("name"=>"gnuplot --version"));
CheckOptional("CmdExists","imagemagick",array("name"=>"convert -version"));

CheckStop();

?>