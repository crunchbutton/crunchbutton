<?php

class Controller_Api_Script_Sandbox extends Crunchbutton_Controller_RestAccount {

	public function init() {
		// 52813 live
		// 59639 local
		$id_admin_shift_assign = $_GET[ 'id' ];
		$assignment = Crunchbutton_Admin_Shift_Assign::o( $id_admin_shift_assign );
		if( $assignment->id_admin_shift_assign ){
			Crunchbutton_Admin_Shift_Assign_Confirmation::askDriverToConfirm( $assignment );
		}
	}
}