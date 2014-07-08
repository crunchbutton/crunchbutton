<?php

class Controller_giftcards_credits extends Crunchbutton_Controller_Account {

	public function init() {

		if (!c::admin()->permission()->check(['global','gift-card-all', 'gift-card-anti-cheat'])) {
			return ;
		}

		$action = c::getPagePiece(2);

		switch ( $action ) {

			case 'byphone':
				$this->byphone();
				break;

			case 'byuser':
				$this->byuser();
				break;

			default:

				$credits_by_phone = Crunchbutton_Credit::antifraudByPhone( 5 );
				$credits_by_user = Crunchbutton_Credit::antifraudByUser( 5 );
				c::view()->page = 'giftcards';
				c::view()->credits_by_phone = $credits_by_phone;
				c::view()->credits_by_user = $credits_by_user;
				c::view()->display('giftcards/credits/index');
				break;
		}
	}

	private function byuser(){
		$id_user = c::getPagePiece(3);
		c::view()->totalCreditsByPhone = Crunchbutton_Credit::totalCreditsByIdUser( $id_user );
		c::view()->totalRefundedCreditsByPhone = Crunchbutton_Credit::totalRefundedCreditsByIdUser( $id_user );
		c::view()->totalDebitsByPhone = Crunchbutton_Credit::totalDebitsByIdUser( $id_user );

		c::view()->credits = Crunchbutton_Credit::creditsByIdUser( $id_user );
		c::view()->user = Crunchbutton_User::o( $id_user );
		c::view()->giftcards = Crunchbutton_Promo::byIdUser( $id_user );
		c::view()->display( 'giftcards/credits/byphone' );
	}

	private function byphone(){
		$phone = c::getPagePiece(3);
		c::view()->totalCreditsByPhone = Crunchbutton_Credit::totalCreditsByPhone( $phone );
		c::view()->totalRefundedCreditsByPhone = Crunchbutton_Credit::totalRefundedCreditsByPhone( $phone );
		c::view()->totalDebitsByPhone = Crunchbutton_Credit::totalDebitsByPhone( $phone );

		c::view()->credits = Crunchbutton_Credit::creditsByPhone( $phone );
		c::view()->user = Crunchbutton_User::byPhone( $phone );
		c::view()->giftcards = Crunchbutton_Promo::byPhone( $phone );
		c::view()->display( 'giftcards/credits/byphone' );
	}
}