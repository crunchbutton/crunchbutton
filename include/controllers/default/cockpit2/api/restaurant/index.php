<?php

class Controller_api_restaurant extends Crunchbutton_Controller_RestAccount {

	public function init() {

		$restaurant = Restaurant::permalink(c::getPagePiece(2));

		if (!$restaurant->id_restaurant) {
			$restaurant = Restaurant::o(c::getPagePiece(2));
		}

		if (!$restaurant->id_restaurant) {
			$this->error(404);
		}

		if (!c::admin()->permission()->check(['global', 'restaurants-all', 'restaurants-crud', 'restaurant-'.$restaurant->id_restaurant.'-edit', 'restaurant-'.$restaurant->id_restaurant.'-all'])) {
			$this->error(401);
		}
		
		$this->restaurant = $restaurant;
		
		switch (c::getPagePiece(3)) {
			case 'image':
				$this->_image();
				break;
			default:
				$this->_restaurant();
				break;
			
		}
	}
	
	private function _image() {
		// pull path of a temporary file

		switch ($this->method()) {
			case 'get':
				die($this->restaurant->image());
				header('Location: '.$this->restaurant->image());
				break;

			case 'post':
			case 'put':
				if ($_FILES) {
					foreach ($_FILES as $file) {
						$this->restaurant->updateImage($file['tmp_name']);
					}
				}
				break;
		}
	}
	
	private function _restaurant() {
		switch ($this->method()) {
			case 'get':
				echo $this->restaurant->json();
				break;
			case 'post':
				// do nothing for now
				break;
		}
	}
}