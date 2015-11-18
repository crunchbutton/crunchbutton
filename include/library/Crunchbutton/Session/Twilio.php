<?php

class Crunchbutton_Session_Twilio extends Cana_Table {

	public static function capture( $data = false ) {

		$sess = self::q('select * from session_twilio where id_session="'.c::auth()->session()->adapter()->id_session.'"');

		// If the session id was lost, let's try to recover it by selecting the last support of the given phone number
		// the support souldnt be more than 10 days old
		if (!$sess->id_session_twilio && $data && $data[ 'From' ] && $data[ 'From' ] != '' ) {
			$phone = str_replace( '+1','', $data['From'] );
			$phone = str_replace( '-','', $phone );
			$sess = self::q( "SELECT st.* FROM session_twilio st
												INNER JOIN support s ON st.id_session_twilio = s.id_session_twilio
												WHERE st.phone = '{$phone}' AND DATEDIFF( NOW(), s.datetime ) <= 10
												ORDER BY st.id_session_twilio DESC LIMIT 1" );
			$sess->id_session = c::auth()->session()->adapter()->id_session;
			$sess->save();
		}

		// If we couldnt recovery the session, let's create a new one
		if ( !$sess->id_session_twilio ) {
			$sess = new Session_Twilio;
			$sess->id_session = c::auth()->session()->adapter()->id_session;
			$sess->save();
		}

		return $sess;
	}

	public function sessionByPhone( $phone ){
		$phone = str_replace( '+1','', $phone );
		$sess = Crunchbutton_Session_Twilio::q( "SELECT st.* FROM session_twilio st
												INNER JOIN support s ON st.id_session_twilio = s.id_session_twilio
												WHERE st.phone = '{$phone}' AND DATEDIFF( NOW(), s.datetime ) <= 10
												ORDER BY st.id_session_twilio DESC LIMIT 1" );
		if ( $sess->id_session_twilio ) {
			return $sess;
		}
		return false;
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('session_twilio')
			->idVar('id_session_twilio')
			->load($id);
	}
}
