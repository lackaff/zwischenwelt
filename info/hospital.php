<?php 

$gClassName = "cInfoHospital";
class cInfoHospital extends cInfoBuilding {
	
	function cancontroll ($user=false) { global $gUser; return $gUser->admin; }
	
	function mycommand () {
		foreach ($_REQUEST as $name=>$val) ${"f_".$name} = $val;
		global $gUser;
		global $gObject;
		global $gGlobal;
		
		if (!isset($f_do) || !$this->cancontroll($gUser->id)) return;

		switch ($f_do) {
			case "sqlbookmark": break; // handled below
			case "create":
				if(!isset($f_create))return;
				switch ($f_create){
					case "terrain":
						$o="";
						$o->name="new_terrain";
						$o->descr="edit me";
						sql("INSERT INTO `terraintype` SET ".obj2sql($o));
						echo "terrain created .. please edit and refresh cached types<br>";
					break;
					
					case "building":
						$o="";
						$o->name="new_building";
						$o->descr="edit me";
						$o->special=1;
						sql("INSERT INTO `buildingtype` SET ".obj2sql($o));
						echo "building created .. please edit and refresh cached types<br>";
					break;
					
					case "unit":
						$o="";
						$o->name="new_unit";
						$o->descr="edit me";
						$o->buildingtype=$gObject->id;
						sql("INSERT INTO `unittype` SET ".obj2sql($o));
						echo "unit created .. please edit and refresh cached types<br>";
					break;
					
					case "technology":
						$o="";
						$o->name="new_tech";
						$o->descr="edit me";
						$o->buildingtype=$gObject->id;
						sql("INSERT INTO `technologytype` SET ".obj2sql($o));
						echo "tech created .. please edit and refresh cached types<br>";
					break;
					
					case "technologygroup":
						$o="";
						$o->name="new_techgroup";
						$o->descr="edit me";
						$o->buildingtype=$gObject->id;
						sql("INSERT INTO `technologygroup` SET ".obj2sql($o));
						echo "techgroup created .. please edit and refresh cached types<br>";
					break;
					
					default:
					break;
				}
			break;
				
			case "switchupdate":
				if(!isset($gGlobal['liveupdate']) || $gGlobal['liveupdate']==0){
					sql("UPDATE `global` SET `value`='1' WHERE `name`='liveupdate'");
					sql("UPDATE `session` SET `lastuse`=0 WHERE `userid`<>".$gUser->id);
				}else{
					sql("UPDATE `global` SET `value`='0' WHERE `name`='liveupdate'");
					$gGlobal['liveupdate']=0;
				}
				require_once("../generate_types.php");
				require_once(kTypeCacheFile);
				Redirect(Query("?sid=?&x=?&y=?"));
			break;
			
			default:
			break;
		}
		
	}

