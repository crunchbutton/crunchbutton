<?php

class Controller_Api_PexCard extends Crunchbutton_Controller_RestAccount {

	public function init() {

		switch ( c::getPagePiece( 2 ) ) {

			case 'pex-id':
				$this->_pex_id();
				break;

			case 'admin-pexcard':
				$this->_admin_pexcard();
				break;

			case 'admin-pexcard-remove':
				$this->_admin_pexcard_remove();
				break;

			default:
				$this->_error();
				break;
		}

	}

	private function _admin_pexcard(){
		$id_admin = $this->request()[ 'id_admin' ];
		$id_pexcard = $this->request()[ 'id_pexcard' ];
		$admin_pexcard = Cockpit_Admin_Pexcard::getByPexcard( $id_pexcard );
		$admin_pexcard->id_admin = $id_admin;
		$admin_pexcard->save();

		$admin_pexcard = Cockpit_Admin_Pexcard::o( $admin_pexcard->id_admin_pexcard );
		$admin = $admin_pexcard->admin();

		echo json_encode( [ 'success' => [ 'name' => $admin->name, 'login' => $admin->login ] ] );
	}

	private function _admin_pexcard_remove(){
		$id_pexcard = $this->request()[ 'id_pexcard' ];
		$admin_pexcard = Cockpit_Admin_Pexcard::getByPexcard( $id_pexcard );
		if( $admin_pexcard->id_admin_pexcard ){
			$admin_pexcard->id_admin = null;
			$admin_pexcard->save();
		}
		echo json_encode( [ 'success' => $admin_pexcard->id_admin_pexcard ] );
	}

	private function _pex_id(){
		$crunchbutton_id = $this->request()[ 'id' ];
		if( $crunchbutton_id ){
			$cards = Crunchbutton_Pexcard_Card::card_list();
			if( is_array( $cards->body ) ){
				foreach( $cards->body as $card ){
					if( $card->lastName == $crunchbutton_id ){
						$admin_pexcard = Cockpit_Admin_Pexcard::getByPexcard( $card->id );
						if( $admin_pexcard->id_admin ){
							$card->id_admin = intval( $admin_pexcard->id_admin );
							$card->admin_name = $admin_pexcard->admin()->name;
							$card->admin_login = $admin_pexcard->admin()->login;
						}
						echo json_encode( $card );exit;
					}
				}
			} else {
				$this->_error( 'Oops, something is wrong!' );
			}
		}
		$this->_error( 'Card Not Found' );
	}

	private function _error( $error = 'invalid request' ){
		echo json_encode( [ 'error' => $error ] );
		exit();
	}
}
