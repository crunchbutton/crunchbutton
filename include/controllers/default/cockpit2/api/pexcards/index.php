<?php

class Controller_api_pexcards extends Crunchbutton_Controller_RestAccount {

	public function init() {

		if (!c::admin()->permission()->check( ['global', 'drivers-all'])) {
			$this->error(401, true);
		}

		$out = [];
		$drivers = Crunchbutton_Admin::q( 'SELECT a.name, a.id_admin FROM admin a INNER JOIN admin_payment_type apt ON apt.id_admin = a.id_admin WHERE a.active = true ORDER BY a.name' );

		foreach( $drivers as $drive ){
			$out[] = [ 'id_admin' => intval( $drive->id_admin ), 'name' => $drive->name ];
		}
		echo json_encode( $out );
	
	}
}