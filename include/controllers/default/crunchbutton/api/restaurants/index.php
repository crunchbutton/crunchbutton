<?php

class Controller_api_restaurants extends Crunchbutton_Controller_Rest {
	public function init() {
		$config = [];
		if ($_REQUEST['lat'] && $_REQUEST['lon']) {
			$restaurants = Restaurant::byRange([
				'lat' => c::db()->escape($_REQUEST['lat']),
				'lon' => c::db()->escape($_REQUEST['lon']),
				'range' => c::db()->escape($_REQUEST['range']),
			]);
			foreach ($restaurants as $restaurant) {
				$data = $restaurant->exports(['categories' => true]);
				$data[ 'top_name' ] = $restaurant->top()->top_name;
				$config['restaurants'][] = $data;
			}
		}
		echo json_encode($config);
	}
}