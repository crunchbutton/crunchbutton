<?php

class Controller_api_driver_notify extends Crunchbutton_Controller_RestAccount {
	
	public function init() {

		if( $this->method() != 'post' ){
			$this->_error();
		}

		$id_admin = c::getPagePiece( 3 );

		$driver = Crunchbutton_Admin::o( $id_admin );

		if( !$driver->id_admin ){
			$this->_error();
		}

		$message = $this->request()[ 'message' ];

		$phone = $driver->phone();

		$phone = str_replace( '-' , '', $phone );

		if( trim( $phone ) == '' ){
			$this->_error( 'we need a phone number!' );
		}

		// Pre defined messages
		switch ( $message ) {
			case 'setup':
				$message = "Access cockpit.la/setup/{$phone}";
				break;
			
			case 'welcome':
				$message = 'Test this URL out on your phone (exactly as it appears, no www.) cockpit.la/16844. Play around with it and make sure you understand how everything works';
				break;

			default:
				break;
		}

		if( trim( $message ) == '' ){
			$this->_error();	
		}
		
		$env = c::getEnv();

		// todo: put this notifications at timeout

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

		if( $isOk ){
			// log
			$log = new Crunchbutton_Driver_Log();
			$log->id_admin = $driver->id_admin;
			$log->action = 'notified';
			$log->info = $phone . ': ' . join( $message );
			$log->datetime = date('Y-m-d H:i:s');
			$log->save();
			echo json_encode( [ 'success' => $driver->exports() ] );
		} else {
			$this->_error( 'notification not sent' );
		}
	}

	private function _error( $error = 'invalid request' ){
		echo json_encode( [ 'error' => $error ] );
		exit();
	}
}