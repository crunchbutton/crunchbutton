<?php

class Crunchbutton_Credit extends Cana_Table
{
	const TYPE_CREDIT = 'CREDIT';
	const TYPE_DEBIT = 'DEBIT';

	const PAID_BY_CRUNCHBUTTON = 'crunchbutton';
	const PAID_BY_RESTAURANT = 'restaurant';
	const PAID_BY_PROMOTIONAL = 'promotional';
	const PAID_BY_OTHER_RESTAURANT = 'other_restaurant';

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
		if (!$id_user) {
			return 0;
		}
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

	public function creditByOrderPaidBy( $id_order, $paid_by ) {
		$query = 'SELECT SUM(`value`) as credit FROM credit WHERE id_order = ' . $id_order . ' AND type = "' . Crunchbutton_Credit::TYPE_DEBIT . '" AND paid_by = "' . $paid_by . '"';
		$row = Cana::db()->get( $query );
		if( $row->_items && $row->_items[0] ){
			if( $row->_items[0]->credit ){
				return $row->_items[0]->credit;
			}
		}
		return 0;
	}

	public function debitHistory(){
		return Crunchbutton_Credit::q('SELECT * FROM credit WHERE id_credit_debited_from="'.$this->id_credit.'"');
	}

	public function calcDebitFromUserCredit( $valueToCharge, $id_user, $id_restaurant ){
		return Crunchbutton_Credit::debitFromUserCredit( $valueToCharge, $id_user, $id_restaurant, 0, true );
	}

	public function debitFromUserCredit( $valueToCharge, $id_user, $id_restaurant, $id_order = 0, $justCalc = false ){
		$credit = Crunchbutton_Credit::creditByUserRestaurant( $id_user, $id_restaurant );
		$totalCharged = 0;
		$charge = $valueToCharge;
		// It means the user has credit
		if( $credit > 0 ){
			$chargeLeft = $charge;
			// Now I need to share the total at the credits availabe
			$credits_available = Crunchbutton_Credit::creditsAvailableByUserRestaurant( $id_user, $id_restaurant );
			$credits_charge = array();
			if( count( $credits_available ) > 0 ){
				// Divide the same amount to each credit
				$charge_divided = Util::ceil( $charge / count( $credits_available ), 2);
				// Because the number is rounded I need to do this verification to not charge more of the user
				// if the $total is more then the $charge i just subtract the excendent from the first charge
				$total = $charge_divided * count( $credits_available );
				$first_charge = false;
				if( $total > $charge ){
					$first_charge = $charge_divided - ( $total - $charge );
				}
				foreach ($credits_available as $credit) {

					if( $first_charge ){
						$chargeOfThisCredit = $first_charge;
						$first_charge = false;
					} else {
						$chargeOfThisCredit = $charge_divided;
					}
					
					// returns how much left of this credit
					$left = $credit->creditLeft();

					// If the left if less than the total do charge, just charge the left.
					if( $left < $chargeOfThisCredit ){
						$chargeOfThisCredit = $left;
					}
					// Update the chargeLeft value
					$chargeLeft = $chargeLeft - $chargeOfThisCredit;
					// Populate the array
					$credits_charge[] = array( 'id_credit' => $credit->id_credit, 'charge' => $chargeOfThisCredit, 'left' => $left, 'credit' => $credit );
				}

				// If there are more to charge, lets use check again if we can use more of the user's credit
				if( $chargeLeft > 0 ){
					foreach( $credits_charge as $key => $value ){
						if( $chargeLeft <= 0 ){
							continue;
						}
						$left = $credits_charge[ $key ][ 'left' ];
						$charge = $credits_charge[ $key ][ 'charge' ];
						if( $charge < $left ){
							if( $left >= ( $charge + $chargeLeft ) ){
								$tryCharge = $charge + $chargeLeft;
								$chargeLeft = 0;
							} else {
								$tryCharge = $left;
								$chargeLeft = $left - $charge;
							}
							$credits_charge[ $key ][ 'charge' ] = $tryCharge;
						}
					}
				}
				// Finally all is calculate, let's debit the credits
				foreach( $credits_charge as $key => $value ){
					$credit = $credits_charge[ $key ][ 'credit' ];
					$charge = $credits_charge[ $key ][ 'charge' ];
					// At the first time I need just the calc, so do not charge for while
					if( !$justCalc ){
						$credit->charge( $charge, $id_order );	
					}
					$totalCharged = $totalCharged + $charge;
				}
			}
		}
		return $totalCharged;
	}

	public function creditSpent(){
		$query = 'SELECT SUM( value ) as spent FROM credit c WHERE id_user = ' . $this->id_user . ' AND type = "DEBIT" AND id_credit_debited_from = ' . $this->id_credit;
		$row = Cana::db()->get( $query );
		if( $row->_items && $row->_items[0] ){
				$row = $row->_items[0];
				return $row->spent;
		}
		return 0;
	}

	public function creditLeft(){
		$spent = $this->creditSpent();
		return $this->value - $spent;
	}

	public function charge( $value, $id_order ){
		$credit = new Crunchbutton_Credit();
		$credit->id_user = c::user()->id_user;
		$credit->type = Crunchbutton_Credit::TYPE_DEBIT;
		$credit->id_restaurant = $this->id_restaurant;
		$credit->date = date('Y-m-d H:i:s');
		$credit->value = $value;
		$credit->id_order = $id_order;
		$credit->paid_by = $this->paid_by;
		$credit->id_restaurant_paid_by = $this->id_restaurant_paid_by;
		$credit->id_credit_debited_from = $this->id_credit;
		$credit->save();
	}

	public function creditsAvailableByUserRestaurant( $id_user, $id_restaurant ){
		$credit_available = array();
		$credits = Crunchbutton_Credit::q('SELECT * FROM credit WHERE type = "' . self::TYPE_CREDIT . '" AND id_restaurant = '.$id_restaurant.' AND id_user="'.$id_user.'"');
		if( $credits->count() > 0 ){
			foreach( $credits as $credit ){
				$left = $credit->creditLeft();
				if( $left > 0 ){
					$credit_available[] = $credit;
				}
			}
		}
		return $credit_available;
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