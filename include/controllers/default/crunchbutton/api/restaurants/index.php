<?php

class Controller_api_restaurants extends Crunchbutton_Controller_Rest {

	public function init() {

		switch ( c::getPagePiece( 2 ) ) {
			case 'hours':
				$this->_hours();
				break;

			case 'hours-gmt':
				$this->_hoursGMT();
				break;

			default:
				$this->_byRange();
				break;
		}
	}

	private function _hoursGMT(){
		$utc_str = gmdate( 'Y/n/d/H/i/s', time() );
		$out = [ 'hours' => $this->_hours( true ), 'gmt' => $utc_str ];
		echo json_encode( $out );exit;
	}

	private function _hours( $return = false ){
		$lat = $this->request()[ 'lat' ];
		$lon = $this->request()[ 'lon' ];
		$range = $this->request()[ 'range' ];

		$ids = [];

		if( $lat && $lon && $range ){
			$restaurants = Restaurant::byRange([
				'lat' => $_REQUEST['lat'],
				'lon' => $_REQUEST['lon'],
				'range' => $_REQUEST['range']
			]);
			foreach ($restaurants as $restaurant) {
				$ids[] = $restaurant->id_restaurant;
			}
		} else {
			$ids = explode( ',', c::getPagePiece( 3 ) );
		}
		$restaurants = [];
		foreach( $ids as $id ){
			$restaurant = Restaurant::o( $id );
			if( $restaurant->active ){
				$restaurants[] = $restaurant;
			}
		}
		$out = [];
		if( count( $restaurants ) ){
			foreach( $restaurants as $restaurant ){
				$out[] = $restaurant->timeInfo();
			}
		}
		if( $return ){
			return $out;
		}
		echo json_encode( $out );exit;
	}

	private function _byRange() {

		$config = [];

		$communities = [];

		if ($_REQUEST['lat'] && $_REQUEST['lon']) {

			$restaurants = Restaurant::byRange([
				'lat' => $_REQUEST['lat'],
				'lon' => $_REQUEST['lon'],
				'range' => $_REQUEST['range']
			]);

			// check if the community is closed
			$community_closed_message = [];
			$_all_closed = true;
			$id_community = 0;

			foreach ($restaurants as $restaurant) {

				$restaurant->byrange = true;

				$data = $restaurant->exports( [ 'categories' => true, 'eta' => true] );

				$communities[ $restaurant->community()->id_community ] = true;
				$id_community = $restaurant->community()->id_community;
				$data['id_community'] = $id_community;
				if( $restaurant->open() ){
					$_all_closed = false;
				} else {
					$community_closed_message[ $data[ 'closed_message' ] ] = $data[ 'closed_message' ];
				}
				if( !$data[ 'closed_message' ] ){
					$community_closed_message[ 'no_closed_message' ] = true;
				}

				$data[ 'top_name' ] = $restaurant->top()->top_name;
				$data[ '_short_description' ] = ( $data[ 'short_description' ] ? $data[ 'short_description' ] : (  $data[ 'top_name' ] ? 'Top Order: ' . $data[ 'top_name' ] : ''  ) );

				if( intval( $restaurant->open_for_business ) == 0 && trim( $restaurant->force_close_tagline ) ){
					$data[ 'short_description' ] = $restaurant->force_close_tagline;
					$data[ '_short_description' ] = $restaurant->force_close_tagline;
				}

				$data[ 'loc_lat' ] = $restaurant->loc_lat;
				$data[ 'loc_long' ] = $restaurant->loc_long;

				if ($data['takeout']) {
					$data['address'] = $restaurant->address;
					$data['phone'] = $restaurant->phone;
				}

				if( $data[ '_open' ] ){
					$data[ 'allow_preorder' ] = $restaurant->allowPreorder();
				}

				if( $data[ '_open' ] || $restaurant->show_when_closed || $_REQUEST['_all']){
					$config['restaurants'][] = $data;
				}
			}

			// change driver restaurant name when auto shutting down community #4514
			if( $id_community ){
				$community = Community::o( $id_community );
				if( !$community->active ){
					echo json_encode( [] );exit;
				}
				$driverRestaurant = $community->driverRestaurant();
				if( $community->is_auto_closed ){
					if( $driverRestaurant->id_restaurant ){
						for( $i = 0; $i < count( $config['restaurants'] ); $i++ ){
							if( $config['restaurants'][ $i ][ 'id_restaurant' ] == $driverRestaurant->id_restaurant ){
								$config['restaurants'][ $i ][ 'name' ] = $community->driverRestaurantName();
							}
						}
					}
					$config[ 'community_closed' ] = $community->driverRestaurantName();
				}

				if( $driverRestaurant->id_restaurant ){
					for( $i = 0; $i < count( $config['restaurants'] ); $i++ ){
						if( $config['restaurants'][ $i ][ 'id_restaurant' ] == $driverRestaurant->id_restaurant ){
							if( !$_all_closed ){
								$config['restaurants'][ $i ][ '_weight' ] = -100;
							}
							$config['restaurants'][ $i ][ 'driver_restaurant' ] = true;

							if( $community->automatic_driver_restaurant_name && trim($community->driver_restaurant_name) != '' ){
								$config['restaurants'][ $i ][ 'name' ] = $community->driver_restaurant_name;
							}
							if(trim($config['restaurants'][ $i ][ 'name' ]) == ''){
								$closedMessage = $community->driverRestaurantName();
								if(!$closedMessage){
									$closedMessage = $community->closed_message;
								}
								$config['restaurants'][ $i ][ 'name' ] = $closedMessage;
							}
						} else {
							$config['restaurants'][ $i ][ 'driver_restaurant' ] = false;
						}
					}
				}
			}

			if( $_all_closed && count( $community_closed_message ) == 1 ){
				$closed_message = false;
				foreach( $community_closed_message as $key => $val ){
					$closed_message = $val;
				}
				$config[ 'community_closed' ] = $closed_message;
			}

			if( $community ){
				$config[ 'community' ] = [ 'id_community' => $community->id_community, 'tagline1' => $community->tagline1, 'tagline2' => $community->tagline2 ];
				if( $community->display_hours_restaurants_page ){
					$config[ 'community' ][ 'operation_hours' ] = $community->operationHours();
				}
			}
		}

		echo json_encode( is_array($config) ? null : $config );
	}
}