<?php

class Cockpit_Admin extends Crunchbutton_Admin {

	public function location() {
		if (!isset($this->_location)) {
			$this->_location = Admin_Location::q('SELECT * FROM admin_location WHERE id_admin="'.$this->id_admin.'" ORDER BY date DESC LIMIT 1')->get(0);
		}
		return $this->_location;
	}

	// return the restaurant the admin could order from #3350
	public function restaurantOrderPlacement(){
		$permission_prefix = 'restaurant-order-placement-';
		$permissions = c::admin()->getAllPermissionsName();
		foreach( $permissions as $permission ){
			if( strpos( $permission->permission, $permission_prefix ) !== false ){
				$id_restaurant = str_replace( $permission_prefix, '', $permission->permission );
				$restaurant = Restaurant::o( $id_restaurant );
				if( $restaurant->id_restaurant ){
					return $restaurant;
				}
			}
		}
		return false;
	}

}