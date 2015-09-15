<?php

class Controller_api_communities_closed extends Crunchbutton_Controller_Rest {

	public function init() {
		$out = [];
		$communities = Crunchbutton_Community::q( 'SELECT * FROM community WHERE ( close_all_restaurants = 1 OR close_3rd_party_delivery_restaurants = 1 OR is_auto_closed = 1 ) AND active = 1 ORDER BY name ASC' );
		foreach( $communities as $community ){
			$data = [ 'name' => $community->name, 'id_community' => $community->id_community, 'permalink' => $community->permalink ];
			$data[ 'close_all_restaurants' ] = $community->close_all_restaurants;
			$data[ 'close_3rd_party_delivery_restaurants' ] = $community->close_3rd_party_delivery_restaurants;
			$data[ 'is_auto_closed' ] = $community->is_auto_closed;

			$data[ 'drivers_working' ] = $community->activeDrivers();;
			$data[ 'log' ] = $community->closedSince()[0];
			$out[] = $data;
		}
		echo json_encode( $out );exit;
	}
}