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

	public static function shiftsByPeriod( $id_admin, $from, $to, $dontWantToWorkItems = false ){
		if( $dontWantToWorkItems ){
			$where = 'AND asp.ranking = 0';
		} else {
			$where = 'AND asp.ranking > 0';
		}

		// hack to deal with date without using mysql date functions
		$to = new DateTime( $to, new DateTimeZone( c::config()->timezone ) );
		$to->modify( '+ 1 day' );
		$to = $to->format( 'Y-m-d' ) . ' 23:59:59';

		return Crunchbutton_Community_Shift::q('
			SELECT cs.*, asp.ranking FROM community_shift cs
			INNER JOIN admin_shift_preference asp ON asp.id_community_shift = cs.id_community_shift
			WHERE cs.date_start >= ? AND cs.date_start <= ?
			AND asp.id_admin = ?
			' . $where . '
			ORDER BY asp.ranking ASC, cs.date_start ASC
		', [$from, $to, $id_admin]);
	}

	public function highestRankingByPeriod( $id_admin, $from, $to ){
			$to .= ' 23:59:59';
			$shift = Crunchbutton_Community_Shift::q('
				SELECT cs.*, asp.ranking FROM community_shift cs
				INNER JOIN admin_shift_preference asp ON asp.id_community_shift = cs.id_community_shift
				WHERE cs.date_start >= ? AND cs.date_start <= ?
				AND asp.id_admin = ?
				ORDER BY asp.ranking DESC
				LIMIT 1
			', [$from, $to, $id_admin]);

			if ($shift->id_community_shift) {
				return $shift->ranking;
			}
			return 0;
	}

	public function adminHasShift( $id_admin, $id_community_shift ){
		$shift = Crunchbutton_Admin_Shift_Preference::q('
			SELECT * FROM admin_shift_preference
			WHERE id_admin = ?
			AND id_community_shift = ?
			LIMIT 1
		', [$id_admin, $id_community_shift]);

		if ($shift->id_admin_shift_preference) {
			return true;
		}
		return false;
	}

}