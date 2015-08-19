<?php

class Crunchbutton_Custom_Query_Version extends Cana_Table {

	const STATUS_DRAFT = 'draft';
	const STATUS_DELETE = 'deleted';
	const STATUS_WORKING = 'working';

	public function __construct($id = null) {
		parent::__construct();
		$this->table('custom_query_version')->idVar('id_custom_query_version')->load($id);
	}

	public static function workingByQuery( $id_custom_query ){
		return Crunchbutton_Custom_Query_Version::q( 'SELECT * FROM custom_query_version WHERE id_custom_query = ? AND status = ? ORDER BY id_custom_query_version DESC LIMIT 1', [ $id_custom_query, self::STATUS_WORKING ] )->get( 0 );
	}

	public function run(){
		if( $this->query ){
			if( !preg_match( "/INSERT|DELETE|UPDATE|DROP|ALTER|TABLE/i", $this->query )){
				return c::db()->get( $this->query );
			}
		}
		return false;
	}

	public function query(){
		return $this->query;
	}

	public function customQuery(){
		return $this->custom_query();
	}

	public function custom_query(){
		if( !$this->_custom_query ){
			$this->_custom_query = Crunchbutton_Custom_Query::o( $this->id_custom_query );
		}
		return $this->_custom_query;
	}

}
