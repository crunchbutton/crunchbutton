<?php

class Controller_api_log extends Crunchbutton_Controller_Rest {
	public function init() {
		// Log the order - process started
		switch ( $this->method() ) {
			case 'post':
				Log::debug([
							'action' => 'javascript - log',
							'data' 	 => $this->request()['data'],
							'type' 	 => $this->request()['type']
						]);
				echo json_encode(['success' => 'success']);
			break;
		
			default:
				echo json_encode(['error' => 'invalid request']);
			break;
		}
	}
}