<?php

class Controller_api_dish extends Crunchbutton_Controller_Rest {
	public function init() {
		switch ($this->method()) {
			case 'post':
			case 'get':
				$out = Dish::o(c::getPagePiece(2));
				if ($out->id_dish) {
					echo $out->json();
				} else {
					echo json_encode(['error' => 'invalid object']);
				}
				break;
		}
	}
}