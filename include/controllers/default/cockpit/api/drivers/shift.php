<?php

class Controller_api_drivers_shift extends Crunchbutton_Controller_RestAccount {
	
	public function init() {
		switch ( c::getPagePiece( 3 ) ) {
			case 'community':
				$this->community();				
				break;
			
			default:
				echo json_encode( [ 'error' => 'invalid object' ] );
				break;
		}
	}

	public function community(){

		switch ( c::getPagePiece( 4 ) ) {

			case 'edit':
				$id_community_shift = $this->request()[ 'id_community_shift' ];
				$segments = $this->request()[ 'hours' ];
				$shift = Crunchbutton_Community_Shift::o( $id_community_shift );
				$date_base = $shift->dateStart();
				$_hours = Crunchbutton_Admin_Hour::segmentToDate( $date_base, $segments, $timezone );
				if( $_hours ){
					$shift->date_start = $_hours[ 'start' ];
					$shift->date_end = $_hours[ 'end' ];
					if( $shift->date_start && $shift->date_end ){
						$shift->save();	
					}
				}
				echo json_encode( array( 'success' => true ) );
				break;

			case 'remove':
				$id_community_shift = $this->request()[ 'id_community_shift' ];
				Crunchbutton_Community_Shift::remove( $id_community_shift );
				echo json_encode( array( 'success' => true ) );
				break;


			case 'add':
				$id_community = $this->request()[ 'id_community' ];
				$day = $this->request()[ 'day' ];
				$month = $this->request()[ 'month' ];
				$year = $this->request()[ 'year' ];
				$week = $this->request()[ 'week' ];
				$segments = $this->request()[ 'hours' ];
				$weekdays = $this->request()[ 'weekdays' ] ;

				$community = Crunchbutton_Community::o( $id_community );

				$timezone = $community->timezone;

				if( !$id_community || !$day || !$month || !$year ){
					echo json_encode( [ 'error' => 'invalid object' ] );
					exit;
				}

				$hours = [];

				if( count( $weekdays ) > 0 ){
					foreach( $weekdays as $weekday ){
						$date_base = DateTime::createFromFormat( 'Y-m-d H:i:s', $weekday . ' 00:00:00', new DateTimeZone( $timezone ) );
						$_hours = Crunchbutton_Admin_Hour::segmentToDate( $date_base, $segments, $timezone );
						if( $_hours ){
							$hours[] = [ 'start' => $_hours[ 'start' ], 'end' => $_hours[ 'end' ] ];	
						}
					}
				} else {
					// add just the hour for the day
					$date_base = DateTime::createFromFormat( 'Y-m-d H:i:s', $year . '-' . $month . '-' . $day . ' 00:00:00', new DateTimeZone( $timezone ) );
					$_hours = Crunchbutton_Admin_Hour::segmentToDate( $date_base, $segments, $timezone );
					if( $_hours ){
						$hours[] = [ 'start' => $_hours[ 'start' ], 'end' => $_hours[ 'end' ] ];	
					}
				}

				foreach( $hours as $hour ){
					$shift = new Crunchbutton_Community_Shift();
					$shift->id_community = $id_community;
					$shift->date_start = $hour[ 'start' ];
					$shift->date_end = $hour[ 'end' ];
					if( $shift->date_start && $shift->date_end ){
						$shift->save();	
					}
				}

				echo json_encode( array( 'success' => true ) );
				break;
			
			case 'copy-all':

				$year = $this->request()[ 'year' ];
				$week = $this->request()[ 'week' ];
				$id_community = $this->request()[ 'id_community' ];
				

				for( $i = 0; $i <= 6; $i++ ){
					$dateFrom = new DateTime( date( 'Y-m-d', strtotime( $year . 'W' . $week . $i ) ), new DateTimeZone( c::config()->timezone  ) );
					$dateFrom->modify( '- 7 day' );
					$dateTo = new DateTime( date( 'Y-m-d', strtotime( $year . 'W' . $week . $i ) ), new DateTimeZone( c::config()->timezone  ) );
					Crunchbutton_Community_Shift::removeHoursFromDay( $id_community, $dateTo->format( 'Y-m-d' ) );
					Crunchbutton_Community_Shift::copyHoursFromTo( $id_community, $dateFrom->format( 'Y-m-d' ), $dateTo->format( 'Y-m-d' ) );
				}				
				echo json_encode( array( 'success' => true ) );
				break;
			default:
				echo json_encode( [ 'error' => 'invalid object' ] );
				break;
		}
	}
}
