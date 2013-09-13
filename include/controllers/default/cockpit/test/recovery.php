<?php
error_reporting(0);
class Controller_test_recovery extends Crunchbutton_Controller_Account {
	public function init() {
		$users = User::q( 'SELECT * FROM `user` u WHERE u.balanced_id IS NOT NULL AND uuid IS NULL' );
		foreach ( $users as $user ) {
			if( $user->balanced_id ){
				try {
					$balanced_id = $user->balanced_id;
					$account = Crunchbutton_Balanced_Account::byId( $balanced_id );	
					if( $account->email_address ){
						$uuid = str_replace( '@_DOMAIN_' , '', $account->email_address );
						$user->uuid = $uuid;
						$user->save();
						echo '<pre>';var_dump( $user );exit();
					}
				} catch (Exception $e) {  
					throw new Exception( 'Something really gone wrong', 0, $e);  
				}
			}
		}
	}
}