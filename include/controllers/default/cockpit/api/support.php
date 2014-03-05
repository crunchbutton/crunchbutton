<?php

class Controller_api_support extends Crunchbutton_Controller_RestAccount {
	
	public function init() {
		
		if (!c::admin()->permission()->check( [ 'global','support-all', 'support-view', 'support-crud', 'support-settings' ])) {
			echo json_encode( [ 'error' => 'invalid object' ] );
			exit;
		}
		
		switch ( c::getPagePiece( 2 ) ) {
			case 'count':
				echo json_encode( [ 'total' => Crunchbutton_Support::pendingSupport()->count() ] );
				
				break;				
		}
		
	}
}
