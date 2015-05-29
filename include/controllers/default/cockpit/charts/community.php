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

			case 'delivered-orders':
				c::view()->layout( 'layout/ajax' );
				c::view()->display( 'charts/community/delivered_orders' );
				break;

			case 'orders-per-day':

				$hasPermissionFullPermission = c::admin()->permission()->check( [ 'global', 'metrics-all', 'metrics-communities-all' ] );
				$communities = Crunchbutton_Community::q( 'SELECT * FROM community WHERE active = true ORDER BY name ASC' );

				if( !$hasPermissionFullPermission ){
					$_communities = [];
					foreach ( $communities as $community ) {
						$permission_name = strtolower( $community->name );
						$permission_name = str_replace( ' ' , '-', $permission_name );
						$permission_name = "metrics-communities-{$permission_name}";
						if( c::admin()->permission()->check( [ $permission_name ] ) ){
							$_communities[] = $community;
						}
					}
				} else {
					$_communities = $communities;
				}
				c::view()->communities = $_communities;
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
		$communities = Crunchbutton_Community::q( 'SELECT * FROM community WHERE active = true' );
		$_communities = [];
		foreach ( $communities as $community ) {
			$permission_name = strtolower( $community->name );
			$permission_name = str_replace( ' ' , '-', $permission_name );
			$permission_name = "metrics-communities-{$permission_name}";
			if( $hasPermissionFullPermission || c::admin()->permission()->check( [ $permission_name ] ) ){
				$_communities[] = $community;
			}
		}
		c::view()->communities = $_communities;
	}
}