<?php

class Controller_drivers_hours extends Crunchbutton_Controller_Account {
	
	public function init() {
		die('#5430 deprecated');
		/*

		if (!c::admin()->permission()->check(['global', 'drivers-all', 'drivers-working-hours', 'drivers-working-hours-view'])) {
			return ;
		}

		$hasEditPermission = c::admin()->permission()->check(['global','drivers-working-hours', 'drivers-all']);

		$reps = Admin::q( 'SELECT DISTINCT( a.id_admin ) id, a.* FROM admin a INNER JOIN admin_notification an ON an.id_admin = a.id_admin AND an.active = true ORDER BY name ASC' );

		switch ( c::getPagePiece(2) ) {
			
			case 'edit':

				if (!$hasEditPermission) { return ; }

				$year = c::getPagePiece(3) ? c::getPagePiece(3) : date( 'Y' );
				$month = c::getPagePiece(4) ? c::getPagePiece(4) : date( 'm' );
				$day = c::getPagePiece(5) ? c::getPagePiece(5) : date( 'd' );
				$id_admin = c::getPagePiece(6);

				if( !$id_admin ){
					return '';
				}

				$date = $year . '-' . $month . '-' . $day;

				$hours = Admin_Hour::segmentsByDate( $date, ', ', $id_admin );

				$admin = Admin::o( $id_admin );

				$restaurants = $admin->restaurantsHeDeliveryFor();
				$deliveryFor = [];
				foreach( $restaurants as $restaurant ){
					$deliveryFor[ $restaurant->id_restaurant ] = $restaurant->name;
				}

				$adminCommunities = [];
				$groups = $admin->groups();
				foreach ( $groups as $group ) {
					$pos = strrpos( $group->name, Crunchbutton_Group::DRIVER_GROUPS_PREFIX );
					if( $pos !== false ){
						$adminCommunities[ $group->name ] = Group::getRestaurantCommunityName( $group->name );	
					}
				}

				c::view()->restaurants = $deliveryFor;
				c::view()->communities = $adminCommunities;
				c::view()->month = $month;
				c::view()->day = $day;
				c::view()->year = $year;
				c::view()->segment = $hours[ $id_admin ][ 'hours' ];
				c::view()->admin = $admin;
				c::view()->layout( 'layout/ajax' );
				c::view()->display( 'drivers/hours/edit' );

				break;

			case 'add':

				if (!$hasEditPermission) { return ; }

				$year = c::getPagePiece(3) ? c::getPagePiece(3) : date( 'Y' );
				$month = c::getPagePiece(4) ? c::getPagePiece(4) : date( 'm' );
				$day = c::getPagePiece(5) ? c::getPagePiece(5) : date( 'd' );
				$week = c::getPagePiece(6) ? c::getPagePiece(6) : date( 'W' );

				$id_admin = c::getPagePiece(7) ? c::getPagePiece(7) : false;

				$days = [];
				for( $i = 0; $i <= 6; $i++ ){
					$days[] = new DateTime( date( 'Y-m-d', strtotime( $year . 'W' . $week . $i ) ), new DateTimeZone( c::config()->timezone  ) );
				}

				c::view()->id_admin = $id_admin;
				c::view()->year = $year;
				c::view()->month = $month;
				c::view()->day = $day;
				c::view()->days = $days;
				c::view()->week = $week;
				c::view()->reps = $reps;
				c::view()->layout( 'layout/ajax' );
				c::view()->display( 'drivers/hours/add' );

				break;
			
			default:

				$year = c::getPagePiece(2) ? c::getPagePiece(2) : date( 'Y' ) ;
				$week = c::getPagePiece(3) ? c::getPagePiece(3) : date( 'W' ) ;

				if( intval( $week ) < 10 ){
					$week = '0' . intval( $week );
				}

				$days = [];
				for( $i = 0; $i <= 6; $i++ ){
					$days[] = new DateTime( date( 'Y-m-d', strtotime( $year . 'W' . $week . $i ) ), new DateTimeZone( c::config()->timezone  ) );
				}

				if( $week <= 1 ){
					$weekPrev = ( $year - 1 ) . '/52';
				} else {
					$weekPrev = ( $year ) . '/' . ( $week - 1 );
				}
				if( $week >= 52 ){
					$weekNext = ( $year + 1 ) . '/01';
				} else {
					$weekNext = ( $year ) . '/' . ( $week + 1 );
				}

				c::view()->weekPrev = $weekPrev;
				c::view()->weekNext = $weekNext;

				c::view()->page = 'permissions';
				c::view()->week = $week;
				c::view()->year = $year;
				c::view()->days = $days;
				c::view()->reps = $reps;
				c::view()->hasEditPermission = $hasEditPermission;
				c::view()->startDate = $startDate;
				c::view()->display( 'drivers/hours/index' );

				break;
		}
		*/

	}
}