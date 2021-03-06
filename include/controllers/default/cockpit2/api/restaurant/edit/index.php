<?php

class Controller_api_restaurant_edit extends Crunchbutton_Controller_RestAccount {

	public function init() {
		
		if( c::getPagePiece( 3 ) == 'new' ){
			return $this->_new();
		}

		$restaurant = Restaurant::permalink( c::getPagePiece( 4 ) );

		if( !$restaurant->id_restaurant ){
			$restaurant = Restaurant::o( c::getPagePiece( 4 ) );
		}

		$hasPermission = false;

		if (c::admin()->permission()->check(['global', 'restaurants-all', 'restaurants-crud', 'restaurant-'.$restaurant->id_restaurant.'-edit', 'restaurant-'.$restaurant->id_restaurant.'-all','community-director'])) {
			$hasPermission = true;
		}

		if(c::admin()->isCommunityDirector()){
			$community = c::admin()->communityDirectorCommunity();
			if($community->id_community != $restaurant->community()->id_community){
				$hasPermission = false;
			};
		}

		$this->restaurant = $restaurant;

		// permission to close/open restaurant
		if (!$hasPermission &&
				(c::getPagePiece(3) == 'close-for-today' || c::getPagePiece(3) == 'force-reopen-for-today') &&
				c::admin()->permission()->check(['community-cs'])) {
			$communities = c::user()->communitiesDriverDelivery();
			foreach($communities as $community){
				$_community = $this->restaurant->community();
				if ($community->id_community == $_community->id_community) {
					$hasPermission = true;
				}
			}
		}

		if( !$this->restaurant->id_restaurant ){
			$this->error(404, true);
		}

		if (!$hasPermission) {
			$this->error(401, true);
		}

		switch (c::getPagePiece(3)) {
			case 'cover':
				$this->_cover();
				break;
			case 'basic':
				$this->_basic();
				break;
			case 'hours':
				$this->_hours();
				break;
			case 'delivery':
				$this->_delivery();
				break;
			case 'notes':
				$this->_notes();
				break;
			case 'notifications':
				$this->_notifications();
				break;
			case 'menu':
				$this->_menu();
				break;
			case 'duplicate':
				$this->_duplicate();
				break;
			case 'close-for-today':
				$this->_closeForToday();
				break;
			case 'force-reopen-for-today':
				$this->_forceReopenForToday();
				break;
		}
	}

	private function _duplicate(){
		$id_restaurant = $this->restaurant->duplicate();
		$restaurant = Restaurant::o( $id_restaurant );
		$restaurant->permalink = 'restaurant-' . $id_restaurant;
		$restaurant->save();
		$this->_return( [ 'permalink' => $restaurant->permalink ] );
	}

	private function _closeForToday(){
		$this->restaurant->closeForBusinessForToday();
		$this->_return( [ 'permalink' => $restaurant->permalink ] );
	}

	private function _forceReopenForToday(){
		$this->restaurant->forceReopenForBusiness();
		$this->_return( [ 'permalink' => $restaurant->permalink ] );
	}


