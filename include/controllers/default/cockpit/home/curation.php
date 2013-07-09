<?php

class Controller_home_curation extends Crunchbutton_Controller_Account {

	public function init() {

		$orderByCategory = ( c::getPagePiece(2) == 'category' );

		$data = [];
		$restaurants = Restaurant::q('SELECT * FROM restaurant WHERE active = 1 ORDER BY name ASC');
		foreach( $restaurants as $restaurant ){
			$data[ $restaurant->id_restaurant ] = [];
			$data[ $restaurant->id_restaurant ][ 'Name' ] = $restaurant->name;
			$data[ $restaurant->id_restaurant ][ 'Food' ] = [];
			$foods = $restaurant->foodReport( $orderByCategory );
			foreach( $foods as $food ){
				$data[ $restaurant->id_restaurant ][ 'Food' ][] = array( 'name' => $food->dish, 'times' => $food->times, 'category' => $food->category );
			}
		}

	c::view()->data = $data;
	c::view()->display('home/curation');

	}
}