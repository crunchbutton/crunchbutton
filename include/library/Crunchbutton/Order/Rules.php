<?php

class Crunchbutton_Order_Rules extends Cana_Model {

	public $_rules = array();
	public $_config = array();

	public function __construct() {

		$this->_rules[ 'gift-card-redeemed' ] = array( 	'method' => 'validation_newGiftCardRedeemed', 
																										'alert' => 'User redeemed two different gift cards in less than %s days. GC: #%s, #%s ',
																										'settings' => array(	'time' => 'rule-time-since-last-order-time', 
																																					'active' => 'rule-time-since-last-order-active', 
																																					'warning-cs' => 'rule-time-since-last-order-cs', 
																																					'warning-rep' => 'rule-time-since-last-order-reps' 
																																				),
																										'title' => 'Gift cards redeemed',
																										'period' => 'days',
																										'action' => 'registerTicket'
																									);

		$this->_rules[ 'time-since-last-order' ] = array(	'method' => 'validation_timeSinceLastOrder_DifferentOrders', 
																											'alert' => 'User placed two orders (same restaurant but with different contents) in less than %s min. Os: #%s, #%s ',
																											'settings' => array(	'time' => 'rule-time-since-last-order-equal-time', 
																																						'active' => 'rule-time-since-last-order-equal-active', 
																																						'warning-cs' => 'rule-time-since-last-order-equal-cs', 
																																						'warning-rep' => 'rule-time-since-last-order-equal-reps' 
																																					),
																											'title' => 'Time since last order (different content)',
																											'period' => 'minutes',
																											'action' => 'registerTicket'
																										);

		$this->_rules[ 'time-since-last-order-equal' ] = array(	'method' => 'validation_timeSinceLastOrder_EqualOrders', 
																														'alert' => 'User ordered same thing two times in less than %s minutes. Os: #%s, #%s ',
																														'settings' => array(	'time' => 'rule-gift-card-redeemed-time', 
																																									'active' => 'rule-gift-card-redeemed-active', 
																																									'warning-cs' => 'rule-gift-card-redeemed-cs', 
																																									'warning-rep' => 'rule-gift-card-redeemed-reps' 
																																								),
																														'title' => 'Time since last order (equal content)',
																														'period' => 'minutes'
																													);

		$this->_rules[ 'monitor-name-phone' ] = array(	'method' => 'validation_monitorNamePhone', 
																														'alert' => 'Monitor alert: The user %s P#%s just ordered some food from %s. O#%s',
																														'settings' => array(	'active' => 'rule-monitor-name-phone-active', 
																																									'name' => 'rule-monitor-name-phone-name', 
																																									'phone' => 'rule-monitor-name-phone-phone', 
																																									'warning-phone' => 'rule-monitor-name-phone-warning-phone', 
																																									'warning-email' => 'rule-monitor-name-phone-warning-email', 
																																								),
																														'title' => 'Monitor name/phone',
																														'action' => 'registerTicket'
																													);
		$this->loadSettings();
	}


	private function loadSettings(){
		$configs = Crunchbutton_Config::q( "SELECT * FROM config WHERE `key` LIKE 'rule%'" );
		foreach ( $configs as $config ) {
			$this->_config[ $config->key ] = $config->value;
		}
	}

	public function getSetting( $key ){
		return $this->_config[ $key ];
	}

	public function rules(){
		return $this->_rules;
	}

	public function createAlert( $string, $array ){
		return call_user_func_array( 'sprintf', array_merge( ( array ) $string, $array ) );
	}

	public function registerTicket( $order, $rule, $result ){

		$message = $this->createAlert( $rule[ 'alert' ], $result );

		$support = new Crunchbutton_Support();
		$support->status = 'open';
		$support->id_user = $order->id_user;
		$support->id_order = $order->id_order;
		$support->id_restaurant = $order->id_restaurant;
		$support->name = $message;
		$support->phone = $order->phone;
		$support->ip = $_SERVER['REMOTE_ADDR'];
		$support->datetime = date('Y-m-d H:i:s');
		$support->save();

		$note = new Support_Note();
		$note->id_support = $support->id_support;
		$note->text = $message;
		$note->from = 'system';
		$note->visibility = 'internal';
		$note->save();

		$message .= ' - ';
		$message .= ' U: ' . $order->name;
		$message .= ' O# ' . $order->id_order;
		$message .= ' P# ' . $order->phone;
		$message .= ' S# ' . $support->id_support;

		$this->notify( $order, $rule, $message );
	}

