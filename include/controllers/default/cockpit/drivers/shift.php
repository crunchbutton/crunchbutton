<?php

class Controller_drivers_shift extends Crunchbutton_Controller_Account {
	
	public function init() {

		c::view()->page = 'drivers';

		switch ( c::getPagePiece( 2 ) ) {
			case 'community':
				
				switch ( c::getPagePiece( 3 ) ) {
					case 'add':
						$this->communityAdd();
						break;
					
					default:
						$this->community();
						break;
				}
				break;
			
			case 'schedule':
				switch ( c::getPagePiece( 3 ) ) {
					case 'driver':
						$this->scheduleDriver();
					break;
					default:
					break;
				}
				break;

			default:
				
				break;
		}
	}

	public function scheduleDriver(){
		$admin = Admin::o( c::admin()->id_admin );
		c::view()->communities = $admin->communitiesHeDeliveriesFor();
		c::view()->display( 'drivers/shift/schedule/driver' );
	}

	public function communityAdd(){
		$id_community = c::getPagePiece( 4 );
		$year = c::getPagePiece( 5 ) ? c::getPagePiece( 5 ) : date( 'Y' );
		$month = c::getPagePiece( 6 ) ? c::getPagePiece( 6 ) : date( 'm' );
		$day = c::getPagePiece( 7 ) ? c::getPagePiece( 7 ) : date( 'd' );
		$week = c::getPagePiece( 8 ) ? c::getPagePiece( 8 ) : date( 'W' );

		$days = [];
		for( $i = 0; $i <= 6; $i++ ){
			$days[] = new DateTime( date( 'Y-m-d', strtotime( $year . 'W' . $week . $i ) ), new DateTimeZone( c::config()->timezone  ) );
		}

		if( $id_community ){
			c::view()->year = $year;
			c::view()->month = $month;
			c::view()->day = $day;
			c::view()->days = $days;
			c::view()->community = Crunchbutton_Community::o( $id_community );
			c::view()->layout( 'layout/ajax' );
			c::view()->display( 'drivers/shift/community/add' );
		}
	}

	public function community(){
		
		$id_community = c::getPagePiece( 3 );

		if( $id_community ){

			$year = c::getPagePiece( 4 ) ? c::getPagePiece( 4 ) : date( 'Y' ) ;
			$week = c::getPagePiece( 5 ) ? c::getPagePiece( 5 ) : date( 'W' ) ;

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
			c::view()->week = $week;
			c::view()->year = $year;
			c::view()->days = $days;
			c::view()->from = new DateTime( date( 'Y-m-d', strtotime( $year . 'W' . $week . '0' ) ), new DateTimeZone( c::config()->timezone  ) );
			c::view()->to = new DateTime( date( 'Y-m-d', strtotime( $year . 'W' . $week . '6' ) ), new DateTimeZone( c::config()->timezone  ) );
			

			/*$year = c::getPagePiece( 4 );
			$week = c::getPagePiece( 5 );
			$today = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
			if( !$year ){
				$year = $today->format( 'Y' );
			}
			if( !$week ){
				$week = $today->format( 'W' );
			}
			$week = ( intval( $week ) < 10 ) ? '0' . $week : $week;
			c::view()->week = $week;
			c::view()->year = $year;
			c::view()->days = $days;
			// c::view()->segments = Crunchbutton_Community_Shift::shiftByCommunity( $id_community );		
			*/
		}
		c::view()->id_community = $id_community;
		c::view()->display( 'drivers/shift/community/index' );
	}
}