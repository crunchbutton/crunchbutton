<?php

class Controller_api_restaurant extends Crunchbutton_Controller_Rest {
	public function init() {
		switch ($this->method()) {
			case 'post':
				// @todo: real logins
				if ($_SESSION['admin']) {
					// save the restaurant
					$r = Restaurant::o(c::getPagePiece(2));

					switch (c::getPagePiece(3)) {
						case 'fake-merchant':
							if ($r->id_restaurant) {
								$r->balanced_id = c::config()->balanced->sharedMerchant;
								$r->save();
							}
							break;
							
						case 'fakeremove-merchant':
							if ($r->id_restaurant) {
								$r->balanced_id = null;
								$r->save();
							}
							break;
							
						case 'remove-bankinfo':
							if ($r->id_restaurant) {
								$r->balanced_bank = null;
								$r->save();
							}
							break;

						case 'merchant':
							if ($r->id_restaurant) {
								$r->createMerchant([
									'name' => $this->request()['name'],
									'zip' => $this->request()['zip'],
									'address' => $this->request()['address'],
									'dob' => $this->request()['dob']
								]);
							}
							break;					

						case 'credit':
							if ($r->id_restaurant) {
								$p = Payment::credit([
									'id_restaurant' => $r->id_restaurant,
									'amount' => $this->request()['amount'],
									'note' => $this->request()['note']
								]);
							}
							break;

						case 'bankinfo':
							if ($r->id_restaurant) {
								$r->saveBankInfo($this->request()['name'],$this->request()['account'],$this->request()['routing']);
							}
							break;

						case 'hours':
							if ($r->id_restaurant) {
								$r->saveHours($this->request()['hours']);
								echo json_encode($this->request()['hours']);
							}
							break;
							
						case 'dishes':
							if ($r->id_restaurant) {
								$r->saveDishes($this->request()['dishes']);
								echo json_encode($this->request()['dishes']);
							}
							break;

						default:
							$r->serialize($this->request());
							$r->save();
							
							// save the community
							if ($this->request()['id_community']) {
								$c = Community::o($this->request()['id_community']);
								
								// only save if its a valid community
								if ($c->id_community) {
									$rc = Restaurant_Community::q('select * from restaurant_community where id_restaurant="'.$r->id_restaurant.'"');
									if (!$rc->id_restaurant_community) {
										$rc = new Restaurant_Community;
										$rc->id_restaurant = $r->id_restaurant;
									}
									$rc->id_community = $this->request()['id_community'];
									$rc->save();
								}
							}
							echo $r->json();
							break;
					}
					
				}
				break;

			case 'get':
				$out = Restaurant::o(c::getPagePiece(2));
				if ($out->id_restaurant) {
					echo $out->json();
				} else {
					echo json_encode(['error' => 'invalid object']);
				}
				break;
		}
	}
}