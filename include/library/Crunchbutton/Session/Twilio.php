<?php

class Crunchbutton_Session_Twilio extends Cana_Table {
	public static function get() {
		$sess = self::q('select * from session_twilio where id_session="'.c::auth()->session()->id_session.'"');

		if (!$sess->id_session_twilio) {
			$sess = new Session_Twilio;
			$sess->id_session = c::auth()->session()->id_session;
			$sess->save();
		}

		return $sess;
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('session_twilio')
			->idVar('id_session_twilio')
			->load($id);
	}
}