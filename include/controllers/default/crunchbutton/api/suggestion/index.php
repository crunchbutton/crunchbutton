<?php

class Controller_api_Suggestion extends Crunchbutton_Controller_Rest {
	public function init() {

		switch ( $this->method() ) {
			// Saves a suggestion
			case 'post':

				if (c::getPagePiece(2) == 'new') {
				
					$suggesion = new Suggestion;
					$restaurant = Restaurant::permalink($this->request()['restaurant']);
				
					if (!$restaurant->id_restaurant || !$this->request()['name']) {
						echo json_encode(['status' => false]);
						exit;
					}

					$suggesion->id_user = c::user()->name ? c::user()->id_user : null;

					$suggesion->status = 'new';
					$suggesion->type = 'dish';
					$suggesion->id_restaurant = $restaurant->id_restaurant;
					$suggesion->name = $this->request()['name'];
					$suggesion->ip = $_SERVER['REMOTE_ADDR'];
					$suggesion->date = date('Y-m-d H:i:s');
					$suggesion->save();
					
					$suggesion->queNotify();

					echo $suggesion->json();
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