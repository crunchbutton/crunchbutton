<?php

class Controller_giftcards_groups extends Crunchbutton_Controller_Account {

	public function init() {

		$action = c::getPagePiece(2);

		switch ( $action ) {

			case 'content':
				$this->search();
				break;

			case 'new':
				$this->form();
				break;

			case 'remove':
				$id_promo_group = $_REQUEST[ 'id_promo_group' ];
				$group = Crunchbutton_Promo_Group::o( $id_promo_group );
				if( $group->id_promo_group ){
					$group->delete();
				}
				echo 'ok';
				break;

			default:

				if( is_numeric( $action ) ){
					$this->form();
					exit;
				}
				c::view()->page = 'giftcards';
				c::view()->display('giftcards/groups/index');
				break;
		}
	}

	private function search(){
		$search = [];
		if ( $_REQUEST[ 'name' ] ) {
			$search[ 'name' ] = $_REQUEST[ 'name' ];
		}
		c::view()->giftcards_groups = Crunchbutton_Promo_Group::find( $search );
		c::view()->layout( 'layout/ajax' );
		c::view()->display( 'giftcards/groups/content' );
	}

	private function form(){
		$id_promo_group = c::getPagePiece(2);
		if( $id_promo_group != 'new' ){
			c::view()->group = Crunchbutton_Promo_Group::o( $id_promo_group );
		} else {
			c::view()->group = new Crunchbutton_Promo_Group();
		}
		c::view()->display( 'giftcards/groups/form' );
	}
}