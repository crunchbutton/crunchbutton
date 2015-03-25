<?php

ini_set('memory_limit', '-1');

class Controller_Api_Script_GiftCards extends Crunchbutton_Controller_RestAccount {

	public function init() {

		echo '<pre>';
		echo 'Started at: ' . date( 'Y-m-d H:i:s' );
		echo "\n";
		echo "\n";

		$ids_restaurant = ['*'];
		$value = ( $_GET[ 'value' ] ? $_GET[ 'value' ] : 1 );
		$total = ( $_GET[ 'total' ] ? $_GET[ 'total' ] : 1 );
		$note = '#5100';
		$id_order_reference = null;
		$paid_by = 'crunchbutton';
		$created_by = 'daniel';
		$chars_to_use = '123456789';
		$length = 7;
		$prefix = '';
		$include_gift_card_id = true;

		for( $i = 1; $i<= $total; $i++) {
			$giftcard = new Crunchbutton_Promo;
			$giftcard->note = '';
			$giftcard->value = $value;
			$giftcard->type = Crunchbutton_Promo::TYPE_GIFTCARD;
			$giftcard->note = $note;
			$giftcard->message = 'Gift Card';
			$giftcard->active = 1;
			$giftcard->created_by = $created_by;
			$giftcard->paid_by = $paid_by;
			$giftcard->date = date('Y-m-d H:i:s');
			if( $include_gift_card_id ){
				$giftcard->save();
				$giftcard->code = $giftcard->promoCodeGeneratorUseChars( $chars_to_use, $length, $giftcard->id_promo, $prefix );
			} else {
				$giftcard->code = $giftcard->promoCodeGeneratorUseChars( $chars_to_use, $length, '', $prefix );
			}
			$giftcard->save();
		}

		echo 'Finished at: ' . date( 'Y-m-d H:i:s' );
		echo "\n";
		echo "\n";
		echo $total;
		echo '<script>alert( "finished" )</script>';

	}
}