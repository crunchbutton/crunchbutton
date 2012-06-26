<?php

class Controller_api_restaurant extends Crunchbutton_Controller_Rest {
	public function init() {
		switch ($this->method()) {
			case 'get':
				$out = Restaurant::o(c::getPagePiece(2));
				if ($out->id_restaurant) {
					echo $out->json();
				} else {
					echo json_encode(['error' => 'invalid object']);
				}
				break;
		}
	}
}