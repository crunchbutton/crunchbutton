<?php
error_reporting(0);

class Controller_test_recovery extends Crunchbutton_Controller_Account {

	public $users_ok;
	public $users_no_ok;

	public function init() {
		$users = User::q( 'SELECT * FROM `user` u WHERE u.balanced_id IS NOT NULL AND uuid IS NULL' );
		foreach ( $users as $user ) {
			if( $user->balanced_id ){
				$balanced_id = $user->balanced_id;
				$account = Crunchbutton_Balanced_Account::byId( $balanced_id );	
				if( $account->email_address ){
					// $uuid = str_replace( '@_DOMAIN_' , '', $account->email_address );
					// $user->uuid = $uuid;
					// $user->save();
					$this->users_ok[] = array( 'name' => $user->name, 'user_id' => $user->id_user );
				} else {
					$this->users_no_ok[] = array( 'name' => $user->name, 'user_id' => $user->id_user );
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
			echo "<li>{$user[ 'name' ]}: {$user[ 'user_id' ]}</li>";
		}
		echo '</ul>';	
		echo '</pre>';
	}

}