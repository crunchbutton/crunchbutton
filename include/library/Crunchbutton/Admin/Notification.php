<?php

class Crunchbutton_Admin_Notification extends Cana_Table {

	const TYPE_SMS = 'sms';
	const TYPE_DUMB_SMS = 'sms-dumb';
	const TYPE_EMAIL = 'email';
	const TYPE_PHONE = 'phone';
	const TYPE_URL = 'url';
	const TYPE_FAX = 'fax';
	const TYPE_PUSH_IOS = 'push-ios';
	const TYPE_PUSH_ANDROID = 'push-android';
	const REPS_COCKPIT = 'https://cockpit.la/';

	const PRIORITY_MESSAGES_INDEX = 0;

	const IS_ENABLE_KEY = 'notification-admin-is-enable';
	const IS_ENABLE_TO_TAKEOUT_KEY = 'notification-admin-is-enable-takeout';

	const IS_ENABLE_DEFAULT = true;
	const IS_ENABLE_TO_TAKEOUT_DEFAULT = false;

	const REPS_NONE_WORKING_GROUP_NAME_KEY = 'reps-none-working-group-name';

	public function resendNotification(){

		$type_admin = Crunchbutton_Notification::TYPE_ADMIN;
		$type_delivery = Crunchbutton_Order::SHIPPING_DELIVERY;
		$orderFromLast = ' 3 HOUR';

		$query = "SELECT * FROM
								`order` o
							WHERE
								o.delivery_type = '{$type_delivery}'
									AND
								o.delivery_service = true
									AND
								o.date > DATE_SUB(NOW(), INTERVAL {$orderFromLast} )
									AND
								o.date < DATE_SUB(NOW(), INTERVAL 5 MINUTE)
									AND
									o.name not like '%test%'

							ORDER BY o.id_order ASC";

		$orders = Crunchbutton_Order::q($query);

		$message = 'working with '.$orders->count().' orders';

		Log::debug( [ 'order' => $order->id_order, 'action' => $message, 'type' => 'delivery-driver' ] );

		echo $message ."\n";

		if ( $orders->count() > 0 ) {

			foreach ( $orders as $order ) {

				if( !$order->wasAcceptedByRep() ){

					$hasDriversWorking = false;

					$attempts = intval( Crunchbutton_Admin_Notification_Log::attempts( $order->id_order ) );

					$message = '#'.$order->id_order.' was not accepted - attempts ' . $attempts;
					Log::debug( [ 'order' => $order->id_order, 'action' => $message, 'type' => 'delivery-driver' ] );
					echo $message."\n";

					if( $attempts > 3 ){
						$message = '#'.$order->id_order.' CS was already texted about it - attempts ' . $attempts;
						Log::debug( [ 'order' => $order->id_order, 'action' => $message, 'type' => 'delivery-driver' ] );
						echo $message."\n";
						continue;
					} else if( $attempts == 3 ){
						// More info: https://github.com/crunchbutton/crunchbutton/issues/2352#issuecomment-34780213
						$this->alertDispatch( $order );
						Crunchbutton_Admin_Notification_Log::register( $order->id_order );
						continue;
					} else {

						Crunchbutton_Admin_Notification_Log::register( $order->id_order );
						$drivers = $order->getDriversToNotify();
						if( $drivers ){
							foreach( $drivers as $driver ){
								foreach( $driver->activeNotifications() as $adminNotification ){
									$adminNotification->send( $order );
									$hasDriversWorking = true;
									$message = '#'.$order->id_order.' attempts: ' . $attempts . ' sending driver notification to ' . $driver->name . ' #' . $adminNotification->value;
									Log::debug( [ 'order' => $order->id_order, 'action' => $message, 'type' => 'delivery-driver' ] );
									echo $message ."\n";
								}
							}
						}

						if( !$hasDriversWorking ){
							$restaurant = $order->restaurant()->name;
							$community = $order->restaurant()->communityNames();
							if( $community != '' ){
								$restaurant .= ' (' . $order->restaurant()->community . ')';
							}
							$message = '#'.$order->id_order.' there is no drivers to get the order - ' . $restaurant;
							Log::debug( [ 'order' => $order->id_order, 'action' => $message, 'type' => 'delivery-driver' ] );
							echo $message."\n";
							// Crunchbutton_Admin_Notification::warningAboutNoRepsWorking( $order );
						}
					}

				} else {
					$message = '#'.$order->id_order.' was accepted';
					Log::debug( [ 'order' => $order->id_order, 'action' => $message, 'type' => 'delivery-driver' ] );
					echo $message."\n";
				}
			}
		}
	}

