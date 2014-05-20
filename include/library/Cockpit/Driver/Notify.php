<?php

class Cockpit_Driver_Notify extends Cana_Table {

	const TYPE_SETUP = 'setup';
	const TYPE_WELCOME = 'welcome';
	
	public function send( $id_admin, $message ){


		$driver = Crunchbutton_Admin::o( $id_admin );

		if( !$driver->id_admin ){
			$this->_error();
		}

		$phone = $driver->phone();

		$phone = str_replace( '-' , '', $phone );

		if( trim( $phone ) == '' ){
			return [ 'error' => 'invalid phone' ];
		}

		Log::debug( [ 'action' => 'notification starting', 'driver' => $id_admin, 'phone' => $phone, 'message' => $message, 'type' => 'drivers-onboarding'] );

		// Pre defined messages
		switch ( $message ) {
			case Cockpit_Driver_Notify::TYPE_WELCOME:
				$message = "Access cockpit.la/setup/{$phone}";
				break;
			
			case Cockpit_Driver_Notify::TYPE_SETUP:
				$message = 'Test this URL out on your phone (exactly as it appears, no www.) cockpit.la/16844. Play around with it and make sure you understand how everything works';
				break;
		}

		if( trim( $message ) == '' ){
			return [ 'error' => 'enter a message' ];
		}
		
		$env = c::getEnv();

		//!todo: put this notifications at timeout

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
		if( $driver->email ){

			switch ( $message ) {
				case Cockpit_Driver_Notify::TYPE_WELCOME:
					$mail = new Cockpit_Email_Driver_Welcome( [ 'id_admin' => $driver->id_admin ] );
					// $mail->send();
					break;
				
				case Cockpit_Driver_Notify::TYPE_SETUP:
					$mail = new Cockpit_Email_Driver_Setup( [ 'id_admin' => $driver->id_admin ] );
					// $mail->send();
					break;
			}
		}

		if( $isOk ){
			// log
			$log = new Cockpit_Driver_Log();
			$log->id_admin = $driver->id_admin;
			$log->action = Cockpit_Driver_Log::ACTION_NOTIFIED_SETUP;
			$log->info = $phone . ': ' . join( $message );
			$log->datetime = date('Y-m-d H:i:s');
			$log->save();
			return [ 'success' => 'notification sent' ];
		} else {
			return [ 'error' => 'notification not sent' ];
		}

	}
}