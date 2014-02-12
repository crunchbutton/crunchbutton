<?php

class Crunchbutton_Admin_Notification_Log extends Cana_Table {

	public function attempts( $id_order ){
		$query = "SELECT COUNT(*) AS Total FROM `admin_notification_log` a WHERE id_order = {$id_order}";
		$result = c::db()->get( $query );
		return intval( $result->_items[0]->Total ); 
	}

	public function register( $id_order ){
		$attempts = Crunchbutton_Admin_Notification_Log::attempts( $id_order );
		$log = new Crunchbutton_Admin_Notification_Log();

		$description = 'Notification #' . ( $attempts + 1 );

		if( $attempts == 0 ){
			$description .= ' regular notification';
		}

		if( $attempts == 1 ){
			$description .= ' First phone call';
		}

		if( $attempts == 2 ){
			$description .= ' Second phone call';
		}

		if( $attempts == 3 ){
			$description .= ' Alert to CS';
		}

		$log->id_order = $id_order;
		$log->description = $description;
		$log->date = date('Y-m-d H:i:s');
		$log->save();
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('admin_notification_log')
			->idVar('id_admin_notification_log')
			->load($id);
	}
}