<?php

class Cockpit_Admin_Pexcard extends Cana_Table {

	public function __construct($id = null) {
		parent::__construct();
		$this->table( 'admin_pexcard' )->idVar( 'id_admin_pexcard' )->load( $id );
	}

	public function admin(){
		if( !$this->_admin && $this->id_admin ){
			$this->_admin = Admin::o( $this->id_admin );
		}
		return $this->_admin;
	}

	public function load_card_info(){
		if( $this->id_pexcard ){
			$card = Crunchbutton_Pexcard_Card::details( $this->id_pexcard );
			if( $card->body && $card->body->id ){
				return $card->body;
			}
		}
		return false;
	}

	public function getByAdmin( $id_admin ){
		return Cockpit_Admin_Pexcard::q( 'SELECT * FROM admin_pexcard WHERE id_admin = "' . $id_admin . '"' );
	}

	public function getByPexcard( $id_pexcard ){
		$admin_pexcard = Cockpit_Admin_Pexcard::q( 'SELECT * FROM admin_pexcard WHERE id_pexcard = "' . $id_pexcard . '" LIMIT 1' );
		if( $admin_pexcard->id_admin_pexcard ){
			return $admin_pexcard;
		}
		$admin_pexcard = new Cockpit_Admin_Pexcard;
		$admin_pexcard->id_pexcard = $id_pexcard;
		return $admin_pexcard;
	}

}