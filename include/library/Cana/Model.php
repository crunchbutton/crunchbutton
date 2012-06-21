<?php

/**
 * Base model class
 * 
 * @author    Devin Smith <devin@cana.la>
 * @date     2009.09.17
 *
 * All classes extend the Model object as a base.
 * Contains methods to extend objects.
 *
 * ex:
 *		Staff::extend(['simpleFunc' => function($me, $in) {
 *			echo get_class($me).' '.$in;
 *		}]);
 *		Staff::o(DSM)->simpleFunc('got this');
 *		// outputs "Staff got this"
 *
 */

class Cana_Model {
	private $_extended = [];
	
	public static function extend() {
		foreach (func_get_args() as $arg) {
			foreach ($arg as $key => $value) {
				c::app()->extended(get_called_class(),$key,$value);
			}
		}
	}
	
	public function __call($name, $arguments) {
		$func = c::app()->extended(get_called_class(), $name);
		if (is_callable($func)) {
			array_unshift($arguments,$this);
			return call_user_func_array($func, $arguments);
		} else {
			throw new Exception(get_called_class().' has no method '.$name);
		}
	}
	
	public static function l2a($list,$sep = "\n") {
		$list = explode($sep,$list);
		foreach ($list as $item) {
			$item = trim($item);
			if (!$item) continue;
			$items[] = $item;
		}
		return $items;
	}

    /**
	 * Converts an array into a model
	 * 
	 * @param    array        the array to convert
	 * @return    Model
	 */
	public static function toModel($array) {

		$object = new Cana_Model();
		if (is_array($array) && count($array) > 0) {
			foreach ($array as $name => $value) {
				if ($name === 0) {
					$isArray = true;
				}

				if (!empty($name) || $name === 0) {

					if (is_array($value)) {
						if (!count($value)) {
                    		$value = null;
						} else {
							$value = self::toModel($value);
						}
                    }
					if ($isArray) {
						switch ($value) {
							case 'false':
								$array[$name] = false;
								break;
							case 'true':
								$array[$name] = true;
								break;
							case 'null':
								$array[$name] = null;
								break;
							default:
								$array[$name] = $value;
								break;
						}
					} else {
						$name = trim($name);
						switch ($value) {
							case 'false':
								$object->$name = false;
								break;
							case 'true':
								$object->$name = true;
								break;
							case 'null':
								$object->$name = null;
								break;
							default:
								$object->$name = $value;
								break;
						}					
					}
				}
			}
		}

		return $isArray ? $array : $object;
	}
} 