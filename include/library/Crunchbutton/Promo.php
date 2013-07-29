<?php

class Crunchbutton_Promo extends Cana_Table
{

	const TYPE_SHARE = 'user_share';
	const TYPE_GIFTCARD = 'gift_card';

	const ISSUED_CREDIT = 'credit';
	const ISSUED_TEXT = 'text';
	const ISSUED_EMAIL = 'email';
	const ISSUED_PRINT = 'print';

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

	public static function lastID(){
		return Crunchbutton_Promo::q( 'SELECT * FROM promo ORDER BY id_promo DESC LIMIT 1' );
	}

	public static function giftWasAlreadyUsed( $id_promo ){
		$gift = Crunchbutton_Promo::q( 'SELECT * FROM promo p INNER JOIN credit c ON p.id_promo = c.id_promo AND p.id_promo = ' . $id_promo );
		return ( $gift->count() > 0 );
	}

	public static function promoCodeGenerator(){
		$random_id_length = 6; 
		$characters = '123456789ABCDEFGHIJKLMNPQRSTUVWXYZ';
		$rnd_id = '';
		for ($i = 0; $i < $random_id_length; $i++) {
			$rnd_id .= $characters[rand(0, strlen($characters) - 1)];
		}

		// make sure the code do not exist
		$promo = Crunchbutton_Promo::byCode( $rnd_id );
		if( $promo->count() > 0 ){
			return static::promoCodeGenerator();
		} else {
			return $rnd_id;	
		}
	}

