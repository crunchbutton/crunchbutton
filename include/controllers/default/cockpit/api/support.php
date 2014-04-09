<?php

class Controller_api_support extends Crunchbutton_Controller_RestAccount {
	
	public function init() {
		
		switch ( c::getPagePiece( 2 ) ) {
			case 'count':
				echo json_encode( [ 'total' => Crunchbutton_Support::pendingSupport()->count() ] );
				break;
			case 'new-chat':
				$params = [];
				$params[ 'Action' ] = 'FakeSMS';
				$params[ 'Name' ] = $this->request()[ 'Name' ];;
				$params[ 'Created_By' ] = c::admin()->name;
				$params[ 'Body' ] = $this->request()[ 'Body' ];;
				$params[ 'From' ] = $this->request()[ 'From' ];
				$support = Crunchbutton_Support::createNewChat( $params );
				if( $support->id_support ){
						echo json_encode( [ 'success' => $support->id_support ] );
				} else {
					echo json_encode( [ 'error' => 'error creating new chat' ] );
				}
				break;
		}
		
	}
}
