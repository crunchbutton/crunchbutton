<?php

class Crunchbutton_Order extends Cana_Table {
	public function process($params) {
		// @todo: add more security here
		
		$this->pay_type = $params['pay_type'] == 'cash' ? 'cash' : 'card';
		$this->delivery_type = $params['delivery_type'] == 'delivery' ? 'delivery' : 'takeout';
		$this->address = $params['address'];
		$this->phone = $params['phone'];
		$this->name = $params['name'];
		$this->notes = $params['notes'];
		
		$this->_number = $params['card']['number'];
		$this->_exp_month = $params['card']['month'];
		$this->_exp_year = $params['card']['year'];

		$subtotal = 0;

		foreach ($params['cart'] as $d) {
			$dish = new Order_Dish;
			$dish->id_dish = $d['id'];
			$subtotal += $dish->dish()->price;
			if ($d['options']) {
				foreach ($d['options'] as $o) {
					$option = new Order_Dish_Option;
					$option->id_option = $o;
					$subtotal += $option->option()->price;
//					$subtotal += $option->option()->optionPrice($d['options']);
					$dish->_options[] = $option;
				}
			}
			
			$this->_dishes[] = $dish;
		}
		
		$this->id_restaurant = $params['restaurant'];

		// price
		$this->price = number_format($subtotal, 2);
		
		// delivery fee
		$this->delivery_fee = ($this->restaurant()->delivery_fee && $this->delivery_type == 'delivery') ? $this->restaurant()->delivery_fee : 0;

		// service fee for customer
		$this->service_fee = $this->restaurant()->fee_customer;
		$serviceFee = ($this->price + $this->delivery_fee) * ($this->service_fee/100);
		$totalWithFees = $this->price + $this->delivery_fee + $serviceFee;

		// tip
		$this->tip = $params['tip'];
		$tip = ($this->price * ($this->tip/100));
		
		// tax
		$this->tax = $this->restaurant()->tax;
		$tax = $totalWithFees * ($this->tax/100);

		$this->final_price = Util::ceil($totalWithFees + $tip + $tax, 2); // price
		
		$this->order = json_encode($params['cart']);

		if (!c::user()->id_user) {
			if (!$params['name']) {
				$errors['name'] = 'Please enter a name.';
			}
			if (!$params['phone']) {
				$errors['phone'] = 'Please enter a phone #.';
			}
			if (!$params['address'] && $this->delivery_type == 'delivery') {
				$errors['address'] = 'Please enter an address.';			
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
		
		if (!$this->restaurant()->open() && c::env() == 'live') {
			$errors['closed'] = 'This restaurant is closed.';
		}

		if (!$this->restaurant()->meetDeliveryMin($this) && $this->delivery_type == 'delivery') {
			$errors['minimum'] = 'Please meet the delivery minimum of '.$this->restaurant()->delivery_min.'.';
		}

		if ($errors) {
			return $errors;
		}

		$res = $this->verifyPayment();

		if ($res !== true) {
			return $res['errors'];

		} else {
			$this->txn = $this->transaction();
		}
		
		$user = c::user()->id_user ? c::user() : new User;
		
		if (!c::user()->id_user) {
			$user->active = 1;
		}
		if ($this->_customer->id) {
			switch (c::config()->processor) {
				case 'stripe':
				default:
					$user->stripe_id = $this->_customer->id;
					break;

				case 'balanced':
					$user->balanced_id = $this->_customer->id;
					break;
			}
		}
		
		$user->location_lat = $params['lat'];
		$user->location_lon = $params['lon'];

		$user->name = $this->name;
		$user->phone = $this->phone;
		
		if ($this->delivery_type == 'delivery') {
			$user->address = $this->address;
		}

		if ($this->pay_type == 'card' && $params['card']['number']) {
			$user->card = str_repeat('*',strlen($params['card']['number'])-4).substr($params['card']['number'],-4);
			$user->card_exp_year = $params['card']['year'];
			$user->card_exp_month = $params['card']['month'];
		}
		
		$user->pay_type = $this->pay_type;
		$user->delivery_type = $this->delivery_type;
		$user->tip = $this->tip;
		
		$this->env = c::env();
		$this->processor = c::config()->processor;

		$user->save();
		$user = new User($user->id_user);
		$this->_user = $user;
		
		if ($this->_customer->id) {
			switch (c::config()->processor) {
				case 'balanced':
					$this->_customer->email_address = 'uuid-'.$user->uuid.'@_DOMAIN_';
					try {
						$this->_customer->save();
					} catch (Exception $e) {

					}
					break;
			}
		}
		
		c::auth()->session()->id_user = $user->id_user;
		c::auth()->session()->generateAndSaveToken();
		
		$this->id_user = $this->_user->id_user;
		$this->date = date('Y-m-d H:i:s');
		$this->id_community = $this->restaurant()->community()->id_community;
		$this->save();
		
		if (c::env() != 'local') {
			$this->que();
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
	
	public function accepted() {
		$nl = Notification_Log::q('select * from notification_log where id_order="'.$this->id_order.'" and status="accepted"');
		return $nl->count() ? true : false;
	}
	
	public function transaction() {
		return $this->_txn;
	}
	
	public function date() {
		if (!isset($this->_date)) {
			$this->_date = new DateTime($this->date, new DateTimeZone(c::config()->timezone));
			$this->_date->setTimezone(new DateTimeZone($this->restaurant()->timezone));
		}
		return $this->_date;
	}
	
	public function verifyPayment() {
		switch ($this->pay_type) {
			case 'cash':
				$status = true;
				break;

			case 'card':
				$user = c::user()->id_user ? c::user() : null;
				switch (c::config()->processor) {
					case 'stripe':
					default:
						$charge = new Charge_Stripe([
							'stripe_id' => $user->stripe_id
						]);
						break;

					case 'balanced':
						$charge = new Charge_Balanced([
							'balanced_id' => $user->balanced_id
						]);
						break;
				}
				$r = $charge->charge([
					'amount' => $this->final_price,
					'number' => $this->_number,
					'exp_month' => $this->_exp_month,
					'exp_year' => $this->_exp_year,
					'name' => $this->name,
					'address' => $this->address,
					'phone' => $this->phone,
					'user' => $user,
					'restaurant' => $this->restaurant()
				]);
				if ($r['status']) {
					$this->_txn = $r['txn'];
					$this->_user = $user;
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
	
	public static function find($search = []) {
		$query = '
			select `order`.* from `order`
			left join restaurant using(id_restaurant)
			where id_order is not null
		';
		if ($search['env']) {
			$query .= ' and env="'.$search['env'].'" ';
		}
		if ($search['processor']) {
			$query .= ' and processor="'.$search['processor'].'" ';
		}
		if ($search['start']) {
			$s = new DateTime($search['start']);
			$query .= ' and DATE(`date`)>="'.$s->format('Y-m-d').'" ';
		}
		if ($search['end']) {
			$s = new DateTime($search['end']);
			$query .= ' and DATE(`date`)<="'.$s->format('Y-m-d').'" ';
		}
		
		if ($search['restaurant']) {
			$query .= ' and `order`.id_restaurant="'.$search['restaurant'].'" ';
		}
		
		if ($search['order']) {
			$query .= ' and `order`.id_order="'.$search['order'].'" ';			
		}
		
		if ($search['search']) {
			$qn =  '';
			$q = '';
			$searches = explode(' ',$search['search']);
			foreach ($searches as $word) {
				if ($word{0} == '-') {
					$qn .= ' and `order`.name not like "%'.substr($word,1).'%" ';
					$qn .= ' and `order`.address not like "%'.substr($word,1).'%" ';
					$qn .= ' and `order`.phone not like "%'.substr($word,1).'%" ';
					$qn .= ' and `restaurant`.name not like "%'.substr($word,1).'%" ';
				} else {
					$q .= '
						and (`order`.name like "%'.$word.'%"
						or `order`.address like "%'.$word.'%"
						or `restaurant`.name like "%'.$word.'%"
						or `order`.phone like "%'.$word.'%")
					';
				}
			}
			$query .= $q.$qn;
		}

		$query .= '
			order by `date` DESC
		';
		
		if ($search['limit']) {
			$query .= ' limit '.$search['limit'].' ';
		}

		$orders = self::q($query);
		return $orders;
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
		return number_format(($this->price + $this->deliveryFee() + $this->serviceFee()) * ($this->tax/100),2);
	}
	
	public function deliveryFee() {
		return number_format($this->delivery_fee,2);
	}
	
	public function serviceFee() {
		return number_format(($this->price + $this->delivery_fee) * ($this->service_fee/100),2);
	}
	
	public function notify() {
		foreach ($this->restaurant()->notifications() as $n) {
			$n->send($this);
		}
	}
	
	public function confirm() {
		$env = c::env() == 'live' ? 'live' : 'dev';
		$num = ($env == 'live' ? $this->restaurant()->phone : c::config()->twilio->testnumber);

		$twilio = new Services_Twilio(c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token);
		$call = $twilio->account->calls->create(
			c::config()->twilio->{$env}->outgoingRestaurant,
			'+1'.$num,
			'http://'.$_SERVER['__HTTP_HOST'].'/api/order/'.$this->id_order.'/doconfirm',
			[
				'StatusCallback' => 'http://'.$_SERVER['__HTTP_HOST'].'/api/notification/'.$log->id_notification_log.'/callback',
				'IfMachine' => 'Hangup'
			]
		);
	}
	
	public function receipt() {
		$env = c::env() == 'live' ? 'live' : 'dev';
		//$num = ($env == 'live' ? $this->phone : c::config()->twilio->testnumber);
		$num = $this->phone;

		$twilio = new Twilio(c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token);
		$message = str_split($this->message('selfsms'),160);

		$type = 'twilio';

		foreach ($message as $msg) {
			switch ($type) {
				case 'googlevoice':
					$gv = new GoogleVoice('cbvoice@arzynik.com', base64_decode('eXVtbWllc3Q='));
					$gv->sms($num, $msg);
					break;

				case 'twilio':
					$twilio->account->sms_messages->create(
						c::config()->twilio->{$env}->outgoingTextCustomer,
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
		$order = $this;
		c::timeout(function() use($order) {
			$order->notify();
		});

		if (!$this->restaurant()->confirmation) {
			c::timeout(function() use($order) {
				$order->receipt();
			}, 30 * 1000); // 30 seconds
		}
	}
	
	public function queConfirm() {
		$order = $this;
		c::timeout(function() use($order) {
			$order->confirm();
		}, 2 * 60 * 1000, false); // 2 minites
	}
	
	public function orderMessage($type) {

		switch ($type) {
			case 'sms':
			case 'web':
				$with = 'w/';
				$space = ',';
				break;

			case 'phone':
				$with = '. ';
				$space = '.';
				break;
		}
		
		if ($type == 'phone') {
			$pFind = ['/fries/i','/BBQ/i'];
			$pReplace = ['frys','barbecue'];
		} else {
			$pFind = $pReplace = [];
		}
		
		$i = 1;
		$d = new DateTime('01-01-2000');

		foreach ($this->dishes() as $dish) {

			if ($type == 'phone') {
				$prefix = $d->format('jS').' item. ';
				$d->modify('+1 day');
			}

			$foodItem = "\n- ".$prefix.preg_replace($pFind, $pReplace, $dish->dish()->name);
			$options = $dish->options();

			if (gettype($options) == 'array') {
				$options = i::o($options);
			}

			if ($options->count()) {
				$foodItem .= ' '.$with.' ';

				foreach ($dish->options() as $option) {
					if ($option->option()->type == 'select') {
						continue;
					}
					$foodItem .= preg_replace($pFind, $pReplace, $option->option()->name).$space.' ';
				}
				$foodItem = substr($foodItem, 0, -2).'. ';

			} else {
				$foodItem .= '. ';
			}

			if ($type == 'phone') {
				$foodItem .= ']]></Say><Pause length="2" /><Say voice="'.c::config()->twilio->voice.'"><![CDATA[';
			}
			$food .= $foodItem;
		}

		return $food;
	}
	
	public function streetName() {
		$name = explode("\n",$this->address);
		$name = preg_replace('/^[0-9]+ (.*)$/i','\\1',$name[0]);
		$spaceName = '';

		for ($x=0; $x<strlen($name); $x++) {
			$letter = strtolower($name{$x});
			switch ($letter) {
				case ' ':
				case '.':
				case ',':
				case "\n":
					$addPause = true;
					break;
				case 'c':
					$letter = 'see.';
				default:
					if ($addPause) {
						$spaceName .= '<Pause length="1" />';	
					}
					$spaceName .= '<Say voice="'.c::config()->twilio->voice.'"><![CDATA['.$letter.']]></Say><Pause length="1" />';
					$addPause = false;
					break;
			}

		}
		return $spaceName;
	}
	
	public function phoneticStreet($st) {
		$pFind = ['/(st\.)|( st($|\n))/i','/(ct\.)|( ct($|\n))/i','/(ave\.)|( ave($|\n))/i'];
		$pReplace = [' street. ',' court. ',' avenue. '];
		$st = preg_replace($pFind,$pReplace,$st);
		return $st;
	}
	
	public function message($type) {

		$food = $this->orderMessage($type);

		switch ($type) {
			case 'selfsms':
				$msg = "Crunchbutton #".$this->id_order."\n\n";
				if ($this->delivery_type == 'delivery') {
					$msg .= "Your delivery should arrive within ".($this->restaurant()->delivery_estimated_time ? $this->restaurant()->delivery_estimated_time : 60)." minutes.\n";
				} else {
					$msg .= "Your order will be ready within ".($this->restaurant()->pickup_estimated_time ? $this->restaurant()->pickup_estimated_time : ($this->restaurant()->delivery_estimated_time ? $this->restaurant()->delivery_estimated_time : 60))." minutes.\n";				
				}
				$msg .= "To contact ".$this->restaurant()->shortName().", call ".$this->restaurant()->phone().".\n\n";
				$msg .= 'Total: $'.$this->final_price.'';
				break;

			case 'sms':
				$msg = "Crunchbutton #".$this->id_order."\n\n";
				$msg .= $this->name.' ordered '.$this->delivery_type.' paying by '.$this->pay_type.". \n".$food."\n\nphone: ".preg_replace('/[^\d.]/','',$this->phone).'.';
				if ($this->delivery_type == 'delivery') {
					$msg .= " \naddress: ".$this->address;
				}
				if ($this->notes) {
					$msg .= " \nNOTES: ".$this->notes;
				}
				if ($this->pay_type == 'card' && $this->tip) {
					$msg .= " \nTIP: $".$this->tip();
				}
				break;

			case 'phone':
				$spacedPhone = preg_replace('/[^\d.]/','',$this->phone);
				for ($x=0; $x<strlen($spacedPhone); $x++) {
					$spacedPhones .= $spacedPhone{$x}.'. ';
				}
				$msg = 
						'Customer Phone number. '.$spacedPhones.'.'
						.'</Say><Pause length="1" /><Say voice="'.c::config()->twilio->voice.'"><![CDATA[Customer Name. '.$this->name.'.]]></Say><Pause length="1" /><Say>';

				if ($this->delivery_type == 'delivery') {
					$msg .= '</Say><Pause length="1" /><Say voice="'.c::config()->twilio->voice.'"><![CDATA[Deliver to '.$this->phoneticStreet($this->address).'.]]>';
				} else {
					$msg .= '</Say><Pause length="1" /><Say voice="'.c::config()->twilio->voice.'">This order is for pickup. ';
				}
				
				if ($this->pay_type == 'card') {
					$msg .= '</Say><Pause length="1" /><Say voice="'.c::config()->twilio->voice.'">This order has been prepaid by credit card.';
				} else {
					$msg .= '</Say><Pause length="1" /><Say voice="'.c::config()->twilio->voice.'">The customer will pay for this order with cash.';				
				}

				$msg .= '</Say><Pause length="2" /><Say voice="'.c::config()->twilio->voice.'"><![CDATA['.$food.'.]]>';

				if ($this->notes) {
					$msg .= '</Say><Pause length="1" /><Say voice="'.c::config()->twilio->voice.'"><![CDATA[Customer Notes. '.$this->notes.'.]]>';
				}
				
				$msg .= '</Say><Pause length="1" /><Say voice="'.c::config()->twilio->voice.'">Order total: '.$this->phoeneticNumber($this->final_price);

				if ($this->pay_type == 'card' && $this->tip) {
					$msg .= '</Say><Pause length="1" /><Say voice="'.c::config()->twilio->voice.'">A tip of '.$this->phoeneticNumber($this->tip()).' has been charged to the customer\'s credit card.';
				} else {
					$msg .= '</Say><Pause length="1" /><Say voice="'.c::config()->twilio->voice.'">The customer will be paying the tip by cash.';				
				}

				break;
		}

		return $msg;
	}
	
	public function phoeneticNumber($num) {
		$num = explode('.',$num);
		return $num[0].' dollar'.($num[0] == 1 ? '' : 's').' and '.$num[1].' cent'.($num[1] == '1' ? '' : 's');
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
		switch ($this->processor) {
			case 'stripe':
			default:
				Stripe::setApiKey(c::config()->stripe->{$env}->secret);
				$ch = Stripe_Charge::retrieve($this->txn);
				try {
					$ch->refund();
				} catch (Exception $e) {
					return false;
				}
			break;

			case 'balanced':
				$ch = Crunchbutton_Balanced_Debit::byId($this->txn);
				try {
					$ch->refund();
				} catch (Exception $e) {
					return false;
				}
				break;
		}

		$this->refunded = 1;
		$this->save();
		return true;
	}
	
	public function phone() {
		$phone = $this->phone;
		$phone = preg_replace('/[^\d]*/i','',$phone);
		$phone = preg_replace('/(\d{3})(\d{3})(.*)/', '\\1-\\2-\\3', $phone);
		
		return $phone;
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('order')
			->idVar('id_order')
			->load($id);
	}
}