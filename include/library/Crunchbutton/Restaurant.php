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

	public function isImageUsedByOtherRestaurant(){
		if( !$this->image ){
			return false;
		}
		$restaurants = Crunchbutton_Restaurant::q( 'SELECT * FROM restaurant WHERE image = "' . $this->image . '" AND id_restaurant != "' . $this->id_restaurant . '"' );
		if( $restaurants->count() > 0 ){
			return true;
		}
		return false;
	}

	public function deliveryItSelf(){
		if( $this->delivery ){
			if( $this->delivery_service ){
				return false;
			}
			return true;
		}
		return false;
	}

	public static function permalink($permalink) {
		return self::q('select * from restaurant where permalink=?', [$permalink])->get(0);
	}

	public function active(){

		if(c::user()->isCommunityDirector()){
			$community = c::user()->communityDirectorCommunity();
			return Crunchbutton_Restaurant::q( 'SELECT r.id_restaurant, r.name, c.name AS community FROM restaurant r LEFT JOIN restaurant_community rc ON rc.id_restaurant = r.id_restaurant LEFT JOIN community c ON c.id_community = rc.id_community WHERE r.active = true AND c.id_community = ? ORDER BY r.name ASC', [$community->id_community] );
		}

		return Crunchbutton_Restaurant::q( 'SELECT r.id_restaurant, r.name, c.name AS community FROM restaurant r LEFT JOIN restaurant_community rc ON rc.id_restaurant = r.id_restaurant LEFT JOIN community c ON c.id_community = rc.id_community WHERE r.active = true ORDER BY r.name ASC' );
	}

	public function with_no_payment_method(){
		$_restaurants = [];
		$restaurants = Crunchbutton_Restaurant::q( 'SELECT * FROM restaurant WHERE active = true AND formal_relationship = 1 AND name NOT LIKE "%test%" ORDER BY name' );
		foreach( $restaurants as $restaurant ){
			if( !$restaurant->hasPaymentType() ){
				$_restaurants[] = $restaurant;
			}
		}
		return $_restaurants;
	}

	public function paymentType(){
		return $this->payment_type();
	}

	public function hasPaymentType(){
		$paymentType = $this->paymentType();
		if( $paymentType->stripe_id  ){
			return true;
		} else {
			return false;
		}
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
			$this->_dishes = Dish::q($sql);
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
			$this->_categories = Category::q($sql);
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
		if( !$this->_communities ){
			$this->_communities = $this->communities();
		}
		return $this->_communities;
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
			$this->_communities = Community::q('select community.* from community left join restaurant_community using(id_community) where id_restaurant=? ORDER BY community.name ASC', [$this->id_restaurant]);
		}
		return $this->_communities;
	}

	public function phone() {
		$phone = $this->phone;
		$phone = preg_replace('/[^\d]*/i','',$phone);
		$phone = preg_replace('/(\d{3})(\d{3})(.*)/', '\\1-\\2-\\3', $phone);

		return $phone;
	}

	// name that appears on credit card statement
	public function statementName() {
		if (!isset($this->_statementName)) {
			$name = $this->short_name ? $this->short_name : $this->name;
			$name = 'Crunchbutton-'.preg_replace('/[^a-z ]/i','',$name);
			if (strlen($name) > 22) {
				$name = str_replace(' ', '', $name);
			}
			$this->_statementName = strtoupper(substr($name, 0, 22));
		}
		return $this->_statementName;
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
		// @balanced
		return false;
	}

	public function merchant() {
		// @balanced
		return false;
	}

	public function activeDrivers(){
		$community = $this->community()->get( 0 );
		$activeDrivers = 0;
		if( $community->id_community ){
			$drivers = $community->getDriversOfCommunity();
			foreach( $drivers as $driver ){
				if( $driver->isWorking() ){
					$activeDrivers++;
				}
			}
		}

		return $activeDrivers;
	}

	// Smart ETA MVP #4600
	public function smartETA( $array = false, $params = [] ){

		if( !$this->hasDeliveryService() ){
			return 0;
		}

		$eta = 0;

		// N = # of active drivers
		// X = # of orders placed but not picked up
		// Y = # of orders picked up but not delivered
		// Z = # of additional orders from the same restaurant accepted by the any driver
		// Estimated ETA:
		// 30 min + (15X + 7Y - 8Z) / N

		$interval = 1;

		// N = # of active drivers
		if( $params[ 'activeDrivers' ] ){
			$activeDrivers = $params[ 'activeDrivers' ];
		} else {
			$activeDrivers = $this->activeDrivers();
		}

		if( $params[ 'id_community' ] ){
			$id_community = $params[ 'id_community' ];
		} else {
			$community = $this->community();
			$id_community = $community->id_community;
		}

		// X = # of orders placed but not picked up
		// Y = # of orders picked up but not delivered
		// Z = # of additional orders from the same restaurant accepted by the same driver
		$ordersPlacedButNotPickedUp = 0;
		$ordersPickedUpButNotDelivered = 0;
		$additionalOrdersFromTheSameRestaurantAcceptedByAnyDriver = 0;

		if( $params[ 'orders' ] ){
			$orders = $params[ 'orders' ];
		} else {
			$query = '
				SELECT o.* FROM `order` o
				INNER JOIN restaurant r ON r.id_restaurant = o.id_restaurant
				INNER JOIN restaurant_community rc ON rc.id_restaurant = r.id_restaurant AND rc.id_community = ?
				WHERE o.delivery_type = ?
					AND o.delivery_service = true
					AND o.date >= now() - INTERVAL '.$interval.' DAY
				ORDER BY o.id_order DESC
			';
			$orders = Order::q($query, [$id_community, Crunchbutton_Order::SHIPPING_DELIVERY]);
		}
		foreach( $orders as $order ){
			$lastStatus = $order->status()->last();
			if( $lastStatus[ 'status' ] == 'new' || $lastStatus[ 'status' ] == 'accepted' ){
				$ordersPlacedButNotPickedUp++;
			}
			if( $lastStatus[ 'status' ] == 'pickedup' ){
				$ordersPickedUpButNotDelivered++;
			}
			if( $order->id_restaurant == $this->id_restaurant && $lastStatus[ 'status' ] == 'accepted' ){
				$additionalOrdersFromTheSameRestaurantAcceptedByAnyDriver++;
			}
		}

		if( $activeDrivers ){
			$eta = 30 + ( ( 15 * $ordersPlacedButNotPickedUp ) +
										( 7 * $ordersPickedUpButNotDelivered ) +
										( 8 * $additionalOrdersFromTheSameRestaurantAcceptedByAnyDriver ) ) / $activeDrivers;
		}

		if( $eta < 40 ){
			$eta = 40;
		}

		$eta = ceil( $eta );
		if ($this->delivery_estimated_time && $eta < $this->delivery_estimated_time) {
			$eta = $this->delivery_estimated_time;
		}

		if( $array ){
			return [ 	'eta' => $eta,
								'activeDrivers' => $activeDrivers,
								'ordersPlacedButNotPickedUp' => $ordersPlacedButNotPickedUp,
								'ordersPickedUpButNotDelivered' => $ordersPickedUpButNotDelivered,
								'additionalOrdersFromTheSameRestaurantAcceptedByAnyDriver' => $additionalOrdersFromTheSameRestaurantAcceptedByAnyDriver ];
		}
		return intval( $eta );
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
		c::dbWrite()->query( 'DELETE FROM restaurant_community WHERE id_restaurant=?', [$this->id_restaurant]);
	}

	/**
	 * Save the notifications as they are send by the API
	 *
	 * @param array $elements
	 */
	public function saveNotifications($elements) {
		if( $elements ){
			foreach( $elements as $element ){
				$shouldSave = false;
				if( $element[ 'type' ] == 'admin' && trim( $element[ 'id_admin' ] ) != '' ){
					$id_admin = $element[ 'id_admin' ];
					$value = '';
					$shouldSave = true;
				}
				if( $element[ 'type' ] != 'admin' && trim( $element[ 'value' ] ) != '' ){
					$value = $element[ 'value' ];
					$id_admin = NULL;
					$shouldSave = true;
				}
				// echo '<pre>';var_dump( $shouldSave, $element );exit();
				if( $shouldSave ){
					if( $element[ 'id_notification' ] ){
						$notification = Crunchbutton_Notification::o( $element[ 'id_notification' ] );
					} else {
						$notification = new Crunchbutton_Notification;
					}
					$notification->id_restaurant = $this->id_restaurant;
					$notification->active = ( $element[ 'active' ] == 'true' || $element[ 'active' ] == '1' ) ? 1 : 0;
					$notification->type = $element[ 'type' ];
					$notification->id_admin = $id_admin;
					$notification->value = $value;
					$notification->save();
				} else {
					// remove
					if( $element[ 'id_notification' ] ){
						c::dbWrite()->query( 'DELETE FROM notification WHERE id_notification = "' . $element[ 'id_notification' ] . '"' );
					}
				}
			}
		}
		$this->_notifications = null;
		$where = [];
		$where[ 'active' ] = NULL;
		$elements = $this->notifications( $where );
		return $elements;
	}

	public function saveCommunity( $id_community ){
		c::dbWrite()->query( 'DELETE FROM restaurant_community WHERE id_restaurant = "' . $this->id_restaurant . '"');
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
		c::dbWrite()->query('delete from hour where id_restaurant=?', [$this->id_restaurant]);
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
			$this->_admin_notifications = Crunchbutton_Notification::q( "SELECT n.* FROM notification n WHERE id_restaurant = {$this->id_restaurant} AND active = true AND type = 'admin' " );
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
			$this->_notifications = Crunchbutton_Notification::q( "SELECT * FROM notification WHERE $whereSql ORDER BY active DESC, type" );
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
			select * from preset where id_restaurant=?
			and id_user is null
		', [$this->id_restaurant]);
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

	public function getImgFormats() {
		return [
			'normal' => ['height' => 596, 'width' => 596, 'crop' => 0],
			'thumb' => ['height' => 200, 'width' => 200, 'crop' => 1],
			'icon' => ['height' => 66, 'width' => 66, 'crop' => 0],
			'header' => ['height' => 425, 'width' => 1200, 'crop' => 1]
		];
	}

	public function image() {
		return $this->getImages()['original'];
	}

	public function updateImage($file = null, $name = null) {
		if (!$file) {
			$file = $this->imagePath().$this->image;
		}

		$bucket = c::config()->s3->buckets->{'image-restaurant'}->name;

		// download the source from s3 and resize
		if (!file_exists($file)) {
			echo 'no file:' . $file. "\n";
			$file = tempnam(sys_get_temp_dir(), 'restaurant-image');
			$fp = fopen($file, 'wb');
			if (($object = S3::getObject($bucket, $this->permalink.'.jpg', $fp)) !== false) {
				var_dump($object);
			} else {
				$file = false;
			}

		} else {
			$info = pathinfo($file);
			$ext = $info['extension'];
			if (!$ext) {
				$pos = strrpos($name, '.');
						$ext = substr($name, $pos+1);
			}
			$ext = strtolower($ext);
			switch ($ext) {
				case 'jpg':
				case 'jpeg':
					$type = 'image/jpeg';
					break;
				case 'gif':
					$type = 'image/gif';
					break;
				case 'png':
					$type = 'image/png';
					break;
			}

			// upload the source image
			$upload = new Crunchbutton_Upload([
				'file' => $file,
				'resource' => $this->permalink.'.'.$ext,
				'bucket' => $bucket,
				'type' => $type
			]);
			$r[] = $upload->upload();

			// update the local file for backwards compatability
			// shit is on dif servers so this isnt gonna work
			/*
			$images_path = '/home/i.crunchbutton/www/image/';

			$rand = substr( str_replace( '.' , '', uniqid( rand(), true ) ), 0, 8 );
			$this->image = $this->id_restaurant.'.'.$rand.'.'.$ext;
			$this->save();

			$current_image = $images_path.$this->image;
			if (@copy($file, $current_image)) {
				@chmod($current_image,0777);
			}
			*/

			$formats = $this->getImgFormats();
		}

		if (!$file) {
			return false;
		}

		// loop through each thumb and upload
		foreach ($formats as $format) {

			$format['img']			= $info['basename'];
			$format['cache'] 		= '/tmp/';
			$format['path'] 		= $info['dirname'].'/';
			$format['format']		= 'jpg';
			$format['quality']		= '70';

			try {
				$thumb = new Cana_Thumb($format);
			} catch (Exception $e) {
				print_r($e->getMessage());
				$r[] = false;
				$thumb = null;
			}

			if ($thumb) {
				$upload = new Crunchbutton_Upload([
					'file' => $thumb->_image['file'],
					'resource' => $this->permalink.'-'.$format['width'].'x'.$format['height'].'.'.$format['format'],
					'bucket' => $bucket
				]);
				$r[] = $upload->upload();
			}
		}

		return in_array(false, $r) ? false : true;

	}

	public function weight() {
		if (!isset($this->_weight)) {
			$res = self::q('
				select count(*) as weight, restaurant.name from `order`
				left join restaurant using(id_restaurant)
				where restaurant.id_restaurant=?
				group by restaurant.id_restaurant
			', [$this->id_restaurant]);
			$this->_weight = $res->weight;
		}
		return $this->_weight;
	}

	public function getImages($loc = 'cache') {
		if ($loc == 'cache') {
			$url = c::config()->s3->buckets->{'image-restaurant'}->cache;
		} else {
			$url = 's3.amazonaws.com/'.c::config()->s3->buckets->{'image-restaurant'}->name;
		}
		$imgPrefix = 'https://'.$url.'/';
		$out = [
			'original' => $imgPrefix.$this->permalink.'.jpg',
		];

		foreach ($this->getImgFormats() as $key => $format) {
			$out[$key] = $imgPrefix.$this->permalink.'-'.$format['width'].'x'.$format['height'].'.jpg';
		}
		return $out;
	}

	public function campusCash(){
		if( $this->delivery_service && $this->campus_cash ){
			$community = $this->community()->get(0);
			if( $community && $community->id_community && $community->campusCash() ){
				return true;
			}
		}
		return false;
	}

	public function campusCashDeliveryLocatedOnCampus(){
		if( $this->delivery_service ){
			$community = $this->community()->get(0);
			if( $community && $community->id_community && $community->campusCashDeliveryLocatedOnCampus() ){
				return true;
			}
		}
		return false;
	}

	public function campusCashName(){
		if( $this->campusCash() ){
			$community = $this->community()->get(0);
			return $community->campusCashName();
		}
		return null;
	}

	public function campusCashDefaultPaymentMethod(){
		if( $this->campusCash() ){
			$community = $this->community()->get(0);
			return $community->campusCashDefaultPaymentMethod();
		}
		return null;
	}

	public function campusCashReceiptInfo(){
		if( $this->campusCash() ){
			$community = $this->community()->get(0);
			return $community->campusCashReceiptInfo();
		}
		return null;
	}

	public function campusCashFee(){
		if( $this->campusCash() ){
			$community = $this->community()->get(0);
			return $community->campusCashFee();
		}
		return 0;
	}

	public function campusCashMask(){
		if( $this->campusCash() ){
			$community = $this->community()->get(0);
			return $community->campusCashMask();
		}
		return null;
	}

	public function campusCashValidate( $card ){
		if( $this->campusCash() ){
			$community = $this->community()->get(0);
			return $community->campusCashValidate( $card );
		}
		return false;
	}

	public function preOrderHours(){

		if( $this->_preOrderHours ){
			return $this->_preOrderHours;
		}

		if( !$this->allowPreorder() ){
			return false;
		}

		if( !$this->open_for_business ){
			return false;
		}

		$this->preOrderTimeToTime = null;

		$timeToTime = null;

		// get shift plus restaurant merged hours
		$hours = Hour::hoursByRestaurant( $this, false, true );
		$hours = $this->preOrderProcessHours( $hours );
		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
		$now->setTimezone( new DateTimeZone( $this->timezone ) );

		$imutable = $now->format( 'Ymd' );

		$days = [];

		$today = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
		$today->setTimezone( new DateTimeZone( $this->timezone ) );

		for( $i = 1; $i <= 6; $i++ ){

			$label = $now->format( 'D M d' );
			if( $i == 1 ){
				$label .= ' (Today)';
			}
			if( $i == 2 ){
				$label .= ' (Tomorrow)';
			}

			$day = [ 'value' => $now->format( 'Y-m-d' ), 'label' => $label, 'hours' => [] ];

			$day[ 'hours' ][] = [ 'label' => 'Desired Time', 'value' => false ];
			$first_hour_of_day = true;

			foreach( $hours as $hour ){
				if( $hour->day == strtolower( $now->format( 'D' ) ) ){
					$store = true;
					if( $hour->day == strtolower( $today->format( 'D' ) ) ){
						// just to make sure it will not add an old hour
						$compare = DateTime::createFromFormat( 'Y-m-d h:i A', $today->format( 'Y-m-d ' ) . $hour->time_close, new DateTimeZone( $this->timezone ) );
						if( $today > $compare ){
							$store = false;
						}
					}
					if( $store ){

						if($first_hour_of_day && !$this->delivery_service){
							$label = 'ASAP after the restaurant opens';
						} else {
							$label = $hour->time_open . ' - ' . $hour->time_close;
						}
						$first_hour_of_day = false;
						$day[ 'hours' ][] = [ 'label' => $label, 'value' => strtolower( $hour->time_open ) ];
					}

				}
			}

			if( sizeof( $day[ 'hours' ] ) > 1 ){
				$days[] = $day;
				if( !$timeToTime ){
					if( $imutable != $now->format( 'Ymd' ) ){
						$timeToTime = $now->format( 'D ' );;
						if($day[ 'hours' ][ 0 ][ 'value' ] !== false){
							$timeToTime .= trim( $day[ 'hours' ][ 0 ][ 'value' ] );
						} else {
							$timeToTime .= trim( $day[ 'hours' ][ 1 ][ 'value' ] );
						}
					} else {
						if($day[ 'hours' ][ 0 ][ 'value' ] !== false){
							$timeToTime .= trim( $day[ 'hours' ][ 0 ][ 'value' ] );
						} else {
							$timeToTime .= trim( $day[ 'hours' ][ 1 ][ 'value' ] );
						}
					}
				}
			}
			$now->modify( '+ 1 day' );
		}

		if( $timeToTime ){
			$this->preOrderTimeToTime = $timeToTime;
		}

		$this->_preOrderHours = $days;

		return $this->_preOrderHours;
	}

	public function preOrderProcessHours( $hours ){

		$_hours = [];
		$_segments = [];

		if( !$this->delivery_service ){
			$_hours = [];
			$now = new DateTime( 'now', new DateTimeZone( $this->timezone ) );
			$weekdays = [ 'mon' => 'Monday', 'tue' => 'Tuesday', 'wed' => 'Wednesday', 'thu' => 'Thursday', 'fri' => 'Friday', 'sat' => 'Saturday', 'sun' => 'Sunday' ];
			foreach( $hours as $hour ){
				if( $now->format( 'l' ) == $weekdays[ $hour->day ] ){
					$date = clone $now;
					$deliveryEstimate = clone $now;
					$time = explode( ':', $hour->time_open );
					$date->setTime( $time[ 0 ], $time[ 1 ] );
					$deliveryEstimate->modify( '+ ' . $this->delivery_estimated_time . ' minutes' );
					if( $date < $deliveryEstimate ){
						$minutes = round( $deliveryEstimate->format( 'i' ) / 10, 0 )  * 10;
						if( $minutes >= 60 ){
							$minutes -= 60;
							$deliveryEstimate->modify( '+ 1 hour' );
						}
						$hour->time_open = $deliveryEstimate->format( 'H' ) . ':' . $minutes;
					}
				} else {
					$date = new DateTime( 'next ' . $hour->day, new DateTimeZone( $this->timezone ) );
				}
				$hour = ( object ) [ 'day' => $hour->day, 'time_open' => $hour->time_open, 'time_close' => $hour->time_close, 'date' => $date->format( 'Y-m-d' ) ];
				$_hours[] = $hour;
			}
			$hours = $_hours;
		}

		$today = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
		$today->setTimezone( new DateTimeZone( $this->timezone ) );

		foreach( $hours as $hour ){

			$interval = Crunchbutton_Order::PRE_ORDER_DELIVERY_WINDOW;

			if( $hour->day == strtolower( $today->format( 'D' ) ) ){
				$hour->date = $today->format( 'Y-m-d' );
			}

			$now = new DateTime( 'now', new DateTimeZone( $this->timezone ) );
			$open = new DateTime( $hour->date . ' ' . $hour->time_open, new DateTimeZone( $this->timezone ) );
			$close = new DateTime( $hour->date . ' ' . $hour->time_close, new DateTimeZone( $this->timezone ) );
			$count = 0;

			if($this->delivery_service){
				$minutes = $this->preorderMinAfterCommunityOpen();
			} else {
				$minutes = $this->delivery_estimated_time + 5;
			}


			$open->modify( "+ $minutes minutes" );

			if( $now->format( 'Ymd' ) == $open->format( 'Ymd' ) ){
				$eta = 5 * ceil( $this->smartEta() / 5 );
				$minutes =  5 - ( $now->format( 'i' ) % 5 );
				$now->modify( '+ ' . ( $eta + $minutes ) . ' minutes' );
				// $open
				if( $now > $open ){
					$open = $now;
				}
			}

			if( $now->format( 'Ymd' ) == $close->format( 'Ymd' ) ){
				if( $now >= $close ){
					continue;
				}
			}

			$i_open = intval( $open->format( 'YmdHi' ) );
			$i_close = intval( $close->format( 'YmdHi' ) );

			$start = clone $open;

			$count = 0;
			$continue = true;
			while( $continue ){

				$end = clone $start;
				$end->modify( $interval );

				if( $i_close >= intval( $end->format( 'YmdHi' ) ) ){
					$_hour = clone $hour;
					$_hour->time_open = $start->format( 'h:i A' );
					$_hour->time_close = $end->format( 'h:i A' );
					// $_hour->i_time_open = intval( $start->format( 'YmdHi' ) );
					// $_hour->i_time_close = intval( $end->format( 'YmdHi' ) );
					$_segments[] = $_hour;
					$start = $end;
				}
				// dogwatch
				$count++;
				if( $count >= 96 ){
					$continue = false;
				}

			}
		}
		return $_segments;
	}

	public function allowPreorder(){
		$community = $this->community()->get( 0 );
		if( $community->allRestaurantsClosed() || $community->allThirdPartyDeliveryRestaurantsClosed() ){
			return false;
		}
		return $community->allow_preorder && $this->allow_preorder;
	}

	public function preorderMinAfterCommunityOpen(){
		if( $this->allowPreorder() ){
			$community = $this->community()->get( 0 );
			return $community->preorderMinAfterCommunityOpen();
		}
		return null;
	}

	public function preOrderDays(){
		if( $this->allowPreorder() ){
			$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
			$days = [];
			$days[] = [ 'value' => $now->format( 'Y-m-d' ), 'label' => $now->format( 'D M d ' ) . '(Today)' ];
			for( $i = 1; $i <= 4; $i++ ){
				$now->modify( '+ 1 day' );
				$label = $now->format( 'D M d' );
				if( $i == 1 ){
					$label .= ' (Tomorrow)';
				}
				$days[] = [ 'value' => $now->format( 'Y-m-d' ), 'label' => $label  ];
			}
			return $days;
		}
		return null;
	}

	/**
	 * Returns an array with all the information for a Restaurant.
	 *
	 * This is usually used to JSON encode and send to the browser
	 *
	 * @param array $ignore An indexed array of what items not to ad to the export array
	 * @param array $where  Adds a layer to filter the SQL WHERE statements
	 *
	 * @return array
	 */
	public function exports($ignore = [], $where = []) {

		$isCockpit = Crunchbutton_Util::isCockpit();

		$out = $this->properties();
		if( count( $ignore ) && is_array( $ignore ) ){
			$_ignore = $ignore;
			foreach ( $_ignore as $property ) {
				$ignore[ $property ] = true;
			}
		}

		// method ByRand doesnt need all the properties
		if( $this->byrange ){
			$_ignore = [ 'type', 'credit','address','max_items','tax','phone','fee_restaurant','fee_customer','delivery_min','delivery_min_amt','notes_todo','pickup_estimated_time','delivery_fee','delivery_estimated_time','notes_owner','confirmation','zip','customer_receipt','cash','giftcard','email','notes','balanced_id','balanced_bank','fee_on_subtotal','payment_method','charge_credit_fee','waive_fee_first_month','max_pay_promotion','pay_apology_credits','check_address','contact_name','summary_fax','summary_email','summary_frequency','legal_name_payment','tax_id','community','_preset','id_community', '_hoursFormat', 'loc_long', 'loc_lat', 'weight_adj', 'pay_promotions', 'promotion_maximum', 'summary_method', 'max_apology_credit', 'order_notifications_sent', 'confirmation_type', 'notes_to_driver', 'delivery_radius_type', 'timezone', 'image', 'delivery_area_notes', 'message', 'balanced_id', 'balanced_bank', 'notifications', 'notes_owner', 'notes', 'email' ];
			foreach ( $_ignore as $property ) {
				$ignore[ $property ] = true;
			}
		}

		// front end restaurant's page
		if( $this->restaurant_page ){
			$_ignore = [ 'notes_todo', 'notes_owner', 'confirmation', 'zip', 'customer_receipt', 'email', 'notes', 'balanced_id', 'balanced_bank', 'weight_adj', 'message', 'waive_fee_first_month', 'pay_promotions', 'pay_apology_credits', 'check_address', 'contact_name', 'summary_fax', 'summary_email', 'summary_frequency', 'legal_name_payment', 'tax_id', 'promotion_maximum', 'summary_method', 'max_apology_credit', 'order_notifications_sent', 'confirmation_type', 'notes_to_driver', 'order_ahead_time', 'service_time', 'notifications', 'notes_owner', 'notes', 'email' ];
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

		// See - #4250
		if( !$isCockpit ){
			$thirdPartyClosed = false;
			if( strrpos( $this->permalink, 'drive-' ) !== false && $community && $community->get( 0 ) ){
				$thirdPartyClosed = $community->get( 0 )->allThirdPartyDeliveryRestaurantsClosed();
				if( $thirdPartyClosed ){
					$out[ 'name' ] = $community->get( 0 )->close_3rd_party_delivery_restaurants_note;
				}
			}
		}

		$timezone = new DateTimeZone( $this->timezone );
		$date = new DateTime( 'now ', $timezone ) ;

		if (!$ignore['images']) {
			$out['images'] = $this->getImages();
			$out['img']    = $out['images']['normal'];

		}

		if (!$ignore['categories']) {
			foreach ($this->categories() as $category) {
				$out['_categories'][] = $category->exports($where);
			}
			// To make sure it will be ignored at cockpit
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
								$price = self::roundDeliveryMarkupPrice( $price );
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
									$price = self::roundDeliveryMarkupPrice( $price );
									$out[ '_categories' ][ $i ][ '_dishes' ][ $j ][ '_options' ][ $k ][ 'price' ] = $price;
									$out[ '_categories' ][ $i ][ '_dishes' ][ $j ][ '_options' ][ $k ][ 'markup' ] = number_format( $price - $price_original, 2 );
								// }
							}
						}
					}
				}
			}
		}

		if (!$ignore['notifications']) {
			$where = [];
			if ( $isCockpit ) {
				$where['active'] = NULL;
			}
			foreach ($this->notifications($where) as $notification) {
				/* @var $notification Crunchbutton_Notification */
				$out['_notifications'][$notification->id_notification] = $notification->exports();
			}
		}

		// change how we do open calculations #6902
		if (!$ignore['hours']) {

			if( $this->force_hours_calculation ){

				// Return the offset to help the Javascript to calculate the open/close hour correctly
				$out['_tzoffset'] = ( $date->getOffset() ) / 60 / 60;
				$out['_tzabbr'] = $date->format('T');

				if( $isCockpit ){

					$payment_type = $this->payment_type();
					$out[ 'payment_method' ] = $payment_type->payment_method;

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

			}
			else {
				$_time = Crunchbutton_Restaurant_Time::getTime( $this->id_restaurant );
				// Return the offset to help the Javascript to calculate the open/close hour correctly
				$out['_tzoffset'] = $_time[ 'tzoffset' ];
				$out['_tzabbr'] = $_time[ 'tzabbr' ];

				if( $isCockpit ){

					$payment_type = $this->payment_type();
					$out[ 'payment_method' ] = $payment_type->payment_method;

					foreach ($this->hours() as $hours) {
						$out['_hours'][$hours->day][] = [$hours->time_open, $hours->time_close];
					}
				} else {
					$out[ 'hours' ] = $_time[ 'hours_next_24_hours' ];
					if( $_time[ 'next_open_time_message_utc' ] ){
						$out[ 'next_open_time' ] = $_time[ 'next_open_time_utc' ];
						$out[ 'next_open_time_message' ] = $_time[ 'next_open_time_message' ];
					}
				}
				$out['closed_message'] = $_time[ 'closed_message' ];
			}
		}

		if (!$ignore['_preset']) {
			if ($this->preset()->count()) {
				$out['_preset'] = $this->preset()->get(0)->exports();
			}
		}

		$out['id_community'] = intval( $this->community()->id_community );

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

		if( !$community->display_eta && !Crunchbutton_Util::isCockpit() ){
			$ignore['eta'] = true;
		}

		// start eta
		if (!$ignore['eta']) {
			$out['eta'] = $this->smartETA();
		}

		$money_value = [ 'delivery_fee' ];
		foreach ( $money_value as $key ) {
			if( $out[ $key ] ){
				$out[ $key ] = floatval( number_format( $out[ $key ], 2 ) );
			}
		}

		// change its lat and long to the community's
		if( !$isCockpit && $this->delivery_radius_type == 'community' ){
			$community = $this->community();
			$out[ 'loc_lat' ] = $community->loc_lat;
			$out[ 'loc_long' ] = $community->loc_lon;
		}

		if (!$ignore['hours']) {
			$out[ '_open' ] = $this->open();
		}

		// Remove ignored methods
		if( count( $ignore ) ){
			foreach ( $ignore as $property => $val ) {
				unset( $out[ $property ] );
			}
		}

		if( $this->campusCash() ){
			$out[ 'campus_cash' ] = true;
			$out[ 'campus_cash_name' ] = $this->campusCashName();
			$out[ 'campus_cash_fee' ] = $this->campusCashFee();
			$out[ 'campus_cash_mask' ] = $this->campusCashMask();
			$out[ 'campus_cash_delivery_on_campus_confirmation' ] = $this->campusCashDeliveryLocatedOnCampus();
			$out[ 'campus_cash_default_payment' ] = $this->campusCashDefaultPaymentMethod();
		} else {
			$out[ 'campus_cash' ] = false;
		}

		if(!$out[ '_open' ] && !$out[ 'hours' ] && $this->reopen_for_business_at){
			$out[ 'reopen_tomorrow' ] = true;
		}
		return $out;
	}

	public function timeInfo(){

		$out = [ 'id_restaurant' => $this->id_restaurant, 'name' => $this->name, 'permalink' => $this->permalink ];

		$timezone = new DateTimeZone( $this->timezone );
		$date = new DateTime( 'now ', $timezone ) ;

		if( $this->force_hours_calculation ){

			// Return the offset to help the Javascript to calculate the open/close hour correctly
			$out['_tzoffset'] = ( $date->getOffset() ) / 60 / 60;
			$out['_tzabbr'] = $date->format('T');

			if( $isCockpit ){
				$payment_type = $this->payment_type();
				$out[ 'payment_method' ] = $payment_type->payment_method;

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

		}
		else {
			$_time = Crunchbutton_Restaurant_Time::getTime( $this->id_restaurant );
			// Return the offset to help the Javascript to calculate the open/close hour correctly
			$out['_tzoffset'] = $_time[ 'tzoffset' ];
			$out['_tzabbr'] = $_time[ 'tzabbr' ];

			if( $isCockpit ){

				$payment_type = $this->payment_type();
				$out[ 'payment_method' ] = $payment_type->payment_method;

				foreach ($this->hours() as $hours) {
					$out['_hours'][$hours->day][] = [$hours->time_open, $hours->time_close];
				}
			} else {
				$out[ 'hours' ] = $_time[ 'hours_next_24_hours' ];
				if( $_time[ 'next_open_time_message_utc' ] ){
					$out[ 'next_open_time' ] = $_time[ 'next_open_time_utc' ];
					$out[ 'next_open_time_message' ] = $_time[ 'next_open_time_message' ];
				}
			}

			$out['closed_message'] = $_time[ 'closed_message' ];
		}
		return $out;
	}

	// See #4323
	public static function roundDeliveryMarkupPrice( $price ){
		$nearests = [ 29, 49, 79, 99 ];
		$price = number_format( $price, 2 );
		if( $price > 0 ){
			$price .= '';
			$cents = 0;
			$price = explode( '.' ,  $price );
			if( $price[ 1 ] ){
				$cents = intval( $price[ 1 ] );
				foreach( $nearests as $nearest ){
					if( $nearest > $cents ){
						$cents = $nearest;
						break;
					}
				}
			}
			$price = floatval( $price[ 0 ] . '.' . $cents );
		}
		return $price;
	}

	public function assignedShiftHours( $allDay = false ){
		$community = $this->community()->get( 0 );
		if( $community->id_community ){
			return $community->assignedShiftHours( $allDay );
		}
		return false;
	}

	public function isCommunityAutoClosed(){
		$community = $this->community()->get( 0 );
		if( $community->id_community && $community->isAutoClosed() ){
			return true;
		}
		return false;
	}

	public function hasDeliveryService(){
		// At first check the delivery_service
		if( $this->delivery_service ){
			return 1;
		}
		/*
		// Second, check if it has an admin active notification
		$type_admin = Crunchbutton_Notification::TYPE_ADMIN;
		$notification = Notification::q( "SELECT n.* FROM notification n WHERE n.id_restaurant = {$this->id_restaurant} AND n.active = true AND n.type = '{$type_admin}' LIMIT 1");
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

		$payment_type = $this->payment_type();
		$payment_type->summary_method = $restaurant['summary_method'];
		$payment_type->payment_method = $restaurant['payment_method'];
		$payment_type->save();

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
					$sql = 'SELECT * FROM category WHERE name = ? AND id_restaurant = ? ORDER BY sort ASC LIMIT 1';
					$c = Crunchbutton_Category::q($sql, [$category['name'], $restaurant[ 'id_restaurant']]);
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
			$this->_ratingCount = Order::q('select count(*) as c from `order` where id_restaurant=? and env=?', [$this->id_restaurant, 'live'])->c;
		}
		return $this->_ratingCount;
	}

	public static function byRange($params) {

		$params['range'] = $params['range'] ? $params['range'] : 2;

		$range = floatval($params['range']);
		$rangeDif = $range - 2;
		$lat = floatval($params['lat']);
		$lon = floatval($params['lon']);

		$locCast = function($loc) {
			return 'CAST('.$loc.' as DECIMAL(19,15))';
		};

		$formula = '( ( ACOS( SIN( %F * PI() / 180 ) * SIN( %s * PI() / 180 ) + COS( %F * PI() / 180 ) * COS( %s * PI() / 180 ) * COS( ( %F - %s ) * PI() / 180 ) ) * 180 / PI() ) * 60 * 1.1515 )';

		$regular_calc = sprintf( $formula, $lat, $locCast('loc_lat'), $lat, $locCast('loc_lat'), $lon, $locCast('loc_long') );

		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
		$now->modify( '- 30 day' );
		$interval = $now->format( 'Y-m-d' ) . ' 00:00:00';

		$query = "
			SELECT
				count(*) as _weight,
				".$locCast('restaurant.loc_lat')." as loc_lat,
				".$locCast('restaurant.loc_long')." as loc_long,
				'byrange' as type,
				{$regular_calc} AS distance,
				restaurant.*
			FROM restaurant
				LEFT JOIN `order` o ON o.id_restaurant = restaurant.id_restaurant AND o.date > '{$interval}'
				WHERE
					active = true AND delivery_radius_type = 'restaurant'
				GROUP BY restaurant.id_restaurant
				HAVING
						takeout = true
					AND
						delivery = false
					AND
						{$regular_calc} <= {$range}
					OR
						delivery = true
					AND
						{$regular_calc} <= (delivery_radius + {$rangeDif} ) ";

		$query .= " UNION ";

		$community_calc = sprintf( $formula, $lat, $locCast('max(c.loc_lat)'), $lat, $locCast('max(c.loc_lat)'), $lon, $locCast('max(c.loc_lon)') );
		$restaurant_calc = sprintf( $formula, $lat, $locCast('max(r.loc_lat)'), $lat, $locCast('max(r.loc_lat)'), $lon, $locCast('max(r.loc_long)') );

		$query .= "
				SELECT
					count(*) as _weight,
					".$locCast('max(c.loc_lat)')." AS loc_lat,
					".$locCast('max(c.loc_lon)')." AS loc_long,
					'byrange' as type,
					{$restaurant_calc} AS distance,
					r.*
				FROM restaurant r
				LEFT JOIN `order` o ON o.id_restaurant = r.id_restaurant AND o.date > '{$interval}'
				INNER JOIN restaurant_community rc ON rc.id_restaurant = r.id_restaurant
				INNER JOIN community c ON c.id_community = rc.id_community
				WHERE
					r.active = true AND r.delivery_radius_type = 'community' AND c.active = true
				GROUP BY r.id_restaurant
				 HAVING
						r.takeout = true
					AND
						r.delivery = false
					AND
						{$community_calc} <= {$range}
					OR
						delivery = true
					AND
						{$community_calc} <= (delivery_radius + {$rangeDif} ) ";

		$query .= " ORDER BY _weight DESC; ";

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
		$phone = Notification::q( 'SELECT * FROM notification WHERE id_restaurant = ? AND active = true and type = ? ', [$this->id_restaurant, Crunchbutton_Notification::TYPE_PHONE]);
		if( $phone->id_notification ){
			return true;
		} else {
			return false;
		}
	}

	public function hasFaxNotification(){
		$fax = Notification::q( 'SELECT * FROM notification WHERE id_restaurant = ? AND active = true and ( type = ? OR type = ?)', [$this->id_restaurant, Crunchbutton_Notification::TYPE_FAX, Crunchbutton_Notification::TYPE_STEALTH]);
		if( $fax->id_notification ){
			return true;
		} else {
			return false;
		}
	}

	public static function getCommunities(){
		$data = c::db()->get( 'SELECT DISTINCT( community ) community FROM restaurant WHERE community IS NOT NULL AND community != "" AND active = true ORDER BY community ASC' );
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
			$where = 'AND active = true';
		}
		return Crunchbutton_Restaurant::q('SELECT * FROM restaurant WHERE community = ? '.$where.' ORDER BY name ASC', [$community]);
	}

	public static function getDeliveryRestaurantsWithGeoByIdCommunity( $id_community, $inactive = false ){
		if($inactive){
			return Crunchbutton_Restaurant::q('select a.* from restaurant as a inner join restaurant_community as b using (id_restaurant) inner join community as c using (id_community) where id_community = ?  and a.delivery_service=1', [$id_community]);
		}
		else{
			return Crunchbutton_Restaurant::q('select a.* from restaurant as a inner join restaurant_community as b using (id_restaurant) inner join community as c using (id_community) where id_community = ?  and a.delivery_service=1 and a.active = true;', [$id_community]);
		}
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
		$sendSMSTo = [];
		$usersToReceiveSMS = Admin::q( $query );
		foreach( $usersToReceiveSMS as $user ){
			if( $user->isWorking() ){
				$sendSMSTo[ $user->name ] = $user->txt;
			}
		}
		return $sendSMSTo;
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
			$this->_comment = Restaurant_Comment::q('select * from restaurant_comment where top=true AND id_restaurant=?', [$this->id_restaurant]);
		}
		return $this->_comment;
	}

	public function calc_pickup_estimated_time( $datetime = null, $dateObject = false ){
		$multipleOf = 15;
		$time = new DateTime( ( $datetime ? $datetime : 'now' ), new DateTimeZone( $this->timezone ) );
		$minutes = round( ( ( $time->format( 'i' ) + $this->pickup_estimated_time ) + $multipleOf / 2 ) / $multipleOf ) * $multipleOf;
		$minutes -= $time->format( 'i' );
		if( $dateObject ){
			$time->modify( ' + ' . $minutes . ' minutes' );
			return $time;
		}
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

	public function calc_delivery_estimated_time( $datetime = null, $dateObject = false ){
		$time = new DateTime( ( $datetime ? $datetime : 'now' ), new DateTimeZone( $this->timezone ) );
		if( $this->smartETA() ){
			$minutes = $this->smartETA();
		} else {
			$multipleOf = 15;
			$minutes = round( ( ( $time->format( 'i' ) + $this->delivery_estimated_time ) + $multipleOf / 2 ) / $multipleOf ) * $multipleOf;
			$minutes -= $time->format( 'i' );
		}
		if( $dateObject ){
			$time->modify( ' + ' . $minutes . ' minutes' );
			return $time;
		}
		return date( 'g:i a', strtotime( $time->format( 'Y-m-d H:i' ) . ' + ' . $minutes . ' minute' ) );
	}

	public function payment_type(){
		if( !$this->_payment_type ){
			$this->_payment_type = Crunchbutton_Restaurant_Payment_Type::byRestaurant( $this->id_restaurant );
		}
		return $this->_payment_type;
	}

	public function save($new = false) {
		if (!$this->timezone) {
			$this->timezone = Crunchbutton_Community_Shift::CB_TIMEZONE;
		}
		if(!$this->delivery_estimated_time){
			$this->delivery_estimated_time = 45;
		}
		$this->phone = Phone::clean($this->phone);
		return parent::save();
	}

	public function load($stuff = null) {
		parent::load($stuff);
		if (!$this->timezone) {
			$this->timezone = c::config()->timezone;
		}
		return $this;
	}

	public function drivers(){
		return Admin::q("SELECT DISTINCT( a.id_admin ) id, a. * FROM admin a INNER JOIN notification n ON a.id_admin = n.id_admin AND n.id_restaurant = ? AND n.active = true AND n.type = ?", [$this->id_restaurant, Crunchbutton_Notification::TYPE_ADMIN]);
	}

	public function withDrivers(){
		return Restaurant::q("SELECT DISTINCT(r.id_restaurant) id, r.* FROM restaurant r INNER JOIN notification n ON r.id_restaurant = n.id_restaurant AND n.type = '" . Crunchbutton_Notification::TYPE_ADMIN . "' WHERE r.name NOT LIKE '%test%' ORDER BY r.name");
	}

	public function totalOrders(){
	$query = "SELECT
							 COUNT(*) AS Total
						FROM `order` o
						WHERE id_restaurant = {$this->id_restaurant}";
		$result = c::db()->get( $query );
		return $result->_items[0]->Total;
	}

	/*
	* Hours and Open/Closed methods
	*/

	// return the restaurant's hours
	public function hours( $gmt = false ) {

		$isCockpit = Crunchbutton_Util::isCockpit();

		if( !$isCockpit ){
			// check if the community is closed #2988
			$community = $this->community()->get(0);
			if( $community->id_community ){
				$allRestaurantsClosed = $community->allRestaurantsClosed();
				if( $allRestaurantsClosed ){
					return [];
				}

				$allThirdPartyDeliveryRestaurantsClosed = $community->allThirdPartyDeliveryRestaurantsClosed();
				if( $this->delivery_service && $allThirdPartyDeliveryRestaurantsClosed ){
					return [];
				}
			}
		}

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

		// if it is a driver restaurant it will return open - #5371
		if( $this->isDriverRestaurant() ){
			return true;
		}

		// restaurant without hours is not open
		if( count( $this->hours() ) == 0 ){
			return false;
		}

		// Calculate the hours to verify if it is open or not
		return Hour::restaurantIsOpen( $this, $dt );
	}

	public function isDriverRestaurant(){
		$restaurant = Crunchbutton_Community::q( 'SELECT * FROM community WHERE id_driver_restaurant = ' . $this->id_restaurant . ' LIMIT 1' )->get( 0 );
		if( $restaurant->id_driver_restaurant == $this->id_restaurant ){
			return true;
		}
		return false;
	}

	// return the closed message
	public function closed_message(){
		$closed_message = Hour::restaurantClosedMessage( $this );
		if( trim( $closed_message ) == '' ){
			// check if the community is closed #2988
			$community = $this->community()->get(0);
			if( $community->id_community ){
				if( $community->allRestaurantsClosed() ){
					$closed_message = $community->close_all_restaurants_note;
				}
				$allThirdPartyDeliveryRestaurantsClosed = $community->allThirdPartyDeliveryRestaurantsClosed();
				if( !$closed_message && $this->delivery_service && $allThirdPartyDeliveryRestaurantsClosed ){
					$closed_message = $community->close_3rd_party_delivery_restaurants_note;
				}
				if( !$closed_message && $this->delivery_service && $community->is_auto_closed ){
					$closed_message = $community->driver_restaurant_name;
				}
			}

		}
		return $closed_message;
	}

	public function next_open_time_message( $utc = false ){
		if( $this->open_for_business ){
			// if the restaurant is open return false
			// if ( $this->closed() || ( !$this->closed() && $this->closesIn() <= 60 ) ) {
				return Hour::restaurantNextOpenTimeMessage( $this, $utc );
			// }
		}
		return false;
	}

	// Return the next open time
	public function next_open_time( $utc = false ){
		if( $this->open_for_business ){
			// if the restaurant is open return false
			// if ( $this->closed() || ( !$this->closed() && $this->closesIn() <= 60 ) ) {
				return Hour::restaurantNextOpenTime( $this, $utc );
			// }
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
			if( $this->isDriverRestaurant() ){
				// #5371
				$now = new DateTime( 'now', new DateTimeZone( ( $gmt ? 'UTC' : $this->timezone ) ) );
				$now->modify( '- 10 hours' );
				$out = [];
				$start = $now->format( 'Y-m-d H:i' );
				$now->modify( '+ 1 day' );
				$end = $now->format( 'Y-m-d H:i' );
				$out[] = [ 'from' => $start, 'to' => $end, 'status' => 'open' ];
				return $out;
			} else {
				// check if the community is forced closed or auto closed
				$community = $this->community()->get(0);
				if( $community->id_community ){
					if( $community->allRestaurantsClosed() ){
						return [];
					}
					if( $this->delivery_service && $community->allThirdPartyDeliveryRestaurantsClosed() ){
						return [];
					}
				}
				return Hour::getByRestaurantNext24Hours( $this, $gmt );
			}
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
		die('deprecated #5584');
		$query = "SELECT SUM(1) orders, DATE_FORMAT( o.date, '%m/%d/%Y' ) day FROM `order` o
					INNER JOIN restaurant r ON r.id_restaurant = o.id_restaurant AND r.community = '$community'
					WHERE o.date > DATE_SUB(CURDATE(), INTERVAL $days DAY) AND o.name NOT LIKE '%test%' GROUP BY day ORDER BY o.date ASC";
		return c::db()->get( $query );
	}


	public function duplicate(){

		$self = $this;
		$restaurant = new Restaurant;
		$restaurant->save();
		$id_restaurant = $restaurant->id_restaurant;
		$restaurant = Restaurant::o( $id_restaurant );
		foreach( $self->_properties as $property => $value ){
			if( !in_array(  $property, [ 'id_restaurant', 'id', 'permalink', 'community', 'phone', 'address', 'balanced_id', 'balanced_bank', 'tax', 'loc_lat', 'loc_long' ] ) ){
				$restaurant->$property = $value;
			}
		}
		$restaurant->permalink = 'restaurant-' . $id_restaurant;
		$restaurant->name = $self->name . ' - duplicated';
		$restaurant->notes_todo = 'Duplicated from ' . $self->name . ' (' . $self->id_restaurant . ')';
		$restaurant->active = 0;
		$restaurant->open_for_business = 0;
		$restaurant->save();
		$id_restaurant = $restaurant->id_restaurant;

		// Hours
		// don't duplicate hours: https://github.com/crunchbutton/crunchbutton/issues/3446#issuecomment-59111100
		/**
		$hours = Crunchbutton_Hour::q( "SELECT * FROM hour WHERE id_restaurant = {$self->id_restaurant}" );
		foreach( $hours as $_hour ){
			$hour = new Crunchbutton_Hour;
			$hour->id_restaurant = $id_restaurant;
			$hour->day = $_hour->day;
			$hour->time_open = $_hour->time_open;
			$hour->time_close = $_hour->time_close;
			$hour->save();
		}
		**/

		// Categories
		$categories_map = [];
		$_categories = Crunchbutton_Category::q('SELECT * FROM category WHERE id_restaurant=?', [$self->id_restaurant]);
		foreach( $_categories as $_category ){
			$category = new Crunchbutton_Category;
			$category->id_restaurant = $id_restaurant;
			$category->name = $_category->name;
			$category->sort = $_category->sort;
			$category->loc = $_category->loc;
			$category->save();
			$categories_map[ $_category->id_category ] = $category->id_category;
		}

		// Dishes
		$dishes_map = [];
		$dishes = $self->dishes();
		foreach( $dishes as $_dish ){
			$dish = new Crunchbutton_Dish;
			foreach( $_dish->_properties as $property => $value ){
				$dish->$property = $value;
			}
			foreach( [ 'id_dish', 'id', 'id_category' ] as $remove ){
				$dish->$remove = null;
			}
			$dish->id_category = $categories_map[ $_dish->id_category ];
			$dish->id_restaurant = $id_restaurant;
			$dish->save();
			$dishes_map[ $_dish->id_dish ] = $dish->id_dish;
		}

		// Options
		$options_map = [];
		$_options = Crunchbutton_Option::q('SELECT * FROM `option` WHERE id_restaurant=?', [$self->id_restaurant]);
		foreach( $_options as $_option ) {
			$option = new Crunchbutton_Option;
			foreach( $_option->_properties as $property => $value ){
				$option->$property = $value;
			}
			foreach( [ 'id_option', 'id', 'id_category' ] as $remove ){
				$option->$remove = null;
			}
			$option->id_restaurant = $id_restaurant;
			$option->id_category = $categories_map[ $_option->id_category ];
			$option->save();
			$options_map[ $_option->id_option ] = $option->id_option;
		}

		// Fix the id_option_parent (option)
		$_options = Crunchbutton_Option::q('SELECT * FROM `option` WHERE id_restaurant=?', [$id_restaurant]);
		foreach( $_options as $option ){
			if( $option->id_option_parent ){
				$option->id_option_parent = $options_map[ $option->id_option_parent ];
				$option->save();
			}
		}

		// Dish options
		$_dish_options = Crunchbutton_Dish_Option::q('SELECT do.* FROM dish_option do INNER JOIN dish d ON d.id_dish = do.id_dish AND d.id_restaurant = ?',[$self->id_restaurant] );
		foreach( $_dish_options as $_dish_option ){
			$dish_option = new Crunchbutton_Dish_Option;
			$dish_option->id_dish = $dishes_map[ $_dish_option->id_dish ];
			$dish_option->id_option = $options_map[ $_dish_option->id_option ];
			$dish_option->default = $_dish_option->default;
			$dish_option->sort = $_dish_option->sort;
			$dish_option->date = $_dish_option->date;
			$dish_option->save();
		}

		// Payment type
		$_payment = Crunchbutton_Restaurant_Payment_Type::q( 'SELECT * FROM restaurant_payment_type WHERE id_restaurant=? ORDER BY id_restaurant_payment_type DESC', [$self->id_restaurant]);
		if( $_payment->id_restaurant_payment_type ){
			$payment = new Crunchbutton_Restaurant_Payment_Type;
			foreach( $_payment->_properties as $property => $value ){
				$payment->$property = $value;
			}
			foreach( [ 'id_restaurant', 'id', 'tax_id', 'stripe_id', 'stripe_account_id', 'balanced_id', 'balanced_bank' ] as $remove ){
				$payment->$remove = null;
			}
			$payment->id_restaurant = $id_restaurant;
			$payment->save();
		}
		return $id_restaurant;
	}

	// temporary function. should calculate or return a better value in the future
	public function lineTime() {
		return 11;
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

		public function parking($time, $dow) {
				$qString = "SELECT * FROM order_logistics_parking WHERE id_restaurant= ? and "
						."time_start_community <= ? and time_end_community > ? and day_of_week = ?";
				$parking = Crunchbutton_Order_Logistics_Parking::q($qString, [$this->id_restaurant, $time, $time, $dow]);
				if (is_null($parking) || $parking->count()==0){
						return null;
				} else{
						return $parking->get(0);
				}
		}

	public function service($time, $dow) {
		$qString = "SELECT * FROM order_logistics_service WHERE id_restaurant= ? and "
			."time_start_community <= ? and time_end_community > ? and day_of_week = ?";
		$parking = Crunchbutton_Order_Logistics_Service::q($qString, [$this->id_restaurant, $time, $time, $dow]);
		if (is_null($parking) || $parking->count()==0){
			return null;
		} else{
			return $parking->get(0);
		}
	}

		public function ordertime($time, $dow) {
				$qString = "SELECT * FROM order_logistics_ordertime WHERE id_restaurant= ? and "
						."time_start_community <= ? and time_end_community > ? and day_of_week = ?";
				$ot = Crunchbutton_Order_Logistics_Ordertime::q($qString, [$this->id_restaurant, $time, $time, $dow]);
				if (is_null($ot) || $ot->count()==0){
						return null;
				} else{
						return $ot->get(0);
				}
		}

		public function cluster($time, $dow) {
				$qString = "SELECT * FROM order_logistics_cluster WHERE id_restaurant= ? and "
						."time_start_community <= ? and time_end_community > ? and day_of_week = ?";
				$olc = Crunchbutton_Order_Logistics_Cluster::q($qString, [$this->id_restaurant, $time, $time, $dow]);
				// Clear out the cluster that day just in case
				if (is_null($olc) || $olc->count()==0){
						$qString = "SELECT * FROM `order_logistics_cluster` WHERE id_restaurant= ? and "
								."day_of_week = ?";
						$olc = Crunchbutton_Order_Logistics_Cluster::q($qString, [$this->id_restaurant, $dow]);
						if (!is_null($olc) && $olc->count()==0) {
								$olc->delete();
						}
						$olc = new Crunchbutton_Order_Logistics_Cluster([
								'id_restaurant_cluster' => $this->id_restaurant,
								'id_restaurant' => $this->id_restaurant,
								'time_start_community' => '00:00:00',
								'time_end_community' => '24:00:00',
								'day_of_week' => $dow
						]);
						$olc->save();
						return $olc;
				} else{
						return $olc->get(0);
				}
		}

	public function validatePreOrderDate($date){
		$date = new DateTime($date, new DateTimeZone(c::config()->timezone));
		$date->setTimezone(new DateTimeZone($this->timezone));
		$now = new DateTime('now', new DateTimeZone($this->timezone));
		if($date < $now){
			return false;
		}

		$hours = Hour::hoursByRestaurant($this);

		$open = false;
		$day = strtolower($date->format('D'));
		foreach($hours as $hour){
			if($hour->day == $day){
				$time_open = new DateTime($date->format('Y-m-d ') . $hour->time_open, new DateTimeZone($this->timezone));
				$time_close = new DateTime($date->format('Y-m-d ') . $hour->time_close, new DateTimeZone($this->timezone));
				if($time_open->format('YmdHi') <= $date->format('YmdHi') && $time_close->format('YmdHi') >= $date->format('YmdHi')){
					$open = true;
				}
			}
		}

		if(!$open){
			return false;
		}

		// delivered by CB
		if ($this->delivery_service) {
			// check if there is a shift
			$next = clone $date;
			// some shifts start at one day and finish at the next one
			$next->modify('+ 1 day');
			$shifts = Community_Shift::q('SELECT * FROM community_shift WHERE date_start >= ? AND date_end <= ? AND id_community = ? AND active = true',
																		[$date->format('Y-m-d'), $next->format('Y-m-d 23:59:59' ), $this->community()->id_community]);

			if($shifts->count()){
				foreach($shifts as $shift)	{
					if($shift->dateStart()->format('YmdHi') <= $date->format('YmdHi') && $shift->dateEnd()->format('YmdHi') >= $date->format('YmdHi')){
						return true;
					}
				}
			}
			return false;
		} else {
			return true;
		}
	}

	public static function selectFakeRestaurant($id_community) {
		$fr = null;
		// Randomly choose a restaurant from the community list
		$rs = Crunchbutton_Restaurant::getDeliveryRestaurantsWithGeoByIdCommunity($id_community);
		$rcount = $rs->count();
		if ($rcount > 0) {
			$select = rand(0, $rcount - 1);
			$fr = $rs->get($select);
		}
		return $fr;
	}

}
