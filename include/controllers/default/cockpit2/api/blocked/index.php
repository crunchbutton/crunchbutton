<?php

class Controller_Api_Blocked extends Crunchbutton_Controller_Rest {

	public function init() {

		$this->_permissionDenied();

		if ( $this->method() == 'post' ) {
			if ( $this->request()[ 'id_user' ] ) {
				$user = User::o( $this->request()[ 'id_user' ] );
				if( $user->id_user ){
					if( !Crunchbutton_Blocked::isUserBlocked( $user->id_user ) ){
						Crunchbutton_Blocked::blockUser( $user->id_user );
						$status = 'blocked';
					} else {
						Crunchbutton_Blocked::unBlockUser( $user->id_user );
						$status = 'unblocked';
					}
					echo json_encode( [ 'success' => $status ] );exit();
				} else {
					$this->error(404, true);
				}
			} else if ( $this->request()[ 'id_phone' ] || $this->request()[ 'phone' ] ) {
				if( $this->request()[ 'id_phone' ] ){
					$phone = Phone::o( $this->request()[ 'id_phone' ] );
				} else if ( $this->request()[ 'phone' ] ){
					$phone = Phone::byPhone( $this->request()[ 'phone' ] );
				}
				if( $phone->id_phone ){
					if( !Crunchbutton_Blocked::isPhoneBlocked( $phone->id_phone ) ){
						Crunchbutton_Blocked::blockPhone( $phone->id_phone );
						$status = 'blocked';
					} else {
						Crunchbutton_Blocked::unBlockPhone( $phone->id_phone );
						$status = 'unblocked';
					}
					echo json_encode( [ 'success' => $status ] );exit();
				} else {
					$this->error(404, true);
				}
			}
		} else {
			$this->error(404, true);
		}

	}
	private function _permissionDenied(){
		if (!c::admin()->permission()->check(['global', 'customer-all', 'customer-block' ])) {
			$this->error(401, true);
		}
	}
}