	public function run( $order ){
		$failed = false;
		$rules = $this->rules();

		foreach( $rules as $key => $rule ){
			$active = ( $this->getSetting( $rule[ 'settings' ][ 'active' ] ) == '1' );
			if( !$active ){ continue; }
			$method = $rule[ 'method' ];
			$result = $this->$method( $order, $rule );

			if( $result ){
				$failed = true;
				$message = $this->createAlert( $rule[ 'alert' ], $result );
				if( $rule[ 'action' ] == 'registerTicket' ){
					$this->registerTicket( $order, $rule, $result );	
				}
				Log::debug( [ 'id_order' => $order->id_order, 'status' => 'failed', 'rule' => $key, 'message' => $message, 'type' => 'order-rules' ] );
			}
		}
		if( !$failed ){
			Log::debug( [ 'id_order' => $order->id_order, 'status' => 'ok', 'description' => "order didn't failed the rules", 'type' => 'order-rules' ] );
		}
	}

	public function notify( $order, $rule, $message ){

		// To avoid send same sms twice
		$send_to = array();

		// Notify custom service
		$warning_cs = ( $this->getSetting( $rule[ 'settings' ][ 'warning-cs' ] ) == '1' );
		if( $warning_cs ){
			foreach (c::config()->text as $supportName => $supportPhone) {
				$phone = $supportPhone;
				$send_to[ $phone ] = $message;
			}			
		}

		// Notify reps with support access
		$warning_rep = ( $this->getSetting( $rule[ 'settings' ][ 'warning-rep' ] ) == '1' );
		if( $warning_rep ){
			$admins = $order->restaurant()->adminWithSupportAccess();
			foreach ( $admins as $admin ) {
				$phone = $admin->txt;
				$send_to[ $phone ] = $message;
				
			}
		}

		foreach( $send_to as $phone => $message ){
			$this->notify_sms( $phone, $message );
		}

	}

	public function notify_sms( $phone, $message ){
		
		$env = c::getEnv();

		$message .= ' E: ' . $env;

		$twilio = new Twilio( c::config()->twilio->{ $env }->sid, c::config()->twilio->{ $env }->token );
		$msgs = str_split( $message, 160 );
		foreach($msgs as $msg) {
			try {
				Log::debug( [ 'action' => 'sending sms - rule failed', 'phone' => $phone, 'msg' => $msg, 'type' => 'order-rules' ] );
				$twilio->account->sms_messages->create(
					c::config()->twilio->{$env}->outgoingTextCustomer,
					'+1'.$phone,
					$msg
				);
			} catch (Exception $e) {
				Log::debug( [ 'action' => 'ERROR!!! sending sms - rule failed', 'phone' => $phone, 'msg' => $msg, 'type' => 'order-rules' ] );
			}
		}
	}

	public function notify_email( $email, $message ){

		$env = c::getEnv();

		$message .= ' E: ' . $env;

		$mail = new Crunchbutton_Email_RulesNotify([
			'message' => $message,
			'subject' => $message,
			'email' => $email
		]);

		$mail->send();
	}

	/* validations */
	private function getLastOrdersFromSameRestaurant( $order ){
		
		$query = "SELECT ( TIME_TO_SEC( TIMEDIFF( last_order.date, o.date ) ) / 60 ) minutes, last_order.id_order last_id_order, o.id_order 
								FROM `order` o 
								INNER JOIN ( SELECT o.* FROM `order` o WHERE o.id_order = {$order->id_order} ) last_order 
								WHERE 
									o.phone = '{$order->phone}' 
									AND 
										o.id_restaurant = {$order->id_restaurant}  AND o.id_order < {$order->id_order}
								ORDER BY o.id_order DESC
								LIMIT 1";
		return c::db()->get( $query )->get(0);
	}

