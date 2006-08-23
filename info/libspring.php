<?php

class Vector{
	var $x,$y;

	function Vector($x,$y){
		$this->x = $x;
		$this->y = $y;
	}

	function length(){
		return sqrt($this->x*$this->x+$this->y*$this->y);
	}

	function add($v){
		$this->x += $v->x;
		$this->y += $v->y;
	}

	function mul($s){
		$this->x *= $s;
		$this->y *= $s;
	}

	function norm(){
		$l = $this->length();
		$this->x /= $l;
		$this->y /= $l;
	}

	function info(){
		return "Vector[x=".($this->x)." y=".($this->y)."]";
	}
}

class Node{
	var $p,$v; //position, speed

	function Node($p){
		$this->p = $p;
		$this->v = new Vector(0,0);
	}

	function info(){
		return "Node[p=".($this->p->info())." v=".($this->v->info())."]";
	}
}

class Link{
	var $left,$right; //both endnodes, references	
	var $length;  //original length
	
	function Link(&$left,&$right,$length){
		$this->left =& $left;
		$this->right =& $right;
		$this->length = $length;
	}
	
	function info(){
		return "Link[left=".($this->left->info())." right=".($this->right->info())." length=".($this->length)." l=".($this->l())."]";
	}
	
	function l(){
		$x = $this->right->p;
		$x->mul(-1);
		$x->add($this->left->p);
		return $x->length();
	}
}

/* example

$links = Array();
$nodes = Array();


$node1 = new Node(new Vector(1,1));
$node2 = new Node(new Vector(5,1));
$node3 = new Node(new Vector(3,3));

$link1 = new Link($node1,$node2,2);
$link2 = new Link($node2,$node3,10);
$link3 = new Link($node3,$node1,3);

$nodes[] =& $node1;
$nodes[] =& $node2;
$nodes[] =& $node3;
$links[] =& $link1;
$links[] =& $link2;
$links[] =& $link3;

*/


function moveNodes(&$nodes,&$links,$fak=0.1){
	//echo "==[ move ]===============================================\n";
	for($i=0;$i<sizeof($links);++$i){
		$link =& $links[$i];
		//echo $link->info()."\n";
		$disp = $link->l() - $link->length;
		$vn = $link->left->p;
		$vn->mul(-1);
		$vn->add($link->right->p);
		$vn->norm();
		$vn->mul($disp);
		$vn->mul($fak);
		
		$link->left->p->add($vn);
		$vn->mul(-1);
		$link->right->p->add($vn);
	}

	for($i=0;$i<sizeof($nodes);++$i){
		$node =& $nodes[$i];
		//echo $node->info()."\n";
		$v = $node->v;
		$v->mul(1);
		$node->p->add($v);
		$node->v->mul(0.98);
	}
}

function bounceNodes(&$nodes){
	//echo "==[ bounce ]===============================================\n";
	for($i=0;$i<sizeof($nodes);++$i){
		$node =& $nodes[$i];
		//echo $node->info()."\n";
		$v = $node->v;

		for($a=0;$a<sizeof($nodes);++$a)if($i != $a){
			$other =& $nodes[$a];
			$x = $node->p;
			$x->mul(-1);
			$x->add($other->p);
			if($x->length() == 0)continue;
			$x->mul(1 / ($x->length()));
			$v->add($x);
		}

		$v->mul(0.8);
		$node->p->add($v);
	}
}

//translate the hole nodes, so that the positions are html friendly
function makeNodesNice(&$nodes,$width=800,$height=600){
	$left = 10000000;
	$right = -10000000;
	$top = 10000000;
	$bottom = -10000000;

	for($i=0;$i<sizeof($nodes);++$i){
		$node =& $nodes[$i];
	
		if($node->p->x < $left)$left = $node->p->x;
		if($node->p->x > $right)$right = $node->p->x;
		if($node->p->y < $top)$top = $node->p->y;
		if($node->p->y > $bottom)$bottom = $node->p->y;
	}

	$w = $right - $left;
	$h = $bottom - $top;

	for($i=0;$i<sizeof($nodes);++$i){
		$node =& $nodes[$i];

		$node->p->x -= $left;
		$node->p->y -= $top;
		$node->p->x = $node->p->x * $width / $w;
		$node->p->y = $node->p->y * $height / $h;
	}
}
?>
