<?php

class Controller_api_houroverride extends Crunchbutton_Controller_RestAccount {
	
	public function init() {

		// if (!c::admin()->permission()->check(['global','rules'])) {
			// return ;
		// }
		$id_restaurant_hour_override = $this->request()[ 'id_restaurant_hour_override' ];
		if( $id_restaurant_hour_override ){
			$hour = Crunchbutton_Restaurant_Hour_Override::o( $id_restaurant_hour_override );
			$id_restaurant = $hour->restaurant()->id_restaurant;
		} else {
			$id_restaurant = $this->request()[ 'id_restaurant' ];	
		}

		if( !$id_restaurant ){
			return;
		}

		if (!c::admin()->permission()->check(['global', 'restaurants-all', 'restaurants-crud','restaurant-'.$id_restaurant.'-all','restaurant-'.$id_restaurant.'-edit'])) {
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

						$date_start = $this->request()[ 'date_start' ];
						$date_start = explode( '/' , $date_start );
						$date_start_hour = ( $this->request()[ 'date_start_hour' ] ) ? $this->request()[ 'date_start_hour' ] : '00:00';
						$date_start_hour = explode( ':', $date_start_hour );
						$date_start_hr = $date_start_hour[ 0 ];
						$date_start_mn = $date_start_hour[ 1 ];
						$hour_override_date_start_ampm = $this->request()[ 'hour_override_date_start_ampm' ];
						if( $hour_override_date_start_ampm == 'PM' && $date_start_hr < 12 ){
							$date_start_hr = $date_start_hr + 12;
						}

						$date_start = $date_start[ 2 ] . '/' . $date_start[ 0 ] . '/' . $date_start[ 1 ] . ' ' . $date_start_hr . ':' . $date_start_mn;

						$date_end = $this->request()[ 'date_end' ];
						$date_end = explode( '/' , $date_end );
						$date_end_hour = ( $this->request()[ 'date_end_hour' ] ) ? $this->request()[ 'date_end_hour' ] : '00:00';
						$date_end_hour = explode( ':', $date_end_hour );
						$date_end_hr = $date_end_hour[ 0 ];
						$date_end_mn = $date_end_hour[ 1 ];
						$hour_override_date_end_ampm = $this->request()[ 'hour_override_date_end_ampm' ];
						if( $hour_override_date_end_ampm == 'PM' && $date_end_hr < 12 ){
							$date_end_hr = $date_end_hr + 12;
						}

						$date_end = $date_end[ 2 ] . '/' . $date_end[ 0 ] . '/' . $date_end[ 1 ] . ' ' . $date_end_hr . ':' . $date_end_mn;

						$start = new DateTime( $date_start, new DateTimeZone( 'UTC' ) );
						$end = new DateTime( $date_end, new DateTimeZone( 'UTC' ) );

						if( $start > $end ){
							echo json_encode( [ 'error' => 'The end date should be AFTER start date!' ] );
							exit();
						}

						$hour->date_start = $date_start;
						$hour->date_end = $date_end;
						$hour->type = $this->request()[ 'type' ];
						$hour->notes = $this->request()[ 'notes' ];
						$hour->id_admin = c::admin()->id_admin;
						$hour->save();

						if( $hour->id_restaurant_hour_override ){
							echo json_encode( [ 'success' => 'success' ] );	
						} else {
							echo json_encode( [ 'error' => 'error' ] );
						}	
						exit;
						break;
				}
				break;
		}
	}
}
