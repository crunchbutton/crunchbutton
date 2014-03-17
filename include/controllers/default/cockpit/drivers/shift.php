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

		if( $id_community ){
			$segments = Crunchbutton_Community_Shift::shiftByCommunity( $id_community );
			c::view()->segments = $segments;
		}

		$days = [ 'mon','tue','wed','thu','fri','sat','sun' ];
		c::view()->id_community = $id_community;
		c::view()->days = $days;
		c::view()->display( 'drivers/shift/community/index' );
	}
}