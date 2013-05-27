<?php

class Crunchbutton_Promo extends Cana_Table
{

	const TYPE_SHARE = 'user_share';
	const TYPE_GIFTCARD = 'gift_card';

	const TAG_GIFT_VALUE = '[gift_value]';
	const TAG_RESTAURANT_NAME = '[restaurant_name]';
	const TAG_GIFT_CODE = '[gift_code]';
	const TAG_GIFT_URL = '[gift_url]';

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
			return strtoupper( $rnd_id );	
		}
	}

	public function validateNotesField( $notes, $id_restaurant = false ){
		$return = array();
		$giftcards = array();
		$words = explode( ' ',  $notes);
		foreach( $words as $word ){
			$code = preg_replace( '/[^a-zA-Z 0-9]+/', '', $word );
			$giftcard = Crunchbutton_Promo::byCode( $code );
			if( $giftcard->id_promo ){
				if( !$giftcard->id_user || ( $giftcard->id_user && $giftcard->id_user == c::user()->id_user ) ){
					if( !Crunchbutton_Promo::giftWasAlreadyUsed( $giftcard->id_promo ) ){
						if( $id_restaurant ){
							if( $id_restaurant == $giftcard->id_restaurant || !$giftcard->id_restaurant ){
								$giftcards[ $giftcard->id_promo ] = $giftcard;	
							}
						} else {
							$giftcards[ $giftcard->id_promo ] = $giftcard;
						}
						// Remove the gift card code from the notes
						$notes = str_replace( $code, '', $notes );
					}
				}
			}
		}
		$return[ 'notes' ] = $notes;
		$return[ 'giftcards' ] = $giftcards;
		return $return;
	}

	public function addCredit( $id_user ){
		$credit = new Crunchbutton_Credit();
		$credit->id_user = $id_user;
		$credit->type = Crunchbutton_Credit::TYPE_CREDIT;
		$credit->id_restaurant = $this->id_restaurant;
		$credit->id_promo = $this->id_promo;
		$credit->date = date('Y-m-d H:i:s');
		$credit->value = $this->value;
		$credit->id_order_reference = $this->id_order_reference;
		$credit->id_restaurant_paid_by = $this->id_restaurant_paid_by;
		$credit->paid_by = $this->paid_by;
		$credit->note = 'Giftcard: ' . $this->id_promo;
		$credit->save();

		$this->id_user = $id_user;
		$this->save();

		if( $credit->id_credit ){
			$this->queTrack();
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

	public function multiple( $ids ){
		return Crunchbutton_Promo::q( 'SELECT * FROM promo WHERE id_promo IN ( ' . $ids . ' )');
	}

	public function credit(){
		return Crunchbutton_Credit::q( 'SELECT * FROM credit WHERE type = "' . Crunchbutton_Credit::TYPE_CREDIT . '" AND id_promo = ' . $this->id_promo );
	}

	public function queTrack(){

		$giftcard = $this;

		if( $giftcard->track ){
			// c::timeout(function() use($giftcard) {
				$giftcard->trackItSMS();
			// }, 1000);
		}
	}

	public function trackItSMS(){

		if( $this->track ){

			if( $this->notify_phone ){
		
				$env = c::env() == 'live' ? 'live' : 'dev';
				
				$twilio = new Twilio(c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token);
				
				$phone = $this->notify_phone;

				$message = 'The gift card you\'ve created was redeemed (' . $this->id_promo . ').';

				if( $this->name ){
					$message .= "\n";
					$message .= "\n";
					$message .= 'Name: ' . $this->name;
				}

				$message .= "\n";
				$message .= "Code: " . $this->code;

				$message .= "\n";
				$message .= "Value: $" . $this->value;

				$this->note = 'Sent a track notification to ' . $phone . ' at ' . date( 'M jS Y g:i:s A') . "\n\n" . $this->note;
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
		}
	}

	public function queNotifySMS() {
		$giftcard = $this;
		Cana::timeout(function() use($giftcard) {
			$giftcard->notifySMS();
		}, 1000); // 1 second
	}

	public function queNotifyEMAIL() {

		$giftcard = $this;

		// c::timeout(function() use($giftcard) {
			$giftcard->notifyEMAIL();
		// }, 1000); // 1 second

	}
	public function notifyEMAIL() {

		Log::debug([
			'action' => 'INSIDE notifyEMAIL cana::timeout',
			'promo_id' => $this->id_promo,
			'promo_code' => $this->code,
			'method' => '$promo->notifyEMAIL()',
			'type' => 'promo_email'
		]);

		$env = c::env() == 'live' ? 'live' : 'dev';

		if( $env == 'live' ){
			$serverUrl = '_DOMAIN_';
		} else {
			$serverUrl = 'beta._DOMAIN_';
		}

		$url = 'http://' . $serverUrl . '/giftcard/'. $this->code;

		$content = $this->email_content;
		$content = str_replace( static::TAG_GIFT_VALUE , $this->value, $content );
		$content = str_replace( static::TAG_GIFT_URL , $url, $content );
		$content = str_replace( static::TAG_GIFT_CODE , $this->code, $content );
		if( $this->restaurant()->id_restaurant ){
			$content = str_replace( static::TAG_RESTAURANT_NAME , $this->restaurant()->name, $content );	
		} else {
			$content = str_replace( static::TAG_RESTAURANT_NAME , 'Crunchbutton', $content );	
		}
		
		$content = nl2br( $content );

		$email = $this->email;
		$subject = $this->email_subject;

		$mail = new Crunchbutton_Email_Promo([
			'message' => $content,
			'subject' => $subject,
			'email' => $email
		]);
		
		$mail->send();

		$this->note =  'EMAIL sent to ' . $email . ' at ' . date( 'M jS Y g:i:s A') . "\n" . $this->note;
		$this->save();
	}

	public function notifySMS() {

		Log::debug([
				'action' => 'INSIDE notifySMS cana::timeout',
				'promo_id' => $this->id_promo,
				'promo_code' => $this->code,
				'method' => '$promo->notifySMS()',
				'type' => 'promo_sms'
			]);

		$env = c::env() == 'live' ? 'live' : 'dev';
		
		$twilio = new Twilio(c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token);
		$phone = $this->phone;

		if( !$phone ){
			return false;
		}

		$env = c::env() == 'live' ? 'live' : 'dev';

		if( $env == 'live' ){
			$serverUrl = '_DOMAIN_';
		} else {
			$serverUrl = 'beta._DOMAIN_';
		}

		$url = $serverUrl . '/giftcard/'. $this->code;

		if( $this->restaurant()->id_restaurant ){
			$message = "Congrats, you got a gift card to {$this->restaurant()->name}! Enter code: {$this->code} in your order notes or click here: {$url}"; 
		} else {
			$message = "Congrats, you got a gift card to Crunchbutton! Enter code: {$this->code} in your order notes or click here: {$url}"; 
		}

		$this->note = 'SMS sent to ' . $phone . ' at ' . date( 'M jS Y g:i:s A') . "\n" . $this->note;
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
	public function order_reference(){
		return Order::o($this->id_order_reference);	
	}

	public function restaurant_paid_by() {
		return Restaurant::o($this->id_restaurant_paid_by);
	}

	public function date() {
		if (!isset($this->_date)) {
			$this->_date = new DateTime($this->date, new DateTimeZone(c::config()->timezone));
			$this->_date->setTimezone(new DateTimeZone( c::config()->timezone ));
		}
		return $this->_date;
	}
}