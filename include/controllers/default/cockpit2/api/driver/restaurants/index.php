<?php

class Controller_api_driver_restaurants extends Crunchbutton_Controller_RestAccount {

	public function init() {
		switch ( c::getPagePiece( 3 ) ) {
			case 'status':
			default:
				$this->_status();
				break;
		}
	}

	private function _close(){
		$driver = c::user();
		if( !$driver->isDriver() ){
			return $this->error( 404 );
		}

		$id_community = $this->request()[ 'id_community' ];
		$communities = $driver->driverCommunities();
		$community = null;
		foreach ( $communities as $_community ) {
			if( $_community->id_community == $id_community ){
				$community = $_community;
			}
		}
		if( $community->id_community ){
			$minutes = intval($this->request()[ 'how_long' ]);
			$reason = $this->request()[ 'reason' ];
			$success = $community->closeCommunityByDriver( $driver->id_admin, $minutes, $reason );
			if( $success ){
				return $this->_status();
			}
		}
		echo json_encode( [ 'error' => true ] );exit;
	}

	private function _open(){
		$driver = c::user();
		if( !$driver->isDriver() ){
			return $this->error( 404 );
		}

		$permalink = $this->request()[ 'permalink' ];
		$communities = $driver->driverCommunities();
		$community = null;
		foreach ( $communities as $_community ) {
			if( $_community->id_community == $id_community ){
				$community = $_community;
			}
		}
		if( $community->id_community ){
			$hour = $this->request()[ 'hour' ];
			$success = $community->openCommunityByDriver( $driver->id_admin, $hour );
			if( $success ){
				return $this->_status();
			}
		}
		echo json_encode( [ 'error' => true ] );exit;
	}

	private function _status(){
		$driver = c::user();
		if( !$driver->isDriver() ){
			return $this->error( 404 );
		}
		$out = [];
		$communities = $driver->driverCommunities();
		foreach ( $communities as $community ) {

			$_community[ 'id_community' ] = $community->id_community;
			$_community[ 'name' ] = $community->name;

			$_community[ 'is_open' ] = $community->isOpen();
			$_community[ 'could_be_opened' ] = $community->isElegibleToBeOpened();
			$_community[ 'could_be_closed' ] = $community->isElegibleToBeClosed();

			$_community[ 'name_status' ] = $community->name . ( $_community[ 'is_open' ] ? ' [Open]' : ' [Closed]' );

			$_community[ 'restaurants' ] = [];
			$restaurants = $community->restaurants();
			foreach( $restaurants as $restaurant ){
				$closed_for_today = false;
				if(!$restaurant->open_for_business && $restaurant->reopen_for_business_at){
					$closed_for_today = true;
				}
				if( $restaurant->delivery_service && $restaurant->active){
					$_community[ 'restaurants' ][] = [ 'id_restaurant' => $restaurant->id_restaurant,
																			'name' => $restaurant->name,
																			'closed_for_today' => $closed_for_today,
																			'closed_message' => $restaurant->closed_message(),
																			'is_open' => $restaurant->open() ];
				}
			}
			$out[] = $_community;
		}
		echo json_encode( $out );exit;
	}
}