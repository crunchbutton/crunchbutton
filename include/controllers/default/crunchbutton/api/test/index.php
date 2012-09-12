<?php

class Controller_api_test extends Crunchbutton_Controller_Rest {
	public function init() {
	$account = Crunchbutton_Balanced_Account::byId('AC5eeCR7KIgHQNJrHiQvWoa3');
//	$account->email_address = 'bacontest@arzynik.com';
//	$account->save();
	print_r($account);
	exit;
			try {
				$card = c::balanced()->createCard(null, null, null, null,
					'me',
					'4242424242424242',
					null,
					'09',
					'2023'
				);


				$customer = c::balanced()->createBuyer(
					'mytest@_DOMAIN_',
					$card->uri,
					[
						'name' => 'me'
					]
				);
				$this->_customer = $customer;

				$c = $customer->debit($params['amount'] * 100, $params['restaurant']->name);

			} catch (Exception $e) {
				print_r($e);
//				$e->description
				}
				print_r($c);
		exit;
		$q = 'select dish_option.* from dish_option left join dish using(id_dish) where dish.id_restaurant="18" and dish_option.id_dish="126"';
		$r = c::db()->query($q);
		while ($o = $r->fetch()) {

			$ob = new Dish_Option;
			$ob->id_dish = 125;
			$ob->id_option = $o->id_option;
			$ob->default = $o->default;
			$ob->save();
		}

		//$o = new Order(111);
		//$o->notify();
	}
}