	private function _new(){

		if (!c::admin()->permission()->check(['global','restaurants-all', 'restaurants-crud', 'restaurants-create']) && !c::user()->isCommunityDirector()) {
			$this->error(401, true);
		}

		$restaurant = new Restaurant;
		$restaurant->cash = 1;
		$restaurant->campus_cash = 1;
		$restaurant->credit = 1;
		$restaurant->giftcard = 1;
		$restaurant->delivery = 1;
		$restaurant->takeout = 1;
		$restaurant->confirmation = 0;
		$restaurant->charge_credit_fee = 1;
		$restaurant->max_pay_promotion = 2;
		$restaurant->pay_apology_credits = 1;
		$restaurant->promotion_maximum = 2;
		$restaurant->pay_promotions = 1;
		$restaurant->max_apology_credit = 5;
		$restaurant->fee_customer = '0';
		$restaurant->formal_relationship = 1;
		$restaurant->delivery_min_amt = 1;
		$restaurant->confirmation_type = 'regular';
		$restaurant->save();

		$category = new Crunchbutton_Category();
		$category->id_restaurant = $restaurant->id_restaurant;
		$category->name = '';
		$category->sort = 1;
		$category->save();

		$payment_type = $restaurant->payment_type();
		$payment_type->charge_credit_fee = 1;
		$payment_type->max_pay_promotion = 2;
		$payment_type->pay_apology_credits = 1;
		$payment_type->formal_relationship = 1;
		$payment_type->pay_promotions = 1;
		$payment_type->promotion_maximum = 2;
		$payment_type->max_apology_credit = 5;
		$payment_type->save();

		$restaurant->name = 'Restaurant ' . $restaurant->id_restaurant;
		$restaurant->permalink = 'restaurant-' . $restaurant->id_restaurant;
		$restaurant->save();

		if(c::user()->isCommunityDirector()){
			$restaurant->saveCommunity(c::user()->communityDirectorCommunity()->id_community);
		}


		$out = [ 'id_restaurant' => $restaurant->id_restaurant, 'permalink' => $restaurant->permalink ];
		$this->_return( $out );
	}

	private function _cover(){
		$community = $this->restaurant->community()->get( 0 );
		$out = [];
		$out[ 'id_restaurant' ] = $this->restaurant->id_restaurant;
		$out[ 'name' ] = $this->restaurant->name;
		$out[ 'permalink' ] = $this->restaurant->permalink;
		$out[ 'image' ] = $this->restaurant->getImages('name');
		$out[ 'image' ] = $this->restaurant->getImages('name');
		if(!$this->restaurant->open_for_business && $this->restaurant->reopen_for_business_at){
			$out['closed_for_today'] = true;
		}
		$this->_return( $out );
	}

	private function _basic(){
		switch ( $this->method() ) {
			case 'post':
				$this->_basicSave();
				break;
			case 'get':
				$this->_basicExport();
				break;
		}
	}

	private function _hours(){
		switch ( $this->method() ) {
			case 'post':
				$this->_hoursSave();
				break;
			case 'get':
				$this->_hoursExport();
				break;
		}
	}

	private function _delivery(){
		switch ( $this->method() ) {
			case 'post':
				$this->_deliverySave();
				break;
			case 'get':
				$this->_deliveryExport();
				break;
		}
	}

	private function _notes(){
		switch ( $this->method() ) {
			case 'post':
				$this->_notesSave();
				break;
			case 'get':
				$this->_notesExport();
				break;
		}
	}

	private function _notifications(){
		switch ( $this->method() ) {
			case 'post':
				$this->_notificationsSave();
				break;
			case 'get':
				$this->_notificationsExport();
				break;
		}
	}

	private function _menu(){
		switch ( $this->method() ) {
			case 'post':
				$this->_menuSave();
				break;
			case 'get':
				$this->_menuExport();
				break;
		}
	}

