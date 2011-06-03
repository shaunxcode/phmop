<?php

namespace {
	const NS = '\\';
	
	require_once 'vendor/phutility/src/Node.php';
	require_once 'vendor/phutility/src/Invokable.php';
	require_once 'vendor/phutility/src/Appos.php';
	require_once 'vendor/phutility/src/Func.php';
	
	require_once 'src/MetaArg.php';
	require_once 'src/Registry.php';
	require_once 'src/StandardClass.php';
	
	use phmop\MetaArg;
	use phmop\Registry;
	use phutility\Appos;
	
	function ns($namespace) {
		Registry::setNamespace($namespace);
		return $namespace;
	}
	
	function Appos($args) {
		return Appos::create($args); 
	}
	
	function MetaArg($type) {
		return new MetaArg($type);
	}

	function getclass($name) {
		return Registry::getClass($name);
	}

	function extend() {
		return MetaArg('Extend')->classes(func_get_args());
	}

	function slot() {
		$args = func_get_args();
		if(count($args) == 2) {
			return MetaArg('Slot')->name(current($args))->default(end($args));
		} else {
			return MetaArg('Slot')->name(current($args));
		}
	}

	function method($name, $func) {
		return MetaArg('Method')->name($name)->func($func);
	}
	
	function before() {
		return MetaArg('Before');
	}

	function after() {
		return MetaArg('After');
	}

	function arity() {
		return MetaArg('Arity')->add(func_get_args());
	}

	function defclass() {
		$args = func_get_args();
		$name = array_shift($args);
		return Registry::addClass($name, MetaArg('Class')->name($name)->add($args));
	}
	
	function defmethod() {
		$args = func_get_args();
		$name = array_shift($args);
		return Registry::addMethod($name, MetaArg('Method')->name($name)->add($args));
	}
	
	function defgeneric() {
		$args = func_get_args();
		return Registry::addGeneric(array_shift($args), MetaArg('Generic')->args($args));
	}
}

namespace phmop {
	const StandardClass = 'phmop\StandardClass';
}