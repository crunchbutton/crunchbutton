<?php

class Crunchbutton_Support_Answer extends Cana_Table {

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('support_answer')
			->idVar('id_support_answer')
			->load($id);
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
		Crunchbutton_Hipchat_Notification::NewSupportAnswer($this);
	}
}