	public static function promoCodeGeneratorUseChars( $chars, $length, $id_promo, $prefix ){

		$random_id_length = $length - ( strlen( $id_promo ) + strlen( $prefix ) ); 
		$characters = $chars;
		$rnd_id = $prefix . $id_promo;
		for ($i = 0; $i < $random_id_length; $i++) {
			$rnd_id .= $characters[rand(0, strlen($characters) - 1)];
		}

		// make sure the code do not exist
		$promo = Crunchbutton_Promo::byCode( $rnd_id );
		if( $promo->count() > 0 ){
			return static::promoCodeGeneratorUseChars( $chars, $length, $id_promo, $prefix );
		} else {
			return $rnd_id;	
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

	public function multiple( $ids, $sort = true ){

		// Check if the sting has a dash
		if( strpos( $ids, '-' ) ){
			$ids = explode( '-', $ids );
			$id_ini = $ids[ 0 ];
			$id_end = $ids[ 1 ];
		} else {
			$id_ini = $ids;
			$id_end = $ids;
		}


		if( !$sort ){
			$giftcards = Crunchbutton_Promo::q( 'SELECT * FROM promo WHERE id_promo BETWEEN ' . $id_ini . ' AND ' . $id_end . ' AND id_promo NOT IN ( SELECT DISTINCT( id_promo ) id_promo FROM credit WHERE id_promo IS NOT NULL ) ORDER BY id_promo ASC');
			return $giftcards;
		}

		$giftcards = Crunchbutton_Promo::q( 'SELECT * FROM promo WHERE id_promo BETWEEN ' . $id_ini . ' AND ' . $id_end . ' AND id_promo NOT IN ( SELECT DISTINCT( id_promo ) id_promo FROM credit WHERE id_promo IS NOT NULL ) ORDER BY id_promo');

		$idsArray = array();
		foreach ( $giftcards as $giftcard ) {
			$idsArray[] = $giftcard->id_promo;
		}

		// Change the way it is sorted - Issue #1419
		$giftcardPerPage = 3;
		$totalGifts = sizeof( $idsArray );
		$giftsPerPosition = ceil( $totalGifts / $giftcardPerPage );
		$left = $totalGifts % $giftcardPerPage;
		$perPosition = array();
		$idsOrdered = array();
		if( $left != 0 ){
			for( $i = 0; $i < $giftcardPerPage; $i++ ){
				if( $left > 0 ){
					$perPosition[ $i ] = $giftsPerPosition;
					$left--;
				} else {
					$perPosition[ $i ] = $giftsPerPosition - 1;
				}
			}
		} else {
			for( $i = 0; $i <= $giftcardPerPage; $i++ ){
				$perPosition[ $i ] = $giftsPerPosition ;
			}
		}
		$startsAt = array();
		$sum = 0;
		for( $i = 0; $i < sizeof( $perPosition ); $i ++ ){
			$startsAt[ $i ] = $sum;
			$sum = $sum + $perPosition[ $i ];
		}
		for( $i = 0; $i < $giftsPerPosition; $i++ ){
			for( $j = 1; $j <= $giftcardPerPage; $j++ ){
				if( sizeof( $idsOrdered ) < sizeof( $idsArray ) ){
					$index = $startsAt[ $j - 1 ] + $i;
					$idsOrdered[] = $idsArray[ $index ];
				}
			}
		}
		
		// Remove duplicated - It should not to have duplicated, it is just to make sure!
		array_unique( $idsOrdered );

		// Make sure that all the ids where included
		foreach ( $idsArray as $idArray ) {
			$has = false;
			foreach ( $idsOrdered as $idOrdered ) {
				if( $idArray == $idOrdered ){
					$has = true;
				}
			}
			if( !$has ){
				$idsOrdered[] = $idArray;
			}
		}
		
		$giftcardsArray = array();
		$giftcardsOrdered = array();

		// Convert the interactor to array
		foreach ( $giftcards as $giftcard ) {
			$giftcardsArray[ $giftcard->id_promo ] = $giftcard;
		}

		// Sort
		for( $i = 0; $i < sizeof( $idsOrdered ); $i ++ ){
			$giftcardsOrdered[] = $giftcardsArray[ $idsOrdered[ $i ] ];
		}
		return $giftcardsOrdered;
	}

	public function credit(){
		return Crunchbutton_Credit::q( 'SELECT * FROM credit WHERE type = "' . Crunchbutton_Credit::TYPE_CREDIT . '" AND id_promo = ' . $this->id_promo );
	}

	public function queTrack(){

		$giftcard = $this;

		if( $giftcard->track ){
			c::timeout(function() use($giftcard) {
				$giftcard->trackItSMS();
			}, 1000);
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
		$gift = $this;
		c::timeout(function() use( $gift ) {
			$gift->notifySMS();
		});
	}

	public function queNotifyEMAIL() {
		$gift = $this;
		// c::timeout(function() use($gift) {
			$gift->notifyEMAIL();
		// });
	}

	public function notifyEMAIL() {

		$gift = $this;
		
		Log::debug([
			'action' => 'INSIDE notifyEMAIL cana::timeout',
			'promo_id' => $gift->id_promo,
			'promo_code' => $gift->code,
			'method' => '$promo->notifyEMAIL()',
			'type' => 'promo_email'
		]);

		$env = c::env() == 'live' ? 'live' : 'dev';

		if( $env == 'live' ){
			$serverUrl = '_DOMAIN_';
		} else {
			$serverUrl = 'beta._DOMAIN_';
		}

		$url = 'http://' . $serverUrl . '/giftcard/'. $gift->code;

		$content = $gift->email_content;
		$content = str_replace( static::TAG_GIFT_VALUE , $gift->value, $content );
		$content = str_replace( static::TAG_GIFT_URL , $url, $content );
		$content = str_replace( static::TAG_GIFT_CODE , $gift->code, $content );
		if( $gift->restaurant()->id_restaurant ){
			$content = str_replace( static::TAG_RESTAURANT_NAME , $gift->restaurant()->name, $content );	
		} else {
			$content = str_replace( static::TAG_RESTAURANT_NAME , 'Crunchbutton', $content );	
		}
		
		$content = nl2br( $content );

		$email = $gift->email;
		$subject = $gift->email_subject;

		$mail = new Crunchbutton_Email_Promo([
			'message' => $content,
			'subject' => $subject,
			'email' => $email
		]);
		
		$mail->send();

		$gift->note =  'EMAIL sent to ' . $email . ' at ' . date( 'M jS Y g:i:s A') . "\n" . $gift->note;
		$gift->issued = static::ISSUED_EMAIL;
		$gift->save();
	}

	public function notifySMS() {

		$gift = $this;

		Log::debug([
				'action' => 'INSIDE notifySMS cana::timeout',
				'promo_id' => $gift->id_promo,
				'promo_code' => $gift->code,
				'method' => '$promo->notifySMS()',
				'type' => 'promo_sms'
			]);

		$env = c::env() == 'live' ? 'live' : 'dev';

		$twilio = new Twilio(c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token);
		$phone = $gift->phone;

		if( !$phone ){
			return false;
		}

		$env = c::env() == 'live' ? 'live' : 'dev';

		if( $env == 'live' ){
			$serverUrl = '_DOMAIN_';
		} else {
			$serverUrl = 'beta._DOMAIN_';
		}

		$url = $serverUrl . '/giftcard/'. $gift->code;

		if( $gift->restaurant()->id_restaurant ){
			$message = "Congrats, you got a gift card to {$gift->restaurant()->name}! Enter code: {$gift->code} in your order notes or click here: {$url}"; 
		} else {
			$message = "Congrats, you got a gift card to Crunchbutton! Enter code: {$gift->code} in your order notes or click here: {$url}"; 
		}

		$gift->note = 'SMS sent to ' . $phone . ' at ' . date( 'M jS Y g:i:s A') . "\n" . $gift->note;
		$gift->issued = static::ISSUED_TEXT;
		$gift->save();

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

		$query .= 'ORDER BY `id_promo` DESC';

		if ($search['limit']) {
			$query .= ' limit '.$search['limit'].' ';
		}

		$gifts = self::q($query);
		return $gifts;
	}

	public function groups(){
		return Crunchbutton_Promo_Group::q( "SELECT g.* FROM promo_group g INNER JOIN promo_group_promo pgp ON pgp.id_promo_group = g.id_promo_group AND pgp.id_promo = {$this->id_promo}" );		
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