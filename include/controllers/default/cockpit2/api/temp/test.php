<?php

class Controller_api_temp_test extends Crunchbutton_Controller_RestAccount {
	public function init() {
		// $assignment = Crunchbutton_Admin_Shift_Assign::o( 62233 );
		// Crunchbutton_Admin_Shift_Assign_Confirmation::confirm( $assignment );
		// Crunchbutton_Admin_Shift_Assign_Confirmation::warnCS( $assignment );
		// $community = Community::o( 70 );
		// $community->shutDownCommunity();
		Community::shutDownCommunities();
	}
}