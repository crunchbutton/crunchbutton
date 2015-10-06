<?php

class Controller_api_community extends Crunchbutton_Controller_RestAccount {

	public function init() {

		if (!c::admin()->permission()->check( ['global'] ) ) {
			echo json_encode( [ 'error' => 'invalid object' ] );
			exit;
		}

		switch ( $this->method() ) {
			case 'post':
			case 'get':
				switch ( c::getPagePiece( 2 ) ) {
					case 'close-all':
						$this->closeAll();
						break;
					case 'close-3rd':
						$this->close3rdParty();
						break;
					case 'auto-closed-message':
						$this->autoClosedMessage();
						break;
					case 'dont-warn-till':
						$this->dontWarnTill();
						break;
					case 'position':
						$this->position();
						break;
				}

			break;
			default:
				echo json_encode( [ 'error' => 'invalid object' ] );
			break;
		}
	}

	public function position(){
		$community = Community::o( c::getPagePiece( 3 ) );
		if( $community->id_community ){
			echo json_encode( [ 'lat' => $community->loc_lat, 'lon' => $community->loc_lon ] );exit;
		}
	}

	public function dontWarnTill(){
		$id_community = $this->request()[ 'id_community' ];
		$community = Crunchbutton_Community::o( $id_community );
		if( $community->id_community ){
			$dont_warn_till = $this->request()[ 'dont_warn_till' ];
			$dont_warn_till = new DateTime( $dont_warn_till, new DateTimeZone( c::config()->timezone ) );
			$dont_warn_till = $dont_warn_till->format( 'Y-m-d H:i:s' );
			$community->dont_warn_till = $dont_warn_till;
			$community->save();
			echo json_encode( [ 'success' => true ] );
		} else {
			echo json_encode( [ 'error' => 'invalid object' ] );
		}

		exit;
	}

	public function closeAll(){
		$id_community = $this->request()[ 'id_community' ];
		$community = Crunchbutton_Community::o( $id_community );
		if( $community->id_community ){
			$community->close_all_restaurants = $this->request()[ 'close_all_restaurants' ];
			$community->close_all_restaurants_note = $this->request()[ 'close_all_restaurants_note' ];
			$community->close_all_restaurants_id_admin = c::admin()->id_admin;
			$community->close_all_restaurants_date = date( 'Y-m-d H:i:s' );
			$community->save();
			echo json_encode( [ 'success' => true ] );
		} else {
			echo json_encode( [ 'error' => 'invalid object' ] );
		}

		exit;
	}

	public function autoClosedMessage(){
		$id_community = $this->request()[ 'id_community' ];
		$community = Crunchbutton_Community::o( $id_community );
		$community->driver_restaurant_name = $this->request()[ 'auto_closed_message' ];
		$community->save();
		echo json_encode( [ 'success' => true ] );
		exit;
	}

	public function close3rdParty(){
		$id_community = $this->request()[ 'id_community' ];
		$community = Crunchbutton_Community::o( $id_community );
		$community->close_3rd_party_delivery_restaurants = $this->request()[ 'close_3rd_party_delivery_restaurants' ];
		$community->close_3rd_party_delivery_restaurants_note = $this->request()[ 'close_3rd_party_delivery_restaurants_note' ];
		$community->close_3rd_party_delivery_restaurants_id_admin = c::admin()->id_admin;
		$community->close_3rd_party_delivery_restaurants_date = date( 'Y-m-d H:i:s' );
		$community->save();
		echo json_encode( [ 'success' => true ] );
		exit;
	}

}
