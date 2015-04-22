<?php

class Crunchbutton_Order_Logistics_Destination extends Cana_Model {
	public static function addDestination($list = [], $destination = null, $force = false) {
		if (!$destination) {
			return $list;
		}

		if (!$force) {
			foreach ($list as $dst) {
				if ($dst->address == $destination->address) {
					continue;
				}
			}
		}

		$list[] = $destination;
		return $list;
	}
	
	public function __construct($params = []) {
		foreach ($params as $key => $param) {
			$this->{$key} = $param;
		}
		
		// need to calculate the distance from the driver
		
	}
}