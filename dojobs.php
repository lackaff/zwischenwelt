<?php
set_time_limit(0);
error_reporting(E_ALL);

require_once("lib.main.php");

// run the pending jobs
Job::runJobs(100, true);

?>
