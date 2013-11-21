<?php

class Crunchbutton_Admin_Notification extends Cana_Table {

	const TYPE_SMS   = 'sms';
	const TYPE_EMAIL = 'email';
	const TYPE_PHONE = 'phone';
	const TYPE_URL   = 'url';
	const TYPE_FAX   = 'fax';
	const REPS_COCKPIT = 'http://cbtn.io/';

	public function send( Crunchbutton_Order $order ) {

		if ($_SESSION['admin'] && c::admin()->testphone) {
			c::config()->twilio->testnumber = c::admin()->testphone;
		}

		Log::debug( [ 'order' => $order->id_order, 'action' => 'notification to admin', 'notification_type' => $this->type, 'type' => 'admin_notification' ]);

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

	public function sendFax( Crunchbutton_Order $order ){

		$env = c::getEnv();
		$fax = $this->value;
		$cockpit_url = static::REPS_COCKPIT . $order->id_order;
		$mail = new Email_Order( [ 'order' => $order, 'cockpit_url' => $cockpit_url  ] );

		$temp = tempnam('/tmp','fax');
		file_put_contents($temp, $mail->message());
		rename($temp, $temp.'.html');

		// Log
		Log::debug( [ 'order' => $order->id_order, 'action' => 'send fax to admin', 'fax' => $fax, 'host' => c::config()->host_callback, 'type' => 'admin_notification' ]);

		$fax = new Phaxio( [ 'to' => $fax, 'file' => $temp.'.html' ] );

		unlink($temp.'.html');
	}

	public function phoneCall( Crunchbutton_Order $order ){

		$env = c::getEnv();
		$num = $this->value;

		// Log
		Log::debug( [ 'order' => $order->id_order, 'action' => 'send call to admin', 'num' => $num, 'host' => c::config()->host_callback, 'type' => 'admin_notification' ]);

		$twilio = new Services_Twilio(c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token);
		$call = $twilio->account->calls->create(
			c::config()->twilio->{$env}->outgoingRestaurant,
			'+1'.$num,
			'http://'.c::config()->host_callback.'/api/order/'.$order->id_order.'/sayorderadmin'
		);

		$log->remote = $call->sid;
		$log->status = $call->status;
		$log->save();
	}

	public function sendSms( Crunchbutton_Order $order ){

		$env = c::getEnv();

		$sms = $this->value;

		$twilio = new Twilio( c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token );
		
		$message = $order->message( 'sms' );
		$message .= ' ' . static::REPS_COCKPIT . $order->id_order;

		$message = str_split( $message , 160 );

		// Log
		Log::debug( [ 'order' => $order->id_order, 'action' => 'send sms to admin', 'num' => $sms, 'host' => c::config()->host_callback, 'message' => join( ' ', $message ), 'type' => 'admin_notification' ]);

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

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('admin_notification')
			->idVar('id_admin_notification')
			->load($id);
	}
}