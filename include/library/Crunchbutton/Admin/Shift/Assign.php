<?php

class Crunchbutton_Admin_Shift_Assign extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('admin_shift_assign')
			->idVar('id_admin_shift_assign')
			->load($id);
	}

	public function admin(){
		if( !$this->_admin ){
			$this->_admin = Admin::o( $this->id_admin );
		}
		return $this->_admin;
	}

	public function isFirstWeek( $id_admin, $date ){
		$query = '
			SELECT
				YEARWEEK( cs.date_start ) first_week,
				YEARWEEK( ? ) current_week
			FROM community_shift cs
			INNER JOIN admin_shift_assign asa ON asa.id_community_shift = cs.id_community_shift
			AND asa.id_admin = ?
			ORDER BY cs.date_start ASC LIMIT 1
		';
		$result = c::db()->get($query, [$date, $id_admin])->get(0);
		if( $result->first_week && $result->current_week ){
			return ( $result->first_week == $result->current_week );
		}
		return false;
	}

	public function isPermanent(){
		return Crunchbutton_Admin_Shift_Assign_Permanently::adminIsPermanently( $this->id_admin, $this->id_community_shift );
	}

	public function shift(){
		if( !$this->_shift ){
			$this->_shift = Crunchbutton_Community_Shift::o( $this->id_community_shift );
		}
		return $this->_shift;
	}

	public function removeAssignment( $id_community_shift ){
		Crunchbutton_Admin_Shift_Assign_Permanently::removeByShift( $id_community_shift );
		return c::db()->query( "DELETE FROM admin_shift_assign WHERE id_community_shift = " . $id_community_shift );
	}

	public function shiftsByAdminPeriod( $id_admin, $date_start, $date_end ){
		return Crunchbutton_Community_Shift::q('
			SELECT cs.*, asa.id_admin_shift_assign FROM community_shift cs
			INNER JOIN admin_shift_assign asa ON asa.id_community_shift = cs.id_community_shift AND asa.id_admin = ?
			WHERE cs.date_start >= ? AND cs.date_end <= ?
		', [$id_admin, $date_start, $date_end]);
	}

	public function assignAdminToShift( $id_admin, $id_community_shift, $permanently ){
		if( !Crunchbutton_Admin_Shift_Assign::adminHasShift( $id_admin, $id_community_shift ) ){
			$assignment = new Crunchbutton_Admin_Shift_Assign();
			$assignment->id_admin = $id_admin;
			$assignment->id_community_shift = $id_community_shift;
			$assignment->date = date('Y-m-d H:i:s');
			$assignment->save();
			Crunchbutton_Admin_Shift_Assign_Permanently::removeByAdminShift( $id_admin, $id_community_shift );
			if( $permanently ){
				Crunchbutton_Admin_Shift_Assign_Permanently::addDriver( $id_admin, $id_community_shift );
			}
		} else {
			Crunchbutton_Admin_Shift_Assign_Permanently::removeByAdminShift( $id_admin, $id_community_shift );
			if( $permanently ){
				Crunchbutton_Admin_Shift_Assign_Permanently::addDriver( $id_admin, $id_community_shift );
			}
		}

		return true;
	}

	public function adminHasShift( $id_admin, $id_community_shift ){
		$shift = Crunchbutton_Admin_Shift_Assign::q( "SELECT * FROM admin_shift_assign WHERE id_admin = " . $id_admin . " AND id_community_shift = " . $id_community_shift . " LIMIT 1" );
		if( $shift->id_admin_shift_assign ){
			return true;
		}
		return false;
	}

}