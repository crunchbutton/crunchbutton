<?php

class Controller_giftcards_credits extends Crunchbutton_Controller_Account {

	public function init() {

		$action = c::getPagePiece(2);

		switch ( $action ) {

			case 'byphone':
				$this->byphone();
				break;

			default:

				$credits = Crunchbutton_Credit::antifraud( 5 );
				c::view()->page = 'giftcards';
				c::view()->credits = $credits;
				c::view()->display('giftcards/credits/index');
				break;
		}
	}

	private function byphone(){
		$phone = c::getPagePiece(3);
		c::view()->user = Crunchbutton_User::byPhone( $phone );
		c::view()->giftcards = Crunchbutton_Promo::byPhone( $phone );
		c::view()->display( 'giftcards/credits/byphone' );
	}
}