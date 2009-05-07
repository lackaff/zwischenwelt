<?php
/*
TODO 
errorhandler schmeist exception und ruft on_error auf damit der job auf den fehler reagieren kann
locked erweitern: 0=free 1=running/locked 2=finished
errors/exceptions loggen in der joblog

== produktion ==
*gebäude
*rohstoffe
*forschung
*einheiten
*reparieren

== effekt ==
*zauber
*waldwachstum
*ruineneinsturz

== armee ==
*think
*kampf
*plünderung
*belagerung
*res abbau

== wartung ==
*stats
*optimize tables
*optimize terrain
*minimaps
*flush
*loglöschen
*/

// default log file if non specified
if(!defined("JOB_LOG_FILE"))define("JOB_LOG_FILE",BASEPATH."/tmp/job.log");

class Job {
	private $_id;
	private $_priority;
	private $_name;
	private $_time;
	private $_starttime;
	private $_endtime;
	private $_sqlentry;
	private $_locked;
	private $_error;
	
	protected $_payload;
	
	protected function __construct($id){
		$o = sqlgetobject("SELECT * FROM `job` WHERE `id`=".intval($id));
		if($o){
			$this->_id = $o->id;
			$this->_priority = $o->prio;
			$this->_name = $o->name;
			$this->_payload = empty($o->payload) ? array() : unserialize($o->payload);
			$this->_time = $o->time;
			$this->_locked = $o->locked;
		}
		$this->_error = array();
	}
	/**
	 * handle locking
	 * @return bool true if lock is aquired
	 */
	private function acquireLock(){
		if(empty($this->_id))return false;
		
		sql("UPDATE `job` SET `locked`=1 WHERE `id`=".intval($this->_id)." AND `locked`=0");
		return mysql_affected_rows() > 0;
	}
	
	/**
	 * log the given job output into the logfile
	 * @param $output
	 */
	private function logOutput($output,$dt){
		$dt = round($dt * 1000);
		$f = fopen(JOB_LOG_FILE, "a");
		if($f){
			$output = trim($output);
			if(empty($output)){
				fwrite($f, "===[ $this->_id : $this->_name : $this->_time : $dt ms ]===\n");
			} else {
				fwrite($f, "===[ $this->_id : $this->_name : $this->_time : $dt ms ]===\n$output\n");
			}
			fclose($f);
		}
	}
	
	public function error_handler($errno, $errstr, $errfile, $errline, $errcontext){
	    $line = "[$errfile:$errline] $errstr";
		switch ($errno) {
	    case E_USER_ERROR:
	        $this->_error[] = "Error: $line";
	        throw new Exception();
	        break;
	
	    case E_WARNING:
	    case E_USER_WARNING:
	        $this->_error[] = "Warning: $line";
	    	break;
	
	    case E_NOTICE:
	    case E_USER_NOTICE:
	        $this->_error[] = "Notice: $line";
	        break;
	
	    default:
	        $this->_error[] = "Unknown: [$errno] $line";
	        break;
	    }
	
	    /* Don't execute PHP internal error handler */
	    return true;		
	}
	
