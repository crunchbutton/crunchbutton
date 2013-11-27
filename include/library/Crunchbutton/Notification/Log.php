<?php

class Crunchbutton_Notification_Log extends Cana_Table {
	
	const MAX_CALL_GROUP_KEY = 'notification-max-call-support-group-name';
	const MAX_CALL_SUPPORT_SAY_KEY = 'notification-max-call-support-say';
	const MAX_CALL_RECALL_AFTER_KEY = 'notification-max-call-recall-after-min';

	public function order() {
		return Order::o($this->id_order);
	}
	
	public function tries() {
		return self::q('select * from notification_log where id_order="'.$this->id_order.'"')->count();
	}
	
	public function deleteFromOrder( $id_order ){
		$query = 'DELETE FROM notification_log WHERE id_order = ' . $id_order;
		Cana::db()->query( $query );
	}

	public function notification() {
		return Notification::o($this->id_notification);
	}

	public function maxCallMinutesToWaitBeforeRecall(){
		$say = Config::getVal( Crunchbutton_Notification_Log::MAX_CALL_RECALL_AFTER_KEY );
		if( $say ){
			return $say;
		}
		return 3;
	}

	public function maxCallWasConfirmed(){
		$notification = Notification_Log::q( "SELECT * FROM notification_log WHERE type = 'maxcall' AND id_order = {$this->id_notification_log}" );
		if( $notification->id_notification_log ){
			if( $notification->status != 'success' ){
				$this->tellRepsAboutMaxCall();
			}
		} 
		return false;
	}

	public function maxCallMSayAtTheEndOfMessage(){
		$say = Config::getVal( Crunchbutton_Notification_Log::MAX_CALL_SUPPORT_SAY_KEY );
		if( $say ){
			return $say;
		}
		return 'press 1 . to confirm you\'ve received this call. otherwise, we will call you back';
	}

	public function repsWillReceiveMaxCallWarning(){
		$group_name = Config::getVal( Crunchbutton_Notification_Log::MAX_CALL_GROUP_KEY );
		$group = Crunchbutton_Group::byName( $group_name );
		if( $group->id_group ){
			return $group->users();
		}
		return false;
	}

	public function tellRepsAboutMaxCall(){
		// Issue #1250 - make Max CB a phone call in addition to a text
		$env = c::getEnv();
		$twilio = new Services_Twilio(c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token);
		
		// Create a notification_log
		$log = new Notification_Log;
		$log->status = 'pending';
		$log->type = 'maxcall';
		$log->date = date('Y-m-d H:i:s');
		$log->id_order = $this->id_order;
		$log->save();
		
		$url = 'http://'.c::config()->host_callback.'/api/order/' . $this->id_order . '/maxcalling?id_notification=' . $log->id_notification;
		
		Log::debug( [ 'order' => $order->id_order, 'action' => 'MAX CB - starting', 'url' => $url, 'callto'=> $support, 'type' => 'notification' ]);
		
		$users = $this->repsWillReceiveMaxCallWarning();
		foreach( $users as $user ){
			if( !$user->phone ){
				Log::debug( [ 'order' => $order->id_order, 'action' => 'MAX CB - dont have phone', 'user' => $user->name, 'id_user' => $user->id_user, 'url' => $url, 'type' => 'notification' ]);
				continue;
			}
			$phone = $user->phone;
			Log::debug( [ 'order' => $order->id_order, 'action' => 'MAX CB - calling', 'user' => $user->name, 'id_user' => $user->id_user, 'phone' => $user->phone, 'url' => $url, 'type' => 'notification' ]);
			$call = $twilio->account->calls->create( c::config()->twilio->{$env}->outgoingRestaurant, '+1'.$phone, $url );
		}

		$timeToWait = Notification_Log::maxCallMinutesToWaitBeforeRecall();
		c::timeout(function() use( $notification ) {
			$notification->maxCallWasConfirmed();
		}, $timeToWait * 60 * 1000 );

	}