	function mydisplay() {
		foreach ($_REQUEST as $name=>$val) ${"f_".$name} = $val;
		global $gUser;
		global $gObject;
		global $gItemType;
		global $gSpellType;
		global $gRes;
		global $gResTypeNames;
		global $gResTypeVars;
		global $gTechnologyType;
		global $gTechnologyGroup;
		global $gGlobal;
		
		if ($gUser->admin){ ?>
		<ul>
			<li><a href="http://zwischenwelt.milchkind.net/phpmyadmin/" target="_blank">phpMyAdmin</a></li>
			<li><a href="http://zwischenwelt.milchkind.net/munin/" target="_blank">munin</a></li>
			<li><a href="http://zwischenwelt.org/cgi-bin/awstats.pl" target="_blank">AWStats</a></li>
		</ul>
		
		<ul>
			<li><a href="../sqlerror.log" target="_blank">SQLerror.log</a></li>
			<li><a href="../script/viewerror.php" target="_blank">PHPerror.log</a></li>
			<li><a href="<?=Query("../listall.php?sid=?")?>">list all terrain/building/unit/technology types (usefull for searching something to edit)</a></li>
			<li><a href="../lastcron.html" target="_blank">lastcron</a></li>
			<li><a href="<?=Query("../cvsup.php?sid=?")?>">cvsup</a></li>
			<li><a href="<?=Query("adminglobal.php?sid=?")?>">Globale Einstellungen</a></li>
		</ul>
		
		<ul>
			<li><a href="<?=Query("?sid=?&x=?&y=?&building=hospital&id=$gObject->id&do=create&create=terrain")?>">Add a new Terrain</a></li>
			<li><a href="<?=Query("?sid=?&x=?&y=?&building=hospital&id=$gObject->id&do=create&create=building")?>">Add a new Building</a></li>
			<li><a href="<?=Query("?sid=?&x=?&y=?&building=hospital&id=$gObject->id&do=create&create=unit")?>">Add a new Unit</a></li>
			<li><a href="<?=Query("?sid=?&x=?&y=?&building=hospital&id=$gObject->id&do=create&create=technology")?>">Add a new Technology</a></li>
			<li><a href="<?=Query("?sid=?&x=?&y=?&building=hospital&id=$gObject->id&do=create&create=technologygroup")?>">Add a new Technologygroup</a></li>
		</ul>
		
		<ul>
			<li><a href="<?=Query("?sid=?&x=?&y=?&building=hospital&id=$gObject->id&do=switchupdate")?>">Switch liveupdatestatus* (currently: <?=($gGlobal['liveupdate']==1?"on":"off")?>)</a></li>
		</ul>
		<?php 
			if (isset($f_clear_profile)) sql("DELETE FROM `profile`");
			if (isset($f_clear_sqlerror)) sql("DELETE FROM `sqlerror`");
		?>
		<?php 
			// mysqldump :
			// --add-drop-table 
			// -Q  Quote table and column names
			// -e extended insert
			// --password[=name]
			// --user=name
			// mysqldump
			$nodump = array("newlog","message","stats","guild_msg");
			$optdump = array("terrain","building","triggerlog");
			$outfile = "hospitaldump.sql";
			$outfile2 = "hospitaldump.sql.zip";
			$prepath = ($_SERVER["REMOTE_ADDR"]=="127.0.0.1" && $gUser->name == "ghoulsblade")?"C:/mysql/bin/":"";
			
			if (isset($f_removemysqldump)) {
				unlink($outfile);
				unlink($outfile2);
			} else if (isset($f_mysqldump)) {
				$tablenames = sqlgetonetable("SHOW TABLES");
				foreach ($nodump as $tbl) {
					$key = array_search($tbl,$tablenames);
					if ($key) unset($tablenames[$key]);
				}
				if (!isset($f_optdump)) $f_optdump = array();
				foreach ($optdump as $tbl) if (!in_array($tbl,$f_optdump)) {
					$key = array_search($tbl,$tablenames);
					if ($key) unset($tablenames[$key]);
				}
				$tables = implode(" ",$tablenames);
				$command = $prepath."mysqldump -Qe --add-drop-table -u ".MYSQL_USER." --password=\"".MYSQL_PASS."\" ".MYSQL_DB." ".$tables." > ".$outfile;
				$out = shell_exec($command);
				var_dump($out); 
				$out = shell_exec("zip ".$outfile2." ".$outfile);
				var_dump($out); 
				echo "tables not dumped : ".implode(" ",$nodump)." ".implode(" ",array_diff($optdump,$f_optdump))."<br>";
				echo "tables dumped : ".implode(" ",$tablenames)."<br>";
				echo "<a href='$outfile2'>mysqldump</a>(".ceil(filesize($outfile2)/1024)." kB):";
			}
			
			if (file_exists($outfile) || file_exists($outfile2)) {
				?>
				<form method="post" action="<?=Query("?sid=?&x=?&y=?")?>">
					<input type="submit" name="removemysqldump" value="removemysqldump">
				</form>
				<?php
			}
			?>
			<form method="post" action="<?=Query("?sid=?&x=?&y=?")?>">
				<?php foreach ($optdump as $o) {?>
				<input type="checkbox" name="optdump[]" value="<?=$o?>" checked><?=$o?><br>
				<?php }?>
				<input type="submit" name="mysqldump" value="mysqldump">
			</form>
			<?php
		?>
		
		
		<h3>sqlbookmark</h3>
		<?php 
			if (isset($f_antislash) && $f_antislash != "'") $f_i_sql = stripslashes($f_i_sql);
			if (isset($f_do) && $f_do == "sqlbookmark" && isset($f_del) && isset($f_sure) && $f_sure == 1) 
				sql("DELETE FROM `sqlbookmark` WHERE `id` = ".intval($f_sqlbookmark)." LIMIT 1");
			if (isset($f_do) && $f_do == "sqlbookmark" && isset($f_new)) { INew("sqlbookmark"); $f_sqlbookmark = mysql_insert_id(); }
			$sqlbookmarks = sqlgettable("SELECT * FROM `sqlbookmark` ORDER BY `id`","id");
			$sqlqry = false;
			if (isset($f_do) && $f_do == "sqlbookmark" && isset($f_i_sql)) $sqlqry = $f_i_sql;
			if (isset($f_do) && $f_do == "sqlbookmark" && (isset($f_show) || isset($f_use))) $sqlqry = $sqlbookmarks[intval($f_sqlbookmark)]->sql;
			$newname = isset($f_i_name) ? $f_i_name : "neues sqlbookmark";
			if (isset($f_show) || isset($f_use)) $newname = $sqlbookmarks[$f_sqlbookmark]->name;
			if (isset($f_usedirect)) $f_sqlbookmark = 0;
		?>
		<form method="post" action="<?=Query("?sid=?&x=?&y=?")?>">
			<input type="hidden" name="antislash" value="'">
			<input type="hidden" name="do" value="sqlbookmark">
			<input type="hidden" name="building" value="hospital">
			<input type="hidden" name="id" value="<?=$gObject->id?>">
			<select name="sqlbookmark"><option value="0">-</option><?=PrintObjOptions($sqlbookmarks,"id","name",isset($f_sqlbookmark)?$f_sqlbookmark:0)?></select>
			<input type="submit" name="use" value="ausführen"> |
			<input type="submit" name="show" value="befehl anzeigen"> |
			<input type="submit" name="del" value="löschen">
			<input type="checkbox" name="sure" value="1">sicher? <br>
			<textarea name="i_sql" cols=60 rows=3><?=htmlspecialchars($sqlqry)?></textarea><br>
			<input type="submit" name="usedirect" value="ausführen">
			Name:<input type="text" name="i_name" value="<?=$newname?>">
			<input type="submit" name="new" value="speichern">
		</form>
		<?php 
			$execsql = false;
			if (isset($f_do) && $f_do == "sqlbookmark" && isset($f_usedirect)) $execsql = $f_i_sql;
			if (isset($f_do) && $f_do == "sqlbookmark" && isset($f_use)) $execsql = $sqlbookmarks[intval($f_sqlbookmark)]->sql;
			if ($execsql) do { // fake loop, so we can use break;
				// a little bit of mild security, but this is not a complete protection ! just to avoid mistakes
				if (eregi("DELETE.+FROM",$execsql)) { echo "no DELETE querries<br>"; break; }
				if (eregi("UPDATE.+SET",$execsql)) { echo "no UPDATE querries<br>"; break; }
				if (eregi("DROP.+TABLE",$execsql)) { echo "no DROP querries<br>"; break; }
				if (eregi("TRUNCATE.+TABLE",$execsql)) { echo "no TRUNCATE querries<br>"; break; }
				if (eregi("ALTER.+TABLE",$execsql)) { echo "no ALTER querries<br>"; break; }
				if (eregi("SELECT.+INTO",$execsql)) { echo "no SELECT INTO querries<br>"; break; }
				if (!eregi("SELECT.+FROM[ `\\t]+([a-z0-9_]+)",$execsql,$r)) { echo "only SELECT FROM querries<br>"; break; }
				$table = $r[1];
				$rows = sqlgettable($execsql);
				echo "TABLE : $table , ".count($rows)." ROWS :<br>";
				$rownum=0;
				?>
				<table border=1 cellspacing=0>
				<?php foreach ($rows as $o) {?>
				<?php
				$arr = obj2arr($o);
				$out = array();
				global $gArmyType,$gBuildingType,$gUnitType,$gItemType;
				$hellhole_ai_name = array(0=>"creep",1=>"raid",2=>"blob"); // TODO : unhardcode
				foreach ($arr as $k=>$v) {
					if ($k == "c") continue; // after id, for counting
					if ($k == "x" && isset($arr["y"])) { $out["x,y"] = pos2txt($arr["x"],$arr["y"]); continue; }
					if ($k == "y" && isset($arr["x"])) continue;
					if ($k == "user" && $v) $v = nick($v)."[$v]";
					if ($k == "ai_type" && isset($hellhole_ai_name[$v])) $v = $hellhole_ai_name[$v]."[$v]";
					if ($k == "flags" || $k == "movable_flag" || $k == "movable_flag") {
						$flagbits = array();
						for ($i=0;$i<32;++$i) if (intval($v) & (1<<$i)) $flagbits[] = $i;
						$v = implode(",",$flagbits);
					}
					if ($k == "code" && $table == "phperror") $v = nl2br(htmlspecialchars($v));
					if ($v && $k == "id") {
						if ($table == "hellhole")	$v = $v.AdminBtn("hellhole","adminhellhole.php?sid=?&id=$v");
						if ($table == "army")		$v = $v.AdminBtn("army","adminarmy.php?sid=?&id=$v");
						if ($table == "user")		$v = $v.AdminBtn("user","adminuser.php?sid=?&id=$v");
						if ($table == "unit")		$v = $v.AdminBtn("unit","adminunittype.php?sid=?&id=$v");
						if ($table == "building")	$v = $v.AdminBtn("building","adminbuilding.php?sid=?&id=$v");
					}
					if ($v && $table == "army" && $k == "type")		$v = $gArmyType[$v]->name;
					if ($v && $table == "building" && $k == "type")	$v = "<img alt=$v title=$v src='".g($gBuildingType[$v]->gfx)."'>".
																			AdminBtn("type","adminbuildingtype.php?sid=?&id=$v");
					if ($v && $table == "item" && $k == "type")	$v = "<img alt=$v title=$v src='".g($gItemType[$v]->gfx)."'>".
																			AdminBtn("type","adminitemtype.php?sid=?&id=$v");
					if ($v && (	($table == "unit" && $k == "type") || 
								($table == "hellhole" && $k == "type") ||
								($table == "hellhole" && $k == "type2") ))
							$v = "<img alt=$v title=$v src='".g($gUnitType[$v]->gfx)."'>".
								AdminBtn("type","adminunittype.php?sid=?&id=$v");
					if ($v && in_array($k,explode(",","nextactiontime,spawntime,lastupgrade"))) $v = date("d.m. H:i:s",intval($v));
					if ($v && in_array($k,explode(",","idle,spawndelay"))) $v = Duration2Text(intval($v));
					if ($v && in_array($k,explode(",","frags,lumber,stone,food,metal,runes,amount"))) $v = ktrenner(intval($v));
					if ($v && in_array($k,explode(",","army,transport,attacker,defender,follow"))) {
						$army = sqlgetobject("SELECT * FROM `army` WHERE `id` = ".intval($v));
						$v = $army ? opos2txt($army) : "missing_$v";
					}
					if ($v && in_array($k,array("building"))) {
						$building = sqlgetobject("SELECT * FROM `building` WHERE `id` = ".intval($v));
						$v = $building ? opos2txt($building) : "missing_$v";
					}
					if ($k == "hellhole" && $v) {
						$hellhole = sqlgetobject("SELECT * FROM `hellhole` WHERE `id` = ".intval($v));
						$v = $hellhole ? opos2txt($hellhole) : ("missing_".$v); 
					}
					$out[$k] = $v;
					if ($k == "id" && isset($arr["c"])) $out["c"] = $arr["c"]; // for counting
					// prime unit type and count
					if ($v && $k == "id" && $table == "army") {
						$id = $arr["id"];
						$units = cUnit::GetUnits($id);
						$unittype = cUnit::GetUnitsMaxType($units);
						$amount = cUnit::GetUnitsSum(cUnit::FilterUnitsType($units,$unittype));
						$out["units"] = ceil($amount)."<img alt=$unittype title=$unittype src='".g($gUnitType[$unittype]->gfx)."'>".
								AdminBtn("type","adminunittype.php?sid=?&id=$unittype").
								AdminBtn("units","adminunit.php?sid=?&containerid=$id&containertype=".kUnitContainer_Army);
						$units = cUnit::GetUnits($id,kUnitContainer_Transport);
						$unittype = cUnit::GetUnitsMaxType($units);
						$amount = cUnit::GetUnitsSum(cUnit::FilterUnitsType($units,$unittype));
						$out["transport"] = (($amount==0) ? "" : ( ceil($amount)."<img alt=$unittype title=$unittype src='".g($gUnitType[$unittype]->gfx)."'>".
								AdminBtn("type","adminunittype.php?sid=?&id=$unittype") )).
								AdminBtn("units","adminunit.php?sid=?&containerid=$id&containertype=".kUnitContainer_Transport);
					}
					if ($v && $k == "id" && $table == "building") {
						$id = $arr["id"];
						$units = cUnit::GetUnits($id,kUnitContainer_Building);
						$unittype = cUnit::GetUnitsMaxType($units);
						$amount = cUnit::GetUnitsSum(cUnit::FilterUnitsType($units,$unittype));
						$out["units"] = (($amount==0) ? "" : ( ceil($amount)."<img alt=$unittype title=$unittype src='".g($gUnitType[$unittype]->gfx)."'>".
								AdminBtn("type","adminunittype.php?sid=?&id=$unittype") )).
								AdminBtn("units","adminunit.php?sid=?&containerid=$id&containertype=".kUnitContainer_Building);
					}
					// buildingparam (schild-text,portal-tax)
					if ($v && $k == "id" && $table == "building") {
						$out["bparam"] = nl2br(htmlspecialchars(sqlgetone("SELECT `value` FROM `buildingparam` WHERE `building` = ".intval($arr["id"]))));
					}
				}
				?>
				<?php if (($rownum%20) == 0) {?><tr><th><?=implode("</th><th>",array_keys($out))?></th></tr><?php }?>
				<tr><?php foreach ($out as $v) {?><td nowrap align="right"><?=$v?></td><?php }?></tr>
				<?php ++$rownum;} // endforeach?>
				</table>
				<?php
			} while (0) ;
		?>
		
		
		
		<h3>Profiling</h3>
		<table border=1><tr>
		<th>page</th><th>hits</th><th>time</th><th>max dt</th><th>avg dt</td><th>max sql</th><th>avg sql</th><th>max mem</th><th>avg mem</th></tr>
		<?php
		$t = sqlgettable("SELECT * , `time` / `hits` AS `avg` , `sql` / `hits` AS `sqlavg`, `mem` / `hits` AS `memavg` FROM `profile` ORDER BY `time` DESC");
		foreach($t as $p){ ?>
		<tr><td><?=$p->page?></td>
		<td align="right"><?=$p->hits?></td>
		<td align="right"><?=sprintf("%0.2f",$p->time)?></td>
		<td align="right"><?=sprintf("%0.2f",$p->max)?></td>
		<td align="right"><?=sprintf("%0.2f",$p->avg)?></td>
		<td align="right"><?=$p->sqlmax?></td>
		<td align="right"><?=sprintf("%0.2f",$p->sqlavg)?></td>
		<td align="right"><?=$p->memmax?></td>
		<td align="right"><?=sprintf("%0.2f",$p->memavg)?></td></tr>
		<? } ?>
		</table>
		<h3>SQL Fehler</h3>
		<table border=1>
		<tr>
			<th>time</td>
			<th>self</th>
			<th width="300">sqlquery<img src="<?=g("1px.gif")?>" width="300" height="1" alt="."></th>
			<th width="300">error<img src="<?=g("1px.gif")?>" width="300" height="1" alt="."></th>
			<th>query</th>
			<th>stacktrace</th>
		</tr><?php $t = sqlgettable("SELECT * FROM `sqlerror` ORDER BY `time` DESC");foreach($t as $p){ ?><tr>
			<td><?=date("d.m H:i",$p->time)?></td>
			<td><?=$p->self?></td>	<td><?=$p->sqlquery?></td>	<td><?=$p->error?></td></td>
			<td><?=ereg_replace("sid=[a-z0-9A-Z]+","",$p->query)?></td><td><?=nl2br($p->stacktrace)?></tr><? } ?></table>
		
		<form method="post" action="<?=Query("?sid=?&x=?&y=?")?>">
			<input type="submit" name="clear_profile" value="clear_profile">
		</form>
		<form method="post" action="<?=Query("?sid=?&x=?&y=?")?>">
			<input type="submit" name="clear_sqlerror" value="clear_sqlerror">
		</form>
		
		<?=kPathSwitchTesting?><br>
		<?=kZWTestMode2?"true":"false"?><br>
		<?=kZWTestMode2?><br>
		
		* ) beendet alle Sessions und macht nur noch adminlogin möglich<br>
		<?php } 
	}
}?>