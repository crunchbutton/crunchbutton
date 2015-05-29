<?php

class Controller_Api_User_Referral extends Crunchbutton_Controller_Rest {
	public function init() {
		$id_user = c::user()->id_user;
		$out = [];
		if( $id_user ){
			$users = Crunchbutton_Referral::newReferredUsersByUser( $id_user );
			if( $users ){
				foreach( $users as $user ){
					$out[] = $user->name;
				}
				$users = join( $out, ', ' );
			}
		}
		echo json_encode( [ 'users' => $users ] );exit;
	}
}