<?php

class Cockpit_Community_Status_Log extends Cana_Table {

	const TYPE_ALL_RESTAURANTS = 'close_all_restaurants';
	const TYPE_3RD_PARY_DELIRERY_RESTAURANTS = 'close_3rd_party_delivery_restaurants';
	const TYPE_AUTO_CLOSED = 'is_auto_closed';

	const NOTE_ALL_RESTAURANTS = 'close_all_restaurants_note';
	const NOTE_3RD_PARY_DELIRERY_RESTAURANTS = 'close_3rd_party_delivery_restaurants_note';
	const NOTE_AUTO_CLOSED = 'driver_restaurant_name';

	public function properties(){

		$out = [];

		$closed_at = $this->closed_at();
		if( $closed_at ){
			$out[ 'closed_at_utc' ] = $closed_at->format( 'Y-m-d H:i:s' );
			$closed_at->setTimezone( new DateTimeZone( $this->community()->timezone ) );
			$out[ 'closed_at' ] = $closed_at->format( 'M/d/Y h:i:s T' );
		}

		$opened_at = $this->opened_at();
		if( $opened_at ){
			$out[ 'opened_at_utc' ] = $opened_at->format( 'Y-m-d H:i:s' );
			$opened_at->setTimezone( new DateTimeZone( $this->community()->timezone ) );
			$out[ 'opened_at' ] = $opened_at->format( 'M/d/Y h:i:s T' );
		}

		if( $closed_at && $opened_at ){
			$interval = $opened_at->diff( $closed_at );
			$out[ 'how_long' ] = Crunchbutton_Util::format_interval( $interval );
		} else {
			$out[ 'how_long' ] = '?';
		}

		switch ( $this->type ) {
			case self::TYPE_ALL_RESTAURANTS:
				$out[ 'type' ] = Crunchbutton_Community::TITLE_CLOSE_ALL_RESTAURANTS;
				break;
			case self::TYPE_3RD_PARY_DELIRERY_RESTAURANTS:
				$out[ 'type' ] = Crunchbutton_Community::TITLE_CLOSE_3RD_PARY_RESTAURANTS;
				break;
			case self::TYPE_AUTO_CLOSED:
				$out[ 'type' ] = Crunchbutton_Community::TITLE_CLOSE_AUTO_CLOSED;
				break;
		}

		$out[ 'note' ] = nl2br( $this->notes );

		$out[ 'closed_by' ] = $this->closed_by()->name;
		$out[ 'opened_by' ] = $this->opened_by()->name;
		if( !$out[ 'closed_by' ] ){
			$out[ 'closed_by' ] = '-';
		}
		if( !$out[ 'opened_by' ] ){
			$out[ 'opened_by' ] = '-';
		}
		$out[ 'reason' ] = $this->reason();
		return $out;
	}

	public function reason(){
		if( !$this->_reason && $this->id_community_closed_reason ){
			$reason = Cockpit_Community_Closed_Reason::o( $this->id_community_closed_reason );
			if( $reason->id_community_closed_reason ){
				$out = 'Reason: ' . $reason->reason;
				if( $reason->id_driver ){
					$out .= ' :' . $reason->driver()->name;
				}
			}
			$this->_reason = $out;
		}
		return $this->_reason;
	}

	public function community(){
		if( !$this->_community ){
			$this->_community = Community::o( $this->id_community );
		}
		return $this->_community;
	}

	public function closed_at(){
		if( !$this->_closed_at && $this->closed_date ){
			$this->_closed_at = new DateTime( $this->closed_date, new DateTimeZone( c::config()->timezone ) );
		}
		return $this->_closed_at;
	}

	public function opened_at(){
		if( !$this->_opened_at && $this->opened_date ){
			$this->_opened_at = new DateTime( $this->opened_date, new DateTimeZone( c::config()->timezone ) );
		}
		return $this->_opened_at;
	}

	public function closed_by(){
		if( !$this->_closed_by && $this->closed_by ){
			$this->_closed_by = Admin::o( $this->closed_by );
		}
		return $this->_closed_by;
	}

	public function opened_by(){
		if( !$this->_opened_by && $this->opened_by ){
			$this->_opened_by = Admin::o( $this->opened_by );
		}
		return $this->_opened_by;
	}

