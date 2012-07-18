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
		$this->date = date('Y-m-d H:i:s');
		$this->save();
		
		$this->notify();

		return true;
	}
	
	public static function uuid($uuid) {
		return self::q('select * from `order` where uuid="'.$uuid.'"');
	}
	
	public function restaurant() {
		return Restaurant::o($this->id_restaurant);
	}
	
	public function user() {
		return User::o($this->id_user);
	}
	
	public function transaction() {
		return $this->_txn;
	}
	
	public function date() {
		if (!isset($this->_date)) {
			$this->_date = new DateTime($this->date, new DateTimeZone($this->restaurant()->timezone));
		}
		return $this->_date;
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
	
	public static function recent() {
		return self::q('select * from `order` order by `date`');
	}
	
	public function order() {
		if (!isset($this->_order)) {
			$order = json_decode($this->order,'array');

			foreach ($order as $type => $typeItem) {
				switch ($type) {
					case 'dishes':
						foreach ($typeItem as $item) {
							$dish = Dish::o($item['id']);
							if ($item['toppings']) {
								foreach ($item['toppings'] as $topping => $bleh) {
									$dish->_toppings[] = Topping::o($topping);
								}
							}
							if ($item['substitutions']) {
								foreach ($item['substitutions'] as $topping => $bleh) {
									$dish->_substitution[] += Substitution::o($topping);
								}
							}
							$orderItems[] = $dish;
						}		
						break;
					case 'sides':
						foreach ($typeItem as $item) {
							$orderItems[] = Side::o($item['id']);
						}
						break;
					case 'extras':
						foreach ($typeItem as $item) {
							$orderItems[] = Extra::o($item['id']);
						}					
						break;
				}
			}
			$this->_order = $orderItems;
		}
		
		return $this->_order;
	}
	
	public function tip() {
		return number_format($this->price * ($this->tip/100),2);
	}
	
	public function tax() {
		return number_format($this->price * ($this->tax/100),2);
	}
	
	public function notify() {
		foreach ($this->restaurant()->notifications() as $n) {
			$n->send($this);
		}
	}
	
	public function message($type) {
		foreach ($order->order() as $item) {
			$food .= $item->name.' ';
			if ($item->_toppings || $item->_substitutions) {
				$food .= 'with ';
			}
			if ($item->_toppings) {
				foreach ($item->_toppings as $topping) {
					$food .= $topping->name.', ';
				}
			}
			if ($item->_substitutions) {
				foreach ($item->_substitutions as $topping) {
					$food .= $topping->name.', ';
				}
			}
		}

		switch ($type) {
			case 'sms':
				$msg = $this->name.' ordered '.$this->delivery_type.' paying by '.$this->pay_type.'. '.$food.'.  phone: '.preg_replace('/[^\d.]/','',$this->phone).'.';
				if ($this->delivery_type == 'delivery') {
					$msg .= ' address: '.$this->address;
				}
				break;

			case 'phone':
				$msg = 'This is an automated order. A customer ordered '.$food.'. Name. '.$this->name.'.  Phone number. '.preg_replace('/[^\d.]/','',$this->phone).'.  Customer paying by '.$this->pay_type.'.';
				if ($this->delivery_type == 'delivery') {
					$msg .= ' Deliver to '.$this->address;
				}
				break;
		}
		return $msg;
	}
	
	public function exports() {
		$out = $this->properties();
		unset($out['id_user']);
		unset($out['id']);
		unset($out['id_order']);
		$out['user'] = $this->user()->uuid;
		return $out;
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('order')
			->idVar('id_order')
			->load($id);
	}
}