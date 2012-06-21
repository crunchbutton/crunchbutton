<?php

/**
 * Object factory and cacher
 * 
 * @author		Devin Smith <devin@cana.la>
 * @date		2011.12.16
 *
 * The factory automaticly figures out what you are passing it and returns
 * either the cached object, or caches it.
 *
 * ex:
 *		class myObject extends Cana_Table {
 *			...
 *		}
 *		$object = new myObject($id);
 *		$object->something = 'something else';
 * 		echo c::factory($object)->something;
 * 		// would output 'something else'
 *
 * ex2:
 *		// factory also loads the object from table if it doesnt have it
 *		$object = c::factory('myObject',$id);
 *
 * ex3:
 *		// iterator automaticly calls factory
 * 		foreach (myObject::o($id, $id2) as $object) {
 *			echo $object->something;
 *		}
 *		
 */

// object maping factory cacher for Cana_Table

class Cana_Factory extends Cana_Model {
	private $_objectMap;
	public function __construct() {
		$this->_objectMap = [];
	}
	public function objectMap($a, $b = null) {

		// create a new object if not caching
		if (Cana::config()->cache->object === false) {
			$obj = new $a($b);

		} else {

			if (is_string($a)) {
				$t = new $a;
			}
			
			// NOCACHE: if the first param is an object, and you gave us the id, use the id you gave us
			if (is_object($a) && (is_string($b) || is_int($b))) {
				$obj = $this->_objectMap[get_class($a)][$b] = $a;

			// CACHED: if the first param is an object, the second is an id, and we have it cached
			} elseif (is_object($a) && (is_string($b) || is_int($b)) && $this->_objectMap[get_class($a)][$a->{$b}]) {
				$obj = $this->_objectMap[get_class($a)][$a->{$b}];
				
			// CACHED: if the first param is an object, and we have it cached
			} elseif (is_object($a) && method_exists($a, 'idVar') && $this->_objectMap[get_class($a)][$a->{$a->idVar()}]) {
				$obj = $this->_objectMap[get_class($a)][$a->{$a->idVar()}];

			// NOCACHE: if the first param is an object with no other info, store it. these come from Cana_Table typicaly
			} elseif (is_object($a) && method_exists($a, 'idVar')) {
				$obj = $this->_objectMap[get_class($a)][$a->{$a->idVar()}] = $a;

			// CACHED: if the first param is the type of object, and the second one is the id
			} elseif (is_string($a) && (is_string($b) || is_int($b)) && $this->_objectMap[$a][$b]) {
				$obj = $this->_objectMap[$a][$b];

			// CACHED: if the first param is the type of object, and the second one is the object and we didnt know that we already had it
			} elseif (is_string($a) && is_object($b) && method_exists($t, 'idVar') && $this->_objectMap[$a][$b->{$t->idVar()}]) {
				$obj = $this->_objectMap[$a][$b->{$t->idVar()}];
	
			// NOCACHE: we dont have it, so make it and store it
			} elseif ($a) {
				$obj = new $a($b);
				if (!$this->_objectMap[get_class($obj)][$obj->{$obj->idVar()}]) {
					$this->_objectMap[get_class($obj)][$obj->{$obj->idVar()}] = $obj;
				}

			// NOCACHE: you didnt give us anything to work with
			} else {
				$obj = new Cana_Model;
			}
		}

		// return an object of some type
		$t = null;
		return $obj;
	}
	
	public function count() {
		$count = 0;
		if ($this->_objectMap) {
			foreach ($this->_objectMap as $o) {
				$count += count($o);
			}
		}
		return $count;
	}
	
	public function __toString() {
		$print = '';
		foreach ($this->_objectMap as $key => $type) {
			foreach ($type as $k => $item) {
				$print .= $key.' -- '.$k." -- \n".$item->__toString()."\n\n";
			}
		}
		return $print;
	}
}