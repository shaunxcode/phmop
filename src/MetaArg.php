<?php
namespace phmop;
use Phutility\Node;

class MetaArg extends Node {
	public function __construct($type) {
		parent::__construct($type);
	}
	
	public function __call($key, $val) {
		$this->add(new Node($key, array_shift($val)));
		return $this;
	}
}