<?php

class Controller_api_Permissions_Users extends Crunchbutton_Controller_RestAccount {

	public function init() {

		if (!c::admin()->permission()->check(['global','permissions-all', 'permission-users'])) {
			return ;
		}

		switch ( $this->method() ) {

			case 'post':

				$id_admin = c::getPagePiece( 3 );

				$action = c::getPagePiece( 4 );

				switch ( $action ) {

					case 'inactive':
						$admin = Crunchbutton_Admin::o( $id_admin );
						$admin->active = 0;
						$admin->save();
						echo json_encode( ['success' => $admin->id_admin ] );
						break;

					case 'active':
						$admin = Crunchbutton_Admin::o( $id_admin );
						$admin->active = 1;
						$admin->save();
						echo json_encode( ['success' => $admin->id_admin ] );
						break;

					case 'permissions':
						$admin = Crunchbutton_Admin::o( $id_admin );
						$admin->removePermissions();
						$admin->addPermissions( $_REQUEST[ 'permissions' ] );
						echo json_encode( ['success' => $admin->id_admin ] );
						break;

					case 'notifications':

						switch ( c::getPagePiece( 5 ) ) {
							case 'active':
								$id_admin_notification = $_REQUEST[ 'id_admin_notification' ];
								$active = $_REQUEST[ 'active' ];
								$notification = Crunchbutton_Admin_Notification::o( $id_admin_notification );
								if( $notification->id_admin_notification ){
									$notification->active = $active;
									$notification->save();
									echo json_encode( ['success' => $notification->id_admin_notification ] );
								} else {
									echo json_encode( ['error' => '1' ] );
								}
								break;

							case 'remove':
								$id_admin_notification = $_REQUEST[ 'id_admin_notification' ];
								$notification = Crunchbutton_Admin_Notification::o( $id_admin_notification );
								if( $notification->id_admin_notification ){
									$notification->delete();
									echo json_encode( ['success' => 'removed' ] );
								} else {
									echo json_encode( ['error' => '1' ] );
								}
								break;

							case 'save':
								$value = $_REQUEST[ 'value' ];
								$type = $_REQUEST[ 'type' ];
								$notification = new Crunchbutton_Admin_Notification();
								$notification->value = $value;
								$notification->type = $type;
								$notification->active = 1;
								$notification->id_admin = $id_admin;
								$notification->save();
								if( $notification->id_admin_notification ){
									echo json_encode( ['success' => $notification->id_admin_notification ] );
								} else {
									echo json_encode( ['error' => '1' ] );
								}
								break;
							default:
								echo json_encode( [ 'error' => 'invalid object' ] );
						}

						break;

					default:
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
							$admin->active = 1;
						}
						$admin->name = $name;
						$admin->phone = $phone;
						$admin->txt = $txt;
						$admin->email = $email;
						$admin->testphone = $testphone;
						$admin->timezone = $timezone;
						$admin->login = $login;
						if( $password != '' ){
							$admin->pass = $admin->makePass( $password );
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

						$payment_type = $admin->payment_type();
						$payment_type->using_pex = ( intval( $this->request()[ 'using_pex' ] ) ? intval( $this->request()[ 'using_pex' ] ) : 0 );
						$payment_type->save();


						echo json_encode( ['success' => $admin->id_admin ] );
						break;
				}


			break;
			default:
				echo json_encode( [ 'error' => 'invalid object' ] );
			break;
		}
	}


}