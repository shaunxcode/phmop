<?php
namespace phmop;

class Registry {
	private static $classes = array(); 
	private static $methods = array(); 
	private static $generics = array();
	private static $namespace = '';
	
	public static function setNamespace($namespace) {
		self::$namespace = $namespace; 
	}
	
	public static function getNamespace() {
		return self::$namespace;
	}
	
	public static function getFQN($class) {
		if(strpos($class, NS) === false) {
			return self::getNamespace() . NS . $class;
		}
		return $class;
	}
	
	public static function addClass($name, $meta) {
		//TODO write to file and require to avoid eval
		$classEval = 'namespace ' . self::$namespace .";\nclass {$name} extends \phmop\StandardClass {}\n";
		eval($classEval);
		
		if(!$meta->has('Extend')) { 
			$meta->add(extend('phmop\StandardClass'));
		} else {
			if(!$meta->Extend->classes->has('phmop\StandardClass')) {
				$meta->Extend->classes->add('phmop\StandardClass');
			}
		}
		
		$meta->Extend->classes->each(function($c) { 
			return Registry::getFQN($c);
		});
		
		self::$classes[self::$namespace . NS . $name] = $meta;
		return $meta;
	}
	
	public static function getClass($name) {
		if(isset(self::$classes[$name])) { 
			return self::$classes[$name];
		} else {
			if($name == "phmop\StandardClass") {
				self::$classes[$name] =  MetaArg('Class')->name('StandardClass')->add(extend(''));
				return self::$classes[$name];
			}
			throw new \Exception("MOP Class {$name} not defined");
		}
	}

	public static function classHasSlot($class, $slot) {
		$checker = function($n) use($slot) { 
			return $n->isType(Slot) && $n->name->is($slot); 
		};
		
		if(self::getClass($class)->has($checker)) {
			return true;
		} else {
			foreach(self::getClass($class)->Extend->classes->all() as $extClass) {
				if(self::getClass($extClass)->has($checker)) {
					return true;
				}
			}
		}
	}
	
	public static function addGeneric($name, $meta) {
		self::$generics[$name] = array($meta);
		return $meta;
	}
		
	public static function addMethod($name, $meta) {
		if(isset(self::$generics[$name])) {
			foreach($meta->allOfType('Arity') as $arity) {
				self::$generics[$name][implode(':', $arity->prior())] = $arity->last();
			}
		} else { 
			self::$methods[$name] = $meta;
		}
		return $meta;
	}
	
	public static function getMethod($name) {
		if(!isset(self::$methods[$name])) {
			throw new \Exception("Method {$name} not defined");
		}
		return self::$methods[$name];
	}
	
	public function setSlot($obj, $slot, $val) {
		if(self::classHasSlot(get_class($obj), $slot)) {
			return $obj->slots[$slot] = $val;
		}
		throw new \Exception("Try to set $slot for " . get_class($obj) . " which does not exist");		
	}
	
	public function getSlot($obj, $slot) {
		if(self::classHasSlot(get_class($obj), $slot)) {
			return $obj->slots[$slot]; 
		}
		throw new \Exception("Try to get $slot for " . get_class($obj) . " which does not exist");
	}
	
	public function dispatchMethod($obj, $method, $args) {
		throw new \Exception("Could not find $method on " . get_class($obj));
	}
}