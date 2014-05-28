<?php

class Cockpit_Driver_Notify extends Cana_Table {

	const TYPE_SETUP = 'setup';
	const TYPE_WELCOME = 'welcome';
	const ORDER_TEST = '22890'; // id_order sent to drivers play with - Issue #2969 - step 3
	
	public function send( $id_admin, $message ){

		$driver = Crunchbutton_Admin::o( $id_admin );

		if( !$driver->id_admin ){
			return [ 'error' => 'invalid user' ];
		}

		$phone = $driver->phone();

		$phone = str_replace( '-' , '', $phone );

		if( trim( $phone ) == '' ){
			return [ 'error' => 'invalid phone' ];
		}

		Log::debug( [ 'action' => 'notification starting', 'driver' => $id_admin, 'phone' => $phone, 'message' => $message, 'type' => 'drivers-onboarding'] );

		$username = $driver->login;

		// Pre defined messages
		switch ( $message ) {
			case Cockpit_Driver_Notify::TYPE_WELCOME:
				$message_type = Cockpit_Driver_Notify::TYPE_WELCOME;
				$message = "You username is {$username}. Access cockpit.la/setup/{$phone}";
				break;
			
			case Cockpit_Driver_Notify::TYPE_SETUP:
				$message_type = Cockpit_Driver_Notify::TYPE_SETUP;
				$message = 'Test this URL out on your phone (exactly as it appears, no www.) cockpit.la/' . Cockpit_Driver_Notify::ORDER_TEST . '. Play around with it and make sure you understand how everything works';
				$message .="\n" . 'If you have any questions, just text us directly at _PHONE_.';
				break;
		}

		if( trim( $message ) == '' ){
			return [ 'error' => 'enter a message' ];
		}

		$notification = new Cockpit_Driver_Notify;
		$notification->id_admin = $driver->id_admin;
		$notification->phone = $phone;
		$notification->message_type = $message_type;
		$notification->email = $driver->email;
		$notification->message = $message;

		// Cana::timeout( function() use( $notification ) {
			$notification->notify();
		// } );

		// log
		$log = new Cockpit_Driver_Log();
		$log->id_admin = $id_admin;
		$log->action = Cockpit_Driver_Log::ACTION_NOTIFIED_SETUP;
		$log->info = $phone . ' (' . $driver->email . ') ' . $message;
		$log->datetime = date('Y-m-d H:i:s');
		$log->save();

		return [ 'success' => 'notification sent' ];

	}

	public function notify(){

		$notification = $this;

		$message = $notification->message;
		$id_admin = $notification->id_admin;
		$phone = $notification->phone;
		$email = $notification->email;
		$message_type = $notification->message_type;

		$env = c::getEnv();

		$twilio = new Twilio( c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token );

		$message = str_split( $message, 160 );

		$isOk = true;

		foreach ( $message as $msg ) {
			try {
				// Log
				Log::debug( [ 'action' => 'notify admin: ' . $id_admin, 'phone' => $phone, 'msg' => $msg, 'type' => 'admin-notification' ] );
				$twilio->account->sms_messages->create( c::config()->twilio->{$env}->outgoingTextCustomer, '+1'. $phone, $msg );
			} catch ( Exception $e ) {
				$isOk = false;
				// Log
				Log::debug( [ 'action' => 'ERROR notify admin: ' . $id_admin, 'error' => $e->getInfo(), 'phone' => $phone, 'msg' => $msg, 'type' => 'admin-notification' ] );
			}
		}

		// Send email
		if( $email ){
			switch ( $message_type ) {
				case Cockpit_Driver_Notify::TYPE_WELCOME:
					$mail = new Cockpit_Email_Driver_Welcome( [ 'id_admin' => $id_admin ] );
					$mail->send();
					break;
				
				case Cockpit_Driver_Notify::TYPE_SETUP:
					$mail = new Cockpit_Email_Driver_Setup( [ 'id_admin' => $id_admin ] );
					$mail->send();
					break;
			}
		}
	}
}