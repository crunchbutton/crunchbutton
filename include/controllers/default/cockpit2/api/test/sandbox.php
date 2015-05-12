<?php

class Controller_Api_Test_Sandbox extends Crunchbutton_Controller_Account {

	public function init() {


		echo '<pre>';var_dump(  Crunchbutton_Promo::userHasAlreadyUsedDiscountCode( 'daniel', '***REMOVED***' )  );exit();;

		$giftcards = Crunchbutton_Promo::validateNotesField( 'daniel', 1, '***REMOVED***' );
		foreach ( $giftcards[ 'giftcards' ] as $giftcard ) {
			if( $giftcard->id_promo ){
				echo '<pre>';var_dump( $giftcard->id_promo );exit();
				// if( !$giftCardAdded ){

					echo json_encode( $giftcard->properties() );exit;
					$this->giftcardValue = $giftcard->value;
					$giftCardAdded = true;
					break;
				// }
			}
		}
// echo json_encode( $giftcards );exit;
		// $restaurant = Restaurant::o( 789 );
		// echo '<pre>';var_dump( $restaurant->smartETA( true ) );exit();


	}
}