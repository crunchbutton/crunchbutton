<?php

class Controller_api_status extends Crunchbutton_Controller_Rest {
	public function init() {
		// check to see that the database is working ok
		$restaurant = Restaurant::q('select * from restaurant limit 1');
		echo json_encode(['status' => $restaurant->id_restaurant ? 'online' : 'offline']);
	}
}