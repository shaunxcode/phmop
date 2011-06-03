<?php

namespace phmop\Test\defmethod;

use Phutility\Test;
use phmop\Registry; 

ns('phmop\Test\defmethod'); 

defclass(rectangle, 
	slot(height, 0.0),
	slot(width, 0.0));

defclass(colorMixin,
	slot(cyan, 0),
	slot(magenta, 0),
	slot(yellow, 0));
	
defclass(colorRectangle,
	extend(colorMixin, rectangle),
	slot(clearp),
	method(clearp, function() { return true; }));
	
defgeneric(paint, x);

defmethod(paint,
	function(rectangle $x) {
		verticalStroke(
			$x->height, 
			$x->width);});

defmethod(paint,
	before(),
	function(colorMixin $x) {
		setBrushColor($x->cyan, $x->magenta, $x->yellow);});

defmethod(paint,
	function(colorRectangle $x) {
		return "Called paint for type of colorRectangle";});

$door = new colorRectangle(
	width, 38, 
	height, 84, 
	cyan, 60, 
	yellow, 65, 
	clearp, null);

Test::assert("Is instance of colorRectangle", $door->isInstanceOf(colorRectangle));
Test::assert("Is instance of StandardClass", $door->isInstanceOf(\phmop\StandardClass));
Test::assert("Is instance of colorMixin", $door->isInstanceOf(colorMixin));
Test::assert("Is instance of rectangle", $door->isInstanceOf(rectangle));
Test::throwsException("exception on bad method", function() use($door) { $door->isMagicPig(); });
Test::assert("access property", $door->width, 38);
Test::assert("can access mixin property", $door->yellow, 65);
Test::assert("call generic method", $door->paint(), "Called paint for type of colorRectangle");

//$method = Registry::getMethod(Paint);
//Test::assert("Is a Method node", $method->isType(Method), true);

