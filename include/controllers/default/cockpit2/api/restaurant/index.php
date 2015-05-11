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
		
		switch (c::getPagePiece(2)) {
			case 'image':
				$this->_image();
				break;
			default:
				$this->_restaurant();
				break;
			
		}
	}
	
	private function _image() {
		switch ($this->method()) {
			case 'get':
				echo $restaurant->image;
				break;
			case 'post':
			case 'put':
				// convert the image localy into its 3 different sizes:
				//   origional
				//   
				// upload the image to amazon aws, or localy, depending onthe environment
				break;
		}
	}
	
	private function _restaurant() {
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