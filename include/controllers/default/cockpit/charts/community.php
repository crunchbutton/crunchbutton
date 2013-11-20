<?php

class Controller_charts_community extends Crunchbutton_Controller_Account {

	public function init() {
		if (!c::admin()->permission()->check( [ 'global', 'metrics-all', 'metrics-communities', 'metrics-communities-page'] )) {
			return ;
		}

		$hasPermissionFullPermission = c::admin()->permission()->check( [ 'global', 'metrics-all', 'metrics-communities-all' ] );

		$communities = Restaurant::getCommunitiesWithRestaurantsNumber();

		if( !$hasPermissionFullPermission ){
			$_communities = [];
			foreach ( $communities as $community ) {
				$permission_name = strtolower( $community->community );
				$permission_name = str_replace( ' ' , '-', $permission_name );
				$permission_name = "metrics-communities-{$permission_name}";
				if( c::admin()->permission()->check( [ $permission_name ] ) ){
					$_communities[] = $community;
				}
			}
			$communities = $_communities;
		}
		c::view()->communities = $communities;

		if( c::getPagePiece( 2 ) == 'delivery-tips' ){
			c::view()->display( 'charts/community/delivery_tips' );
		} else {
			c::view()->display( 'charts/community/index' );
		}
	}
}