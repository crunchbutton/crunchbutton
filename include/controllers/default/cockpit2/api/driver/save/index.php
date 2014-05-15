<?php

class Controller_api_driver_save extends Crunchbutton_Controller_RestAccount {
	
	public function init() {
		$this->_save();
	}

	private function _save(){

		if( $this->method() != 'post' ){
			$this->_error();
		}

		$id_admin = c::getPagePiece( 3 );
		// saves a new driver
		if( !$id_admin ){
			$driver = new Crunchbutton_Admin();
			// create the new driver as inactive
			$driver->active = 0;
		} else {
			$driver = Crunchbutton_Admin::o( $id_admin );
		}
			
		$driver->name = $this->request()[ 'name' ];
		$driver->phone = $this->request()[ 'phone' ];
		$driver->email = $this->request()[ 'email' ];
		
		$driver->save();

		// add the community
		$id_community = $this->request()[ 'id_community' ];

		// first remove the driver from the delivery groups
		$_communities = Crunchbutton_Community::q( 'SELECT * FROM community ORDER BY name ASC' );;
		foreach( $_communities as $community ){
			$group = $community->groupOfDrivers();
			if( $group->id_group ){
				$driver->removeGroup( $group->id_group );
			}
		}

		if( $id_community ){
			$community = Crunchbutton_Community::o( $id_community );
			if( $community->id_community ){
				$group = $community->groupOfDrivers();
				$adminGroup = new Crunchbutton_Admin_Group();
				$adminGroup->id_admin = $driver->id_admin;
				$adminGroup->id_group = $group->id_group;
				$adminGroup->save();
			}	
		}

		echo json_encode( [ 'success' => $driver->exports() ] );
		return;
	}

	private function _error( $error = 'invalid request' ){
		echo json_encode( [ 'error' => $error ] );
		exit();
	}
}