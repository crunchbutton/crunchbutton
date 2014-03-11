<?php

class Crunchbutton_Admin_Notification_Log extends Cana_Table {

	public function attempts( $id_order ){
		$query = "SELECT COUNT(*) AS Total FROM `admin_notification_log` a WHERE id_order = {$id_order}";
		$result = c::db()->get( $query );
		return intval( $result->_items[0]->Total ); 
	}

	public function byOrder( $id_order ){
		$query = "SELECT * FROM admin_notification_log a WHERE a.id_order = {$id_order} ORDER BY id_admin_notification_log ASC";
		return Crunchbutton_Admin_Notification_Log::q( $query );
	}

	// Clear the log to restart the notification process 
	public function cleanLog( $id_order ){
		$query = "DELETE FROM admin_notification_log WHERE id_order = {$id_order}";
		c::db()->query( $query );
	}

	public function restaurant(){
		return Crunchbutton_Restaurant::q( "SELECT r.* FROM restaurant r INNER JOIN `order` o ON o.id_restaurant = r.id_restaurant  WHERE id_order = {$this->id_order}" );
	}

	public function date() {
		if ( ! isset($this->_date ) ) {
			$this->_date = new DateTime( $this->date, new DateTimeZone( c::config()->timezone ) );
			$this->_date->setTimezone( new DateTimeZone( $this->restaurant()->timezone ) );
		}
		return $this->_date;
	}

	public function dateAtTz( $timezone ) {
		$date = new DateTime( $this->date, new DateTimeZone( c::config()->timezone ) );
		$date->setTimezone( new DateTimeZone( $timezone ) );
		return $date;
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