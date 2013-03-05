<?php

class Controller_api_user extends Crunchbutton_Controller_Rest {
	public function init() {

		switch (c::getPagePiece(2)) {
			case 'cookie':
				switch ($this->method()) {
					case 'get':
						echo json_encode(['error' => 'invalid request']);
						break;
					case 'post':
						// store cookies on the server for use with facebook api
						foreach ($_POST['cookie'] as $key => $value) {
							
						}
						break;
				}
				break;
			// Verify if the login was already taken
			case 'verify':
				switch ($this->method()) {
					case 'get':
						if( c::getPagePiece(3) != '' ){
							$emailExists = User_Auth::checkEmailExists( c::getPagePiece(3) );
							if( $emailExists ){
								echo json_encode(['error' => 'user exists']);
							} else {
								echo json_encode(['success' => 'user not exists']);
							}
						} else {
							echo json_encode(['error' => 'invalid request']);	
						}
					break;
					default:
						echo json_encode(['error' => 'invalid request']);
					break;
				}
				break;
			// Sign in the user
			case 'auth':
				switch ($this->method()) {
					case 'post':
						$params = array();
						$params[ 'email' ] = $_POST[ 'email' ];
						$params[ 'password' ] = $_POST[ 'password' ];
						$user = c::auth()->doAuthByLocalUser( $params );
						if( $user ){
							echo c::user()->json();
						} else {
							echo json_encode(['error' => 'invalid user']);
						}
						break;
					default:
						echo json_encode(['error' => 'invalid request']);
						exit;
						break;
				}
			case 'auths':
				switch ($this->method()) {
					case 'get':
						$user = c::user();
						if( $user->id_user ){
							$auths = User_Auth::byUserExport( $user->id_user );
							echo json_encode( [ 'auths' => $auths ] );
							exit;
						} 
						echo json_encode(['error' => 'invalid request']);
						exit;
						break;
				}
			// Create a user
			case 'create':
				switch ($this->method()) {
					case 'post':
						switch ( c::getPagePiece( 3 ) ) {
							case 'local':
								$params = array();
								$params[ 'email' ] = $_POST[ 'email' ];
								$params[ 'password' ] = $_POST[ 'password' ];
								$emailExists = User_Auth::checkEmailExists( $params[ 'email' ] );
								if( $emailExists ){
									echo json_encode(['error' => 'user exists']);
									exit;
								}
								$user = c::user();
								if (!$user->id_user) {
									// we dont have a user, and we need to make one
									$user = new User;
									$user->active = 1;
									if( filter_var( $_POST[ 'email' ], FILTER_VALIDATE_EMAIL ) ){
										$user->email = $_POST[ 'email' ];
									} else {
										$user->phone = $_POST[ 'email' ];
									}
									$user->name = '';
									$user->save();
								}
								$user_auth = new User_Auth();
								$user_auth->id_user = $user->id_user;
								$user_auth->type = 'local';
								$user_auth->auth = User_Auth::passwordEncrypt( $params[ 'password' ] );
								$user_auth->email = $params[ 'email' ];
								$user_auth->active = 1;
								$user_auth->save();

								// This line will create a phone user auth just if the user already has an email auth
								if( $user->phone ){
									User_Auth::createPhoneAuth( $user->id_user, $user->phone );	
								}
								
								$user = c::auth()->doAuthByLocalUser( $params );
								echo c::user()->json();
								break;
						}
						break;
					default:
						echo json_encode(['error' => 'invalid request']);
						break;
				}
			break;
			// Reset the user password - create a reset code
			case 'reset':
				switch ( $this->method() ) {
					case 'post':
						$email = $_POST[ 'email' ];
						$user_auth = User_Auth::checkEmailExists( $email );
						if( !$user_auth ){
							echo json_encode(['error' => 'user is not registred']);
							exit;
						} 
						$code = User_Auth::resetCodeGenerator();
						$user_auth->reset_code = $code;
						$user_auth->reset_date = date('Y-m-d H:i:s');
						$user_auth->save();

						// Send the code by email
						if( filter_var( $_POST[ 'email' ], FILTER_VALIDATE_EMAIL ) ){
							$mail = new User_Auth_Reset_Email( [
								'code' => $code,
								'email' => $email
							] );
							$mail->send();
						} 
						// Send the code by sms
						else {
							$phone = $email;

							$env = c::env() == 'live' ? 'live' : 'dev';

							$twilio = new Twilio(c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token);

							$url = 'http://' . $_SERVER['HTTP_HOST'] .  '/reset/';

							$message = "Your crunchbutton password reset code is '".$code."'.\n\n";
							$message .= "Access ".$url." to reset your password.\n\n";

							$message = str_split( $message, 160 );
							foreach ( $message as $msg ) {
								$twilio->account->sms_messages->create(
									c::config()->twilio->{$env}->outgoingTextCustomer,
									'+1'.$phone,
									$msg
								);
								continue;
							}
						}
						echo json_encode(['success' => 'code generated']);
						exit;
						break;
				}
			// Validate a reset code
			case 'code-validate':
				switch ( $this->method() ) {
					case 'post':
						$code = $_POST[ 'code' ];
						$user_auth = User_Auth::validateResetCode( $code );
						if( !$user_auth ){
							echo json_encode(['error' => 'invalid code']);
							exit;
						} else {
							$now = strtotime( 'now' );
							$reset_date = strtotime( $user_auth->reset_date );
							$time = 86400; // 24 hours has 86400 seconds
							// The code is valid for 24 hours
							if( ( $reset_date + $time ) < $now ){
								echo json_encode(['error' => 'expired code']);
								exit;
							} else {
								echo json_encode(['success' => 'valid code']);
								exit;
							}
						}
						break;
				}
			// Change the user password
			case 'change-password':
				switch ( $this->method() ) {
					case 'post':
						$code = $_POST[ 'code' ];
						// Make sure that the user is not cheating!
						$user_auth = User_Auth::validateResetCode($code );
						if( !$user_auth ){
							echo json_encode(['error' => 'invalid code']);
							exit;
						} else {
							$now = strtotime( 'now' );
							$reset_date = strtotime( $user_auth->reset_date );
							$time = 86400; // 24 hours has 86400 seconds
							// The code is valid for 24 hours
							if( ( $reset_date + $time ) < $now ){
								echo json_encode(['error' => 'expired code']);
								exit;
							} else {
								$password = $_POST[ 'password' ];
								$password = User_Auth::passwordEncrypt( $password  );
								$user_auth->auth = $password;
								$user_auth->reset_code = NULL;
								$user_auth->reset_date = NULL;
								$user_auth->save();
								echo json_encode(['success' => 'password changed']);
							}
						}
						break;
					default:
						echo json_encode(['error' => 'invalid request']);
						break;
				}
				break;
			// Force register the facebook
			case 'facebook':
				foreach ( $_COOKIE as $key => $value ) {
						if ( preg_match('/^fbsr_.*$/', $key ) ) {
							$fb = new Crunchbutton_Auth_Facebook;
							$user = c::user();
							if ( $fb->user()->id ) {
								// It seems the facebook user is already related with other user
								$fb_user = User::facebook( $fb->user()->id );	
								if ( $fb_user->id_user && $user->id_user ) {
									if( $fb_user->id_user != $user->id_user ){
										echo json_encode(['error' => 'facebook id already in use']);
										exit;
									}
								}
								if ( !$fb_user->id_user ) {
									$user->active = 1;
									$user->name = $fb->user()->name;
									$user->email = $fb->user()->email;
									$user->save();

									$userAuth = new User_Auth;
									$userAuth->active = 1;
									$userAuth->id_user = $user->id_user;
									$userAuth->type = 'facebook';
									$userAuth->auth = $fb->user()->id;
									$userAuth->save();
								} 
							} else {
								// we dont have a facebook user
							}
							break;
						}
					}
					echo c::user()->json();
				break;
			default:
				switch ($this->method()) {
					case 'get':
						echo c::user()->json();
						break;
					case 'post':
						// we are going to use this for saving user data
						echo c::user()->json();
						break;
				}
				break;
		}
	}
}
