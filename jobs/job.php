<?php
/*
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
	
	protected $_payload;
	
	protected function __construct($id){
		$o = sqlgetobject("SELECT * FROM `job` WHERE `id`=".intval($id));
		if($o){
			$this->_id = $o->id;
			$this->_priority = $o->prio;
			$this->_name = $o->name;
			$this->_payload = empty($o->payload) ? array() : unserialize($o->payload);
			$this->_time = $o->time;
		}
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
	
	/**
	 * calls the _run body of the job, dont use this directly
	 */
	private function run(){
		if($this->acquireLock()){
			// lock ok so run the job
			rob_ob_start();
			$this->_starttime = time();
			$t1 = microtime(true);
			
			$this->_run();
			
			$t2 = microtime(true);
			$this->_endtime = time();
			$output = rob_ob_end(); 
			
			$this->logOutput($output,$t2-$t1);
			
			// update job
			sql("UPDATE `job` SET 
				`starttime`=".intval($this->_starttime).",
				`endtime`=".intval($this->_endtime)."
				WHERE `id`=".intval($this->_id));
			
			// and add log entry
			sql("INSERT DELAYED INTO `joblog` (`time`,`name`,`payload`,`starttime`,`endtime`,`jobid`,`dt`) VALUES (
				".intval($this->_time).",
				'".mysql_real_escape_string($this->_name)."',
				'".mysql_real_escape_string(serialize($this->_payload))."',
				'".intval($this->_starttime)."',
				'".intval($this->_endtime)."',
				".intval($this->_id).",
				'".round($t2 * 1000 - $t1 * 1000)."'				
			)");
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
	public static function queueIfNonQueued($name, $payload, $time, $prio){
		if(!self::isQueuedWithName($name)){
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
		$jobs = sqlgettable("SELECT `id`,`time`,`prio`,`name` FROM `job` WHERE `locked`=0 AND `time`<".time()." ORDER BY `tries` ASC, `prio` DESC, `time` ASC LIMIT ".intval($limit));
		return $jobs;
	}
	
	/**
	 * call this if you want to execute every pending job
	 * this should only be used for debugging and development
	 */
	public static function requeueJobsToBeFinished(){
		sql("UPDATE `job` SET `time`=`time`-".time()." WHERE `locked`=0 AND `endtime`=0 AND `starttime`=0");
		echo "Jobs betroffen: ".mysql_affected_rows();
	}
	
	/**
	 * checks if there is a job with the given name pending to execute
	 * @param $name jobname
	 * @return true if there is one
	 */
	public static function isQueuedWithName($name){
		return sqlgetone("SELECT 1 FROM `job` WHERE `name`='".mysql_real_escape_string($name)."' AND `endtime`=0 AND `starttime`=0") == 1;
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
			if($echo)echo "next job is $job->id with name $job->name<br>\n";
			
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
		}
		return $count;
	}
}

include_once("job_maintainance.php");
include_once("job_user.php");
include_once("job_map.php");
include_once("job_monster.php");
include_once("job_global.php");

?>
