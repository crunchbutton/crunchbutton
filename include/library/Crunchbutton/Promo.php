<?php

class Crunchbutton_Promo extends Cana_Table
{

	const TYPE_SHARE = 'user_share';

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('promo')
			->idVar('id_promo')
			->load($id);
	}
	public static function byCode( $code ){
		return Crunchbutton_Promo::q( 'SELECT * FROM promo WHERE code = "' . $code . '"' );
	}

	public static function giftWasAlreadyUsed( $id_promo ){
		$gift = Crunchbutton_Promo::q( 'SELECT * FROM promo p INNER JOIN credit c ON p.id_promo = c.id_promo AND p.id_promo = ' . $id_promo );
		return ( $gift->count() > 0 );
	}

	public function addCredit(){
		$credit = new Crunchbutton_Credit();
		$credit->id_user = $this->id_user;
		$credit->type = Crunchbutton_Credit::TYPE_CREDIT;
		$credit->id_restaurant = $this->id_restaurant;
		$credit->id_promo = $this->id_promo;
		$credit->date = date('Y-m-d H:i:s');
		$credit->value = $this->value;
		$credit->save();
		if( $credit->id_credit ){
			return $credit;
		} else {
			return false;
		}
	}

	public function user() {
		return User::o($this->id_user);
	}

	public function restaurant() {
		return Restaurant::o($this->id_restaurant);
	}

}