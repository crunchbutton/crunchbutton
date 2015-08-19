<?php

class Crunchbutton_Custom_Query extends Cana_Table {

	const QUERY_SMART_POPULAR_LOCATION = 'smart-popular-location';

	public function __construct($id = null) {
		parent::__construct();
		$this->table('custom_query')->idVar('id_custom_query')->load($id);
	}

	public function run(){
		$current = $this->current();
		if( $current ){
			return $current->run();
		}
		return false;
	}

	public function current(){
		if( !$this->_current ){
			$this->_current = Crunchbutton_Custom_Query_Version::workingByQuery( $this->id_custom_query );
		}
		return $this->_current;
	}

	public function working(){
		return $this->current();
	}

	public static function bySlug( $slug ){
		$query = Crunchbutton_Custom_Query::q( 'SELECT * FROM custom_query WHERE slug = ? LIMIT 1', [ $slug ] )->get( 0 );
		if( $query->id_custom_query ){
			return $query;
		}
		return false;
	}

	// specify queries
	public static function mostPopularLocationQuery(){
		return self::bySlug( self::QUERY_SMART_POPULAR_LOCATION );
	}

}
