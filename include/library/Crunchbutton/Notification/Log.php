<?php

class Crunchbutton_Notification_Log extends Cana_Table {

	const MAX_CALL_GROUP_KEY = 'notification-max-call-support-group-name';
	const MAX_CALL_SUPPORT_SAY_KEY = 'notification-max-call-support-say';
	const MAX_CALL_RECALL_AFTER_KEY = 'notification-max-call-recall-after-min';

	public function order() {
		return Order::o($this->id_order);
	}

	public function notificationOrder( $id_order, $id_notification ){
		return self::q('SELECT * FROM notification_log WHERE id_order = "' . $id_order . '" AND id_notification="' . $id_notification . '"')->count();
	}

	public function tries() {
		return self::q('select * from notification_log where id_order="'.$this->id_order.'"')->count();
	}

	public function deleteFromOrder( $id_order ){
		$query = 'DELETE FROM notification_log WHERE id_order = ' . $id_order;
		Cana::dbWrite()->query( $query );
	}

	public function notification() {
		return Notification::o($this->id_notification);
	}

	public function callback() {
		$nl = Notification_Log::q("select * from notification_log where id_order=? and status='callback' and `type`='twilio'", [$this->id_order]);

		if ($nl->count() >= c::config()->twilio->maxcallback) {
			$this->status = 'maxcallbackexceeded';
			$this->save();

			if (c::env() != 'live') {
				return;
			}

			// Issue #1250 - make Max CB a phone call in addition to a text
			$env = c::getEnv();

			$twilio = c::twilio();


			$url = 'https://'.c::config()->host_callback.'/api/order/' . $this->id_order . '/maxcalling';

			Log::debug( [ 'order' => $order->id_order, 'action' => 'MAX CB - starting', 'url' => $url, 'type' => 'notification' ]);

			// c::timeout(function() use( $support, $twilio, $url ) {
				foreach ( Crunchbutton_Support::getUsers() as $supportName => $supportPhone ) {
					// Log
					Log::debug( [ 'order' => $order->id_order, 'action' => 'MAX CB', 'supportPhone' => $supportPhone, 'supportName' => $supportName, 'url' => $url, 'type' => 'notification' ]);

					$call = $twilio->account->calls->create(
						c::config()->twilio->{$env}->outgoingRestaurant,
						'+1'.$supportPhone,
						$url
					);
				}
			// });


			$sendSMSTo = array();
			foreach ( Crunchbutton_Support::getUsers() as $supportName => $supportPhone ) {
				$sendSMSTo[ $supportName ] = $supportPhone;

			}

			// Send SMS to Reps - Issue #2027
			$usersToReceiveSMS = $this->order()->restaurant()->adminReceiveSupportSMS();

			foreach( $usersToReceiveSMS as $user ){
				$sendSMSTo[ $user->name ] = $user->txt;
			}

			$community = $this->order()->restaurant()->communityNames();
			if( $community != '' ){
				$community = '(' . $community . ')';
			}

			// Make these notifications pop up on support on cockpit #3008
			$body = '#'.$this->id_order.' MAX CALLBACK for '.$this->order()->restaurant()->name. $community. "\nR# ".$this->order()->restaurant()->phone().$notifications."\nC# ".$this->order()->phone();
			Crunchbutton_Support::createNewWarning( [ 'id_order' => $this->id_order, 'body' => $body ] );

			// Send SMS to Reps - Issue #2027
			if( count( $sendSMSTo ) > 0 ){

				$restaurant = Restaurant::o( $this->order()->id_restaurant );
				$types = $restaurant->notification_types();

				if( count( $types ) > 0 ){
					$notifications = '/ RN: ' . join( '/', $types );
				} else {
					$notifications = '';
				}

				$community = $this->order()->restaurant()->communityNames();
				if( $community != '' ){
					$community = '(' . $community . ')';
				}

				$message = Crunchbutton_Message_Sms::greeting();
				$message .= '#'.$this->id_order.' MAX CB for '.$this->order()->restaurant()->name.$community."\nR# ".$this->order()->restaurant()->phone(). $notifications . "\n C# ".$this->order()->name . ' / ' . $this->order()->phone();

				$message = str_split($message,160);

				Crunchbutton_Message_Sms::send([
					'to' => $sendSMSTo,
					'message' => $message,
					'reason' => Crunchbutton_Message_Sms::REASON_SUPPORT_WARNING
				]);
			}
/*
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
			*/

		} else {
			$this->queCallback();
		}
	}


