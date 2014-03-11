<?php

class Controller_api_adminconfig extends Crunchbutton_Controller_RestAccount {
	
	public function init() {

		switch ( $this->method() ) {
			case 'post':
				$key = $this->request()[ 'key' ];
				$value = $this->request()[ 'value' ];
				$id_admin = $this->request()[ 'id_admin' ];
				$this->save( $key, $value, $id_admin );
			break;
			default:
				echo json_encode( [ 'error' => 'invalid object' ] );
			break;
		}
	}

	private function save( $key, $value, $id_admin = false ){
		if( !$id_admin ){
			$id_admin = c::admin()->id_admin;
		}
		$admin = Admin::o( $id_admin );
		if( $admin->id_admin ){
			$admin->setConfig( $key, $value );
			echo json_encode( [ 'success' => 'success' ] );
		} else {
			echo json_encode( [ 'error' => 'invalid object' ] );
		}
	}
}
