<?php

class Controller_api_restaurants extends Crunchbutton_Controller_Rest {
	public function init() {
		$config = [];
		if ($_REQUEST['lat'] && $_REQUEST['lon']) {
			$restaurants = Restaurant::byRange([
				'lat' => $_REQUEST['lat'],
				'lon' => $_REQUEST['lon'],
				'miles' => $_REQUEST['miles'],
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