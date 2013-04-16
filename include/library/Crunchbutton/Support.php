<?php

class Crunchbutton_Support extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('support')
			->idVar('id_support')
			->load($id);
	}
	
	public function queNotify() {
		$support = $this;
		c::timeout(function() use($support) {
			$support->notify();
		}, 100); 
	}

	public function getByTwilioSessionId( $id_session_twilio ){
		return Crunchbutton_Support::q( 'SELECT * FROM support WHERE id_session_twilio = ' . $id_session_twilio );
	}

	public function notify() {

		$env = c::env() == 'live' ? 'live' : 'dev';

		$twilio = new Twilio(c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token);

		$message =
			"(support-" . $env . "): ".
			$this->name.
			"\n\n".
			"phone: ".
			$this->phone.
			"\n\n".
			$this->message;

		// Log
		Log::debug( [ 'action' => 'support', 'message' => $message, 'type' => 'support' ] );

		$message = '@'.$this->id_session_twilio.' : ' . $message;
		$message = str_split( $message, 160 );

		// Send this message to the customer service
		foreach (c::config()->text as $supportName => $supportPhone) {
			$num = $supportPhone;
			foreach ($message as $msg) {
				try {
					// Log
					Log::debug( [ 'action' => 'sending sms - support', 'session id' => $this->id_session_twilio, 'num' => $num, 'msg' => $msg, 'type' => 'support' ] );
					$twilio->account->sms_messages->create(
						c::config()->twilio->{$env}->outgoingTextCustomer,
						'+1'.$num,
						$msg
					);
				} catch (Exception $e) {
					// Log
					Log::debug( [ 'action' => 'ERROR sending sms - support', 'session id' => $this->id_session_twilio, 'num' => $num, 'msg' => $msg, 'type' => 'sms' ] );
				}
			}
		}
	}
	
	public static function find($search = []) {
		$query = 'SELECT `support`.* FROM `support` WHERE id_support IS NOT NULL ';
		
		if ($search['type']) {
			$query .= ' and type="'.$search['type'].'" ';
		}
		
		if ($search['status']) {
			$query .= ' and status="'.$search['status'].'" ';
		}
		
		if ($search['start']) {
			$s = new DateTime($search['start']);
			$query .= ' and DATE(`date`)>="'.$s->format('Y-m-d').'" ';
		}
		
		if ($search['end']) {
			$s = new DateTime($search['end']);
			$query .= ' and DATE(`date`)<="'.$s->format('Y-m-d').'" ';
		}

		if ($search['search']) {
			$qn =  '';
			$q = '';
			$searches = explode(' ',$search['search']);
			foreach ($searches as $word) {
				if ($word{0} == '-') {
					$qn .= ' and `support`.name not like "%'.substr($word,1).'%" ';
					$qn .= ' and `support`.message not like "%'.substr($word,1).'%" ';
				} else {
					$q .= '
						and (`support`.name like "%'.$word.'%"
						or `support`.message like "%'.$word.'%")
					';
				}
			}
			$query .= $q.$qn;
		}

		$query .= 'ORDER BY `date` DESC';

		if ($search['limit']) {
			$query .= ' limit '.$search['limit'].' ';
		}

		$supports = self::q($query);
		return $supports;
	}

	public function user() {
		return User::o($this->id_user);
	}

	public function answers(){
		return Crunchbutton_Support_Answer::q('SELECT * FROM `support_answer` WHERE id_support=' . $this->id_support . ' ORDER BY date DESC');
	}

	public function date() {
		if (!isset($this->_date)) {
			$this->_date = new DateTime($this->date, new DateTimeZone(c::config()->timezone));
			$this->_date->setTimezone(new DateTimeZone( c::config()->timezone ));
		}
		return $this->_date;
	}

	public function save() {
		parent::save();
	}
}