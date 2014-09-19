<?php

class Controller_api_driver extends Crunchbutton_Controller_RestAccount {

	public function init() {
		if (preg_replace('/[^0-9]/','',c::getPagePiece(2)) == c::getPagePiece(2) && c::getPagePiece(2)) {
			$driver = Admin::o(c::getPagePiece(2));
			$action = c::getPagePiece(3);
		} else {
			$driver = c::user();
			$action = c::getPagePiece(2);
		}

		switch ($action) {
			case 'location':
				if ($this->method() == 'post') {
					(new Admin_Location([
						'id_admin' => $driver->id_admin,
						'date' => date('Y-m-d H:i:s'),
						'lat' => $this->request()['lat'],
						'lon' => $this->request()['lon'],
						'accuracy' => $this->request()['accuracy']
					]))->save();
				}
				if ( method_exists( $driver, 'location' ) && $driver->location()->id_admin_location) {
					echo $driver->location()->json();
				} else {
					echo json_encode(null);
				}
				break;

			case 'all':
				$out = [];
				$drivers = Admin::drivers();
				foreach( $drivers as $driver ){
					$out[] = [ 'id_admin' => intval( $driver->id_admin ), 'name' => $driver->name ];
				}
				echo json_encode( $out );
				break;

			case 'list-payment-type':
				$out = [];
				$drivers = Admin::drivers();
				foreach( $drivers as $driver ){
					if( $driver->hasPaymentType() ){
						$out[] = [ 'id_admin' => intval( $driver->id_admin ), 'name' => $driver->name ];
					}
				}
				echo json_encode( $out );
				break;

			default:
				if ($this->method() == 'post') {
					// save a setting
				}
				echo $driver->json();
				break;
		}


	}
}