	public function getMaxCallNotification( $id_order ){
		return Notification_Log::q( "SELECT * FROm notification_log WHERE id_order ='$id_order' AND type ='maxcall' " );
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

		$nl = Notification_Log::q("select * from notification_log where id_order=? and status='callback' and `type`='confirm'", [$this->id_order]);

		Log::debug( [ 'order' => $this->id_order, 'action' => 'MAX CB - maxconfirmbackexceeded count', 'count' => $nl->count(), 'type' => 'notification' ]);

		if ($nl->count() >= c::config()->twilio->maxconfirmback) {
			$this->status = 'maxconfirmbackexceeded';
			$this->save();

			// Create a new maxcall notification_log
			$log = new Notification_Log();
			$log->status = 'pending';
			$log->type = 'maxcall';
			$log->date = date('Y-m-d H:i:s');
			$log->id_order = $this->id_order;
			$log->save();

			// $this->tellRepsAboutMaxConfirmationCall();

			$sendSMSTo = array();
			foreach ( Crunchbutton_Support::getUsers() as $supportName => $supportPhone ) {
				$sendSMSTo[ $supportName ] = $supportPhone;
			}

			$community = $this->order()->restaurant()->communityNames();
			if( $community != '' ){
				$community = '(' . $community . ')';
			}

			// Make these notifications pop up on support on cockpit #3008
			$body = '#'.$this->id_order.' MAX CONFIRMATION CALL for '.$this->order()->restaurant()->name. $community. "\nR# ".$this->order()->restaurant()->phone().$notifications."\nC# ".$this->order()->phone();
			Crunchbutton_Support::createNewWarning( [ 'id_order' => $this->id_order, 'body' => $body ] );

			// Send SMS to Reps - Issue #2027
			// $usersToReceiveSMS = $this->order()->restaurant()->adminReceiveSupportSMS();

			foreach( $usersToReceiveSMS as $user ){
				$sendSMSTo[ $user->name ] = $user->txt;
			}


			if( count( $sendSMSTo ) > 0 ){

				$restaurant = Restaurant::o( $this->order()->id_restaurant );
				$types = $restaurant->notification_types();
				if( count( $types ) > 0 ){
					$notifications = '/ RN: ' . join( '/', $types );
				} else {
					$notifications = '';
				}

				$community = $this->order()->restaurant()->communityNames();
				if( $community != '' ){
					$community = '(' . $community . ')';
				}

				$message = '#'.$this->id_order.' MAX CONFIRM CB for '.$this->order()->restaurant()->name. $community. "\nR# ".$this->order()->restaurant()->phone().$notifications."\nC# ".$this->order()->phone();

				Crunchbutton_Message_Sms::send([
					'to' => $sendSMSTo,
					'message' => $message,
					'reason' => Crunchbutton_Message_Sms::REASON_SUPPORT_WARNING
				]);
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

	public function maxCallMinutesToWaitBeforeRecall(){
		$say = Config::getVal( Crunchbutton_Notification_Log::MAX_CALL_RECALL_AFTER_KEY );
		if( $say ){
			return $say;
		}
		return 3;
	}

	public function maxCallWasConfirmed(){
		$notification = $this;
		$notification = Notification_Log::getMaxCallNotification( $notification->id_order );
		if( $notification->id_notification_log ){
			Log::debug( [ 'order' => $notification->id_order, 'id_notification_log' => $notification->id_notification_log, 'action' => 'MAX CB - confirmed', 'status' => $notification->status, 'type' => 'notification' ]);
			if( $notification->status != 'success' ){
				$this->tellRepsAboutMaxConfirmationCall();
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
			return Crunchbutton_Admin_Group::q( "SELECT a.* FROM admin a INNER JOIN admin_group ag ON ag.id_admin = a.id_admin AND ag.id_group = {$group->id_group}" );
		}
		return false;
	}

	public function tellRepsAboutMaxConfirmationCall(){

		// Stop Phone Calls for Max CB #3901
		return;

		$env = c::getEnv();
		$twilio = c::twilio();

		$url = 'https://'.c::config()->host_callback.'/api/order/' . $this->id_order . '/maxcalling?id_notification=' . $log->id_notification;

		Log::debug( [ 'order' => $this->id_order, 'action' => 'MAX CB - starting', 'url' => $url, 'callto'=> $support, 'type' => 'notification' ]);

		$users = $this->repsWillReceiveMaxCallWarning();
		foreach( $users as $user ){
			if( !$user->phone ){
				Log::debug( [ 'order' => $this->id_order, 'action' => 'MAX CB - dont have phone', 'user' => $user->name, 'id_user' => $user->id_admin, 'url' => $url, 'type' => 'notification' ]);
				continue;
			}
			$phone = $user->phone;
			Log::debug( [ 'order' => $this->id_order, 'action' => 'MAX CB - calling', 'user' => $user->name, 'id_user' => $user->id_admin, 'phone' => $user->phone, 'url' => $url, 'type' => 'notification' ]);
			$call = $twilio->account->calls->create( c::config()->twilio->{$env}->outgoingRestaurant, '+1'.$phone, $url );
		}

		$timeToWait = $this->maxCallMinutesToWaitBeforeRecall();

		Log::debug( [ 'order' => $this->id_order, 'action' => 'MAX CB - time to recall', 'timeToWait' => $timeToWait, 'type' => 'notification' ]);

		$notification = $this;

		c::timeout(function() use( $notification ) {
			$notification->maxCallWasConfirmed();
		}, $timeToWait * 60 * 1000 );

	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('notification_log')
			->idVar('id_notification_log')
			->load($id);
	}
}
