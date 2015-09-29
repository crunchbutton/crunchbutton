<?php

class Cockpit_Community_Note extends Cana_Table {

	public static function lastNoteByCommunity( $id_community ){
		return self::q( 'SELECT * FROM community_note WHERE id_community = ? ORDER BY id_community_note DESC LIMIT 1', [$id_community])->get( 0 );
	}

	public function date() {
		if (!isset($this->_date)) {
			$this->_date = new DateTime( $this->date, new DateTimeZone( c::config()->timezone ) );
		}
		return $this->_date;
	}

	public function admin() {
		return Admin::o( $this->id_admin );
	}

	public function exports() {
		$out = [];
		$out[ 'id_admin' ] = $this->id_admin;
		$out[ 'id_community_note' ] = $this->id_community_note;
		$out[ 'date' ] = $this->date()->format( 'M jS Y g:i:s A' );
		$out[ 'date_utc' ] = Crunchbutton_Util::dateToUnixTimestamp( $this->date() );
		$out[ 'text' ] = $this->text;
		$out[ 'added_by' ] = $this->admin()->name;
		return $out;
	}

	public function __construct($id = null) {
		parent::__construct();
		$this->table('community_note')->idVar('id_community_note')->load($id);
	}
}