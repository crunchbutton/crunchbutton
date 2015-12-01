<?php

class Controller_api_restaurant_edit extends Crunchbutton_Controller_RestAccount {

	public function init() {

		// $this->restaurant = Restaurant::o( 107 );
		// $this->_menuSave();exit;

		if( c::getPagePiece( 3 ) == 'new' ){
			return $this->_new();
		}

		$restaurant = Restaurant::permalink( c::getPagePiece( 4 ) );

		if( !$restaurant->id_restaurant ){
			$restaurant = Restaurant::o( c::getPagePiece( 4 ) );
		}

		if (!c::admin()->permission()->check(['global', 'restaurants-all', 'restaurants-crud', 'restaurant-'.$restaurant->id_restaurant.'-edit', 'restaurant-'.$restaurant->id_restaurant.'-all'])) {
			$this->error(401);
		}

		$this->restaurant = $restaurant;

		if( !$this->restaurant->id_restaurant ){
			$this->error(404);
		}

		switch (c::getPagePiece(3)) {
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
		}
	}

	private function _new(){

		if (!c::admin()->permission()->check(['global','restaurants-all', 'restaurants-crud', 'restaurants-create'])) {
			$this->error(401);
		}

		$restaurant = new Restaurant;
		$restaurant->cash = 1;
		$restaurant->credit = 1;
		$restaurant->giftcard = 1;
		$restaurant->delivery = 1;
		$restaurant->takeout = 1;
		$restaurant->confirmation = 1;
		$restaurant->charge_credit_fee = 1;
		$restaurant->max_pay_promotion = 2;
		$restaurant->pay_apology_credits = 1;
		$restaurant->promotion_maximum = 2;
		$restaurant->pay_promotions = 1;
		$restaurant->max_apology_credit = 5;
		$restaurant->fee_customer = '0';
		$restaurant->formal_relationship = 1;
		$restaurant->save();

		$payment_type = $restaurant->payment_type();
		$payment_type->charge_credit_fee = 1;
		$payment_type->max_pay_promotion = 2;
		$payment_type->pay_apology_credits = 1;
		$payment_type->formal_relationship = 1;
		$payment_type->pay_promotions = 1;
		$payment_type->promotion_maximum = 2;
		$payment_type->max_apology_credit = 5;
		$payment_type->save();

		$restaurant->permalink = 'restaurant-' . $restaurant->id_restaurant;
		$restaurant->save();

		$out = [ 'id_restaurant' => $restaurant->id_restaurant, 'permalink' => $restaurant->permalink ];
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

		$community = $this->restaurant->community()->get( 0 );

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
		$out[ 'credit' ] = $this->restaurant->credit;
		$out[ 'giftcard' ] = $this->restaurant->giftcard;
		$out[ 'fee_on_subtotal' ] = $this->restaurant->fee_on_subtotal;
		$out[ 'fee_restaurant' ] = $this->restaurant->fee_restaurant;
		$out[ 'fee_customer' ] = $this->restaurant->fee_customer;
		$out[ 'tax' ] = $this->restaurant->tax;

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
		// $categories = json_decode( '[{"id_category":382,"id_restaurant":107,"name":"Most Popular","sort":1,"loc":true,"id":"382","_dishes":[{"id_dish":1526,"name":"test 2 a","price":1,"image":null,"id_restaurant":107,"top":true,"top_name":null,"description":null,"type":null,"id_category":382,"active":true,"sort":1,"expand_view":true,"id":1526,"changeable_price":true,"_options":[{"id_option":8458,"name":"group 1","price":1,"id_restaurant":107,"id_option_parent":0,"description":null,"type":"check","price_linked":0,"default":true,"sort":1,"id_dish_option":8627,"id":8458,"prices":[],"options":[],"show_up":false,"show_down":true},{"id_option":8459,"name":"group 2","price":1,"id_restaurant":107,"id_option_parent":0,"description":null,"type":"check","price_linked":0,"default":true,"sort":2,"id_dish_option":8628,"id":8459,"prices":[],"options":[],"show_up":true,"show_down":false}],"options":{"selects":[],"checkboxes":[{"id_option":8458,"name":"group 1","price":1,"id_restaurant":107,"id_option_parent":0,"description":null,"type":"check","price_linked":0,"default":true,"sort":1,"id_dish_option":8627,"id":8458,"prices":[],"options":[],"show_up":false,"show_down":true},{"id_option":8459,"name":"group 2","price":1,"id_restaurant":107,"id_option_parent":0,"description":null,"type":"check","price_linked":0,"default":true,"sort":2,"id_dish_option":8628,"id":8459,"prices":[],"options":[],"show_up":true,"show_down":false}]},"show_up":false,"show_down":true},{"id_dish":1525,"name":"test 1 b","price":1,"image":null,"id_restaurant":107,"top":false,"top_name":null,"description":null,"type":null,"id_category":382,"active":true,"sort":2,"expand_view":false,"id":1525,"changeable_price":false,"options":{"selects":[],"checkboxes":[]},"show_up":true,"show_down":true},{"id_dish":1527,"name":"test 3 c","price":1,"image":null,"id_restaurant":107,"top":false,"top_name":null,"description":null,"type":null,"id_category":382,"active":true,"sort":3,"expand_view":false,"id":1527,"changeable_price":false,"options":{"selects":[],"checkboxes":[]},"show_up":true,"show_down":true},{"id_dish":2632,"name":"fries d","price":10,"image":null,"id_restaurant":107,"top":false,"top_name":null,"description":null,"type":null,"id_category":382,"active":true,"sort":4,"expand_view":false,"id":2632,"changeable_price":false,"options":{"selects":[],"checkboxes":[]},"show_up":true,"show_down":true},{"id_dish":16999,"name":"aaa e","price":10,"image":null,"id_restaurant":107,"top":false,"top_name":null,"description":null,"type":null,"id_category":382,"active":true,"sort":5,"expand_view":true,"id":16999,"changeable_price":false,"options":{"selects":[],"checkboxes":[]},"show_up":true,"show_down":true},{"id_dish":1528,"name":"test f","price":1,"image":null,"id_restaurant":107,"top":false,"top_name":null,"description":null,"type":null,"id_category":382,"active":true,"sort":6,"expand_view":false,"id":1528,"changeable_price":false,"options":{"selects":[],"checkboxes":[]},"show_up":true,"show_down":false}],"show_up":false,"show_down":true},{"id_category":2098,"id_restaurant":107,"name":"another one","sort":2,"loc":false,"id":"2098","_dishes":[{"id_dish":6804,"name":"dish test g","price":10,"image":null,"id_restaurant":107,"top":false,"top_name":null,"description":null,"type":null,"id_category":2098,"active":true,"sort":1,"expand_view":true,"id":6804,"changeable_price":false,"options":{"selects":[],"checkboxes":[]},"show_up":false,"show_down":true},{"id_dish":7036,"name":"Gift Card Redeem h","price":5,"image":null,"id_restaurant":107,"top":false,"top_name":null,"description":null,"type":null,"id_category":2098,"active":true,"sort":2,"expand_view":true,"id":7036,"changeable_price":false,"options":{"selects":[],"checkboxes":[]},"show_up":true,"show_down":false}],"show_up":true,"show_down":false}]' );

		$_categories = $this->_menuExport( false );
		$_categories = $_categories[ 'categories' ];

		$actions = [];
		$actions[ 'add' ] = [ 'categories' => [], 'dishes' => [], 'options' => [] ];
		$actions[ 'delete' ] = [ 'categories' => [], 'dishes' => [], 'options' => [] ];
		$actions[ 'update' ] = [ 'categories' => [], 'dishes' => [], 'options' => [] ];

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

		foreach( $_categories as $_category ){
			$delete = true;
			foreach( $categories as $category ){
				if( $_category[ 'id_category' ] == $category[ 'id_category' ] ){
					$delete = false;
					continue;
				}
			}
			if( $delete ){
				$actions[ 'delete' ][ 'categories' ][] = $_category[ 'id_category' ];
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

		foreach( $_categories as $_category ){
			$_dishes = $_category[ '_dishes' ];
			foreach( $_dishes as $_dish ){
				$delete = true;
				foreach( $categories as $category ){
					$dishes = $category[ '_dishes' ];
					foreach( $dishes as $dish ){
						if( $_dish[ 'id_dish' ] == $dish[ 'id_dish' ] ){
							$delete = false;
							continue;
						}
					}
				}
				if( $delete ){
					$actions[ 'delete' ][ 'dishes' ][] = $_dish[ 'id_dish' ];
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
							$do->date = date( 'Y-m-d H:i:s' );
							$do->default = $checkbox[ 'default' ];
							$do->save();
						}
					}
				}
			}
		}

		// remove options
		foreach( $_categories as $_category ){
			$_dishes = $_category[ '_dishes' ];
			if( count( $_dishes ) ){
				foreach( $_dishes as $_dish ){
					$_options = $_dish[ '_options' ];
					if( count( $_options ) ){
						foreach ( $_options as $_option ) {
							$delete = true;

							foreach( $categories as $category ){
								$dishes = $category[ '_dishes' ];
								if( count( $dishes ) ){
									foreach( $dishes as $dish ){
										$selects = $dish[ 'options' ][ 'selects' ];
										if( count( $selects ) ){
											foreach ( $selects as $select ) {
												if( $select[ 'id_option' ] == $_option[ 'id_option' ] ){
													$delete = false;
													continue;
												}
												$suboptions = $select[ 'options' ];
												if( count( $suboptions ) ){
													foreach( $suboptions as $suboption ){
														if( $suboption[ 'id_option' ] == $_option[ 'id_option' ] ){
															$delete = false;
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
													$delete = false;
													continue;
												}
											}
										}
									}
								}
							}
							if( $delete ){
								$actions[ 'delete' ][ 'options' ][] = $_option[ 'id_option' ];
							}
						}
					}
				}
			}
		}

		echo json_encode( $actions );exit;
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