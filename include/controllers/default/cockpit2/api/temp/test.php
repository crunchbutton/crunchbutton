<?php

class Controller_api_temp_test extends Crunchbutton_Controller_RestAccount {

	public function init(){
			$log = Crunchbutton_Phone_Log::o( $_GET[ 'id' ] );
			$log->emit();
	}
}