	public function callback() {
		
		$nl = Notification_Log::q('select * from notification_log where id_order="'.$this->id_order.'" and status="callback" and `type`="twilio"');

		Log::debug( [ 'order' => $this->id_order, 'action' => 'MAX CB - maxcallbackexceeded count', 'count' => $nl->count(), 'type' => 'notification' ]);

		if ($nl->count() >= c::config()->twilio->maxcallback) {
			$this->status = 'maxcallbackexceeded';
			$this->save();
// !!! BACK HERE - REMOVE THIS COMMENT			
			// if (c::env() != 'live') {
			// 	return; 
			// }
			
			$this->tellRepsAboutMaxCall();
// !!! BACK HERE - REMOVE THIS RETURN
return;
			$env = c::getEnv();
			
			// Send SMS to Reps - Issue #2027
			$usersToReceiveSMS = $this->order()->restaurant()->adminReceiveSupportSMS();
			if( count( $usersToReceiveSMS ) > 0 ){
				foreach( $usersToReceiveSMS as $user ){
					$sendSMSTo[ $user->name ] = $user->txt;
				}
				$message = '#'.$this->id_order.' MAX CB for '.$this->order()->restaurant()->name."\nR# ".$this->order()->restaurant()->phone()."\n C# ".$this->order()->name . ' / ' . $this->order()->phone();
				$message = str_split($message,160);
				foreach ( $sendSMSTo as $supportName => $supportPhone) {
					$num = $supportPhone;
					foreach ($message as $msg) {
						try {
							// Log
							Log::debug( [ 'action' => 'sending sms MAX CB ', 'to' => $supportName, 'num' => $num, 'msg' => $msg, 'type' => 'max-call' ] );
							$twilio->account->sms_messages->create( c::config()->twilio->{$env}->outgoingTextCustomer, '+1'.$num, $msg );
						} catch (Exception $e) {
							// Log
							Log::debug( [ 'action' => 'ERROR!!! sending sms MAX CB ', 'to' => $supportName, 'num' => $num, 'msg' => $msg, 'type' => 'max-call' ] );
						}
					}
				}
			}

			Log::critical([
				'id_notification_log' => $this->id_notification_log,
				'id_notification' => $this->id_notification,
				'id_restaurant' => $this->order()->restaurant()->id_restaurant,
				'restaurant' => $this->order()->restaurant()->name,
				'restaurant_phone' => $this->order()->restaurant()->phone,
				'customer_phone' => $this->order()->phone,
				'customer_name' => $this->order()->name,
				'action' => '#'.$this->id_order.' MAX CB for '.$this->order()->restaurant()->name."\nR# ".$this->order()->restaurant()->phone()."\n C# ".$this->order()->name . ' / ' . $this->order()->phone(),
				'host' => c::config()->host_callback,
				'type' => 'notification'
			]);

		} else {
			$this->queCallback();
		}
	}
	
	public function queCallback() {
		$log = $this;

		c::timeout(function() use($log) {
			$not = $log->notification();
			$order = $log->order();
			$not->send($order);
		}, c::config()->twilio->callbackTime);		
	}

	public function confirm() {
		$nl = Notification_Log::q('select * from notification_log where id_order="'.$this->id_order.'" and status="callback" and `type`="confirm"');

		if ($nl->count() >= c::config()->twilio->maxconfirmback) {
			$this->status = 'maxconfirmbackexceeded';
			$this->save();
			
			if (c::env() != 'live') {
				return;
			}

			// Issue #1250 - make Max CB a phone call in addition to a text
			$env = c::getEnv();

			$twilio = new Services_Twilio(c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token);

			$support = c::config()->text;

			$url = 'http://'.c::config()->host_callback.'/api/order/' . $this->id_order . '/maxconfirmation';

			Log::debug( [ 'order' => $order->id_order, 'action' => 'MAX CB Calling - starting', 'url' => $url, 'callto'=> $support, 'type' => 'notification' ]);

			foreach ( $support as $supportName => $supportPhone ) {

				// Log
				Log::debug( [ 'order' => $order->id_order, 'action' => 'MAX CONFIRM CB', 'supportPhone' => $supportPhone, 'supportName' => $supportName, 'url' => $url, 'type' => 'notification' ]);

				$call = $twilio->account->calls->create(
					c::config()->twilio->{$env}->outgoingRestaurant,
					'+1'.$supportPhone,
					$url
				);
			}

			// Send SMS to Reps - Issue #2027
			$usersToReceiveSMS = $this->order()->restaurant()->adminReceiveSupportSMS();
			if( count( $usersToReceiveSMS ) > 0 ){
				foreach( $usersToReceiveSMS as $user ){
					$sendSMSTo[ $user->name ] = $user->txt;
				}
				$message = '#'.$this->id_order.' MAX CONFIRM CB for '.$this->order()->restaurant()->name."\nR# ".$this->order()->restaurant()->phone()."\nC# ".$this->order()->phone();
				$message = str_split($message,160);
				foreach ( $sendSMSTo as $supportName => $supportPhone) {
					$num = $supportPhone;
					foreach ($message as $msg) {
						try {
							// Log
							Log::debug( [ 'action' => 'sending sms MAX CONFIRM CB ', 'to' => $supportName, 'num' => $num, 'msg' => $msg, 'type' => 'max-call' ] );
							$twilio->account->sms_messages->create( c::config()->twilio->{$env}->outgoingTextCustomer, '+1'.$num, $msg );
						} catch (Exception $e) {
							// Log
							Log::debug( [ 'action' => 'ERROR!!! sending sms MAX CONFIRM CB ', 'to' => $supportName, 'num' => $num, 'msg' => $msg, 'type' => 'max-call' ] );
						}
					}
				}
			}

			Log::critical([
				'id_order' => $this->id_order, 
				'id_notification_log' => $this->id_notification_log,
				'id_notification' => $this->id_notification,
				'id_restaurant' => $this->order()->restaurant()->id_restaurant,
				'restaurant' => $this->order()->restaurant()->name,
				'restaurant_phone' => $this->order()->restaurant()->phone,
				'customer_phone' => $this->order()->phone,
				'customer_name' => $this->order()->name,
				'action' => '#'.$this->id_order.' MAX CONFIRM CB for '.$this->order()->restaurant()->name."\nR# ".$this->order()->restaurant()->phone()."\nC# ".$this->order()->phone(),
				'host' => c::config()->host_callback,
				'type' => 'notification'
			]);
		} else {

			$this->order()->queConfirm();
		}
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('notification_log')
			->idVar('id_notification_log')
			->load($id);
	}
}