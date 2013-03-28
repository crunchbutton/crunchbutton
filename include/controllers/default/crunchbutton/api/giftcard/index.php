<?php

class Controller_api_Giftcard extends Crunchbutton_Controller_Rest {
	public function init() {

		switch ( $this->method() ) {
			
			case 'post':
				if (c::getPagePiece(2) == 'code') {
					// Get the giftcard (promo) by code
					$giftcard = Crunchbutton_Promo::byCode( $this->request()['code'] );
					// Check if the giftcard is valid
					if( $giftcard->id_promo ){
						// Check if the giftcard was already used
						// if( Crunchbutton_Promo::giftWasAlreadyUsed( $giftcard->id_promo ) ){
						if( false ){
							echo json_encode(['error' => 'gift card already used']);
						} else {
							// Add credit to user
							$credit = $giftcard->addCredit();
							if( $credit->id_credit ){
								echo json_encode( [ 'success' => [ 'value' => $credit->value, 'restaurant' => $credit->restaurant()->name, 'permalink' => $credit->restaurant()->permalink ] ] );
							} else {
								echo json_encode(['error' => 'gift card not added']);
							}
						}
					} else {
						echo json_encode(['error' => 'invalid gift card']);
					}
				}
			break;
			default:
				echo json_encode(['error' => 'invalid object']);
			break;
		}
	}
}