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
					$date_mkt = $_REQUEST[ 'date_mkt' ];
					$date_mkt = explode( '/', $date_mkt );
					$date_mkt = $date_mkt[2].'-'.$date_mkt[0].'-'.$date_mkt[1];
					$community = $_REQUEST[ 'community' ];
					$promotion_type = $_REQUEST[ 'promotion_type' ];
					$description = $_REQUEST[ 'description' ];
					$man_hours = $_REQUEST[ 'man_hours' ];
					if( $id_promo_group ){
						$group = Crunchbutton_Promo_Group::o( $id_promo_group );
					} else {
						$group = new Crunchbutton_Promo_Group();
						$group->date = date('Y-m-d H:i:s');
					}
					$group->name = $name;
					$group->show_at_metrics = 1;
					$group->range = $range;
					$group->date_mkt = $date_mkt;
					$group->community = $community;
					$group->promotion_type = $promotion_type;
					$group->description = $description;
					$group->man_hours = $man_hours;
					$group->save();
					if( trim( $range ) != '' ){
						$group->save_giftcards( $range );	
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