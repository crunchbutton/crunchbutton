<?php

class Controller_api_restaurant extends Crunchbutton_Controller_Rest {

	/**
	 * Stores the categories
	 *
	 * @param Crunchbutton_Restaurant $restaurant Current restaurant
	 *
	 * @return void
	 */
	private function _saveCategories(Crunchbutton_Restaurant $restaurant)
	{
		if (!$restaurant->id_restaurant) return;
		$elements = $restaurant->saveCategories($this->request()['elements']);

		$out = [];
		foreach ($elements as $element) {
			/* @var $element Crunchbutton_Category */
			$out['_categories'][$element->id_category] = $element->exports();
		}
		echo json_encode($out);
	}

	/**
	 * Save all dishes for this restaurant
	 *
	 * @param Crunchbutton_Restaurant $restaurant
	 *
	 * @todo shouldn't we return the saved dishes to confirm?
	 */
	private function _saveDishes(Crunchbutton_Restaurant $restaurant)
	{
		if ($restaurant->id_restaurant) {
			$restaurant->saveDishes($this->request()['dishes']);
			echo json_encode($this->request()['dishes']);
		}
	}

	/**
	 * Stores the notifications
	 *
	 * @param Crunchbutton_Restaurant $restaurant Current restaurant
	 *
	 * @return void
	 */
	private function _saveNotifications(Crunchbutton_Restaurant $restaurant)
	{
		if (!$restaurant->id_restaurant) return;
		$elements = $restaurant->saveNotifications($this->request()['elements']);

		$out = [];
		foreach ($elements as $notification) {
			/* @var $notification Crunchbutton_Notification */
			$out['_notifications'][$notification->id_notification] = $notification->exports();
		}
		echo json_encode($out);
	}

	private function _saveRestaurant(Crunchbutton_Restaurant $restaurant)
	{
		$request = $this->request();
		foreach ($request as $key => $value) {
			if ($value == 'null') {
				$request[$key] = null;
			}
		}
		// echo var_dump($request);exit;
		$restaurant->serialize($request);
		$restaurant->save();

		// save the community
		if ($this->request()['id_community']) {
			$c = Crunchbutton_Community::o($this->request()['id_community']);

			// only save if its a valid community
			if ($c->id_community) {
				$rc = Crunchbutton_Restaurant_Community::q('select * from restaurant_community where id_restaurant="'.$restaurant->id_restaurant.'"');
				if (!$rc->id_restaurant_community) {
					$rc = new Crunchbutton_Restaurant_Community;
					$rc->id_restaurant = $restaurant->id_restaurant;
				}
				$rc->id_community = $this->request()['id_community'];
				$rc->sort         = $this->request()['sort'];
				$rc->save();
			}
		}

		// Removes restaurant from community
		if( $this->request()['id_community'] == 0 ){
			$rc = Crunchbutton_Restaurant_Community::q('select * from restaurant_community where id_restaurant="'.$restaurant->id_restaurant.'"');
			$rc->delete();
		}

		echo $restaurant->json();
	}

	/**
	 * Echo JSON with restaurant data
	 *
	 * Let's see first if we got a permalink, or else, try to load it by ID.
	 * See issue #776, the problem is that restaurants starting with a number
	 * in their permalink, are used as the number, ignoring the rest of the
	 * text.
	 *
	 * We do not use the Restaurant->json() method as we need to send the $where
	 * variable to the Restaurant->export() metod after we detected the API was
	 * called from the admin side
	 *
	 * @return void
	 */
	private function _returnRestaurant()
	{
		$restaurant = Crunchbutton_Restaurant::permalink(c::getPagePiece(2));
		/* @var $restaurant Crunchbutton_Restaurant */
		if (!$restaurant->id_restaurant) {
			$restaurant = Crunchbutton_Restaurant::o(c::getPagePiece(2));
		}
		if ($restaurant->id_restaurant) {
			$where = [];
			if (preg_match('/admin/i',$_SERVER['HTTP_REFERER'])) { // if API is being called by the admin
				// @todo: refered can be spoofed!!! who put this here?! wtf?!
				$where['Dish']['active'] = NULL;
			}
			$isCockpit = ( $_REQUEST[ 'cockpit' ] || ( strpos( $_SERVER['HTTP_HOST'], 'cockpit' ) !== false )  ) ? true : false;
			if( $isCockpit ){
				// dont show the price recalculated by delivery_service_markup at cockpit
				$ignore = array( 'delivery_service_markup_prices' => 1 );
			} else {
				$ignore = [];
			}
			$json = json_encode($restaurant->exports( $ignore, $where));
		} else {
			$json = json_encode(['error' => 'invalid object']);
		}
		echo $json;
	}

