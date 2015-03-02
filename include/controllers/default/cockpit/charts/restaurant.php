<?php

class Controller_charts_restaurant extends Crunchbutton_Controller_Account {

	public function init() {
		if (!c::admin()->permission()->check( [ 'global', 'metrics-all', 'metrics-restaurants-page'] )) {
			return ;
		}

		$hasPermissionFullPermission = c::admin()->permission()->check( [ 'global', 'metrics-all' ] );

		$query = 'SELECT * FROM restaurant WHERE active = true';

		if( !$hasPermissionFullPermission ){
			$restaurants = c::admin()->getRestaurantsUserHasPermissionToSeeTheirMetrics();	
			$query .= ' AND id_restaurant IN (' . join( ',', $restaurants ) . ')';
		} 

		$restaurants = Crunchbutton_Restaurant::q( $query );

		c::view()->restaurants = $restaurants;
		c::view()->display( 'charts/restaurant/index' );
	}
}