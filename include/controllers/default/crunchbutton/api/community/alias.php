<?php

class Controller_api_community_alias extends Crunchbutton_Controller_Rest {
	
	public function init() {

		switch ( $this->method() ) {

			case 'get':
				$alias = c::getPagePiece( 3 );
				if( $alias != '' ){
					$alias = Community_Alias::alias( $alias );
					if( $alias ){
						echo json_encode( $alias );
				 	} else {
				 		echo json_encode( [ 'error' => 'invalid object' ] );
				 	}
				} else {
					echo json_encode( Community_Alias::all() );
				}
			break;

			default:
				echo json_encode( [ 'error' => 'invalid object' ] );
			break;
		}
	
	}
}