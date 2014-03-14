<?php

class Controller_charts_community extends Crunchbutton_Controller_Account {

	public function init() {
		if (!c::admin()->permission()->check( [ 'global', 'metrics-all', 'metrics-communities', 'metrics-communities-page'] )) {
			return ;
		}

		switch ( c::getPagePiece( 2 ) ) {
			case 'delivery-tips':
				$this->data();
				c::view()->display( 'charts/community/delivery_tips' );
				break;
			
			case 'orders-per-day':
				$hasPermissionFullPermission = c::admin()->permission()->check( [ 'global', 'metrics-all', 'metrics-communities-all' ] );
				$communities = Restaurant::q( "SELECT DISTINCT( community ) FROM restaurant r INNER JOIN `order` o ON r.id_restaurant = o.id_restaurant WHERE o.date BETWEEN CURDATE() - INTERVAL 14 DAY AND CURDATE()" );
				$_communities = [];
				foreach ( $communities as $community ) {
					$permission_name = strtolower( $community->community );
					$permission_name = str_replace( ' ' , '-', $permission_name );
					$permission_name = "metrics-communities-{$permission_name}";
					if( $hasPermissionFullPermission || c::admin()->permission()->check( [ $permission_name ] ) ){
						$_communities[] = $community;
					}
				}
				c::view()->communities = $communities;
				c::view()->display( 'charts/community/orders_per_day' );
				break;

			default:
				$this->data();
				c::view()->display( 'charts/community/index' );
				break;
		}
	}

	public function data(){
		$hasPermissionFullPermission = c::admin()->permission()->check( [ 'global', 'metrics-all', 'metrics-communities-all' ] );
		$communities = Restaurant::getCommunitiesWithRestaurantsNumber();
		$_communities = [];
		foreach ( $communities as $community ) {
			$permission_name = strtolower( $community->community );
			$permission_name = str_replace( ' ' , '-', $permission_name );
			$permission_name = "metrics-communities-{$permission_name}";
			if( $hasPermissionFullPermission || c::admin()->permission()->check( [ $permission_name ] ) ){
				$_communities[] = $community;
			}
		}
		c::view()->communities = $communities;
	}
}