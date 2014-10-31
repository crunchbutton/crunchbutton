<?php

class Crunchbutton_Support_Message extends Cana_Table {

	const TYPE_SMS = 'sms';
	const TYPE_NOTE = 'note';
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

	public function notify() {
		self::notify_by_sms();
	}

 	public function byPhone( $phone ){
 		$phone = str_replace( '-', '', $phone );
 		return Crunchbutton_Support_Message::q( "SELECT sm.* FROM support_message sm
																							INNER JOIN support s ON s.id_support = sm.id_support
																								WHERE REPLACE( REPLACE( s.phone, ' ', '' ), '-', '' ) = '" . $phone . "'
																								ORDER BY sm.id_support_message ASC" );
	}
	
	public function exports() {
		$out = $this->properties();
		$out['name'] = $this->getName();
		$out['timestamp'] = strtotime($this->date);
		return $out;
	}
	
	public function getName() {
		if (!isset($this->_name)) {
			
			$phone = preg_replace('/[^0-9]/','', $this->phone);

			if ($this->from == 'system') {
				$this->_name = 'SYSTEM';

			} elseif (!$this->name) {

				$phoneFormat = preg_replace('/([0-9]{3})([0-9]{3})([0-9]{4})/','\\1-\\2-\\3', $phone);

				if ($phone) {
					$user = Crunchbutton_Admin::q('select * from admin where phone="'.$phone.'"');

					if (!$user->id_admin) {
						$user = Crunchbutton_Admin::q('select * from admin where phone="'.$phoneFormat.'"');
					}
					
					if (!$user->id_admin) {
						$user = Crunchbutton_User::q('select * from `user` where phone="'.$phone.'"');
					}
					
					if ($user->id_admin || $user->id_user) {
						$this->_name = $user->phone;
					}
				}
				
			} else {
				$this->_name = $this->name;
			}
		}
		return $this->_name;
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
			'message' => $msg
		]);

	}

	public function admin(){
		return Crunchbutton_Admin::o( $this->id_admin );
	}

	public function support() {
		$message = Support::o($this->id_support);
		return $message;
	}

	public function relativeTime( $forceUTC = false ) {
		$date = new DateTime( $this->date, new DateTimeZone( c::config()->timezone ) );
		if( $forceUTC ){
			$date->setTimezone( new DateTimeZone( 'utc' ) );
		}
		return Crunchbutton_Util::relativeTime( $date->format( 'Y-m-d H:i:s' ), 'utc', 'utc' );
	}

	public function date() {
		if (!isset($this->_date)) {
			$this->_date = new DateTime($this->date, new DateTimeZone(c::config()->timezone));
		}
		return $this->_date;
	}

	public function repTime() {
		$date = $this->date();
		$date->setTimezone(c::admin()->timezone());
		return $date;
	}

}
