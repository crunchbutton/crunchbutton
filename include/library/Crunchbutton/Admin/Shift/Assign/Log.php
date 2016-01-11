<?php

class Crunchbutton_Admin_Shift_Assign_Log extends Cana_Table {

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('admin_shift_assign_log')
			->idVar('id_admin_shift_assign_log')
			->load($id);
	}

	public static function logByShift( $id_community_shift ){
		return self::q( 'SELECT
											asal.id_admin_shift_assign_log,
											asal.date, a1.name AS driver,
											a2.name AS admin,
											asal.assigned,
											asal.reason,
											asal.reason_other,
											asal.find_replacement
										FROM admin_shift_assign_log asal
											INNER JOIN admin a1 ON a1.id_admin = asal.id_driver
											INNER JOIN admin a2 ON a2.id_admin = asal.id_admin
										WHERE id_community_shift = ?
										ORDER BY id_admin_shift_assign_log DESC', [ $id_community_shift ] );
	}

	public static function logRemovedByShiftDriver( $id_community_shift, $id_driver ){
		return self::q( 'SELECT * FROM admin_shift_assign_log
											WHERE id_community_shift = ? AND id_driver = ? AND assigned = false
											ORDER BY id_admin_shift_assign_log DESC LIMIT 1', [ $id_community_shift, $id_driver ] )->get( 0 );
	}

	public function admin(){
		if( !$this->_admin ){
			$this->_admin = Admin::o( $this->id_admin );
		}
		return $this->_admin;
	}

	public function driver(){
		if( !$this->_driver ){
			$this->_driver = Admin::o( $this->id_driver );
		}
		return $this->_driver;
	}

	public static function removeAssignment( $id_admin_shift_assign, $reason = [] ){
		$assign = Crunchbutton_Admin_Shift_Assign::o( $id_admin_shift_assign );
		$params[ 'id_community_shift' ] = $assign->id_community_shift;
		$params[ 'id_driver' ] = $assign->id_admin;
		$params[ 'assigned' ] = false;
		if( $reason[ 'reason' ] ){
			$params[ 'reason' ] = $reason[ 'reason' ];
		}
		if( $reason[ 'reason_other' ] ){
			$params[ 'reason_other' ] = $reason[ 'reason_other' ];
		}
		if( $reason[ 'find_replacement' ] ){
			$params[ 'find_replacement' ] = $reason[ 'find_replacement' ];
		}
		self::create( $params );
	}

	public static function addAssignment( $params = [] ){
		$assign = Crunchbutton_Admin_Shift_Assign::o( $id_admin_shift_assign );
		$params[ 'id_community_shift' ] = $params[ 'id_community_shift' ];
		$params[ 'id_driver' ] = $params[ 'id_driver' ];
		$params[ 'assigned' ] = true;
		self::create( $params );
	}

	public static function create( $params = [] ){
		$log = new Crunchbutton_Admin_Shift_Assign_Log;
		$log->id_community_shift = $params[ 'id_community_shift' ];
		$log->id_admin = c::user()->id_admin;
		$log->id_driver = $params[ 'id_driver' ];
		$log->assigned = $params[ 'assigned' ];
		$log->reason = $params[ 'reason' ];
		$log->reason_other = $params[ 'reason_other' ];
		$log->find_replacement = $params[ 'find_replacement' ];
		$log->date = date( 'Y-m-d H:i:s' );
		$log->save();
		return $log;
	}

	public function date(){
		if (!isset($this->_date)) {
			$this->_date = new DateTime( $this->datetime, new DateTimeZone( c::config()->timezone ) );
		}
		return $this->_date;
	}
}