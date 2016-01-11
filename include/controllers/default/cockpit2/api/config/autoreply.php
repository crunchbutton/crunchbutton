<?php

class Controller_api_config_autoreply extends Crunchbutton_Controller_RestAccount {

	public function init() {

		if( !c::admin()->permission()->check( ['global'] ) ){
			$this->_error();
		}

		switch ( c::getPagePiece( 3 ) ) {
			case 'load':
				$this->_load();
				break;
			case 'save':
				$this->_save();
				break;
			case 'remove':
				$this->_remove();
				break;
			default:
				$this->_error();
				break;
		}
	}

	private function _load(){
		$messages = Crunchbutton_Config::q( 'SELECT * FROM config WHERE `key` = "' . Crunchbutton_Support::CONFIG_AUTO_REPLY_KEY . '" ORDER BY id_config' );
		$out = [];
		foreach( $messages as $message ){
			$out[] = [ 'id_config' => $message->id_config, 'text' => $message->value ];
		}
		echo json_encode( $out );exit;
	}

	private function _remove(){
		if( trim( $this->request()[ 'id_config' ] ) ){
			$id_config = trim( $this->request()[ 'id_config' ] );
			c::dbWrite()->query( 'DELETE FROM config WHERE id_config = "' . $id_config . '" AND `key` = "' . Crunchbutton_Support::CONFIG_AUTO_REPLY_KEY . '"' );
			echo json_encode( [ 'success' => 'success' ] );exit();
		} else {
			$this->_error();
		}
	}

	private function _save(){
		if( trim( $this->request()[ 'text' ] ) ){
			$config = new Crunchbutton_Config;
			$config->key = Crunchbutton_Support::CONFIG_AUTO_REPLY_KEY;
			$config->value = trim( $this->request()[ 'text' ] );
			$config->save();
			echo json_encode( [ 'success' => 'success' ] );exit();
		} else {
			$this->_error();
		}
	}

	private function _error( $error = 'invalid request' ){
		echo json_encode( [ 'error' => $error ] );
		exit();
	}
}
