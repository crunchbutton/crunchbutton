<?php

class Crunchbutton_Credit extends Cana_Table
{
	const TYPE_CREDIT = 'CREDIT';
	const TYPE_DEBIT = 'DEBIT';

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('credit')
			->idVar('id_credit')
			->load($id);
	}

	public function creditByUser( $id_user ) {
		return Crunchbutton_Credit::q('SELECT * FROM credit WHERE type = "' . self::TYPE_CREDIT . '" AND id_user="'.$id_user.'"');
	}

	public function debitByUser( $id_user ) {
		return Crunchbutton_Credit::q('SELECT * FROM credit WHERE type = "' . self::TYPE_DEBIT . '" AND id_user="'.$id_user.'"');
	}

	public function creditByUserRestaurant( $id_user, $id_restaurant ) {
		$query = 'SELECT SUM(`value`) as credit FROM ( SELECT SUM(`value`) as `value` FROM credit WHERE type = "' . self::TYPE_CREDIT . '" AND id_user = '.$id_user.' AND id_restaurant = ' . $id_restaurant . ' UNION SELECT SUM(`value`) * -1 as `value` FROM credit WHERE type = "' . self::TYPE_DEBIT . '" AND id_user = '.$id_user.' AND id_restaurant = ' . $id_restaurant . ' ) credit';
		$row = Cana::db()->get( $query );
		if( $row->_items && $row->_items[0] ){
			if( $row->_items[0]->credit ){
				return $row->_items[0]->credit;
			}
		}
		return 0;
	}
	
	public function creditByOrder( $id_order ) {
		return Crunchbutton_Credit::q('SELECT * FROM credit WHERE id_order="'.$id_order.'"');
	}

	public function user() {
		return User::o($this->id_user);
	}
	public function userFROM() {
		return User::o($this->id_user_FROM);
	}
	public function order() {
		return Order::o($this->id_user_FROM);
	}
	public function promo() {
		return Promo::o($this->id_promo);
	}
	public function restaurant() {
		return Restaurant::o($this->id_restaurant);
	}
}