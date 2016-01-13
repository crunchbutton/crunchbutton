<?php

class Crunchbutton_Admin_Shift_Assign extends Cana_Table {

	public function __construct($id = null) {
		parent::__construct();
		$this->table('admin_shift_assign')->idVar('id_admin_shift_assign')->load($id);
	}

	public function save($newItem = 0){
		if( !$this->id_admin_shift_assign ){
			Crunchbutton_Admin_Shift_Assign_Log::addAssignment( [ 'id_driver' => $this->id_admin, 'id_community_shift' => $this->id_community_shift ] );
		}
		return parent::save($newItem);
	}

	public function admin(){
		if( !$this->_admin ){
			$this->_admin = Admin::o( $this->id_admin );
		}
		return $this->_admin;
	}

	public function isConfirmed(){
		return intval( $this->confirmed ) > 0;
	}

	// If an unchecked in driver is doing orders, they should automatically be checked in #6841
	public static function autoCheckingWhenDriverIsDoingOrders(){
		$shift = Crunchbutton_Community_Shift::shiftDriverIsCurrentWorkingOn( c::user()->id_admin, null, null, false );
		if( $shift->id_admin_shift_assign && !$shift->confirmed ){
			$assignment = self::o( $shift->id_admin_shift_assign );
			Crunchbutton_Admin_Shift_Assign_Confirmation::confirm( $assignment, true );
			// $message = 'Automatically checkin for shift ' . $shift->fullDate() . ' when driver accpeted an order.';
			// $admin = Admin::o( c::user()->id_admin );
			// $admin->addNote( $message );
		}
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
		return c::dbWrite()->query( "DELETE FROM admin_shift_assign WHERE id_community_shift = " . $id_community_shift );
	}

	public function delete( $reason = [] ) {
		Crunchbutton_Admin_Shift_Assign_Log::removeAssignment( $this->id_admin_shift_assign, $reason );
		parent::delete();
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

			// if the shift has started automaticaly checkin the driver
			$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
			$now->modify( '+ 15 minutes' );
			$shift = $assignment->shift();
			$startAt = $shift->dateStart( c::config()->timezone );
			if( $now > $startAt ){
				Crunchbutton_Admin_Shift_Assign_Confirmation::confirm( $assignment, true );
			}
			return $assignment;
		} else {
			Crunchbutton_Admin_Shift_Assign_Permanently::removeByAdminShift( $id_admin, $id_community_shift );
			if( $permanently ){
				Crunchbutton_Admin_Shift_Assign_Permanently::addDriver( $id_admin, $id_community_shift );
			}
		}
		return true;
	}

	public function community(){
		return $this->shift()->community();
	}

	public function adminHasShift( $id_admin, $id_community_shift ){
		$shift = Crunchbutton_Admin_Shift_Assign::q( "SELECT * FROM admin_shift_assign WHERE id_admin = " . $id_admin . " AND id_community_shift = " . $id_community_shift . " LIMIT 1" );
		if( $shift->id_admin_shift_assign ){
			return true;
		}
		return false;
	}

	public function timesDriverWasAskedToConfirm(){
		return Crunchbutton_Admin_Shift_Assign_Confirmation::timesDriverWasAskedToConfirm( $this->id_admin_shift_assign );
	}

	public function date(){
		if (!isset($this->_date)) {
			$this->_date = new DateTime( $this->date, new DateTimeZone( c::config()->timezone ) );
		}
		return $this->_date;
	}

}
