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

	public function notify_by_sms() {
		$env = c::getEnv();
		$twilio = new Twilio( c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token );
		$support = $this->support();
		$phone = $support->phone;
		if (!$phone) return;
		$rep_name = $this->admin()->name;
		$msg = '' . ( $rep_name ? $rep_name.': ' : '' ) . $this->body;
		$msgs = str_split( $msg, 160 );
		foreach($msgs as $msg) {
			$twilio->account->sms_messages->create( c::config()->twilio->{$env}->outgoingTextCustomer, '+1'.$phone, $msg );
		}
	}

	public function admin(){
		return Crunchbutton_Admin::o( $this->id_admin );
	}

	public function support() {
		$message = Support::o($this->id_support);
		return $message;
	}
	
	public function relativeTime(  ) {
		return Crunchbutton_Util::relativeTime( $this->date, 'utc', 'utc' );
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
