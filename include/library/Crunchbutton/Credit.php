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
	
	public static function find($search = []) {

		$query = 'SELECT `credit`.* FROM `credit` LEFT JOIN restaurant USING(id_restaurant) WHERE id_credit IS NOT NULL ';
		
		if ($search['type']) {
			$query .= ' and type="'.$search['type'].'" ';
		}
		
		if ($search['start']) {
			$s = new DateTime($search['start']);
			$query .= ' and DATE(`date`)>="'.$s->format('Y-m-d').'" ';
		}
		
		if ($search['end']) {
			$s = new DateTime($search['end']);
			$query .= ' and DATE(`date`)<="'.$s->format('Y-m-d').'" ';
		}

		if ($search['restaurant']) {
			$query .= ' and `credit`.id_restaurant="'.$search['restaurant'].'" ';
		}

		if ($search['id_order']) {
			$query .= ' and `credit`.id_order="'.$search['id_order'].'" ';
		}

		if ($search['id_user']) {
			$query .= ' and `credit`.id_user="'.$search['id_user'].'" ';
		}

		$query .= 'ORDER BY `date` DESC';

		if ($search['limit']) {
			$query .= ' limit '.$search['limit'].' ';
		}

		$credits = self::q($query);
		return $credits;
	}

	public function date() {
		if (!isset($this->_date)) {
			$this->_date = new DateTime($this->date, new DateTimeZone(c::config()->timezone));
			$this->_date->setTimezone(new DateTimeZone( c::config()->timezone ));
		}
		return $this->_date;
	}

	public function creditByOrder( $id_order ) {
		return Crunchbutton_Credit::q('SELECT * FROM credit WHERE id_order="'.$id_order.'"');
	}

	public function user() {
		return User::o($this->id_user);
	}

	public function userFROM() {
		return User::o($this->id_user_from);
	}

	public function order() {
		return Order::o($this->id_order);
	}

	public function order_reference(){
		return Order::o($this->id_order_reference);	
	}

	public function promo() {
		return Promo::o($this->id_promo);
	}

	public function restaurant() {
		return Restaurant::o($this->id_restaurant);
	}

	public function restaurant_paid_by() {
		return Restaurant::o($this->id_restaurant_paid_by);
	}

	

}