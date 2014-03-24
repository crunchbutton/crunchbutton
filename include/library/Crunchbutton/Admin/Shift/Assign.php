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

	public function removeAssignment( $id_community_shift ){
		Crunchbutton_Admin_Shift_Assign_Permanently::removeByShift( $id_community_shift );
		return c::db()->query( "DELETE FROM admin_shift_assign WHERE id_community_shift = " . $id_community_shift );
	}

	public function shiftsByAdminPeriod( $id_admin, $date_start, $date_end ){
		return Crunchbutton_Community_Shift::q( 'SELECT cs.* FROM community_shift cs
																							INNER JOIN admin_shift_assign asa ON asa.id_community_shift = cs.id_community_shift AND asa.id_admin = ' . $id_admin . 
																							' WHERE DATE_FORMAT( cs.date_start, "%Y-%m-%d" ) >= "' . $date_start . '" AND DATE_FORMAT( cs.date_end, "%Y-%m-%d" ) <= "' . $date_end . '"' );
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