<?php

/**
 * Restaurant model
 *
 * @package  Crunchbutton.Restaurant
 * @category model
 *
 * @property int id_restaurant
 */
class Crunchbutton_Restaurant extends Cana_Table
{

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('restaurant')
			->idVar('id_restaurant')
			->load($id);
	}

	public static function permalink($permalink) {
		return self::q('select * from restaurant where permalink="'.$permalink.'"')->get(0);
	}

	public function meetDeliveryMin($order) {
		if (!$this->delivery_min) {
			return true;
		}
		$price = $this->delivery_min_amt == 'subtotal' ? $order->price : $order->final_price;
		return $price < $this->delivery_min ? false : true;
	}

	public function top() {
		if (!isset($this->_top)) {
			foreach ($this->dishes() as $dish) {
				if ($dish->top) {
					$this->_top = $dish;
					break;
				}
			}
		}
		return $this->_top;
	}

	/**
	 * Return the dishes for the restaurant
	 *
	 * Save actions should fetch all by addint active=null in the $where param
	 *
	 * @param string[] $where Associative array with the filters to use to fetch the dishes
	 *
	 * @todo Why is the restaurant calling the dishes directly instead of using the categoyr->dishes() method?
	 */
	public function dishes($where = []) {
		if (!isset($this->_dishes)) {
			$defaultFilters = [
				'id_restaurant' => $this->id_restaurant,
				'active'        => 1,
			];
			$where = $this->_mergeWhere($defaultFilters, $where);
			$sql   = "SELECT * FROM dish WHERE $where";
			$this->_dishes = Dish::q($sql, $this->db());
		}
		return $this->_dishes;
	}

	/**
	 * Returns the categories for this restaurant order as their sort field
	 *
	 * @return Crunchbutton_Category[]
	 */
	public function categories() {
		if (!isset($this->_categories)) {
			$sql               = "SELECT * FROM category WHERE id_restaurant={$this->id_restaurant} ORDER BY sort ASC";
			$this->_categories = Crunchbutton_Category::q($sql);
		}
		return $this->_categories;
	}

	/**
	 * Returns... the same as communities()?
	 *
	 * @todo Shouldn't this return only the first community? I mean...
	 *
	 * @return Cana_Iterator
	 */
	public function community() {
		$communities = $this->communities();
		return $communities;
	}

	public function communities() {
		if (!isset($this->_communities)) {
			$this->_communities = Community::q('select community.* from community left join restaurant_community using(id_community) where id_restaurant="'.$this->id_restaurant.'"');
		}
		return $this->_communities;
	}

	public function phone() {
		$phone = $this->phone;
		$phone = preg_replace('/[^\d]*/i','',$phone);
		$phone = preg_replace('/(\d{3})(\d{3})(.*)/', '\\1-\\2-\\3', $phone);

		return $phone;
	}

	public function shortName() {
		return $this->short_name ? $this->short_name : $this->name;
	}

	public function _hasOption($option, $options) {
		foreach ($options as $o) {
			if ($o->id_option == $option->id_option) {
				return $o->id_dish_option;
			}
		}
		return false;
	}

	public function payments() {
		return Payment::q('select * from payment where env="'.c::env().'" and id_restaurant="'.$this->id_restaurant.'" order by date desc');
	}

	public function createMerchant($params = []) {

		$type = $params['type'] == 'business' ? 'business' : 'person';

		try {
			$p = [
				'type' => $type,
				'name' => $params['name'] ? $params['name'] : $this->name,
				'phone_number' => $this->phone,
				'country_code' => 'USA',
				'street_address' => $params['address'] ? $params['address'] : $this->address,
				'postal_code' => $params['zip'] ? $params['zip'] : $this->zip
			];
			switch ($type) {
				case 'person':
					$p['dob'] = $params['dob'];
					break;
				case 'business':
					$p['tax_id'] = $params['taxid'];
					$p['person'] = $params['person'];
					break;
			}

			$merchant = c::balanced()->createMerchant(
				'restaurant-'.$this->id_restaurant.'@_DOMAIN_',
				$p,
				null,
				null,
				$this->name
			);
		} catch (Balanced\Exceptions\HTTPError $e) {
			print_r($e);
			exit;
		}

		$this->balanced_id = $merchant->id;
		$this->save();

		return $merchant;

	}

	public function merchant() {

		if ($this->balanced_id) {
			$a = Crunchbutton_Balanced_Merchant::byId($this->balanced_id);
			if ($a->id) {
				$merchant = $a;
			}
		}

		if (!$merchant) {
			$a = Crunchbutton_Balanced_Merchant::byRestaurant($this);
			if ($a->id) {
				if (c::env() == 'live') {
					$this->balanced_id = $a->id;
					$this->save();
				}
				$merchant = $a;
			}
		}

		if (!$merchant) {
			die('no merchant');
			$merchant = $this->createMerchant();
		}

		return $merchant;
	}

	public function saveBankInfo($name, $account, $routing) {
		try {
			$bank = c::balanced()->createBankAccount($name, $account, $routing);
			$info = $this->merchant()->addBankAccount($bank);

			$this->balanced_bank = $bank->id;
			$this->save();
		} catch (Exception $e) {
			print_r($e);
			exit;
		}
	}

	/**
	 * Stores the Dish Categories for this restaurant
	 *
	 * @param array $rawData array with the JS restaurants
	 */
	public function saveCategories($rawData)
	{
		$originalCategories = $this->categories();
		$doNotDelete        = [];
		if ($rawData) {
			foreach ($rawData as $data) {
				// if (!$data['name']) continue;
				$element                = new Crunchbutton_Category($data['id_category']);
				$element->id_restaurant = $this->id_restaurant;
				$element->name          = $data['name'];
				$element->sort          = $data['sort'];
				$element->save();
				$doNotDelete[]          = $element->id_category;
			}
		}
		foreach($originalCategories as $toDelete) {
			/* @var $toDelete Crunchbutton_Category */
			if (!in_array($toDelete->id_category, $doNotDelete)) {
				$toDelete->delete();
			}
		}

		$this->_categories = null;
		$elements = $this->categories();
		return $elements;
	}

	/**
	 * Stores the dishes and it's options
	 *
	 * @return void
	 */
	public function saveDishes($newDishes) {
		foreach ($this->categories() as $cat) {
			if (!$category) {
				$category = $cat;
			}
		}

		if (!$category) {
			$category = new Category;
			$category->id_restaurant = $this->id_restaurant;
			$category->name = 'Most Popular';
			$category->sort = 1;
			$category->loc = 1;
			$category->save();
		}

		// fetch all (active and inactive dishes) before any changes
		$originalDishes = $this->dishes(['active' => null]);
	
			Log::debug([
				'newDishes' => $newDishes,
				'type' => 'dishes'
			]);
	

		if ($newDishes) {
			foreach ($newDishes as $dish) {
				$dishO                = new Dish($dish['id_dish']);
				$dishO->id_restaurant = $this->id_restaurant;
				$dishO->active        = $this->_jsonBoolean($dish, 'active', true);
				$dishO->name          = $dish['name'];
				$dishO->description   = $dish['description'];
				$dishO->price         = $dish['price'];
				$dishO->sort          = isset($dish['sort']) ? $dish['sort'] : 0;
				if (isset($dish['id_category']) && $dish['id_category']) {
					$dishO->id_category = $dish['id_category'];
				} elseif (!$dishO->id_category) { // this else doesn't make sense to me, but it is what it was before my changes
					$dishO->id_category = $category->id_category;
				}

				$dishO->save();

				$options = $dishO->options();

				$newOptions = [];

				if ($dish['optionGroups']) {
					foreach ($dish['optionGroups'] as $optionGroup) {
						if ($optionGroup['id_option'] == 'BASIC') {
							$parent = null;

						} else {

							$group = new Option($optionGroup['id_option']);
							$group->name = $optionGroup['name'];
							$group->price_linked = $optionGroup['price'];
							$group->type = $optionGroup['type'];
							$group->id_restaurant = $this->id_restaurant;
							$group->save();
							$parent = $group->id_option;
							$newOptions[$group->id_option] = $group->id_option;

							if (!$doid = $this->_hasOption($group, $options)) {
								$do = new Dish_Option;
								$do->id_dish = $dishO->id_dish;
								$do->id_option = $group->id_option;
								$do->save();
							} else {
								$do = new Dish_Option($doid);
								$do->default = $opt->default;
							}
						}

						$_debug_opts = array();

						if ($optionGroup['options']) {
							foreach ($optionGroup['options'] as $opt) {

								$_debug_opts[] = $opt;

								$option                   = new Option($opt['id_option']);
								$option->id_restaurant    = $this->id_restaurant;
								$option->id_option_parent = $parent;
								$option->price            = $opt['price'];
								$option->name             = $opt['name'];
								$option->active           = 1;
								$option->type             = 'check';
								$option->save();
								$newOptions[$option->id_option] = $option->id_option;
								$opt['default'] = $opt['default'] == 'true' ? 1 : 0;

								if (!$doid = $this->_hasOption($option, $options)) {
									$do            = new Dish_Option;
									$do->id_dish   = $dishO->id_dish;
									$do->id_option = $option->id_option;
									$do->default   = $opt['default'];
								} else {
									$do = new Dish_Option($doid);
									if ($opt['default'] != $do->default) {
										$do->default = $opt['default'];
									}
								}
								$do->sort    = $opt['sort'];
								$do->save();
							}
						}

						// Log::debug([
						// 	'id_dish' => $dish['id_dish'],
						// 	'id_option' => $optionGroup['id_option'],
						// 	'options' => $_debug_opts,
						// 	'type' => 'options-dishes'
						// ]);

					}
				}

				$removed = array();

				foreach ($options as $option) {
					if (!in_array($option->id_option, $newOptions)) {
						$do = new Dish_Option($option->id_dish_option);
						$removed[] = $option->id_dish_option;
						$do->delete();
					}
				}


				// Log::debug([
				// 	'id_dish' => $dish['id_dish'],
				// 	'id_option' => $optionGroup['id_option'],
				// 	'removed' => $removed,
				// 	'newOptions' => $newOptions,
				// 	'type' => 'options-dishes-removed'
				// ]);


			}
		}

		$nd = [];
		if ($newDishes) {
			foreach ($newDishes as $dish) {
				$nd[$dish['id_dish']] = $dish['id_dish'];
			}
		}

		foreach ($originalDishes as $dish) {
			if (!in_array($dish->id_dish, $nd)) {
				$d = new Dish($dish->id_dish);
				$d->delete();
			}
		}
	}

