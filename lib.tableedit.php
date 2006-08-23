<?php

class cTableEdit {
	function Show($base=""){echo "[Show($base):overload me]";}
	function HandleInput($base=""){echo "[HandleInput($base):overload me]";}
}

//formular
class cTableEditForm extends cTableEdit {
	var $child,$queryurl,$title;
	var $deletetable,$deletekey,$deletekeyvalue,$deletejumptourl;
	var $deletebutton;
	/**
	*set the delete... paremeters to get a simple table row delete button
	*/
	function cTableEditForm($queryurl,$title,$te,$deletetable="",$deletekey="",$deletekeyvalue="",$deletejumptourl=""){
		$this->child = $te;
		$this->queryurl = $queryurl;
		$this->title = $title;
		$this->deletetable = $deletetable;
		$this->deletekey = $deletekey;
		$this->deletekeyvalue = $deletekeyvalue;
		$this->deletejumptourl = $deletejumptourl;
		$this->deletebutton = !empty($deletetable) && !empty($deletekey) && !empty($deletekeyvalue) && !empty($deletejumptourl);
	}
	function Show($base=""){
		?>
		<form method=post action="<?=query($this->queryurl)?>">
			<table border=1>
				<tr><th align=center><?=$this->title?></td></tr>
				<tr><td><?=$this->child->Show($base."/form")?></td></tr>
				<tr><td align=center>
					<?php if($this->deletebutton){ ?>
					sicher? <input type=checkbox name="<?=$base?>/delete_ok" value="ok">
					<input type=submit name="<?=$base?>/delete" value="L&ouml;schen">
					| <?php } ?>
					<input type=submit name="<?=$base?>/submit" value="&Uuml;bernehmen">
				</td></tr>
			</table>
		<form>
		<?php
	}
	function HandleInput($base=""){
		if(isset($_REQUEST["$base/delete"])){
			if($this->deletebutton && $_REQUEST["$base/delete_ok"] == "ok"){
				//handle delete of one row
				sql("DELETE FROM `".addslashes($this->deletetable)."` WHERE `".addslashes($this->deletekey)."`='".addslashes($this->deletekeyvalue)."'");
				Redirect($this->deletejumptourl);
			}
		} else if(isset($_REQUEST["$base/submit"])){
			//vardump($_REQUEST);
			$this->child->HandleInput("$base/form");
		}
	}
}

//links ein edit und rechts ein edit
class cTableEditLeftRight extends cTableEdit {
	var $leftchild,$rightchild;
	function cTableEditLeftRight($leftte,$rightte){
		$this->leftchild = $leftte;
		$this->rightchild = $rightte;
	}
	function Show($base=""){
		?>
		<table>
			<tr>
				<td valign=top><?=$this->leftchild->Show($base."/left")?></td>
				<td valign=top><?=$this->rightchild->Show($base."/right")?></td>
			</tr>
		</table>
		<?php
	}
	function HandleInput($base=""){
		$this->leftchild->HandleInput($base."/left");
		$this->rightchild->HandleInput($base."/right");
	}
}

//eine reihe von edits angeordnet als table row
class cTableEditRows extends cTableEdit {
	var $rowchilds;
	function cTableEditRows($rowchilds){
		$this->rowchilds = $rowchilds;
	}
	function Show($base=""){
		?>
		<table>
		<?php foreach($this->rowchilds as $k=>$x){ ?>
			<tr><td><?=$x->Show($base."/$k")?></td></tr>
		<?php } ?>
		</table>
		<?php
	}
	function HandleInput($base=""){
		foreach($this->rowchilds as $k=>$x){
			$x->HandleInput($base."/$k");
		}
	}
}

//eine reihe von edits angeordnet als table col
class cTableEditCols extends cTableEdit {
	var $rowchilds;
	function cTableEditCols($rowchilds){
		$this->rowchilds = $rowchilds;
	}
	function Show($base=""){
		?>
		<table><tr>
		<?php foreach($this->rowchilds as $k=>$x){ ?>
			<td valign=top><?=$x->Show($base."/$k")?></td>
		<?php } ?>
		</tr></table>
		<?php
	}
	function HandleInput($base=""){
		foreach($this->rowchilds as $k=>$x){
			$x->HandleInput($base."/$k");
		}
	}
}

class cTableEditShowText extends cTableEdit {
	var $text;
	function cTableEditShowText($text){$this->text=$text;}
	function Show($base=""){echo $this->text;}
	function HandleInput($base=""){}
}

//edit one table field with rowid id
class cTableEditField extends cTableEdit {
	var $table,$idfield,$idval,$name,$field;
	function cTableEditField($table,$idfield,$idval,$name,$field){
		$this->table = addslashes($table);
		$this->idfield = addslashes($idfield);
		$this->idval = addslashes($idval);
		$this->name = $name;
		$this->field = addslashes($field);
	}
	function HandleInput($base=""){
		$value = addslashes($_REQUEST["$base/$this->field"]);
		sql("UPDATE `$this->table` SET `$this->field`='$value' WHERE `$this->idfield`='$this->idval'");
	}
}

