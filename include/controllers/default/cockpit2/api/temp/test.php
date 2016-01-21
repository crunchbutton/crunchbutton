<?php

class Controller_api_temp_test extends Crunchbutton_Controller_RestAccount {

	public function init(){

			Log::debug( [
				'action' 				=> 'testing log insert',
				'type' 					=> 'testing log'
			] );

	}
}