	public function alertDispatch( Crunchbutton_Order $order ) {

		$env = c::getEnv();
		$twilio = new Twilio(c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token);
		$message = 'Reps failed to pickup order #' . $order->id_order . '. Restaurant ' . $order->restaurant()->name . ' / Customer ' . $order->name . ' https://cockpit.la/' . $order->id_order;

		// Get drivers name
		$drivers = Crunchbutton_Community_Shift::driversCouldDeliveryOrder( $order->id_order );
		if( $drivers ){
			foreach( $drivers as $driver ){
				foreach( $driver->activeNotifications() as $adminNotification ){
					$driversToNotify[ $driver->id_admin ] = $driver->name . ': ' . $driver->phone();
				}
			}
		}
		$drivers_list = "";
		$commas = "";
		foreach( $driversToNotify as $key => $val ){
			$drivers_list .= $commas . $val;
			$commas = "; ";
		}

		$sms_message = Crunchbutton_Message_Sms::greeting() . '#'.$order->id_order.' sms: reps failed to pickup order';
		$sms_message .= "\n";
		$sms_message .= "R: " . $order->restaurant()->name;
		if( $order->restaurant()->community && $order->restaurant()->community != '' ){
			$sms_message .= ' (' . $order->restaurant()->community . ')';
		}
		$sms_message .= "\n";
		$sms_message .= "C: " . $order->name;

		if( $drivers_list != '' ){
			$sms_message .= "\nD: " . $drivers_list;
		}

		// Reps failed to pickup order texts changes #2802
		Crunchbutton_Support::createNewWarning( [ 'id_order' => $order->id_order, 'body' => $sms_message ] );

		Crunchbutton_Message_Sms::send([
			'to' => Crunchbutton_Support::getUsers(),
			'message' => $sms_message,
			'reason' => Crunchbutton_Message_Sms::REASON_SUPPORT_WARNING
		]);

		echo $sms_message."\n";
		Log::debug( [ 'order' => $order->id_order, 'action' => $sms_message, 'num' => $a->txt, 'message' => $sms_message, 'type' => 'delivery-driver' ]);


		/*
		Removed the phone call for while - asked by David 2/23/2014
		$num = $a->getPhoneNumber();
		if( $num ){

			$url = 'http://'.$this->host_callback().'/api/order/'.$order->id_order.'/pick-up-fail';
			$message = '#'.$order->id_order.' call: reps failed to pickup order url: ' . $url;
			$message .= "\n";
			$message .= $order->restaurant()->name;
			if( $order->restaurant()->community && $order->restaurant()->community != '' ){
				$message .= ' (' . $order->restaurant()->community . ')';
			}

			echo $message."\n";
			Log::debug( [ 'order' => $order->id_order, 'action' => $message, 'num' => $num, 'type' => 'delivery-driver' ]);
			$twilio = new Services_Twilio(c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token);
			$call = $twilio->account->calls->create(
				c::config()->twilio->{$env}->outgoingDriver,
				'+1'.$num,
				$url
			);
		}
		*/

	}

