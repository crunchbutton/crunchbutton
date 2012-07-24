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
						if ($item['toppings']) {
							foreach ($item['toppings'] as $topping => $bleh) {
								$total += Topping::o($topping)->price;
							}
						}
						if ($item['substitutions']) {
							foreach ($item['substitutions'] as $topping => $bleh) {
								$total += Substitution::o($topping)->price;
							}
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
		$this->address = $params['address'];
		$this->phone = $params['phone'];
		$this->name = $params['name'];
		
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
		} else {
			if (!$params['address']) {
				$this->address = c::user()->address;
			}
			if (!$params['phone']) {
				$this->phone = c::user()->phone;
			}
			if (!$params['name']) {
				$this->name = c::user()->name;
			}
		}
		
		if (!$this->restaurant()->open()) {
			$errors[] = 'This restaurant is closed.';
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
		
		$user = c::user()->id_user ? c::user() : new User;
		
		if (!c::user()->id_user) {
			$user->active = 1;
			$user->stripe_id = $this->_customer->id;
		}

		$user->name = $this->name;
		$user->phone = $this->phone;
		
		if ($this->delivery_type == 'delivery') {
			$user->address = $this->address;
		}

		if ($this->pay_type == 'card' && $params['card']['number']) {
			$user->card = str_repeat('*',strlen($params['card']['number'])-4).substr($params['card']['number'],-4);
		}
		
		$user->pay_type = $this->pay_type;
		$user->delivery_type = $this->delivery_type;

		$user->save();
		$this->_user = $user;
		
		c::auth()->session()->id_user = $user->id_user;
		c::auth()->session()->generateAndSaveToken();
		
		$this->id_user = $this->_user->id_user;
		$this->date = date('Y-m-d H:i:s');
		$this->save();
		
		if (c::env() == 'live') {
			$this->que();
		} else {
			$this->notify();
		}
		
		$def = json_encode($params['cart']);
		if ($def != $this->restaurant()->defaultOrder()->config) {
			$defaultOrder = new Restaurant_DefaultOrder;
			$defaultOrder->id_user = $this->id_user;
			$defaultOrder->id_restaurant = $this->id_restaurant;
			$defaultOrder->config = $def;
			$defaultOrder->save();
		}
		
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
				$status = true;
				break;

			case 'card':
				$r = Charge::charge([
					'amount' => $this->final_price,
					'number' => $this->_number,
					'exp_month' => $this->_exp_month,
					'exp_year' => $this->_exp_year,
					'name' => $this->name,
					'address' => $this->address,
					'phone' => $this->phone,
					'user' => c::user()->id_user ? c::user() : null
				]);
				if ($r['status']) {
					$this->_txn = $r['txn'];
					$this->_user = $r['user'];
					$this->_customer = $r['customer'];
					$status = true;
				} else {
					$status = $r;
				}
				break;
		}
		return $status;
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
							$dish = new Dish($item['id']);
							if ($item['toppings']) {
								foreach ($item['toppings'] as $topping => $bleh) {
									$dish->_toppings[] = new Topping($topping);
								}
							}
							if ($item['substitutions']) {
								foreach ($item['substitutions'] as $topping => $bleh) {
									$dish->_substitution[] += new Substitution($topping);
								}
							}
							$orderItems[] = $dish;
						}		
						break;
					case 'sides':
						foreach ($typeItem as $item) {
							$orderItems[] = new Side($item['id']);
						}
						break;
					case 'extras':
						foreach ($typeItem as $item) {
							$orderItems[] = new Extra($item['id']);
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
	
	public function receipt() {
		$env = c::env() == 'live' ? 'live' : 'dev';
		//$num = ($env == 'live' ? $this->phone : c::config()->twilio->testnumber);
		$num = $this->phone;
		
		$twilio = new Twilio(c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token);
		$message = str_split($this->message('selfsms'),160);

		$type = 'textbelt';


		foreach ($message as $msg) {
			switch ($type) {
				case 'twilio':
					$twilio->account->sms_messages->create(
						c::config()->twilio->{$env}->outgoing,
						'+1'.$num,
						$msg
					);
					break;
	
				case 'textbelt':
				default:
					$cmd = 'curl http://textbelt.com/text \\'
						.'-d number='.$num.' \\'
						.'-d "message='.$msg.'"';
					exec($cmd, $return);
					print_r($return);
					break;
			}
		}
	}
	
	public function que() {
		$scripts = ['notify','receipt'];
		foreach ($scripts as $script) {
			exec('nohup '.c::config()->dirs->root.'cli/'.$script.'.php '.$this->id_order.' > /dev/null 2>&1 &');
		}
		//exec('nohup '.c::config()->dirs->root.'cli/notify.php '.$this->id_order.' &> /dev/null');
	}
	
	public function orderMessage($type) {
		switch ($type) {
			case 'sms':
			case 'web':
				$with = 'w/';
				break;

			case 'phone':
				$with = 'with';
				break;

		}
		foreach ($this->order() as $item) {
			$foodItem = "\n- ".$item->name.' ';
			if ($item->_toppings || $item->_substitutions) {
				$foodItem .= $with.' ';
			}
			if ($item->_toppings) {
				foreach ($item->_toppings as $topping) {
					$foodItem .= $topping->name.', ';
				}
				
				$foodItem = substr($foodItem, 0, -2).'. ';
			}
			if ($item->_substitutions) {
				foreach ($item->_substitutions as $topping) {
					$foodItem .= $topping->name.', ';
				}
				$foodItem = substr($foodItem, 0, -2).'. ';
			}
			
			$food .= $foodItem;
			
			if (!$item->_substitutions && !$item->_toppings) {
				$food .= '. ';
			}
		}
		return $food;
	}
	
	public function message($type) {

		$food = $this->orderMessage($type);

		switch ($type) {
			case 'selfsms':
				$msg = 'You ordered '.$this->delivery_type.' paying by '.$this->pay_type.". \n".$food."\n\n";
				if ($this->delivery_type == 'delivery') {
					$msg .= " \naddress: ".$this->address;
				}
				break;

			case 'sms':
				$msg = $this->name.' ordered '.$this->delivery_type.' paying by '.$this->pay_type.". \n".$food."\n\nphone: ".preg_replace('/[^\d.]/','',$this->phone).'.';
				if ($this->delivery_type == 'delivery') {
					$msg .= " \naddress: ".$this->address;
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
		$out['_message'] = nl2br($this->orderMessage('web'));
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