	public static function register( $params ){

		$log = null;
		$close = $params[ 'close' ];

		// action close
		if( $close ){

			$log = new Cockpit_Community_Status_Log;
			if( $params[ 'type' ] == self::TYPE_AUTO_CLOSED ){
				$log->closed_by = Admin::login( Crunchbutton_Community::AUTO_SHUTDOWN_COMMUNITY_LOGIN )->id_admin;
			} else {
				$log->closed_by = c::admin()->id_admin;
			}

			switch ( $params[ 'type' ] ) {
				case self::TYPE_ALL_RESTAURANTS:
					$log->notes = $params[ 'properties' ][ self::NOTE_ALL_RESTAURANTS ];
					break;
				case self::TYPE_3RD_PARY_DELIRERY_RESTAURANTS:
					$log->notes = $params[ 'properties' ][ self::NOTE_3RD_PARY_DELIRERY_RESTAURANTS ];
					break;
				case self::TYPE_AUTO_CLOSED:
					$log->notes = $params[ 'properties' ][ self::NOTE_AUTO_CLOSED ];
					break;
			}

			if( $params[ 'properties' ][ 'id_community_closed_reason' ] ){
				$log->id_community_closed_reason = $params[ 'properties' ][ 'id_community_closed_reason' ];
			}

			if( $log->notes ){
				$log->notes = 'Close: ' . $log->notes;
			}
			if( $params[ 'properties' ][ 'reopen_at' ] ){
				$reopen_at = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
				if( $params[ 'properties' ][ 'timezone' ] ){
					$reopen_at->setTimezone( new DateTimeZone( $params[ 'properties' ][ 'timezone' ] ) );
				}
				$log->notes .= "\nThe force close will be removed at: " . $reopen_at->format( 'M jS Y g:i:s A T' );;
			}

			$log->closed_date = date( 'Y-m-d H:i:s' );
		}
		// action open
		else {
			$log = self::q( 'SELECT * FROM community_status_log WHERE id_community = ? AND type = ? AND opened_by IS NULL ORDER BY id_community_status_log DESC LIMIT 1', [ $params[ 'properties' ][ 'id_community' ], $params[ 'type' ] ] );
			if( !$log->id_community_status_log ){
				$log = new Cockpit_Community_Status_Log;
				$log->notes = 'Unknown when it was closed.';
			}
			if( $params[ 'type' ] == self::TYPE_AUTO_CLOSED ){
				$log->opened_by = Admin::login( Crunchbutton_Community::AUTO_SHUTDOWN_COMMUNITY_LOGIN )->id_admin;
			} else {
				$log->opened_by = c::admin()->id_admin;
			}

			if( !$log->opened_by ){
				$log->opened_by = Admin::login( Crunchbutton_Community::AUTO_SHUTDOWN_COMMUNITY_LOGIN )->id_admin;
			}

			$log->opened_date = date( 'Y-m-d H:i:s' );
		}
		$log->type = $params[ 'type' ];
		$log->id_community = $params[ 'properties' ][ 'id_community' ];
		$log->save();
	}

	public static function registerNote( $params ){
		$log = self::q( 'SELECT * FROM community_status_log WHERE id_community = ? AND type = ? AND opened_by IS NULL ORDER BY id_community_status_log DESC LIMIT 1', [ $params[ 'properties' ][ 'id_community' ], $params[ 'type' ] ] );

		if( $log->id_community_status_log ){
			$log->notes .= "\n Closed message changed to: '";
			switch ( $params[ 'type' ] ) {
				case self::TYPE_ALL_RESTAURANTS:
					$log->notes .= $params[ 'properties' ][ self::NOTE_ALL_RESTAURANTS ];
					break;
				case self::TYPE_3RD_PARY_DELIRERY_RESTAURANTS:
					$log->notes .= $params[ 'properties' ][ self::NOTE_3RD_PARY_DELIRERY_RESTAURANTS ];
					break;
				case self::TYPE_AUTO_CLOSED:
					$log->notes .= $params[ 'properties' ][ self::NOTE_AUTO_CLOSED ];
					break;
			}
			$log->notes .= "' by " . c::admin()->name;
			$log->save();
		}
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('community_status_log')
			->idVar('id_community_status_log')
			->load($id);
	}


}