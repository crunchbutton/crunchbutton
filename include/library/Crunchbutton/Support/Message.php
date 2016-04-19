<?php

class Crunchbutton_Support_Message extends Cana_Table {

	const TYPE_SMS = 'sms';
	const TYPE_EMAIL = 'email';
	const TYPE_NOTE = 'note';
	const TYPE_WARNING = 'warning';
	const TYPE_AUTO_REPLY = 'auto-reply';
	const TYPE_FROM_CLIENT = 'client';
	const TYPE_FROM_REP = 'rep';
	const TYPE_FROM_SYSTEM = 'system';
	const TYPE_VISIBILITY_INTERNAL = 'internal';
	const TYPE_VISIBILITY_EXTERNAL = 'external';

	const TICKET_CREATED_COCKPIT_BODY = '(Ticket created at cockpit)';

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('support_message')
			->idVar('id_support_message')
			->load($id);
	}

	public function save($new = false) {
		$this->phone = Phone::clean($this->phone);
		$guid = $this->guid;
		$new = $this->id_support_message ? false : true;

		$this->media = $this->media ? json_encode($this->media) : null;

		$phone = Phone::byPhone( $this->phone );
		$this->id_phone = $phone->id_phone;

		parent::save();

		if( $this->ignore_broadcast === true ){
			$new = false;
		}

		if ($new) {
			Event::create([
				'room' => [
					'ticket.'.$this->id_support,
					'tickets'
				]
			], 'message', $this->exports($guid));
		}
	}

	public function load($stuff = null) {
		parent::load($stuff);
		$this->media = $this->media ? json_decode($this->media) : null;
		//print_r($this->media);
		return $this;
	}

	public function notify() {
		if( $this->support()->type == Crunchbutton_Support::TYPE_EMAIL ){
			self::notify_by_email();
		} else {
			self::notify_by_sms();
		}

	}

	public function totalMessagesByPhone( $phone ){
		$phone = Phone::clean( $phone );
		$query = 'SELECT COUNT(*) AS total FROM support_message sm INNER JOIN support s ON s.id_support = sm.id_support INNER JOIN phone p ON p.id_phone = s.id_phone WHERE p.phone = ? ORDER BY sm.id_support_message ASC';
		$r = c::db()->get( $query, [ $phone ] )->get( 0 );
 		return intval( $r->total );
	}

	public static function totalMessagesByEmail( $email ){
		$query = 'SELECT COUNT(*) AS total FROM support_message INNER JOIN support ON support.id_support = support_message.id_support AND email = ?';
		$r = c::db()->get( $query, [ $email ] )->get( 0 );
 		return intval( $r->total );
	}


 	public function byPhone( $phone, $id_support = false, $page = false, $limit = false ){
 		$phone = Phone::clean( $phone );
 		$where = ( $id_support ) ? ' OR sm.id_support = "' . $id_support . '" ' : '';
 		if( $page && $limit ){
 			$total = Crunchbutton_Support_Message::totalMessagesByPhone( $phone );
 			$pages = ceil( $total / $limit );
 			if( $page > $pages ){
 				return false;
 			}
 			$start = $total - ( $limit * $page );
 			$end = ( ( $start + $limit ) <= $total ) ? ( $limit ) : 0;
 			if( $start < 0 ){
 				$end = $start + $limit;
 				$start = 0;
 			}
 			$_limit = ' LIMIT ' . $start . ',' . $end;
 		} else {
 			$_limit = '';
 		}
 		return Crunchbutton_Support_Message::q( 'SELECT sm.* FROM support_message sm
 																							INNER JOIN support s ON s.id_support = sm.id_support
 																							INNER JOIN phone p ON p.id_phone = s.id_phone
 																							WHERE p.phone = ? ORDER BY sm.id_support_message ASC ' . $_limit, [ $phone ] );
	}

 	public static function byEmail( $email, $id_support = false, $page = false, $limit = false ){
 		$where = ( $id_support ) ? ' OR sm.id_support = "' . $id_support . '" ' : '';
 		if( $page && $limit ){
 			$total = Crunchbutton_Support_Message::totalMessagesByEmail( $email );
 			$pages = ceil( $total / $limit );
 			if( $page > $pages ){
 				return false;
 			}
 			$start = $total - ( $limit * $page );
 			$end = ( ( $start + $limit ) <= $total ) ? ( $limit ) : 0;
 			if( $start < 0 ){
 				$end = $start + $limit;
 				$start = 0;
 			}
 			$_limit = ' LIMIT ' . $start . ',' . $end;
 		} else {
 			$_limit = '';
 		}
 		return Crunchbutton_Support_Message::q( 'SELECT sm.* FROM support_message sm
 																							INNER JOIN support s ON s.id_support = sm.id_support
 																							WHERE s.email = ? ORDER BY sm.id_support_message ASC ' . $_limit, [ $email ] );
	}

	public function exports($guid = null) {
		// @todo: #5734
		$out = $this->properties();

		if( $this->type != Crunchbutton_Support_Message::TYPE_EMAIL ){
			$info = Phone::name( $this, true );
		 	$out['name'] = $info[ 'name' ];
		}

		 if( !$out[ 'id_admin' ] && $info[ 'id_admin' ] ){
			 $out[ 'id_admin' ] = $info[ 'id_admin' ];
			 $this->id_admin = $info[ 'id_admin' ];
		 }

		 if( !$out[ 'id_user' ] && $info[ 'id_user' ] ){
			 $out[ 'id_user' ] = $info[ 'id_user' ];
		 }

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
		unset( $out[ 'media' ] );
		unset( $out[ 'id' ] );
		unset( $out[ 'id_phone' ] );
		$out['date'] = $this->date()->format( 'M jS Y g:i:s A T' );
		$out['hour'] = $this->date()->format( 'D m/d @ g:i A' );
		$out['name'] = $this->admin()->firstName();
		return $out;
	}

	public function exportsSide(){
		$out = $this->exports();
		$remove = [ 'id_admin', 'id', 'id_phone', 'id_phone_log' ];
		foreach( $out as $key => $val ){
			if( in_array( $key, $remove ) || !$val ){
				unset( $out[ $key ] );
			}
		}
		if( $out[ 'from' ] == 'rep' && $out[ 'type' ] == 'sms' && !$out[ 'status' ] ){
			$out[ 'status' ] = 'unknown';
		}
		if( $out[ 'from' ] == 'rep' && $out[ 'is_driver' ] ){
			$out[ 'is_support' ] = true;
		}

		$out[ 'md5' ] = md5($out['first_name'] . $out['from'] . $out['body'] . $out['timestamp']);
		$out[ 'body' ] = $this->cleanBody();
		return $out;
	}

	// numbers should not show up in messages when CS people text from phones #7855
	public function cleanBody(){
		return trim( preg_replace( '/^@([1-9]*)/', '', $this->body) );
	}

	public function status(){
		$log = $this->phone_log();
		if( $log->status ){
			return $log->status;
		}
		return null;
	}

	public function phone_log(){
		if( !$this->_phone_log && $this->id_phone_log ){
			$this->_phone_log = Crunchbutton_Phone_Log::o( $this->id_phone_log );
		}
		return $this->_phone_log;
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

		$ret = Crunchbutton_Message_Sms::send([
						'to' => $phone,
						'message' => $msg,
						'reason' => Crunchbutton_Message_Sms::REASON_SUPPORT,
						'log' => true // log
					] );

		if( $ret[0] && $ret[0]->id_phone_log ){
			$this->id_phone_log = $ret[0]->id_phone_log;
			$this->save();
		}
	}

	public function notify_by_email() {
		$support = $this->support();
		if (!$support->email) return;
		$subject = 'Re: ' . $support->lastSubject();
		$to = $support->email . ',' . Crunchbutton_Support::SUPPORT_EMAIL;
		$params = [ 'to' => $to, 'message' => $this->body, 'subject' => $subject, 'name' => c::user()->name ];
		$email = new Crunchbutton_Email_CSReply( $params );
		$email->send();
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
