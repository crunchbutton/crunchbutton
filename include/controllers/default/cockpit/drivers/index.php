<?php

class Controller_drivers extends Crunchbutton_Controller_Account {
	
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