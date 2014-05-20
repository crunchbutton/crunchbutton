<?php

class Controller_email extends Crunchbutton_Controller_Account{
	
	public function init() {

		if ( !c::admin()->permission()->check( [ 'global' ] ) ) {
			return ;
		}

		$id_admin = c::getPagePiece( 2 );

		switch ( c::getPagePiece( 1 ) ) {
			case 'welcome':
				$this->_welcome( $id_admin );
				break;
			case 'setup':
				$this->_setup( $id_admin );
				break;
		}
	}

	private function _welcome( $id_admin ){
		$mail = new Cockpit_Email_Driver_Welcome( [ 'id_admin' => $id_admin ] );
		echo $mail->message();
		exit;
	}

	private function _setup( $id_admin ){
		$mail = new Cockpit_Email_Driver_Setup( [ 'id_admin' => $id_admin ] );
		echo $mail->message();
		exit;
	}
}