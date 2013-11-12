<?php

class Controller_api_houroverride extends Crunchbutton_Controller_RestAccount {
	
	public function init() {

		// if (!c::admin()->permission()->check(['global','rules'])) {
			// return ;
		// }
		$id_restaurant_hour_override = $this->request()[ 'id_restaurant_hour_override' ];
		if( $id_restaurant_hour_override ){
			$hour = new Crunchbutton_Restaurant_Hour_Override();
			$id_restaurant = $hour->restaurant()->id_restaurant;
		} else {
			$id_restaurant = $this->request()[ 'id_restaurant' ];	
		}

		if (!c::admin()->permission()->check(['global', 'restaurants-all', 'restaurants-crud','restaurant-'.$hour->restaurant()->id_restaurant.'-all','restaurant-'.$hour->restaurant()->id_restaurant.'-edit'])) {
			return;
		}

		switch ( $this->method() ) {

			case 'post':

				switch ( c::getPagePiece(2) ) {

					case 'remove':
						$hour = Crunchbutton_Restaurant_Hour_Override::o( $id_restaurant_hour_override );
						$hour->delete();
						echo json_encode( [ 'success' => 'success' ] );
						exit;
						break;

					case 'add':
						$hour = new Crunchbutton_Restaurant_Hour_Override();
						$hour->id_restaurant = $this->request()[ 'id_restaurant' ];
						$hour->date_start = $this->request()[ 'date_start' ];
						$hour->date_end = $this->request()[ 'date_end' ];
						$hour->type = $this->request()[ 'type' ];
						$hour->notes = $this->request()[ 'notes' ];
						$hour->id_admin = c::admin()->id_admin;
						$hour->save();
						echo json_encode( [ 'success' => 'success' ] );
						exit;
						break;
				}
				break;
		}
	}
}
