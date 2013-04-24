<?php

class Crunchbutton_Suggestion extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('suggestion')
			->idVar('id_suggestion')
			->load($id);
	}
	
	public function queNotify() {
		$suggestion = $this;
		Log::debug( [ 'suggestion' => $suggestion->id_suggestion, 'action' => 'queNotify', 'type' => 'suggestion' ]);
		Cana::timeout(function() use($suggestion) {
			$suggestion->notify();
		});
	}
	
	public function notify() {
		$env = c::env() == 'live' ? 'live' : 'dev';
		$phones = c::config()->suggestion->{$env}->phone;
		$twilio = new Twilio(c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token);

		$message =
			($this->user()->name ? $this->user()->name : 'A guest').
			($this->user()->phone ? ' (' . $this->user()->phone . ')': '').
			" suggested:\n\n".
			$this->name."\n\n".
			"at ".
			$this->restaurant()->name.
			"\n\n (". $env . ")";

		// Log
		Log::debug( [ 'suggestion' => $this->id_suggestion, 'action' => 'starting send sms', 'type' => 'suggestion' ]);

		$message = str_split($message, 160);
		
		foreach ($message as $msg) {
			foreach ($phones as $phone) {
				$twilio->account->sms_messages->create(
					c::config()->twilio->{$env}->outgoingTextCustomer,
					'+1'.$phone,
					$msg
				);
				// Log
				Log::debug( [ 'suggestion' => $this->id_suggestion, 'message' => $message, 'phone' => $phone, 'type' => 'suggestion' ]);
				continue;	
			}
		}
	}
	
	public static function find($search = []) {
		$query = 'SELECT `suggestion`.* FROM `suggestion` LEFT JOIN restaurant USING(id_restaurant) WHERE id_suggestion IS NOT NULL ';
		
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

		if ($search['restaurant']) {
			$query .= ' and `suggestion`.id_restaurant="'.$search['restaurant'].'" ';
		}

		if ($search['community']) {
			$query .= ' and `suggestion`.id_community="'.$search['community'].'" ';
		}

		if ($search['search']) {
			$qn =  '';
			$q = '';
			$searches = explode(' ',$search['search']);
			foreach ($searches as $word) {
				if ($word{0} == '-') {
					$qn .= ' and `suggestion`.name not like "%'.substr($word,1).'%" ';
					$qn .= ' and `suggestion`.content not like "%'.substr($word,1).'%" ';
				} else {
					$q .= '
						and (`suggestion`.name like "%'.$word.'%"
						or `suggestion`.content like "%'.$word.'%")
					';
				}
			}
			$query .= $q.$qn;
		}

		$query .= 'ORDER BY `date` DESC';

		if ($search['limit']) {
			$query .= ' limit '.$search['limit'].' ';
		}

		$suggestions = self::q($query);
		return $suggestions;
	}

	public function restaurant() {
		return Restaurant::o($this->id_restaurant);
	}

	public function community() {
		return Community::o($this->id_community);
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