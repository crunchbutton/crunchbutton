<?php

class Crunchbutton_Admin_Notification extends Cana_Table {

	const TYPE_SMS = 'sms';
	const TYPE_DUMB_SMS = 'sms-dumb';
	const TYPE_EMAIL = 'email';
	const TYPE_PHONE = 'phone';
	const TYPE_URL = 'url';
	const TYPE_FAX = 'fax';
	const REPS_COCKPIT = 'http://cbtn.io/';

	const IS_ENABLE_KEY = 'notification-admin-is-enable';
	const IS_ENABLE_TO_TAKEOUT_KEY = 'notification-admin-is-enable-takeout';

	const IS_ENABLE_DEFAULT = true;
	const IS_ENABLE_TO_TAKEOUT_DEFAULT = false;

	const REPS_NONE_WORKING_GROUP_NAME_KEY = 'reps-none-working-group-name';

	public function resendNotification(){

		$type_admin = Crunchbutton_Notification::TYPE_ADMIN;
		$type_delivery = Crunchbutton_Order::SHIPPING_DELIVERY;
		$orderFromLast = ' 3 HOUR';

		$query = "SELECT * FROM `order` o WHERE o.delivery_type = '{$type_delivery}' AND o.delivery_service = 1 AND o.date > DATE_SUB(NOW(), INTERVAL {$orderFromLast} ) AND o.date < DATE_SUB(NOW(), INTERVAL 5 MINUTE) ORDER BY o.id_order ASC";

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
						$driversToNotify = [];

						foreach ( $order->restaurant()->notifications() as $n ) {
							// Admin notification type means it needs a driver
							if( $n->type == Crunchbutton_Notification::TYPE_ADMIN ){
								$admin = $n->admin();
								// Store the drivers
								$driversToNotify[ $admin->id_admin ] = $admin;
							}
						}

						// get the restaurant community and its drivers
						$communities = $order->restaurant()->communities();
						foreach( $communities as $community ){
							if( $community->id_community ){
								$drivers = $community->getDriversOfCommunity();
								foreach( $drivers as $driver ){
									$driversToNotify[ $driver->id_admin ] = $driver;
								}
							}
						}

						// Legacy - lets keep it here for while						
						$community = $order->restaurant()->community;
						if( $community ){
							$group = Crunchbutton_Group::getDeliveryGroupByCommunity( Crunchbutton_Group::driverGroupOfCommunity( $community ) );
							if( $group->id_group ){
								$drivers = Crunchbutton_Admin::q( "SELECT a.* FROM admin a INNER JOIN admin_group ag ON ag.id_admin = a.id_admin AND ag.id_group = {$group->id_group}" );	
								foreach( $drivers as $driver ){
									$driversToNotify[ $driver->id_admin ] = $driver;
								}
							}
						}

						// Send notification to drivers
						if( count( $driversToNotify ) > 0 ){
							foreach( $driversToNotify as $driver ){
								if( $driver->isWorking() ){
									foreach( $driver->activeNotifications() as $adminNotification ){
										$adminNotification->send( $order );
										$hasDriversWorking = true;
										$message = '#'.$order->id_order.' sending notification to ' . $driver->name . ' # ' . $adminNotification->value;
										Log::debug( [ 'order' => $order->id_order, 'action' => $message, 'type' => 'delivery-driver' ] );
										echo $message."\n";
									}	
								}
							}
						}
						Crunchbutton_Admin_Notification_Log::register( $order->id_order );
						if( !$hasDriversWorking ){
							$restaurant = $order->restaurant()->name;
							if( $order->restaurant()->community && $order->restaurant()->community != '' ){
								$restaurant .= ' (' . $order->restaurant()->community . ')';
							} 
							$message = '#'.$order->id_order.' there is no drivers to get the order - ' . $restaurant;
							Log::debug( [ 'order' => $order->id_order, 'action' => $message, 'type' => 'delivery-driver' ] );
							echo $message."\n";
							Crunchbutton_Admin_Notification::warningAboutNoRepsWorking( $order );
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
	
	public function alertDispatch(Crunchbutton_Order $order) {

		$env = c::getEnv();

		if( $env != 'live' ){
			Log::debug( [ 'order' => $order->id_order, 'action' => 'alertDispatch to admin at DEV - not sent', 'notification_type' => $this->type, 'value'=> $this->value, 'type' => 'delivery-driver' ]);
			return;
		}

		$group = Crunchbutton_Group::byName(Config::getVal('rep-fail-group-name'));

		if ($group->id_group) {
			
			$ags = Crunchbutton_Admin::q('SELECT admin.* FROM admin LEFT JOIN admin_group using(id_admin) WHERE id_group="'.$group->id_group.'"');

			$env = c::getEnv();
			$twilio = new Twilio(c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token);
			$message = 'Reps failed to pickup order http://cbtn.io/' . $order->id_order;

			foreach ( $ags as $a ) {
				// notify each person
				$message = '#'.$order->id_order.' sms: reps failed to pickup order';
				$message .= "\n";
				$message .= $order->restaurant()->name;
				if( $order->restaurant()->community && $order->restaurant()->community != '' ){
					$message .= ' (' . $order->restaurant()->community . ')';
				} 
				echo $message."\n";
				Log::debug( [ 'order' => $order->id_order, 'action' => $message, 'num' => $a->txt, 'message' => $message, 'type' => 'delivery-driver' ]);
				$twilio->account->sms_messages->create( c::config()->twilio->{$env}->outgoingTextDriver, '+1'.$a->txt, $message );

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
					/* 
					Removed the phone call for while - asked by David 2/23/2014
					$twilio = new Services_Twilio(c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token);
					$call = $twilio->account->calls->create(
						c::config()->twilio->{$env}->outgoingDriver,
						'+1'.$num,
						$url
					);	*/
				}
			}
		}
	}

	public function send( Crunchbutton_Order $order ) {

		$env = c::getEnv();

		if( $env != 'live' ){
			Log::debug( [ 'order' => $order->id_order, 'action' => 'notification to admin at DEV - not sent', 'notification_type' => $this->type, 'value'=> $this->value, 'type' => 'delivery-driver' ]);
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

		$attempts = Crunchbutton_Admin_Notification_Log::attempts( $order->id_order );

		Log::debug( [ 'order' => $order->id_order, 'attempts' => $attempts, 'action' => 'notification to admin starting', 'notification_type' => $this->type, 'value'=> $this->value, 'type' => 'delivery-driver' ]);

		// Send the regular notification just at the first time
		// More info: https://github.com/crunchbutton/crunchbutton/issues/2352#issuecomment-34780213
		if( $attempts == 0 ){
			switch ( $this->type ) {
				case Crunchbutton_Admin_Notification::TYPE_FAX :
					$this->sendFax( $order );
					break;

				case Crunchbutton_Admin_Notification::TYPE_SMS :
					$this->sendSms( $order );
					break;

				case Crunchbutton_Admin_Notification::TYPE_DUMB_SMS :
					$this->sendDumbSms( $order );
					break;

				case Crunchbutton_Admin_Notification::TYPE_PHONE :
					$this->phoneCall( $order );
					break;

				case Crunchbutton_Admin_Notification::TYPE_EMAIL :
					$this->sendEmail( $order );
					break;
			}
		} else if( $attempts >= 1 ){
			// Send the phone call
			$admin = $this->admin();
			$phoneNumber = $admin->getPhoneNumber();
			if( $phoneNumber ){
				$call = '';
				switch ( $attempts ) {
					case 1:
						$call = 'driver-first-call-warning';
						break;
					case 2:
						$call = 'driver-second-call-warning';
						break;
				}
				if( $call != '' ){
					$env = c::getEnv();
					$num = $phoneNumber;
					$url = 'http://'.$this->host_callback().'/api/order/'.$order->id_order.'/'.$call;
					Log::debug( [ 'order' => $order->id_order, 'action' => 'send call to admin', 'num' => $num, 'host' => $this->host_callback(), 'env' => $env, 'url' => $url, 'type' => 'admin_notification' ]);
					$twilio = new Services_Twilio(c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token);
					$call = $twilio->account->calls->create(
						c::config()->twilio->{$env}->outgoingDriver,
						'+1'.$num,
						$url
					);
				}
			}
		}
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
		$message = str_split( $message,160 );
		foreach ( $users as $user ) {
			$num = $user->txt;
			$name = $user->name;
			foreach ($message as $msg) {
				try {
					// Log
					Log::debug( [ 'action' => 'Sending no reps working warning ', 'to' => $name, 'num' => $num, 'msg' => $msg, 'type' => 'notification' ] );
					$twilio->account->sms_messages->create( c::config()->twilio->{$env}->outgoingTextCustomer, '+1'.$num, $msg );
				} catch (Exception $e) {
					// Log
					Log::debug( [ 'action' => 'ERROR!!! Sending no reps working warning ', 'to' => $name, 'num' => $num, 'msg' => $msg, 'type' => 'notification' ] );
				}
			}
		}
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

		$mail = new Email_Order( [ 'order' => $order, 'cockpit_url' => $cockpit_url  ] );
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
			$url
		);

	}

	public function sendDumbSms( Crunchbutton_Order $order ){

		$env = c::getEnv();

		$sms = $this->value;

		$twilio = new Twilio( c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token );
		
		$message .= $order->message( 'sms-driver' );

		$message = str_split( $message , 160 );

		Log::debug( [ 'order' => $order->id_order, 'action' => 'send dumb sms to admin', 'num' => $sms, 'host' => $this->host_callback(), 'message' => join( ' ', $message ), 'type' => 'admin_notification' ]);

		foreach ($message as $msg) {
			$twilio->account->sms_messages->create(
				c::config()->twilio->{$env}->outgoingTextDriver,
				'+1'.$sms,
				$msg
			);
			continue;
		}

	}

	public function sendSms( Crunchbutton_Order $order ){

		$env = c::getEnv();

		$sms = $this->value;

		$twilio = new Twilio( c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token );
		
		$message = static::REPS_COCKPIT . $order->id_order;
		$message .= "\n";
		$message .= $order->message( 'sms-admin' );

		$message = str_split( $message , 160 );

		Log::debug( [ 'order' => $order->id_order, 'action' => 'send sms to admin', 'num' => $sms, 'host' => $this->host_callback(), 'message' => join( ' ', $message ), 'type' => 'admin_notification' ]);

		foreach ($message as $msg) {
			$twilio->account->sms_messages->create(
				c::config()->twilio->{$env}->outgoingTextDriver,
				'+1'.$sms,
				$msg
			);
			continue;
		}

	}

	public function sendEmail( Crunchbutton_Order $order ){

		$env = c::getEnv();
		$mail = $this->value;
		Log::debug( [ 'order' => $order->id_order, 'action' => 'send mail to admin', 'mail' => $mail, 'type' => 'admin_notification' ]);
		$cockpit_url = static::REPS_COCKPIT . $order->id_order;

		$mail = new Email_Order( [	'order' => $order, 
																'email' => $mail,
																'cockpit_url' => $cockpit_url 
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

	public function __construct($id = null) {
		$this->loadSettings();
		parent::__construct();
		$this
			->table('admin_notification')
			->idVar('id_admin_notification')
			->load($id);
	}
}