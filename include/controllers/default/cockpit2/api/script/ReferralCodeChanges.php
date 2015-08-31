<?php
// RUN THE SQL 000354_user.sql
// Make customer referral code name+number #5321
class Controller_Api_Script_ReferralCodeChanges extends Crunchbutton_Controller_RestAccount {

	public function init() {

		// if( $_GET[ 'step' ] == '1' ){

		// 	$query = "SELECT u.*, SUM(1) total, CONCAT( SUBSTRING_INDEX( u.name, ' ', 1 ), u.phone ) AS new_code, u.id_user
		// 				FROM `user` u
		// 				WHERE u.name IS NOT NULL AND u.phone IS NOT NULL AND ( u.invite_code_updated IS NULL OR invite_code_updated = 0 )
		// 				GROUP BY new_code
		// 				HAVING total = 1 ORDER BY id_user DESC LIMIT 1000";

		// 	$users = Crunchbutton_User::q( $query );
		// 	foreach( $users as $user ){
		// 		$user->inviteCode();
		// 	}
		// }

		if( $_GET[ 'step' ] == '2' ){

			$query = "SELECT * FROM user u WHERE u.name IS NOT NULL AND ( u.invite_code_updated IS NULL OR invite_code_updated = 0 ) ORDER BY id_user DESC LIMIT 3000";
			$users = Crunchbutton_User::q( $query );
			foreach( $users as $user ){
				$new_code = $user->inviteCodeNameBased();
				$user->invite_code_updated = 1;
				if( !$user->invite_code_bkp ){
					$user->invite_code_bkp = $user->invite_code;
				}
				$user->invite_code = $new_code;
				echo $new_code;
				echo "<br>\n";
				$user->save();
				// echo '<pre>';var_dump( $user->id_user );exit();
			}
			echo 1;
		}

	}
}
