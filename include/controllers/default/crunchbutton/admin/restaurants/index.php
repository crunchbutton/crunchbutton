<?php

class Controller_admin_restaurants extends Crunchbutton_Controller_Account {
	public function init() {
		c::view()->layout('layout/admin');
		c::view()->page = 'admin/restaurants';

		$restaurant = Restaurant::o(c::getPagePiece(2));
		
		if (c::getPagePiece(2) == 'new') {
			c::view()->display('admin/restaurants/restaurant');

		} elseif ($restaurant->id_restaurant) {
			c::view()->restaurant = $restaurant;
			switch (c::getPagePiece(3)) {
				case 'pay':
					c::view()->display('admin/restaurants/pay');					
					break;
				case 'fax':
					foreach ($restaurant->notifications() as $notification) {
						if ($notification->type == 'fax') {
							c::view()->notification = $notification;
						}
					}
					c::view()->display('admin/restaurants/fax');					
					break;
				default:
					c::view()->display('admin/restaurants/restaurant');
					break;
			}

		} else {
			c::view()->display('admin/restaurants/index');
		}


	}
}