	private function _basicExport( $printJson = true ){

		$community = $this->restaurant->community()->get(0);

		$out = [];
		$out[ 'id_restaurant' ] = $this->restaurant->id_restaurant;
		$out[ 'name' ] = $this->restaurant->name;
		$out[ 'permalink' ] = $this->restaurant->permalink;
		$out[ '_permalink' ] = $this->restaurant->permalink;
		$out[ 'id_community' ] = $community->id_community;
		$out[ 'short_description' ] = $this->restaurant->short_description;
		$out[ 'force_close_tagline' ] = $this->restaurant->force_close_tagline;
		$out[ 'message' ] = $this->restaurant->message;
		$out[ 'phone' ] = $this->restaurant->phone;
		$out[ 'address' ] = $this->restaurant->address;
		$out[ 'loc_lat' ] = $this->restaurant->loc_lat;
		$out[ 'loc_long' ] = $this->restaurant->loc_long;
		$out[ 'formal_relationship' ] = $this->restaurant->formal_relationship;
		$out[ 'open_for_business' ] = $this->restaurant->open_for_business;
		$out[ 'active' ] = $this->restaurant->active;
		$out[ 'timezone' ] = $this->restaurant->timezone;
		$out[ 'active_restaurant_order_placement' ] = $this->restaurant->active_restaurant_order_placement;
		$out[ 'show_when_closed' ] = $this->restaurant->show_when_closed;
		$out[ 'cash' ] = $this->restaurant->cash;
		$out[ 'campus_cash' ] = $this->restaurant->campus_cash;
		$out[ 'credit' ] = $this->restaurant->credit;
		$out[ 'giftcard' ] = $this->restaurant->giftcard;
		$out[ 'fee_on_subtotal' ] = $this->restaurant->fee_on_subtotal;
		$out[ 'fee_restaurant' ] = $this->restaurant->fee_restaurant;
		$out[ 'fee_customer' ] = $this->restaurant->fee_customer;
		$out[ 'image' ] = $this->restaurant->getImages( 'name' );
		$out[ 'id_community_chain' ] = null;
		$out[ 'tax' ] = intval( $this->restaurant->tax );
		$out[ 'service_fee' ] = floatval( $this->restaurant->service_fee );

		$chain = $this->restaurant->chain();
		if( $chain->id_community_chain ){
			$out[ 'id_community_chain' ] = $chain->id_community_chain;
		}

		if( $printJson ){
			$this->_return( $out );
		} else {
			return $out;
		}
	}

	private function _basicSave(){

		$fields = $this->_basicExport( false );

		foreach ( $fields as $key => $val) {
			$this->restaurant->{$key} = $this->request()[ $key ];
		}

		$_restaurant = Crunchbutton_Restaurant::permalink( $this->restaurant->_permalink );
		if( $_restaurant->id_restaurant && $this->restaurant->id_restaurant != $_restaurant->id_restaurant ){
			echo json_encode( [ 'error' => 'this permalink is already in use for another restaurant' ] );exit;
		}
		$this->restaurant->permalink = $this->restaurant->_permalink;
		$this->restaurant->save();

		Cockpit_Restaurant_Chain::removeChainsByIdRestaurant( $this->restaurant->id_restaurant );

		if( $this->request()[ 'id_community_chain' ] ){

			Cockpit_Restaurant_Chain::removeChainsByIdCommunityChain( $this->request()[ 'id_community_chain' ] );

			$restaurantChain = new Restaurant_Chain;
			$restaurantChain->id_community_chain = $this->request()[ 'id_community_chain' ];
			$restaurantChain->id_restaurant = $this->restaurant->id_restaurant;
			$restaurantChain->save();
		}

		if( $this->request()[ 'id_community' ] ){
			$this->restaurant->removeCommunity();
			$this->restaurant->saveCommunity( $this->request()[ 'id_community' ] );
		}


		if( $this->restaurant->id_restaurant ){
			echo json_encode( [ 'success' => true ] );exit;
		}
		echo json_encode( [ 'error' => 'not saved' ] );exit;
	}

	private function _hoursExport(){
		$out = [ 'id_restaurant' => $this->restaurant->id_restaurant, 'permalink' => $this->restaurant->permalink ];
		foreach ($this->restaurant->hours() as $hours) {
			$out[ 'hours' ][ $hours->day ][] = [ $hours->time_open, $hours->time_close ];
		}
		$this->_return( $out );
	}

	private function _hoursSave(){
		$hours = $this->request()[ '_hours' ];
		$this->restaurant->saveHours( $hours );
		if( $this->restaurant->id_restaurant ){
			echo json_encode( [ 'success' => true ] );exit;
		}
		echo json_encode( [ 'error' => 'not saved' ] );exit;
	}

