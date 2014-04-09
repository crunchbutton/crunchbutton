<?php

class Crunchbutton_Community_Shift extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('community_shift')
			->idVar('id_community_shift')
			->load($id);
	}

	public function driversCouldDeliveryOrder( $id_order ){
		if( !$id_order ){
			return false;
		}

		$order = Order::o( $id_order );
		if( $order->restaurant()->timezone ){
			$now = new DateTime( 'now', new DateTimeZone( $order->restaurant()->timezone ) );	
		} else {
			$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone  ) );
		}
	
		return Admin::q( 'SELECT a.* FROM admin a 
												INNER JOIN admin_shift_assign asa ON asa.id_admin = a.id_admin 
												INNER JOIN community_shift cs ON cs.id_community_shift = asa.id_community_shift
												INNER JOIN restaurant_community rc ON rc.id_community = cs.id_community
												INNER JOIN `order` o ON o.id_restaurant = rc.id_restaurant
											WHERE o.id_order = ' . $id_order . ' AND cs.date_start <= "' . $now->format( 'Y-m-d H:i:s' ) . '" AND cs.date_end >= "' . $now->format( 'Y-m-d H:i:s' ) . '" AND cs.active = 1 ');
	}

	public function shiftsByDay( $date ){
		Crunchbutton_Community_Shift::createRecurringEvent( $date );
		return Crunchbutton_Community_Shift::q( 'SELECT cs.* FROM community_shift cs INNER JOIN community c ON c.id_community = cs.id_community WHERE DATE_FORMAT( cs.date_start, "%Y-%m-%d" ) = "' . $date . '" ORDER BY c.name, cs.date_start ASC' );
	}

	public function shiftsByCommunityPeriod( $id_community, $from, $to ){
		return Crunchbutton_Community_Shift::q( 'SELECT cs.* FROM community_shift cs WHERE DATE_FORMAT( cs.date_start, "%Y-%m-%d" ) >= "' . $from . '" AND DATE_FORMAT( cs.date_end, "%Y-%m-%d" ) <= "' . $to . '" AND id_community = "' . $id_community . '" ORDER BY cs.date_start ASC' );
	}

	public function week(){
		// Start week at monday #2666
		$date = DateTime::createFromFormat( 'Y-m-d H:i:s', $this->dateStart()->format( 'Y-m-d H:i:s' ), new DateTimeZone( $this->timezone() ) );	
		return $date->format( 'W' );
	}

	public function year(){
		// Start week at monday #2666
		$date = DateTime::createFromFormat( 'Y-m-d H:i:s', $this->dateStart()->format( 'Y-m-d H:i:s' ), new DateTimeZone( $this->timezone() ) );	
		return $date->format( 'Y' );
	}

	public function firstDayOfWeek(){
		// Start week at monday #2666
		$year = $this->year();
		$week = $this->week();
		return new DateTime( date( 'Y-m-d', strtotime( $year . 'W' . $week . 1 ) ), new DateTimeZone( c::config()->timezone  ) );
	}

	public function lastDayOfWeek(){
		// Start week at monday #2666
		$year = $this->year();
		$week = $this->week();
		$day = new DateTime( date( 'Y-m-d', strtotime( $year . 'W' . $week . 1 ) ), new DateTimeZone( c::config()->timezone  ) );
		$day->modify( '+ 6 day' );
		return $day;
	}

	public function isRecurring(){
		if( $this->recurringId() ){
			return true;
		}
	}

	public function recurringId(){
		if( $this->recurring ){
			return $this->id_community_shift;
		}
		if( $this->id_community_shift_father ){
			return $this->id_community_shift_father;
		}
		return false;
	}

	public function shiftByCommunityDay( $id_community, $date ){
		Crunchbutton_Community_Shift::createRecurringEvent( $date );
		return Crunchbutton_Community_Shift::q( 'SELECT * FROM community_shift WHERE id_community = "' . $id_community . '" AND DATE_FORMAT( date_start, "%Y-%m-%d" ) = "' . $date . '" AND active = 1 ORDER BY id_community_shift ASC' );
	}

	public function createRecurringEvent( $date ){
		// Search for recurring events
		$now =  new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
		$day =  new DateTime( $date, new DateTimeZone( c::config()->timezone ) );
		$weekday = $day->format( 'w' );
		$shifts = Crunchbutton_Community_Shift::q( 'SELECT * FROM community_shift WHERE recurring = 1 AND DATE_FORMAT( date_start, "%w" ) = "' . $weekday . '"' );
		// Create the recurring events
		foreach( $shifts as $shift ){
			if( $shift->dateStart()->format( 'Ymd' ) < $day->format( 'Ymd' ) ){
				$date_base = DateTime::createFromFormat( 'Y-m-d H:i:s', $date . ' 00:00:00', new DateTimeZone( c::config()->timezone ) );
				$hours = Crunchbutton_Admin_Hour::segmentToDate( $date_base, $shift->startEndToString(), $timezone );
				if( $hours ){
					// Check if the recurring event was already created
					$checkShift = Crunchbutton_Community_Shift::q( 'SELECT * FROM community_shift WHERE id_community_shift_father = ' . $shift->id_community_shift . ' AND date_start = "' . $hours[ 'start' ] . '" AND date_end = "' . $hours[ 'end' ] . '"' );
					if( !$checkShift->id_community_shift ){
						$newShift = new Crunchbutton_Community_Shift();
						$newShift->id_community = $shift->id_community;
						$newShift->date_start = $hours[ 'start' ];
						$newShift->date_end = $hours[ 'end' ];
						$newShift->active = 1;
						$newShift->id_community_shift_father = $shift->id_community_shift;
						if( $newShift->date_start && $newShift->date_end ){
							$newShift->save();	
						}

						// Create the permanent assigments
						$permanentlys = Crunchbutton_Admin_Shift_Assign_Permanently::getByShift( $shift->id_community_shift );
						foreach( $permanentlys as $permanently ){
							if( $permanently->id_admin ){
								$assignment = new Crunchbutton_Admin_Shift_Assign();
								$assignment->id_admin = $permanently->id_admin;
								$assignment->id_community_shift = $newShift->id_community_shift;
								$assignment->date = date('Y-m-d H:i:s');
								$assignment->save();
							}	
						}
					} else {

						if( $now < $checkShift->dateStart()->get( 0 ) ){
							// Create the permanent assigments
							$permanentlys = Crunchbutton_Admin_Shift_Assign_Permanently::getByShift( $shift->id_community_shift );
							foreach( $permanentlys as $permanently ){
								if( $permanently->id_admin ){
									if( !Crunchbutton_Admin_Shift_Assign::adminHasShift( $permanently->id_admin, $checkShift->id_community_shift ) ){
										$assignment = new Crunchbutton_Admin_Shift_Assign();
										$assignment->id_admin = $permanently->id_admin;
										$assignment->id_community_shift = $checkShift->id_community_shift;
										$assignment->date = date('Y-m-d H:i:s');
										$assignment->save();	
									}
								}	
							}
						}
					}
				}
			}
		}
	}

	public function shiftByCommunity( $id_community ){
		$weekdays = [ 'mon' =>  false, 'tue' =>  false, 'wed' =>  false, 'thu' =>  false, 'fri' =>  false, 'sat' =>  false, 'sun'  =>  false ];
		foreach( $weekdays as $day => $val ){
			$shifts = Crunchbutton_Community_Shift::q( 'SELECT * FROM community_shift WHERE id_community = "' . $id_community . '" AND day = "' . $day . '" ORDER BY id_community_shift ASC' );	
			$segment = [];
			foreach ( $shifts as $shift ) {
				$segment[] = Crunchbutton_Community_Shift::startEndToSegment( $shift->start, $shift->end );
			}
			$weekdays[ $day ] = join( ', ', $segment );
		}
		return $weekdays;
	}

	public function parseSegment( $segment ){
		$pattern = '@^ *(\d+)(?:\:(\d+))? *(am|pm) *(?:to|-) *(\d+)(?:\:(\d+))? *(am|pm) *$@i';
		preg_match( $pattern, $segment , $matches);
		$start = Crunchbutton_Community_Shift::parseHour( $matches[ 1 ], $matches[ 2 ], $matches[ 3 ] );
		$end = Crunchbutton_Community_Shift::parseHour( $matches[ 4 ], $matches[ 5 ], $matches[ 6 ] );
		return array( 'start' => $start, 'end' => $end );
	}

	public function community(){
		if( !$this->_community ){
			$this->_community = Crunchbutton_Community::o( $this->id_community );
		}
		return $this->_community;
	}

	public function removeRecurring( $id_community_shift ){
		$shift = Crunchbutton_Community_Shift::o( $id_community_shift );
		if( $shift->id_community_shift_father ){
			c::db()->query( "UPDATE community_shift SET recurring = 0 WHERE id_community_shift = " . $shift->id_community_shift_father );	
			Crunchbutton_Community_Shift::removeRecurringChildren( $shift->id_community_shift_father );
		}		
	}

	public function removeRecurringChildren( $id_community_shift_father ){
		$now =  new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
		c::db()->query( 'DELETE FROM community_shift WHERE id_community_shift_father = ' . $id_community_shift_father . ' AND date_start >= "' . $now->format( 'Y-m-d' ) . '"' );
	}

	public function remove( $id_community_shift ){
		$shift = Crunchbutton_Community_Shift::o( $id_community_shift );
		if( $shift->recurring ){
			// If it is a recurring remove its childen
			Crunchbutton_Community_Shift::removeRecurringChildren( $shift->id_community_shift );
		}
		// If it has a father, just desactive the event - to avoid it the be re created again
		if( $shift->id_community_shift_father ){
			c::db()->query( "UPDATE community_shift SET active = 0 WHERE id_community_shift = " . $id_community_shift );
		} else {
			c::db()->query( "DELETE FROM community_shift WHERE id_community_shift = " . $id_community_shift );
		}		
	}

	public function removeHoursFromDay( $id_community, $date ){
		return c::db()->query( "DELETE FROM community_shift WHERE id_community = $id_community AND DATE_FORMAT( date_start, '%Y-%m-%d' ) = '$date'" );
	}

	public function copyHoursFromTo( $id_community, $dayFrom, $dayTo ){
		$shifts = Crunchbutton_Community_Shift::shiftByCommunityDay( $id_community, $dayFrom );
		foreach( $shifts as $shift ){
			$date_start = DateTime::createFromFormat( 'Y-m-d H:i:s', $dayTo . ' ' . $shift->dateStart()->format( 'H:i:s' ), new DateTimeZone( $shift->timezone() ) );	
			$date_end = DateTime::createFromFormat( 'Y-m-d H:i:s', $dayTo . ' ' . $shift->dateEnd()->format( 'H:i:s' ), new DateTimeZone( $shift->timezone() ) );	
			$newShift = new Crunchbutton_Community_Shift();
			$newShift->id_community = $id_community;
			$newShift->date_start = $date_start->format( 'Y-m-d H:i:s' );
			$newShift->date_end = $date_end->format( 'Y-m-d H:i:s' );
			$newShift->active = 1;
			if( $newShift->date_start && $newShift->date_end ){
				$newShift->save();	
			}
		}
	}

	public function timezone(){
		if( !$this->_timezone ){
			$this->_timezone = $this->community()->timezone;
		}
		return $this->_timezone;
	}

	public function timezoneAbbr(){
		$dateTime = new DateTime(); 
		$dateTime->setTimeZone( new DateTimeZone( $this->timezone() ) ); 
		return $dateTime->format( 'T' ); 
	}

	public function dateStart( $timezone = false ){

		if( $timezone ){
			$date = $this->dateStart();
			$date->setTimezone( new DateTimeZone( $timezone ) );
			return $date;
		}

		if( !$this->_date_start ){
			$this->_date_start = DateTime::createFromFormat( 'Y-m-d H:i:s', $this->date_start, new DateTimeZone( $this->timezone() ) );	
		}
		return $this->_date_start;
	}

	public function dateEnd( $timezone = false ){
		
		if( $timezone ){
			$date = $this->dateEnd();
			$date->setTimezone( new DateTimeZone( $timezone ) );
			return $date;
		}

		if( !$this->_date_end ){
			$this->_date_end = DateTime::createFromFormat( 'Y-m-d H:i:s', $this->date_end, new DateTimeZone( $this->timezone() ) );	
		}
		return $this->_date_end;
	}

	public function startEndToString( $timezone = false ){
		$time = Crunchbutton_Community_Shift::timeToSegmentString( $this->dateStart( $timezone )->format( 'H:i' ) );
		$time .= ' - ';
		$time .= Crunchbutton_Community_Shift::timeToSegmentString( $this->dateEnd( $timezone )->format( 'H:i' ) );
		return trim( $time );
	}

	public function timeToSegmentString( $time ){
		$time = explode( ':' , $time );
		$hour = $time[0];
		$min  = $time[1];
		$separator = ':';
		$ampm = 'am';
		if( $hour > 12 ){
			$hour -= 12;
			$ampm = 'pm';
		}
		if( $hour == 0 ){
			$hour = 12;
		}
		if( intval( $min ) == 0 ){
			$min = '';
			$separator = '';
		}
		return intval( $hour ) . $separator . $min . $ampm;
	}

	public function dateStartFriendly( $timezone = false ){
		return $this->dateStart( $timezone )->format( 'M jS Y g:i A T' );
	}

	public function dateEndFriendly( $timezone = false ){
		return $this->dateEnd( $timezone )->format( 'M jS Y g:i A T' );
	}

	public function fullDate( $timezone = false ){
		return 'From ' . $this->dateStartFriendly( $timezone ) . ' to ' . $this->dateEndFriendly( $timezone ); 
	}

	public function parseHour( $hour, $min, $ampm ){
		if( strtolower( $ampm ) == 'pm' ){
			if( intval( $hour ) < 12 ){
				$hour += 12;
			}
		}
		if( $hour > 24 ){
			$hour -= 24;
		}
		if( trim( $min ) == '' ){
			$min = '00';
		}
		if( trim( $hour ) != '' ){
			return $hour . ':' . $min;	
		}
		return false;
	}

	public function getAdminPreferences(){
		return Crunchbutton_Admin_Shift_Preference::q( 'SELECT * FROM admin_shift_preference WHERE id_community_shift = ' . $this->id_community_shift . ' ORDER BY ranking' );
	}

	public function getDrivers(){
		return Crunchbutton_Admin::q( 'SELECT a.* FROM admin a INNER JOIN admin_shift_assign asa ON asa.id_admin = a.id_admin AND asa.id_community_shift = ' . $this->id_community_shift );
	}

	public function communitiesWithDeliveryService(){
		return Crunchbutton_Community::q( 'SELECT DISTINCT( c.id_community ) AS id, c.* FROM community c
																				INNER JOIN restaurant_community rc ON c.id_community = rc.id_community
																				INNER JOIN restaurant r ON r.id_restaurant = rc.id_restaurant AND r.delivery_service = 1 
																			ORDER BY c.name ASC' );
	}

	public function sendWarningToDrivers(){

		$year = date( 'Y', strtotime( '- 1 day' ) );
		$week = date( 'W', strtotime( '- 1 day' ) );

		$day = new DateTime( date( 'Y-m-d', strtotime( $year . 'W' . $week . 1 ) ), new DateTimeZone( c::config()->timezone  ) );
		$day->modify( '+ 1 week' );

		$from = new DateTime( $day->format( 'Y-m-d' ), new DateTimeZone( c::config()->timezone  ) );
		$day->modify( '+6 day' );
		$to = new DateTime( $day->format( 'Y-m-d' ), new DateTimeZone( c::config()->timezone  ) );

		$log = 'Starting the driver schedule verification period from ' . $from->format( 'Y-m-d' ) . ' to ' . $to->format( 'Y-m-d' ) . ' at ' . date( 'Y-m-d H:i:s l' );
		Log::debug( [ 'action' => $log, 'type' => 'driver-schedule' ] );
		echo $log."\n";

		switch ( date( 'l' ) ) {
			case 'Monday':
				$driversMessage = 'Hey [name]! Please fill out your schedule for next week at cockpit._DOMAIN_/schedule. If you have any questions, just text us back.';
				$warningDrivers = true;
				$warningCS = false;
				break;
			case 'Wednesday':
				$driversMessage = 'Remember: update your Crunchbutton schedule for next week at cockpit._DOMAIN_/schedule. Don\'t leave us hanging :(';
					$warningDrivers = true;
					$warningCS = false;
				break;
			case 'Thursday':
					$warningCS = true;
					$driversMessage = false;
					break;
			default:
				$driversMessage = false;
				$warningCS = false;
				break;
		}

		$communitiesWithShift = [];
		$communitiesWithoutShift = [];

		$driversWillReceiveTheNotification = [];

		// Get the communities with active and delivery_service restaurants
		$communities = Crunchbutton_Community::q( 'SELECT DISTINCT( c.id_community ) AS id, c.* FROM community c INNER JOIN restaurant_community rc ON rc.id_community = c.id_community INNER JOIN restaurant r ON r.id_restaurant = rc.id_restaurant WHERE r.active = 1 AND r.delivery_service = 1 ORDER BY c.name' );
		foreach( $communities as $community ){
			// Check if the community has shift for current week
			$shifts = Crunchbutton_Community_Shift::shiftsByCommunityPeriod( $community->id_community, $from->format( 'Y-m-d' ), $to->format( 'Y-m-d' ) );
		
			$drivers = $community->getDriversOfCommunity();
			
			echo "\n";
			$log = $community->name . ' has ' .  $drivers->count() . ' drivers';
			Log::debug( [ 'action' => $log, 'type' => 'driver-schedule' ] );
			echo $log."\n";
			echo "\n";

			foreach( $drivers as $driver ){

				$preferences = Crunchbutton_Admin_Shift_Status::getByAdminWeekYear( $driver->id_admin, $week, $year );

				$receiveSMS = $driver->getConfig( Crunchbutton_Admin::CONFIG_RECEIVE_DRIVER_SCHEDULE_SMS_WARNING );
				if( $receiveSMS->id_admin_config ){
					$receiveSMS = $receiveSMS->value;
				} else {
					$receiveSMS = false;
				}
				if( $receiveSMS ){
					$log = 'driver set up to RECEIVE sms: ' . $driver->name . ' - ' . $community->name;
					Log::debug( [ 'action' => $log, 'type' => 'driver-schedule' ] );
					echo $log."\n";
					if( $preferences->completed == 0 ){
						$driversWillReceiveTheNotification[] = array(  'id_admin' => $driver->id_admin, 'name' => $driver->name, 'txt' => $driver->txt, 'phone' => $driver->phone );
					}
				} else {
					$log = 'driver set up to NOT receive sms: ' . $driver->name;
					Log::debug( [ 'action' => $log, 'type' => 'driver-schedule' ] );
					echo $log."\n";
				}
			}
			if( $shifts->count() == 0 ){
				$communitiesWithoutShift[] = $community->name;
			}
		}
		echo "\n";
		$log = 'communitiesWithoutShift: ' . count( $communitiesWithoutShift ) . ', list ' . join( ', ', $communitiesWithoutShift );
		Log::debug( [ 'action' => $log, 'type' => 'driver-schedule' ] );
		echo $log."\n";

		echo "\n";
		$log = 'driversWillReceiveTheNotification: ' . count( $driversWillReceiveTheNotification );
		Log::debug( [ 'action' => $log, 'type' => 'driver-schedule' ] );
		echo $log."\n";

		$env = c::getEnv();

		$twilio = new Twilio( c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token );

		// removed this sms for while

		if( false && count( $communitiesWithoutShift ) > 0 ){

			$message = "The following communities doesn't have shifts for the current week: " . join( ', ', $communitiesWithoutShift );
			
			$message = str_split( $message, 160 );

			foreach ( Crunchbutton_Support::getUsers() as $supportName => $supportPhone ) {
				$num = $supportPhone;
				foreach ( $message as $msg ) {
					if( $supportName != $admin->name ){
						try {
							// Log
							Log::debug( [ 'action' => 'community without shift warning', 'admin' => $supportName, 'num' => $num, 'msg' => $msg, 'type' => 'driver-schedule' ] );
							$twilio->account->sms_messages->create( c::config()->twilio->{$env}->outgoingTextCustomer, '+1'.$num, $msg );
						} catch (Exception $e) {
							// Log
							Log::debug( [ 'action' => 'ERROR: community without shift warning', 'admin' => $supportName, 'num' => $num, 'msg' => $msg, 'type' => 'driver-schedule' ] );
						}
					}
				}
			}
		}

		echo "\n";
		Log::debug( [ 'action' => 'notification sms', 'total' => count( $driversWillReceiveTheNotification ), 'type' => 'driver-schedule' ] );

		if( $warningCS && count( $driversWillReceiveTheNotification ) > 0 ){

			$message = count( $driversWillReceiveTheNotification ) . ' drivers didn\'t completed their schedule, the list of drivers is available here at cockpit._DOMAIN_/drivers/shift/status/shift';

			$message = str_split( $message, 160 );

			foreach ( Crunchbutton_Support::getUsers() as $supportName => $supportPhone ) {
				$num = $supportPhone;
				foreach ( $message as $msg ) {
					if( $supportName != $admin->name ){
						try {
							// Log
							Log::debug( [ 'action' => 'sending the drivers list', 'admin' => $supportName, 'num' => $num, 'msg' => $msg, 'type' => 'driver-schedule' ] );
							$log = 'Sending sms to: ' . $supportName . ' - ' . $supportPhone . ': ' . $msg;
							Log::debug( [ 'action' => $log, 'type' => 'driver-schedule' ] );
							echo $log."\n";
							$twilio->account->sms_messages->create( c::config()->twilio->{$env}->outgoingTextCustomer, '+1'.$num, $msg );
						} catch (Exception $e) {
							// Log
							$log = 'Error Sending sms to: ' . $supportName . ' - ' . $supportPhone . ': ' . $msg;
							Log::debug( [ 'action' => $log, 'type' => 'driver-schedule' ] );
							echo $log."\n";
							Log::debug( [ 'action' => 'ERROR: sending the drivers list', 'admin' => $supportName, 'num' => $num, 'msg' => $msg, 'type' => 'driver-schedule' ] );
						}
					}
				}
			}
		}

		if( $warningDrivers ){

			foreach( $driversWillReceiveTheNotification as $driver ){

				$id_admin = $driver[ 'id_admin' ];
				$name = $driver[ 'name' ];
				$txt = $driver[ 'txt' ];
				$phone = $driver[ 'phone' ];
				
				$message = str_replace( '[name]' , $name, $driversMessage );

				$num = ( $txt != '' ) ? $txt : $phone; 

				$message = str_split( $message, 160 );
				
				if( $num != '' ){
					foreach ( $message as $msg ) {
						try {
							// Log
							Log::debug( [ 'action' => 'sending sms', 'id_admin' => $id_admin, 'name' => $name, 'num' => $num, 'msg' => $msg, 'type' => 'driver-schedule' ] );
							$twilio->account->sms_messages->create( c::config()->twilio->{ $env }->outgoingTextCustomer, '+1'.$num, $msg );
							$log = 'Sending sms to: ' . $name . ' - ' . $num . ': ' . $msg;
							Log::debug( [ 'action' => $log, 'type' => 'driver-schedule' ] );
							echo $log."\n";
						} catch (Exception $e) {
							// Log
							Log::debug( [ 'action' => 'ERROR: sending sms', 'id_admin' => $id_admin, 'name' => $name, 'num' => $num, 'msg' => $msg, 'type' => 'driver-schedule' ] );
							$log = 'Error Sending sms to: ' . $name . ' - ' . $num . ': ' . $msg;
							Log::debug( [ 'action' => $log, 'type' => 'driver-schedule' ] );
							echo $log."\n";
						}
					}
				} else {
					Log::debug( [ 'action' => 'ERROR: sending sms', 'id_admin' => $id_admin, 'name' => $name, 'num' => $num, 'msg' => $msg, 'type' => 'driver-schedule' ] );
				}
			}
		}
	}
}

