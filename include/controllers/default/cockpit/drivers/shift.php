<?php

class Controller_drivers_shift extends Crunchbutton_Controller_Account {
	
	public function init() {

		c::view()->page = 'drivers';

		switch ( c::getPagePiece( 2 ) ) {
			case 'community':
				$this->community();
				break;
			
			default:
				exit();
				break;
		}
	}

	public function community(){

		$id_community = c::getPagePiece( 3 );
		$days = [];

		if( $id_community ){
			$week = c::getPagePiece( 5 ) ? c::getPagePiece( 5 ) : date( 'W' );
			if( intval( $week ) < 10 ){
				$week = '0' . intval( $week );
			}
			
			for( $i = 0; $i <= 6; $i++ ){
				$days[] = new DateTime( date( 'Y-m-d', strtotime( date( 'Y' ) . 'W' . $week . $i ) ), new DateTimeZone( c::config()->timezone  ) );
			}
		}

		c::view()->id_community = $id_community;
		c::view()->days = $days;
		c::view()->display( 'drivers/shift/community/index' );
	}
}