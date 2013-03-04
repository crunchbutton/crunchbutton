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
		Crunchbutton_Session::flashMessage('Your data has been saved.');
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
/*
DEBUG #809
$dishes = json_decode( '{"dishes":[{"name":"teste","description":"","price":"1.00","id_category":"350","active":"true","sort":"0","id_dish":"1451","optionGroups":[{"name":"Basic options","default":"false","price":"false","id_option":"BASIC"}]},{"name":"dish 1-1","description":"","price":"1.00","id_category":"358","active":"true","sort":"1","id_dish":"1468","optionGroups":[{"name":"Basic options","default":"false","price":"false","id_option":"BASIC"},{"name":"option 1-1-2-1","default":"false","type":"select","price":"false","options":[{"name":"option 1-1-2-1-1","price":"1.00","default":"false","sort":"0","id_option":"7165"},{"name":"option 1-1-2-1-2","price":"1.00","default":"false","sort":"0","id_option":"7166"},{"name":"option 1-1-2-1-3","price":"1.00","default":"false","sort":"0","id_option":"7167"},{"name":"option 1-1-2-1-4","price":"1.00","default":"false","sort":"0","id_option":"7168"},{"name":"option 1-1-2-1-5","price":"5.00","default":"false","sort":"0","id_option":"7169"}],"id_option":"7164"}]},{"name":"dish 1-2","description":"","price":"0.00","id_category":"358","active":"true","sort":"2","id_dish":"1469","optionGroups":[{"name":"Basic options","default":"false","price":"false","id_option":"BASIC"},{"name":"option 1-1-1","default":"false","type":"select","price":"false","options":[{"name":"option 1-1-1-1","price":"1.00","default":"false","sort":"0","id_option":"7140"},{"name":"option 1-1-1-2","price":"2.00","default":"false","sort":"0","id_option":"7141"},{"name":"option 1-1-1-3","price":"3.00","default":"false","sort":"0","id_option":"7142"},{"name":"option 1-1-1-4","price":"4.00","default":"false","sort":"0","id_option":"7143"},{"name":"option 1-1-1-5","price":"5.00","default":"false","sort":"0","id_option":"7144"},{"name":"option 1-1-1-6","price":"6.00","default":"false","sort":"0","id_option":"7145"}],"id_option":"7139"},{"name":"option 1-1-2","default":"false","type":"select","price":"false","options":[{"name":"option 1-1-2-1","price":"1.00","default":"false","sort":"0","id_option":"7154"},{"name":"option 1-1-2-2","price":"2.00","default":"false","sort":"0","id_option":"7155"},{"name":"option 1-1-2-3","price":"3.00","default":"false","sort":"0","id_option":"7156"},{"name":"option 1-1-2-4","price":"4.00","default":"false","sort":"0","id_option":"7157"},{"name":"option 1-1-2-5","price":"5.00","default":"false","sort":"0","id_option":"7158"},{"name":"option 1-1-2-6","price":"6.00","default":"false","sort":"0","id_option":"7159"}],"id_option":"7153"}]},{"name":"dish 1-3","description":"","price":"0.00","id_category":"358","active":"true","sort":"3","id_dish":"1470","optionGroups":[{"name":"Basic options","default":"false","price":"false","options":[{"name":"option 1-3-1","price":"1.00","default":"false","sort":"0","id_option":"7170"},{"name":"option 1-3-2","price":"2.00","default":"false","sort":"0","id_option":"7171"},{"name":"option 1-3-3","price":"3.00","default":"false","sort":"0","id_option":"7172"},{"name":"option 1-3-4","price":"4.00","default":"false","sort":"0","id_option":"7173"},{"name":"option 1-3-5","price":"5.00","default":"false","sort":"0","id_option":"7174"},{"name":"option 1-3-6","price":"6.00","default":"false","sort":"0","id_option":"7175"}],"id_option":"BASIC"}]},{"name":"dish 2-1","description":"","price":"0.00","id_category":"359","active":"true","sort":"0","id_dish":"1471","optionGroups":[{"name":"Basic options","default":"false","price":"false","options":[{"name":"option 2-1-1","price":"1","default":"false","sort":"0"},{"name":"option 2-1-2","price":"2","default":"false","sort":"0"},{"name":"option 2-1-3","price":"3","default":"false","sort":"0"},{"name":"option 2-1-4","price":"4","default":"false","sort":"0"},{"name":"option 2-1-5","price":"5","default":"false","sort":"0"},{"name":"option 2-1-6","price":"6","default":"false","sort":"0"}],"id_option":"BASIC"},{"name":"option 2-2","default":"false","type":"check","price":"false","options":[{"name":"option 2-2-1","price":"1","default":"false","sort":"0"},{"name":"option 2-2-2","price":"2","default":"false","sort":"0"},{"name":"option 2-2-3","price":"3","default":"false","sort":"0"},{"name":"option 2-2-4","price":"4","default":"false","sort":"0"},{"name":"option 2-2-5","price":"5","default":"false","sort":"0"},{"name":"option 2-2-6","price":"6","default":"false","sort":"0"}]}]},{"name":"dish 2-2","description":"","price":"0.00","id_category":"359","active":"true","sort":"0","id_dish":"1472","optionGroups":[{"name":"Basic options","default":"false","price":"false","id_option":"BASIC"}]},{"name":"dish 2-3","description":"","price":"0.00","id_category":"359","active":"true","sort":"0","id_dish":"1473","optionGroups":[{"name":"Basic options","default":"false","price":"false","id_option":"BASIC"}]},{"name":"dish 3-1","description":"","price":"0.00","id_category":"360","active":"true","sort":"0","id_dish":"1474","optionGroups":[{"name":"Basic options","default":"false","price":"false","id_option":"BASIC"}]},{"name":"dish 3-2","description":"","price":"0.00","id_category":"360","active":"true","sort":"0","id_dish":"1475","optionGroups":[{"name":"Basic options","default":"false","price":"false","id_option":"BASIC"}]},{"name":"dish 3-3","description":"","price":"0.00","id_category":"360","active":"false","sort":"0","id_dish":"1476","optionGroups":[{"name":"Basic options","default":"false","price":"false","id_option":"BASIC"}]}],"type":"dishes"}' );

$restaurant->saveDishes( $dishes->dishes );
//echo json_encode($this->request()['dishes']);


exit;
*/
		if ($restaurant->id_restaurant) {
			$where = [];
			if (preg_match('/admin/i',$_SERVER['HTTP_REFERER'])) { // if API is being called by the admin
				$where['Dish']['active'] = NULL;
			}
			$json = json_encode($restaurant->exports($ignore = [], $where));
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

					$action = c::getPagePiece(3);
					switch ($action) {
						case 'categories':
							$this->_saveCategories($r);
							break;
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
						case 'notifications':
							$this->_saveNotifications($r);
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
							$this->_saveDishes($r);
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