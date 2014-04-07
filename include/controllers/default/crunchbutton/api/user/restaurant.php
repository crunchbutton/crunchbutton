<?php
class Controller_api_user_restaurant extends Crunchbutton_Controller_Rest {
	public function init() {

		$id_restaurant = c::getPagePiece(3);
		$r = Restaurant::o( $id_restaurant );
		if( $r->id_restaurant ){
			
			$giftcard = false;

			// gift card
			$words = $this->request()['words'];
			$words = explode( ' ', $words );
			$words = array_unique( $words );
			foreach( $words as $word ){
				$word = trim( $word );
				if( $word == '' ){
					continue;
				}
				// Get the giftcard (promo) by code
				$giftcard = Crunchbutton_Promo::byCode( $word );
				// Check if the giftcard is valid
				if( $giftcard->id_promo ){
					// Check if the giftcard was already used
					if( !Crunchbutton_Promo::giftWasAlreadyUsed( $giftcard->id_promo ) ){						
						// It the gift has a user_id just this user will be able to use it
						if( !$giftcard->id_user || ( $giftcard->id_user == c::user()->id_user ) ){
							$giftcard = [ 'success' => [ 'value' => $giftcard->value, 'id_restaurant' => $giftcard->id_restaurant, 'giftcard' => $word, 'restaurant' => $giftcard->restaurant()->name, 'permalink' => $giftcard->restaurant()->permalink ] ];
						}
					}
				} 
			}

			// user preset & credit
			$id_preset = 0;
			$credit = 0;
			if( c::user()->id_user ){
				$preset = c::user()->preset( $id_restaurant );
				if( $preset->id_preset ){
					$id_preset = $preset->id_preset;
				}
				$credit = Crunchbutton_Credit::creditByUserRestaurant( c::user()->id_user, $id_restaurant );
			}
			echo json_encode( [ 'credit' => $credit, 'id_preset' => $id_preset, 'giftcard' => $giftcard ] );	
		} else {
			echo json_encode( [ 'error' => 'invalid object' ] );
		}
	}
}