	public function sendPriority( Crunchbutton_Order $order ){

		switch ( $this->type ) {

			case Crunchbutton_Admin_Notification::TYPE_SMS:
				$res = $this->sendSms( $order, $this->getSmsMessage($order, static::PRIORITY_MESSAGES_INDEX, 'sms'));
				break;

			case Crunchbutton_Admin_Notification::TYPE_PUSH_IOS:
				$res = $this->sendPushIos( $order, $this->getSmsMessage($order, static::PRIORITY_MESSAGES_INDEX, 'push'));
				break;

			case Crunchbutton_Admin_Notification::TYPE_PUSH_ANDROID:
				$res = $this->sendPushAndroid( $order, $this->getSmsMessage($order, static::PRIORITY_MESSAGES_INDEX, 'push'));
				break;
		}
	}

	public function send( Crunchbutton_Order $order ) {

		$env = c::getEnv();

		$attempts = Crunchbutton_Admin_Notification_Log::attempts( $order->id_order );

		if( $env != 'live' ){
			Log::debug( [ 'order' => $order->id_order, 'action' => 'notification to admin at DEV - not sent', 'notification_type' => $this->type, 'value'=> $this->value, 'attempt' => $attempts, 'type' => 'delivery-driver' ]);
			return;
		}

		$is_enable = ( !is_null( $this->getSetting( Crunchbutton_Admin_Notification::IS_ENABLE_KEY ) ) ? ( $this->getSetting( Crunchbutton_Admin_Notification::IS_ENABLE_KEY ) == '1' ) : Crunchbutton_Admin_Notification::IS_ENABLE_DEFAULT );
		if( !$is_enable ){
			Log::debug( [ 'order' => $order->id_order, 'action' => 'notification to admin is disabled', 'notification_type' => $this->type, 'value'=> $this->value, 'type' => 'delivery-driver' ]);
			return;
		}

		$is_enable_takeout = ( !is_null( $this->getSetting( Crunchbutton_Admin_Notification::IS_ENABLE_TO_TAKEOUT_KEY ) ) ? ( $this->getSetting( Crunchbutton_Admin_Notification::IS_ENABLE_TO_TAKEOUT_KEY ) == '1' ) : Crunchbutton_Admin_Notification::IS_ENABLE_TO_TAKEOUT_DEFAULT );
		if( $order->delivery_type == Crunchbutton_Order::SHIPPING_TAKEOUT && $this->getSetting( Crunchbutton_Admin_Notification::IS_ENABLE_TO_TAKEOUT_KEY ) != '1' ){
			Log::debug( [ 'order' => $order->id_order, 'action' => 'notification to admin to TAKEOUT is disabled', 'notification_type' => $this->type, 'value'=> $this->value, 'type' => 'delivery-driver' ]);
			return;
		}

		Log::debug( [ 'order' => $order->id_order, 'attempts' => $attempts, 'action' => 'notification to admin starting', 'notification_type' => $this->type, 'value'=> $this->value, 'type' => 'delivery-driver' ]);

		if( $attempts == 0 ){
			switch ( $this->type ) {
				case Crunchbutton_Admin_Notification::TYPE_FAX :
					$res = $this->sendFax( $order );
					break;

				case Crunchbutton_Admin_Notification::TYPE_SMS :
					$res = $this->sendSms( $order, $this->getSmsMessage($order, 1, 'sms'));
					break;

				case Crunchbutton_Admin_Notification::TYPE_DUMB_SMS :
					$res = $this->sendDumbSms( $order );
					break;

				case Crunchbutton_Admin_Notification::TYPE_PHONE :
					$res = $this->phoneCall( $order );
					break;

				case Crunchbutton_Admin_Notification::TYPE_EMAIL :
					$res = $this->sendEmail( $order );
					break;

				case Crunchbutton_Admin_Notification::TYPE_PUSH_IOS :
					$res = $this->sendPushIos( $order, $this->getSmsMessage($order, 1, 'push'));
					break;

				case Crunchbutton_Admin_Notification::TYPE_PUSH_ANDROID :
					$res = $this->sendPushAndroid( $order, $this->getSmsMessage($order, 1, 'push'));
					break;
			}
		} else if( $attempts >= 1 ){
			$admin = $this->admin();
			switch ( $attempts ) {
				case 1:
					$shift = Crunchbutton_Community_Shift::currentDriverShift($admin->id_admin);
					if ($shift->id_community_shift) {
						$shiftDateStart = $shift->dateStart(c::config()->timezone);
					}

					if ($shiftDateStart && ($order->date() < $shiftDateStart)) {
						$c = 1;
					} else {
						$c = 2;
					}

					switch ( $this->type ) {

						case Crunchbutton_Admin_Notification::TYPE_PUSH_IOS :
							$res = $this->sendPushIos( $order, $this->getSmsMessage($order, $c, 'push'));
							break;

						case Crunchbutton_Admin_Notification::TYPE_PUSH_ANDROID :
							$res = $this->sendPushAndroid( $order, $this->getSmsMessage($order, $c, 'push'));
							break;

						default:
						case Crunchbutton_Admin_Notification::TYPE_SMS :
							$res = $this->sendSms( $order, $this->getSmsMessage($order, $c, 'sms'));
							break;
					}

					break;
				case 2:
					$phoneNumber = $admin->getPhoneNumber();
					if( $phoneNumber ){
						$call = 'driver-second-call-warning';
						$env = c::getEnv();
						$num = $phoneNumber;
						$url = 'http://'.$this->host_callback().'/api/order/'.$order->id_order.'/'.$call;
						Log::debug( [ 'order' => $order->id_order, 'action' => 'send call to admin', 'num' => $num, 'host' => $this->host_callback(), 'env' => $env, 'url' => $url, 'type' => 'admin_notification' ]);
						$twilio = new Services_Twilio(c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token);
						$call = $twilio->account->calls->create(
							c::config()->twilio->{$env}->outgoingDriver,
							'+1'.$num,
							$url,
							[ 'IfMachine' => 'Hangup' ]
						);
					}
					break;
			}
		}

		return $res;
	}

