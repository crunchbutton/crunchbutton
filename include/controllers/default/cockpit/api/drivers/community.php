<?php

class Controller_api_drivers_community extends Crunchbutton_Controller_RestAccount {
	
	public function init() {
		
		if (!c::admin()->permission()->check( [ 'global' ] ) ) {
			echo json_encode( [ 'error' => 'invalid object' ] );
			exit;
		}
		
		$community = $this->request()[ 'community' ];
		$admins = $this->request()[ 'id_admin' ];

		if( !$community ){
			echo json_encode( [ 'error' => 'invalid object' ] );
			exit;
		}

		// get the group of this community
		$group = Crunchbutton_Group::getDeliveryGroupByCommunity( $community );

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
