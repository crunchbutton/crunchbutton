<?php

class Crunchbutton_Order_Rules extends Cana_Model {

	public $_rules = array();

	public function __construct() {
		$this->_rules[ 'five-minutes-since-last-order' ] = array( 'method' => 'validation_fiveMinutesSinceLastOrder', 'alert' => 'The user did two orders in less than 5 minutes. Orders #%s and #%s.' );
		$this->_rules[ 'five-minutes-since-last-order-equal' ] = array( 'method' => 'validation_fiveMinutesSinceLastOrder_EqualOrders', 'alert' => 'The user ordered same thing two times in less than 5 minutes. Orders #%s and #%s.' );
	}

	public function validation_fiveMinutesSinceLastOrder_EqualOrders( $order ){
		$query = "SELECT ( TIME_TO_SEC( TIMEDIFF( last_order.date, o.date ) ) / 60 ) minutes, last_order.id_order last_id_order, o.id_order 
								FROM `order` o 
								INNER JOIN ( SELECT o.* FROM `order` o WHERE o.id_order = {$order->id_order} ) last_order 
								WHERE 
									o.phone = '{$order->phone}' 
									AND 
										o.id_restaurant = {$order->id_restaurant}  AND o.id_order < {$order->id_order}
								ORDER BY o.id_order DESC
								LIMIT 1";

		$result = c::db()->get( $query )->get(0);

		if( $result ){
			if ( $result->minutes < 5 ){
				$order_1 = Order::o( $result->last_id_order );
				$order_2 = Order::o( $result->id_order );
				if( $order_1->orderMessage( 'web' ) == $order_2->orderMessage( 'web' ) ){
					return array( $result->last_id_order, $result->id_order );
				}
			}
		}
		return false; 
	}

	public function validation_fiveMinutesSinceLastOrder( $order ){
		$query = "SELECT ( TIME_TO_SEC( TIMEDIFF( last_order.date, o.date ) ) / 60 ) minutes, last_order.id_order last_id_order, o.id_order 
								FROM `order` o 
								INNER JOIN ( SELECT o.* FROM `order` o WHERE o.id_order = {$order->id_order} ) last_order 
								WHERE 
									o.phone = '{$order->phone}' 
									AND 
										o.id_restaurant = {$order->id_restaurant}  AND o.id_order < {$order->id_order}
								ORDER BY o.id_order DESC
								LIMIT 1";
		$result = c::db()->get( $query )->get(0);
		if( $result ){
			if ( $result->minutes < 5 ){
				return array( $result->last_id_order, $result->id_order );
			}
		}
		return false; 
	}

	public function rules(){
		return $this->_rules;
	}

	public function createAlert( $string, $array ){
		return call_user_func_array( 'sprintf', array_merge( ( array ) $string, $array ) );
	}

	public function registerTicket( $order, $alert ){
/*
		$support = new Crunchbutton_Support();
		$support->status = 'open';
		$support->id_user = $order->id_user;
		$support->id_order = $order->id_order;
		$support->id_restaurant = $order->id_restaurant;
		$support->name = $alert;
		$support->phone = $order->phone;
		$support->ip = $_SERVER['REMOTE_ADDR'];
		$support->datetime = date('Y-m-d H:i:s');
		$support->save();

		$note = new Support_Note();
		$note->id_support = $support->id_support;
		$note->text = $alert;
		$note->from = 'system';
		$note->visibility = 'internal';
		$note->save();

		echo $support->id_support;

		$message = $alert . ' - ';
		$message .= ' U: ' . $order->name;
		$message .= ' O# ' . $order->id_order;
		$message .= ' P# ' . $order->phone;
		$message .= ' S# ' . $support->id_support;

		$this->notify( $order, $message );
*/
	}

	public function run( $order ){
		$failed = false;
		$rules = $this->rules();
		foreach( $rules as $key => $rule ){
			$method = $rule[ 'method' ];
			$result = $this->$method( $order );
			if( $result ){
				$failed = true;
				$alert = $rule[ 'alert' ];
				$message = $this->createAlert( $alert, $result );
				$this->registerTicket( $order, $message );
				Log::debug( [ 'id_order' => $order->id_order, 'status' => 'failed', 'rule' => $key, 'message' => $message, 'type' => 'order-rules' ] );
			}
		}
		if( !$failed ){
			Log::debug( [ 'id_order' => $order->id_order, 'status' => 'ok', 'description' => "order didn't failed the rules", 'type' => 'order-rules' ] );
		}
	}

	public function notify( $order, $message ){
		
		// Notify custom service
		foreach (c::config()->text as $supportName => $supportPhone) {
			$phone = $supportPhone;
			$this->notify_sms( $phone, $message );
		}

		// Notify admin with support access
		$admins = $order->restaurant()->adminWithSupportAccess();
		foreach ( $admins as $admin ) {
			$phone = $admin->txt;
			$this->notify_sms( $phone, $message );
		}
	}

	public function notify_sms( $phone, $message ){
		$env = c::getEnv();
		$twilio = new Twilio( c::config()->twilio->{ $env }->sid, c::config()->twilio->{ $env }->token );
		$msgs = str_split( $message, 160 );
		foreach($msgs as $msg) {
			try {
				Log::debug( [ 'action' => 'sending sms - support', 'session id' => $this->id_session_twilio, 'num' => $num, 'msg' => $msg, 'type' => 'sms' ] );
				$twilio->account->sms_messages->create(
					c::config()->twilio->{$env}->outgoingTextCustomer,
					'+1'.$phone,
					$msg
				);
			} catch (Exception $e) {
				Log::debug( [ 'action' => 'ERROR sending sms - support', 'session id' => $this->id_session_twilio, 'num' => $num, 'msg' => $msg, 'type' => 'sms' ] );
			}
		}
	}
}