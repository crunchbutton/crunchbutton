<?php

class Controller_api_drivers_shift extends Crunchbutton_Controller_RestAccount {

	public function init() {
		switch ( c::getPagePiece( 3 ) ) {
			case 'community':
				$this->community();
				break;

			case 'driver':
				$this->driver();
				break;

			case 'driver-schedule-sms-config':
				$this->driverScheduleSMSConfig();
				break;

			case 'driver-note-update':
				$this->driverNoteUpdate();
				break;

			case 'driver-orders-per-hour':
				$this->driverOrdersPerHour();
				break;

			default:
				echo json_encode( [ 'error' => 'invalid object' ] );
				break;
		}
	}

	public function driverScheduleSMSConfig(){
		$id_admin = $this->request()[ 'id_admin' ];
		$admin = Admin::o( $id_admin );
		if( $admin->id_admin ){
			$value = $this->request()[ 'value' ];
			$value = ( $value && $value > 0 ) ? 1 : 0;
			$admin->setConfig( Crunchbutton_Admin::CONFIG_RECEIVE_DRIVER_SCHEDULE_SMS_WARNING, $value );
			echo json_encode( [ 'success' => 'success' ] );
		} else {
			echo json_encode( [ 'error' => 'invalid object' ] );
		}
	}

	public function driverOrdersPerHour(){
		$id_admin = $this->request()[ 'id_admin' ];
		$admin = Admin::o( $id_admin );
		if( $admin->id_admin ){
			$orders = $this->request()[ 'orders' ];
			$admin->saveOrdersPerHour( $orders );
			echo json_encode( [ 'success' => true ] );
		} else {
			echo json_encode( [ 'error' => 'invalid object' ] );
		}
	}

	public function driverNoteUpdate(){
		$id_admin = $this->request()[ 'id_admin' ];
		$admin = Admin::o( $id_admin );
		if( $admin->id_admin ){
			$text = $this->request()[ 'text' ];
			$admin->addNote( $text );
			$note = $admin->note();
			echo json_encode( [ 'success' => $note->exports() ] );
		} else {
			echo json_encode( [ 'error' => 'invalid object' ] );
		}
	}

	public function driverAssign(){
		$ids_admin = $this->request()[ 'id_admin' ];
		$id_community_shift = $this->request()[ 'id_community_shift' ];
		$ids_admin_permanently = $this->request()[ 'id_admin_permanently' ];

		$permanently = [];
		if( count( $ids_admin_permanently ) > 0 ){
			foreach( $ids_admin_permanently as $id_admin_permanently ){
				$permanently[ $id_admin_permanently ] = true;
			}
		}


		if( $id_community_shift ){
			Crunchbutton_Admin_Shift_Assign::removeAssignment( $id_community_shift );
			if( count( $ids_admin ) > 0 ){
				foreach( $ids_admin as $id_admin ){
					$_permanently = ( $permanently[ $id_admin ] ) ? true : false;
					Crunchbutton_Admin_Shift_Assign::assignAdminToShift( $id_admin, $id_community_shift, $_permanently );
				}
			}
			echo json_encode( array( 'success' => true ) );
		} else {
			echo json_encode( [ 'error' => 'invalid object' ] );
		}
	}

	public function driver(){

		switch ( c::getPagePiece( 4 ) ) {

			case 'assign':
				$this->driverAssign();
				break;

			default:
				$allItems = $this->request()[ 'allItems' ];
				$wantWorkItems = $this->request()[ 'wantWorkItems' ];
				$dontWantWorkItems = $this->request()[ 'dontWantWorkItems' ];
				$completed = $this->request()[ 'completed' ];
				$shifts = $this->request()[ 'shifts' ];
				$year = $this->request()[ 'year' ];
				$week = $this->request()[ 'week' ];

				if( count( $allItems ) > 0 ){

					$id_admin = c::admin()->id_admin;

					$status = Crunchbutton_Admin_Shift_Status::getByAdminWeekYear( $id_admin, $week, $year );
					$status->completed = $completed;
					$status->shifts = $shifts;
					$status->date = date('Y-m-d H:i:s');
					$status->save();

					// remove all items
					if( count( $allItems ) > 0 ){
						foreach( $allItems as $item ){
							Crunchbutton_Admin_Shift_Preference::removeByAdminShift( $id_admin, $item );
						}
					}
					// wantWorkItems
					$count = 1;
					if( count( $wantWorkItems ) > 0 ){
						foreach( $wantWorkItems as $item ){
							$preference = new Crunchbutton_Admin_Shift_Preference();
							$preference->id_admin = $id_admin;
							$preference->id_community_shift = $item;
							$preference->ranking = $count;
							$preference->save();
							$count++;
						}
					}

					// dontWantWorkItems
					if( count( $dontWantWorkItems ) > 0 ){
						foreach( $dontWantWorkItems as $item ){
							$preference = new Crunchbutton_Admin_Shift_Preference();
							$preference->id_admin = $id_admin;
							$preference->id_community_shift = $item;
							$preference->ranking = 0;
							$preference->save();
						}
					}
					echo json_encode( array( 'success' => true ) );

				} else {
					echo json_encode( [ 'error' => 'invalid object' ] );
				}
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
				$recurring = $this->request()[ 'recurring' ];
				if( $recurring > 0 ){
					Crunchbutton_Community_Shift::removeRecurring( $id_community_shift );
				} else {
					Crunchbutton_Community_Shift::remove( $id_community_shift );
				}
				echo json_encode( array( 'success' => true ) );
				break;


			case 'add':
				$id_community = $this->request()[ 'id_community' ];
				$day = $this->request()[ 'day' ];
				$month = $this->request()[ 'month' ];
				$year = $this->request()[ 'year' ];
				$week = $this->request()[ 'week' ];
				$segments = $this->request()[ 'hours' ];
				$weekdays = $this->request()[ 'weekdays' ];
				$recurring = $this->request()[ 'recurring' ];

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
					$shift->recurring = $recurring;
					$shift->active = 1;
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

				// Start week at monday #2666
				$firstDay = new DateTime( date( 'Y-m-d', strtotime( $year . 'W' . $week . 1 ) ), new DateTimeZone( c::config()->timezone  ) );

				$days = [];
				for( $i = 0; $i <= 6; $i++ ){
					$days[] = new DateTime( $firstDay->format( 'Y-m-d' ), new DateTimeZone( c::config()->timezone  ) );
					$firstDay->modify( '+ 1 day' );
				}

				foreach( $days as $day ){
					$dateFrom = new DateTime( $day->format( 'Y-m-d' ), new DateTimeZone( c::config()->timezone  ) );
					$dateFrom->modify( '- 7 day' );
					$dateTo = new DateTime( $day->format( 'Y-m-d' ), new DateTimeZone( c::config()->timezone  ) );
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
