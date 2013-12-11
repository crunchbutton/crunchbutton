<?php

class Controller_api_user_preset extends Crunchbutton_Controller_Rest {
	public function init() {
		switch ( $this->method() ) {
			case 'get':
				if( c::user()->id_user ){
					$preset = c::user()->preset( c::getPagePiece(3) );
					if( $preset->id_preset ){
						echo json_encode( [ 'id_preset' => $preset->id_preset ] );
						exit;
					}
				}
				echo json_encode( [ 'id_preset' => 0 ] );
				break;
		}
	}
}