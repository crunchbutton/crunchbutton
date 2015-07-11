<?php

class Crunchbutton_Admin_Info extends Cana_Table {

	const KEY_SSN = 'ssn';
	const SSN_MASK = '***-**-****';

	public function getSSN( $id_admin ){
		return Crunchbutton_Admin_Info::getInfo( $id_admin, Crunchbutton_Admin_Info::KEY_SSN );
	}

	public function storeSSN( $id_admin, $value ){
		return Crunchbutton_Admin_Info::storeInfo( $id_admin, Crunchbutton_Admin_Info::KEY_SSN, $value );
	}

	public function getInfo( $id_admin, $key ){
		$info = Crunchbutton_Admin_Info::q( 'SELECT * FROM admin_info WHERE id_admin = ? AND `key` = ? ORDER BY id_admin_info DESC LIMIT 1', [$id_admin, $key]);
		if( $info->id_admin_info ){
			return c::crypt()->decrypt( $info->value );
		}
		return NULL;
	}

	public function storeInfo( $id_admin, $key, $value ){
		$info = Crunchbutton_Admin_Info::q( 'SELECT * FROM admin_info WHERE id_admin = ? AND `key` = ? ORDER BY id_admin_info DESC LIMIT 1', [$id_admin, $key]);
		if( !$info->id_admin_info ){
			$info = new Crunchbutton_Admin_Info;
		}
		$info->id_admin = $id_admin;
		$info->key = $key;
		$info->value = c::crypt()->encrypt( $value );
		$info->save();
		return $info->id_admin_info;
	}

	public function __construct($id = null) {
		parent::__construct();
		$this->table('admin_info')->idVar('id_admin_info')->load($id);
	}
}