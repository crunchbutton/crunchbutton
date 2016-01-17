<?php

class Controller_api_profile extends Crunchbutton_Controller_Rest {
	public function init() {
		switch ( c::getPagePiece( 2 ) ) {
			case 'change-password':
				$this->_changePassword();
				break;
			default:
				$this->error(404, true);
				break;
		}
	}

	private function _changePassword(){

		if( $this->method() == 'post' ){

			$admin = c::user();

			$current = $admin->makePass( $this->request()[ 'current' ] );

			if( $current == $admin->pass ){

				$new_pass = trim( $this->request()[ 'new' ] );
				$confirm_pass = trim( $this->request()[ 'confirm' ] );

				if( !$new_pass || !$confirm_pass ){
					$this->error( 404 );
				}

				if( strlen( $new_pass ) < 5 ){
					$this->_error( 'The new password is too short. It is required to be at least 5 characters.' );
				}

				if( $new_pass == $admin->login || $new_pass == $admin->name || $new_pass == $admin->firstName() ){
					$this->_error( 'Your password can not be your name nor username!' );
				}

				if( $confirm_pass != $new_pass ){
					$this->_error( 'The password confirmation must to be equals your new password!' );
				}

				$admin->pass = $admin->makePass( $new_pass );
				$admin->save();

				echo json_encode( [ 'success' => true ] );
				exit();

			} else {
				$this->_error( 'The current password is wrong!' );
			}

		}else {
			$this->error(404, true);
		}
	}

	private function _error( $error = 'invalid request' ){
		echo json_encode( [ 'error' => $error ] );
		exit();
	}

}