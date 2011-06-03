<?php
namespace phmop;

use Phutility\Func;

class Registry {
	private static $classes = array(); 
	private static $classMethods = array();
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
		
	public static function registerMethodForClass($class, $name, &$func)
	{
		if(!isset(self::$classMethods[$class])) {
			self::$classMethods[$class] = array();
		}
		
		self::$classMethods[$class][$name] =& $func;
	}
	
	public static function addMethod($name, $meta) {
		if(isset(self::$generics[$name])) {
			$funcs = $meta->filter(function($i) { return $i instanceof \Closure; });

			if(empty($funcs)) {
				throw new \Exception("At least one method is required");
			}

			foreach($funcs as &$func) {
				foreach(Func::getParameters($func) as $param) {
					$class = self::getFQN($param->getClass()->getName());
					self::$generics[$name][$class] =& $func;
					self::registerMethodForClass($class, $name, $func);
				}
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
		//check method 
			//check for before / after / around wrappers
		
		$class = get_class($obj);
		if(isset(self::$classMethods[$class][$method])) {
			array_unshift($args, $obj);
			return call_user_func_array(self::$classMethods[$class][$method], $args);
		}
		
		throw new \Exception("Could not find $method on " . get_class($obj));
	}
}