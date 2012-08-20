<?php

class Crunchbutton_Order extends Cana_Table {
	public function process($params) {
		// @todo: add more security here

		$subtotal = 0;

		foreach ($params['cart'] as $d) {
			$dish = new Order_Dish;
			$dish->id_dish = $d['id'];
			$subtotal += $dish->dish()->price;
			if ($d['options']) {
				foreach ($d['options'] as $o) {
					$option = new Order_Dish_Option;
					$option->id_option = $o;
					$total += $option->option()->price;
					$dish->_options[] = $option;
				}
			}
			
			$this->_dishes[] = $dish;
		}

		$this->price = number_format($subtotal, 2);
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
		
		$this->env = c::env();

		$user->save();
		$this->_user = $user;
		
		c::auth()->session()->id_user = $user->id_user;
		c::auth()->session()->generateAndSaveToken();
		
		$this->id_user = $this->_user->id_user;
		$this->date = date('Y-m-d H:i:s');
		$this->save();
		
		if (1==1 || c::env() == 'live') {
			$this->que();
		} else {
			$this->notify();
		}

		if ($params['make_default']) {
			$preset = $user->preset($this->restaurant()->id_restaurant);
			if ($preset->id_preset) {
				$preset->delete();
			}
		}
	
		foreach ($this->_dishes as $dish) {
			$dish->id_order = $this->id_order;
			$dish->save();

			foreach ($dish->options() as $option) {
				$option->id_order_dish = $dish->id_order_dish;
				$option->save();
			}
		}

		if ($params['make_default']) {	
			Preset::cloneFromOrder($this);
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
		return self::q('select * from `order` order by `date` DESC');
	}
	
	public function dishes() {
		if (!isset($this->_dishes)) {
			$this->_dishes = Order_Dish::q('select * from order_dish where id_order="'.$this->id_order.'"');
		}
		return $this->_dishes;
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
				case 'googlevoice':
					$gv = new GoogleVoice('cbvoice@arzynik.com', '***REMOVED***');
					$gv->sms($num, $msg);
					break;

				case 'twilio':
					$twilio->account->sms_messages->create(
						c::config()->twilio->{$env}->outgoing,
						'+1'.$num,
						$msg
					);
					break;
	
				case 'textbelt':
				default:
					$cmd = 'curl http://crunchr.co:9090/text \\'
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

		foreach ($this->dishes() as $dish) {

			$foodItem = "\n- ".$dish->dish()->name;

			if ($dish->options()->count()) {
				$foodItem .= ' '.$with.' ';

				foreach ($dish->options() as $option) {
					$foodItem .= $option->option()->name.', ';
				}
				$foodItem = substr($foodItem, 0, -2).'. ';

			} else {
				$foodItem .= '. ';
			}
			$food .= $foodItem;
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
	
	public function refund() {
		$env = c::env() == 'live' ? 'live' : 'dev';
		Stripe::setApiKey(c::config()->stripe->{$env}->secret);
		$ch = Stripe_Charge::retrieve($this->txn);
		try {
			$ch->refund();
		} catch (Exception $e) {
			return false;
		}

		$this->refunded = 1;
		$this->save();
		return true;
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('order')
			->idVar('id_order')
			->load($id);
	}
}