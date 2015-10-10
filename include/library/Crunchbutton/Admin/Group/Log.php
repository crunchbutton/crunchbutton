<?php

class Crunchbutton_Admin_Group_Log extends Cana_Table {

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('admin_group_log')
			->idVar('id_admin_group_log')
			->load($id);
	}

	public function admin(){
		if( !$this->_admin ){
			$this->_admin = Admin::o( $this->id_admin );
		}
		return $this->_admin;
	}

	public function admin_assigned(){
		if( !$this->_admin_assigned ){
			$this->_admin_assigned = Admin::o( $this->id_admin_assigned );
		}
		return $this->_admin_assigned;
	}

	public static function create( $params = [] ){
		$log = new Crunchbutton_Admin_Group_Log;
		$log->id_group = $params[ 'id_group' ];
		$log->id_admin = c::user()->id_admin;
		$log->id_admin_assigned = $params[ 'id_admin_assigned' ];
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