<?php

class Controller_api_Permissions_Users extends Crunchbutton_Controller_Rest {
	
	public function init() {

		if (!c::admin()->permission()->check(['global','permissions-all', 'permission-users'])) {
			return ;
		}

		switch ( $this->method() ) {

			case 'post':

				$id_admin = c::getPagePiece( 3 );

				if( !$id_admin ){
					if( Crunchbutton_Admin::loginExists( $_REQUEST[ 'login' ] ) ){
						echo json_encode( [ 'error' => 'login' ] );
						exit();
					}
				}

				$name = $_REQUEST[ 'name' ];
				$phone = $_REQUEST[ 'phone' ];
				$txt = $_REQUEST[ 'txt' ];
				$email = $_REQUEST[ 'email' ];
				$testphone = $_REQUEST[ 'testphone' ];
				$timezone = $_REQUEST[ 'timezone' ];
				$login = $_REQUEST[ 'login' ];
				$password = $_REQUEST[ 'password' ];
				$ids_group = $_REQUEST[ 'id_group' ];
				if( $id_admin ){
					$admin = Crunchbutton_Admin::o( $id_admin );
				} else {
					$admin = new Crunchbutton_Admin();
				}
				$admin->name = $name;
				$admin->phone = $phone;
				$admin->txt = $txt;
				$admin->email = $email;
				$admin->testphone = $testphone;
				$admin->timezone = $timezone;
				$admin->login = $login;
				if( $password != '' ){
					$admin->password = $password;	
				}
				$admin->save();
				$admin->removeGroups();
				$ids_group = explode( ',' , $ids_group );
				if( $ids_group ){
					foreach ( $ids_group as $id_group ) {
						$new = new Crunchbutton_Admin_Group();
						$new->id_admin = $admin->id_admin;
						$new->id_group = intval( $id_group );
						$new->save();
					}
				}
				echo json_encode( ['success' => $admin->id_admin ] );
			break;
			default:
				echo json_encode( [ 'error' => 'invalid object' ] );
			break;
		}
	}

		
}