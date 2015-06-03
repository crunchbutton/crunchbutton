<?php

class Crunchbutton_Support_Message extends Cana_Table {

	const TYPE_SMS = 'sms';
	const TYPE_NOTE = 'note';
	const TYPE_AUTO_REPLY = 'auto-reply';
	const TYPE_FROM_CLIENT = 'client';
	const TYPE_FROM_REP = 'rep';
	const TYPE_FROM_SYSTEM = 'system';
	const TYPE_VISIBILITY_INTERNAL = 'internal';
	const TYPE_VISIBILITY_EXTERNAL = 'external';

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('support_message')
			->idVar('id_support_message')
			->load($id);
	}

	public function save() {
		$this->phone = Phone::clean($this->phone);
		$guid = $this->guid;
		$new = $this->id_support_message ? false : true;

		$this->media = $this->media ? json_encode($this->media) : null;

		parent::save();

		if ($new) {
			Event::emit([
				'room' => [
					'ticket.'.$this->id_support,
					'tickets'
				]
			], 'message', $this->exports($guid));
		}
	}

	public function load($stuff) {
		parent::load($stuff);
		$this->media = $this->media ? json_decode($this->media) : null;
		//print_r($this->media);
		return $this;
	}

	public function notify() {
		self::notify_by_sms();
	}

 	public function byPhone( $phone, $id_support = false ){

 		$phone = str_replace( '-', '', $phone );

 		$where = ( $id_support ) ? ' OR sm.id_support = "' . $id_support . '" ' : '';

 		return Crunchbutton_Support_Message::q( "SELECT sm.* FROM support_message sm
																							INNER JOIN support s ON s.id_support = sm.id_support
																								WHERE REPLACE( REPLACE( s.phone, ' ', '' ), '-', '' ) = '" . $phone . "' " . $where . "
																								ORDER BY sm.id_support_message ASC" );
	}

	public function exports($guid = null) {
		// @todo: #5734
		$out = $this->properties();
		$out['name'] = Phone::name($this);
		$out['first_name'] = explode(' ',$out['name'])[0];
		$out['timestamp'] = strtotime($this->date);
		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );

		if( Crunchbutton_Util::intervalMoreThan24Hours( $now->diff( $this->date() ) ) ){
			$out['relative'] = $this->date()->format( 'M jS g:i a' );
		} else {
			$out['relative'] = $this->relativeTime( true );
		}




		$out['hour'] = $this->date()->format( 'H:i' );
		$out['guid'] = $guid;
		$out['is_note'] = ( $this->type == 'note' && $this->from != 'system' );
		if( $out['is_note'] ){
			$out['name'] = $this->admin()->firstName();
		}
		$out['is_support'] = $this->id_admin && $this->admin()->isSupport() ? true : false;
		$out['is_driver'] = $this->id_admin && $this->admin()->isDriver() ? true : false;

		return $out;
	}

	public function exportsNote(){
		$out = $this->properties();
		unset( $out[ 'id_support_message' ] );
		unset( $out[ 'id_support' ] );
		unset( $out[ 'id_admin' ] );
		unset( $out[ 'from' ] );
		unset( $out[ 'type' ] );
		unset( $out[ 'visibility' ] );
		unset( $out[ 'phone' ] );
		$out['date'] = $this->date()->format( 'M jS Y g:i:s A T' );
		$out['hour'] = $this->date()->format( 'H:i' );
		$out['name'] = $this->admin()->firstName();
		return $out;
	}

	public function notify_by_sms() {

		$support = $this->support();
		$phone = $support->phone;

		if (!$phone) return;

		if( $this->admin()->id_admin ){
			$rep_name = $this->admin()->firstName();
		} else {
			$rep_name = '';
		}

		$msg = '' . ( $rep_name ? $rep_name.': ' : '' ) . $this->body;

		Crunchbutton_Message_Sms::send([
			'to' => $phone,
			'message' => $msg,
			'reason' => Crunchbutton_Message_Sms::REASON_SUPPORT
		]);

	}

	public function admin() {
		return Crunchbutton_Admin::o($this->id_admin);
	}

	public function support() {
		return Support::o($this->id_support);
	}

	public function relativeTime( $forceUTC = false ) {
		$date = new DateTime( $this->date, new DateTimeZone( c::config()->timezone ) );
		return Crunchbutton_Util::relativeTime( $date->format( 'Y-m-d H:i:s' ));
	}

	public function date( $timezone = false ) {
		if ( !isset($this->_date) || $timezone ) {
			$this->_date = new DateTime( $this->date, new DateTimeZone( c::config()->timezone ) );
			if( $timezone ){
				$this->_date->setTimezone( new DateTimeZone( $timezone ) );
			}
		}
		return $this->_date;
	}

	public function repTime() {
		$date = $this->date();
		$date->setTimezone(c::admin()->timezone());
		return $date;
	}

}
