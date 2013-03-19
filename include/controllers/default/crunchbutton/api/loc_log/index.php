<?php

class Controller_Api_Loc_Log extends Crunchbutton_Controller_Rest {
	public function init() {

		switch ( $this->method() ) {
			 case 'post':
				if (c::getPagePiece(2) == 'new') {
					$loc = new Crunchbutton_Loc_Log;
					$loc->id_user = c::user()->name ? c::user()->id_user : null;
					$loc->name = $this->request()['name'];
					$loc->address_entered = $this->request()['addressEntered'];
					$loc->address_reverse = $this->request()['addressReverse'];
					$loc->city = $this->request()['city'];
					$loc->region = $this->request()['region'];
					$loc->lat = $this->request()['lat'];
					$loc->long = $this->request()['lon'];
					$loc->ip = $_SERVER['REMOTE_ADDR'];
					$loc->date = date('Y-m-d H:i:s');
					$loc->save();
					echo $loc->json();
				}
			default:
				echo json_encode(['error' => 'invalid request']);
		}
	}
}