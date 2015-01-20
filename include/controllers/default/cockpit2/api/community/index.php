<?php

class Controller_api_community extends Crunchbutton_Controller_RestAccount {

	public function init() {

		if (!c::admin()->permission()->check(['global', 'support-all', 'support-view', 'support-crud'])) {
			header('HTTP/1.1 401 Unauthorized');
			exit;
		}

		$community = Community::permalink(c::getPagePiece(2));

		if (!$community->id_community) {
			$community = Community::o(c::getPagePiece(2));
		}

		if (!$community->id_community) {
			header('HTTP/1.0 404 Not Found');
			exit;
		}

		switch ($this->method()) {
			case 'get':
				echo $community->json();
				break;

			case 'post':
				$id_community = $this->request()[ 'id_community' ];
				if( $id_community ){
					$community = Crunchbutton_Community::o( $id_community );
					if( !$community->id_community ){
						$community = new Crunchbutton_Community;
					}
				} else {
					$community = new Crunchbutton_Community;
				}
				$community->active = $this->request()[ 'active' ];
				$community->close_3rd_party_delivery_restaurants = $this->request()[ 'close_3rd_party_delivery_restaurants' ];
				$community->close_3rd_party_delivery_restaurants_date = $this->request()[ 'close_3rd_party_delivery_restaurants_date' ];
				$community->close_3rd_party_delivery_restaurants_id_admin = $this->request()[ 'close_3rd_party_delivery_restaurants_id_admin' ];
				$community->close_3rd_party_delivery_restaurants_note = $this->request()[ 'close_3rd_party_delivery_restaurants_note' ];
				$community->close_all_restaurants = $this->request()[ 'close_all_restaurants' ];
				$community->close_all_restaurants_date = $this->request()[ 'close_all_restaurants_date' ];
				$community->close_all_restaurants_id_admin = $this->request()[ 'close_all_restaurants_id_admin' ];
				$community->close_all_restaurants_note = $this->request()[ 'close_all_restaurants_note' ];
				// $community->active = $this->request()[ 'driver_group' ];
				$community->image = $this->request()[ 'image' ];
				$community->loc_lat = $this->request()[ 'loc_lat' ];
				$community->loc_lon = $this->request()[ 'loc_lon' ];
				$community->name = $this->request()[ 'name' ];
				$community->name_alt = $this->request()[ 'name_alt' ];
				$community->permalink = $this->request()[ 'permalink' ];
				$community->prep = $this->request()[ 'prep' ];
				$community->private = $this->request()[ 'private' ];
				$community->range = $this->request()[ 'range' ];
				$community->timezone = $this->request()[ 'timezone' ];
				$community->save();
				if( $community->id_community ){
					echo $community->json();
				} else {
					$this->_error( 'error' );
				}

				break;
		}
	}

	private function _error( $error = 'invalid request' ){
		echo json_encode( [ 'error' => $error ] );
		exit();
	}

}