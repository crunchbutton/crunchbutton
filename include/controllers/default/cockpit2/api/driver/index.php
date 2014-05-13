<?php

class Controller_api_driver extends Crunchbutton_Controller_RestAccount {
	
	public function init() {
		$action = c::getPagePiece( 2 );

		switch ( $action ) {

			case 'save':
				$this->_save();
				break;
			
			case 'driver':
				$this->_driver();
				break;

			default:
				$this->_list();
				break;
		}
	}

	private function _driver(){
		$id_admin = c::getPagePiece( 3 );
		$driver = new Crunchbutton_Admin( $id_admin );
		if( $driver->id_admin ){
			echo json_encode( $driver->exports() );
		} else {
			$this->_error( 'invalid object' );
		}
	}

	private function _list(){
		$drivers = Crunchbutton_Admin::drivers();
		$list = [];
		foreach( $drivers as $driver ){
			$list[] = $driver->exports();
		}
		echo json_encode( $list );
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