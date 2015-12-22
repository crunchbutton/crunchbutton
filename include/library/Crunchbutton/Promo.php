<?php

class Crunchbutton_Promo extends Cana_Table
{

	const CHARS = '123456789ABCDEFGHIJKLMNPQRSTUVWXYZ';
	const NUMBERS = '123456789';

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

	const USABLE_BY_NEW_USERS = 'new-users';
	const USABLE_BY_OLD_USERS = 'old-users';
	const USABLE_BY_ANYONE = 'anyone';

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('promo')
			->idVar('id_promo')
			->load($id);
	}

	public static function byCode( $code ){
		return Crunchbutton_Promo::q( 'SELECT * FROM promo WHERE UPPER( code ) = UPPER(?)', [$code]);
	}

	public static function byPhone( $phone ){
		return Crunchbutton_Promo::q( "SELECT p.* FROM credit c INNER JOIN `user` u ON u.id_user = c.id_user INNER JOIN promo p ON c.id_promo = p.id_promo WHERE u.phone = ? AND c.type = 'CREDIT' AND ( c.credit_type = '" . Crunchbutton_Credit::CREDIT_TYPE_CASH . "' OR c.credit_type != '" . Crunchbutton_Credit::CREDIT_TYPE_POINT . "' )", [$phone]);
	}

	public static function byIdUser( $id_user ){
		return Crunchbutton_Promo::q( "SELECT p.* FROM credit c INNER JOIN `user` u ON u.id_user = c.id_user INNER JOIN promo p ON c.id_promo = p.id_promo WHERE u.id_user = ? AND c.type = 'CREDIT' AND ( c.credit_type = '" . Crunchbutton_Credit::CREDIT_TYPE_CASH . "' OR c.credit_type != '" . Crunchbutton_Credit::CREDIT_TYPE_POINT . "' )", [$id_user]);
	}

	public static function lastID(){
		return Crunchbutton_Promo::q( 'SELECT * FROM promo ORDER BY id_promo DESC LIMIT 1' );
	}

	public static function giftWasAlreadyUsed( $id_promo ){
		$gift = Crunchbutton_Promo::q( 'SELECT * FROM promo p INNER JOIN credit c ON p.id_promo = c.id_promo AND p.id_promo = ?', [$id_promo]);
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
		$result = c::db()->get( 'SELECT COUNT(*) AS total FROM promo WHERE code = ?', [ $rnd_id ] )->get( 0 );
		if( intval( $result->total ) > 0 ){
			return static::promoCodeGeneratorUseChars( $chars, $length, $id_promo, $prefix );
		} else {
			return $rnd_id;
		}
	}

	public static function validateNotesField( $notes, $id_restaurant = false, $phone = false ){
		$return = array();
		$giftcards = array();
		$words = preg_replace( "/(\r\n|\r|\n)+/", ' ', $notes );
		$words = explode( ' ',  $words);
		foreach( $words as $word ){
			$code = preg_replace( '/[^a-zA-Z 0-9]+/', '', $word );
			$giftcard = Crunchbutton_Promo::byCode( $code );
			if( $giftcard->id_promo ){
				$discount_code = $giftcard->isDiscountCode( [ 'id_restaurant' => $id_restaurant, 'phone' => $phone ] )->get( 0 );
				if( $discount_code ){
					if( $discount_code[ 'success' ] ){
						$giftcards[ $giftcard->id_promo ] = $giftcard;
						$notes = str_replace( $code, '', $notes );
					}
					continue;
				} else {
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
		}
		$return[ 'notes' ] = $notes;
		$return[ 'giftcards' ] = $giftcards;
		return $return;
	}

	public function addCredit( $id_user, $delivery_fee = 0, $note = false ){
		$credit = new Crunchbutton_Credit;
		$credit->id_user = $id_user;
		$credit->type = Crunchbutton_Credit::TYPE_CREDIT;
		$credit->id_restaurant = $this->id_restaurant;
		$credit->id_promo = $this->id_promo;
		$credit->date = date('Y-m-d H:i:s');
		if( $this->is_discount_code && $this->delivery_fee ){
			$credit->value = $delivery_fee;
		} else {
			$credit->value = $this->value;
		}
		$credit->credit_type = Crunchbutton_Credit::CREDIT_TYPE_CASH;
		$credit->id_order_reference = $this->id_order_reference;
		$credit->id_restaurant_paid_by = $this->id_restaurant_paid_by;
		$credit->paid_by = $this->paid_by;
		if( !$note ){
			$credit->note = 'Giftcard: ' . $this->id_promo;
		} else {
			$credit->note = $note;
		}

		$credit->save();

		if( !$this->is_discount_code ){
			$this->id_user = $id_user;
			$this->save();
		}

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
			$giftcards = Crunchbutton_Promo::q( 'SELECT * FROM promo WHERE id_promo BETWEEN ? AND ? AND id_promo NOT IN ( SELECT DISTINCT( id_promo ) id_promo FROM credit WHERE id_promo IS NOT NULL ) ORDER BY id_promo ASC', [$id_ini, $id_end]);
			return $giftcards;
		}

		$giftcards = Crunchbutton_Promo::q( 'SELECT * FROM promo WHERE id_promo BETWEEN ? AND ? AND id_promo NOT IN ( SELECT DISTINCT( id_promo ) id_promo FROM credit WHERE id_promo IS NOT NULL ) ORDER BY id_promo', [$id_ini, $id_end]);

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
		return Crunchbutton_Credit::q('
			SELECT * FROM credit
			WHERE
				type = ?
				AND (  credit_type IS NULL OR credit_type = ? OR credit_type != ? )
				AND id_promo = ?
		', [Crunchbutton_Credit::TYPE_CREDIT, Crunchbutton_Credit::CREDIT_TYPE_CASH, Crunchbutton_Credit::CREDIT_TYPE_POINT, $this->id_promo]);
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

				Crunchbutton_Message_Sms::send([
					'to' => $phone,
					'message' => $message,
					'reason' => Crunchbutton_Message_Sms::REASON_GIFT_CARD_REDEEMED
				]);

			}
		}
	}

	public function queNotifySMS() {
		$gift = $this;
		$gift->notifySMS();
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

		$env = c::getEnv();

		if( $env == 'live' ){
			$serverUrl = '_DOMAIN_';
		} else {
			$serverUrl = 'beta.crunchr.co';
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

		$env = c::getEnv();

		$phone = $gift->phone;

		if( !$phone ){
			return false;
		}

		$message = Crunchbutton_Message_Sms::greeting() . "Congrats, you got a gift card to Crunchbutton! Enter code: {$gift->code} in your order notes next time to redeem it.";
		$gift->note = 'SMS sent to ' . $phone . ' at ' . date( 'M jS Y g:i:s A') . "\n" . $gift->note;
		$gift->issued = self::ISSUED_TEXT;
		$gift->save();

		Crunchbutton_Message_Sms::send([
			'to' => $phone,
			'message' => $message,
			'reason' => Crunchbutton_Message_Sms::REASON_GIFT_CARD
		]);
	}

	public function createGiftCard( $value, $params = [] ){
			$giftcard = new Crunchbutton_Promo;
			if( isset( $params[ 'note' ] ) ){
				$giftcard->note = $params[ 'note' ];
			}
			if( isset( $params[ 'issued' ] ) ){
				$giftcard->issued = $params[ 'issued' ];
			}
			if( isset( $params[ 'type' ] ) ){
				$giftcard->type = $params[ 'type' ];
			} else {
				$giftcard->type = 'gift_card';
			}
			$giftcard->paid_by = 'CRUNCHBUTTON';
			$giftcard->code = Crunchbutton_Promo::promoCodeGenerator();
			$giftcard->value = $value;

			$giftcard->active = 1;
			$giftcard->date = date( 'Y-m-d H:i:s' );
			$giftcard->save();
			return $giftcard;
	}

	public static function find($search = []) {

		$query = 'SELECT `promo`.*, user.name FROM `promo` LEFT JOIN restaurant USING(id_restaurant) LEFT OUTER JOIN user USING(id_user) WHERE id_promo IS NOT NULL ';
		$qs = [];

		if ($search['type']) {
			$query .= " and type=? ";
			$qs[] = $search['type'];
		}

		if ($search['start']) {
			$s = new DateTime($search['start']);
			$query .= " and DATE(`date`)>=? ";
			$qs[] = $s->format('Y-m-d');
		}

		if ($search['end']) {
			$s = new DateTime($search['end']);
			$query .= " and DATE(`date`)<=? ";
			$qs[] = $s->format('Y-m-d');
		}

		if ($search['restaurant']) {
			$query .= " and `promo`.id_restaurant=? ";
			$qs[] = $search['restaurant'];
		}

		if ($search['id_user']) {
			$query .= " and `promo`.id_user=? ";
			$qs[] = $search['id_user'];
		}

		$query .= 'ORDER BY `id_promo` DESC';

		if ($search['limit']) {
			$query .= ' limit '.$search['limit'].' ';
		}

		$gifts = self::q($query, $qs);
		return $gifts;
	}

	public function userHasAlreadyUsedDiscountCode( $code, $phone ){

		$phone = trim( str_replace( '-', '', str_replace( ' ', '', $phone ) ) );

		$query = 'SELECT p.* FROM promo p
								INNER JOIN credit c ON c.id_promo = p.id_promo
								INNER JOIN `user` u ON u.id_user = c.id_user
								WHERE u.phone = ? AND LOWER( p.code ) = LOWER( ? ) LIMIT 1';
		$promo = Crunchbutton_Promo::q( $query, [ $phone, $code ] );

		if( $promo->id_promo ){
			return true;
		}
		return false;
	}

	public function isDiscountCode( $params = [] ){

		if( $params[ 'id_restaurant' ] ){
			$id_restaurant = $params[ 'id_restaurant' ];
		} else {
			$id_restaurant = false;
		}

		if( $params[ 'phone' ] ){
			$phone = $params[ 'phone' ];
		} else {
			$phone = false;
		}

		$out = [];

		if( $this->is_discount_code ){

			if( $this->active && $this->date_start && $this->date_end ){

				$start = DateTime::createFromFormat( 'Y-m-d H:i:s', $this->date_start . ' 00:00:01', new DateTimeZone( c::config()->timezone ) );
				$end = DateTime::createFromFormat( 'Y-m-d H:i:s', $this->date_end . ' 23:59:59', new DateTimeZone( c::config()->timezone ) );
				$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );

				if( $start < $now && $now < $end ){

					if( $this->id_community ){
						$community = $this->community();
						if( $id_restaurant ){
							$restaurant = Crunchbutton_Restaurant::o( $id_restaurant );
							$restaurant_community = $restaurant->community();
							if( $restaurant_community->id_community != $this->id_community ){
								$out[ 'error' ] = true;
								$out[ 'warning' ] = 'Sorry, this code is valid just for the restaurants of the community ' . $community->name;
							}
						} else {
							$out[ 'success' ][ 'warning'] = 'Valid just for the restaurants of the community ' . $community->name;
						}
					}

					if( $phone ){
						if( Crunchbutton_Promo::userHasAlreadyUsedDiscountCode( $this->code, $phone ) ){
							$out[ 'error' ] = true;
							$out[ 'warning' ] = 'Sorry, it seems you already used this code before.';
						}
					}

					if( !$out[ 'error' ] ){
						switch ( $this->usable_by ) {
							case Crunchbutton_Promo::USABLE_BY_NEW_USERS:
								if( $phone ){
									$orders = Crunchbutton_Order::totalOrdersByPhone( $phone );
									if( intval( $orders ) > 0 ){
										$out[ 'error' ] = true;
										$out[ 'warning' ] = 'Sorry, this code is valid for new users only.';
									}
								} else {
									if( !$out[ 'success' ] ){ $out[ 'success' ] = []; }
									if( $out[ 'success' ][ 'warning' ] ){
										$out[ 'success' ][ 'warning' ] .= ' and it is valid for new users only.';
									} else {
										$out[ 'success' ][ 'warning' ] = 'Valid for new users only.';
									}
								}
								break;

							case Crunchbutton_Promo::USABLE_BY_OLD_USERS:
								if( $phone ){
									$orders = Crunchbutton_Order::totalOrdersByPhone( $phone );
									if( intval( $orders ) == 0 ){
										$out[ 'error' ] = true;
										$out[ 'warning' ] = 'Sorry, this code is valid for existing users only.';
									}
								} else {
									if( !$out[ 'success' ] ){ $out[ 'success' ] = []; }
									if( $out[ 'success' ][ 'warning' ] ){
										$out[ 'success' ][ 'warning' ] .= ' and it is valid for existing users only.';
									} else {
										$out[ 'success' ][ 'warning' ] = 'Valid for existing users only.';
									}
								}
								break;

							case Crunchbutton_Promo::USABLE_BY_ANYONE:
							default:
								break;
						}
					}

					if( !$out[ 'error' ] ){

						if( !$out[ 'success' ] ){ $out[ 'success' ] = []; }

						if( $this->delivery_fee ){
							$out[ 'success' ][ 'delivery_fee'] = true;
							if( $id_restaurant ){
								$restaurant = Crunchbutton_Restaurant::o( $id_restaurant );
								$value = $restaurant->delivery_fee;
							}
							if( !$value ){
								$value = 3;
							}
							$out[ 'success' ][ 'message' ] = 'Congrats, your delivery fee is on us!';
							// $out[ 'success' ][ 'message' ] = 'You have a $' . $value . ' gift card for a free delivery.';
							$out[ 'success' ][ 'value'] = $value;
						} else {
							$out[ 'success' ][ 'value'] = $this->value;
							$out[ 'success' ][ 'message' ] = 'Congrats! This gift card (' . $this->code . ') gives you $' . $this->value . '.';
						}

						if( $out[ 'success' ][ 'warning' ] ){
							$out[ 'success' ][ 'message' ] .= " " . $out[ 'success' ][ 'warning' ];
							unset( $out[ 'success' ][ 'warning' ] );
						}
					}

				} else {
					$out[ 'error' ] = true;
					$out[ 'warning' ] = 'This code has expired!';
				}

			} else {
				$out[ 'error' ] = true;
				$out[ 'warning' ] = 'This code has expired!';
			}
			if( $out[ 'error' ] ){
				unset( $out[ 'success' ] );
			}
			return $out;
		}
		return false;
	}

	public function community(){
		if( !$this->_community ){
			$this->_community = Crunchbutton_Community::o( $this->id_community );
		}
		return $this->_community;

	}

	public function getLastGiftCardsRedeemedFromPhoneNumber( $phone, $giftcards = 2 ){
		$query = "SELECT c.* FROM credit c
								INNER JOIN `user` u ON u.id_user = c.id_user AND u.phone = ?
								WHERE c.type = 'CREDIT' AND ( c.credit_type = '" . Crunchbutton_Credit::CREDIT_TYPE_CASH . "' OR c.credit_type != '" . Crunchbutton_Credit::CREDIT_TYPE_POINT . "' ) AND c.id_promo IS NOT NULL ORDER BY id_credit DESC limit 0,{$giftcards}";
		return Crunchbutton_Promo::q( $query, [$phone]);
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

	public function admin(){
		return Admin::o( $this->id_admin );
	}

	public function dateStart(){
		if( $this->date_start ){
			return DateTime::createFromFormat( 'Y-m-d H:i:s', $this->date_start . ' 00:00:01', new DateTimeZone( c::config()->timezone ) );
		}
		return null;
	}

	public function dateEnd(){
		if( $this->date_end ){
			return DateTime::createFromFormat( 'Y-m-d H:i:s', $this->date_end . ' 23:59:59', new DateTimeZone( c::config()->timezone ) );
		}
		return null;

	}

	public function date() {
		if (!isset($this->_date)) {
			$this->_date = new DateTime($this->date, new DateTimeZone(c::config()->timezone));
			$this->_date->setTimezone(new DateTimeZone( c::config()->timezone ));
		}
		return $this->_date;
	}
}