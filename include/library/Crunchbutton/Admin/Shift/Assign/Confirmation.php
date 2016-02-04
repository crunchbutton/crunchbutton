<?php

class Crunchbutton_Admin_Shift_Assign_Confirmation extends Cana_Table {

	const TYPE_TEXT = 'text';
	const TYPE_CALL = 'call';
	const TYPE_TICKET = 'ticket';

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('admin_shift_assign_confirmation')
			->idVar('id_admin_shift_assign_confirmation')
			->load($id);
	}

	public function create( $params = [] ){
		$confirmation = new Crunchbutton_Admin_Shift_Assign_Confirmation;
		$confirmation->id_admin_shift_assign = $params[ 'id_admin_shift_assign' ];
		$confirmation->type = $params[ 'type' ];
		$confirmation->datetime = date( 'Y-m-d H:i:s' );
		$confirmation->save();
		return $confirmation;
	}

	public function checkIfAdminHasShiftToConfirm( $id_admin ){
		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
		$today = $now->format( 'Y-m-d' );
		$confirmation = Crunchbutton_Admin_Shift_Assign_Confirmation::q( 'SELECT * FROM admin_shift_assign_confirmation asac INNER JOIN admin_shift_assign asa ON asa.id_admin_shift_assign = asac.id_admin_shift_assign WHERE asac.datetime > ? AND asa.id_admin = ? ORDER BY asac.id_admin_shift_assign_confirmation DESC LIMIT 1', [ $today, $id_admin ] )->get( 0 );
		if( $confirmation->id_admin_shift_assign_confirmation ){
			$assignment = Crunchbutton_Admin_Shift_Assign::o( $confirmation->id_admin_shift_assign );
			if( !$assignment->isConfirmed() ){
				return $confirmation;
			}
		}
		return false;
	}

	public function checkIfPhoneHasShiftToConfirm( $phone ){
		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
		$today = $now->format( 'Y-m-d' );
		$confirmation = Crunchbutton_Admin_Shift_Assign_Confirmation::q( 'SELECT * FROM admin_shift_assign_confirmation asac INNER JOIN admin_shift_assign asa ON asa.id_admin_shift_assign = asac.id_admin_shift_assign INNER JOIN admin a ON a.id_admin = asa.id_admin INNER JOIN phone p ON p.id_phone = a.id_phone AND p.phone = ? WHERE asac.datetime > ? ORDER BY asac.id_admin_shift_assign_confirmation DESC LIMIT 1', [ $phone, $today ] )->get( 0 );
		if( $confirmation->id_admin_shift_assign_confirmation ){
			$assignment = Crunchbutton_Admin_Shift_Assign::o( $confirmation->id_admin_shift_assign );
			if( !$assignment->isConfirmed() ){
				return $confirmation;
			}
		}
		return false;
	}

	public function confirmShiftBySMS( $id_admin_shift_assign ){
		$assignment = Crunchbutton_Admin_Shift_Assign::o( $id_admin_shift_assign );
		if( $assignment->id_admin_shift_assign ){
			self::confirm( $assignment );
			return 'Thanks, if you need anything, feel free to text us!';
		}
		return false;
	}

	public function askDriverToConfirm( $assignment ){

		if( !$assignment->id_admin_shift_assign ){
			$assignment = Crunchbutton_Admin_Shift_Assign::o( $assignment );
		}
		if( !$assignment->id_admin_shift_assign ){
			return;
		}

		// when a driver is added to a shift later than 15 minutes before it starts - they should be automatically checked in
		$shift = $assignment->shift();
		$startedAt = $shift->dateStart( c::config()->timezone );
		$startedAt->modify( '-15 minutes' );

		if( $assignment->date() >= $startedAt ){
			self::confirm( $assignment, true );
			return;
		}

		switch ( $assignment->timesDriverWasAskedToConfirm() ) {
			// first time send a text message
			case 0:
				self::askWithTextMessage( $assignment );
				break;

			// 2nd time make a call - after 5 min of the first message
			case 1:
				self::askByCalling( $assignment );
				break;

			// 3rd time - warn CS - after 10 min of the first message
			case 2:
				self::warnCS( $assignment );
				break;
		}
	}

	public function confirm( $assignment, $automatically = false, $id_admin = null ){
		$assignment->confirmed = 1;
		$assignment->save();
		$nextShift = self::checkIfThereIsASecondShift( $assignment );
		if( $nextShift && $nextShift->id_admin_shift_assign ){
			$assignment = Crunchbutton_Admin_Shift_Assign::o( $nextShift->id_admin_shift_assign );
			$assignment->confirmed = 1;
			$assignment->save();
		}
		if( !$automatically ){
			$admin = $assignment->admin();
			$period = $assignment->shift()->startEndToString();
			$community = $assignment->community()->name;
			$num = $admin->phone;
			if( !$id_admin ){
				$message = 'Shift confirmed by ' .  $community . ' driver ' . $admin->name . '!';
				Crunchbutton_Support::createNewWarningStaffTicket(  [ 'dont_open_ticket' => false, 'body' => $message, 'phone' => $num ] );
			}
		}
		return true;
	}

	public function message( $assignment, $type = self::TYPE_TEXT ){

		$shift = $assignment->shift();
		$admin = $assignment->admin();

		$nextShift = self::checkIfThereIsASecondShift( $assignment );
		if( $nextShift && $nextShift->id_community_shift ){
			$secs = Crunchbutton_Util::intervalToSeconds( $nextShift->dateEnd()->diff( $shift->dateStart() ) );
			if( $secs > 0 ){
				$_shift = new Crunchbutton_Community_Shift;
				$_shift->date_start = $shift->dateStart()->format( 'Y-m-d H:i:s' );
				$_shift->date_end = $nextShift->dateEnd()->format( 'Y-m-d H:i:s' );
				$_shift->id_community = $shift->id_community;
				$_shift->_timezone = $shift->timezone;
			}
		}

		$_shift = ( $_shift ) ? $_shift : $shift;

		switch ( $type ) {
			case self::TYPE_TEXT:
				$messagePattern = 'Hey, %s! Are you ready to deliver orders today from %s? Please respond with a "yes"!';
				return sprintf( $messagePattern, $admin->firstName(), $_shift->startEndToString() );
				break;

			case self::TYPE_CALL:
				$messagePattern = 'Hey . this is Crunchbutton . Are you around today to deliver orders from %s? . Just dial 1 now to let us know . Or respond "yes" to the automated text we sent you . ';
				return sprintf( $messagePattern, $_shift->startEndToString() );
				break;

			case self::TYPE_TICKET:
				$messagePattern = 'IMMEDIATE ACTION NEEDED: Independent Contractor %s ( community %s ) has not indicated he is ready to pick up deliveries today. ' .
													'Please call him immediately at %s and prepare to find a replacement NOW and the community will be AUTO-CLOSED at %s if he doesn\'t check in or if no replacement is found. Closing the community is the worst thing we can do for our customers. Please hustle and don\'t let the community auto-close!!';
				// return sprintf( $messagePattern, $admin->name, $assignment->community()->name, Crunchbutton_Phone::formatted( $admin->phone ), $admin->name );
				return sprintf( $messagePattern, $admin->name, $assignment->community()->name, Crunchbutton_Phone::formatted( $admin->phone ), $shift->dateStart()->format( 'g:ia' ) );
				break;
		}
	}

	public function warnCS( $assignment ){
		$lastTry = self::lastConfirmationTryByAssignment( $assignment->id_admin_shift_assign );
		if( $lastTry->id_admin_shift_assign_confirmation ){
			$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
			$interval = $now->diff( $lastTry->date() );

			if( Util::intervalToSeconds( $interval ) >= ( 5 * 60 ) ){
				$admin = $assignment->admin();
				$num = $admin->phone;
				$message = self::message( $assignment, self::TYPE_TICKET );
				Crunchbutton_Support::createNewWarningStaffTicket(  [ 'body' => $message, 'phone' => $num, 'bubble' => true ] );
				self::create( [ 'id_admin_shift_assign' => $assignment->id_admin_shift_assign, 'type' => self::TYPE_TICKET ] );
			}
		}
	}

	public function askByCalling( $assignment ){
		$lastTry = self::lastConfirmationTryByAssignment( $assignment->id_admin_shift_assign );
		if( $lastTry->id_admin_shift_assign_confirmation ){

			$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
			$interval = $now->diff( $lastTry->date() );

			if( Util::intervalToSeconds( $interval ) >= ( 5 * 60 ) ){

				$admin = $assignment->admin();

				$env = c::getEnv();
				$num = $admin->phone;
				$host_callback = self::host_callback();

				$url = 'http://'.$host_callback.'/api/shift/'.$assignment->id_admin_shift_assign.'/first-call';

				echo "Calling to " . $num."...\n";

				self::create( [ 'id_admin_shift_assign' => $assignment->id_admin_shift_assign, 'type' => self::TYPE_CALL ] );

				$twilio = c::twilio();

				$call = $twilio->account->calls->create( c::config()->twilio->{$env}->outgoingDriver, '+1'.$num, $url, [ 'IfMachine' => 'Hangup' ] );
			}
		}
	}

	public function host_callback(){
		if( c::getEnv() == 'live' ){
			return 'live.ci.crunchbutton.crunchr.co';
		} else {
			return $_SERVER['HTTP_HOST'];
		}

		return c::config()->host_callback;
	}

	public function askWithTextMessage( $assignment ){

		$admin = $assignment->admin();

		$txt = $admin->txt;
		$phone = $admin->phone;

		$num = ( $txt != '' ) ? $txt : $phone;

		$message = self::message( $assignment, self::TYPE_TEXT );

		echo "Sending sms to ".$num."...\n" . $message;

		$rets = Crunchbutton_Message_Sms::send( [ 'to' => $num, 'message' => $message, 'reason' => Crunchbutton_Message_Sms::REASON_DRIVER_SHIFT ]);

		self::create( [ 'id_admin_shift_assign' => $assignment->id_admin_shift_assign, 'type' => self::TYPE_TEXT ] );

		Crunchbutton_Support::createNewWarningStaffTicket(  [ 'dont_open_ticket' => true, 'body' => $message, 'phone' => $num ] );

		foreach ($rets as $ret) {
			if (!$ret->sid) {
				echo 'Error Sending sms to: '.$ret->to;
			}
		}
	}

	public function lastConfirmationTryByAssignment( $id_admin_shift_assign ){
		return Crunchbutton_Admin_Shift_Assign_Confirmation::q( 'SELECT * FROM admin_shift_assign_confirmation WHERE id_admin_shift_assign = ? ORDER BY id_admin_shift_assign_confirmation DESC LIMIT 1 ', [ $id_admin_shift_assign ] )->get( 0 );
	}

	public function timesDriverWasAskedToConfirm( $id_admin_shift_assign ){
		$result = c::db()->get( 'SELECT COUNT(*) AS total FROM admin_shift_assign_confirmation WHERE id_admin_shift_assign = ?', [ $id_admin_shift_assign ] )->get( 0 );
		return intval( $result->total );
	}

	public function checkIfThereIsASecondShift( $assignment ){
		$community = $assignment->community();
		$admin = $assignment->admin();
		$shift = $assignment->shift();

		$date_shift_ends = new DateTime( $shift->dateEnd()->format( 'Y-m-d H:i:s' ), new DateTimeZone( $community->timezone ) );
		$date_shift_ends->modify( '+5 minutes' );

		$nextShift = Crunchbutton_Community_Shift::q('
			SELECT cs.*, asa.id_admin_shift_assign FROM community_shift cs
			INNER JOIN admin_shift_assign asa ON asa.id_community_shift = cs.id_community_shift AND id_admin = ?
			WHERE
				cs.id_community = ?
				AND cs.date_start >= ?
				AND cs.date_start <= ?
			ORDER BY cs.date_start ASC
			LIMIT 1
		', [$admin->id_admin, $shift->id_community, $shift->dateEnd()->format( 'Y-m-d H:i:s' ), $date_shift_ends->format( 'Y-m-d H:i:s' )])->get( 0 );

		if( $nextShift->id_community_shift ){
			return $nextShift;
		}
		return false;
	}

	public function warningDriversBeforeTheirShift(){

		$env = c::getEnv();

		$twilio = c::twilio();

		$minutes = 15;

		$communities = Crunchbutton_Community::q( 'SELECT DISTINCT( c.id_community ) AS id, c.* FROM community c INNER JOIN restaurant_community rc ON rc.id_community = c.id_community INNER JOIN restaurant r ON r.id_restaurant = rc.id_restaurant WHERE r.active = true AND r.delivery_service = true AND c.id_community != ? AND c.driver_checkin = 1 ORDER BY c.name', [ Crunchbutton_Community::CUSTOMER_SERVICE_ID_COMMUNITY ] );

		foreach( $communities as $community ){

			if( $community->timezone ){

				$now = new DateTime( 'now', new DateTimeZone( $community->timezone ) );
				$now->modify( '- 5 minutes' );
				$_now = $now->format( 'Y-m-d H:i' );
				$now->modify( '+ ' . ( $minutes + 5 ) . ' minutes' );
				$_interval = $now->format( 'Y-m-d H:i' );

				// get the next shift of the community
				$nextShifts = Crunchbutton_Community_Shift::q('
					SELECT DISTINCT( cs.id_community_shift ) AS id, cs.* FROM admin_shift_assign asa
					INNER JOIN community_shift cs ON cs.id_community_shift = asa.id_community_shift
					WHERE
						cs.date_start >= ?
						AND cs.date_start <= ?
						AND cs.id_community = ?
				', [$_now, $_interval, $community->id_community]);

				if( $nextShifts->count() > 0 ){

					foreach( $nextShifts as $shift ){

						$assigments = Crunchbutton_Admin_Shift_Assign::q( 'SELECT asa.* FROM admin_shift_assign asa INNER JOIN admin a ON a.id_admin = asa.id_admin WHERE asa.id_community_shift = ? AND asa.warned = false AND a.active = true', [$shift->id_community_shift]);

						foreach( $assigments as $assignment ){

							if( !$assignment->isConfirmed() ){
								$shift = $assignment->shift();
								if( $shift->isHidden() ){ continue; }
								$minutesToStart = $shift->minutesToStart();
								if( $minutesToStart > 0 ){
									self::askDriverToConfirm( $assignment );
								}
							}
						}
					}
				}
			}
		}
	}

	public function date(){
		if (!isset($this->_date)) {
			$this->_date = new DateTime( $this->datetime, new DateTimeZone( c::config()->timezone ) );
		}
		return $this->_date;
	}
}
