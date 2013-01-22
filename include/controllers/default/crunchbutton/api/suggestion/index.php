<?php

class Controller_api_Suggestion extends Crunchbutton_Controller_Rest {
	public function init() {

		switch ( $this->method() ) {
			// Saves a suggestion
			case 'post':

				if( c::getPagePiece(2) == 'new' ){
					// If is not admin a new Suggestion will be added
					$s = Suggestion::o(0);
					$request = $this->request();
					foreach ($request as $key => $value) {
						if ($value == 'null') {
							$request[$key] = null;
						}
					}
					$request[ 'ip' ] = $_SERVER['REMOTE_ADDR'];
					$request[ 'date' ] = date('Y-m-d H:i:s');
					$s->serialize($request);
					$s->save();
					echo $s->json();
					exit;
				}

				// If is admin changes the Suggestion attributes
				if ($_SESSION['admin']) {
					$s = Suggestion::o(c::getPagePiece(2));
					$request = $this->request();
					foreach ($request as $key => $value) {
						if ($value == 'null') {
							$request[$key] = null;
						}
					}
					$s->serialize($request);
					$s->save();
					echo $s->json();
				} 
			break;

			case 'get':
				// Get the suggestion by id.
				$out = Suggestion::o(c::getPagePiece(2));
				if ($out->id_suggestion) {
					echo $out->json();
				} else {
					echo json_encode(['error' => 'invalid object']);
				}
				break;
		}
	}
}