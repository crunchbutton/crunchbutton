<?php

class Controller_api_v1_delivery extends Crunchbutton_Controller_Rest {
	public function init() {
		$delivery = Delivery::o(c::getPagePiece(3));
		
		switch (c::getPagePiece(4)) {
			case 'files':
				echo $delivery->files()->json();
				break;
			default:
				echo $delivery->json();
				break;
		}
	}
}