	public function admin(){
		if( !$this->_admin ){
			$this->_admin = Admin::o( $this->id_admin );
		}
		return $this->_admin;
	}

	public function warningAboutNoRepsWorking( $order ){

		$env = c::getEnv();

		if( $env != 'live' ){
			Log::debug( [ 'order' => $order->id_order, 'action' => 'warningAboutNoRepsWorking at DEV - not sent','type' => 'admin_notification' ]);
			return;
		}

		$group = Group::byName( Crunchbutton_Config::getVal( Crunchbutton_Admin_Notification::REPS_NONE_WORKING_GROUP_NAME_KEY ) );
		$users = Crunchbutton_Admin::q( "SELECT a.* FROM admin a INNER JOIN admin_group ag ON ag.id_admin = a.id_admin AND ag.id_group = {$group->id_group}" );
		$twilio = new Services_Twilio( c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token );

		if( $order->restaurant()->community && $order->restaurant()->community != '' ){
			$community = '(' . $order->restaurant()->community . ') ';
		} else {
			$community = '';
		}

		$message = "No drivers for O#{$order->id_order} \nR: {$order->restaurant()->name} {$community}/ {$order->restaurant()->phone()} \nC: {$order->name} / {$order->phone()}";

		// Make these notifications pop up on support on cockpit #3008
		// Crunchbutton_Support::createNewWarning( [ 'id_order' => $order->id_order, 'body' => $message ] );

		// Crunchbutton_Message_Sms::send([
		// 	'to' => Crunchbutton_Support::getUsers(),
		// 	'message' => $message,
		// 	'reason' => Crunchbutton_Message_Sms::REASON_SUPPORT_WARNING
		// ]);
	}

	public function host_callback(){
		if( !c::config()->host_callback ){
			if( c::getEnv() == 'live' ){
				return '_DOMAIN_';
			} else if( c::getEnv() == 'beta' ){
				return 'beta.crunchr.co';
			} else {
				return $_SERVER['HTTP_HOST'];
			}
		}
		return c::config()->host_callback;
	}

