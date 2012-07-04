<?php

class Crunchbutton_Order extends Cana_Table {
	public function process($params) {
		// @todo: add more security here

		$total = 0;

		foreach ($params['cart'] as $type => $typeItem) {
			switch ($type) {
				case 'dishes':
					foreach ($typeItem as $item) {
						$total += Dish::o($item['id'])->price;
						foreach ($item['toppings'] as $topping => $bleh) {
							$total += Topping::o($topping)->price;
						}
						foreach ($item['toppings'] as $topping => $bleh) {
							$total += Substitution::o($topping)->price;
						}
					}		
					break;
				case 'sides':
					foreach ($typeItem as $item) {
						$total += Side::o($item['id'])->price;
					}
					break;
				case 'extras':
					foreach ($typeItem as $item) {
						$total += Extra::o($item['id'])->price;
					}					
					break;
			}
		}

		$this->price = number_format($total, 2);
		$this->tip = $params['tip'];
		$this->id_restaurant = $params['restaurant'];
		$this->tax = $this->restaurant()->tax;
		$this->final_price = Util::ceil(
			($this->price * ($this->tip/100)) + // tip
			($this->price * ($this->tax/100)) + // tax
			$this->price
		, 2); // price

		$this->pay_type = $params['pay_type'] == 'cash' ? 'cash' : 'card';
		$this->delivery_type = $params['delivery_type'] == 'delivery' ? 'delivery' : 'takeout';
		$this->_address = $params['address'];
		$this->_phone = $params['phone'];
		$this->_name = $params['name'];
		
		$this->_number = $params['card']['number'];
		$this->_exp_month = $params['card']['month'];
		$this->_exp_year = $params['card']['year'];
		
		$this->order = json_encode($params['cart']);

		if (!c::user()->id_user) {
			if (!$params['name']) {
				$errors[] = 'Please enter a name.';
			}
			if (!$params['phone']) {
				$errors[] = 'Please enter a phone #.';
			}
			if (!$params['address'] && $this->delivery_type == 'delivery') {
				$errors[] = 'Please enter an address.';			
			}
		}

		if ($errors) {
			return $errors;
		}

		$res = $this->verifyPayment();

		if ($res !== true) {
			return $res['errors'];

		} else {
			$this->txn = $this->transaction()->id;
		}
		
		c::auth()->session()->id_user = $this->_user->id_user;
		
		$this->id_user = $this->_user->id_user;
		$this->save();

		return true;
	}
	
	public function restaurant() {
		return Restaurant::o($this->id_restaurant);
	}
	
	public function transaction() {
		return $this->_txn;
	}
	
	public function verifyPayment() {
		switch ($this->pay_type) {
			case 'cash':
				return true;
				break;

			case 'card':
				$r = Charge::charge([
					'amount' => $this->final_price,
					'number' => $this->_number,
					'exp_month' => $this->_exp_month,
					'exp_year' => $this->_exp_year,
					'name' => $this->_name,
					'address' => $this->_address,
					'phone' => $this->_phone,
					'user' => c::user()->id_user ? c::user() : null
				]);
				if ($r['status']) {
					$this->_txn = $r['txn'];
					$this->_user = $r['user'];
					return true;
				} else {
					return $r;
				}
				break;
		}
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('order')
			->idVar('id_order')
			->load($id);
	}
}