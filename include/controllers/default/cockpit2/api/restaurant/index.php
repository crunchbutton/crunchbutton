<?php

class Controller_api_restaurant extends Crunchbutton_Controller_RestAccount {

	public function init() {

		$restaurant = Restaurant::permalink(c::getPagePiece(2));

		if (!$restaurant->id_restaurant) {
			$restaurant = Restaurant::o(c::getPagePiece(2));
		}

		if (!$restaurant->id_restaurant) {
			header('HTTP/1.0 404 Not Found');
			exit;
		}

		if (!c::admin()->permission()->check(['global', 'restaurants-all', 'restaurants-crud', 'restaurant-'.$restaurant->id_restaurant.'-edit', 'restaurant-'.$restaurant->id_restaurant.'-all'])) {
			header('HTTP/1.1 401 Unauthorized');
			exit;
		}

		switch ($this->method()) {
			case 'get':
				echo $restaurant->json();
				break;

			case 'post':
				// do nothing for now
				break;
		}
	}
}