<?php

class Controller_api_restaurants extends Crunchbutton_Controller_Rest {
	public function init() {
		$config = [];
		if ($_REQUEST['lat'] && $_REQUEST['lon']) {
			$restaurants = Restaurant::byRange([
				'lat' => $_REQUEST['lat'],
				'lon' => $_REQUEST['lon']
			]);
			foreach ($restaurants as $restaurant) {
				$config['restaurants'][] = $restaurant->exports();
			}
		}
		echo json_encode($config);
	}
}