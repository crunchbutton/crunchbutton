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

		$message =
			($this->user()->name ? $this->user()->name : 'A guest').
			($this->user()->phone ? ' (' . $this->user()->phone . ')': '').
			" suggested:\n\n".
			$this->name."\n\n".
			"at ".
			$this->restaurant()->name.
			"\n\n (". $env . ")";

		Crunchbutton_Message_Sms::send([
			'to' => Crunchbutton_Support::getUsers(),
			'message' => $message,
			'reason' => Crunchbutton_Message_Sms::REASON_SUPPORT_SUGGESTION
		]);
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

	public function save($new = false) {
		parent::save();
	}
}