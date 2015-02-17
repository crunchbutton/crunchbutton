<?php

class Crunchbutton_Admin_Shift_Assign_Permanently_Removed extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('admin_shift_assign_permanently_removed')
			->idVar('id_admin_shift_assign_permanently_removed')
			->load($id);
	}

	public function wasRemoved( $id_community_shift, $id_admin ){
		$removed = Crunchbutton_Admin_Shift_Assign_Permanently_Removed::q( "SELECT * FROM admin_shift_assign_permanently_removed WHERE id_community_shift = '" . $id_community_shift . "' AND id_admin = '" . $id_admin . "'" );
		if( $removed->id_admin_shift_assign_permanently_removed ){
			return true;
		}
		return false;
	}

	public function add( $id_community_shift, $id_admin ){
		if( !Crunchbutton_Admin_Shift_Assign_Permanently_Removed::wasRemoved( $id_community_shift, $id_admin ) ){
			$removed = new Crunchbutton_Admin_Shift_Assign_Permanently_Removed;
			$removed->id_community_shift = $id_community_shift;
			$removed->id_admin = $id_admin;
			$removed->save();
		}
		return true;
	}
}