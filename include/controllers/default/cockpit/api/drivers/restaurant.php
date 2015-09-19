<?php

class Controller_api_drivers_restaurant extends Crunchbutton_Controller_RestAccount {

	public function init() {

		if (!c::admin()->permission()->check( ['global','drivers-assign', 'drivers-all'] ) ) {
			echo json_encode( [ 'error' => 'invalid object' ] );
			exit;
		}

		$id_restaurant = $this->request()[ 'id_restaurant' ];
		$admins = $this->request()[ 'id_admin' ];

		if( !$id_restaurant ){
			echo json_encode( [ 'error' => 'invalid object' ] );
			exit;
		}

		// get the admin notifications of this restaurant and deactive them
		$currentNotifications = Notification::q( 'SELECT * FROM notification n WHERE id_restaurant = ' . $id_restaurant . ' AND type = "' . Crunchbutton_Notification::TYPE_ADMIN . '"');
		foreach( $currentNotifications as $notification ){
			$notification->active = 0;
			$notification->save();
		}

		if( count( $admins ) > 0 && $admins != '' ){
			foreach( $admins as $admin ){
				if( trim( $admin ) == '' ){
					break;
				}
				$hasNotification = false;
				// check if it already has a notification for this restaurant and active it
				foreach( $currentNotifications as $notification ){
					if( $notification->id_admin == $admin ){
						$notification->active = 1;
						$notification->save();
						$hasNotification = true;
						break;
					}
				}
				if( !$hasNotification ){
					$notification = new Crunchbutton_Notification();
					$notification->id_restaurant = $id_restaurant;
					$notification->active = 1;
					$notification->type = Crunchbutton_Notification::TYPE_ADMIN;
					$notification->id_admin = $admin;
					$notification->save();
				}
			}
		}
		echo json_encode( [ 'success' => true ] );
	}
}
