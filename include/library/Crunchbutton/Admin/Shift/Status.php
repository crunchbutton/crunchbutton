<?php

class Crunchbutton_Admin_Shift_Status extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('admin_shift_status')
			->idVar('id_admin_shift_status')
			->load($id);
	}

	public function currentStatus( $id_admin ){
		// Start week at monday #2666
		$year = date( 'Y', strtotime( '- 1 day' ) );
		$week = date( 'Y', strtotime( '- 1 day' ) );
		$date = new DateTime( date( 'Y-m-d', strtotime( $year . 'W' . $week . 1 ) ), new DateTimeZone( c::config()->timezone  ) );
		$date->modify( '+ 1 week' );
		return Crunchbutton_Admin_Shift_Status::getByAdminWeekYear( $id_admin, $date->format( 'W' ), $date->format( 'Y' ) );
	}

	public function totalShiftsAssigned(){

	}

	public static function getByAdminWeekYear( $id_admin, $week, $year ){
		$status = Crunchbutton_Admin_Shift_Status::q( 'SELECT * FROM admin_shift_status WHERE id_admin = ? AND year = ? AND week = ? ORDER BY id_admin_shift_status DESC LIMIT 1', [$id_admin, $year, $week])->get( 0 );
		if( !$status->id_admin_shift_status ){
			$status = new Crunchbutton_Admin_Shift_Status();
			$status->id_admin = $id_admin;
			$status->week = $week;
			$status->year = $year;
			$status->date = date('Y-m-d H:i:s');
			$status->save();
		}
		return $status;
	}

}