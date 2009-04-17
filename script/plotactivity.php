<?php
define("CONTENT_TYPE","image/png");

include_once("../lib.main.php");
if($f_mode == "plot"){
		if(!file_exists("tmp")){
				mkdir("tmp");
				chmod("tmp",0777);
		}
		
		$q = "title=".$_REQUEST["title"]."&ids=".$_REQUEST["ids"];
		$md5 = md5($q);

		if(file_exists("tmp/activity-$md5.png")){
			header("Location: tmp/activity-$md5.png");
			exit();
		}

		// see CONTENT_TYPE header('Content-type: image/png');

		$tmp = "tmp/activity-".$md5;

		$ids = explode(",",$f_ids);
		$number = 0;
		foreach($ids as $id){
				if(!is_numeric($id))$id = sqlgetone("SELECT `id` FROM `user` WHERE `name`='".addslashes($id)."'");

				$dat = "#time number\n";
				
				$f = fopen($tmp."-$number.dat","w");
				fputs($f,$dat);

				$t = sqlgettable("SELECT `time` FROM `calllog` WHERE `user`=".intval($id));
				foreach($t as $x)fputs($f,$x->time." ".$number."\n");

				fclose($f);
				++$number;
		}

		$plot = "
		set terminal postscript eps noenhanced color solid defaultplex \"Verdana\" 16
		set output \"| convert - $tmp.png\"
		set grid
		set size 1,0.7
		set pointsize 2.0
		set xdata time
		set timefmt \"%s\"\n";
		
		$ps = array();
		$number = 0;
		foreach($ids as $id){
				$name = sqlgetone("SELECT `name` FROM `user` WHERE `id`=".intval($id));
				$ps[] = "\"$tmp-$number.dat\" using 1:2 title '$name' with points";
				++$number;
		}
		$plot .= "plot ".implode(", ",$ps);

		$f = fopen($tmp.".plot","w");
		fputs($f,$plot);
		fclose($f);
		$plot = null;

		//$s = 'echo \'set terminal postscript eps noenhanced color solid defaultplex "Verdana" 36\'"\n"\'set output "| convert - '.$tmp.'"\'"\nplot sin(x)" | gnuplot';
		$s = "gnuplot < $tmp.plot";
		exec($s);
		passthru("rm $tmp*.dat && rm $tmp*.plot && cat $tmp.png");
} else {
		?>
		<form method=post action=?>
				User ID Liste: <input type=text name=ids value="<?=$f_ids?>" size=64>
				<input type=submit value=anzeigen>
				<?php if(!empty($f_ids)){ ?>
				<hr>
				<img src="?mode=plot&ids=<?=$f_ids?>&title=Activity">
				<hr>
				<?php } ?>
		</form>
		<?php
}
?>
