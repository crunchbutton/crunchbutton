<?php

class Controller_Api_Stripe_Dispute extends Crunchbutton_Controller_Rest {

	public function init() {

		$this->_permissionDenied();

		$dispute = Crunchbutton_Stripe_Dispute::o( c::getPagePiece( 3 ) );
		if( !$dispute->id_stripe_dispute ){
			$this->error(404);
		}
		// actions
		switch ( c::getPagePiece( 4 ) ) {
			case 'value':
				# code...
				break;

			default:
				$this->_export( $dispute );
				break;
		}

	}

	private function _export( $dispute ){
		$out = $dispute->properties();
		$out['date'] = $dispute->date()->format('c');
		$out[ 'order' ] = $dispute->order()->exports();
		$out['user'] = $dispute->order()->user()->id_user ? $dispute->order()->user()->exports() : null;
		$out[ 'log' ] = [];
		$log_data = $dispute->log();
		foreach( $log_data as $log ){
			$out[ 'log' ][] = $log->exports();
		}
		echo json_encode( $out );exit();
	}

	private function _permissionDenied(){
		if (!c::admin()->permission()->check(['global', 'permission-all', 'permission-users'])) {
			$this->error(401);
		}
	}
}
