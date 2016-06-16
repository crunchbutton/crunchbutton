<?php

class Controller_api_fax extends Crunchbutton_Controller_Rest {
	public function init() {
		$fax = $_REQUEST['fax'];
		$fax = json_decode($fax);
		if($fax && $fax->id){
			$body = "New fax received.\n";
			$body .= 'From number: ' . Phone::formatted(Phone::clean($fax->from_number)) . ".\n";
			$body .= 'To number: ' . Phone::formatted(Phone::clean($fax->to_number)) . ".\n";
			$body .= 'Pages: ' . $fax->num_pages . ".\n";
			$body .= 'URL: https://www.phaxio.com/viewFax?faxId=' . $fax->id;
			Support::createNewWarning(['body'=>$body, 'bubble'=>true]);
		}
	}
}
