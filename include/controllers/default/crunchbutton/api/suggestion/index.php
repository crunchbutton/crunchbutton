<?php

class Controller_api_Suggestion extends Crunchbutton_Controller_Rest {

	public function init() {

		switch ( $this->method() ) {
			// Saves a suggestion
			case 'post':

				if (c::getPagePiece(2) == 'save-suggestion') {

					$suggesion = new Suggestion;
					$suggesion->status = 'new';
					$suggesion->type = $this->request()[ 'type' ];
					$suggesion->id_user = c::user()->id_user ? c::user()->id_user : null;
					$suggesion->name = c::user()->name ? c::user()->name : null;
					$suggesion->id_restaurant = $this->request()[ 'id_restaurant' ];
					$suggesion->id_community = $this->request()[ 'id_community' ];
					$suggesion->content = $this->request()[ 'content' ];
					$suggesion->ip = c::getIp();
					$suggesion->date = date('Y-m-d H:i:s');
					$suggesion->save();
					echo $suggesion->json();

					exit();
				}

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
					$suggesion->ip = c::getIp();
					$suggesion->date = date('Y-m-d H:i:s');
					$suggesion->save();

					$suggesion->queNotify();

					echo $suggesion->json();
					exit;
				}

				if (c::getPagePiece(2) == 'restaurant') {

					$suggesion = new Suggestion;

					$suggesion->id_user = c::user()->id_user ? c::user()->id_user : null;

					$suggesion->status = 'new';
					$suggesion->type = 'restaurant';
					$suggesion->name = $this->request()['name'];
					$suggesion->content = $this->request()['content'];
					$suggesion->ip = c::getIp();
					$suggesion->date = date('Y-m-d H:i:s');
					$suggesion->save();

					echo $suggesion->json();
					exit;
				}

				if (c::getPagePiece(2) == 'relateuser') {

					$id_suggestion = $this->request()['id_suggestion'];
					$id_user = $this->request()['id_user'];
					$suggesion = Suggestion::o( $id_suggestion );
					if( $suggesion->id_suggestion && $id_user ){
						$suggesion->id_user = $id_user;
						$suggesion->save();
						echo json_encode(['success' => 'success']);
						exit;
					}
					echo json_encode(['error' => 'error']);
					exit;
				}

				// notify by email if we are in the area
				if (c::getPagePiece(2) == 'notify') {

					$suggesion = new Suggestion;

					$suggesion->id_user = c::user()->id_user ? c::user()->id_user : null;

					$suggesion->status = 'new';
					$suggesion->type = 'email';
					$suggesion->name = $this->request()['name'];
					$suggesion->content = $this->request()['content'];
					$suggesion->ip = c::getIp();
					$suggesion->date = date('Y-m-d H:i:s');
					$suggesion->save();

					echo $suggesion->json();
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