//edit one table field with rowid id
class cTableEditTextField extends cTableEditField {
	function cTableEditTextField($table,$idfield,$idval,$name,$field){
		parent::cTableEditField($table,$idfield,$idval,$name,$field);
	}
	function Show($base=""){
		$value = sqlgetone("SELECT `$this->field` FROM `$this->table` WHERE `$this->idfield`='$this->idval'");
		?>
		<table width=100% border=0><tr>
			<th><?=$this->name?></th>
			<td align=right><input type="text" name="<?=$base."/".$this->field?>" size=16 value="<?=htmlspecialchars($value)?>"></td>
		</tr></table>
		<?php
	}
}

//edit one table field with rowid id, data layout is time
class cTableEditTimeField extends cTableEditField {
	function cTableEditTimeField($table,$idfield,$idval,$name,$field){
		parent::cTableEditField($table,$idfield,$idval,$name,$field);
	}
	function Show($base=""){
		$value = sqlgetone("SELECT `$this->field` FROM `$this->table` WHERE `$this->idfield`='$this->idval'");
		$d = floor($value / (60*60*24));
		$value %= (60*60*24);
		$h = floor($value / (60*60));
		$value %= (60*60);
		$m = floor($value / 60);
		$value %= 60;
		$s = $value;
		?>
		<table width=100% border=0><tr>
			<th><?=$this->name?></th>
			<td align=right>
				<table border=0><tr>
					<td><input type="text" name="<?=$base."/".$this->field."/d"?>" size=2 value="<?=$d?>">d</td>
					<td><input type="text" name="<?=$base."/".$this->field."/h"?>" size=2 value="<?=$h?>">h</td>
					<td><input type="text" name="<?=$base."/".$this->field."/m"?>" size=2 value="<?=$m?>">m</td>
					<td><input type="text" name="<?=$base."/".$this->field."/s"?>" size=2 value="<?=$s?>">s</td>
				</tr></table>
				</td>
		</tr></table>
		<?php
	}
	function HandleInput($base=""){
		$d = intval($_REQUEST["$base/$this->field/d"]);
		$h = intval($_REQUEST["$base/$this->field/h"]);
		$m = intval($_REQUEST["$base/$this->field/m"]);
		$s = intval($_REQUEST["$base/$this->field/s"]);
		$_REQUEST["$base/$this->field"] = $s + 60*$m + 60*60*$h + 60*60*24*$d;
		parent::HandleInput($base);
	}
}

//edit one table field with rowid id
class cTableEditTextArea extends cTableEditField {
	var $cols,$rows;
	function cTableEditTextArea($table,$idfield,$idval,$name,$field,$cols=30,$rows=5){
		parent::cTableEditField($table,$idfield,$idval,$name,$field);
		$this->cols = intval($cols);
		$this->rows = intval($rows);
	}
	function Show($base=""){
		$value = sqlgetone("SELECT `$this->field` FROM `$this->table` WHERE `$this->idfield`='$this->idval'");
		?>
		<?=$this->name?>:<br><textarea name="<?=$base."/".$this->field?>" rows="<?=$this->rows?>" cols="<?=$this->cols?>"><?=htmlspecialchars($value)?></textarea>
		<?php
	}
	function HandleInput($base=""){
		$_REQUEST["$base/$this->field"] = unhtmlentities($_REQUEST["$base/$this->field"]);
		parent::HandleInput($base);
	}
}



//edit one table field with flags
//$flags is an BITVALUE=>NAME array
class cTableEditFlagField extends cTableEditField {
	var $flags;
	function cTableEditFlagField($table,$idfield,$idval,$name,$field,$flags){
		parent::cTableEditField($table,$idfield,$idval,$name,$field);
		$this->flags = $flags;
	}
	function Show($base=""){
		$value = sqlgetone("SELECT `$this->field` FROM `$this->table` WHERE `$this->idfield`='$this->idval'");
		?>
		<table>
			<tr><th valign=top><?=$this->name?></th>
			<td><table>
			<?php foreach($this->flags as $bit=>$name){ 
				if($value & $bit)$checked = "checked";
				else $checked = "";
				?>
				<tr><td nowrap><input type="checkbox" name="<?=$base."/bitfield/".$bit?>" value=1 <?=$checked?>> <?=$name?></td></tr>
			<?php } ?>
			</table></td></tr>
		</table>
		<?php
	}
	function HandleInput($base=""){
		$value = 0;
		foreach($this->flags as $bit=>$name)
			if(isset($_REQUEST["$base/bitfield/$bit"]))$value |= $bit;
		$_REQUEST["$base/$this->field"] = $value;
		parent::HandleInput($base);
	}
}