	private function _deliveryExport( $printJson = true ){
		$out = [ 'id_restaurant' => $this->restaurant->id_restaurant, 'permalink' => $this->restaurant->permalink ];
		$out[ 'delivery_service' ] = $this->restaurant->delivery_service;
		$out[ 'allow_preorder' ] = $this->restaurant->allow_preorder;
		$out[ 'delivery_radius_type' ] = $this->restaurant->delivery_radius_type;
		$out[ 'order_ahead_time' ] = $this->restaurant->order_ahead_time;
		$out[ 'delivery' ] = $this->restaurant->delivery;
		$out[ 'delivery_min' ] = $this->restaurant->delivery_min;
		$out[ 'delivery_min_amt' ] = $this->restaurant->delivery_min_amt;
		$out[ 'service_time' ] = $this->restaurant->service_time;
		$out[ 'delivery_fee' ] = $this->restaurant->delivery_fee;
		$out[ 'delivery_radius' ] = $this->restaurant->delivery_radius;
		$out[ 'delivery_estimated_time' ] = $this->restaurant->delivery_estimated_time;
		$out[ 'takeout' ] = $this->restaurant->takeout;
		$out[ 'pickup_estimated_time' ] = $this->restaurant->pickup_estimated_time;
		$out[ 'delivery_service_markup' ] = $this->restaurant->delivery_service_markup;
		if( $printJson ){
			$this->_return( $out );
		} else {
			return $out;
		}
	}

	private function _deliverySave(){
		$fields = $this->_deliveryExport( false );
		foreach ( $fields as $key => $val) {
			$this->restaurant->{$key} = $this->request()[ $key ];
		}
		$this->restaurant->save();
		if( $this->restaurant->id_restaurant ){
			echo json_encode( [ 'success' => true ] );exit;
		}
		echo json_encode( [ 'error' => 'not saved' ] );exit;
	}

	private function _notesExport( $printJson = true ){
		$out = [ 'id_restaurant' => $this->restaurant->id_restaurant, 'permalink' => $this->restaurant->permalink ];
		$out[ 'email' ] = $this->restaurant->email;
		$out[ 'notes' ] = $this->restaurant->notes;
		$out[ 'notes_owner' ] = $this->restaurant->notes_owner;
		$out[ 'notes_to_driver' ] = $this->restaurant->notes_to_driver;
		$out[ 'notes_todo' ] = $this->restaurant->notes_todo;
		if( $printJson ){
			$this->_return( $out );
		} else {
			return $out;
		}
	}

	private function _notesSave(){

		$fields = $this->_notesExport( false );

		foreach ( $fields as $key => $val) {
			$this->restaurant->{$key} = $this->request()[ $key ];
		}
		$this->restaurant->save();
		if( $this->restaurant->id_restaurant ){
			echo json_encode( [ 'success' => true ] );exit;
		}
		echo json_encode( [ 'error' => 'not saved' ] );exit;
	}

	private function _notificationsExport(){
		$out = [ 'id_restaurant' => $this->restaurant->id_restaurant, 'permalink' => $this->restaurant->permalink ];
		$out[ 'order_notifications_sent' ] = $this->restaurant->order_notifications_sent;
		$out[ 'confirmation' ] = $this->restaurant->confirmation;
		$out[ 'confirmation_type' ] = $this->restaurant->confirmation_type;
		$out['notifications'] = [];
		foreach ( $this->restaurant->notifications( [ 'active' => null ] ) as $notification ) {
			$out['notifications'][] = $notification->exports();
		}
		$this->_return( $out );
	}


	private function _notificationsSave(){

		$notification = $this->request()[ 'notifications' ];
		foreach ( $notification as $notification ) {

			if( $notification[ 'id_notification' ] ){
				$n = Notification::o( $notification[ 'id_notification' ] );
			} else {
				$n = new Notification;
			}
			$n->id_restaurant = $this->restaurant->id_restaurant;
			$n->type = $notification[ 'type' ];
			$n->value = $notification[ 'value' ];
			$n->active = $notification[ 'active' ];
			$n->save();
		}

		$this->restaurant->order_notifications_sent = $this->request()[ 'order_notifications_sent' ];
		$this->restaurant->confirmation = $this->request()[ 'confirmation' ];
		$this->restaurant->confirmation_type = $this->request()[ 'confirmation_type' ];
		$this->restaurant->save();

		if( $this->restaurant->id_restaurant ){
			echo json_encode( [ 'success' => true ] );exit;
		}
		echo json_encode( [ 'error' => 'not saved' ] );exit;
	}

