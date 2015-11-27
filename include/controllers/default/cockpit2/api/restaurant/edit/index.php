<?php

class Controller_api_restaurant_edit extends Crunchbutton_Controller_RestAccount {

	public function init() {

		$restaurant = Restaurant::permalink( c::getPagePiece( 4 ) );

		if (!c::admin()->permission()->check(['global', 'restaurants-all', 'restaurants-crud', 'restaurant-'.$restaurant->id_restaurant.'-edit', 'restaurant-'.$restaurant->id_restaurant.'-all'])) {
			$this->error(401);
		}

		$this->restaurant = $restaurant;

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
		}
	}

	private function _basic(){
		switch ( $this->method() ) {
			case 'post':
				// $this->_basicSave();
				break;
			case 'get':
				$this->_basicExport();
				break;
		}
	}

	private function _hours(){
		switch ( $this->method() ) {
			case 'post':
				// $this->_hoursSave();
				break;
			case 'get':
				$this->_hoursExport();
				break;
		}
	}

	private function _delivery(){
		switch ( $this->method() ) {
			case 'post':
				// $this->_deliverySave();
				break;
			case 'get':
				$this->_deliveryExport();
				break;
		}
	}

	private function _notes(){
		switch ( $this->method() ) {
			case 'post':
				// $this->_notesSave();
				break;
			case 'get':
				$this->_notesExport();
				break;
		}
	}

	private function _notifications(){
		switch ( $this->method() ) {
			case 'post':
				// $this->_notificationsSave();
				break;
			case 'get':
				$this->_notificationsExport();
				break;
		}
	}

	private function _basicExport(){

		$community = $this->restaurant->community()->get( 0 );

		$out = [];
		$out[ 'id_restaurant' ] = $this->restaurant->id_restaurant;
		$out[ 'name' ] = $this->restaurant->name;
		$out[ 'permalink' ] = $this->restaurant->permalink;
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

		$this->_return( $out );
	}

	private function _hoursExport(){
		$out = [ 'id_restaurant' => $this->restaurant->id_restaurant, 'permalink' => $this->restaurant->permalink ];
		foreach ($this->restaurant->hours() as $hours) {
			$out[ 'hours' ][ $hours->day ][] = [ $hours->time_open, $hours->time_close ];
		}
		$this->_return( $out );
	}

	private function _deliveryExport(){
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
		$this->_return( $out );
	}

	private function _notesExport(){
		$out = [ 'id_restaurant' => $this->restaurant->id_restaurant, 'permalink' => $this->restaurant->permalink ];
		$out[ 'email' ] = $this->restaurant->email;
		$out[ 'notes' ] = $this->restaurant->notes;
		$out[ 'notes_owner' ] = $this->restaurant->notes_owner;
		$out[ 'notes_to_driver' ] = $this->restaurant->notes_to_driver;
		$out[ 'notes_todo' ] = $this->restaurant->notes_todo;
		$this->_return( $out );
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

	private function _return( $out ){
		foreach( $out as $key => $val ){
			if( is_numeric( $val ) ){
				$out[ $key ] = floatval( $val );
			}
		}
		echo json_encode( $out );exit;
	}

}