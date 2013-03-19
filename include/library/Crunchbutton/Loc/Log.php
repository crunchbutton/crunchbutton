<?php

class Crunchbutton_Loc_Log extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('loc_log')
			->idVar('id_loc_log')
			->load($id);
	}

	public static function all(){
		return Crunchbutton_Loc_Log::q( 'SELECT * FROM loc_log ORDER BY id_loc_log DESC' ); 
	}
	
	public static function countCities() {
		$query = 'SELECT DISTINCT city AS place, lat, `long`, SUM(1) AS total FROM loc_log GROUP BY city, lat, `long` ORDER BY total DESC LIMIT 0,10';
		return static::countByQuery( $query );
	}

	public static function countRegions(){
		$query = 'SELECT DISTINCT region AS place, lat, `long`, SUM(1) AS total FROM loc_log GROUP BY city, lat, `long` ORDER BY total DESC LIMIT 0,10';
		return static::countByQuery( $query );
	}

	public static function countByQuery( $query ){
		$places = Cana::db()->get( $query );
		if( $places->_items ){
			$_places = array();
			foreach ( $places->_items as $place) {
				$_places[] = array( 'place' => $place->place, 'total' => $place->total, 'lat' => $place->lat, 'long' => $place->long );
			}
			return $_places;
		}	
		return false;
	}

	public static function countAll(){
		$places = Cana::db()->get( 'SELECT COUNT(*) as total FROM loc_log' );
		if( $places->_items ){
			return $places->_items[0]->total;
		}	
		return 0;
	}

	public function user() {
		return User::o($this->id_user);
	}

	public function date() {
		if (!isset($this->_date)) {
			$this->_date = new DateTime($this->date, new DateTimeZone(c::config()->timezone));
			$this->_date->setTimezone(new DateTimeZone( c::config()->timezone ));
		}
		return $this->_date;
	}

	public function save() {
		parent::save();
	}
}