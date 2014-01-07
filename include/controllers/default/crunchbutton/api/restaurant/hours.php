<?php

class Controller_api_restaurant_hours extends Crunchbutton_Controller_Rest {
	public function init() {
		$r = Restaurant::o( c::getPagePiece( 3 ) );
		if( !$r->id_restaurant ){
			echo json_encode( [ 'error' => 'invalid object' ] );
			exit;
		}
		switch ( c::getPagePiece( 4 ) ) {
			case 'week':
				// export the hours for the whole week
				$no_utc = ( c::getPagePiece( 5 ) != 'regular' );
				echo json_encode( $r->export_hours_week( $no_utc ) );exit;;
				break;
			
			default:
				// export the hours for the next 24 hours
				$no_utc = ( c::getPagePiece( 4 ) != 'regular' );
				echo json_encode( $r->export_hours_next_24_hours( $no_utc ) );exit;;
				break;
		}
	}
}