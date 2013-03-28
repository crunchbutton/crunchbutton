<?php

class Controller_api_Giftcard extends Crunchbutton_Controller_Rest {
	public function init() {

		switch ( $this->method() ) {
			
			case 'post':
				
				if ($_SESSION['admin']) {
					switch ( c::getPagePiece( 2 ) ) {
						case 'generate':
							$id_restaurant = $this->request()['id_restaurant'];
							$value = $this->request()['value'];
							$total = $this->request()['total'];
							for( $i = 1; $i<= $total; $i++){
								$giftcard = new Crunchbutton_Promo;
								$giftcard->id_restaurant = $id_restaurant;
								$giftcard->code = $giftcard->promoCodeGenerator();
								$giftcard->value = $value;
								$giftcard->type = Crunchbutton_Promo::TYPE_GIFTCARD;
								$giftcard->date = date('Y-m-d H:i:s');
								$giftcard->save();
							}
							echo json_encode(['success' => 'success']);
							break;
						default:
							# code...
							break;
					}
				}
 				else {
					if ( c::getPagePiece(2) == 'code' ) {
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
 				}
			break;
			default:
				echo json_encode(['error' => 'invalid object']);
			break;
		}
	}
}