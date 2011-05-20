<?php
namespace phmop;

class StandardClass {
    public $slots = array();
	public $meta; 
	
    public function __call($slot, $args) {
        return Registry::dispatchMethod($this, $slot, $args);
    }

    public function &__get($slot) {
        return Registry::getSlot($this, $slot);
    }

    public function __set($slot, $value) {
        Registry::setSlot($this, $slot, $value);
    }

    public function __construct() {
		$this->meta = Registry::getClass(get_class($this));
		
        foreach(Appos(func_get_args()) as $key => $val) { 
            $this->$key = $val;
        }
    }

	public function isInstanceOf($class) {
		return $this->meta->name->is($class) || (bool)$this->meta->Extend->classes->has(Registry::getFQN($class));
	}
}
