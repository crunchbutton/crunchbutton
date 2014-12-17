<?php

class Controller_api_restaurants extends Crunchbutton_Controller_Rest {
	public function init() {
		$config = [];
		if ($_REQUEST['lat'] && $_REQUEST['lon']) {
			$restaurants = Restaurant::byRange([
				'lat' => c::db()->escape($_REQUEST['lat']),
				'lon' => c::db()->escape($_REQUEST['lon']),
				'range' => c::db()->escape($_REQUEST['range']),
			]);

			// check if the community is closed
			$community_closed_message = [];
			$_all_closed = true;
			foreach ($restaurants as $restaurant) {
				$data = $restaurant->exports(['categories' => true]);
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