	public function validation_newGiftCardRedeemed( $order, $rule ){

		$giftcars_number = 2;

		if( $order->chargedByCredit() <= 0 ){
			return false;
		}

		// Get last two gift cards
		$giftcards = Promo::getLastGiftCardsRedeemedFromPhoneNumber( $order->phone, $giftcars_number );

		if( $giftcards->count() >= $giftcars_number  ){

			$time = $this->getSetting( $rule[ 'settings' ][ 'time' ] );

			$giftcard_1 = $giftcards->get( 0 );
			$giftcard_2 = $giftcards->get( 1 );

			$datetime1 = new DateTime( $giftcard_1->date );
			$datetime2 = new DateTime( $giftcard_2->date );
			$interval = $datetime1->diff( $datetime2 );
			$days = $interval->format( '%d' );

			if( $days <= $time ){
				return array( $time, $giftcard_1->id_promo, $giftcard_2->id_promo );
			}
		} 
		return false;
	}

	public function validation_timeSinceLastOrder_EqualOrders( $order, $rule ){
		$result = $this->getLastOrdersFromSameRestaurant( $order );
		if( $result ){
			$time = $this->getSetting( $rule[ 'settings' ][ 'time' ] );
			if ( $result->minutes < $time ){
				$order_1 = Order::o( $result->last_id_order );
				$order_2 = Order::o( $result->id_order );
				if( $order_1->orderMessage( 'web' ) == $order_2->orderMessage( 'web' ) ){
					return array( $time, $result->last_id_order, $result->id_order );
				}
			}
		}
		return false; 
	}

	public function validation_timeSinceLastOrder_DifferentOrders( $order, $rule ){
		$result = $this->getLastOrdersFromSameRestaurant( $order );
		if( $result ){
			$time = $this->getSetting( $rule[ 'settings' ][ 'time' ] );
			if ( $result->minutes < $time ){
				$order_1 = Order::o( $result->last_id_order );
				$order_2 = Order::o( $result->id_order );
				if( $order_1->orderMessage( 'web' ) != $order_2->orderMessage( 'web' ) ){
					return array( $time, $result->last_id_order, $result->id_order );
				}
			}
		}
		return false; 
	}

	public function validation_monitorNamePhone( $order, $rule ){
		
		$matches = array();

		// Verify names
		$names = $this->getSetting( $rule[ 'settings' ][ 'name' ] );
		if( $names && trim( $names ) != '' ){
			$names = explode( ',', $names );
			foreach( $names as $name ){
				$name = trim( $name );
				if( strtolower( trim( $order->name ) ) == strtolower( $name ) ){
					$matches[ 'name' ] = $name;
				}
			}
		}

		// Verify phones
		$phones = $this->getSetting( $rule[ 'settings' ][ 'phone' ] );
		if( $phones && trim( $phones ) != '' ){
			$phones = explode( ',', $phones );
			foreach( $phones as $phone ){
				$phone = trim( $phone );
				if( strtolower( trim( $order->phone ) ) == strtolower( $phone ) ){
					$matches[ 'phone' ] = $phone;
				}
			}
		} 

		if( count( $matches ) > 0 ){

			$message = $this->createAlert( $rule[ 'alert' ], array( $order->name, $order->phone, $order->restaurant()->name, $order->id_order ) );

			// Send sms
			$phones = $this->getSetting( $rule[ 'settings' ][ 'warning-phone' ] );
			if( $phones && trim( $phones ) != '' ){
				$phones = explode( ',', $phones );
				foreach( $phones as $phone ){
					$this->notify_sms( $phone, $message );
				}
			}

			// Send email
			$emails = $this->getSetting( $rule[ 'settings' ][ 'warning-email' ] );
			if( $emails && trim( $emails ) != '' ){
				$emails = explode( ',', $emails );
				foreach( $emails as $email ){
					$this->notify_email( $email, $message );
				}
			}
		}
	}
}