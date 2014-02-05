<?php

class Crunchbutton_Admin_Notification extends Cana_Table {

	const TYPE_SMS   = 'sms';
	const TYPE_EMAIL = 'email';
	const TYPE_PHONE = 'phone';
	const TYPE_URL   = 'url';
	const TYPE_FAX   = 'fax';
	const REPS_COCKPIT = 'http://cbtn.io/';

	const IS_ENABLE_KEY = 'notification-admin-is-enable';
	const IS_ENABLE_TO_TAKEOUT_KEY = 'notification-admin-is-enable-takeout';

	const IS_ENABLE_DEFAULT = true;
	const IS_ENABLE_TO_TAKEOUT_DEFAULT = false;

	const REPS_NONE_WORKING_GROUP_NAME_KEY = 'reps-none-working-group-name';

	public function resendNotification(){

		// Get orders with active admin notifications from the last 3 hours, and only if they have been  there for 3 min
		$query = '
			SELECT DISTINCT( o.id_order ), o.*
			FROM `order` o
			INNER JOIN `restaurant` r ON o.id_restaurant = o.id_restaurant
			INNER JOIN `notification` n ON n.id_restaurant = o.id_restaurant AND n.active = 1 AND n.type = "' . Crunchbutton_Notification::TYPE_ADMIN . '" 
			INNER JOIN `admin_notification` an ON an.id_admin = n.id_admin AND an.active = 1
			WHERE
				o.date > DATE_SUB(NOW(), INTERVAL 3 HOUR)
				AND o.date < DATE_SUB(NOW(), INTERVAL 3 MINUTE)
			ORDER BY o.id_order ASC
		';

		$orders = Crunchbutton_Order::q($query);
		echo 'working with '.$orders->count().' orders'."\n";

		if ($orders->count() > 0) {
			foreach ( $orders as $order ) {
				if( !$order->wasAcceptedByRep() ){
					echo '#'.$order->id_order.' was not accepted'."\n";
					// also notify global dispatch
					$this->alertDispatch($order);
					
					Log::debug( [ 'order' => $order->id_order, 'action' => 'resend admin notification', 'type' => 'admin_notification' ]);
					foreach ( $order->restaurant()->notifications() as $n ) {
						Log::debug([ 'order' => $order->id_order, 'action' => 'starting resend notification', 'notification_type' => $n->type, 'env' => c::getEnv(), 'notification_id_admin' => $n->id_admin, 'type' => 'admin_notification']);
						if( $n->type == Crunchbutton_Notification::TYPE_ADMIN ){
							foreach( $n->admin()->activeNotifications() as $adminNotification ){
								$adminNotification->send( $order );
							}
						} 
					}
				} else {
					echo '#'.$order->id_order.' was accepted'."\n";
				}
			}
		}
	}
	
	public function alertDispatch(Crunchbutton_Order $order) {
		$group = Crunchbutton_Group::byName(Config::getVal('rep-fail-group-name'));

		if ($group->id_group) {
			$ags = Crunchbutton_Admin::q('
				SELECT admin.* FROM admin
				LEFT JOIN admin_group using(id_admin)
				WHERE id_group="'.$group->id_group.'"
			');

			$env = c::getEnv();
			$twilio = new Twilio(c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token);
			$message = 'Reps failed to pickup order http://cbtn.io/' . $order->id_order;

			foreach ($ags as $a) {
				// notify each person		
				Log::debug( [ 'order' => $order->id_order, 'action' => 'send rep failed notification to admin', 'num' => $a->txt, 'host' => $_SERVER['HTTP_HOST'], 'message' => $message, 'type' => 'dispatch_notification' ]);

				$twilio->account->sms_messages->create(
					c::config()->twilio->{$env}->outgoingTextRestaurant,
					'+1'.$a->txt,
					$message
				);
			}
		}
	}

	public function send( Crunchbutton_Order $order ) {

		$is_enable = ( !is_null( $this->getSetting( Crunchbutton_Admin_Notification::IS_ENABLE_KEY ) ) ? ( $this->getSetting( Crunchbutton_Admin_Notification::IS_ENABLE_KEY ) == '1' ) : Crunchbutton_Admin_Notification::IS_ENABLE_DEFAULT );
		if( !$is_enable ){
			Log::debug( [ 'order' => $order->id_order, 'action' => 'notification to admin is disabled', 'notification_type' => $this->type, 'value'=> $this->value, 'type' => 'admin_notification' ]);
			return;
		}

		$is_enable_takeout = ( !is_null( $this->getSetting( Crunchbutton_Admin_Notification::IS_ENABLE_TO_TAKEOUT_KEY ) ) ? ( $this->getSetting( Crunchbutton_Admin_Notification::IS_ENABLE_TO_TAKEOUT_KEY ) == '1' ) : Crunchbutton_Admin_Notification::IS_ENABLE_TO_TAKEOUT_DEFAULT );
		if( $order->delivery_type == Crunchbutton_Order::SHIPPING_TAKEOUT && $this->getSetting( Crunchbutton_Admin_Notification::IS_ENABLE_TO_TAKEOUT_KEY ) != '1' ){
			Log::debug( [ 'order' => $order->id_order, 'action' => 'notification to admin to TAKEOUT is disabled', 'notification_type' => $this->type, 'value'=> $this->value, 'type' => 'admin_notification' ]);
			return;
		}

		Log::debug( [ 'order' => $order->id_order, 'action' => 'notification to admin starting', 'notification_type' => $this->type, 'value'=> $this->value, 'type' => 'admin_notification' ]);

		switch ( $this->type ) {
			case Crunchbutton_Admin_Notification::TYPE_FAX :
				$this->sendFax( $order );
				break;

			case Crunchbutton_Admin_Notification::TYPE_SMS :
				$this->sendSms( $order );
				break;

			case Crunchbutton_Admin_Notification::TYPE_PHONE :
				$this->phoneCall( $order );
				break;

			case Crunchbutton_Admin_Notification::TYPE_EMAIL :
				$this->sendEmail( $order );
				break;
		}
	}

	public function warningAboutNoRepsWorking( $order ){
		$env = c::getEnv();
		$group = Group::byName( Crunchbutton_Config::getVal( Crunchbutton_Admin_Notification::REPS_NONE_WORKING_GROUP_NAME_KEY ) );
		$users = $group->users();
		$twilio = new Services_Twilio( c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token );
		$message = "No drivers for O#{$order->id_order} \nR: {$order->restaurant()->name} / {$order->restaurant()->phone()} \nC: {$order->name} / {$order->phone()}";
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
			c::config()->twilio->{$env}->outgoingRestaurant,
			'+1'.$num,
			$url
		);

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
				c::config()->twilio->{$env}->outgoingTextRestaurant,
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