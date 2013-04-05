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

		Log::debug([
				'action' => 'BEFORE cana::timeout',
				'support_id' => $support->id_support,
				'support_name' => $support->name,
				'support_phone' => $support->phone,
				'support_message' => $support->message,
				'method' => '$support->notify()',
				'type' => 'support'
			]);

		c::timeout(function() use($support) {
			$support->notify();
		}, 1000); // 1 second

		Log::debug([
				'action' => 'AFTER cana::timeout',
				'support_id' => $support->id_support,
				'support_name' => $support->name,
				'support_phone' => $support->phone,
				'support_message' => $support->message,
				'type' => 'support'
			]);
	}
	
	public function notify() {

		Log::debug([
			'action' => 'INSIDE notify cana::timeout',
			'support_id' => $this->id_support,
			'support_name' => $this->name,
			'support_phone' => $this->phone,
			'support_message' => $this->message,
			'method' => '$support->notify()',
			'type' => 'support'
		]);

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

			Log::debug([
				'message' => $message,
				'type' => 'support'
			]);

		$message = str_split($message, 160);

		$phone = c::config()->support->{$env}->phone;

		foreach ($message as $msg) {
			$twilio->account->sms_messages->create(
				c::config()->twilio->{$env}->outgoingTextCustomer,
				'+1'.$phone,
				$msg
			);
			continue;	
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