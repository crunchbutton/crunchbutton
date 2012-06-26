<?php

class Controller_api_resturant extends Crunchbutton_Controller_Rest {
	public function init() {
		switch ($this->method()) {
			case 'get':
				$r = new Resturant(c::getPagePiece(1));
				print_r($r);
				
				break;

		}
	}
}