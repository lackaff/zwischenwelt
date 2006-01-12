<?php

// cInfoBuilding see below...

class cInfoBase {
	var $infobase_cmdout = false;
	
	// every object class gets one dummy instance for executing commands, those only use display for the commandout
	var $infobase_nodisplay = false; 
	
	// initialize member vars from sqlgetobject or something like that
	function cInfoBase ($o=null) {
		if ($o) {
			//echo "instance of ".get_class($this)." created<br>";
			$o = get_object_vars($o);
			foreach($o as $name=>$value) $this->$name = $value;
		} else $this->infobase_nodisplay = true;
	}
	
	// execute form posts in mycommand(), output buffered to display()
	function command () {
		//rob_ob_start();
		$this->mycommand();
		$this->classcommand();
		//$this->infobase_cmdout = trim(rob_ob_end());
	}
	
	
	
	// execute drawing code in display() after displaying the buffered command() output
	function generate_tabs () {
		if ($this->infobase_nodisplay) return;
		global $gObject; $gObject = $this; // backwards compatibility, better user $this
		$this->classgenerate_tabs(); // for building base class (unit prod, tech)
		$this->mygenerate_tabs();
	} 
	
	function classgenerate_tabs () {}
	function mygenerate_tabs () { } // override-me
	
	function display () {
		global $gObject; $gObject = $this; // backwards compatibility, better user $this
		// set into a nice papyrus info in display
		//echo get_class($this);vardump2($this);
		if ($this->infobase_cmdout) {
			ImgBorderStart("s1","jpg","#ffffee","",32,33);
			echo $this->infobase_cmdout;
			ImgBorderEnd("s1","jpg","#ffffee",32,33);
			echo "<hr>";
		}
		if ($this->infobase_nodisplay) return;
		//echo "displaying ".get_class($this)."<hr>";
		//vardump2($this);echo "<hr>";
		$this->display_header();
		$this->mydisplay();
		$this->display_footer();
	}
	
	function display_header() {} // above mydisplay
	function display_footer() {} // below mydisplay
	
	function cancontroll ($user) { return true; } // check if commands may be executed
	function classcommand () {} // override me in building-base-class for things like gate and production
	function mycommand () {} // execute commands
	function mydisplay () {} // draw info
}

?>