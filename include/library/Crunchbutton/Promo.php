<?php

class Crunchbutton_Promo extends Cana_Table
{

	const TYPE_SHARE = 'user_share';
	const TYPE_GIFTCARD = 'gift_card';

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

	public static function promoCodeGenerator(){
		$random_id_length = 6; 
		$rnd_id = crypt( uniqid( rand(), 1 ) ); 
		$rnd_id = strip_tags( stripslashes( $rnd_id ) ); 
		$rnd_id = str_replace( '.', '', $rnd_id ); 
		$rnd_id = strrev( str_replace( '/', '', $rnd_id ) ); 
		$rnd_id = substr( $rnd_id, 0, $random_id_length ); 

		// make sure the code do not exist
		$promo = Crunchbutton_Promo::byCode( $rnd_id );
		if( $promo->count() > 0 ){
			return $this->promoCodeGenerator();
		} else {
			return strtolower( $rnd_id );	
		}
	}

	public function addCredit(){
		$credit = new Crunchbutton_Credit();
		$credit->id_user = c::user()->id_user;
		$credit->type = Crunchbutton_Credit::TYPE_CREDIT;
		$credit->id_restaurant = $this->id_restaurant;
		$credit->id_promo = $this->id_promo;
		$credit->date = date('Y-m-d H:i:s');
		$credit->value = $this->value;
		$credit->save();

		$this->id_user = c::user()->id_user;
		$this->save();

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

	public function queNotify() {
		//$promo = $this;
		//Cana::timeout(function() use($promo) {
			$promo->notify();
		//});
	}

	public function notify() {
		
		$env = c::env() == 'live' ? 'live' : 'dev';
		
		$twilio = new Twilio(c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token);
		$phone = $this->phone;

		$url = 'http://' . $_SERVER['SERVER_NAME'] . '/giftcard/'. $this->code;

		// Alpha Delta has a special message
		if( $this->id_restaurant == 1 ){
			$message = "Congrats, you just got a ${$this->value} gift card to {$this->restaurant()->name}. Wenzel away at {$url}.";
		} else {
			$message = "Congrats, you just got a ${$this->value} gift card to {$this->restaurant()->name}. Enjoy: {$url}.";
		}
		
		$this->note = $this->note . 'SMS sent to ' . $phone . ' at ' . date( 'M jS Y g:i:s A') . "\n";
		$this->save();

		$message = str_split($message, 160);
		
		foreach ($message as $msg) {
			$twilio->account->sms_messages->create(
				c::config()->twilio->{$env}->outgoingTextCustomer,
				'+1'.$phone,
				$msg
			);
		}
	}

	public static function find($search = []) {

		$query = 'SELECT `promo`.*, user.name FROM `promo` LEFT JOIN restaurant USING(id_restaurant) LEFT OUTER JOIN user USING(id_user) WHERE id_promo IS NOT NULL ';
		
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
			$query .= ' and `promo`.id_restaurant="'.$search['restaurant'].'" ';
		}

		if ($search['id_user']) {
			$query .= ' and `promo`.id_user="'.$search['id_user'].'" ';
		}

		$query .= 'ORDER BY `date` DESC';

		if ($search['limit']) {
			$query .= ' limit '.$search['limit'].' ';
		}

		$gifts = self::q($query);
		return $gifts;
	}

	public function date() {
		if (!isset($this->_date)) {
			$this->_date = new DateTime($this->date, new DateTimeZone(c::config()->timezone));
			$this->_date->setTimezone(new DateTimeZone( c::config()->timezone ));
		}
		return $this->_date;
	}
}