/**
	 * Stores the dish and it's options
	 *
	 * @return void
	 */
	public function saveDish($dish) {
		foreach ($this->categories() as $cat) {
			if (!$category) {
				$category = $cat;
			}
		}

		if (!$category) {
			$category = new Category;
			$category->id_restaurant = $this->id_restaurant;
			$category->name = 'Most Popular';
			$category->sort = 1;
			$category->loc = 1;
			$category->save();
		}

		$dishO                = new Dish($dish['id_dish']);
		$dishO->id_restaurant = $this->id_restaurant;
		$dishO->active        = $this->_jsonBoolean($dish, 'active', true);
		$dishO->name          = $dish['name'];
		$dishO->description   = $dish['description'];
		$dishO->price         = $dish['price'];
		$dishO->sort          = isset($dish['sort']) ? $dish['sort'] : 0;
		if (isset($dish['id_category']) && $dish['id_category']) {
			$dishO->id_category = $dish['id_category'];
		} elseif (!$dishO->id_category) { // this else doesn't make sense to me, but it is what it was before my changes
			$dishO->id_category = $category->id_category;
		}

		$dishO->save();

		$options = $dishO->options();

		$newOptions = [];

		if ($dish['optionGroups']) {
			foreach ($dish['optionGroups'] as $optionGroup) {
				if ($optionGroup['id_option'] == 'BASIC') {
					$parent = null;

				} else {

					$group = new Option($optionGroup['id_option']);
					$group->name = $optionGroup['name'];
					$group->price_linked = $optionGroup['price'];
					$group->type = $optionGroup['type'];
					$group->id_restaurant = $this->id_restaurant;
					$group->save();
					$parent = $group->id_option;
					$newOptions[$group->id_option] = $group->id_option;

					if (!$doid = $this->_hasOption($group, $options)) {
						$do = new Dish_Option;
						$do->id_dish = $dishO->id_dish;
						$do->id_option = $group->id_option;
						$do->save();
					} else {
						$do = new Dish_Option($doid);
						$do->default = $opt->default;
					}
				}

				if ($optionGroup['options']) {
					foreach ($optionGroup['options'] as $opt) {

						$option                   = new Option($opt['id_option']);
						$option->id_restaurant    = $this->id_restaurant;
						$option->id_option_parent = $parent;
						$option->price            = $opt['price'];
						$option->name             = $opt['name'];
						$option->active           = 1;
						$option->type             = 'check';
						$option->save();
						$newOptions[$option->id_option] = $option->id_option;
						$opt['default'] = $opt['default'] == 'true' ? 1 : 0;

						if (!$doid = $this->_hasOption($option, $options)) {
							$do            = new Dish_Option;
							$do->id_dish   = $dishO->id_dish;
							$do->id_option = $option->id_option;
							$do->default   = $opt['default'];
						} else {
							$do = new Dish_Option($doid);
							if ($opt['default'] != $do->default) {
								$do->default = $opt['default'];
							}
						}
						$do->sort    = $opt['sort'];
						$do->save();
					}
				}
			}
		}

		$removed = array();
		foreach ($options as $option) {
			if (!in_array($option->id_option, $newOptions)) {
				$do = new Dish_Option($option->id_dish_option);
				$removed[] = $option->id_dish_option;
				$do->delete();
			}
		}
		return true;
	}

	public function deleteDish( $id_dish ){
		$dish = new Dish( $id_dish );
		if( $dish->id_dish ){
			$dish->delete();
			return true;
		}
		return false;
	}

	public function deleteCategory( $id_category ){
		$category = new Category( $id_category );
		if( $category->id_dish ){
			$category->delete();
			return true;
		}
		return false;
	}
	

	/**
	 * Save the notifications as they are send by the API
	 *
	 * @param array $elements
	 */
	public function saveNotifications($elements) {
		c::db()->query('DELETE FROM notification WHERE id_restaurant="'.$this->id_restaurant.'"');
		if(!$elements)
			return;
		foreach ($elements as $data) {
			if (!$data['value']) continue;
			$element                = new Crunchbutton_Notification($data['id_notification']);
			$element->id_restaurant = $this->id_restaurant;
			$element->active        = ($data['active'] == 'true' || $data['active'] == '1') ? "1" : "0";
			$element->type          = $data['type'];
			$element->value         = $data['value'];
			$element->save();
		}

		$this->_notifications = null;
		$where           = [];
		$where['active'] = NULL;
		$elements = $this->notifications($where);
		return $elements;
	}

	/**
	 * Saves the hours the restaurant uses for delivery
	 *
	 * @return void
	 */
	public function saveHours($hours) {
		c::db()->query('delete from hour where id_restaurant="'.$this->id_restaurant.'"');
		if ($hours) {
			foreach ($hours as $day => $times) {
				foreach ($times as $time) {
					$hour = new Hour;
					$hour->id_restaurant = $this->id_restaurant;
					$hour->day = $day;
					$hour->time_open = $time[0];
					$hour->time_close = $time[1];
					$hour->save();
				}
			}
		}
		unset($this->_hours);
		$this->hours();
	}

	public function hours($gmt = false) {
		$gmt = $gmt ? '1' : '0';
		if (!isset($this->_hours[$gmt])) {
			$hours = Hour::q('select * from hour where id_restaurant="'.$this->id_restaurant.'"');
			if ($gmt) {
				$timezone = new DateTime('now ', new DateTimeZone($this->timezone));
				$timezone = $timezone->format('O');

				foreach ($hours as $hour) {
					$open = new DateTime('next '.$hour->day. ' ' .$hour->time_open, new DateTimeZone($this->timezone));
					$open->setTimezone(new DateTimeZone('GMT'));
					$close = new DateTime('next '.$hour->day. ' ' .$hour->time_close, new DateTimeZone($this->timezone));
					$close->setTimezone(new DateTimeZone('GMT'));
					$hour->time_open = $open->format('Y-m-d H:i');
					$hour->time_close = $close->format('Y-m-d H:i');
				}
			}
			$this->_hours[$gmt] = $hours;
		}
		return $this->_hours[$gmt];
	}

	/**
	 * Confirms a restaurant is open
	 *
	 * Uses TimeMachine to test if the restaurant is open forcing time travel
	 *
	 * @link /api/TimeMachine/set?time=12:30am
	 * @link /api/TimeMachine/reset
	 */
	public function open() {

		if (c::env() != 'live' && ($this->id_restaurant == 1 || $this->id_restaurant == 18)) {
			// return true;
		}

		$hours = $this->hours();
		$DeLorean = new TimeMachine($this->timezone);
		$today    = $DeLorean->now();
		$day      = strtolower($today->format('D'));

		$hasHours = false;

		foreach ($hours as $hour) {

			$hasHours = true;

			if ($hour->day != $day) {
				continue;
			}

			$open  = new DateTime('today '.$hour->time_open,  new DateTimeZone($this->timezone));
			$close = new DateTime('today '.$hour->time_close, new DateTimeZone($this->timezone));

			// if closeTime before openTime, then closeTime should be for tomorrow
			if ($close->getTimestamp() < $open->getTimestamp()) {
				$close = new DateTime('+1 day '.$hour->time_close, new DateTimeZone($this->timezone));
			}


			if ($today->getTimestamp() >= $open->getTimestamp() && $today->getTimestamp() <= $close->getTimestamp()) {
				return true;
			}
		}

		if( !$hasHours ){
			return true;
		}

		return false;
	}

	/**
	 * Returns all the notifications linked to this restaurant
	 *
	 * @param array $where asociative values to filter the where
	 *
	 * @todo $where only handles AND keys, where engine should probably be stored in the Cana_Table class
	 *
	 * @return Cana_Iterator
	 */
	public function notifications($where = []) {
		$defaultFilters = [
			'id_restaurant' => $this->id_restaurant,
			'active'        => 1,
		];
		$whereSql = $this->_mergeWhere($defaultFilters, $where);
		if (!isset($this->_notifications)) {
			$this->_notifications = Crunchbutton_Notification::q("SELECT * FROM notification WHERE $whereSql");
		}
		return $this->_notifications;
	}

	public function preset() {
		return Preset::q('
			select * from preset where id_restaurant="'.$this->id_restaurant.'"
			and id_user is null
		');
	}

	public function cachePath() {
		switch (c::env()) {
			case 'local':
				$path = c::config()->dirs->cache.'thumb/';
				break;
			default:
				$path = '/home/i.crunchbutton/www/cache/';
				break;

		}
		return $path;
	}

	public function imagePath() {
		switch (c::env()) {
			case 'local':
				$path = c::config()->dirs->www.'assets/images/food/';
				break;
			default:
				$path = '/home/i.crunchbutton/www/image/';
				break;

		}
		return $path;
	}

	public function publicImagePath() {
		switch (c::env()) {
			case 'local':
				$path = '/cache/images-local/';
				break;
			default:
				$path = '/cache/images/';
				break;

		}
		return $path;
	}

	public function thumb($params = []) {
		$params['height'] = 596; //310 *2;
		$params['width'] = 596; //310 *2;
		$params['crop'] = 0;
		$params['gravity'] = 'center';
		$params['format'] = 'jpg';
		$params['quality'] = '70';

		$params['img']			= $this->image;
		$params['cache'] 		= $this->cachePath();
		$params['path'] 		= $this->imagePath();

		try {
			$thumb = new Cana_Thumb($params);
		} catch (Exception $e) {
			return null;
		}
		return $thumb;

	}

	/**
	 * Restaurant's email address, if any
	 *
	 * @return string|null
	 */
	public function email()
	{
		$email = null;
		if ($this->email) {
			$email =  $this->email;
		} else {
			foreach ($this->notifications() as $notification) {
				if ($notification->type == Crunchbutton_Notification::TYPE_EMAIL) {
					$email = $notification->value;
				}
			}
		}
		return $email;
	}

	public function facebook($params = []) {
		$params['height'] = 400; //310 *2;
		$params['width'] = 400; //310 *2;
		$params['crop'] = 1;
		$params['gravity'] = 'center';
		$params['format'] = 'jpg';
		$params['quality'] = '70';

		$params['img']			= $this->image;
		$params['cache'] 		= $this->cachePath();
		$params['path'] 		= $this->imagePath();

		try {
			$thumb = new Cana_Thumb($params);
		} catch (Exception $e) {
			return null;
		}
		return $thumb;
	}

	/**
	 * Restaurant's fax number, if any
	 *
	 * @return string|null
	 */
	public function fax()
	{
		$fax = null;
		foreach ($this->notifications() as $notification) {
			if ($notification->type == Crunchbutton_Notification::TYPE_FAX) {
				$fax = $notification->value;
			}
		}
		return $fax;
	}

	public function image($params = []) {
		$params['height'] = 280;
		$params['width'] = 630;
		$params['crop'] = 1;
		$params['gravity'] = 'center';
		$params['format'] = 'jpg';
		$params['quality'] = '70';

		$params['img']			= $this->image;
		$params['cache'] 		= $this->cachePath();
		$params['path'] 		= $this->imagePath();

		try {
			$thumb = new Cana_Thumb($params);
		} catch (Exception $e) {
			return null;
		}
		return $thumb;

	}

	public function weight() {
		if (!isset($this->_weight)) {
			$res = self::q('
				select count(*) as `weight`, `restaurant`.name from `order`
				left join `restaurant` using(id_restaurant)
				where id_restaurant='.$this->id_restaurant.'
			');
			$this->_weight = $res->weight;
		}
		return $this->_weight;
	}

	/**
	 * Returns an array with all the information for a Restaurant.
	 *
	 * This is usualy used to JSON encode and send to the browser
	 *
	 * @param array $ignore An indexed array of what items not to ad to the export array
	 * @param array $where  Adds a layer to filter the SQL WHERE statements
	 *
	 * @return array
	 */
	public function exports($ignore = [], $where = []) {
		$out              = $this->properties();
		$out['_open']     = $this->open();
		$out['_weight']    = $this->weight();

		$timezone = new DateTimeZone( $this->timezone );
		$date = new DateTime( 'now ', $timezone ) ;

		// Return the offset to help the Javascript to calculate the open/close hour correctly
		$out['_tzoffset'] = ( $date->getOffset() ) / 60 / 60;
		$out['_tzabbr'] = $date->format('T');

		// $out['img']    = '/assets/images/food/630x280/'.$this->image.'?crop=1';
		$out['img']       = $this->publicImagePath().($this->image() ? $this->image()->getFileName() : '');
		$out['img64']     = $this->publicImagePath().($this->thumb() ? $this->thumb()->getFileName() : '');
		// $out['img64']  = (new ImageBase64($this->thumb()))->output();
		// $out['img64']  = '/assets/images/food/310x310/'.$this->image;

		if (!$ignore['categories']) {
			foreach ($this->categories() as $category) {
				$out['_categories'][] = $category->exports($where);
			}
		}

		if (!$ignore['notifications']) {
			$where = [];
			if (isset($_SESSION['admin'])) {
				$where['active'] = NULL;
			}
			foreach ($this->notifications($where) as $notification) {
				/* @var $notification Crunchbutton_Notification */
				$out['_notifications'][$notification->id_notification] = $notification->exports();
			}
		}

		foreach ($this->hours(true) as $hours) {
			$out['_hoursFormat'][$hours->day][] = [$hours->time_open, $hours->time_close];
		}
		foreach ($this->hours() as $hours) {
			$out['_hours'][$hours->day][] = [$hours->time_open, $hours->time_close];
		}
		if ($this->preset()->count()) {
			$out['_preset'] = $this->preset()->get(0)->exports();
		}

		$out['id_community'] = $this->community()->id_community;
		return $out;
	}

	/**
	 * Imports an array with all the information for a Restaurant.
	 *
	 * Should be an exact inverse of exports()
	 * for starters, it's an approximation
	 *
	 * @return null
	 */
	public function imports($restaurant) {

		foreach($this->properties() as $key=>$val) {
			if(in_array($key, array_keys($restaurant))) {
				$this->$key = $restaurant[$key];
			}
		}
		$this->saveHours($restaurant['_hours']);
		$this->saveNotifications($restaurant['_notifications']);
		$this->saveCategories($restaurant['_categories']);

		// dishes with options are the awful part
		$all_dishes = [];
		if(!array_key_exists('_categories', $restaurant)) {
			$restaurant['_categories'] = [];
		}
		foreach($restaurant['_categories'] as $category) {
			if(!array_key_exists('_dishes', $category)) {
				$category['_dishes'] = [];
			}
			foreach($category['_dishes'] as &$dish) {
				$dish['optionGroups'] = [];
				if(!intval($dish['id_category'])) {
					$sql = 'SELECT * FROM category WHERE name like \''.$category['name'].'\' ORDER BY sort ASC LIMIT 1';
					$category = Crunchbutton_Category::q($sql);
					$dish['id_category'] = $category->id_category;
				}
				if(!array_key_exists('_options', $dish)) {
					$dish['_options'] = [];
				}
				$basicOptionsIds    = [];
				$optionGroupsIds    = [];
				$optionsInGroupsIds = [];
				$optionGroups = [];
				foreach($dish['_options'] as $option) {
					if($option['id_option_parent']) {
						$optionGroupsIds[] = $option['id_option_parent'];
						$optionsInGroupsIds[] = $option['id_option'];
					}
				}
				foreach($dish['_options'] as $option) {
					if(in_array($option['id_option'], $optionGroupsIds)) continue;
					if(in_array($option['id_option'], $optionsInGroupsIds)) continue;
					$option['id_option_parent'] = 'BASIC';
					$basicOptionsIds[] = $option['id_option'];
				}
				$optionGroupsIds[] = 'BASIC';
				$optionGroups['BASIC'] = array('id_option'=>'BASIC');

				// option groups
				foreach($dish['_options'] as $option) {
					if(in_array($option['id_option'], $optionGroupsIds)) {
						$optionGroups[$option['id_option']] = $option;
						$optionGroups[$option['id_option']]['options'] = [];
					}
				}
				// regular options
				foreach($dish['_options'] as $option) {
					if(!in_array($option['id_option'], $optionGroupsIds)) {
						$optionGroups[$option['id_option_parent']]['options'][] = $option;
					}
				}
				$dish['optionGroups'] = $optionGroups;
				$all_dishes[] = $dish;
			}
		}
		$this->saveDishes($all_dishes);
		return null;
	}

	public function priceRange() {
		if (!isset($this->_priceRange)) {
			$price = 0;
			foreach ($this->dishes() as $dish) {
				$price += $dish->price;
			}
			$price = $price / $this->dishes()->count();

			if ($price > 60) {
				$this->_priceRange = '$$$$';
			} elseif ($price > 30) {
				$this->_priceRange = '$$$';
			} elseif ($price > 10) {
				$this->_priceRange = '$$';
			} else {
				$this->_priceRange = '$';
			}
		}
		return $this->_priceRange;
	}

	public function ratingCount() {
		if (!isset($this->_ratingCount)) {
			$this->_ratingCount = Order::q('select count(*) as c from `order` where id_restaurant="'.$this->id_restaurant.'" and env="live"')->c;
		}
		return $this->_ratingCount;
	}

	public static function byRange($params) {
		$params['range'] = $params['range'] ? $params['range'] : 2;
		$rangeDif = $params['range']-2;

		$query = '
			SELECT
				count(*) as _weight,
				((ACOS(SIN('.$params['lat'].' * PI() / 180) * SIN(loc_lat * PI() / 180) + COS('.$params['lat'].' * PI() / 180) * COS(loc_lat * PI() / 180) * COS(('.$params['lon'].' - loc_long) * PI() / 180)) * 180 / PI()) * 60 * 1.1515) AS `distance`,
				restaurant.*
			FROM `restaurant`
			LEFT JOIN `order` using(id_restaurant)
			WHERE
				active = 1
			GROUP BY restaurant.id_restaurant
			HAVING
					takeout = 1
				AND
					delivery = 0
				AND
					`distance` <= '.$params['range'].'
				OR
					delivery = 1
				AND
					`distance` <= (`delivery_radius`+'.$rangeDif.')
			ORDER BY _weight DESC;
		';
		$restaurants = self::q($query);
		foreach ($restaurants as $restaurant) {
			$sum += $restaurant->_weight;
		}
		foreach ($restaurants as $restaurant) {
			$restaurant->_weight = (($restaurant->_weight / $sum) * 100) + $restaurant->weight_adj;
		}

		return $restaurants;
	}

	public function hasFaxNotification(){
		foreach ( $this->notifications() as $notification ){
			if( $notification->type == 'fax' ){
				return true;
			}
		}	
		return false;	
	}

	public function save() {
		if (!$this->timezone) {
			$this->timezone = 'America/New_York';
		}
		parent::save();
	}
}
