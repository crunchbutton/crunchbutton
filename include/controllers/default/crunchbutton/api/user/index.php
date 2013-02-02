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
						break;
				}
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
								$user_auth = new User_Auth();
								$user_auth->id_user = c::user()->id_user;
								$user_auth->type = 'local';
								$user_auth->auth = User_Auth::passwordEncrypt( $params[ 'password' ] );
								$user_auth->email = $params[ 'email' ];
								$user_auth->active = 1;
								$user_auth->save();
								echo c::user()->json();
								break;
						}
						break;
					default:
						echo json_encode(['error' => 'invalid request']);
						break;
				}
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