	public function sendFax( Crunchbutton_Order $order ){

		$env = c::getEnv();
		$fax = $this->value;
		$cockpit_url = static::REPS_COCKPIT . $order->id_order;

		Log::debug( [ 'order' => $order->id_order, 'action' => 'send fax to admin', 'fax' => $fax, 'host' => $this->host_callback(), 'type' => 'admin_notification' ]);


		$admin = $this->admin();
		$mail = new Email_Order( [	'order' => $order,
																'cockpit_url' => $cockpit_url,
																'show_credit_card_tips' => $admin->showCreditCardTips(),
																'show_delivery_fees' => $admin->showDeliveryFees(),
															] );

		$temp = tempnam('/tmp','fax');

		file_put_contents($temp, $mail->message());
		rename($temp, $temp.'.html');

		$fax = new Phaxio( [ 'to' => $fax, 'file' => $temp.'.html' ] );

		unlink($temp.'.html');
	}

	public function phoneCall( Crunchbutton_Order $order ){

		$env = c::getEnv();
		$num = $this->value;
		$url = 'http://'.$this->host_callback().'/api/order/'.$order->id_order.'/sayorderadmin';

		Log::debug( [ 'order' => $order->id_order, 'action' => 'send call to admin', 'num' => $num, 'host' => $this->host_callback(), 'env' => $env, 'url' => $url, 'type' => 'admin_notification' ]);

		$twilio = new Services_Twilio(c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token);
		$call = $twilio->account->calls->create(
			c::config()->twilio->{$env}->outgoingDriver,
			'+1'.$num,
			$url,
			[ 'IfMachine' => 'Hangup' ]
		);

	}

	public function sendDumbSms( Crunchbutton_Order $order ){

		$env = c::getEnv();

		$sms = $this->value;

		$admin = $this->admin();
		if( $admin->id_admin ){
			$name = $admin->firstName();
		} else {
			$name = '';
		}

		$message = Crunchbutton_Message_Sms::greeting( $name ) . $order->message( 'sms-driver' );

		Crunchbutton_Message_Sms::send([
			'to' => $sms,
			'from' => 'driver',
			'message' => $message,
			'reason' => Crunchbutton_Message_Sms::REASON_DRIVER_ORDER
		]);
	}

	public function sendSms( Crunchbutton_Order $order, $message){

		$sms = $this->value;

		$ret = Crunchbutton_Message_Sms::send([
			'to' => $sms,
			'from' => 'driver',
			'message' => $message,
			'reason' => Crunchbutton_Message_Sms::REASON_DRIVER_ORDER
		]);

		Log::debug( [ 'order' => $order->id_order, 'action' => 'send sms to admin', 'sms' => $sms, 'message' => $message, 'type' => 'admin_notification' ]);

		return $ret;
	}

	public function sendEmail( Crunchbutton_Order $order ){

		$env = c::getEnv();
		$mail = $this->value;
		$admin = $this->admin();
		Log::debug( [ 'order' => $order->id_order, 'action' => 'send mail to admin', 'mail' => $mail, 'type' => 'admin_notification' ]);
		$cockpit_url = static::REPS_COCKPIT . $order->id_order;

		$mail = new Email_Order( [	'order' => $order,
																'email' => $mail,
																'cockpit_url' => $cockpit_url,
																'show_credit_card_tips' => $admin->showCreditCardTips(),
																'show_delivery_fees' => $admin->showDeliveryFees(),
															] );
		$mail->send();
	}

	public function spellOutURL( $id_order ) {
		$cockpit_url = Crunchbutton_Admin_Notification::REPS_COCKPIT . $id_order;
		$name = preg_replace('/^[0-9]+ (.*)$/i','\\1',$cockpit_url);
		$spaceName = '';

		for ($x=0; $x<strlen($name); $x++) {
			$letter = strtolower($name{$x});
			$addPause = false;
			switch ($letter) {
				case ' ':
				case ',':
				case "\n":
					$addPause = true;
					break;
				case 'c':
					$letter = 'see.';
					break;
				case '.':
					$letter = 'dot.';
					break;
				case ':':
					$letter = 'colon.';
					break;
				case '/':
					$letter = 'slash.';
					break;
				default:
					break;
			}
			if ($addPause) {
				$spaceName .= '<Pause length="1" />';
			}
			$spaceName .= '<Say voice="'.c::config()->twilio->voice.'"><![CDATA['.$letter.']]></Say><Pause length="1" />';
		}
		return $spaceName;
	}

