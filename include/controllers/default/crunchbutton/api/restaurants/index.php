<?php

class Controller_api_restaurants extends Crunchbutton_Controller_Rest {
	public function init() {

		$config = [];

		$communities = [];

		if ($_REQUEST['lat'] && $_REQUEST['lon']) {

			$restaurants = Restaurant::byRange([
				'lat' => c::db()->escape($_REQUEST['lat']),
				'lon' => c::db()->escape($_REQUEST['lon']),
				'range' => c::db()->escape($_REQUEST['range']),
			]);

			// check if the community is closed
			$community_closed_message = [];
			$_all_closed = true;
			$id_community = 0;
			foreach ($restaurants as $restaurant) {
				$data = $restaurant->exports( [ 'categories' => true ] );
				$communities[ $restaurant->community()->id_community ] = true;
				$id_community = $restaurant->community()->id_community;
				if( $restaurant->open() ){
					$_all_closed = false;
				} else {
					$community_closed_message[ $data[ 'closed_message' ] ] = $data[ 'closed_message' ];
				}
				if( !$data[ 'closed_message' ] ){
					$community_closed_message[ 'no_closed_message' ] = true;
				} else {

				}

				$data[ 'top_name' ] = $restaurant->top()->top_name;
				$data[ '_short_description' ] = ( $data[ 'short_description' ] ? $data[ 'short_description' ] : (  $data[ 'top_name' ] ? 'Top Order: ' . $data[ 'top_name' ] : ''  ) );

				if( intval( $restaurant->open_for_business ) == 0 && trim( $restaurant->force_close_tagline ) ){
					$data[ 'short_description' ] = $restaurant->force_close_tagline;
					$data[ '_short_description' ] = $restaurant->force_close_tagline;
				}

				$config['restaurants'][] = $data;
			}

			// change driver restaurant name when auto shutting down community #4514
			if( count( $communities ) == 1 && $id_community ){
				$community = Community::o( $id_community );
				if( $community->allThirdPartyDeliveryRestaurantsClosed() || $community->allRestaurantsClosed() ){
					// Check if the community was auto shutdown
					$autoShutdownAdmin = Admin::login( Crunchbutton_Community::AUTO_SHUTDOWN_COMMUNITY_LOGIN );
					$id_admin = $autoShutdownAdmin->id_admin;
					if( $id_admin == $community->close_3rd_party_delivery_restaurants_id_admin ){
						$driverRestaurant = $community->driverRestaurant();
						if( $driverRestaurant->id_restaurant ){
							for( $i = 0; $i < count( $config['restaurants'] ); $i++ ){
								if( $config['restaurants'][ $i ][ 'id_restaurant' ] == $driverRestaurant->id_restaurant ){
									$config['restaurants'][ $i ][ 'name' ] = $community->driver_restaurant_name;
								}
							}
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
		}
		echo json_encode($config);
	}
}