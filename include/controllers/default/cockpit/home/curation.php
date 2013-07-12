<?php

class Controller_home_curation extends Crunchbutton_Controller_Account {

	public function init() {

		$orderByCategory = ( c::getPagePiece(2) == 'category' );
		$showInactive = ( $_GET[ 'showInactive' ] ) ? $_GET[ 'showInactive' ] : 0;

		$totalFoodOrdered = Crunchbutton_Order_Dish::totalDishesOrdered();

		$data = [];
		$restaurants = Restaurant::q('SELECT * FROM restaurant WHERE active = 1 ORDER BY name ASC');
		foreach( $restaurants as $restaurant ){
			$data[ $restaurant->id_restaurant ] = [];
			$data[ $restaurant->id_restaurant ][ 'Name' ] = $restaurant->name;
			$data[ $restaurant->id_restaurant ][ 'Food' ] = $restaurant->foodReport( $orderByCategory, ( $showInactive == 1 ) );
		}

	c::view()->totalFoodOrdered = $totalFoodOrdered;
	c::view()->data = $data;
	c::view()->display('home/curation');

	}
}