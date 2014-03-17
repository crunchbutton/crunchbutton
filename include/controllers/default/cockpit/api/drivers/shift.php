<?php

class Controller_api_drivers_shift extends Crunchbutton_Controller_RestAccount {
	
	public function init() {
		switch ( c::getPagePiece( 3 ) ) {
			case 'community':
				$this->community();				
				break;
			
			default:
				echo json_encode( [ 'error' => 'invalid object' ] );
				break;
		}
	}

	public function community(){
		$id_community = $this->request()[ 'id_community' ];
		$day = $this->request()[ 'day' ];
		$segment = $this->request()[ 'segment' ];
		Crunchbutton_Community_Shift::saveShift( $id_community, $day, $segment );
		echo json_encode( [ 'success' => $day ] );
	}
}
