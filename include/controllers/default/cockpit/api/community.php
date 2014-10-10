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
				}

			break;
			default:
				echo json_encode( [ 'error' => 'invalid object' ] );
			break;
		}
	}

	public function closeAll(){
		$id_community = $this->request()[ 'id_community' ];
		$community = Crunchbutton_Community::o( $id_community );
		if( $community->id_community ){
			$community->close_all_restaurants = $this->request()[ 'close_all_restaurants' ];
			$community->close_all_restaurants_note = $this->request()[ 'close_all_restaurants_note' ];
			$community->save();
			echo json_encode( [ 'success' => true ] );
		} else {
			echo json_encode( [ 'error' => 'invalid object' ] );
		}

		exit;
	}

	public function close3rdParty(){
		$id_community = $this->request()[ 'id_community' ];
		$community = Crunchbutton_Community::o( $id_community );
		$community->close_3rd_party_delivery_restaurants = $this->request()[ 'close_3rd_party_delivery_restaurants' ];
		$community->close_3rd_party_delivery_restaurants_note = $this->request()[ 'close_3rd_party_delivery_restaurants_note' ];
		$community->save();
		echo json_encode( [ 'success' => true ] );
		exit;
	}

}
