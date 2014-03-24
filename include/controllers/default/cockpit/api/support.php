<?php

class Controller_api_support extends Crunchbutton_Controller_RestAccount {
	
	public function init() {
		
		switch ( c::getPagePiece( 2 ) ) {
			case 'count':
				echo json_encode( [ 'total' => Crunchbutton_Support::pendingSupport()->count() ] );
				
				break;				
		}
		
	}
}