	/**
	 * calls the _run body of the job, dont use this directly
	 */
	private function run(){
		if($this->acquireLock()){
			// lock ok so run the job
			rob_ob_start();
			
			// overwrite error handler
			set_error_handler(array(&$this, "error_handler"));
			
			$this->_starttime = time();
			sql("UPDATE `job` SET `starttime`=".intval($this->_starttime)." WHERE `id`=".$this->_id);
			$t1 = microtime(true);
			
			try {
				$this->_run();
			} catch(Exception $e){
				$line = "Exception: [".$e->getFile().":".$e->getLine()."] ".$e->getMessage();
				$this->_error[] = $line;
				$this->_on_error();
			}
			
			$t2 = microtime(true);
			$this->_endtime = time();
			sql("UPDATE `job` SET `endtime`=".intval($this->_endtime).", `locked`=2 WHERE `id`=".$this->_id);
			$output = rob_ob_end(); 
			
			// restore error handler
			restore_error_handler();
			
			$this->logOutput($output,$t2-$t1);
			
			if(sizeof($this->_error) > 0){
				$errorsql = "`error` = '".mysql_real_escape_string(implode("|",$this->_error))."', ";
			} else {
				$errorsql = "";
			}
			
			// and add log entry
			sql("INSERT DELAYED INTO `joblog` SET
				`time` = ".intval($this->_time).",
				`name` = '".mysql_real_escape_string($this->_name)."',
				`payload` = '".mysql_real_escape_string(serialize($this->_payload))."',
				`starttime` = '".intval($this->_starttime)."',
				`endtime` = '".intval($this->_endtime)."',
				`jobid` = ".intval($this->_id).",
				`output` = '".mysql_real_escape_string($output)."',
				$errorsql
				`dt` = '".round($t2 * 1000 - $t1 * 1000)."'		
			");
		}
	}
	
	/**
	 * queues an identical job
	 * @param $time execute time
	 * @param $prio priority
	 */
	protected function requeue($time,$prio = null){
		if($prio === null)$prio = $this->_priority;
		
		self::queue($this->_name, $this->_payload, $time, $prio);
	}
	
	/**
	 * overwrite this with your own job code
	 */
	protected function _run(){}
	/**
	 * overwrite this with your own error handling code 
	 */
	protected function _on_error(){}
	
	// -----------------------------------------
	
	/**
	 * interface to queue a job
	 * @param $name name of the job
	 * @param $payload object to get as payload
	 * @param $time execute time
	 * @param $prio execute priority
	 */
	public static function queue($name, $payload, $time, $prio){
		$time = empty($time) ? time() : $time;
		$prio = empty($prio) ? 0 : $prio;
		
		$payload = serialize($payload);
		sql("INSERT INTO `job` (`name`,`payload`,`time`,`prio`) VALUES (
			'".mysql_real_escape_string($name)."',
			'".mysql_real_escape_string($payload)."',
			'".intval($time)."',
			'".intval($prio)."'
		)");
	}
	
	/**
	 * like queue but only queues if there is no job of the given name queued
	 * @param $name name of the job
	 * @param $payload object to get as payload
	 * @param $time execute time
	 * @param $prio priority
	 */
	public static function queueIfNonQueuedOrRunning($name, $payload, $time, $prio){
		if(!self::isQueuedOrRunningWithName($name)){
			self::queue($name, $payload, $time, $prio);
		}
	}
	
	/**
	 * gets the class name of a given job name
	 * @param $name
	 */
	private static function className($name){
		return "Job_".$name;
	}
	
	/**
	 * returns a list of jobs that are due to execute
	 * @param $limit max number of jobs
	 */
	private static function getJobs($limit){
		$jobs = sqlgettable("SELECT `id`,`time`,`prio`,`name` FROM `job` WHERE `locked`=0 AND `time`<".time()." AND `endtime`=0 ORDER BY `tries` ASC, `prio` DESC, `time` ASC LIMIT ".intval($limit));
		return $jobs;
	}
	
	/**
	 * call this if you want to execute every pending job
	 * this should only be used for debugging and development
	 */
	public static function requeueJobsToBeFinished(){
		$t = time();
		sql("UPDATE `job` SET `time`=`time`-$t WHERE `time`>$t locked`=0");
		echo "Jobs betroffen: ".mysql_affected_rows();
	}
	
	/**
	 * checks if there is a job with the given name pending to execute or executing
	 * @param $name jobname
	 * @return true if there is one
	 */
	public static function isQueuedOrRunningWithName($name){
		return sqlgetone("SELECT 1 FROM `job` WHERE `name`='".mysql_real_escape_string($name)."' AND `locked`!=2") == 1;
	}

	public static function runJob($id, $echo = false){
		$job = sqlgetobject("SELECT `id`,`time`,`prio`,`name` FROM `job` WHERE `locked`=0 AND `endtime`=0 AND `id`=".intval($id));
		
		if($job){
			// update try to reduce priority of broken jobs
			sql("UPDATE `job` SET `tries`=`tries`+1 WHERE `id`=".intval($job->id));
		
			$className = self::className($job->name);
			if(class_exists($className)){
				$j = new $className($job->id);
				$j->run();
				++$count;
			} else {
				if($echo)echo "ERROR: $className does not exist<br>\n";
			}
		} else {
			if($echo)echo "ERROR: there is no unlocked job with id $job->id\n";
		}
	}
	
	/**
	 * executes jobs that are due to execute
	 * you can call this as many times as you want
	 * @param $limit max number of jobs
	 * @param $echo true if this functoin should produce some debug output
	 * @return int number of executed jobs (jobs that fail due to locking issues will also count)
	 */
	public static function runJobs($limit, $echo = false){
		$jobs = self::getJobs($limit);
		$count = 0;
		foreach($jobs as $job){
			// TODO job gets loaded 2 times
			self::runJob($job->id, $echo);
		}
		return $count;
	}
}

class Job_Test extends Job {
	protected function _run(){
		//sleep(3);
		//throw new Exception("test1");
		$this->requeue(in_secs(time(),10));
	}
	
	protected function _on_error(){
		$this->requeue(in_secs(time(),60));
	}
}

require_once(BASEPATH."/jobs/job_maintainance.php");
require_once(BASEPATH."/jobs/job_user.php");
require_once(BASEPATH."/jobs/job_map.php");
require_once(BASEPATH."/jobs/job_monster.php");
require_once(BASEPATH."/jobs/job_global.php");
require_once(BASEPATH."/jobs/job_army.php");
require_once(BASEPATH."/jobs/job_building.php");

?>
