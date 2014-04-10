<?php

class Controller_api_drivers_shifts extends Crunchbutton_Controller_RestAccount {
	
	public function init() {
		$id_admin = c::admin()->id_admin;
		$shifts = Crunchbutton_Community_Shift::nextShiftsByAdmin( $id_admin );
		$data = [];
		foreach( $shifts as $shift ){
			$data[] = $shift->export();
		}
		echo json_encode( $data );
	}
}
