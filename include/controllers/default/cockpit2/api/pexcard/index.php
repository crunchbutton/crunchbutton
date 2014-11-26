<?php

class Controller_Api_PexCard extends Crunchbutton_Controller_RestAccount {

	public function init() {
		switch ( c::getPagePiece( 2 ) ) {
			case 'pex-id':
				$this->_pex_id();
				break;

			default:
				$this->_error();
				break;
		}
	}

	private function _pex_id(){
		$crunchbutton_id = $this->request()[ 'id' ];
		if( $crunchbutton_id ){
			$cards = Crunchbutton_Pexcard_Card::card_list();
			if( is_array( $cards->body ) ){
				foreach( $cards->body as $card ){
					if( $card->lastName == $crunchbutton_id ){
						echo json_encode( $card );exit;
					}
				}
			} else {
				$this->_error( 'Oops, something is wrong!' );
			}
		}
		$this->_error( 'Card Not Found' );
	}

	private function _error( $error = 'invalid request' ){
		echo json_encode( [ 'error' => $error ] );
		exit();
	}
}
