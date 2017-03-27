<?php

// user bounce back text system #2775
class Cockpit_Bounce_Back extends Cana_Table {

	const RULE_ONE_WEEK_ANNIVERSARY = 'one-week-anniversary';
	const RULE_TWO_WEEKS_ANNIVERSARY = 'two-weeks-anniversary';
	const RULE_NOT_ORDERED_IN_60_DAYS = 'not-ordered-60-days';

	public function __construct($id = null) {
		parent::__construct();
		$this->table( 'bounce_back' )->idVar( 'id_bounce_back' )->load( $id );
	}

	// Text users who have not ordered in 60 days
	public function notOrdered60Days(){
		$pattern = "%s, We miss you! Here's a code for $%d off your next Crunchbutton.com order: %s. Cheers, Judd (CEO)";
		Cockpit_Bounce_Back::startRules( Cockpit_Bounce_Back::RULE_NOT_ORDERED_IN_60_DAYS, 60, 3, $pattern );
	}

	// Second-time users 14 days after their second order if they haven't ordered again:
	public function twoWeeksAnniversary(){
		$pattern = "%s, We miss you! Here's a code for $%d off your next Crunchbutton.com order: %s. Cheers, Judd (CEO)";
		Cockpit_Bounce_Back::startRules( Cockpit_Bounce_Back::RULE_TWO_WEEKS_ANNIVERSARY, 14, 3, $pattern );
	}

	// Text users 1 week after an order if they have not placed an order
	public function oneWeekAnniversary(){
		$pattern = "%s, Happy one-week anniversary! Here's a code for $%d off your next Crunchbutton order: %s. Cheers, Judd (CEO)";
		Cockpit_Bounce_Back::startRules( Cockpit_Bounce_Back::RULE_ONE_WEEK_ANNIVERSARY, 60, 1, $pattern );
	}

	// Generic rulles processor
	public function startRules( $rule, $days, $giftCardValue, $pattern ){
		$giftCardParams = [ 'note' => 'user bounce back system', 'issued' => 'text', 'type' => 'gift_card' ];
		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
		$days_ago = $now->modify( '-' . $days . ' days' )->format( 'Y-m-d' );

		$query = "SELECT DISTINCT( o.phone ) AS phone FROM `order` o WHERE o.date BETWEEN '{$days_ago} 00:00:00' AND '{$days_ago} 23:59:59' AND o.phone NOT IN ( SELECT DISTINCT( o.phone ) FROM `order` o WHERE o.date > '$days_ago 23:59:59' ORDER BY o.id_order )";
		$phones = c::db()->get( $query );

		$data = [];

		foreach( $phones as $phone ){
			if( $phone->phone ){
				$user = Crunchbutton_User::byPhone( $phone->phone );
				if( $user->id_user ){
					$last_order = $user->lastOrder();
					$couldMessageBeSent = Cockpit_Bounce_Back::couldMessageBeSent( $last_order->id_order );
					if( $couldMessageBeSent ){
						$bounce = Cockpit_Bounce_Back::checkBoundWasSent( $user->id_user, $last_order->id_order, $rule );
						if( !$bounce ){
							$giftcard = Crunchbutton_Promo::createGiftCard( $giftCardValue, $giftCardParams );
							if( $giftcard->id_promo ){
								$message = sprintf( $pattern, $user->name, $giftcard->value, $giftcard->code );
								$giftcard->message = $message;
								$giftcard->save();
								$data[] = [ 'name' => $user->name, 'phone' => $user->phone, 'id_user' => $user->id_user, 'message' => $message, 'id_order' => $last_order->id_order, 'rule' => $rule ];
							}
						}
					}
				}
			}
		}

		foreach( $data as $text ){
			Cockpit_Bounce_Back::sendText( $text );
			Cockpit_Bounce_Back::save_log( $text );
		}
	}

	public function couldMessageBeSent( $id_order ){
		// only text during business hours and when the restaurant the customer originally ordered from is open
		$order = Crunchbutton_Order::o( $id_order );
		$restaurant = $order->restaurant();
		$now = new DateTime( 'now', new DateTimeZone( $restaurant->timezone ) );
		if( intval( $now->format( 'H' ) ) > 9 && intval( $now->format( 'H' ) ) < 17 && $restaurant->open() ){
			return true;
		}
		return false;
	}

	public function checkBoundWasSent( $id_user, $id_order, $rule ){
		$bounce = Cockpit_Bounce_Back::q( 'SELECT * FROM bounce_back WHERE id_user = ? AND id_order = ? AND rule = ? ORDER BY id_bounce_back DESC LIMIT 1', [$id_user, $id_order, $rule]);
		if( $bounce->id_bounce_back ){
			return true;
		}
		return false;
	}

	public function sendText( $text ){
		$phone = $text[ 'phone' ];
		$message = $text[ 'message' ];
		$reason = $text[ 'rule' ];
		// Number for tests
		$phone = '_PHONE_';
		return;
		Crunchbutton_Message_Sms::send([
			'to' => $phone,
			'message' => $message,
			'reason' => $reason
		]);
	}

	public function run(){
		Cockpit_Bounce_Back::oneWeekAnniversary();
		Cockpit_Bounce_Back::twoWeeksAnniversary();
		Cockpit_Bounce_Back::notOrdered60Days();
	}

	public function save_log( $params ){
		$bounce = new Cockpit_Bounce_Back;
		$bounce->id_user = $params[ 'id_user' ];
		$bounce->id_order = $params[ 'id_order' ];
		$bounce->rule = $params[ 'rule' ];
		$bounce->phone = $params[ 'phone' ];
		$bounce->message = $params[ 'message' ];
		$bounce->date = date( 'Y-m-d H:i:s' );
		$bounce->save();
		return $bounce;
	}

}