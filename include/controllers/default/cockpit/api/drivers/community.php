<?php

class Controller_api_drivers_community extends Crunchbutton_Controller_RestAccount {
	
	public function init() {
		
		if (!c::admin()->permission()->check( ['global','drivers-assign', 'drivers-all'] ) ) {
			echo json_encode( [ 'error' => 'invalid object' ] );
			exit;
		}
		
		$id_community = $this->request()[ 'id_community' ];
		$admins = $this->request()[ 'id_admin' ];

		if( !$id_community ){
			echo json_encode( [ 'error' => 'invalid object' ] );
			exit;
		}

		// get the group of this community
		$community = Crunchbutton_Community::o( $id_community );
		$group = $community->groupOfDrivers();

		if( !$group->id_group ){
			echo json_encode( [ 'error' => 'invalid object' ] );
			exit;	
		}

		// remove the users
		$group->removeUsers();

		// add the new drivers
		if( count( $admins ) > 0 && $admins != '' ){
			foreach( $admins as $admin ){
				if( trim( $admin ) == '' ){
					break;
				}
				if( $group->id_group ){
					$adminGroup = new Crunchbutton_Admin_Group();
					$adminGroup->id_admin = $admin;
					$adminGroup->id_group = $group->id_group;
					$adminGroup->save();
				}
			}
		}
		echo json_encode( [ 'success' => true ] );
	}
}
