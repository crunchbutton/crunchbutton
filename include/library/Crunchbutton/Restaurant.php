<?php

/**
 * Restaurant model
 *
 * @package  Crunchbutton.Restaurant
 * @category model
 *
 * @property int id_restaurant
 */
class Crunchbutton_Restaurant extends Cana_Table_Trackchange {

	public $__dt = null;

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
		if( $order->final_price_plus_delivery_markup && $order->price_plus_delivery_markup ){
			$price = $this->delivery_min_amt == 'subtotal' ? $order->price_plus_delivery_markup : $order->final_price_plus_delivery_markup;	
		} else {
			$price = $this->delivery_min_amt == 'subtotal' ? $order->price : $order->final_price;	
		}
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

	public function communityNames(){
		$names = [];
		$communities = $this->communities();
		foreach( $communities as $community ){
			$names[] = $community->name;
		}
		return join( ', ', $names );
	}

	public function communities() {
		if (!isset($this->_communities)) {
			$this->_communities = Community::q('select community.* from community left join restaurant_community using(id_community) where id_restaurant="'.$this->id_restaurant.'" ORDER BY community.name ASC');
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
		$payment = $this->payment_type();
		$payment->id_restaurant = $this->id_restaurant;
		$payment->balanced_id = $merchant->id;
		$payment->save();

		return $merchant;

	}

	public function merchant() {

		$payment_type = $this->payment_type(); 

		if ($payment_type->balanced_id) {
			$a = Crunchbutton_Balanced_Merchant::byId($payment_type->balanced_id);
			if ($a->id) {
				$merchant = $a;
			}
		}

		if (!$merchant) {
			$a = Crunchbutton_Balanced_Merchant::byRestaurant($this);
			if ($a->id) {
				if (c::env() == 'live') {
					$payment = $r->payment_type();
					$payment->id_restaurant = $r->id_restaurant;
					$payment->balanced_id = $a->id;
					$payment->save();
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

	public function saveStripeBankAccount( $bank_account ){
		$payment_type = $this->payment_type(); 
		try{
			Stripe::setApiKey(c::config()->stripe->{c::getEnv()}->secret);
			if( $payment_type->stripe_id ){
				$recipient = $payment_type->getRecipientInfo();
				$recipient->bank_account = $bank_account;
				$recipient->save();
				$payment_type->stripe_account_id = $bank_account;
				$payment_type->save();	
				return true;
			} 
		} catch (Exception $e) {
			print_r($e);
			exit;
		}
		return false;
	}

	public function saveStripeRecipient( $name, $type, $tax_id ){
		
		$payment_type = $this->payment_type(); 

		try{
		
			$tax_id = ( $tax_id == '' ) ? NULL : $tax_id;

			Stripe::setApiKey(c::config()->stripe->{c::getEnv()}->secret);

			if( $payment_type->stripe_id ){

				$recipient = $payment_type->getRecipientInfo();
				$recipient->name = $name;
				$recipient->type = $type;
				$recipient->tax_id = $tax_id;
				$recipient->save();

			} else {

				$recipient = Stripe_Recipient::create( array(
					'name' => $name,
					'type' => $type,
					'tax_id' => $tax_id
				));
				if( !$recipient->id ){
					return false;
				}
				$payment_type = $this->payment_type(); 
				$payment_type->stripe_id = $recipient->id;
				$payment_type->save();	
			}
			
			return true;
		} catch (Exception $e) {
			print_r($e);
			exit;
		}

	}

	public function saveBankInfo($name, $account, $routing, $type) {
		try {
			$bank = c::balanced()->createBankAccount($name, $account, $routing,  $type);
			$info = $this->merchant()->addBankAccount($bank);
			$payment_type = $this->payment_type(); 
			$payment_type->id_restaurant = $this->id_restaurant;
			$payment_type->balanced_bank = $bank->id;
			$payment_type->save();
			echo json_encode( [ 'success' => 'success' ] );
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
	
		// maintain a mapping of client-side-generated ids so that we can faithfully
		// generate server-side-ids. if they were the same client-side object, they
		// should be the same server-side
		$cs_id_map = [];

		if ($newDishes) {
			foreach ($newDishes as $dish) {
				$dishO                = new Dish($dish['id_dish']);
				$dishO->id_restaurant = $this->id_restaurant;
				$dishO->active        = $this->_jsonBoolean($dish, 'active', true);
				$dishO->name          = $dish['name'];
				$dishO->description   = $dish['description'];
				$dishO->price         = $dish['price'];
				$dishO->expand_view   = $dish['expand_view'];
				$dishO->top   				= $dish['top'];
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

							$cs_id = '';
							if(preg_match('/^[^\d]/', $optionGroup['id_option'])) {
								$cs_id = $optionGroup['id_option'];
								if(isset($cs_id_map[$cs_id])) {
									$optionGroup['id_option'] = $cs_id_map[$cs_id];
								}
							}

							$group = new Option($optionGroup['id_option']);
							$group->name = $optionGroup['name'];
							$group->price_linked = $optionGroup['price'];
							$group->type = $optionGroup['type'];
							$group->id_restaurant = $this->id_restaurant;
							$group->save();

							if($cs_id) {
								$cs_id_map[$cs_id] = $group->id_option;
							}

							$parent = $group->id_option;
							$newOptions[$group->id_option] = $group->id_option;

							if (!$doid = $this->_hasOption($group, $options)) {
								// New Option Group Parent
								$do = new Dish_Option;
								$do->id_dish = $dishO->id_dish;
								$do->id_option = $group->id_option;
								$do->sort = $optionGroup['sort'];
								$do->save();
							} else {
								// Existing Option Group Parent
								$do = new Dish_Option($doid);
								$do->default = $opt->default;
								$do->sort = $optionGroup['sort'];
								$do->save();
							}
						}

						$_debug_opts = array();

						if ($optionGroup['options']) {
							foreach ($optionGroup['options'] as $opt) {

								$_debug_opts[] = $opt;

								$cs_id = '';
								if(preg_match('/^[^\d]/', $opt['id_option'])) {
									$cs_id = $opt['id_option'];
									if(isset($cs_id_map[$cs_id])) {
										$opt['id_option'] = $cs_id_map[$cs_id];
									}
								}

								$option                   = new Option($opt['id_option']);
								$option->id_restaurant    = $this->id_restaurant;
								$option->id_option_parent = $parent;
								$option->price            = $opt['price'];
								$option->name             = $opt['name'];
								$option->active           = 1;
								$option->type             = 'check';
								$option->save();

								if($cs_id) {
									$cs_id_map[$cs_id] = $option->id_option;
								}

								$newOptions[$option->id_option] = $option->id_option;
								$opt['default'] = 
										(in_array($opt['default'], ['true','1',1]) ? 1 : 0);
								// I added this new column date in order to make this issue work #1437
								if (!$doid = $this->_hasOption($option, $options)) {
									// New Option
									$do            = new Dish_Option;
									$do->id_dish   = $dishO->id_dish;
									$do->id_option = $option->id_option;
									$do->default   = $opt['default'];
									if( $do->default ){
										$do->date = date('Y-m-d H:i:s');
									}
								} else {
									// Existing Option
									$do = new Dish_Option($doid);
									if ($opt['default'] != $do->default) {
										$do->default = $opt['default'];
										$do->date = date('Y-m-d H:i:s');
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
	

	public function removeCommunity(){
		c::db()->query( 'DELETE FROM restaurant_community WHERE id_restaurant="'.$this->id_restaurant.'"' );
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
			$shouldSave = false;
			if( $data['type'] == 'admin' && $data['id_admin'] ){
				$id_admin = $data['id_admin'];
				$value = '';
				$shouldSave = true;
			} if( $data['type'] != 'admin' && $data['value'] ){
				$value = $data['value'];
				$id_admin = NULL;
				$shouldSave = true;
			}
			if (!$shouldSave) { continue; }
			$element                = new Crunchbutton_Notification($data['id_notification']);
			$element->id_restaurant = $this->id_restaurant;
			$element->active        = ($data['active'] == 'true' || $data['active'] == '1') ? "1" : "0";
			$element->type          = $data['type'];
			$element->id_admin      = $id_admin;
			$element->value         = $value;
			$element->save();
		}

		$this->_notifications = null;
		$where           = [];
		$where['active'] = NULL;
		$elements = $this->notifications($where);
		return $elements;
	}

	public function saveCommunity( $id_community ){
		c::db()->query( 'DELETE FROM restaurant_community WHERE id_restaurant = "' . $this->id_restaurant . '"');
		$restaurantCommunity = new Crunchbutton_Restaurant_Community();	
		$restaurantCommunity->id_restaurant = $this->id_restaurant;
		$restaurantCommunity->id_community = $id_community;
		$restaurantCommunity->save();
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


	public function adminNotifications(){
		if (!isset($this->_admin_notifications)) {
			$this->_admin_notifications = Crunchbutton_Notification::q( "SELECT n.* FROM notification n WHERE id_restaurant = {$this->id_restaurant} AND active = 1 AND type = 'admin' " );
		}
		return $this->_admin_notifications;
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
			'active'        => 1
		];
		$whereSql = $this->_mergeWhere($defaultFilters, $where);
		if (!isset($this->_notifications)) {
			$this->_notifications = Crunchbutton_Notification::q( "SELECT * FROM notification WHERE $whereSql" );
		}
		return $this->_notifications;
	}

	public function hasNotification( $verify ){
		$types = $this->notification_types();
		foreach( $types as $type ){
			if( $type == $verify ){
				return true;
			}
		}
		return false;
	}

	public function notification_types(){
		$types = array();
		$notifications = $this->notifications();
		foreach( $notifications as $notification ){
			$types[ $notification->type ] = $notification->type;
		}
		return $types;
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
		$out = $this->properties();		
		// method ByRand doesnt need all the properties
		if( $out['type'] && $out['type'] == 'byrange' ){
			$_ignore = [ 'type', 'credit','address','max_items','tax','active','phone','fee_restaurant','fee_customer','delivery_min','delivery_min_amt','notes_todo','pickup_estimated_time','delivery_fee','delivery_estimated_time','notes_owner','confirmation','zip','customer_receipt','cash','giftcard','email','notes','balanced_id','balanced_bank','fee_on_subtotal','payment_method','id_restaurant_pay_another_restaurant','charge_credit_fee','waive_fee_first_month','pay_promotions','pay_apology_credits','check_address','contact_name','summary_fax','summary_email','summary_frequency','legal_name_payment','tax_id','community','_preset','id_community', '_hoursFormat', 'loc_long', 'lat_lat', 'id_community' ];
			foreach ( $_ignore as $property ) {
				$ignore[ $property ] = true;
			}
		}
		$out['_weight'] = $this->weight();
		$community = $this->community();
		if( $community->id_community ){
			$out['id_community'] = $community->id_community;	
		} else {
			$out['id_community'] = null;
		}
		

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

			// To make sure it will be ignored at cockpit
			$isCockpit = ( $_REQUEST[ 'cockpit' ] || ( strpos( $_SERVER['HTTP_HOST'], 'cockpit' ) !== false )  ) ? true : false;
			if( $isCockpit ){
				$ignore[ 'delivery_service_markup_prices' ] = true;	
			}
			if ( !$ignore[ 'delivery_service_markup_prices' ] ) {
				// Recalculate the price using the delivery_service_markup variable #2032
				$delivery_service_markup = $this->delivery_service_markup;
				if( $delivery_service_markup && $delivery_service_markup > 0 ){
					// Categories
					for( $i=0; $i<( count( $out[ '_categories' ] ) ); $i++ ){
						// Dishes
						for( $j=0; $j<( count( $out[ '_categories' ][ $i ][ '_dishes' ] ) ); $j++ ){
							$price = $out[ '_categories' ][ $i ][ '_dishes' ][ $j ][ 'price' ];
							$price_original = $price;
							if( $price > 0 ){
								$price = $price + ( $price * $delivery_service_markup / 100 );
								$price = number_format( $price, 2 );
								$out[ '_categories' ][ $i ][ '_dishes' ][ $j ][ 'price' ] = $price;	
								$out[ '_categories' ][ $i ][ '_dishes' ][ $j ][ 'markup' ] = number_format( $price - $price_original, 2 );	
							}
							// Options
							for( $k=0; $k<( count( $out[ '_categories' ][ $i ][ '_dishes' ][ $j ][ '_options' ] ) ); $k++ ){
								$price = $out[ '_categories' ][ $i ][ '_dishes' ][ $j ][ '_options' ][ $k ][ 'price' ];
								$price_original = $price;
								$out[ '_categories' ][ $i ][ '_dishes' ][ $j ][ '_options' ][ $k ][ 'o_price' ] = $price;
								// if( $price > 0 ){
								// The markup should be applied even to subtract prices: #2434
									$price = $price + ( $price * $delivery_service_markup / 100 );
									$price = number_format( $price, 2 );
									$out[ '_categories' ][ $i ][ '_dishes' ][ $j ][ '_options' ][ $k ][ 'price' ] = $price;
									$out[ '_categories' ][ $i ][ '_dishes' ][ $j ][ '_options' ][ $k ][ 'markup' ] = number_format( $price - $price_original, 2 );	
								// }
							}
						}
					}
				}
			}
		}

		$isAdmin = ( isset( $_SESSION['admin'] ) && $_SESSION[ 'admin' ] );

		// Issue #1051 - potentially urgent security issue
		if( !$isAdmin ){
			$ignore['notifications'] = true;
			$out[ 'notes_owner' ] = NULL;
			$out[ 'balanced_id' ] = NULL;
			$out[ 'balanced_bank' ] = NULL;
			$out[ 'notes' ] = NULL;
			$out[ 'email' ] = NULL;
		}		

		if (!$ignore['notifications']) {
			$where = [];
			if ( $isAdmin ) {
				$where['active'] = NULL;
			}
			foreach ($this->notifications($where) as $notification) {
				/* @var $notification Crunchbutton_Notification */
				$out['_notifications'][$notification->id_notification] = $notification->exports();
			}
		}

		if( $isCockpit ){
			foreach ($this->hours() as $hours) {
				$out['_hours'][$hours->day][] = [$hours->time_open, $hours->time_close];
			}			
		} else {
			$out[ 'hours' ] = $this->hours_next_24_hours( true );
			$next_open_time = $this->next_open_time( true );
			if( $next_open_time ){
				$next_open_time_restaurant_tz = $this->next_open_time();

				$out[ 'next_open_time' ] = ( $next_open_time ) ? $next_open_time->format( 'Y-m-d H:i' ) : false;
				$out[ 'next_open_time_message' ] = $this->next_open_time_message();
			}
			
		}


		$out['closed_message'] = $this->closed_message();

		if (!$ignore['_preset']) {
			if ($this->preset()->count()) {
				$out['_preset'] = $this->preset()->get(0)->exports();
			}
		}

		$out['id_community'] = $this->community()->id_community;

		// Remove ignored methods
		foreach ( $ignore as $property => $val ) {
			unset( $out[ $property ] );
		}
		
		$comment = $this->comment();
		
		if ($comment->id_restaurant_comment) {
			$auths = $comment->user()->auths()->get(0);

			foreach ($auths as $auth) {
				if ($auth->type == 'facebook') {
					$id = $auth->auth;
					break;
				}
			}
			$out['comment'] = [
				'content' => $comment->content,
				'user' => $comment->user()->get(0)->name(),
				'fb' => $id
			];
		}

		// get the legacy data
		if( !$isCockpit ){
			$out = array_merge( $out, $this->hours_legacy(  $isCockpit ) );	
		}
		

		return $out;
	}

	public function hasDeliveryService(){
		// At first check the delivery_service
		if( $this->delivery_service ){
			return 1;
		} 
		/*
		// Second, check if it has an admin active notification
		$type_admin = Crunchbutton_Notification::TYPE_ADMIN;
		$notification = Notification::q( "SELECT n.* FROM notification n WHERE n.id_restaurant = {$this->id_restaurant} AND n.active = 1 AND n.type = '{$type_admin}' LIMIT 1");
		if( $notification->id_notification ){
			return 1;
		}*/
		return 0;
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

		if( $restaurant[ 'id_community' ] ){
			$this->saveCommunity( $restaurant[ 'id_community' ] );
			// legacy for while
			$community = Crunchbutton_Community::o( $restaurant[ 'id_community' ] );
			if( $community->id_community ){
				$this->community = $community->name;	
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
					$sql = "SELECT * FROM category WHERE name = '". $category['name']. "' AND id_restaurant = '" . $restaurant[ 'id_restaurant'] . "'  ORDER BY sort ASC LIMIT 1";
					$c = Crunchbutton_Category::q($sql);
					$dish['id_category'] = $c->id_category;
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
				"byrange" type,
				((ACOS(SIN('.$params['lat'].' * PI() / 180) * SIN(loc_lat * PI() / 180) + COS('.$params['lat'].' * PI() / 180) * COS(loc_lat * PI() / 180) * COS(('.$params['lon'].' - loc_long) * PI() / 180)) * 180 / PI()) * 60 * 1.1515) AS `distance`,
				restaurant.*
			FROM `restaurant`
			LEFT JOIN `order` o ON o.id_restaurant = restaurant.id_restaurant AND o.date > DATE( NOW() - INTERVAL 30 DAY)
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
			$restaurant->_weight_old = $restaurant->_weight;
			$restaurant->_weight = (($restaurant->_weight / $sum) * 100) + $restaurant->weight_adj;
		}

		return $restaurants;
	}

	public function hasPhoneNotification(){
		$phone = Notification::q( 'SELECT * FROM notification WHERE id_restaurant = ' . $this->id_restaurant . ' AND active = 1 and type = "' . Crunchbutton_Notification::TYPE_PHONE . '"' );
		if( $phone->id_notification ){
			return true;
		} else {
			return false;
		}
	}

	public function hasFaxNotification(){
		$fax = Notification::q( 'SELECT * FROM notification WHERE id_restaurant = ' . $this->id_restaurant . ' AND active = 1 and ( type = "' . Crunchbutton_Notification::TYPE_FAX . '" OR type = "' . Crunchbutton_Notification::TYPE_STEALTH . '" )' );
		if( $fax->id_notification ){
			return true;
		} else {
			return false;
		}
	}

	public static function getCommunities(){
		$data = c::db()->get( 'SELECT DISTINCT( community ) community FROM restaurant WHERE community IS NOT NULL AND community != "" AND active = 1 ORDER BY community ASC' );
		$communities = [];
		foreach ( $data as $item ) {
			$communities[] = $item->community;
		}
		return $communities;
	}

	public static function getCommunitiesWithRestaurantsNumber(){
					$data = c::db()->get( 'SELECT SUM(1) restaurants, community FROM restaurant WHERE community IS NOT NULL AND community != "" GROUP BY community' );
					$communities = [];
					foreach ( $data as $item ) {
									$communities[] = $item;
					}
					return $communities;
	}

	public static function getRestaurantsByCommunity( $community, $inactive = false ){
		if( !$inactive ){
			$where = 'AND active = 1';
		}
		return Crunchbutton_Restaurant::q( "SELECT * FROM restaurant WHERE community = '{$community}' {$where} ORDER BY name ASC" );
	}

	public function restaurantsUserHasPermission(){
		$restaurants_ids = [];
		$_permissions = new Crunchbutton_Admin_Permission();
		$all = $_permissions->all();
		// Get all restaurants permissions
		$restaurant_permissions = $all[ 'restaurant' ][ 'permissions' ];
		$permissions = c::admin()->getAllPermissionsName();
		$restaurants_id = array();
		foreach ( $permissions as $permission ) {
			$permission = $permission->permission;
			$info = $_permissions->getPermissionInfo( $permission );
			$name = $info[ 'permission' ];
			foreach( $restaurant_permissions as $restaurant_permission_name => $meta ){
				if( $restaurant_permission_name == $name ){
					if( strstr( $name, 'ID' ) ){
						$regex = str_replace( 'ID' , '((.)*)', $name );
						$regex = '/' . $regex . '/';
						preg_match( $regex, $permission, $matches );
						if( count( $matches ) > 0 ){
							$restaurants_ids[] = $matches[ 1 ];
						}
					}
				}
			}
		}
		return array_unique( $restaurants_ids );
	}

	public function adminReceiveSupportSMS(){
		$permission = "support-receive-notification-{$this->id_restaurant}";
		$query = "SELECT DISTINCT(name),
									 txt FROM
							(SELECT a.*
							 FROM admin_permission ap
							 INNER JOIN admin_group ag ON ap.id_group = ag.id_group
							 INNER JOIN admin a ON ag.id_admin = a.id_admin
							 WHERE ap.permission = '{$permission}'
								 AND ap.id_group IS NOT NULL
							 UNION SELECT a.*
							 FROM admin_permission ap
							 INNER JOIN admin a ON ap.id_admin = a.id_admin
							 WHERE ap.permission = '{$permission}'
								 AND ap.id_admin IS NOT NULL) admin
						WHERE txt IS NOT NULL";
		return Admin::q( $query );
	}

	public function adminWithSupportAccess(){
		$permission = "support-create-edit-{$this->id_restaurant}";
		$query = "SELECT DISTINCT(name),
									 txt FROM
							(SELECT a.*
							 FROM admin_permission ap
							 INNER JOIN admin_group ag ON ap.id_group = ag.id_group
							 INNER JOIN admin a ON ag.id_admin = a.id_admin
							 WHERE ap.permission = '{$permission}'
								 AND ap.id_group IS NOT NULL
							 UNION SELECT a.*
							 FROM admin_permission ap
							 INNER JOIN admin a ON ap.id_admin = a.id_admin
							 WHERE ap.permission = '{$permission}'
								 AND ap.id_admin IS NOT NULL) admin
						WHERE txt IS NOT NULL";
		return Admin::q( $query );
	}
	
	public function comment() {
		if (!isset($this->_comment)) {
			$this->_comment = Restaurant_Comment::q('select * from restaurant_comment where top=1 && id_restaurant='.$this->id_restaurant.'');
		}
		return $this->_comment;
	}

	public function calc_pickup_estimated_time( $datetime = null ){
		$multipleOf = 15;
		$time = new DateTime( ( $datetime ? $datetime : 'now' ), new DateTimeZone( $this->timezone ) );
		$minutes = round( ( ( $time->format( 'i' ) + $this->pickup_estimated_time ) + $multipleOf / 2 ) / $multipleOf ) * $multipleOf;
		$minutes -= $time->format( 'i' );
		return date( 'g:i a', strtotime( $time->format( 'Y-m-d H:i' ) . ' + ' . $minutes . ' minute' ) );
	}

	// Start Telling Customers Estimated Delivery Time #2476
	public function calc_delivery_estimated_to_text_message( $datetime = null ){
		// https://github.com/crunchbutton/crunchbutton/issues/2600#issuecomment-37227298
		return $this->calc_delivery_estimated_time( $datetime );
		/*
		$multipleOf = 15;
		$estimated = 60; // minutes
		$time = new DateTime( ( $datetime ? $datetime : 'now' ), new DateTimeZone( $this->timezone ) );
		$minutes = round( ( ( $time->format( 'i' ) + $estimated ) + $multipleOf / 2 ) / $multipleOf ) * $multipleOf;
		$minutes -= $time->format( 'i' );
		return date( 'g:i a', strtotime( $time->format( 'Y-m-d H:i' ) . ' + ' . $minutes . ' minute' ) ); 
		*/
	}

	public function calc_delivery_estimated_time( $datetime = null ){
		$multipleOf = 15;
		$time = new DateTime( ( $datetime ? $datetime : 'now' ), new DateTimeZone( $this->timezone ) );
		$minutes = round( ( ( $time->format( 'i' ) + $this->delivery_estimated_time ) + $multipleOf / 2 ) / $multipleOf ) * $multipleOf;
		$minutes -= $time->format( 'i' );
		return date( 'g:i a', strtotime( $time->format( 'Y-m-d H:i' ) . ' + ' . $minutes . ' minute' ) );
	}

	public function payment_type(){
		return Crunchbutton_Restaurant_Payment_Type::byRestaurant( $this->id_restaurant );
	}

	public function save() {
		if (!$this->timezone) {
			$this->timezone = 'America/Los_Angeles';
		}
		parent::save();
	}


	public function drivers(){
		return Admin::q( 'SELECT DISTINCT( a.id_admin ) id, a. * FROM admin a INNER JOIN notification n ON a.id_admin = n.id_admin AND n.id_restaurant = ' . $this->id_restaurant . ' AND n.active = 1 AND n.type = "' . Crunchbutton_Notification::TYPE_ADMIN . '"');
	}

	public function withDrivers(){
		return Restaurant::q( 'SELECT DISTINCT(r.id_restaurant) id, r.* FROM restaurant r INNER JOIN notification n ON r.id_restaurant = n.id_restaurant AND n.type = "' . Crunchbutton_Notification::TYPE_ADMIN . '" WHERE r.name NOT LIKE "%test%" ORDER BY r.name' );
	}

	public function totalOrders(){
		return Crunchbutton_Chart_Order::totalOrdersByRestaurant( $this->id_restaurant );
	}


	/*
	* Hours and Open/Closed methods
	*/

	// return the hours info used at iphone native app
	public function hours_legacy( $isCockpit ){

		$data = [];
		$data[ 'open_for_business' ] = $this->open_for_business;
		$data[ '_open' ] = $this->open();

		// force open
		$data[ '_force_open' ] = Crunchbutton_Restaurant_Hour_Override::forceOpen( $this->id_restaurant );;

		// force close
		$forceClose = Crunchbutton_Restaurant_Hour_Override::forceClose( $this->id_restaurant );
		if( $forceClose ){
			$data['_force_close'] = true;
			$data['_force_close_notes'] = $forceClose;
		} else {
			$data['_force_close'] = false;
		}

		// if it is open shows closesIn
		if( $data[ '_open' ] ){
			$closesIn = $this->closesIn();
			$data[ '_closesIn' ] = $closesIn;
			if( $data['_closesIn'] === 0 ){
					$data[ '_open' ] = false;
					$data[ '_closesIn' ] = false;
			} else {
				$data[ '_closesIn_formated' ] = Cana_Util::formatMinutes( $closesIn )[ 'formatted' ];	
			}
		} else {
			$data[ '_closesIn' ] = false;
		}
		
		// if it is closed shows opensIn
		if( !$data[ '_open' ] ){
			$opensIn = $this->opensIn();
			$data[ '_openIn' ] = $opensIn;
			if( $data[ '_openIn' ] ){
				$data[ '_openIn_formated' ] = Cana_Util::formatMinutes( $opensIn )[ 'formatted' ];
			}
		} else {
			$data[ '_openIn' ] = false;
		}

		// Min minutes to show the hurry message
		$data[ '_minimumTime' ] = 15;  

		// tags
		if( $data['_open'] ){
			if( $this->delivery != 1 ){
				$data['_tag']  = 'takeout';        
			} else {
			if( $data['_closesIn'] <= $data['_minimumTime'] && $data['_closesIn'] !== false){
		      $data['_tag']  = 'closing';
				}
			}
		} else {
			$data['_tag']  = 'closed';
			if( $data[ '_force_close' ] ){
				$data['_tag']  = 'force_close';
			}
		}

		$data[ 'open_holidays' ] = $this->open_holidays;

		// hours utc formatted
		$hours = $this->hours( true );
		foreach ( $hours as $hours ) {
			$data[ '_hoursFormat' ][ $hours->day ][] = [ $hours->time_open, $hours->time_close ];
		}

		$hours = $this->hours();
		$_hours = [];
		foreach ( $hours as $hours ) {
			$_hours[ $hours->day ][] = [ $hours->time_open, $hours->time_close ];
		}

		if( !$isCockpit ){
			$data[ '_hours' ] = Hour::mergeHolidays( $_hours, $this, false );
		}

		$_hours_converted_utc = Hour::hoursStartingMondayUTC( $_hours );
		$hours_converted_utc = [];
		foreach( $_hours_converted_utc as $_hour_converted_utc ){
			$hours_converted_utc[] = (object) $_hour_converted_utc;
		}

		$data[ '_hours_converted_utc' ] = $hours_converted_utc;
		
		return $data;
	}

	// return the restaurant's hours
	public function hours( $gmt = false ) {
		return Hour::hoursByRestaurant( $this, $gmt );
	}

	// return if the restaurant is closed or not
	public function closed( $dt = null ){
		return !$this->open( $dt );
	}

	// return if the restaurant is open or not
	public function open( $dt = null ) {

		if( $dt ){
			$this->_dt = $dt;
		}

		// it is not open
		if ( !$this->open_for_business ) {
			return false;
		}

		// restaurant without hours is not open
		if( count( $this->hours() ) == 0 ){
			return false;
		}

		// Calculate the hours to verify if it is open or not
		return Hour::restaurantIsOpen( $this, $dt );
	}

	// return the closed message
	public function closed_message(){
		return Hour::restaurantClosedMessage( $this );	
	}

	public function next_open_time_message( $utc = false ){
		if( $this->open_for_business ){
			// if the restaurant is open return false
			if ( $this->closed() ) {
				return Hour::restaurantNextOpenTimeMessage( $this, $utc );	
			}
		} 
		return false;
	}

	// Return the next open time
	public function next_open_time( $utc = false ){
		if( $this->open_for_business ){
			// if the restaurant is open return false
			if ( $this->closed() ) {
				return Hour::restaurantNextOpenTime( $this, $utc );	
			}
		} 
		return false;
	}

	// return the next close time
	public function next_close_time(){
		if( $this->open_for_business ){
			// if the restaurant is closed return false
			if ( $this->open() ) {
				return Hour::restaurantNextCloseTime( $this );
			}
		}
		return false;
	}

	// Export the restaurant statuses: open/close for the week starting at the previous day
	public function hours_week( $gmt = true ){
		if( $this->open_for_business ){
			return Hour::getByRestaurantWeek( $this, $gmt );
		} 
		return false;
	}

	// Export the restaurant statuses: open/close for the next 24 hours
	public function hours_next_24_hours( $gmt = false ){
		if( $this->open_for_business ){
			return Hour::getByRestaurantNext24Hours( $this, $gmt );	
		}
		return false;
	}

	// Return minutes left to close
	public function closesIn( $dt = null ) {
		// if the restaurant is closed return false
		if ( $this->closed( $dt ) ) {
			return false;
		}
		return Hour::restaurantClosesIn( $this );
	}

	public function getOrdersFromLastDaysByCommunity( $community, $days = 14 ){
		$query = "SELECT SUM(1) orders, DATE_FORMAT( o.date, '%m/%d/%Y' ) day FROM `order` o
					INNER JOIN restaurant r ON r.id_restaurant = o.id_restaurant AND r.community = '$community'
					WHERE o.date > DATE_SUB(CURDATE(), INTERVAL $days DAY) AND o.name NOT LIKE '%test%' GROUP BY day ORDER BY o.date ASC";
		return c::db()->get( $query );
	}

	// Return minutes left to open
	public function opensIn( $dt = null ) {
		if( $this->open_for_business ){
				// if the restaurant is open return false
			if ( $this->open( $dt ) ) {
				return false;
			}
			return Hour::restaurantOpensIn( $this );
		}
		return false;
	}
}
