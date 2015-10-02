<?php

class Controller_api_driver_community extends Crunchbutton_Controller_RestAccount {

	public function init() {
		switch ( c::getPagePiece( 3 ) ) {
			case 'open':
				$this->_open();
				break;
			case 'status':
			default:
				$this->_status();
				break;
		}
	}

	private function _open(){
		$driver = c::user();
		if( !$driver->isDriver() ){
			return $this->error( 404 );
		}
		$community = $driver->community();
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
		$community = $driver->community();
		if( $community->id_community ){
			$out = [];
			$out[ 'id_community' ] = $community->id_community;
			$out[ 'name' ] = $community->name;

			$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
			$now->setTimezone( new DateTimeZone( $community->timezone ) );

			$out[ 'now' ] = $now->format( 'H:i' );
			$out[ 'now_formated' ] = $now->format( 'h:i a' );

			$out[ 'is_open' ] = $community->isOpen();
			$out[ 'could_be_opened' ] = $community->isElegibleToBeOpened();

			$out[ 'restaurants' ] = [];
			$restaurants = $community->restaurants();
			foreach( $restaurants as $restaurant ){
				if( $restaurant->delivery_service && $restaurant->active && $restaurant->open_for_business ){
					$out[ 'restaurants' ][] = [ 'id_restaurant' => $restaurant->id_restaurant,
																			'name' => $restaurant->name,
																			'closed_message' => $restaurant->closed_message(),
																			'is_open' => $restaurant->open() ];
				}
			}
			$community->isElegibleToBeOpened();

			echo json_encode( $out );exit;
		}
	}
}