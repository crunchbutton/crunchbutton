<?php

class Controller_api_communities_closed extends Crunchbutton_Controller_Rest {

	public function init() {
		die('depreciated');
		$out = [];
		$communities = Crunchbutton_Community::q( 'SELECT * FROM community WHERE ( close_all_restaurants = 1 OR close_3rd_party_delivery_restaurants = 1 ) ORDER BY name ASC' );
		foreach( $communities as $community ){
			$data = [ 'name' => $community->name, 'id_community' => $community->id_community, 'permalink' => $community->permalink ];
			$data[ 'log' ] = $closed_log = $community->closedSince();
			$out[] = $data;
		}
		echo json_encode( $out );exit;
	}
}