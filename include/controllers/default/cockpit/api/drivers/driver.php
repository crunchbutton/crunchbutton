<?php

class Controller_api_drivers_driver extends Crunchbutton_Controller_RestAccount {

	public function init() {

		if (!c::admin()->permission()->check( ['global','drivers-assign', 'drivers-all'] ) ) {
			echo json_encode( [ 'error' => 'invalid object' ] );
			exit;
		}

		$id_admin = $this->request()[ 'id_admin' ];
		$restaurants = $this->request()[ 'id_restaurant' ];
		$communities = $this->request()[ 'id_community' ];


		$admin = Admin::o( $id_admin );

		if( !$admin->id_admin ){
			echo json_encode( [ 'error' => 'invalid object' ] );
			exit;
		}

		// first remove the driver from the delivery groups
		$_communities = Crunchbutton_Community::q( 'SELECT * FROM community ORDER BY name ASC' );;
		foreach( $_communities as $community ){
			$group = $community->groupOfDrivers();
			if( $group->id_group ){
				$admin->removeGroup( $group->id_group );
			}
		}

		// relate the communities with the driver
		if( count( $communities ) > 0 && $communities != '' ){
			foreach ( $communities as $community ) {
				$community = Crunchbutton_Community::o( $community );
				if( $community->id_community ){
					$group = $community->groupOfDrivers();
					$adminGroup = new Crunchbutton_Admin_Group();
					$adminGroup->id_admin = $id_admin;
					$adminGroup->id_group = $group->id_group;
					$adminGroup->save();
				}
			}
		}

		// get the admin notifications of this driver and deactive them
		$currentNotifications = Notification::q( 'SELECT * FROM notification n WHERE id_admin = ' . $id_admin );
		foreach( $currentNotifications as $notification ){
			$notification->active = 0;
			$notification->save();
		}
		if( count( $restaurants ) > 0 && $restaurants != '' ){
			foreach( $restaurants as $restaurant ){
				if( trim( $restaurant ) == '' ){
					break;
				}
				$hasNotification = false;
				// check if it already has a notification for this restaurant and active it
				foreach( $currentNotifications as $notification ){
					if( $notification->id_restaurant == $restaurant ){
						$notification->active = 1;
						$notification->save();
						$hasNotification = true;
						break;
					}
				}
				if( !$hasNotification ){
					$notification = new Crunchbutton_Notification();
					$notification->id_restaurant = $restaurant;
					$notification->active = 1;
					$notification->type = Crunchbutton_Notification::TYPE_ADMIN;
					$notification->id_admin = $id_admin;
					$notification->save();
				}
			}
		}
		echo json_encode( [ 'success' => true ] );

	}
}
