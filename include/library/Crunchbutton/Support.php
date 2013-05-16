<?php

class Crunchbutton_Support extends Cana_Table {

	const TYPE_SMS = 'SMS';
	const TYPE_BOX_NEED_HELP = 'BOX_NEED_HELP';

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('support')
			->idVar('id_support')
			->load($id);
    date_default_timezone_set('UTC'); // always save in utc
    $this->datetime = date('Y-m-d H:i:s e');
	}
	
	public function queNotify() {
		$support = $this;
		c::timeout(function() use($support) {
			$support->notify();
		}); 
	}

	public function getByTwilioSessionId( $id_session_twilio ){
		return Crunchbutton_Support::q( 'SELECT * FROM support WHERE id_session_twilio = ' . $id_session_twilio . ' ORDER BY id_support DESC LIMIT 1' );
	}

	public function sameTwilioSession(){
		return $this->getAllByTwilioSessionId( $this->id_session_twilio, $this->id_support );
	}

	public function getAllByTwilioSessionId( $id_session_twilio, $id_support = false ){
		$where = '';
		if( $id_support ){
			$where = ' AND id_support != ' . $id_support;
		}
		return Crunchbutton_Support::q( 'SELECT * FROM support WHERE id_session_twilio = ' . $id_session_twilio . $where . ' ORDER BY id_support DESC' );
	}

	public function notify() {

		$support = $this;

		$env = c::env() == 'live' ? 'live' : 'dev';

		$twilio = new Twilio(c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token);

		$message =
			"(support-" . $env . "): ".
			$support->name.
			"\n\n".
			"phone: ".
			$support->phone.
			"\n\n".
			$support->message;

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
					Log::debug( [ 'action' => 'sending sms - support', 'session id' => $this->id_session_twilio, 'num' => $num, 'msg' => $msg, 'type' => 'sms' ] );
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

		$this->makeACall();

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

	public function notes($internalexternal='') {
		if($internalexternal == '') {
			return Crunchbutton_Support_Note::q(
				"SELECT * ".
				"FROM support_note ".
				"WHERE id_support=$this->id_support ".
				"ORDER BY datetime ASC");
		}
		else {
			return Crunchbutton_Support_Note::q(
				"SELECT * ".
				"FROM support_note ".
				"WHERE id_support=$this->id_support ".
				"AND visibility='$internalexternal' ".
				"ORDER BY datetime ASC");
		}
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

	public function rep() {
		return Support_Rep::o($this->id_support_rep);
	}

	public function order() {
		return Order::o($this->id_order);
	}

	public function save() {
		parent::save();
		Crunchbutton_Hipchat_Notification::NewSupport($this);
	}

	public function makeACall(){

		$dateTime = new DateTime( 'now', new DateTimeZone('America/New_York'));
		$hour = $dateTime->format( 'H' );

		// Issue #1100 - Call David if CB receives a support after 1AM
		if( $hour >= 1 && $hour <= 7 ){
		
			$env = c::env() == 'live' ? 'live' : 'dev';
			
			$twilio = new Services_Twilio(c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token);

			$id_support = $this->id_support;

			$url = 'http://' . c::config()->host_callback . '/api/support/say/' . $id_support;

			Log::debug( [ 'action' => 'Need to call', 'id_support' => $id_support, 'url' => $url, 'hour' => $hour, 'type' => 'sms' ] );

			foreach ( c::config()->supportcall as $supportName => $supportPhone ) {
					$num = $supportPhone;
					$name = $supportName;
					$urlWithName = $url . '/' . $name;
					$call = $twilio->account->calls->create(
							c::config()->twilio->{$env}->outgoingRestaurant,
							'+1'.$num,
							$urlWithName
					);
					Log::debug( [ 'action' => 'Calling', 'num' => $num, 'url' => $urlWithName, 'type' => 'sms' ] );
			}
		}

		Log::debug( [ 'action' => 'Not need to call', 'id_support' => $id_support, 'hour' => $hour, 'type' => 'sms' ] );

	}

	public function addNote($text, $from, $visibility) {
		$sn = new Support_Note();
		$sn->id_support = $this->id;
		$sn->text = $text;
		$sn->from = $from;
		$sn->visibility = $visibility;
		$sn->save();
		return $sn;
	}

	public function systemNote($text) {
		self::addNote($text, 'system', 'internal');
	}

	public static function getSupportForOrder($id_order) {
		$s = self::q("SELECT * FROM `support` WHERE `id_order`='$id_order' ORDER BY `id_support` DESC LIMIT 1");
		return $s->id ? $s : false;
	}



}
