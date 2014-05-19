<?php

class Controller_api_driver_setup extends Crunchbutton_Controller_Rest {
	
	public function init() {
		if( $this->method() == 'post' ) {
			$id_admin = $this->request()[ 'id_admin' ];
			$admin = Crunchbutton_Admin::o( $id_admin );
			if( $admin->id_admin ){
				$admin->email = $this->request()[ 'email' ];;
				$admin->login = $admin->createLogin();
				$admin->active = 1;
				$admin->pass = $admin->makePass( $this->request()[ 'password' ] );
				$admin->save();	

				$log = new Crunchbutton_Driver_Log();
				$log->id_admin = $admin->id_admin;
				$log->action = Crunchbutton_Driver_Log::ACTION_ACCOUNT_SETUP;
				$log->info = $admin->login;
				$log->datetime = date('Y-m-d H:i:s');
				$log->save();
				
				// Notify
				Crunchbutton_Driver_Notify::send( $admin->id_admin, Crunchbutton_Driver_Notify::TYPE_WELCOME );
				
				echo json_encode( [ 'success' => $admin->exports() ] );
			} else {
				$this->_error();
			}
			
		} else {

			$phone = c::getPagePiece( 3 );
			if( $phone ){
				$phone = preg_replace( '/[^0-9]/i', '', $phone );
				$admin = Crunchbutton_Admin::getByPhoneSetup( $phone );
				if( $admin->id_admin ){
					echo json_encode( [ 'success' => [ 'id_admin' => $admin->id_admin, 'hasEmail' => ( $admin->email && $admin->email != '' ) ? true : false ] ] );
				} else {
					$this->_error( 'Invalid phone number' );
				}
			} else {
				$this->_error();
			}
		}
	}

	private function _error( $error = 'invalid request' ){
		echo json_encode( [ 'error' => $error ] );
		exit();
	}

}