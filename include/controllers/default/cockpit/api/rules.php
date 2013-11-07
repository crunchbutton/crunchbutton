<?php

class Controller_api_rules extends Crunchbutton_Controller_RestAccount {
	
	public function init() {
		
		if (!c::admin()->permission()->check(['global','rules'])) {
			return ;
		}
		foreach( $_POST as $key => $value ){
			Crunchbutton_Config::store( $key, $value );
		}
		echo json_encode( array( 'success' => true ) );
	}
}