	public function init() {
		switch ($this->method()) {
			case 'post':
				// @todo: real logins
				if ($_SESSION['admin']) {
					// save the restaurant
					$r = Restaurant::o(c::getPagePiece(2));
					/* @var $r Crunchbutton_Restaurant */

					// Permissions
					if( !c::admin() ){
						return;
					}

					$hasPermission = c::admin()->permission()->check(['global', 'restaurants-all', "restaurant-{$r->id_restaurant}-all" ]);

					if( !$hasPermission ){
						switch ($action) {
							case 'fake-merchant':
							case 'fakeremove-merchant':
							case 'remove-bankinfo':
							case 'paymentinfo':
							case 'merchant':
							case 'credit':
							case 'bankinfo':
							case 'remove-stripe-recipient':
							case 'stripe-recipient':
							case 'stripe-account':
							case 'stripe-credit':
								$hasPermission = c::admin()->permission()->check(['global', 'restaurants-all', "restaurant-{$r->id_restaurant}-pay" ]);
								break;

							case 'weight-adj':
							case 'categories':
							case 'notifications':
							case 'hours':
							case 'delete-category': 
							case 'delete-dish': 
							case 'save-dish':
							case 'dishes':
							default:
								$hasPermission = c::admin()->permission()->check(['global', 'restaurants-all', "restaurant-{$r->id_restaurant}-edit" ]);
								break;
						}	
					}

					if( !$hasPermission ){
						echo json_encode( [ 'error' => 'permission denied: ' . $action ] );
					}

					$action = c::getPagePiece(3);
					switch ($action) {
						case 'categories':
							$this->_saveCategories($r);
							break;
						case 'fake-merchant':
							if ($r->id_restaurant) {
								$payment = $r->payment_type();
								$payment->id_restaurant = $r->id_restaurant;
								$payment->balanced_id = c::config()->balanced->sharedMerchant;
								$payment->save();
								echo json_encode( [ 'success' => 'success' ] );
							}
							break;

						case 'stripe-account':
							if ($r->id_restaurant) {
								$bank_account = $this->request()['bank_account'];
								if( $r->saveStripeBankAccount( $bank_account ) ){
									echo json_encode( [ 'success' => 'success' ] );	
								} else {
									echo json_encode( [ 'error' => 'error' ] );	
								}
								
							}
							break;

						case 'remove-stripe-recipient':
							if ( $r->id_restaurant ) {
								$payment = $r->payment_type();
								$payment->stripe_id = null;
								$payment->stripe_account_id = null;
								$payment->save();
								echo json_encode( [ 'success' => 'success' ] );
							}
							break;

						case 'fakeremove-merchant':
							if ($r->id_restaurant) {
								$payment = $r->payment_type();
								$payment->id_restaurant = $r->id_restaurant;
								$payment->balanced_id = null;
								$payment->save();
								echo json_encode( [ 'success' => 'success' ] );
							}
							break;

						case 'remove-bankinfo':
							if ($r->id_restaurant) {
								$payment = $r->payment_type();
								$payment->id_restaurant = $r->id_restaurant;
								$payment->balanced_bank = null;
								$payment->save();
								echo json_encode( [ 'success' => 'success' ] );
							}
							break;

						case 'stripe-recipient':
							if ($r->id_restaurant) {
								$name = $this->request()['name'];
								$type = $this->request()['type'];
								$tax_id = $this->request()['tax_id'];
								if( $r->saveStripeRecipient( $name, $type, $tax_id ) ){
									echo json_encode( [ 'success' => 'success' ] );	
								} else {
									echo json_encode( [ 'error' => 'error' ] );	
								}
								
							}
							break;

						case 'paymentinfo':

							if ( $r->id_restaurant ) {
								$payment = $r->payment_type();
								$payment->id_restaurant = $r->id_restaurant;
								$payment->payment_method = $this->request()['payment_method'];
								$payment->id_restaurant_pay_another_restaurant = $this->request()['id_restaurant_pay_another_restaurant'];
								$payment->check_address = $this->request()['check_address'];
								$payment->contact_name = $this->request()['contact_name'];
								$payment->summary_fax = $this->request()['summary_fax'];
								$payment->summary_email = $this->request()['summary_email'];
								$payment->summary_method = $this->request()['summary_method'];
								$payment->summary_frequency = $this->request()['summary_frequency'];
								$payment->legal_name_payment = $this->request()['legal_name_payment'];
								$payment->tax_id = $this->request()['tax_id'];
								$payment->charge_credit_fee = $this->request()['charge_credit_fee'];
								$payment->waive_fee_first_month = $this->request()['waive_fee_first_month'];
								$payment->pay_promotions = $this->request()['pay_promotions'];
								$payment->pay_apology_credits = $this->request()['pay_apology_credits'];
								$payment->max_apology_credit = $this->request()['max_apology_credit'];
								$payment->save();
								echo json_encode( [ 'success' => 'restaurant saved' ] );
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
						case 'notifications':
							$this->_saveNotifications($r);
							break;

						case 'stripe-credit':
							if ($r->id_restaurant) {
								$p = Payment::credit([
									'id_restaurant' => $r->id_restaurant,
									'amount' => $this->request()['amount'],
									'note' => $this->request()['note'],
									'type' => 'stripe'
								]);
								if( $p ){
									echo json_encode( [ 'success' => 'success' ] );
								} else {
									echo json_encode( [ 'error' => 'error' ] );
								}
							}
							break;

						case 'credit':
							if ($r->id_restaurant) {
								$p = Payment::credit([
									'id_restaurant' => $r->id_restaurant,
									'amount' => $this->request()['amount'],
									'note' => $this->request()['note'],
									'type' => 'balanced'
								]);
								if( $p ){
									echo json_encode( [ 'success' => 'success' ] );
								} else {
									echo json_encode( [ 'error' => 'error' ] );
								}
							}
							break;

						case 'bankinfo':
							if ($r->id_restaurant) {
								$r->saveBankInfo($this->request()['name'],$this->request()['account'],$this->request()['routing'],$this->request()['type']);
							}
							break;

						case 'hours':
							if ($r->id_restaurant) {
								$r->saveHours($this->request()['hours']);
								echo json_encode($this->request()['hours']);
							}
							break;

						case 'delete-category': 
								if( $r->deleteCategory( $this->request()[ 'id_category' ] ) ){
									echo json_encode( [ 'success' => 'category deleted' ] );
								} else {
									echo json_encode( [ 'error' => 'category not deleted' ] );
								}
							break;


						case 'delete-dish': 
								if( $r->deleteDish( $this->request()[ 'id_dish' ] ) ){
									echo json_encode( [ 'success' => 'dish deleted' ] );
								} else {
									echo json_encode( [ 'error' => 'dish not deleted' ] );
								}
							break;

					case 'save-dish': 
								if( $r->saveDish( $this->request()[ 'dish' ] ) ){
									echo json_encode( [ 'success' => 'dish saved' ] );
								} else {
									echo json_encode( [ 'error' => 'dish not saved' ] );
								}
							break;

						case 'dishes':
							$this->_saveDishes($r);
							break;
						
						case  'weight-adj':
							$weight_adj = $this->request()['weight_adj'];
							$r->weight_adj = $weight_adj;
							$r->save();
							echo json_encode( [ 'success' => 'saved' ] );
						break;

						default:
							$this->_saveRestaurant($r);
							break;
					}

				}
				break;

			case 'get':
				$this->_returnRestaurant();
				break;
		}
	}
}