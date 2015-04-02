<?php

class Controller_Api_Test_Sandbox extends Cana_Controller {
	public function init(){

		$out = [ 'ok' => [], 'no' => [] ];

		$admins = Admin::q( 'SELECT * FROM admin WHERE active = 0 OR active IS NULL ORDER BY name ASC' );
		foreach( $admins as $admin ){
			$change_set = Cockpit_Admin_Changeset::q( 'SELECT MAX( timestamp ) AS date, acs.* FROM admin_change ac
																						INNER JOIN admin_change_set acs ON acs.id_admin_change_set = ac.id_admin_change_set
																						WHERE field = "active" and old_value = 1 AND  id_admin = ' . $admin->id_admin . ' LIMIT 1' );
			if( $change_set->id_admin_change_set ){
				$date = new DateTime( $change_set->date, new DateTimeZone( c::config()->timezone ) );
				$admin->date_terminated = $date->format( 'Y-m-d' );
				$admin->save();
				$out[ 'ok' ][ $admin->id_admin ] = $admin->name;
			} else {
				$admin->date_terminated = '2015-01-01';
				$out[ 'no' ][ $admin->id_admin ] = $admin->name;
				$admin->save();
			}
		}

		echo json_encode( $out );exit;

	}
}