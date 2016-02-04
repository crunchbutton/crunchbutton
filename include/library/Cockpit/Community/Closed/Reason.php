<?php

class Cockpit_Community_Closed_Reason extends Cana_Table {

	const TYPE_ALL_RESTAURANTS = 'all_restaurants';
	const TYPE_3RD_PARTY_DELIVERY_RESTAURANTS = 'close_3rd_party_delivery_restaurants';
	const TYPE_AUTO_CLOSED = 'auto_closed';

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('community_closed_reason')
			->idVar('id_community_closed_reason')
			->load($id);
	}

	public function admin(){
		if( !$this->_admin ){
			$this->_admin = Admin::o( $this->id_admin );
		}
		return $this->_admin;
	}

	public function driver(){
		if( !$this->_driver ){
			$this->_driver = Admin::o( $this->id_driver );
		}
		return $this->_driver;
	}

	public function date(){
		if (!isset($this->_date)) {
			$this->_date = new DateTime( $this->datetime, new DateTimeZone( c::config()->timezone ) );
		}
		return $this->_date;
	}

	public static function reasonByCommunityDate( $id_community, $date ){
		$reason = Cockpit_Community_Closed_Reason::q( 'SELECT * FROM community_closed_reason WHERE id_community = ? AND date = ? ORDER by id_community_closed_reason DESC LIMIT 1', [ $id_community, $date ] )->get( 0 );
		if( $reason->id_community_closed_reason ){
			$out = $reason->reason;
			if( $reason->id_driver ){
				$out .= ' :' . $reason->driver()->name;
			}
			return $out;
		}
		return false;
	}

}