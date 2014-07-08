<?php

class Controller_api_driver_paid extends Crunchbutton_Controller_RestAccount {

	public function init() {

			// list of drivers that were already paid
			$drivers = Crunchbutton_Admin::q( 'SELECT DISTINCT(a.id_admin) AS id_admin, a.name  FROM admin a
																								INNER JOIN payment p ON p.id_driver = a.id_admin
																								ORDER BY a.name ASC' );
			$export = [];
			$export[] = array( 'id_driver' => 0, 'name' => 'All' );
			foreach( $drivers as $driver ){
				$export[] = array( 'id_driver' => $driver->id_admin, 'name' => $driver->name );
			}
			echo json_encode( $export );

	}
}