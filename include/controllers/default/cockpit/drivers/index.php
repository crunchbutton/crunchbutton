<?php

class Controller_drivers extends Crunchbutton_Controller_Account {

	const ADMIN_DRIVER_LIST_TZ = 'admin-driver-list-tz';

	public function init() {

		switch ( c::getPagePiece( 1 ) ){

			case 'deliveries':
					if ( c::admin()->permission()->check( [ 'global','drivers-all', 'drivers-orders-view' ] ) ) {
						$this->deliveries();
					}
				break;

			case 'schedule':
					if ( c::admin()->permission()->check( [ 'global','drivers-all', 'drivers-working-hours', 'drivers-working-hours-view' ] ) ) {
						$this->schedule();
					}
				break;

			default:
				if ( c::admin()->permission()->check( [ 'global','drivers-all', 'drivers-page' ] ) ) {
					c::view()->page = 'drivers';
					c::view()->display( 'drivers/index' );
				} else {
					c::view()->display( 'home/index' );
				}
				break;
		}
	}

	public function schedule(){
		switch ( c::getPagePiece( 2 ) ){
			default:

					$date = ( c::getPagePiece( 2 ) ? c::getPagePiece( 2 ) : date( 'Y-m-d' ) );

					$date = new DateTime( $date, new DateTimeZone( c::config()->timezone ) );
					$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );

					c::view()->date = $date->format( 'Y-m-d' );
					c::view()->dateFormatted = $date->format( 'M jS Y D' );

					$date->modify( '+ 1 day' );
					if( $date->format( 'Ymd' ) <= ( $now->format( 'Ymd' ) + 5 ) ){
						c::view()->dayNext = $date->format( 'Y-m-d' );
					}

					$date->modify( '- 2 days' );
					if( $date->format( 'Ymd' ) >= ( $now->format( 'Ymd' ) - 1 ) ){
						c::view()->dayPrev = $date->format( 'Y-m-d' );
					}

					c::view()->restaurants = Restaurant::withDrivers();
					c::view()->page = 'drivers';
					c::view()->display( 'drivers/schedule/index' );
				break;
		}
	}

	public function deliveries(){

		$admin = Admin::o( c::admin()->id_admin );
		$default_tz = $admin->getConfig( Controller_drivers::ADMIN_DRIVER_LIST_TZ );

		if( $default_tz->id_admin_config ){
			$default_tz = $default_tz->value;
		} else {
			$default_tz = 'admin-tz';
		}

		c::view()->tz_default = $default_tz;
		c::view()->admin_tz = c::admin()->timezone;

		switch ( c::getPagePiece( 2 ) ){

			case 'content':
				$search = [];
				if ( $_REQUEST[ 'limit' ] ) {
					$search[ 'limit' ] = intval( $_REQUEST[ 'limit' ] );
				}
				if ( $_REQUEST[ 'dates' ] ) {
					$dates = explode( ',', $_REQUEST[ 'dates' ] );
					$search[ 'start' ] = $dates[ 0 ];
					$search[ 'end' ] = $dates[ 1 ];
				}
				if ( $_REQUEST[ 'id_restaurant' ] ) {
					$search[ 'id_restaurant' ] = $_REQUEST[ 'id_restaurant' ];
				}
				if ( $_REQUEST[ 'id_admin' ] ) {
					$search[ 'id_admin' ] = $_REQUEST[ 'id_admin' ];
				}

				c::view()->orders = Order::deliveredByCBDrivers( $search );
				if ( $_REQUEST[ 'export' ] ) {
					c::view()->layout( 'layout/csv' );
					c::view()->display( 'orders/csv', ['display' => true, 'filter' => false]);
				} else {
					c::view()->layout( 'layout/ajax' );
					c::view()->display( 'drivers/deliveries/content' );
				}
				break;

			case 'action':
				$id_order = c::getPagePiece( 3 );
				c::view()->id_order = $id_order;
				c::view()->order = Order::o( $id_order );
				c::view()->drivers = Admin::drivers();
				c::view()->actions = [ Crunchbutton_Order_Action::DELIVERY_ACCEPTED, Crunchbutton_Order_Action::DELIVERY_PICKEDUP, Crunchbutton_Order_Action::DELIVERY_DELIVERED, Crunchbutton_Order_Action::DELIVERY_REJECTED ];
				c::view()->layout( 'layout/ajax' );
				c::view()->display( 'drivers/deliveries/action' );
				break;
			default:

				$id_admin = c::getPagePiece( 2 );

				c::view()->restaurants = Restaurant::withDrivers();
				c::view()->id_admin = $id_admin;
				c::view()->drivers = Admin::drivers();
				c::view()->page = 'drivers';
				c::view()->display( 'drivers/deliveries/index' );
				break;


		}
	}

}