	private function _menuExport( $printJson = true ){
		$out = [ 'id_restaurant' => $this->restaurant->id_restaurant, 'permalink' => $this->restaurant->permalink ];
		$out['categories'] = [];
		foreach ( $this->restaurant->categories( [ 'Dish' => [ 'active' => null ] ] ) as $category ) {
			$out['categories'][] = $category->exports();
		}
		if( $printJson ){
			$this->_return( $out );
		} else {
			return $out;
		}

	}

	private function _menuSave(){

		$categories = $this->request()[  'categories' ];
		$_categories = $this->_menuExport( false );
		$_categories = $_categories[ 'categories' ];

		Log::debug( [
					'old_items' 		=> $_categories,
					'new_items' 		=> $categories,
					'id_admin' 			=> c::user()->id_admin,
					'id_restaurant'	=> $this->restaurant->id_restaurant,
					'permalink'			=> $this->restaurant->permalink,
					'type' 					=> 'restaurant-menu-save'
				]);

		$remove = [ 'categories' => [], 'dishes' => [], 'options' => [] ];

		// save categories
		foreach( $categories as $catKey => $category ){
			if( $category[ 'id_category' ] ){
				$_category = Category::o( $category[ 'id_category' ] );
			} else if( !$category[ 'id_category' ] ) {
				$_category = new Category;
			}
			$_category->name = $category[ 'name' ];
			$_category->sort = $category[ 'sort' ];
			$_category->id_restaurant = $this->restaurant->id_restaurant;
			$_category->save();
			$category[ 'id_category' ] = $_category->id_category;
			$categories[ $catKey ] = $category;
		}

		// mark categories to be removed
		foreach ( $_categories as $_category ) {
			$_remove = true;
			foreach( $categories as $catKey => $category ){
				if( $category[ 'id_category' ] == $_category[ 'id_category' ] ){
					$_remove = false;
					continue;
				}
			}
			if( $_remove ){
				$remove[ 'categories' ][] = $_category[ 'id_category' ];
			}
		}

		// dishes
		foreach( $categories as $catKey => $category ){
			$dishes = $category[ '_dishes' ];
			if( count( $dishes ) ){
				foreach( $dishes as $dishKey => $dish ){
					if( $dish[ 'id_dish' ] ){
						$_dish = Dish::o( $dish[ 'id_dish' ] );
					} else {
						$_dish = new Dish;
					}
					$_dish->name = $dish[ 'name' ];
					$_dish->price = $dish[ 'price' ];
					$_dish->image = $dish[ 'image' ];
					$_dish->top = $dish[ 'top' ];
					$_dish->top_name = $dish[ 'top_name' ];
					$_dish->description = $dish[ 'description' ];
					$_dish->image = $dish[ 'image' ];
					$_dish->type = $dish[ 'type' ];
					$_dish->changeable_price = $dish[ 'changeable_price' ];
					$_dish->expand_view = $dish[ 'expand_view' ];
					$_dish->sort = $dish[ 'sort' ];
					$_dish->active = $dish[ 'active' ];
					$_dish->id_category = $category[ 'id_category' ];
					$_dish->id_restaurant = $this->restaurant->id_restaurant;
					$_dish->save();
					$dish[ 'id_dish' ] = $_dish->id_dish;
					$categories[ $catKey ][ '_dishes' ][ $dishKey ] = $dish;
				}
			}
		}

		// mark dishes to be removed
		foreach( $_categories as $_category ){
			$_dishes = $_category[ '_dishes' ];
			foreach( $_dishes as $_dish ){
				$_remove = true;
				foreach( $categories as $category ){
					$dishes = $category[ '_dishes' ];
					foreach( $dishes as $dish ){
						if( $_dish[ 'id_dish' ] == $dish[ 'id_dish' ] ){
							$_remove = false;
							continue;
						}
					}
				}
				if( $_remove ){
					$remove[ 'dishes' ][] = $_dish[ 'id_dish' ];
				}
			}
		}

		// checkbox options
		foreach( $categories as $catKey => $category ){
			$dishes = $category[ '_dishes' ];
			if( count( $dishes ) ){
				foreach( $dishes as $dishKey => $dish ){
					$checkboxes = $dish[ 'options' ][ 'checkboxes' ];
					if( count( $checkboxes ) ){
						$_dish = Dish::o( $dish[ 'id_dish' ] );
						$_dish_options = $_dish->options();
						foreach ( $checkboxes as $checkbox ) {
							if( $checkbox[ 'id_option' ] ){
								$_option = Option::o( $checkbox[ 'id_option' ] );
							} else {
								$_option = new Option;
							}
							$_option->name = $checkbox[ 'name' ];
							$_option->price = $checkbox[ 'price' ];
							$_option->description = $checkbox[ 'description' ];
							$_option->type = 'check';
							$_option->price_linked = $checkbox[ 'price_linked' ];
							$_option->default = $checkbox[ 'default' ];
							$_option->id_restaurant = $this->restaurant->id_restaurant;
							$_option->save();

							$dish_option_id = $this->restaurant->_hasOption( $_option, $_dish_options );

							if( $dish_option_id ){
								$do = Dish_Option::o( $dish_option_id );
							} else {
								$do = new Dish_Option;
							}
							$do->id_dish = $_dish->id_dish;
							$do->id_option = $_option->id_option;
							$do->sort = $checkbox[ 'sort' ];
							$do->default = $checkbox[ 'default' ];
							if( $do->default ){
								$do->date = date( 'Y-m-d H:i:s' );
							}
							$do->save();
						}
					}
				}
			}
		}

		// select options
		foreach( $categories as $catKey => $category ){
			$dishes = $category[ '_dishes' ];
			if( count( $dishes ) ){
				foreach( $dishes as $dishKey => $dish ){
					$selects = $dish[ 'options' ][ 'selects' ];
					if( count( $selects ) ){
						$_dish = Dish::o( $dish[ 'id_dish' ] );
						$_dish_options = $_dish->options();
						foreach ( $selects as $selectKey => $select ) {

							if( $select[ 'id_option' ] && strpos( $select[ 'id_option' ], '_' ) === false ){
								$_option = Option::o( $select[ 'id_option' ] );
							} else {
								$_option = new Option;
							}
							$_option->name = $select[ 'name' ];
							$_option->price = $select[ 'price' ];
							$_option->description = $select[ 'description' ];
							$_option->type = 'select';
							$_option->price_linked = $select[ 'price_linked' ];
							$_option->default = $select[ 'default' ];
							$_option->id_restaurant = $this->restaurant->id_restaurant;
							$_option->save();

							$dish_option_id = $this->restaurant->_hasOption( $_option, $_dish_options );
							if( $dish_option_id ){
								$do = Dish_Option::o( $dish_option_id );
							} else {
								$do = new Dish_Option;
							}
							$do->id_dish = $_dish->id_dish;
							$do->id_option = $_option->id_option;
							$do->sort = $select[ 'sort' ];
							$do->default = $select[ 'default' ];
							if( $do->default ){
								$do->date = date( 'Y-m-d H:i:s' );
							}
							$do->save();
							$select[ 'id_option' ] = $_option->id_option;
							$selects[ $selectKey ] = $select;
							$suboptions = $select[ 'options' ];
							if( count( $suboptions ) ){
								foreach( $suboptions as $suboptionKey => $suboption ){
									if( $suboption[ 'id_option' ] ){
										$_suboption = Option::o( $suboption[ 'id_option' ] );
									} else {
										$_suboption = new Option;
									}
									$_suboption->name = $suboption[ 'name' ];
									$_suboption->price = $suboption[ 'price' ];
									$_suboption->description = $suboption[ 'description' ];
									$_suboption->id_option_parent = $select[ 'id_option' ];
									$_suboption->type = 'check';
									$_suboption->price_linked = $suboption[ 'price_linked' ];
									$_suboption->default = $suboption[ 'default' ];
									$_suboption->id_restaurant = $this->restaurant->id_restaurant;
									$_suboption->save();

									$dish_suboption_id = $this->restaurant->_hasOption( $_suboption, $_dish_options );
									if( $dish_suboption_id ){
										$dso = Dish_Option::o( $dish_suboption_id );
									} else {
										$dso = new Dish_Option;
									}
									$dso->id_dish = $_dish->id_dish;
									$dso->id_option = $_suboption->id_option;
									$dso->sort = $suboption[ 'sort' ];
									$dso->default = $suboption[ 'default' ];
									if( $dso->default ){
										$dso->date = date( 'Y-m-d H:i:s' );
									}
									$dso->save();
								}
							}
						}
					}
				}
			}
		}

		// mark options to be removed
		foreach( $_categories as $_category ){
			$_dishes = $_category[ '_dishes' ];
			if( count( $_dishes ) ){
				foreach( $_dishes as $_dish ){
					$_options = $_dish[ '_options' ];
					if( count( $_options ) ){
						foreach ( $_options as $_option ) {
							$_remove = true;
							foreach( $categories as $category ){
								$dishes = $category[ '_dishes' ];
								if( count( $dishes ) ){
									foreach( $dishes as $dish ){
										$selects = $dish[ 'options' ][ 'selects' ];
										if( count( $selects ) ){
											foreach ( $selects as $select ) {
												if( $select[ 'id_option' ] == $_option[ 'id_option' ] ){
													$_remove = false;
													continue;
												}
												$suboptions = $select[ 'options' ];
												if( count( $suboptions ) ){
													foreach( $suboptions as $suboption ){
														if( $suboption[ 'id_option' ] == $_option[ 'id_option' ] ){
															$_remove = false;
															continue;
														}
													}
												}
											}
										}
										$checkboxes = $dish[ 'options' ][ 'checkboxes' ];
										if( count( $checkboxes ) ){
											foreach ( $checkboxes as $checkbox ) {
												if( $checkbox[ 'id_option' ] == $_option[ 'id_option' ] ){
													$_remove = false;
													continue;
												}
											}
										}
									}
								}
							}
							if( $_remove ){
								$remove[ 'options' ][] = $_option[ 'id_dish_option' ];
							}
						}
					}
				}
			}
		}

		// remove stuff

		// options
		if( count( $remove[ 'options' ] ) ){
			foreach( $remove[ 'options' ] as $id_dish_option ){
				$dish_option = Dish_Option::o( $id_dish_option );
				$options = Dish_Option::q( 'SELECT * FROM dish_option WHERE id_option = ? AND id_dish_option != ?', [ $dish_option->id_option, $dish_option->id_dish_option ] );
				// remove the option
				if( $options->count() == 0 ){
					$option = Option::o( $dish_option->id_option );
					$option->delete();
				}
				$dish_option->delete();
			}
		}

		// dishes
		if( count( $remove[ 'dishes' ] ) ){
			foreach( $remove[ 'dishes' ] as $id_dish ){
				$dish = Dish::o( $id_dish );
				if( $dish->id_dish ){
					$dish->delete();
				}
			}
		}

		// categories
		if( count( $remove[ 'categories' ] ) ){
			foreach( $remove[ 'categories' ] as $id_category ){
				$category = Category::o( $id_category );
				if( $category->id_category ){
					$category->delete();
				}
			}
		}
		echo json_encode( [ 'success' => true ] );exit;;
	}

	private function _return( $out ){
		foreach( $out as $key => $val ){
			if( is_numeric( $val ) ){
				$out[ $key ] = floatval( $val );
			}
		}
		echo json_encode( $out );exit;
	}

}