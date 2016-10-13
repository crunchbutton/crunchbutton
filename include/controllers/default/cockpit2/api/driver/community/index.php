<?php

class Controller_api_driver_community extends Crunchbutton_Controller_RestAccount {

	public function init() {
		switch ( c::getPagePiece( 3 ) ) {
			case 'open':
				$this->_open();
				break;
			case 'open-it-now':
				$this->_openItNow();
				break;
			case 'close':
				$this->_close();
				break;
			case 'text-message':
				$this->_textMessage();
				break;
			case 'status':
			default:
				$this->_status();
				break;
		}
	}

	private function _textMessage(){

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

		$numbers = $this->request()[ 'numbers' ];
		$message = $this->request()[ 'message' ];
		if($community->id_community && count($numbers) && trim($message) != ''){
			foreach( $numbers as $number ){
				if( trim( $number ) != '' ){

					$admin = Crunchbutton_Admin::getByPhone( $number );
					if( $admin->id_admin ){
						$name = $admin->firstName();
					} else {
						$name = '';
					}

					$_message = Crunchbutton_Message_Sms::greeting( $name ) . $message;
					$_message .= ' sent by @' . $driver->firstName() . ' #' . $driver->phone();

					Crunchbutton_Support::createNewWarning(  [ 'dont_open_ticket' => true, 'body' => $_message, 'phone' => $number ] );

					Crunchbutton_Message_Sms::send([
							'from' => 'driver',
							'to' => $number,
							'message' => $_message,
							'reason' => Crunchbutton_Message_Sms::REASON_SUPPORT
						] );
				}
			}
			echo json_encode( [ 'success' => true ] );
		} else {
			echo json_encode( [ 'error' => true ] );
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

		$id_community = $this->request()[ 'id_community' ];
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

	private function _openItNow(){
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
			$success = $community->removeForceCloseByDriver( $driver->id_admin );
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

			$_community[ 'has_pre_orders' ] = $community->hasPreOrders();

			$_community[ 'id_community' ] = $community->id_community;
			$_community[ 'name' ] = $community->name;

			$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
			$now->setTimezone( new DateTimeZone( $community->timezone ) );

			$_community[ 'now' ] = $now->format( 'H:i' );
			$_community[ 'now_formated' ] = $now->format( 'h:i a' );

			$_community[ 'is_open' ] = $community->isOpen();
			$_community[ 'could_be_opened' ] = $community->isElegibleToBeOpened();
			$_community[ 'is_force_closed' ] = ($community->allThirdPartyDeliveryRestaurantsClosed());
			if($_community[ 'is_force_closed' ]){
				$_community[ 'is_force_closed_by' ] = $community->reopen_at;
				$_community[ 'will_be_closed_until' ] = $community->close_3rd_party_delivery_restaurants_id_admin;
				$_community[ 'can_remove_force_close' ] = ($community->close_3rd_party_delivery_restaurants_id_admin == c::user()->id_admin);
			}
			$_community[ 'could_be_closed' ] = $community->isElegibleToBeClosed();

			$_community[ 'name_status' ] = $community->name . ( $_community[ 'is_open' ] ? ' [Open]' : ' [Closed]' );

			$_community[ 'restaurants' ] = [];
			$restaurants = $community->restaurants();
			foreach( $restaurants as $restaurant ){
				if( $restaurant->delivery_service && $restaurant->active && $restaurant->open_for_business ){
					$_community[ 'restaurants' ][] = [ 'id_restaurant' => $restaurant->id_restaurant,
																			'name' => $restaurant->name,
																			'closed_message' => $restaurant->closed_message(),
																			'is_open' => $restaurant->open() ];
				}
			}
			$_community[ 'drivers' ] = [];
			$drivers = $community->getDriversOfCommunity();
			foreach( $drivers as $driver ){
				if($driver->id_admin != c::user()->id_admin){
					$working = $driver->isWorking();
					$_community[ 'drivers' ][] = [ 'id_admin' => intval( $driver->id_admin ), 'name' => $driver->name, 'phone' => $driver->phone, 'working' => $working];
				}

			}
			$out[] = $_community;
		}
		echo json_encode( $out );exit;
	}
}