	private function loadSettings(){
		$configs = Crunchbutton_Config::q( "SELECT * FROM config WHERE `key` LIKE 'notification-admin-%'" );
		foreach ( $configs as $config ) {
			$this->_config[ $config->key ] = $config->value;
		}
	}

	public function getSetting( $key ){
		if( !$this->_config ){
			$this->loadSettings();
		}
		return $this->_config[ $key ];
	}

	public function getPendingOrderCount() {
		// @todo: this is not working right
		$orders = Crunchbutton_Order::q('SELECT * FROM `order` o WHERE o.delivery_type = "'.Crunchbutton_Order::SHIPPING_DELIVERY.'" AND o.delivery_service = true AND o.date > DATE_SUB(NOW(), INTERVAL 3 HOUR ) AND o.date < DATE_SUB(NOW(), INTERVAL 5 MINUTE) ORDER BY o.id_order ASC');
		return $orders->count();
	}

	public function sendPushIos($order, $message) {
		$r = Crunchbutton_Message_Push_Ios::send([
			'to' => $this->value,
			'message' => $message,
			'count' => 1,
			'id' => 'order-'.$order->id,
			'category' => 'order-new-test'
		]);

		return $r;
	}

	public function sendPushAndroid($order, $message) {

		$r = Crunchbutton_Message_Push_Android::send([
			'to' => $this->value,
			'message' => $message,
			'title' => 'Cockpit New Order',
			'count' => 1,
			'id' => 'order-'.$order->id
		]);

		return $r;
	}

	public function getSmsMessage($order, $count = 1, $type = 'push') {

		switch ($count) {

			// priority messages
			case static::PRIORITY_MESSAGES_INDEX:
				switch ($type) {
					case 'push':
						$message = '#'.$order->id.': '.$order->user()->name.' has placed an order to '.$order->restaurant()->name.'.';
						$message .= " Sent to YOU 1st. Accept ASAP before others see it!";
						break;
					case 'sms':
						$message = Crunchbutton_Message_Sms::greeting($this->admin()->id_admin ? $this->admin()->firstName() : '');
						$message .= static::REPS_COCKPIT . $order->id_order . "\n";
						$message .= "#: " . $order->phone . "\n";
						$message .= $order->address . "\n";
						$message .= "Sent to YOU 1st. Accept ASAP before others see it!";
					break;
				}
				break;
			default:
			case 1:
				switch ($type) {
					default:
					case 'sms':
						$message = Crunchbutton_Message_Sms::greeting($this->admin()->id_admin ? $this->admin()->firstName() : '');
						$message .= static::REPS_COCKPIT . $order->id_order . "\n";
						$message .= $order->message( 'sms-admin' );
						break;
					case 'push':
						$message = '#'.$order->id.': '.$order->user()->name.' has placed an order to '.$order->restaurant()->name.'.';
						break;
				}

				break;

			case 2:
				switch ($type) {
					default:
					case 'sms':
						$first_name = Crunchbutton_Message_Sms::greeting($this->admin()->firstName());
						$message = $first_name . 'Remember: ACCEPT this order https://cockpit.la/' . $order->id_order . '. Next reminder is a phone call in 3 minutes. Then we\'ll reach out manually, which is not optimal ;)';
						break;
					case 'push':
						$message = 'Please ACCEPT order #'.$order->id.' from '.$order->user()->name.' to '.$order->restaurant()->name.'.';
						break;
				}


				break;
		}
		return $message;
	}

	public function save() {
		if ($this->type == 'sms' || $this->type == 'fax') {
			$this->value = Phone::clean($this->value);
		}
		return parent::save();
	}

	public function __construct($id = null) {
		$this->loadSettings();
		parent::__construct();
		$this
			->table('admin_notification')
			->idVar('id_admin_notification')
			->load($id);
	}
}
