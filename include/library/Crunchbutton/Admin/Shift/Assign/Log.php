<?php

class Crunchbutton_Admin_Shift_Assign_Log extends Cana_Table {

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('admin_shift_assign_log')
			->idVar('id_admin_shift_assign_log')
			->load($id);
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

	public static function removeAssignment( $id_admin_shift_assign ){
		$assign = Crunchbutton_Admin_Shift_Assign::o( $id_admin_shift_assign );
		$params[ 'id_community_shift' ] = $assign->id_community_shift;
		$params[ 'id_driver' ] = $assign->id_admin;
		$params[ 'assigned' ] = false;
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