<?php
error_reporting(0);

class Controller_test_recoveryUserAuth extends Crunchbutton_Controller_Account {

	public $users_ok;
	public $users_no_ok;

	public function init() {

		die( 'remove this die in order to get it working!!!' );

		$auths = User_Auth::q( 'SELECT * FROM user_auth WHERE active = false AND auth IS NULL AND type = "local"' );
		foreach ( $auths as $auth ) {
			$phone = $auth->email;
			if( $phone ){
				$user_auth_facebook = User_Auth::q( 'SELECT * FROM user_auth INNER JOIN user ON user.id_user = user_auth.id_user AND user.phone = "' . $phone . '" WHERE user_auth.type = "facebook"' );
				if( $user_auth_facebook->id_user ){
					$auth->id_user = $user_auth_facebook->id_user;
					$auth->active = 1;
					$auth->save();
					$this->users_ok[] = array( 'name' => $user_auth_facebook->name, 'phone' => $phone );
				} else {
					$user = User::q( 'SELECT * FROM user WHERE phone = "' . $phone . '" ORDER BY id_user DESC LIMIT 1' );
					if( $user->id_user ){
						$auth->id_user = $user->id_user;
						$auth->active = 1;
						$auth->save();
						$this->users_ok[] = array( 'name' => $user->name, 'phone' => $phone );
					} else {
						$this->users_no_ok[] = array( 'phone' => $phone );
					}
				}
			}
		}
		$this->summary( true );
		$this->summary( false );
	}

	public function summary( $ok ){
		echo '<pre>';
		if( $ok ){
			echo '<h1 style="color:green;">Users ok</h1>';
			$list = $this->users_ok;
		} else {
			echo '<h1 style="color:red;">Users problem</h1>';
			$list = $this->users_no_ok;
		}
		echo '<ul>';
		foreach( $list as $user ){
			echo "<li>{$user[ 'name' ]}: {$user[ 'phone' ]}</li>";
		}
		echo '</ul>';	
		echo '</pre>';
	}

}