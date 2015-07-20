<?php

// Make customer referral code name+number #5321
class Controller_Api_Script_ReferralCodeChanges extends Crunchbutton_Controller_RestAccount {

	public function init() {

		if( $_GET[ 'step' ] == '1' ){

			$query = "SELECT SUM(1) total, CONCAT( SUBSTRING_INDEX( u.name, ' ', 1 ), u.phone ) AS new_code, u.id_user
						FROM `user` u
						WHERE u.name IS NOT NULL AND u.phone IS NOT NULL AND ( u.invite_code_updated IS NULL OR invite_code_updated = 0 )
						GROUP BY new_code
						HAVING total = 1 ORDER BY id_user DESC LIMIT 1000";

			$users = c::db()->query( $query );

			while ( $u = $users->fetch() ) {
				if( !Crunchbutton_Referral::isCodeAlreadyInUse( $u->new_code ) ){
					$user = Crunchbutton_User::o( $u->id_user );
					$user->invite_code = $u->new_code;
					$user->invite_code_updated = 1;
					$user->save();
				}
			}
		}

		if( $_GET[ 'step' ] == '2' ){

			$query = "SELECT  SUM(1) total, CONCAT( SUBSTRING_INDEX( u.name, ' ', 1 ), u.phone ) AS new_code, u.id_user
						FROM `user` u
						WHERE u.name IS NOT NULL AND u.phone IS NOT NULL AND ( u.invite_code_updated IS NULL OR invite_code_updated = 0 )
						GROUP BY new_code
						HAVING total > 1 ORDER BY total DESC LIMIT 1000";

			$users = c::db()->query( $query );

			while ( $u = $users->fetch() ) {
				if( !Crunchbutton_Referral::isCodeAlreadyInUse( $u->new_code ) ){
					$user = Crunchbutton_User::q( 'SELECT * FROM user u WHERE CONCAT( SUBSTRING_INDEX( u.name, " ", 1 ), u.phone ) = ? ORDER BY id_user DESC LIMIT 1', [ $u->new_code ] )->get( 0 );
					if( $user->id_user ){
						c::db()->query( 'UPDATE user u SET u.invite_code_updated = 1, invite_code = ""  WHERE CONCAT( SUBSTRING_INDEX( u.name, " ", 1 ), u.phone ) = ?', [ $u->new_code ] );
						$user->invite_code = $u->new_code;
						$user->invite_code_updated = 1;
						$user->save();
					}
				}
			}
		}
	}
}
