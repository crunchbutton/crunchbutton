<?php

class Crunchbutton_Admin_Shift_Preference extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('admin_shift_preference')
			->idVar('id_admin_shift_preference')
			->load($id);
	}

	public function admin(){
		if( !$this->_admin ){
			$this->_admin = Admin::o( $this->id_admin );
		}
		return $this->_admin;
	}

	public function getShiftByAdminPeriod( $id_admin, $date_start, $date_end ){

	}

	public function removeByAdminShift( $id_admin, $id_community_shift ){
		return c::db()->query( "DELETE FROM admin_shift_preference WHERE id_admin = " . $id_admin . " AND id_community_shift = " . $id_community_shift );
	}

	public function shiftsByPeriod( $id_admin, $from, $to, $dontWantToWorkItems = false ){
		if( $dontWantToWorkItems ){
			$where = 'AND asp.ranking = 0';
		} else {
			$where = 'AND asp.ranking > 0';
		}
		return Crunchbutton_Community_Shift::q( 'SELECT cs.* FROM community_shift cs 
																							INNER JOIN admin_shift_preference asp ON asp.id_community_shift = cs.id_community_shift
																							WHERE DATE_FORMAT( cs.date_start, "%Y-%m-%d" ) >= "' . $from . '" AND DATE_FORMAT( cs.date_start, "%Y-%m-%d" ) <= "' . $to . '" 
																							AND asp.id_admin = ' . $id_admin . ' ' . $where . '
																							ORDER BY asp.ranking ASC' );
	}

	public function adminHasShift( $id_admin, $id_community_shift ){
		$shift = Crunchbutton_Admin_Shift_Preference::q( "SELECT * FROM admin_shift_preference WHERE id_admin = " . $id_admin . " AND id_community_shift = " . $id_community_shift . " LIMIT 1" );
		if( $shift->id_admin_shift_preference ){
			return true;
		}
		return false;
	}

}