<?php

class Crunchbutton_Cron_Job_TempReferralCode extends Crunchbutton_Cron_Log {

	public function run(){

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

		// it always must call finished method at the end
		$this->finished();
	}
}