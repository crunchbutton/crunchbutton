<?php

class Controller_api_tv extends Crunchbutton_Controller_RestAccount {

	public function init() {
		$tv = Tv::data();
		echo json_encode($tv);
		
	}

	

}