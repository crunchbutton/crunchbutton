<?php

class Controller_api_Giftcard_group extends Crunchbutton_Controller_Rest {
	public function init() {

		switch ( $this->method() ) {
			
			case 'post':
				
				if ( $_SESSION['admin'] ) {
					$id_promo_group = c::getPagePiece( 3 );
					$name = $_REQUEST[ 'name' ];
					$range = $_REQUEST[ 'range' ];
					$show_at_metrics = $_REQUEST[ 'show_at_metrics' ];
					if( $id_promo_group ){
						$group = Crunchbutton_Promo_Group::o( $id_promo_group );
						$group->name = $name;
						$group->show_at_metrics = 1;
						$group->date = date('Y-m-d H:i:s');
						$group->save();
						if( trim( $range ) != '' ){
							$group->save_giftcards( $range );	
						}
					} else {
						$group = new Crunchbutton_Promo_Group();
						$group->name = $name;
						$group->show_at_metrics = 1;
						$group->date = date('Y-m-d H:i:s');
						$group->save();
						if( trim( $range ) != '' ){
							$group->save_giftcards( $range );	
						}
					}
					echo json_encode( ['success' => $group->id_promo_group ] );
				}

			break;
			default:
				echo json_encode( [ 'error' => 'invalid object' ] );
			break;
		}
	}
}