//edit one table field with rowid id but shows the path as an image
class cTableEditIMGUrl extends cTableEditTextField {
	function cTableEditIMGUrl($table,$idfield,$idval,$name,$field){
		parent::cTableEditTextField($table,$idfield,$idval,$name,$field);
	}
	function Show($base=""){
		$value = sqlgetone("SELECT `$this->field` FROM `$this->table` WHERE `$this->idfield`='$this->idval'");
		?>
		<table width=100%><tr>
			<td><img src="<?=g($value)?>" border=0></td>
			<td align=right><?php parent::Show($base)?></td>
		</tr></table>
		<?php
	}
}

//edit one table field with rowid id an shows the value as a color
class cTableEditColorTextField extends cTableEditTextField {
	function cTableEditIMGUrl($table,$idfield,$idval,$name,$field){
		parent::cTableEditTextField($table,$idfield,$idval,$name,$field);
	}
	function Show($base=""){
		$value = sqlgetone("SELECT `$this->field` FROM `$this->table` WHERE `$this->idfield`='$this->idval'");
		?>
		<table width=100%><tr>
			<td><div style="background-color:<?=$value?>;width:15px;height:15px;">&nbsp;</td>
			<td align=right><?php parent::Show($base)?></td>
		</tr></table>
		<?php
	}
}

//edit one table field with on/off 1/0
class cTableEditCheckedField extends cTableEditFlagField {
	function cTableEditCheckedField($table,$idfield,$idval,$name,$field){
		parent::cTableEditFlagField($table,$idfield,$idval,$name,$field,array(1=>""));
	}
}

//edit one table field with radiobuttons
//radios is an VALUE=>NAME array
class cTableEditRadioField extends cTableEditField {
	var $radios;
	function cTableEditRadioField($table,$idfield,$idval,$name,$field,$radios){
		parent::cTableEditField($table,$idfield,$idval,$name,$field);
		$this->radios = $radios;
	}
	function Show($base=""){
		$value = sqlgetone("SELECT `$this->field` FROM `$this->table` WHERE `$this->idfield`='$this->idval'");
		?>
		<table>
			<tr><th valign=top><?=$this->name?></th></tr>
			<?php foreach($this->radios as $val=>$name){ 
				if($value == $val)$checked = "checked";
				else $checked = "";
				?>
				<tr><td nowrap><input type="radio" name="<?=$base."/".$this->field?>" value="<?=htmlspecialchars($val)?>" <?=$checked?>> <?=$name?></td></tr>
			<?php } ?>
		</table>
		<?php
	}
	function HandleInput($base=""){
		parent::HandleInput($base);
	}
}

//edit one table field with a dropdown
//radios is an VALUE=>NAME array
class cTableEditDropDown extends cTableEditField {
	var $dropdown;
	function cTableEditDropDown($table,$idfield,$idval,$name,$field,$dropdown){
		parent::cTableEditField($table,$idfield,$idval,$name,$field);
		$this->dropdown = $dropdown;
	}
	function Show($base=""){
		$value = sqlgetone("SELECT `$this->field` FROM `$this->table` WHERE `$this->idfield`='$this->idval'");
		?>
		<table width=100% border=0><tr>
			<th><?=$this->name?></th>
			<td align=right>
				<select name="<?=$base."/".$this->field?>" size="1">
				<?php foreach($this->dropdown as $val=>$name){ 
					if($value == $val)$checked = "selected";
					else $checked = "";
					?>
					<option value="<?=htmlspecialchars($val)?>" <?=$checked?>><?=$name?></option>
				<?php } ?>
				</select>
			</td>
		</tr></table>
		<?php
	}
	function HandleInput($base=""){
		parent::HandleInput($base);
	}
}

//edit one table field with a list of possible ints, works like a flag field
//ie. 15,6,7,11
//values contain an VALUE=>NAME array
//values is not allowed to contain one element more than one time
class cTableEditListFlagField extends cTableEditField {
	var $values,$delimiter;
	function cTableEditListFlagField($table,$idfield,$idval,$name,$field,$values,$delimiter=","){
		parent::cTableEditField($table,$idfield,$idval,$name,$field);
		$this->values = $values;
		$this->delimiter = $delimiter;
	}
	function Show($base=""){
		$value = sqlgetone("SELECT `$this->field` FROM `$this->table` WHERE `$this->idfield`='$this->idval'");
		$list = explode($this->delimiter,$value);
		?>
		<table>
			<tr><th valign=top><?=$this->name?></th></tr>
			<?php foreach($this->values as $val=>$name){ 
				if(in_array($val,$list))$checked = "checked";
				else $checked = "";
				?>
				<tr><td nowrap><input type="checkbox" name="<?=$base."/list/".$val?>" value=1 <?=$checked?>> <?=$name?></td></tr>
			<?php } ?>
		</table>
		<?php
	}
	function HandleInput($base=""){
		$value = array();
		foreach($this->values as $val=>$name)
			if(isset($_REQUEST["$base/list/$val"]))$value[] = $val;
		$_REQUEST["$base/$this->field"] = implode($this->delimiter,$value);
		parent::HandleInput($base);
	}
}

?>
