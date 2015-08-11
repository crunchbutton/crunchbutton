<?php
// RUN THE SQL 000354_user.sql
// Make customer referral code name+number #5321
class Controller_Api_Script_ReferralCodeChanges extends Crunchbutton_Controller_RestAccount {

	public function init() {

		if( $_GET[ 'step' ] == '1' ){

			$query = "SELECT u.*, SUM(1) total, CONCAT( SUBSTRING_INDEX( u.name, ' ', 1 ), u.phone ) AS new_code, u.id_user
						FROM `user` u
						WHERE u.name IS NOT NULL AND u.phone IS NOT NULL AND ( u.invite_code_updated IS NULL OR invite_code_updated = 0 )
						GROUP BY new_code
						HAVING total = 1 ORDER BY id_user DESC LIMIT 1000";

			$users = Crunchbutton_User::q( $query );
			foreach( $users as $user ){
				$user->inviteCode();
			}
		}

		if( $_GET[ 'step' ] == '2' ){

			$query = "SELECT u.*, SUM(1) total, CONCAT( SUBSTRING_INDEX( u.name, ' ', 1 ), u.phone ) AS new_code, u.id_user
						FROM `user` u
						WHERE u.name IS NOT NULL AND u.phone IS NOT NULL AND ( u.invite_code_updated IS NULL OR invite_code_updated = 0 )
						GROUP BY new_code
						HAVING total > 1 ORDER BY total DESC LIMIT 1000";

			$users = Crunchbutton_User::q( $query );
			foreach( $users as $user ){
				$u = Crunchbutton_User::q( 'SELECT * FROM user u WHERE CONCAT( SUBSTRING_INDEX( u.name, " ", 1 ), u.phone ) = ? ORDER BY u.id_user DESC LIMIT 1', [ $user->new_code ] )->get( 0 );
				$u->inviteCode();
				c::db()->query( 'UPDATE user u SET u.invite_code_updated = 1, invite_code = ""  WHERE CONCAT( SUBSTRING_INDEX( u.name, " ", 1 ), u.phone ) = ?', [ $user->new_code ] );
			}
		}

	}
}
