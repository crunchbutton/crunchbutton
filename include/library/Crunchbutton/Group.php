<?php

class Crunchbutton_Group extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('group')
			->idVar('id_group')
			->load($id);
	}

	public static function find($search = []) {

		$query = 'SELECT `group`.* FROM `group` WHERE id_group IS NOT NULL ';
		
		if ( $search[ 'name' ] ) {
			$query .= " AND name LIKE '%{$search[ 'name' ]}%' ";
		}

		$query .= " ORDER BY name DESC";

		$groups = self::q($query);
		return $groups;
	}

	public function users(){
		if( $this->id_group ){
			return Crunchbutton_Admin_Group::q( "SELECT a.* FROM admin a INNER JOIN admin_group ag ON ag.id_admin = a.id_admin AND ag.id_group = {$this->id_group}" );	
		} 
		return false;
	}

	public function usersTotal(){
		if( $this->id_group ){
			return Crunchbutton_Admin_Group::q( "SELECT a.* FROM admin a INNER JOIN admin_group ag ON ag.id_admin = a.id_admin AND ag.id_group = {$this->id_group}" )->count();	
		} 
		return 0;
	}

}